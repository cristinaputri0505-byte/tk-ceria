@php
    [$label, $tipe] = $f; $hint = $f[3] ?? null;
    $val = old($key, $nilai[$key] ?? '');
    $wide = in_array($tipe, ['textarea', 'lines', 'image', 'embed']);
    $err = $errors->has($key) ? 'border-rose-400' : 'border-slate-200';
@endphp
<div class="{{ $wide ? 'sm:col-span-2' : '' }}">
    @if ($tipe === 'toggle')
        <label class="flex items-center justify-between gap-3 rounded-2xl border border-slate-200 px-4 py-3 cursor-pointer has-[:checked]:border-leaf has-[:checked]:bg-leaf-soft">
            <span class="text-sm font-bold text-navy">{{ $label }}</span>
            <input type="hidden" name="{{ $key }}" value="0">
            <input type="checkbox" id="f_{{ $key }}" name="{{ $key }}" value="1" @checked((string) $val === '1') class="peer sr-only">
            <span class="relative w-11 h-6 rounded-full bg-slate-300 peer-checked:bg-leaf transition-colors after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:rounded-full after:bg-white after:transition-transform peer-checked:after:translate-x-5 peer-focus-visible:ring-2 peer-focus-visible:ring-sun" aria-hidden="true"></span>
        </label>
    @else
    <label for="f_{{ $key }}" class="block text-sm font-bold text-navy mb-1.5">{{ $label }}</label>
    @endif

    @if ($tipe === 'toggle')
    @elseif ($tipe === 'image')
        <div class="flex flex-wrap items-start gap-4">
            @if (! empty($nilai[$key]))
                <img src="{{ Storage::disk('public')->url($nilai[$key]) }}" alt="Gambar saat ini" class="h-28 max-w-[220px] rounded-xl object-cover border border-slate-100">
            @else
                <span class="h-28 w-40 rounded-xl bg-cloud border border-dashed border-slate-300 grid place-items-center text-xs text-slate-500 text-center px-3">Memakai gambar bawaan</span>
            @endif
            <div class="flex-1 min-w-[220px] space-y-2">
                <input id="f_{{ $key }}" name="{{ $key }}" type="file" accept="image/*"
                    class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sky-soft file:px-4 file:py-2 file:font-bold file:text-sky">
                @if (! empty($nilai[$key]))
                    <label class="flex items-center gap-2 text-sm text-rose-600 font-bold">
                        <input type="checkbox" name="hapus_{{ $key }}" value="1" class="accent-rose-500"> Hapus gambar ini (kembali ke bawaan)
                    </label>
                @endif
                <p class="text-xs text-slate-500">{{ $hint ?? '' }} JPG/PNG, maksimal 4 MB.</p>
            </div>
        </div>
    @elseif (in_array($tipe, ['textarea', 'lines', 'embed']))
        <textarea id="f_{{ $key }}" name="{{ $key }}" rows="{{ $tipe === 'textarea' ? 3 : 4 }}" @if($tipe === 'embed') placeholder='<iframe src="https://www.google.com/maps/embed?..."></iframe>' @endif class="w-full rounded-xl border {{ $err }} px-4 py-2.5 text-sm focus:border-sky focus:ring-2 focus:ring-sky/20 outline-none">{{ $val }}</textarea>
    @elseif ($tipe === 'ikon')
        <div class="flex items-center gap-2">
            <span class="w-10 h-10 shrink-0 grid place-items-center rounded-full bg-sky-soft text-sky" data-ikon-preview="{{ $key }}"><i data-lucide="{{ $val }}" class="w-5 h-5"></i></span>
            <select id="f_{{ $key }}" name="{{ $key }}" data-ikon="{{ $key }}" class="flex-1 rounded-xl border {{ $err }} px-3 py-2.5 text-sm">
                @foreach (\App\Support\Pengaturan::IKON as $ik => $nama)<option value="{{ $ik }}" @selected($val === $ik)>{{ $nama }}</option>@endforeach
            </select>
        </div>
    @else
        <input id="f_{{ $key }}" name="{{ $key }}" type="{{ $tipe === 'url' ? 'url' : 'text' }}" value="{{ $val }}" @if($tipe === 'url') placeholder="https://" @endif
            class="w-full rounded-xl border {{ $err }} px-4 py-2.5 text-sm focus:border-sky focus:ring-2 focus:ring-sky/20 outline-none">
    @endif

    @if ($hint && $tipe !== 'image')<p class="text-xs text-slate-500 mt-1">{{ $hint }}</p>@endif
    @error($key)<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
</div>
