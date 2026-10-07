@extends('layouts.dashboard')
@section('title', 'Chat Orang Tua')
@section('heading', 'Chat Orang Tua')
@section('actions')
    <a href="{{ route('chat.grup') }}" class="inline-flex items-center gap-1.5 bg-sky text-white text-sm font-extrabold px-4 py-2 rounded-xl hover:bg-sky-dark"><i data-lucide="users-round" class="w-4 h-4"></i> <span class="hidden sm:inline">Grup Orang Tua</span></a>
@endsection
@section('content')
<p class="text-sm text-slate-500 mb-4 max-w-3xl">Pilih orang tua untuk membuka percakapan pribadi. Yang memiliki pesan baru tampil paling atas.</p>
<ul class="space-y-2 max-w-3xl">
    @forelse ($ortu as $o)
        @php $r = $ringkas->get($o->id); $m = $isiTerakhir->get($o->id); @endphp
        <li>
            <a href="{{ route('chat.pribadi', ['ortu' => $o->id]) }}" class="flex items-center gap-3 bg-white rounded-2xl border {{ ($r->belum ?? 0) ? 'border-sky/40' : 'border-slate-100' }} p-4 hover:bg-cloud">
                <span class="w-11 h-11 rounded-full bg-sky-soft text-sky font-display text-lg grid place-items-center shrink-0">{{ $o->inisial }}</span>
                <span class="min-w-0 flex-1">
                    <span class="block font-extrabold text-navy truncate">{{ $o->name }}</span>
                    <span class="block text-sm text-slate-500 truncate">{{ $m ? ($m->pengirim_id === $o->id ? '' : 'Anda: ') . \Illuminate\Support\Str::limit($m->isi, 70) : 'Belum ada percakapan' }}</span>
                </span>
                <span class="text-right shrink-0">
                    @if ($m)<span class="block text-[11px] text-slate-400">{{ $m->created_at->diffForHumans() }}</span>@endif
                    @if ($r->belum ?? 0)<span class="inline-block mt-1 text-[11px] font-extrabold bg-berry text-white rounded-full px-2 py-0.5">{{ $r->belum }}</span>@endif
                </span>
            </a>
        </li>
    @empty
        <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada akun orang tua.</li>
    @endforelse
</ul>
@endsection
