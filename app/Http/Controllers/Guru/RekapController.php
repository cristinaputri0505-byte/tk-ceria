<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Dokumentasi;
use App\Models\ElearningHarian;
use App\Models\ElearningHasil;
use App\Models\ElearningTugas;
use App\Models\Kelas;
use App\Models\Perkembangan;
use App\Support\Basisdata;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Rekap siswa per kelas untuk laporan harian / mingguan / bulanan guru:
 * kehadiran, nilai Ruang Belajar (e-learning), dan perkembangan (rapor) tiap anak.
 * Bisa dicetak dan diunduh sebagai file Excel (CSV). Dipakai panel Guru (kelasnya) dan Admin (semua kelas).
 */
class RekapController extends Controller
{
    public const MODE = ['harian' => 'Harian', 'mingguan' => 'Mingguan', 'bulanan' => 'Bulanan'];

    public function index(Request $request)
    {
        $kelasList = $request->user()->role === 'admin'
            ? Kelas::with('wali')->orderBy('nama')->get()
            : $request->user()->kelasDiampu()->with('wali')->orderBy('nama')->get();
        $kelas = $kelasList->firstWhere('id', (int) $request->kelas_id) ?? $kelasList->first();

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
        $periode = PerkembanganController::periodeAktif();

        $baris = collect();
        $dokumentasi = collect();
        if ($kelas) {
            $siswa = $kelas->siswa()->orderBy('nama')->get();
            $ids = $siswa->pluck('id');

            $absen = Absensi::whereIn('siswa_id', $ids)->whereDate('tanggal', '>=', $dari)->whereDate('tanggal', '<=', $sampai)->get()->groupBy('siswa_id');
            $rapor = Perkembangan::whereIn('siswa_id', $ids)->where('periode', $periode)->get()->groupBy('siswa_id');

            $tugas = collect();
            $harian = collect();
            if (ElearningTugas::aktifDiDatabase()) {
                $tugas = ElearningHasil::whereIn('siswa_id', $ids)->whereNotNull('selesai_pada')
                    ->whereBetween('selesai_pada', [$dari->copy()->startOfDay(), $sampai->copy()->endOfDay()])->get()->groupBy('siswa_id');
            }
            if (Basisdata::ada('elearning_harian')) {
                $harian = ElearningHarian::whereIn('siswa_id', $ids)
                    ->where('tanggal', '>=', $dari->toDateString())->where('tanggal', '<=', $sampai->toDateString())->get()->groupBy('siswa_id');
            }

            $baris = $siswa->map(function ($s) use ($absen, $rapor, $tugas, $harian) {
                $a = $absen->get($s->id, collect());
                $hitung = collect(Absensi::STATUS)->map(fn ($l, $k) => $a->where('status', $k)->count());
                $tercatat = $hitung->sum();
                $t = $tugas->get($s->id, collect());
                $h = $harian->get($s->id, collect());

                return (object) [
                    'siswa' => $s,
                    'absen' => $hitung,
                    'persen' => $tercatat ? (int) round($hitung['hadir'] / $tercatat * 100) : null,
                    'keterangan' => $a->sortBy('tanggal')->filter(fn ($x) => $x->status !== 'hadir' || $x->keterangan)
                        ->map(fn ($x) => trim(Absensi::STATUS[$x->status] . ($x->keterangan ? ': ' . $x->keterangan : '')))->values(),
                    'catatan' => $a->pluck('keterangan')->filter()->values(),
                    'tugas' => $t->count() + $h->count(),
                    'bintang' => (int) $t->sum('bintang') + (int) $h->sum('bintang'),
                    'rapor' => $rapor->get($s->id, collect())->keyBy('aspek'),
                ];
            });

            $dokumentasi = Dokumentasi::where('kelas_id', $kelas->id)->whereDate('tanggal', '>=', $dari)->whereDate('tanggal', '<=', $sampai)
                ->orderBy('tanggal')->get();
        }

        $judulPeriode = match ($mode) {
            'mingguan' => $dari->translatedFormat('d M') . ' – ' . $sampai->translatedFormat('d M Y'),
            'bulanan' => $dari->translatedFormat('F Y'),
            default => $dari->translatedFormat('l, d F Y'),
        };

        if ($request->query('unduh') === 'csv' && $kelas) {
            return $this->csv($kelas, $baris, $mode, $judulPeriode, $periode);
        }

        return view('guru.rekap', [
            'kelasList' => $kelasList, 'kelas' => $kelas, 'mode' => $mode, 'tanggal' => $tanggal,
            'dari' => $dari, 'sampai' => $sampai, 'judulPeriode' => $judulPeriode, 'periode' => $periode,
            'baris' => $baris, 'dokumentasi' => $dokumentasi,
            'rp' => $request->user()->role === 'admin' ? 'admin.' : 'guru.',
        ]);
    }

    /** File CSV yang langsung terbuka rapi di Excel (pemisah titik koma, UTF-8 BOM). */
    private function csv(Kelas $kelas, $baris, string $mode, string $judulPeriode, string $periode)
    {
        $nama = 'rekap-' . $mode . '-' . \Illuminate\Support\Str::slug($kelas->nama) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($kelas, $baris, $judulPeriode, $periode) {
            $f = fopen('php://output', 'w');
            fwrite($f, "\xEF\xBB\xBF");
            // Teks yang diawali = + - @ diberi tanda kutip agar tidak dijalankan sebagai rumus oleh Excel
            $tulis = fn (array $r) => fputcsv($f, array_map(fn ($v) => is_string($v) && preg_match('/^[=+\-@]/', $v) ? "'" . $v : $v, $r), ';');
            $tulis(['Rekap siswa ' . $kelas->nama, $judulPeriode]);
            $tulis([]);
            $tulis(array_merge(['No', 'Nama', 'NIS', 'Hadir', 'Izin', 'Sakit', 'Alpa', '% Hadir', 'Keterangan', 'Tugas e-learning selesai', 'Bintang'],
                array_map(fn ($l) => $l . ' (' . $periode . ')', array_values(Perkembangan::ASPEK_MUDAH))));
            foreach ($baris->values() as $i => $b) {
                $tulis(array_merge([
                    $i + 1, $b->siswa->nama, $b->siswa->nis,
                    $b->absen['hadir'], $b->absen['izin'], $b->absen['sakit'], $b->absen['alpa'],
                    $b->persen === null ? '' : $b->persen . '%',
                    $b->keterangan->join(', '), $b->tugas, $b->bintang,
                ], array_map(function ($k) use ($b) {
                    $n = optional($b->rapor->get($k))->nilai;

                    return $n ? Perkembangan::NILAI_MUDAH[$n] . ' (' . $n . ')' : '';
                }, array_keys(Perkembangan::ASPEK))));
            }
            fclose($f);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
