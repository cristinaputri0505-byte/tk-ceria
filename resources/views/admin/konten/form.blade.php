@extends('layouts.dashboard')
@section('title', $m['judul'])
@section('heading', ($item->exists ? 'Ubah ' : 'Tambah ').$m['satuan'])
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route($m['route'].'.update', $item->id) : route($m['route'].'.store') }}" class="bg-white rounded-3xl border border-slate-100 p-6 grid sm:grid-cols-2 gap-5 max-w-3xl">
    @csrf @if ($item->exists) @method('PUT') @endif
    @foreach ($m['fields'] as $key => $f)
        @php
            [$label, $tipe] = $f; $opsi = $f[3] ?? [];
            $val = old($key, $item->$key);
            $wide = ($opsi['wide'] ?? false) || in_array($tipe, ['textarea', 'image']);
            $err = $errors->has($key) || $errors->has($key.'.*') ? 'border-rose-400' : 'border-slate-200';
            $multi = ($opsi['multiple'] ?? false) && ! $item->exists;
        @endphp
        <div class="{{ $wide ? 'sm:col-span-2' : '' }}">
            <label for="f_{{ $key }}" class="block text-sm font-bold text-navy mb-1.5">{{ $label }}</label>
            @if ($tipe === 'image')
                <div class="flex flex-wrap items-start gap-4">
                    @if ($item->$key)
                        <img src="{{ asset('storage/'.$item->$key) }}" alt="Gambar saat ini" class="h-28 max-w-[220px] rounded-xl object-cover">
                    @endif
                    <div class="flex-1 min-w-[220px] space-y-2">
                        <input id="f_{{ $key }}" name="{{ $multi ? $key.'[]' : $key }}" type="file" accept="image/*" @if($multi) multiple @endif @if(($opsi['wajib_baru'] ?? false) && ! $item->exists) required @endif
                            class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sky-soft file:px-4 file:py-2 file:font-bold file:text-sky">
                        @if ($item->$key && ! ($opsi['wajib_baru'] ?? false))
                            <label class="flex items-center gap-2 text-sm text-rose-600 font-bold"><input type="checkbox" name="hapus_{{ $key }}" value="1" class="accent-rose-500"> Hapus gambar</label>
                        @endif
                        <p class="text-xs text-slate-500">{{ $opsi['hint'] ?? '' }} JPG/PNG, maksimal 4 MB{{ $item->exists ? '. Kosongkan jika tidak ingin mengganti.' : '.' }}</p>
                    </div>
                </div>
            @elseif ($tipe === 'textarea')
                <textarea id="f_{{ $key }}" name="{{ $key }}" rows="4" class="w-full rounded-xl border {{ $err }} px-4 py-2.5 text-sm">{{ $val }}</textarea>
            @elseif ($tipe === 'ikon')
                <div class="flex items-center gap-2">
                    <span class="w-10 h-10 shrink-0 grid place-items-center rounded-full bg-sky-soft text-sky" id="prev_{{ $key }}"><i data-lucide="{{ $val ?: 'star' }}" class="w-5 h-5"></i></span>
                    <select id="f_{{ $key }}" name="{{ $key }}" class="flex-1 rounded-xl border {{ $err }} px-3 py-2.5 text-sm"
                        onchange="document.getElementById('prev_{{ $key }}').innerHTML='<i data-lucide=&quot;'+this.value+'&quot; class=&quot;w-5 h-5&quot;></i>';lucide.createIcons()">
                        @foreach (\App\Support\Pengaturan::IKON as $ik => $nama)<option value="{{ $ik }}" @selected(($val ?: 'star') === $ik)>{{ $nama }}</option>@endforeach
                    </select>
                </div>
            @else
                <input id="f_{{ $key }}" name="{{ $key }}" type="{{ $tipe === 'number' ? 'number' : 'text' }}" value="{{ $val }}" @if($tipe === 'number') min="0" @endif
                    @if(str_contains($f[2], 'required')) required @endif
                    class="w-full rounded-xl border {{ $err }} px-4 py-2.5 text-sm focus:border-sky focus:ring-2 focus:ring-sky/20 outline-none">
            @endif
            @if (($opsi['hint'] ?? null) && $tipe !== 'image')<p class="text-xs text-slate-500 mt-1">{{ $opsi['hint'] }}</p>@endif
            @error($key)<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            @error($key.'.*')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
        </div>
    @endforeach
    <div class="sm:col-span-2">@include('partials.form-actions', ['back' => route($m['route'].'.index')])</div>
</form>
@endsection
