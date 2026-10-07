@extends('layouts.dashboard')
@section('title', 'Pengguna')
@section('heading', 'Akun Guru & Orang Tua')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.pengguna.create', ['role' => $role]), 'label' => 'Tambah akun '.strtolower($roles[$role])]) @endsection
@section('content')
<div class="flex flex-wrap items-center gap-3 mb-5">
    <nav class="inline-flex bg-white rounded-xl border border-slate-100 p-1" aria-label="Jenis akun">
        @foreach ($roles as $k => $v)
            <a href="{{ route('admin.pengguna.index', ['role' => $k]) }}" @if($role === $k) aria-current="page" @endif
               class="px-4 py-2 rounded-lg text-sm font-bold {{ $role === $k ? 'bg-navy text-white' : 'text-navy hover:bg-cloud' }}">{{ $v }}</a>
        @endforeach
    </nav>
    <form class="flex-1 flex gap-2 min-w-[220px]" role="search">
        <input type="hidden" name="role" value="{{ $role }}">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama atau email" aria-label="Cari akun" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm">
    </form>
</div>

@include('partials.table-open')
    <thead class="bg-cloud text-left text-navy"><tr>
        <th class="px-5 py-3 font-extrabold">Nama</th><th class="px-5 py-3 font-extrabold">Email</th><th class="px-5 py-3 font-extrabold">Telepon</th>
        <th class="px-5 py-3 font-extrabold">{{ $role === 'orangtua' ? 'Anak terdaftar' : 'Jabatan' }}</th><th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
    </tr></thead>
    <tbody class="divide-y divide-slate-100">
    @forelse ($pengguna as $u)
        <tr>
            <td class="px-5 py-3 font-bold text-navy">{{ $u->name }}</td>
            <td class="px-5 py-3">{{ $u->email }}</td>
            <td class="px-5 py-3">{{ $u->telepon ?? '—' }}</td>
            <td class="px-5 py-3">{{ $role === 'orangtua' ? $u->anak_count.' anak' : ($u->jabatan ?? '—') }}</td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
                @if ($u->role !== 'admin')
                    <form method="POST" action="{{ route('admin.pengguna.masuk', $u) }}" class="inline">@csrf
                        <button class="inline-flex items-center gap-1 text-xs font-bold text-navy bg-sun-soft hover:bg-sun/40 px-3 py-1.5 rounded-lg" title="Lihat panel persis seperti yang dilihat {{ $u->name }}"><i data-lucide="eye" class="w-3.5 h-3.5"></i> Masuk sebagai</button>
                    </form>
                @endif
                @include('partials.edit-link', ['href' => route('admin.pengguna.edit', $u)])
                @include('partials.hapus', ['action' => route('admin.pengguna.destroy', $u), 'confirm' => "Hapus akun {$u->name}? Pemilik akun tidak bisa masuk lagi."])
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Belum ada akun {{ strtolower($roles[$role]) }}.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-5">{{ $pengguna->links() }}</div>
@endsection
