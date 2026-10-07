<?php

namespace App\Http\Controllers\OrangTua;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Guru\PerkembanganController;
use App\Models\Dokumentasi;
use App\Models\Kegiatan;
use App\Models\Pengumuman;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Support\Pengaturan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class DashboardController extends Controller
{
    private function anakSaya(Request $request)
    {
        return $request->user()->anak()->with('kelas.wali')->orderBy('nama')->get();
    }

    private function pastikanAnak(Request $request, Siswa $siswa): void
    {
        abort_unless((int) $siswa->orang_tua_id === $request->user()->id, 403);
    }

    /** Foto dokumentasi yang boleh dilihat untuk sekumpulan anak. */
    private function dokumentasiUntuk($anak)
    {
        return Dokumentasi::where(function ($q) use ($anak) {
            foreach ($anak as $a) {
                $q->orWhere(fn ($w) => $w->where('kelas_id', $a->kelas_id)->where(fn ($x) => $x->whereNull('siswa_id')->orWhere('siswa_id', $a->id)));
            }
        });
    }

    public function index(Request $request)
    {
        $anak = $this->anakSaya($request);
        $periode = PerkembanganController::periodeAktif();

        foreach ($anak as $a) {
            $a->rekap = $a->absensi()->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
            $total = $a->rekap->sum();
            $a->persenHadir = $total ? round(($a->rekap['hadir'] ?? 0) / $total * 100) : null;
            $a->sppBulanIni = $a->tagihan()->where('jenis', 'SPP')->whereDate('bulan', now()->startOfMonth()->toDateString())->first();
            $a->rapor = $a->perkembangan()->where('periode', $periode)->pluck('nilai')->countBy();
        }

        $siswaIds = $anak->pluck('id');
        $kelasIds = $anak->pluck('kelas_id');

        $agenda = Kegiatan::whereDate('tanggal', '>=', today())->orderBy('tanggal')->take(5)->get()
            ->map(fn ($k) => ['tanggal' => $k->tanggal, 'judul' => $k->judul, 'jenis' => 'Kegiatan sekolah', 'url' => route('kegiatan.show', $k)])
            ->concat(Pengumuman::untukKelas($kelasIds)->whereDate('tanggal_acara', '>=', today())->get()
                ->map(fn ($p) => ['tanggal' => $p->tanggal_acara, 'judul' => $p->judul, 'jenis' => $p->kelas ? 'Kelas '.$p->kelas->nama : 'Pengumuman', 'url' => route('orangtua.pengumuman')]))
            ->sortBy('tanggal')->take(5)->values();

        return view('orangtua.dashboard', [
            'anak' => $anak,
            'periode' => $periode,
            'tagihanBelum' => Tagihan::with('siswa')->whereIn('siswa_id', $siswaIds)->where('status', '!=', 'lunas')->orderBy('bulan')->get(),
            'pengumuman' => Pengumuman::with('kelas')->untukKelas($kelasIds)->latest()->take(4)->get(),
            'agenda' => $agenda,
            'dokumentasi' => $anak->isEmpty() ? collect() : $this->dokumentasiUntuk($anak)->latest('tanggal')->latest('id')->take(8)->get(),
        ]);
    }

    public function anak(Request $request, Siswa $siswa)
    {
        $this->pastikanAnak($request, $siswa);
        $siswa->load('kelas.wali', 'orangTua');

        $bulan = preg_match('/^\d{4}-\d{2}$/', (string) $request->bulan) ? $request->bulan : now()->format('Y-m');
        $awal = Carbon::createFromFormat('Y-m-d', $bulan . '-01')->startOfDay();

        $absensi = $siswa->absensi()->whereYear('tanggal', $awal->year)->whereMonth('tanggal', $awal->month)->get()
            ->keyBy(fn ($a) => $a->tanggal->format('Y-m-d'));

        $periodeList = $siswa->perkembangan()->select('periode')->distinct()->orderByDesc('periode')->pluck('periode');
        $periode = $request->get('periode', $periodeList->first() ?? PerkembanganController::periodeAktif());

        return view('orangtua.anak', [
            'siswa' => $siswa,
            'semuaAnak' => $this->anakSaya($request),
            'tab' => $request->get('tab', 'profil'),
            'bulan' => $bulan,
            'awal' => $awal,
            'absensi' => $absensi,
            'periodeList' => $periodeList,
            'periode' => $periode,
            'perkembangan' => $siswa->perkembangan()->with('guru')->where('periode', $periode)->get()->keyBy('aspek'),
            'tagihan' => $siswa->tagihan()->orderByDesc('bulan')->orderByDesc('id')->get(),
            'dokumentasi' => Dokumentasi::untukSiswa($siswa)->latest('tanggal')->latest('id')->take(30)->get(),
        ]);
    }

    public function pembayaran(Request $request)
    {
        abort_unless(Pengaturan::fitur('pembayaran'), 404);
        $anak = $this->anakSaya($request);
        $tagihan = Tagihan::with('siswa')->whereIn('siswa_id', $anak->pluck('id'))
            ->orderByRaw("case status when 'belum' then 0 when 'menunggu' then 1 else 2 end")->orderByDesc('bulan')->orderByDesc('id')->get();

        return view('orangtua.pembayaran', compact('anak', 'tagihan'));
    }

    public function bayar(Request $request, Tagihan $tagihan)
    {
        abort_unless(Pengaturan::fitur('pembayaran'), 404);
        $this->pastikanAnak($request, $tagihan->siswa);
        abort_if($tagihan->status === 'lunas', 422, 'Tagihan ini sudah lunas.');

        $request->validate([
            'bukti' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:4096',
            'catatan' => 'nullable|string|max:255',
        ], [], ['bukti' => 'bukti transfer']);

        if ($tagihan->bukti) Storage::disk('local')->delete($tagihan->bukti);

        $tagihan->update([
            'bukti' => $request->file('bukti')->store('bukti-bayar', 'local'),
            'status' => 'menunggu',
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', "Bukti pembayaran {$tagihan->jenis} {$tagihan->periode} terkirim. Admin akan memverifikasi dalam 1×24 jam kerja.");
    }

    public function pengumuman(Request $request)
    {
        abort_unless(Pengaturan::fitur('pengumuman'), 404);
        $kelasIds = $this->anakSaya($request)->pluck('kelas_id');

        return view('orangtua.pengumuman', [
            'pengumuman' => Pengumuman::with('kelas')->untukKelas($kelasIds)->latest()->paginate(10),
        ]);
    }

    public function dokumentasi(Request $request)
    {
        abort_unless(Pengaturan::fitur('dokumentasi'), 404);
        $anak = $this->anakSaya($request);
        $pilih = $anak->firstWhere('id', (int) $request->anak);
        $sumber = $pilih ? collect([$pilih]) : $anak;

        return view('orangtua.dokumentasi', [
            'anak' => $anak,
            'pilih' => $pilih,
            'foto' => $sumber->isEmpty() ? collect() : $this->dokumentasiUntuk($sumber)->with('siswa')->latest('tanggal')->latest('id')->paginate(24)->withQueryString(),
        ]);
    }

    public function profil(Request $request)
    {
        return view('orangtua.profil', ['user' => $request->user(), 'anak' => $this->anakSaya($request)]);
    }

    public function profilUpdate(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'telepon' => 'nullable|string|max:20',
            'foto' => 'nullable|image|max:2048',
            'current_password' => 'nullable|required_with:password|current_password',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [], ['current_password' => 'kata sandi lama', 'password' => 'kata sandi baru']);

        $user->name = $data['name'];
        $user->telepon = $data['telepon'] ?? null;
        if ($request->hasFile('foto')) {
            if ($user->foto) Storage::disk('public')->delete($user->foto);
            $user->foto = $request->file('foto')->store('pengguna', 'public');
        }
        if (! empty($data['password'])) $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Profil Anda diperbarui.');
    }
}
