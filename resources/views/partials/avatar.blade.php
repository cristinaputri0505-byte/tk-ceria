{{-- Foto profil pengguna, atau huruf awal nama bila belum ada / gagal dimuat.
     $u = User, $kelas = kelas ukuran & warna lingkaran, $tag = 'span' atau 'button', $atribut = atribut tambahan --}}
@php $tag = $tag ?? 'span'; @endphp
<{{ $tag }} {!! $atribut ?? '' !!} class="{{ $kelas }} relative overflow-hidden" title="{{ $u->name }}">
    @if ($u->foto_url)
        <img src="{{ $u->foto_url }}" alt="" class="absolute inset-0 w-full h-full object-cover" onerror="this.remove()">
    @endif
    {{ $u->inisial }}
</{{ $tag }}>
