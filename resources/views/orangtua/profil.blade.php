@extends('layouts.dashboard')
@section('title', 'Profil Saya')
@section('heading', 'Profil Saya')
@section('content')
<div class="grid lg:grid-cols-3 gap-5">
    <form method="POST" action="{{ route('orangtua.profil.update') }}" enctype="multipart/form-data" class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5">
        @csrf @method('PUT')
        <div class="sm:col-span-2 flex items-center gap-4">
            @if ($user->foto)
                <img src="{{ asset('storage/'.$user->foto) }}" alt="" class="w-20 h-20 rounded-full object-cover">
            @else
                <span class="w-20 h-20 rounded-full grid place-items-center bg-sky-soft font-display text-3xl text-sky">{{ $user->inisial }}</span>
            @endif
            <div>
                <p class="font-display text-xl font-semibold text-navy">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }} · Orang tua</p>
            </div>
        </div>
        @include('partials.field', ['name' => 'name', 'label' => 'Nama lengkap', 'value' => $user->name])
        @include('partials.field', ['name' => 'telepon', 'label' => 'Nomor HP / WhatsApp', 'type' => 'tel', 'value' => $user->telepon, 'required' => false])
        @include('partials.file', ['name' => 'foto', 'label' => 'Foto profil', 'class' => 'sm:col-span-2'])
        <h2 class="sm:col-span-2 font-display text-lg font-semibold text-navy border-t border-slate-100 pt-5">Ganti kata sandi</h2>
        @include('partials.field', ['name' => 'current_password', 'label' => 'Kata sandi lama', 'type' => 'password', 'required' => false, 'class' => 'sm:col-span-2'])
        @include('partials.field', ['name' => 'password', 'label' => 'Kata sandi baru', 'type' => 'password', 'required' => false, 'hint' => 'Minimal 8 karakter. Kosongkan jika tidak ingin mengganti.'])
        @include('partials.field', ['name' => 'password_confirmation', 'label' => 'Ulangi kata sandi baru', 'type' => 'password', 'required' => false])
        <div class="sm:col-span-2"><button class="bg-sky text-white font-extrabold px-6 py-2.5 rounded-xl hover:bg-sky-dark">Simpan profil</button></div>
    </form>

    <aside class="bg-white rounded-3xl border border-slate-100 p-6">
        <h2 class="font-display text-lg font-semibold text-navy">Anak terdaftar</h2>
        <ul class="mt-4 space-y-3">
            @forelse ($anak as $a)
                <li>
                    <a href="{{ route('orangtua.anak', $a) }}" class="flex items-center gap-3 rounded-2xl p-2 hover:bg-cloud">
                        @include('partials.foto-anak', ['s' => $a, 'size' => 'w-12 h-12 !border-2', 'teks' => 'text-xl'])
                        <span><span class="block font-bold text-navy">{{ $a->nama }}</span><span class="block text-xs text-slate-500">{{ $a->kelas->nama ?? '—' }} · NIS {{ $a->nis }}</span></span>
                    </a>
                </li>
            @empty
                <li class="text-sm text-slate-500">Belum ada anak yang terhubung.</li>
            @endforelse
        </ul>
        <p class="text-xs text-slate-500 mt-4">Data anak dan data ayah/ibu dikelola oleh admin sekolah.</p>
    </aside>
</div>
@endsection
