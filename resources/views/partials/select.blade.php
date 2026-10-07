@php $value = (string) old($name, $value ?? ''); $id = 'f_'.$name; @endphp
<div class="{{ $class ?? '' }}">
    <label for="{{ $id }}" class="block text-sm font-bold text-navy mb-1.5">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}" @if($required ?? true) required @endif
        class="w-full rounded-xl border @error($name) border-rose-400 @else border-slate-200 @enderror bg-white px-4 py-2.5 text-sm focus:border-sky focus:ring-2 focus:ring-sky/20 outline-none">
        @isset($placeholder)<option value="">{{ $placeholder }}</option>@else<option value="" disabled @selected($value === '')>Pilih…</option>@endisset
        @foreach ($options as $k => $v)
            <option value="{{ $k }}" @selected($value === (string) $k)>{{ $v }}</option>
        @endforeach
    </select>
    @error($name)<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
</div>
