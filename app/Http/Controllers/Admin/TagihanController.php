<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Support\Pengaturan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $filter = fn ($q) => $q
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->jenis, fn ($q, $j) => $q->where('jenis', $j))
            ->when($request->periode, fn ($q, $p) => $q->where('periode', $p))
            ->when($request->kelas_id, fn ($q, $k) => $q->whereHas('siswa', fn ($s) => $s->where('kelas_id', $k)))
            ->when($request->q, fn ($q, $s) => $q->whereHas('siswa', fn ($w) => $w->whereLike('nama', "%$s%")->orWhereLike('nis', "%$s%")));

        $ringkas = $filter(Tagihan::query())->selectRaw('status, count(*) as n, sum(jumlah) as total')->groupBy('status')->get()->keyBy('status');

        return view('admin.tagihan.index', [
            'tagihan' => $filter(Tagihan::with('siswa.kelas'))
                ->orderByRaw("case status when 'menunggu' then 0 when 'belum' then 1 else 2 end")
                ->orderByDesc('bulan')->orderBy('siswa_id')->paginate(25)->withQueryString(),
            'ringkas' => $ringkas,
            'kelas' => Kelas::orderBy('nama')->get(),
            'periodeList' => Tagihan::select('periode')->distinct()->orderByDesc('periode')->pluck('periode'),
        ]);
    }

    public function create()
    {
        return view('admin.tagihan.create', [
            'kelas' => Kelas::withCount('siswa')->orderBy('nama')->get(),
            'siswa' => Siswa::with('kelas')->orderBy('nama')->get(),
            'nominal' => (int) preg_replace('/\D/', '', (string) (Pengaturan::semua()['spp_nominal'] ?? 0)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jenis' => ['required', Rule::in(Tagihan::JENIS)],
            'bulan' => 'required|date_format:Y-m',
            'keterangan' => 'nullable|string|max:30',
            'jumlah' => 'required|integer|min:1000|max:100000000',
            'jatuh_tempo' => 'nullable|date',
            'target' => 'required|in:semua,kelas,siswa',
            'kelas_id' => 'required_if:target,kelas|nullable|exists:kelas,id',
            'siswa_id' => 'required_if:target,siswa|nullable|exists:siswa,id',
        ], [
            'kelas_id.required_if' => 'Pilih kelas yang akan ditagih.',
            'siswa_id.required_if' => 'Pilih siswa yang akan ditagih.',
        ]);

        $bulan = Carbon::createFromFormat('Y-m-d', $data['bulan'] . '-01');
        $periode = $data['keterangan'] ?: $bulan->translatedFormat('F Y');

        $siswa = match ($data['target']) {
            'kelas' => Siswa::where('kelas_id', $data['kelas_id'])->get(),
            'siswa' => Siswa::whereKey($data['siswa_id'])->get(),
            default => Siswa::whereNotNull('kelas_id')->get(),
        };

        $dibuat = 0;
        foreach ($siswa as $s) {
            $t = Tagihan::firstOrCreate(
                ['siswa_id' => $s->id, 'jenis' => $data['jenis'], 'periode' => $periode],
                ['bulan' => $bulan->toDateString(), 'jumlah' => $data['jumlah'], 'jatuh_tempo' => $data['jatuh_tempo'], 'status' => 'belum']
            );
            if ($t->wasRecentlyCreated) $dibuat++;
        }
        $lewat = $siswa->count() - $dibuat;

        return redirect()->route('admin.tagihan.index', ['periode' => $periode])
            ->with('success', "{$dibuat} tagihan {$data['jenis']} {$periode} dibuat." . ($lewat ? " {$lewat} siswa dilewati karena tagihannya sudah ada." : ''));
    }

    public function update(Request $request, Tagihan $tagihan)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Tagihan::STATUS))],
            'metode' => ['nullable', Rule::in(array_keys(Tagihan::METODE))],
            'tanggal_bayar' => 'nullable|date|before_or_equal:today',
            'catatan' => 'nullable|string|max:255',
        ]);

        if ($data['status'] === 'lunas') {
            $metode = $data['metode'] ?? ($tagihan->bukti ? 'transfer' : 'tunai');
            $tagihan->lunasi($metode, $request->user(), $data['tanggal_bayar'] ?? null);
            if (array_key_exists('catatan', $data)) $tagihan->update(['catatan' => $data['catatan']]);
            $pesan = "Pembayaran {$tagihan->siswa->nama} ({$tagihan->periode}) dicatat lunas via " . strtolower(Tagihan::METODE[$metode]) . ". No. kuitansi {$tagihan->no_kuitansi}.";
        } else {
            $tagihan->update([
                'status' => $data['status'], 'metode' => null, 'no_kuitansi' => null, 'diterima_oleh' => null, 'dibayar_pada' => null,
                'catatan' => $data['catatan'] ?? $tagihan->catatan,
            ]);
            $pesan = "Tagihan {$tagihan->siswa->nama} ({$tagihan->periode}) ditandai " . strtolower(Tagihan::STATUS[$data['status']]) . '.';
        }

        return back()->with('success', $pesan);
    }

    public function destroy(Tagihan $tagihan)
    {
        if ($tagihan->bukti) Storage::disk('local')->delete($tagihan->bukti);
        $tagihan->delete();
        return back()->with('success', 'Tagihan dihapus.');
    }
}
