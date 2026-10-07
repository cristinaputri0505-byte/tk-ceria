@extends('layouts.dashboard')
@section('title', 'Grup Orang Tua')
@section('heading', 'Grup Orang Tua')
@section('content')
<p class="text-sm text-slate-500 mb-3 max-w-3xl">Semua orang tua dan admin sekolah dapat membaca pesan di grup ini. Jaga kesopanan, dan jangan membagikan data pribadi anak atau nomor rekening.</p>
@include('chat._ruang', [
    'dataUrl' => route('chat.grup.data'),
    'kirimUrl' => route('chat.grup.kirim'),
    'ortuId' => null,
    'tampilNama' => true,
    'kosong' => 'Belum ada pesan di grup. Sapa orang tua lainnya!',
])
@endsection
