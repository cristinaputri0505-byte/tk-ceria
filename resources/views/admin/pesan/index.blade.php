@extends('layouts.dashboard')
@section('title', 'Pesan Masuk')
@section('heading', 'Pesan Masuk')
@section('content')
<p class="text-sm text-slate-500 mb-5">Pesan dari formulir di halaman Kontak.</p>
<ul class="space-y-3">
    @forelse ($pesan as $p)
        <li class="bg-white rounded-3xl border {{ $p->dibaca ? 'border-slate-100' : 'border-sky/40' }} p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <p class="font-extrabold text-navy flex items-center gap-2">
                        @unless ($p->dibaca)<span class="w-2 h-2 rounded-full bg-sky" aria-label="Belum dibaca"></span>@endunless
                        {{ $p->nama }}
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ $p->created_at->translatedFormat('d F Y, H.i') }}
                        @if ($p->telepon) · <a class="text-leaf font-bold" target="_blank" href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $p->telepon)) }}">{{ $p->telepon }}</a>@endif
                        @if ($p->email) · <a class="text-sky font-bold" href="mailto:{{ $p->email }}">{{ $p->email }}</a>@endif
                    </p>
                </div>
                <div class="flex items-center gap-1">
                    <form method="POST" action="{{ route('admin.pesan.update', $p) }}">@csrf @method('PATCH')
                        <button class="text-xs font-bold px-3 py-2 rounded-lg {{ $p->dibaca ? 'text-slate-500 hover:bg-cloud' : 'bg-sky-soft text-sky' }}">{{ $p->dibaca ? 'Tandai belum dibaca' : 'Tandai sudah dibaca' }}</button>
                    </form>
                    @include('partials.hapus', ['action' => route('admin.pesan.destroy', $p)])
                </div>
            </div>
            <p class="text-sm mt-3 whitespace-pre-line">{{ $p->pesan }}</p>
        </li>
    @empty
        <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada pesan masuk.</li>
    @endforelse
</ul>
<div class="mt-5">{{ $pesan->links() }}</div>
@endsection
