@php $logoUrl = \App\Support\Pengaturan::url('logo'); @endphp
@if ($logoUrl)
    <img src="{{ $logoUrl }}" alt="" width="{{ $size ?? 48 }}" height="{{ $size ?? 48 }}" class="object-contain shrink-0" style="width:{{ $size ?? 48 }}px;height:{{ $size ?? 48 }}px">
@else
{{-- Logo bawaan: rumah warna-warni --}}
<svg width="{{ $size ?? 48 }}" height="{{ $size ?? 48 }}" viewBox="0 0 64 64" aria-hidden="true" class="shrink-0">
    <path d="M32 6 6 28h6v26h40V28h6z" fill="#fbbf24"/>
    <path d="M32 6 6 28h7L32 12l19 16h7z" fill="#e5486b"/>
    <rect x="13" y="28" width="38" height="26" rx="3" fill="#1f6fe5"/>
    <circle cx="32" cy="37" r="7" fill="#fff"/>
    <circle cx="32" cy="37" r="4" fill="#1f9d55"/>
    <rect x="27" y="45" width="10" height="9" rx="2" fill="#fbbf24"/>
    <circle cx="10" cy="48" r="6" fill="#1f9d55"/><circle cx="54" cy="48" r="6" fill="#1f9d55"/>
    <path d="M44 8v8h4V8z" fill="#14307d"/>
</svg>
@endif
