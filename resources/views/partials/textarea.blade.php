<div class="{{ $class ?? '' }}">
    <label for="f_{{ $name }}" class="block text-sm font-bold text-navy mb-1.5">{{ $label }}</label>
    <textarea id="f_{{ $name }}" name="{{ $name }}" rows="{{ $rows ?? 3 }}" @if($required ?? true) required @endif
        class="w-full rounded-xl border @error($name) border-rose-400 @else border-slate-200 @enderror bg-white px-4 py-2.5 text-sm focus:border-sky focus:ring-2 focus:ring-sky/20 outline-none">{{ old($name, $value ?? '') }}</textarea>
    @error($name)<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
</div>
