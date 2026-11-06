<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\GuruKehadiran;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Rekap kehadiran guru mengajar (harian / mingguan / bulanan), bisa dicetak dan diunduh Excel. */
class RekapGuruController extends Controller
{
    public const MODE = ['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'];

    public function index(Request $request)
    {
        if (! GuruKehadiran::aktif()) return view('admin.rekap-guru.belum-siap');

        $mode = array_key_exists($request->mode, self::MODE) ? $request->mode : 'harian';
        try {
            $tanggal = $request->filled('tanggal') ? Carbon::parse($request->tanggal)->startOfDay() : today();
        } catch (\Throwable) {
            $tanggal = today();
        }
        if ($tanggal->isFuture()) $tanggal = today();
        [$dari, $sampai] = match ($mode) {
            'mingguan' => [$tanggal->copy()->startOfWeek(), $tanggal->copy()->endOfWeek()->startOfDay()],
            'bulanan' => [$tanggal->copy()->startOfMonth(), $tanggal->copy()->endOfMonth()->startOfDay()],
            default => [$tanggal->copy(), $tanggal->copy()],
        };

        // Hari sekolah = Senin–Jumat sampai hari ini (hari setelah hari ini belum dihitung)
        $akhir = $sampai->greaterThan(today()) ? today() : $sampai;
        $hariSekolah = $akhir->lessThan($dari) ? collect() : collect(CarbonPeriod::create($dari, $akhir))->filter(fn ($d) => $d->isWeekday())->map->toDateString()->values();

        $guru = User::where('role', User::GURU)->with('kelasDiampu')->orderBy('name')->get();
        $catatan = GuruKehadiran::whereIn('user_id', $guru->pluck('id'))
            ->where('tanggal', '>=', $dari->toDateString())->where('tanggal', '<=', $sampai->toDateString())
            ->get()->groupBy('user_id');
        // Tanggal-tanggal absensi siswa yang diisi untuk kelas tiap guru (bukti kegiatan mengajar)
        $absenKelas = Absensi::whereIn('kelas_id', $guru->flatMap->kelasDiampu->pluck('id'))
            ->whereDate('tanggal', '>=', $dari)->whereDate('tanggal', '<=', $sampai)
            ->get(['kelas_id', 'tanggal'])->groupBy('kelas_id')
            ->map(fn ($r) => $r->map(fn ($x) => Carbon::parse($x->tanggal)->toDateString())->unique()->values());

        $baris = $guru->map(function ($g) use ($catatan, $absenKelas, $hariSekolah, $dari) {
            $c = $catatan->get($g->id, collect())->keyBy(fn ($x) => substr((string) $x->tanggal, 0, 10));
            $hitung = collect(GuruKehadiran::STATUS)->map(fn ($s, $k) => $c->where('status', $k)->count());
            $tanggalAbsenKelas = $g->kelasDiampu->flatMap(fn ($k) => $absenKelas->get($k->id, collect()))->unique();

            return (object) [
                'guru' => $g,
                'kelas' => $g->kelasDiampu->pluck('nama')->join(', '),
                'hari' => $c->get($dari->toDateString()),     // untuk rekap harian
                'hitung' => $hitung,
                'terlambat' => $c->filter->terlambat->count(),
                'kosong' => $hariSekolah->reject(fn ($d) => $c->has($d))->count(),
                'absenKelas' => $tanggalAbsenKelas->count(),
                'absenKelasHariIni' => $tanggalAbsenKelas->contains($dari->toDateString()),
                'keterangan' => $c->sortKeys()->filter(fn ($x) => $x->keterangan)->map(fn ($x, $d) => Carbon::parse($d)->translatedFormat('d M') . ': ' . $x->keterangan)->values(),
            ];
        });

        $judulPeriode = match ($mode) {
            'mingguan' => $dari->translatedFormat('d M') . ' – ' . $sampai->translatedFormat('d M Y'),
            'bulanan' => $dari->translatedFormat('F Y'),
            default => $dari->translatedFormat('l, d F Y'),
        };

        if ($request->query('unduh') === 'csv') return $this->csv($baris, $mode, $judulPeriode, $hariSekolah->count());

        return view('admin.rekap-guru.index', compact('mode', 'tanggal', 'dari', 'sampai', 'judulPeriode', 'baris', 'hariSekolah'));
    }

    /** Admin mencatat / mengubah kehadiran seorang guru pada satu tanggal (mis. guru lupa absen atau izin lewat telepon). */
    public function simpan(Request $request)
    {
        abort_unless(GuruKehadiran::aktif(), 404);
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')->where('role', User::GURU)],
            'tanggal' => 'required|date|before_or_equal:today',
            'status' => ['required', Rule::in(array_merge(array_keys(GuruKehadiran::STATUS), ['hapus']))],
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string|max:255',
        ]);
        $tgl = Carbon::parse($data['tanggal'])->toDateString();
        $nama = User::find($data['user_id'])->name;

        if ($data['status'] === 'hapus') {
            GuruKehadiran::where('user_id', $data['user_id'])->where('tanggal', $tgl)->delete();

            return back()->with('success', "Catatan kehadiran {$nama} tanggal " . Carbon::parse($tgl)->translatedFormat('d M Y') . ' dikosongkan.');
        }

        $hadir = $data['status'] === 'hadir';
        GuruKehadiran::updateOrCreate(['user_id' => $data['user_id'], 'tanggal' => $tgl], [
            'status' => $data['status'],
            'jam_masuk' => $hadir ? ($data['jam_masuk'] ?? null) : null,
            'jam_pulang' => $hadir ? ($data['jam_pulang'] ?? null) : null,
            'keterangan' => $data['keterangan'] ?? null,
            'dicatat_oleh' => $request->user()->id,
        ]);

        return back()->with('success', "Kehadiran {$nama} tersimpan.");
    }

    private function csv($baris, string $mode, string $judulPeriode, int $hariSekolah)
    {
        $nama = 'rekap-guru-' . $mode . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($baris, $mode, $judulPeriode, $hariSekolah) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            $tulis = fn (array $r) => fputcsv($f, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $r), ';');
            $tulis(['Rekap kehadiran guru', $judulPeriode, 'Hari sekolah: ' . $hariSekolah]);
            $tulis([]);
            if ($mode === 'harian') {
                $tulis(['No', 'Nama guru', 'Jabatan', 'Kelas', 'Status', 'Jam masuk', 'Jam pulang', 'Terlambat', 'Absensi kelas diisi', 'Keterangan']);
                foreach ($baris->values() as $i => $b) {
                    $h = $b->hari;
                    $tulis([$i + 1, $b->guru->name, $b->guru->jabatan, $b->kelas, $h ? GuruKehadiran::STATUS[$h->status][0] : 'Belum ada catatan',
                        $h->jam_masuk ?? '', $h->jam_pulang ?? '', $h && $h->terlambat ? 'Ya' : '', $b->kelas ? ($b->absenKelasHariIni ? 'Ya' : 'Belum') : '', $h->keterangan ?? '']);
                }
            } else {
                $tulis(array_merge(['No', 'Nama guru', 'Jabatan', 'Kelas'], array_map(fn ($s) => $s[0], array_values(GuruKehadiran::STATUS)), ['Belum ada catatan', 'Terlambat', 'Hari absensi kelas diisi', 'Keterangan']));
                foreach ($baris->values() as $i => $b) {
                    $tulis(array_merge([$i + 1, $b->guru->name, $b->guru->jabatan, $b->kelas], array_values($b->hitung->all()), [$b->kosong, $b->terlambat, $b->kelas ? $b->absenKelas : '', $b->keterangan->join(', ')]));
                }
            }
            fclose($f);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
