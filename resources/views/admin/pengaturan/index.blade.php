@extends('layouts.dashboard')
@section('title', 'Pengaturan Website')
@section('heading', 'Pengaturan Website')
@section('actions')
    <a href="{{ route('home') }}" target="_blank" class="inline-flex items-center gap-2 border border-slate-200 text-navy text-sm font-bold px-4 py-2.5 rounded-xl hover:bg-cloud">
        <i data-lucide="external-link" class="w-4 h-4"></i> <span class="hidden sm:inline">Lihat website</span>
    </a>
@endsection
@section('content')
@php
    $aktif = 'bg-navy text-white border-navy';
    $pasif = 'bg-white text-navy border-slate-200 hover:border-sky';
@endphp

<nav class="flex flex-wrap gap-2 mb-6" role="tablist" aria-label="Bagian website">
    @foreach ($tabs as $tKey => $tInfo)
        <a href="{{ route('admin.pengaturan.index', ['tab' => $tKey]) }}" role="tab" id="tab-{{ $tKey }}"
           data-tab="{{ $tKey }}" aria-controls="panel-{{ $tKey }}" aria-selected="{{ $tKey === $tab ? 'true' : 'false' }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold border {{ $tKey === $tab ? $aktif : $pasif }}">
            <i data-lucide="{{ $tInfo[1] }}" class="w-4 h-4"></i> {{ $tInfo[0] }}
        </a>
    @endforeach
</nav>

@foreach ($tabs as $tKey => $tInfo)
    <section id="panel-{{ $tKey }}" role="tabpanel" aria-labelledby="tab-{{ $tKey }}" data-panel="{{ $tKey }}" @if($tKey !== $tab) hidden @endif>
        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.pengaturan.update', ['tab' => $tKey]) }}" class="bg-white rounded-3xl border border-slate-100 p-6 max-w-4xl">
            @csrf
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="font-display text-xl font-semibold text-navy">Halaman {{ $tInfo[0] }}</h2>
                    <p class="text-sm text-slate-500 mt-1">Kosongkan kolom teks untuk kembali ke tulisan bawaan.</p>
                </div>
                @if (! empty($tInfo[3]))
                    <div class="flex flex-wrap gap-2">
                        @foreach ($tInfo[3] as $lbl => $rt)
                            <a href="{{ route($rt) }}" class="inline-flex items-center gap-1.5 bg-sun-soft text-navy text-xs font-extrabold px-3 py-2 rounded-lg hover:bg-sun/30">
                                <i data-lucide="layout-grid" class="w-3.5 h-3.5"></i> Kelola {{ strtolower($lbl) }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid sm:grid-cols-2 gap-5 mt-6">
                @foreach ($tInfo[2] as $key => $f)
                    @if (is_int($key))
                        <h3 class="sm:col-span-2 font-extrabold text-navy {{ $loop->first ? '' : 'border-t border-slate-100 pt-5' }}">{{ $f['group'] }}</h3>
                    @else
                        @include('admin.pengaturan._field', ['key' => $key, 'f' => $f])
                    @endif
                @endforeach
            </div>

            <div class="flex items-center gap-3 pt-6 mt-6 border-t border-slate-100">
                <button class="bg-sky text-white font-extrabold px-6 py-2.5 rounded-xl hover:bg-sky-dark">Simpan perubahan</button>
                <a href="{{ route('home') }}" target="_blank" class="text-sm font-bold text-slate-500 hover:text-navy">Lihat hasilnya di website</a>
            </div>
        </form>


    </section>
@endforeach
@endsection

@push('scripts')
<script>
(function () {
    const aktif = @json(explode(' ', $aktif));
    const pasif = @json(explode(' ', $pasif));
    const tabs = document.querySelectorAll('[data-tab]');

    function buka(nama) {
        tabs.forEach(t => {
            const on = t.dataset.tab === nama;
            t.classList.remove(...(on ? pasif : aktif));
            t.classList.add(...(on ? aktif : pasif));
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        document.querySelectorAll('[data-panel]').forEach(p => p.hidden = p.dataset.panel !== nama);
        const url = new URL(location.href);
        url.searchParams.set('tab', nama);
        history.replaceState(null, '', url);
    }

    tabs.forEach(t => t.addEventListener('click', e => { e.preventDefault(); buka(t.dataset.tab); }));

    document.querySelectorAll('select[data-ikon]').forEach(sel => sel.addEventListener('change', () => {
        const box = document.querySelector('[data-ikon-preview="' + sel.dataset.ikon + '"]');
        box.innerHTML = '<i data-lucide="' + sel.value + '" class="w-5 h-5"></i>';
        lucide.createIcons();
    }));
})();
</script>
@endpush
