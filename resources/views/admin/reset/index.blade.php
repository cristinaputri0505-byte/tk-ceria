@extends('layouts.dashboard')
@section('title', 'Reset Password')
@section('heading', 'Reset Password')
@section('content')
@php
    $baru = session('password_baru');
    $wa = fn ($t) => $t ? 'https://wa.me/' . preg_replace('/^0/', '62', preg_replace('/\D/', '', $t)) : null;
    $status = [
        'menunggu' => ['Menunggu', 'bg-amber-100 text-amber-700'],
        'selesai' => ['Selesai', 'bg-emerald-100 text-emerald-700'],
        'ditolak' => ['Ditolak', 'bg-rose-100 text-rose-600'],
    ];
    if ($baru) {
        $pesanWa = "Halo {$baru['nama']}, password baru akun " . ($site['nama_sekolah'] ?? 'TK Ceria') . " Anda: {$baru['password']}\nSilakan masuk di " . route('login') . " lalu segera ganti password di menu Profil Saya.";
    }
@endphp

@if ($baru)
    <section class="bg-sun-soft border border-amber-100 rounded-3xl p-6 mb-6 max-w-3xl">
        <p class="font-extrabold text-navy flex items-center gap-2"><i data-lucide="key-round" class="w-5 h-5"></i> Password baru untuk {{ $baru['nama'] }}</p>
        <p class="text-sm text-slate-600 mt-1">{{ $baru['email'] }}. Catat atau kirim sekarang. Password ini <b>hanya ditampilkan sekali</b> dan tidak bisa dilihat lagi setelah halaman ini ditutup.</p>
        <div class="flex flex-wrap items-center gap-2 mt-4">
            <code id="pw-baru" class="font-mono text-2xl font-bold tracking-wider bg-white rounded-xl px-4 py-2 text-navy select-all">{{ $baru['password'] }}</code>
            <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('pw-baru').textContent); this.textContent='Tersalin'" class="bg-navy text-white text-sm font-extrabold px-4 py-2.5 rounded-xl">Salin</button>
            @if ($baru['telepon'])
                <a target="_blank" rel="noopener" href="{{ $wa($baru['telepon']) }}?text={{ rawurlencode($pesanWa) }}" class="bg-leaf text-white text-sm font-extrabold px-4 py-2.5 rounded-xl">Kirim lewat WhatsApp</a>
            @else
                <span class="text-xs text-slate-500">Akun ini belum punya nomor terdaftar. Sampaikan password lewat cara lain yang aman.</span>
            @endif
        </div>
    </section>
@endif

<p class="text-sm text-slate-500 mb-4 max-w-3xl">Permintaan dari tombol "Lupa password" di halaman masuk. Sebelum merilis password, pastikan peminta benar pemilik akun, misalnya dengan menghubungi <b>nomor yang terdaftar</b> di akun (bukan nomor yang diketik peminta).</p>

<ul class="space-y-3 max-w-3xl">
    @forelse ($permintaan as $p)
        @php $u = $p->user; [$label, $kelas] = $status[$p->status] ?? [$p->status, 'bg-cloud text-navy']; @endphp
        <li class="bg-white rounded-3xl border {{ $p->status === 'menunggu' ? 'border-sky/40' : 'border-slate-100' }} p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-extrabold text-navy">{{ $u->name }} <span class="text-xs font-bold bg-cloud text-navy rounded-full px-2 py-0.5 ml-1">{{ $u->roleLabel() }}</span></p>
                    <p class="text-sm text-slate-500">{{ $u->email }}</p>
                </div>
                <span class="text-xs font-extrabold rounded-full px-3 py-1 {{ $kelas }}">{{ $label }}</span>
            </div>

            <dl class="grid sm:grid-cols-2 gap-x-6 gap-y-2 text-sm mt-3">
                <div>
                    <dt class="text-xs font-bold text-slate-500">Nomor terdaftar di akun</dt>
                    <dd>@if ($u->telepon)<a class="text-leaf font-bold" target="_blank" rel="noopener" href="{{ $wa($u->telepon) }}">{{ $u->telepon }}</a>@else<span class="text-slate-500">Belum ada</span>@endif</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-500">Nomor yang diisi peminta</dt>
                    <dd>{{ $p->telepon_kontak ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-500">Diminta pada</dt>
                    <dd>{{ $p->created_at->timezone('Asia/Jakarta')->translatedFormat('d F Y, H.i') }} WIB</dd>
                </div>
                <div>
                    <dt class="text-xs font-bold text-slate-500">Catatan</dt>
                    <dd>{{ $p->catatan ?: '—' }}</dd>
                </div>
            </dl>

            @if ($p->status === 'menunggu')
                <div class="flex flex-wrap items-end gap-3 mt-4 pt-4 border-t border-slate-100">
                    <form method="POST" action="{{ route('admin.reset.rilis', $p) }}" class="flex flex-wrap items-end gap-2"
                          onsubmit="return confirm('Buat password baru? Password lama langsung tidak berlaku.')">@csrf
                        <label class="text-xs font-bold text-slate-500">Password sendiri (opsional)
                            <input name="password" type="text" minlength="8" maxlength="50" placeholder="Kosongkan = dibuat acak" autocomplete="off"
                                class="block mt-1 w-56 rounded-xl border border-slate-200 px-3 py-2 text-sm font-normal text-ink focus:outline-none focus:ring-2 focus:ring-sky/40">
                        </label>
                        <button class="bg-sky text-white text-sm font-extrabold px-4 py-2.5 rounded-xl hover:bg-sky-dark">Rilis password baru</button>
                    </form>
                    <form method="POST" action="{{ route('admin.reset.tolak', $p) }}" onsubmit="return confirm('Tolak permintaan ini?')">@csrf
                        <button class="text-sm font-bold text-berry px-3 py-2.5 rounded-xl hover:bg-rose-50">Tolak</button>
                    </form>
                </div>
                @error('password')<p class="text-sm text-berry mt-2">{{ $message }}</p>@enderror
            @elseif ($p->diproses_pada)
                <p class="text-xs text-slate-500 mt-3">Diproses oleh {{ $p->prosesor?->name ?? 'admin' }} pada {{ $p->diproses_pada->timezone('Asia/Jakarta')->translatedFormat('d F Y, H.i') }} WIB.</p>
            @endif
        </li>
    @empty
        <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada permintaan reset password.</li>
    @endforelse
</ul>
<div class="mt-5">{{ $permintaan->links() }}</div>
@endsection
