@extends('layouts.dashboard')
@section('title', 'Chat')
@section('heading', $untukAdmin ? 'Chat: ' . $ortu->name : 'Chat dengan Admin')
@section('actions')
    @if ($untukAdmin)
        <a href="{{ route('chat.index') }}" class="text-sm font-bold text-sky px-2 py-2">← Semua percakapan</a>
    @endif
@endsection
@section('content')
<p class="text-sm text-slate-500 mb-3 max-w-3xl">
    @if ($untukAdmin)
        Percakapan pribadi dengan {{ $ortu->name }}. Semua admin sekolah dapat melihat dan membalas percakapan ini.
    @else
        Percakapan pribadi dengan admin sekolah. Hanya Anda dan admin yang dapat melihat pesan di sini.
    @endif
</p>
@include('chat._ruang', [
    'dataUrl' => route('chat.pribadi.data'),
    'kirimUrl' => route('chat.pribadi.kirim'),
    'ortuId' => $ortu->id,
    'tampilNama' => false,
    'kosong' => $untukAdmin ? 'Belum ada pesan. Tulis pesan pertama untuk ' . $ortu->name . '.' : 'Belum ada pesan. Tulis pertanyaan Anda untuk admin sekolah.',
])
@endsection
