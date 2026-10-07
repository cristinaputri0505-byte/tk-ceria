@extends('layouts.dashboard')
@section('title', $siswa->nama)
@section('heading', 'Profil Anak')
@section('actions')
    <button type="button" onclick="window.print()" class="hidden sm:inline-flex items-center gap-2 border border-slate-200 text-navy text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-cloud print:hidden">
        <i data-lucide="printer" class="w-4 h-4"></i> Cetak
    </button>
@endsection
@section('content')
@php
    $tabs = ['profil' => ['Profil', 'id-card'], 'kehadiran' => ['Kehadiran', 'calendar-check'], 'rapor' => ['Perkembangan', 'sprout'], 'pembayaran' => ['Pembayaran', 'wallet'], 'dokumentasi' => ['Dokumentasi', 'camera']];
    $tabs = array_filter($tabs, fn ($v, $k) => $k === 'profil' || \App\Support\Pengaturan::fitur(['kehadiran' => 'kehadiran', 'rapor' => 'rapor', 'pembayaran' => 'pembayaran', 'dokumentasi' => 'dokumentasi'][$k]), ARRAY_FILTER_USE_BOTH);
    $tab = array_key_exists($tab, $tabs) ? $tab : 'profil';
    $aktif = 'bg-navy text-white border-navy'; $pasif = 'bg-white text-navy border-slate-200 hover:border-sky';
    $warnaStatus = ['hadir' => 'bg-emerald-400 text-white', 'izin' => 'bg-sky text-white', 'sakit' => 'bg-amber-400 text-white', 'alpa' => 'bg-berry text-white'];
    $rekap = $absensi->countBy('status');
    $prev = $awal->copy()->subMonth()->format('Y-m'); $next = $awal->copy()->addMonth()->format('Y-m');
@endphp

@if ($semuaAnak->count() > 1)
    <nav class="flex flex-wrap gap-2 mb-4 print:hidden" aria-label="Pilih anak">
        @foreach ($semuaAnak as $a)
            <a href="{{ route('orangtua.anak', [$a, 'tab' => $tab]) }}" class="inline-flex items-center gap-2 pl-1 pr-4 py-1 rounded-full text-sm font-bold border {{ $a->id === $siswa->id ? 'bg-sun border-sun text-navy' : 'bg-white border-slate-200 text-navy' }}">
                @include('partials.foto-anak', ['s' => $a, 'size' => 'w-8 h-8 !rounded-full !border-2', 'teks' => 'text-sm'])
                {{ $a->nama_panggilan }}
            </a>
        @endforeach
    </nav>
@endif

{{-- Kepala profil --}}
<section class="relative overflow-hidden bg-white rounded-[2rem] border border-slate-100 p-6 sm:p-8">
    <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-r from-sky-soft via-sun-soft to-emerald-50" aria-hidden="true"></div>
    <div class="relative flex flex-wrap items-end gap-6">
        @include('partials.foto-anak', ['s' => $siswa, 'size' => 'w-32 h-32 sm:w-40 sm:h-40', 'teks' => 'text-6xl'])
        <div class="flex-1 min-w-[220px] pb-1">
            <h2 class="font-display text-3xl sm:text-4xl font-semibold text-navy leading-tight">{{ $siswa->nama }}</h2>
            <p class="text-slate-500 mt-1">Panggilan <b class="text-navy">{{ $siswa->nama_panggilan }}</b> · NIS {{ $siswa->nis }}</p>
            <div class="flex flex-wrap gap-2 mt-3">
                <span class="text-xs font-bold bg-sky-soft text-sky px-3 py-1 rounded-full">{{ $siswa->kelas->nama ?? 'Belum ada kelas' }}</span>
                <span class="text-xs font-bold bg-cloud text-navy px-3 py-1 rounded-full">{{ $siswa->usia }}</span>
                <span class="text-xs font-bold bg-cloud text-navy px-3 py-1 rounded-full">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                @if ($siswa->kelas?->tahun_ajaran)<span class="text-xs font-bold bg-cloud text-navy px-3 py-1 rounded-full">TA {{ $siswa->kelas->tahun_ajaran }}</span>@endif
            </div>
        </div>
    </div>
</section>

<nav class="flex flex-wrap gap-2 my-5 print:hidden" role="tablist">
    @foreach ($tabs as $k => [$lbl, $ic])
        <a href="{{ route('orangtua.anak', [$siswa, 'tab' => $k]) }}" role="tab" data-tab="{{ $k }}" aria-selected="{{ $tab === $k ? 'true' : 'false' }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border {{ $tab === $k ? $aktif : $pasif }}">
            <i data-lucide="{{ $ic }}" class="w-4 h-4"></i> {{ $lbl }}
        </a>
    @endforeach
</nav>

{{-- ============ PROFIL ============ --}}
<section data-panel="profil" @if($tab !== 'profil') hidden @endif class="grid lg:grid-cols-3 gap-5 print:!grid">
    @php
        $baris = fn ($label, $isi) => ['label' => $label, 'isi' => filled($isi) ? $isi : '—'];
        $biodata = [
            $baris('Nama lengkap', $siswa->nama),
            $baris('Nama panggilan', $siswa->nama_panggilan),
            $baris('NIS', $siswa->nis),
            $baris('Tempat, tanggal lahir', trim(($siswa->tempat_lahir ? $siswa->tempat_lahir.', ' : '').$siswa->tanggal_lahir?->translatedFormat('d F Y'))),
            $baris('Jenis kelamin', $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan'),
            $baris('Agama', $siswa->agama),
            $baris('Anak ke', $siswa->anak_ke),
            $baris('Alamat', $siswa->alamat),
        ];
        $ortu = [
            ['Ayah', $siswa->nama_ayah, $siswa->pekerjaan_ayah, $siswa->telepon_ayah],
            ['Ibu', $siswa->nama_ibu, $siswa->pekerjaan_ibu, $siswa->telepon_ibu],
        ];
    @endphp
    <div class="bg-white rounded-3xl border border-slate-100 p-6 lg:col-span-2">
        <h3 class="font-display text-lg font-semibold text-navy flex items-center gap-2"><i data-lucide="baby" class="w-5 h-5 text-rose-500"></i> Biodata anak</h3>
        <dl class="mt-4 grid sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
            @foreach ($biodata as $b)
                <div class="border-b border-slate-100 pb-2"><dt class="text-xs text-slate-500">{{ $b['label'] }}</dt><dd class="font-bold text-navy">{{ $b['isi'] }}</dd></div>
            @endforeach
        </dl>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 p-6">
        <h3 class="font-display text-lg font-semibold text-navy flex items-center gap-2"><i data-lucide="school" class="w-5 h-5 text-sky"></i> Sekolah</h3>
        <dl class="mt-4 space-y-3 text-sm">
            <div><dt class="text-xs text-slate-500">Kelas</dt><dd class="font-bold text-navy">{{ $siswa->kelas->nama ?? '—' }} {{ $siswa->kelas ? '(Kelompok '.$siswa->kelas->kelompok.')' : '' }}</dd></div>
            <div><dt class="text-xs text-slate-500">Wali kelas</dt><dd class="font-bold text-navy">{{ $siswa->kelas?->wali?->name ?? '—' }}</dd>
                @if ($siswa->kelas?->wali?->telepon)<dd><a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $siswa->kelas->wali->telepon)) }}" target="_blank" class="text-xs font-bold text-leaf">Chat WhatsApp wali kelas</a></dd>@endif
            </div>
            <div><dt class="text-xs text-slate-500">Tanggal masuk</dt><dd class="font-bold text-navy">{{ $siswa->tanggal_masuk?->translatedFormat('d F Y') ?? '—' }}</dd></div>
        </dl>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 p-6 lg:col-span-2">
        <h3 class="font-display text-lg font-semibold text-navy flex items-center gap-2"><i data-lucide="users" class="w-5 h-5 text-amber-500"></i> Data orang tua</h3>
        <div class="grid sm:grid-cols-2 gap-4 mt-4">
            @foreach ($ortu as [$peran, $nama, $kerja, $telp])
                <div class="rounded-2xl bg-cloud p-4 text-sm">
                    <p class="text-xs font-extrabold text-slate-500">{{ $peran }}</p>
                    <p class="font-extrabold text-navy text-base mt-0.5">{{ $nama ?: '—' }}</p>
                    <p class="text-slate-600 mt-1">{{ $kerja ?: 'Pekerjaan belum diisi' }}</p>
                    <p class="text-slate-600">{{ $telp ?: 'Telepon belum diisi' }}</p>
                </div>
            @endforeach
        </div>
        <p class="text-xs text-slate-500 mt-3">Akun login: <b>{{ $siswa->orangTua->name }}</b> ({{ $siswa->orangTua->email }})</p>
    </div>

    <div class="bg-white rounded-3xl border border-slate-100 p-6">
        <h3 class="font-display text-lg font-semibold text-navy flex items-center gap-2"><i data-lucide="heart-pulse" class="w-5 h-5 text-berry"></i> Kesehatan</h3>
        <dl class="mt-4 space-y-3 text-sm">
            <div><dt class="text-xs text-slate-500">Golongan darah</dt><dd class="font-bold text-navy">{{ $siswa->golongan_darah ?: '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">Alergi / catatan kesehatan</dt><dd class="font-bold text-navy whitespace-pre-line">{{ $siswa->catatan_kesehatan ?: 'Tidak ada catatan' }}</dd></div>
        </dl>
    </div>

    <p class="lg:col-span-3 text-xs text-slate-500 print:hidden">{{ $site['portal_bantuan'] }} ({{ $site['whatsapp'] ?: $site['telepon'] }})</p>
</section>

{{-- ============ KEHADIRAN ============ --}}
@if (isset($tabs['kehadiran']))
<section data-panel="kehadiran" @if($tab !== 'kehadiran') hidden @endif class="bg-white rounded-3xl border border-slate-100 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <a href="{{ route('orangtua.anak', [$siswa, 'tab' => 'kehadiran', 'bulan' => $prev]) }}" class="p-2 rounded-lg hover:bg-cloud" aria-label="Bulan sebelumnya"><i data-lucide="chevron-left" class="w-5 h-5"></i></a>
            <h3 class="font-display text-xl font-semibold text-navy w-44 text-center">{{ $awal->translatedFormat('F Y') }}</h3>
            <a href="{{ route('orangtua.anak', [$siswa, 'tab' => 'kehadiran', 'bulan' => $next]) }}" class="p-2 rounded-lg hover:bg-cloud" aria-label="Bulan berikutnya"><i data-lucide="chevron-right" class="w-5 h-5"></i></a>
        </div>
        <ul class="flex flex-wrap gap-2 text-xs font-bold">
            @foreach (\App\Models\Absensi::STATUS as $k => $v)
                <li class="flex items-center gap-1.5 bg-cloud rounded-full pl-1 pr-3 py-1"><span class="w-6 h-6 rounded-full grid place-items-center {{ $warnaStatus[$k] }}">{{ $rekap[$k] ?? 0 }}</span> {{ $v }}</li>
            @endforeach
        </ul>
    </div>

    <div class="grid grid-cols-7 gap-1.5 mt-5 text-center" role="grid" aria-label="Kalender kehadiran {{ $awal->translatedFormat('F Y') }}">
        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $h)<div class="text-xs font-extrabold text-slate-400 py-1">{{ $h }}</div>@endforeach
        @for ($i = 1; $i < $awal->dayOfWeekIso; $i++)<div></div>@endfor
        @for ($d = 1; $d <= $awal->daysInMonth; $d++)
            @php $tgl = $awal->copy()->day($d); $ab = $absensi[$tgl->format('Y-m-d')] ?? null; $libur = $tgl->isWeekend(); @endphp
            <div class="aspect-square sm:aspect-[4/3] rounded-xl flex flex-col items-center justify-center text-sm
                {{ $ab ? $warnaStatus[$ab->status] : ($libur ? 'bg-slate-50 text-slate-300' : 'bg-cloud text-navy') }} {{ $tgl->isToday() ? 'ring-2 ring-sun' : '' }}"
                @if($ab) title="{{ \App\Models\Absensi::STATUS[$ab->status] }}{{ $ab->keterangan ? ': '.$ab->keterangan : '' }}" @endif>
                <span class="font-bold">{{ $d }}</span>
                @if ($ab)<span class="text-[10px] font-bold hidden sm:block">{{ \App\Models\Absensi::STATUS[$ab->status] }}</span>@endif
            </div>
        @endfor
    </div>

    @php $catatan = $absensi->filter(fn ($a) => $a->keterangan); @endphp
    @if ($catatan->isNotEmpty())
        <h4 class="font-bold text-navy mt-6 text-sm">Catatan guru</h4>
        <ul class="mt-2 space-y-1 text-sm">
            @foreach ($catatan as $c)<li><b>{{ $c->tanggal->translatedFormat('d M') }}</b> · {{ \App\Models\Absensi::STATUS[$c->status] }}: {{ $c->keterangan }}</li>@endforeach
        </ul>
    @endif
</section>

@endif

{{-- ============ RAPOR ============ --}}
@if (isset($tabs['rapor']))
<section data-panel="rapor" @if($tab !== 'rapor') hidden @endif class="bg-white rounded-3xl border border-slate-100 p-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h3 class="font-display text-xl font-semibold text-navy">Laporan perkembangan</h3>
        @if ($periodeList->count() > 1)
            <form class="print:hidden"><input type="hidden" name="tab" value="rapor">
                <select name="periode" aria-label="Periode" class="rounded-xl border border-slate-200 px-3 py-2 text-sm" onchange="this.form.submit()">
                    @foreach ($periodeList as $p)<option @selected($p === $periode)>{{ $p }}</option>@endforeach
                </select>
            </form>
        @else
            <span class="text-sm font-bold text-slate-500">{{ $periode }}</span>
        @endif
    </div>
    @if ($perkembangan->isEmpty())
        <p class="text-sm text-slate-500 mt-6">Laporan periode ini belum diisi oleh wali kelas.</p>
    @else
        <ul class="mt-5 grid md:grid-cols-2 gap-3">
            @foreach (\App\Models\Perkembangan::ASPEK as $kode => $label)
                @php $p = $perkembangan[$kode] ?? null; $level = ['BB' => 1, 'MB' => 2, 'BSH' => 3, 'BSB' => 4][$p->nilai ?? ''] ?? 0; @endphp
                <li class="rounded-2xl bg-cloud p-4">
                    <div class="flex items-center justify-between gap-3">
                        <h4 class="font-bold text-navy">{{ $label }}</h4>
                        <span class="text-xs font-extrabold bg-white text-navy px-2 py-1 rounded-lg">{{ $p->nilai ?? '—' }}</span>
                    </div>
                    <div class="flex gap-1 mt-2" aria-hidden="true">
                        @for ($i = 1; $i <= 4; $i++)<span class="h-2 flex-1 rounded-full {{ $i <= $level ? 'bg-leaf' : 'bg-slate-200' }}"></span>@endfor
                    </div>
                    <p class="text-xs font-bold text-slate-500 mt-1.5">{{ $p ? \App\Models\Perkembangan::NILAI[$p->nilai] : 'Belum dinilai' }}</p>
                    @if ($p?->catatan)<p class="text-sm text-slate-600 mt-2">{{ $p->catatan }}</p>@endif
                </li>
            @endforeach
        </ul>
        <p class="text-xs text-slate-500 mt-4">Dinilai oleh {{ $perkembangan->first()->guru->name ?? 'wali kelas' }}. BB = Belum Berkembang, MB = Mulai Berkembang, BSH = Berkembang Sesuai Harapan, BSB = Berkembang Sangat Baik.</p>
    @endif
</section>

@endif

{{-- ============ PEMBAYARAN ============ --}}
@if (isset($tabs['pembayaran']))
<section data-panel="pembayaran" @if($tab !== 'pembayaran') hidden @endif>
    <ul class="space-y-3">
        @forelse ($tagihan as $t)
            @include('orangtua._tagihan', ['t' => $t])
        @empty
            <li class="bg-white rounded-3xl border border-slate-100 p-8 text-center text-slate-500">Belum ada tagihan untuk {{ $siswa->nama_panggilan }}.</li>
        @endforelse
    </ul>
    <p class="text-sm text-slate-500 mt-4">Info rekening ada di menu <a href="{{ route('orangtua.pembayaran') }}" class="font-bold text-sky">Pembayaran</a>.</p>
</section>

@endif

{{-- ============ DOKUMENTASI ============ --}}
@if (isset($tabs['dokumentasi']))
<section data-panel="dokumentasi" @if($tab !== 'dokumentasi') hidden @endif class="bg-white rounded-3xl border border-slate-100 p-6">
    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @forelse ($dokumentasi as $d)
            <li>
                <button type="button" data-foto="{{ $d->url }}" data-judul="{{ $d->keterangan }} · {{ $d->tanggal->translatedFormat('d M Y') }}" class="block w-full text-left">
                    <img src="{{ $d->url }}" alt="{{ $d->keterangan ?? 'Dokumentasi' }}" class="w-full aspect-square object-cover rounded-2xl" loading="lazy">
                    <span class="block text-xs font-bold text-navy mt-1.5 truncate">{{ $d->keterangan ?? 'Kegiatan kelas' }}</span>
                    <span class="block text-[11px] text-slate-400">{{ $d->tanggal->translatedFormat('d M Y') }}{{ $d->siswa_id ? ' · Foto khusus '.$siswa->nama_panggilan : '' }}</span>
                </button>
            </li>
        @empty
            <li class="col-span-full text-sm text-slate-500">Belum ada foto dokumentasi. Wali kelas akan membagikan foto kegiatan di sini.</li>
        @endforelse
    </ul>
</section>

@endif

@include('partials.lightbox')
@endsection

@push('scripts')
<script>
(function () {
    const aktif = @json(explode(' ', $aktif)), pasif = @json(explode(' ', $pasif));
    document.querySelectorAll('[data-tab]').forEach(t => t.addEventListener('click', e => {
        e.preventDefault();
        const nama = t.dataset.tab;
        document.querySelectorAll('[data-tab]').forEach(x => {
            const on = x.dataset.tab === nama;
            x.classList.remove(...(on ? pasif : aktif)); x.classList.add(...(on ? aktif : pasif));
            x.setAttribute('aria-selected', on);
        });
        document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== nama);
        const url = new URL(location.href); url.searchParams.set('tab', nama); history.replaceState(null, '', url);
    }));
})();
</script>
@endpush
