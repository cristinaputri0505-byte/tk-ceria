@extends('errors.layout')
@section('kode', '419')
@section('ikon', 'timer-reset')
@section('judul', 'Sesi sudah berakhir')
@section('pesan', 'Halaman terlalu lama dibiarkan terbuka. Muat ulang halaman lalu coba lagi.')
@section('tombol')<a href="javascript:history.back()" class="inline-flex items-center gap-2 bg-cloud text-navy font-bold px-5 py-2.5 rounded-xl">Kembali</a>@endsection
