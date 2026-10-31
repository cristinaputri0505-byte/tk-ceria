@extends('layouts.dashboard')
@section('title', 'Chat')
@section('heading', $untukAdmin ? 'Chat: ' . $guru->name : 'Chat dengan Admin')
@section('actions')
    @if ($untukAdmin)
        <a href="{{ route('chat.guru') }}" class="text-sm font-bold text-sky px-2 py-2">← Semua guru</a>
    @endif
@endsection
@section('content')
<p class="text-sm text-slate-500 mb-3 max-w-3xl">
    @if ($untukAdmin)
        Percakapan pribadi dengan {{ $guru->name }}{{ $guru->jabatan ? ' (' . $guru->jabatan . ')' : '' }}. Semua admin sekolah dapat melihat dan membalas percakapan ini.
    @else
        Percakapan pribadi dengan admin sekolah. Hanya Anda dan admin yang dapat melihat pesan di sini, orang tua tidak.
    @endif
</p>
@include('chat._ruang', [
    'dataUrl' => route('chat.guru.data'),
    'kirimUrl' => route('chat.guru.kirim'),
    'ortuId' => $guru->id,
    'tampilNama' => $untukAdmin ? false : true,
    'kosong' => $untukAdmin ? 'Belum ada pesan. Tulis pesan pertama untuk ' . $guru->name . '.' : 'Belum ada pesan. Tulis pesan atau pertanyaan Anda untuk admin sekolah.',
])
@endsection
