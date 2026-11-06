{{-- Ilustrasi beranda: anak-anak berangkat ke sekolah (bergerak; diam bila pengguna memilih "kurangi gerakan").
     Tampil bila foto utama belum diunggah di Pengaturan Halaman. --}}
@php $g = 'stroke="#2b2140" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"'; @endphp
<svg viewBox="0 0 600 450" class="w-full h-full" preserveAspectRatio="xMidYMax slice" role="img" aria-label="Ilustrasi anak-anak berangkat ke sekolah">
    {{-- awan & burung --}}
    <g class="ilu-awan">
        <path d="M70 92c4-16 26-20 36-8 10-12 34-6 34 10 14 0 18 18 6 22H66c-14-2-12-22 4-24z" fill="#fff" {!! $g !!}/>
    </g>
    <g class="ilu-awan ilu-awan-2">
        <path d="M250 58c3-12 20-15 28-6 8-9 26-4 26 8 11 0 14 14 5 17h-56c-11-2-9-17-3-19z" fill="#fff" {!! $g !!}/>
    </g>
    <g transform="translate(330 110)"><path class="ilu-burung" d="M0 0q7-8 14 0q7-8 14 0" fill="none" {!! $g !!}/></g>
    <g transform="translate(370 92)"><path class="ilu-burung ilu-burung-2" d="M0 0q6-7 12 0q6-7 12 0" fill="none" {!! $g !!}/></g>

    {{-- tiang & bendera merah putih --}}
    <line x1="566" y1="118" x2="566" y2="326" {!! $g !!}/>
    <circle cx="566" cy="116" r="4" fill="#ffc53d" {!! $g !!}/>
    <g class="ilu-bendera">
        <rect x="568" y="122" width="30" height="11" fill="#e5484d" stroke="#2b2140" stroke-width="2.5"/>
        <rect x="568" y="133" width="30" height="11" fill="#fff" stroke="#2b2140" stroke-width="2.5"/>
    </g>

    {{-- gedung sekolah --}}
    <g transform="translate(-26 0)">
    <rect x="392" y="200" width="168" height="122" rx="6" fill="#fff1c7" {!! $g !!}/>
    <path d="M378 206L476 140L574 206Z" fill="#ff8fb1" {!! $g !!}/>
    <circle cx="476" cy="180" r="12" fill="#6ec3ff" {!! $g !!}/>
    <rect x="434" y="208" width="84" height="20" rx="5" fill="#fff" stroke="#2b2140" stroke-width="2.5"/>
    <text x="476" y="223" text-anchor="middle" font-family="'Baloo 2', Fredoka, sans-serif" font-weight="800" font-size="14" fill="#2b2140">SEKOLAH</text>
    <rect x="404" y="238" width="34" height="30" rx="4" fill="#cde8ff" {!! $g !!}/><path d="M421 238v30M404 253h34" stroke="#2b2140" stroke-width="2"/>
    <rect x="514" y="238" width="34" height="30" rx="4" fill="#cde8ff" {!! $g !!}/><path d="M531 238v30M514 253h34" stroke="#2b2140" stroke-width="2"/>
    <path d="M456 322v-46a20 20 0 0 1 40 0v46z" fill="#ff9a5c" {!! $g !!}/>
    <circle cx="488" cy="300" r="2.5" fill="#2b2140"/>
    </g>

    {{-- tanah, jalan setapak, bunga --}}
    <path d="M0 336C120 300 260 306 380 322S560 340 600 330V450H0Z" fill="#3dbe72" {!! $g !!}/>
    <path d="M450 322C446 360 380 380 300 400S120 430 70 450H170C220 432 330 414 400 392S474 350 470 322Z" fill="#ffe7a3" stroke="#2b2140" stroke-width="2.5" stroke-linejoin="round"/>
    @foreach ([[40, 370, '#ff8fb1'], [560, 380, '#ffc53d'], [250, 432, '#b9a4ff']] as [$bx, $by, $bw])
        <g class="ilu-bunga" transform="translate({{ $bx }} {{ $by }})">
            <path d="M0 0v18" stroke="#2b2140" stroke-width="2.5"/>
            <circle cx="0" cy="-4" r="7" fill="{{ $bw }}" stroke="#2b2140" stroke-width="2.5"/>
            <circle cx="0" cy="-4" r="2.5" fill="#fff1c7"/>
        </g>
    @endforeach

    {{-- Anak 1: laki-laki, tas biru, melambaikan tangan --}}
    <g transform="translate(118 392)">
        <g class="ilu-anak">
            <rect x="-24" y="-74" width="14" height="34" rx="5" fill="#6ec3ff" {!! $g !!}/>
            <rect x="-11" y="-30" width="9" height="30" rx="4" fill="#2b2140"/><rect x="3" y="-30" width="9" height="30" rx="4" fill="#2b2140"/>
            <ellipse cx="-7" cy="2" rx="9" ry="5" fill="#ff9a5c" stroke="#2b2140" stroke-width="2.5"/><ellipse cx="8" cy="2" rx="9" ry="5" fill="#ff9a5c" stroke="#2b2140" stroke-width="2.5"/>
            <rect x="-18" y="-76" width="36" height="50" rx="13" fill="#fff" {!! $g !!}/>
            <path d="M-18-50h36" stroke="#e5484d" stroke-width="4"/>
            <path d="M-16-66l-10 24" stroke="#2b2140" stroke-width="12" stroke-linecap="round"/><path d="M-16-66l-10 24" stroke="#fcd9b6" stroke-width="8" stroke-linecap="round"/>
            <g class="ilu-lambai"><path d="M16-66l14-26" stroke="#2b2140" stroke-width="12" stroke-linecap="round"/><path d="M16-66l14-26" stroke="#fcd9b6" stroke-width="8" stroke-linecap="round"/></g>
            <circle cx="0" cy="-98" r="21" fill="#fcd9b6" {!! $g !!}/>
            <path d="M-21-100a21 21 0 0 1 42 0c-8-10-30-14-42 0z" fill="#4a3020" stroke="#2b2140" stroke-width="2.5"/>
            <circle cx="-7" cy="-97" r="2.6" fill="#2b2140"/><circle cx="7" cy="-97" r="2.6" fill="#2b2140"/>
            <path d="M-7-88q7 6 14 0" fill="none" stroke="#2b2140" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="-13" cy="-90" r="3" fill="#ff8fb1" opacity=".7"/><circle cx="13" cy="-90" r="3" fill="#ff8fb1" opacity=".7"/>
        </g>
    </g>

    {{-- Anak 2: perempuan berkerudung, membawa buku --}}
    <g transform="translate(206 400)">
        <g class="ilu-anak ilu-anak-2">
            <rect x="-24" y="-72" width="14" height="32" rx="5" fill="#ffc53d" {!! $g !!}/>
            <rect x="-10" y="-26" width="8" height="26" rx="4" fill="#2b2140"/><rect x="3" y="-26" width="8" height="26" rx="4" fill="#2b2140"/>
            <ellipse cx="-6" cy="2" rx="9" ry="5" fill="#3dbe72" stroke="#2b2140" stroke-width="2.5"/><ellipse cx="8" cy="2" rx="9" ry="5" fill="#3dbe72" stroke="#2b2140" stroke-width="2.5"/>
            <path d="M-20-74h40l6 50h-52z" fill="#ff8fb1" {!! $g !!}/>
            <rect x="8" y="-60" width="18" height="22" rx="2" fill="#6ec3ff" {!! $g !!}/><path d="M17-60v22" stroke="#2b2140" stroke-width="2"/>
            {{-- kerudung --}}
            <path d="M-26-96a26 26 0 0 1 52 0v14c0 10-12 16-26 16s-26-6-26-16z" fill="#b9a4ff" {!! $g !!}/>
            <circle cx="0" cy="-96" r="15" fill="#fcd9b6" stroke="#2b2140" stroke-width="2.5"/>
            <circle cx="-5.5" cy="-97" r="2.4" fill="#2b2140"/><circle cx="5.5" cy="-97" r="2.4" fill="#2b2140"/>
            <path d="M-5-90q5 5 10 0" fill="none" stroke="#2b2140" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="-10" cy="-92" r="2.6" fill="#ff8fb1" opacity=".7"/><circle cx="10" cy="-92" r="2.6" fill="#ff8fb1" opacity=".7"/>
        </g>
    </g>

    {{-- Anak 3: laki-laki berpeci, memantulkan bola --}}
    <g transform="translate(292 410)">
        <g class="ilu-anak ilu-anak-3">
            <rect x="-24" y="-74" width="14" height="34" rx="5" fill="#ff9a5c" {!! $g !!}/>
            <rect x="-11" y="-30" width="9" height="30" rx="4" fill="#2b2140"/><rect x="3" y="-30" width="9" height="30" rx="4" fill="#2b2140"/>
            <ellipse cx="-7" cy="2" rx="9" ry="5" fill="#6ec3ff" stroke="#2b2140" stroke-width="2.5"/><ellipse cx="8" cy="2" rx="9" ry="5" fill="#6ec3ff" stroke="#2b2140" stroke-width="2.5"/>
            <rect x="-18" y="-76" width="36" height="50" rx="13" fill="#3dbe72" {!! $g !!}/>
            <path d="M16-64l14 18" stroke="#2b2140" stroke-width="12" stroke-linecap="round"/><path d="M16-64l14 18" stroke="#fcd9b6" stroke-width="8" stroke-linecap="round"/>
            <circle cx="0" cy="-98" r="21" fill="#fcd9b6" {!! $g !!}/>
            <path d="M-17-108h34v-8a17 6 0 0 0-34 0z" fill="#2b2140"/>
            <circle cx="-7" cy="-97" r="2.6" fill="#2b2140"/><circle cx="7" cy="-97" r="2.6" fill="#2b2140"/>
            <path d="M-8-88q8 7 16 0" fill="#fff" stroke="#2b2140" stroke-width="2.5" stroke-linecap="round"/>
            <circle cx="-13" cy="-90" r="3" fill="#ff8fb1" opacity=".7"/><circle cx="13" cy="-90" r="3" fill="#ff8fb1" opacity=".7"/>
        </g>
        <g class="ilu-bola">
            <circle cx="40" cy="-10" r="12" fill="#ffc53d" {!! $g !!}/>
            <path d="M28-10h24M40-22c-6 6-6 18 0 24" fill="none" stroke="#2b2140" stroke-width="2"/>
        </g>
    </g>
</svg>
