@extends('layouts.dashboard')
@section('title', 'Pendaftaran')
@section('heading', 'Pendaftaran Murid Baru')
@section('content')
<nav class="inline-flex flex-wrap bg-white rounded-xl border border-slate-100 p-1 mb-5" aria-label="Filter status">
    @foreach (['' => 'Semua'] + \App\Models\Pendaftaran::STATUS as $k => $v)
        <a href="{{ route('admin.pendaftaran.index', $k ? ['status' => $k] : []) }}"
           class="px-4 py-2 rounded-lg text-sm font-bold {{ request('status', '') === $k ? 'bg-navy text-white' : 'text-navy hover:bg-cloud' }}">{{ $v }}</a>
    @endforeach
</nav>

<ul class="space-y-4">
    @forelse ($pendaftaran as $p)
        <li class="bg-white rounded-3xl border border-slate-100 p-5 grid lg:grid-cols-[1fr_auto] gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h2 class="font-extrabold text-navy text-lg">{{ $p->nama_anak }}</h2>
                    @include('partials.status-pendaftaran', ['status' => $p->status])
                </div>
                <p class="text-sm text-slate-500 mt-1">{{ $p->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} · lahir {{ $p->tanggal_lahir->translatedFormat('d F Y') }} ({{ $p->tanggal_lahir->age }} tahun) · {{ $p->program }}</p>
                <p class="text-sm mt-2"><span class="font-bold text-navy">{{ $p->nama_orang_tua }}</span> · <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $p->telepon)) }}" target="_blank" class="text-leaf font-bold">{{ $p->telepon }}</a>{{ $p->email ? ' · '.$p->email : '' }}</p>
                <p class="text-sm text-slate-500">{{ $p->alamat }}</p>
                <p class="text-xs text-slate-400 mt-2">Masuk {{ $p->created_at->diffForHumans() }}</p>
            </div>
            <form method="POST" action="{{ route('admin.pendaftaran.update', $p) }}" class="flex flex-wrap lg:flex-col gap-2 lg:w-56">
                @csrf @method('PATCH')
                <label class="sr-only" for="st{{ $p->id }}">Status</label>
                <select id="st{{ $p->id }}" name="status" class="rounded-xl border border-slate-200 px-3 py-2 text-sm">
                    @foreach (\App\Models\Pendaftaran::STATUS as $k => $v)<option value="{{ $k }}" @selected($p->status === $k)>{{ $v }}</option>@endforeach
                </select>
                <input name="catatan" value="{{ $p->catatan }}" placeholder="Catatan (opsional)" class="rounded-xl border border-slate-200 px-3 py-2 text-sm" aria-label="Catatan">
                <button class="bg-sky text-white text-sm font-bold px-4 py-2 rounded-xl">Simpan status</button>
            </form>
            <div class="lg:col-span-2 -mt-2 text-right">@include('partials.hapus', ['action' => route('admin.pendaftaran.destroy', $p), 'confirm' => "Hapus data pendaftaran {$p->nama_anak}?"])</div>
        </li>
    @empty
        <li class="text-slate-500">Belum ada pendaftaran dengan status ini.</li>
    @endforelse
</ul>
<div class="mt-5">{{ $pendaftaran->links() }}</div>
@endsection
