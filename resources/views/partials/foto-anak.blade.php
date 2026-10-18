{{-- $s = Siswa, $size = kelas ukuran. Bila foto gagal dimuat, otomatis diganti huruf awal nama. --}}
@php
    $kelasUkuran = $size ?? 'w-24 h-24';
    $kelasHuruf = $kelasUkuran . ' rounded-3xl grid place-items-center shrink-0 font-display ' . ($teks ?? 'text-4xl') . ' border-4 border-white shadow '
        . ($s->jenis_kelamin === 'P' ? 'bg-rose-100 text-rose-500' : 'bg-sky-100 text-sky-600');
@endphp
@if ($s->foto_url)
    <img src="{{ $s->foto_url }}" alt="Foto {{ $s->nama }}" loading="lazy" decoding="async"
        class="{{ $kelasUkuran }} rounded-3xl object-cover shrink-0 border-4 border-white shadow"
        onerror="this.style.display='none';this.nextElementSibling.style.display=''">
    <span class="{{ $kelasHuruf }}" style="display:none" aria-hidden="true">{{ mb_substr($s->nama, 0, 1) }}</span>
@else
    <span class="{{ $kelasHuruf }}">{{ mb_substr($s->nama, 0, 1) }}</span>
@endif
