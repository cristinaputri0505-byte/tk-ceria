@extends('layouts.dashboard')
@section('title', 'Chat Guru')
@section('heading', 'Chat Guru')
@section('actions')
    <a href="{{ route('chat.index') }}" class="inline-flex items-center gap-1.5 bg-white text-navy text-sm font-extrabold px-4 py-2 rounded-xl"><i data-lucide="message-circle" class="w-4 h-4"></i> <span class="hidden sm:inline">Chat Orang Tua</span></a>
@endsection
@section('content')
<p class="text-sm text-slate-500 mb-4 max-w-3xl">Pilih guru untuk membuka percakapan pribadi. Yang memiliki pesan baru tampil paling atas. Orang tua tidak dapat melihat percakapan ini.</p>
<ul class="space-y-2 max-w-3xl">
    @forelse ($guru as $g)
        @php $r = $ringkas->get($g->id); $m = $isiTerakhir->get($g->id); @endphp
        <li>
            <a href="{{ route('chat.guru', ['ortu' => $g->id]) }}" class="flex items-center gap-3 bg-white rounded-2xl border p-4 hover:bg-cloud">
                @include('partials.avatar', ['u' => $g, 'kelas' => 'w-11 h-11 rounded-full bg-jeruk text-navy border-2 border-navy font-display text-lg grid place-items-center shrink-0'])
                <span class="min-w-0 flex-1">
                    <span class="block font-extrabold text-navy truncate">{{ $g->name }}</span>
                    <span class="block text-sm text-slate-500 truncate">{{ $m ? ((int) $m->pengirim_id === (int) $g->id ? '' : 'Admin: ') . \Illuminate\Support\Str::limit($m->isi, 70) : ($g->jabatan ?: 'Belum ada percakapan') }}</span>
                </span>
                <span class="text-right shrink-0">
                    @if ($m)<span class="block text-[11px] text-slate-400">{{ $m->created_at->diffForHumans() }}</span>@endif
                    @if ($r->belum ?? 0)<span class="inline-block mt-1 text-[11px] font-extrabold bg-berry text-white rounded-full px-2 py-0.5">{{ $r->belum }}</span>@endif
                </span>
            </a>
        </li>
    @empty
        <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada akun guru.</li>
    @endforelse
</ul>
@endsection
