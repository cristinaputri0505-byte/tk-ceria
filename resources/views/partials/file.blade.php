<div class="{{ $class ?? '' }}">
    <label for="f_{{ $name }}" class="block text-sm font-bold text-navy mb-1.5">{{ $label }}</label>
    @if (! empty($current))
        <img src="{{ $currentUrl ?? asset('storage/'.$current) }}" alt="Gambar saat ini" class="h-24 rounded-xl object-cover mb-2">
    @endif
    <input id="f_{{ $name }}" name="{{ $name }}" type="file" accept="image/*"
        class="block w-full text-sm file:mr-3 file:rounded-full file:border-0 file:bg-sky-soft file:px-4 file:py-2 file:font-bold file:text-sky">
    <p class="text-xs text-slate-500 mt-1">JPG atau PNG, maksimal {{ $max ?? '2' }} MB.{{ ! empty($current) ? ' Kosongkan jika tidak ingin mengganti.' : '' }}</p>
    @error($name)<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
</div>
