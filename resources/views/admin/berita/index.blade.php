@extends('layouts.dashboard')
@section('title', 'Berita')
@section('heading', 'Berita & Pengumuman')
@section('actions') @include('partials.btn-tambah', ['href' => route('admin.berita.create'), 'label' => 'Tulis berita']) @endsection
@section('content')
@include('partials.table-open')
    <thead class="bg-cloud text-left text-navy"><tr>
        <th class="px-5 py-3 font-extrabold">Judul</th><th class="px-5 py-3 font-extrabold">Status</th><th class="px-5 py-3"><span class="sr-only">Aksi</span></th>
    </tr></thead>
    <tbody class="divide-y divide-slate-100">
    @forelse ($berita as $b)
        <tr>
            <td class="px-5 py-3"><p class="font-bold text-navy">{{ $b->judul }}</p><p class="text-xs text-slate-500">{{ $b->ringkasan }}</p></td>
            <td class="px-5 py-3 whitespace-nowrap">
                @if ($b->published_at)
                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700">Terbit {{ $b->published_at->translatedFormat('d M Y') }}</span>
                @else
                    <span class="text-xs font-extrabold px-2.5 py-1 rounded-full bg-slate-100 text-slate-600">Draf</span>
                @endif
            </td>
            <td class="px-5 py-3 text-right whitespace-nowrap">
                @if ($b->published_at)<a href="{{ route('berita.show', $b) }}" target="_blank" class="p-2 rounded-lg text-slate-500 hover:bg-cloud inline-flex" aria-label="Lihat di website"><i data-lucide="external-link" class="w-4 h-4"></i></a>@endif
                @include('partials.edit-link', ['href' => route('admin.berita.edit', $b)])
                @include('partials.hapus', ['action' => route('admin.berita.destroy', $b)])
            </td>
        </tr>
    @empty
        <tr><td colspan="3" class="px-5 py-10 text-center text-slate-500">Belum ada berita. Tulis pengumuman pertama untuk orang tua dan pengunjung.</td></tr>
    @endforelse
    </tbody>
</table></div></div>
<div class="mt-5">{{ $berita->links() }}</div>
@endsection
