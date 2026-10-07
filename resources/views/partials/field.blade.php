@php $value = old($name, $value ?? ''); $id = 'f_'.str_replace(['[',']'], '_', $name); @endphp
<div class="{{ $class ?? '' }}">
    <label for="{{ $id }}" class="block text-sm font-bold text-navy mb-1.5">{{ $label }}</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type ?? 'text' }}" value="{{ $value }}" @if($required ?? true) required @endif {{ $attrs ?? '' }}
        class="w-full rounded-xl border @error($name) border-rose-400 @else border-slate-200 @enderror bg-white px-4 py-2.5 text-sm focus:border-sky focus:ring-2 focus:ring-sky/20 outline-none">
    @error($name)<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
    @isset($hint)<p class="text-xs text-slate-500 mt-1">{{ $hint }}</p>@endisset
</div>
