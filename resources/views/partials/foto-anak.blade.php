{{-- $s = Siswa, $size = kelas ukuran --}}
@if ($s->foto_url)
    <img src="{{ $s->foto_url }}" alt="Foto {{ $s->nama }}" class="{{ $size ?? 'w-24 h-24' }} rounded-3xl object-cover shrink-0 border-4 border-white shadow">
@else
    <span class="{{ $size ?? 'w-24 h-24' }} rounded-3xl grid place-items-center shrink-0 font-display {{ $teks ?? 'text-4xl' }} border-4 border-white shadow {{ $s->jenis_kelamin === 'P' ? 'bg-rose-100 text-rose-500' : 'bg-sky-100 text-sky-600' }}">{{ mb_substr($s->nama, 0, 1) }}</span>
@endif
