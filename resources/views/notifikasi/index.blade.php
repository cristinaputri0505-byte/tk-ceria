@extends('layouts.dashboard')
@section('title', 'Notifikasi')
@section('heading', 'Notifikasi')
@section('actions')
<form method="POST" action="{{ route('notifikasi.baca-semua', ['kategori' => $kategori]) }}">@csrf
    <button class="text-xs sm:text-sm font-bold text-sky px-3 py-2 rounded-xl hover:bg-sky-soft inline-flex items-center gap-1.5"><i data-lucide="check-check" class="w-4 h-4"></i> <span class="hidden sm:inline">Tandai semua dibaca</span></button>
</form>
@endsection
@section('content')
@php
    $info = \App\Models\Notifikasi::KATEGORI;
    $gaya = [
        'kegiatan' => 'bg-amber-100 text-amber-700',
        'pembayaran' => 'bg-emerald-100 text-emerald-700',
        'pengumuman' => 'bg-rose-100 text-rose-600',
        'chat' => 'bg-sky-soft text-sky',
    ];
    $tab = fn ($aktif) => 'inline-flex items-center gap-1.5 text-sm font-bold px-4 py-2 rounded-full ' . ($aktif ? 'bg-navy text-white' : 'bg-white border border-slate-100 text-navy hover:bg-cloud');
@endphp

<nav class="flex flex-wrap gap-2 mb-5 max-w-3xl" aria-label="Kategori notifikasi">
    <a href="{{ route('notifikasi.index') }}" class="{{ $tab(! $kategori) }}">Semua
        @if ($belum->sum())<span class="text-[11px] bg-berry text-white rounded-full px-1.5">{{ $belum->sum() }}</span>@endif
    </a>
    @foreach ($info as $kode => [$nama, $ikon])
        <a href="{{ route('notifikasi.index', ['kategori' => $kode]) }}" class="{{ $tab($kategori === $kode) }}">
            <i data-lucide="{{ $ikon }}" class="w-4 h-4"></i> {{ $nama }}
            @if ($belum[$kode] ?? 0)<span class="text-[11px] bg-berry text-white rounded-full px-1.5">{{ $belum[$kode] }}</span>@endif
        </a>
    @endforeach
</nav>

<ul class="space-y-2 max-w-3xl">
    @forelse ($daftar as $n)
        @php [$nama, $ikon] = $info[$n->kategori] ?? ['Lainnya', 'bell']; @endphp
        <li>
            <a href="{{ route('notifikasi.buka', $n) }}" class="flex gap-3 rounded-2xl border p-4 bg-white hover:bg-cloud {{ $n->dibaca_at ? 'border-slate-100' : 'border-sky/40' }}">
                <span class="w-10 h-10 rounded-xl grid place-items-center shrink-0 {{ $gaya[$n->kategori] ?? 'bg-cloud text-navy' }}"><i data-lucide="{{ $ikon }}" class="w-5 h-5"></i></span>
                <span class="min-w-0 flex-1">
                    <span class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                        {{ $nama }} · {{ $n->created_at->diffForHumans() }}
                        @unless ($n->dibaca_at)<span class="w-2 h-2 rounded-full bg-sky ml-auto" aria-label="Belum dibaca"></span>@endunless
                    </span>
                    <span class="block font-extrabold text-navy">{{ $n->judul }}</span>
                    @if ($n->isi)<span class="block text-sm text-slate-600 line-clamp-2">{{ $n->isi }}</span>@endif
                </span>
            </a>
        </li>
    @empty
        <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada notifikasi.</li>
    @endforelse
</ul>
<div class="mt-5">{{ $daftar->links() }}</div>
@endsection
