@extends('layouts.dashboard')
@section('title', 'Dokumentasi Kelas')
@section('heading', 'Dokumentasi Kelas')
@section('content')
@php $rp = auth()->user()->role === 'admin' ? 'admin.' : 'guru.'; $isAdmin = $rp === 'admin.'; @endphp
@if (! $kelas)
    <p class="text-slate-500">{{ $isAdmin ? 'Belum ada kelas. Buat kelas di menu Kelas.' : 'Anda belum menjadi wali kelas. Hubungi admin.' }}</p>
@else
<div class="grid lg:grid-cols-3 gap-5 items-start">
    <form method="POST" action="{{ route($rp.'dokumentasi.store') }}" enctype="multipart/form-data" class="bg-white rounded-3xl border border-slate-100 p-6 space-y-4 lg:sticky lg:top-24">
        @csrf
        <h2 class="font-display text-lg font-semibold text-navy">Bagikan foto ke orang tua</h2>
        <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
        @if ($kelasList->count() > 1 || $isAdmin)
            <div>
                <label class="block text-sm font-bold text-navy mb-1.5" for="pilihKelas">Kelas</label>
                <select id="pilihKelas" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm" onchange="location.href='?kelas_id='+this.value">
                    @foreach ($kelasList as $k)<option value="{{ $k->id }}" @selected($k->id === $kelas->id)>{{ $k->nama }}</option>@endforeach
                </select>
            </div>
        @endif
        <div>
            <label for="f_gambar" class="block text-sm font-bold text-navy mb-1.5">Foto (bisa pilih banyak)</label>
            <input id="f_gambar" type="file" name="gambar[]" accept="image/*" multiple required class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sky-soft file:px-4 file:py-2 file:font-bold file:text-sky">
            <p class="text-xs text-slate-500 mt-1">Maksimal 20 foto, masing-masing 5 MB.</p>
            @error('gambar')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            @error('gambar.*')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
        @include('partials.field', ['name' => 'keterangan', 'label' => 'Keterangan', 'required' => false, 'hint' => 'Contoh: Belajar menanam di kebun sekolah'])
        @include('partials.field', ['name' => 'tanggal', 'label' => 'Tanggal kegiatan', 'type' => 'date', 'value' => today()->format('Y-m-d')])
        @include('partials.select', ['name' => 'siswa_id', 'label' => 'Untuk', 'required' => false, 'placeholder' => 'Seluruh kelas (semua orang tua di kelas)', 'options' => $kelas->siswa->mapWithKeys(fn ($s) => [$s->id => 'Khusus '.$s->nama])->all()])
        <button class="w-full bg-sky text-white font-extrabold py-2.5 rounded-xl hover:bg-sky-dark">Bagikan foto</button>
    </form>

    <section class="lg:col-span-2">
        <h2 class="font-display text-lg font-semibold text-navy mb-3">Foto {{ $kelas->nama }}</h2>
        <ul class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @forelse ($foto as $d)
                <li class="bg-white rounded-2xl border border-slate-100 p-2">
                    <button type="button" data-foto="{{ $d->url }}" data-judul="{{ $d->keterangan }}" class="block w-full"><img src="{{ $d->url }}" alt="{{ $d->keterangan ?? 'Dokumentasi' }}" class="w-full aspect-square object-cover rounded-xl" loading="lazy"></button>
                    <div class="flex items-start justify-between gap-1 px-1 pt-2">
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-navy truncate">{{ $d->keterangan ?? 'Tanpa keterangan' }}</p>
                            <p class="text-[11px] text-slate-400">{{ $d->tanggal->translatedFormat('d M Y') }} · {{ $d->siswa ? 'Khusus '.$d->siswa->nama_panggilan : 'Seluruh kelas' }}</p>
                        </div>
                        @include('partials.hapus', ['action' => route($rp.'dokumentasi.destroy', $d), 'confirm' => 'Hapus foto ini? Orang tua tidak bisa melihatnya lagi.'])
                    </div>
                </li>
            @empty
                <li class="col-span-full bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada foto. Bagikan foto kegiatan agar orang tua bisa melihatnya.</li>
            @endforelse
        </ul>
        @if ($foto instanceof \Illuminate\Contracts\Pagination\Paginator)<div class="mt-5">{{ $foto->links() }}</div>@endif
    </section>
</div>
@include('partials.lightbox')
@endif
@endsection
