{{-- Tanggal berupa tiga daftar pilihan: Tanggal · Bulan · Tahun.
     Parameter: name, label, value (Y-m-d|null), tahunAkhir, tahunAwal, hint (opsional), required (bawaan true) --}}
@php
    $nilai = old($name, $value ?? null);
    try { $d = $nilai ? \Carbon\Carbon::parse($nilai) : null; } catch (\Throwable) { $d = null; }
    $h = old($name.'_hari', $d?->day);
    $b = old($name.'_bulan', $d?->month);
    $t = old($name.'_tahun', $d?->year);
    $id = 'f_'.$name;
    $wajib = $required ?? true;
    $kelasPilih = 'w-full rounded-xl border '.($errors->has($name) ? 'border-rose-400' : 'border-slate-200').' bg-white px-3 py-2.5 text-sm focus:border-sky focus:ring-2 focus:ring-sky/20 outline-none';
@endphp
<fieldset class="{{ $class ?? '' }}">
    <legend class="block text-sm font-bold text-navy mb-1.5">{{ $label }}</legend>
    <div class="grid grid-cols-[4.5rem_1fr_5.5rem] gap-2">
        <div>
            <label for="{{ $id }}_hari" class="sr-only">Tanggal</label>
            <select id="{{ $id }}_hari" name="{{ $name }}_hari" class="{{ $kelasPilih }}" @if($wajib) required @endif>
                <option value="">Tgl</option>
                @for ($i = 1; $i <= 31; $i++)<option value="{{ $i }}" @selected((int) $h === $i)>{{ $i }}</option>@endfor
            </select>
        </div>
        <div>
            <label for="{{ $id }}_bulan" class="sr-only">Bulan</label>
            <select id="{{ $id }}_bulan" name="{{ $name }}_bulan" class="{{ $kelasPilih }}" @if($wajib) required @endif>
                <option value="">Bulan</option>
                @foreach (\App\Support\Tanggal::BULAN as $no => $nm)<option value="{{ $no }}" @selected((int) $b === $no)>{{ $nm }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="{{ $id }}_tahun" class="sr-only">Tahun</label>
            <select id="{{ $id }}_tahun" name="{{ $name }}_tahun" class="{{ $kelasPilih }}" @if($wajib) required @endif>
                <option value="">Tahun</option>
                @for ($y = $tahunAkhir; $y >= $tahunAwal; $y--)<option value="{{ $y }}" @selected((int) $t === $y)>{{ $y }}</option>@endfor
            </select>
        </div>
    </div>
    @error($name)<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    @isset($hint)<p class="text-xs text-slate-500 mt-1">{{ $hint }}</p>@endisset
</fieldset>
