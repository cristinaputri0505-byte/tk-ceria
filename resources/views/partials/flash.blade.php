@if (session('success'))
    <div role="status" class="mb-5 flex items-start gap-3 rounded-2xl bg-leaf-soft text-leaf px-4 py-3 font-semibold text-sm">
        <i data-lucide="circle-check" class="w-5 h-5 shrink-0"></i> <span>{{ session('success') }}</span>
    </div>
@endif
@if (session('error'))
    <div role="alert" class="mb-5 flex items-start gap-3 rounded-2xl bg-rose-50 text-rose-700 px-4 py-3 font-semibold text-sm">
        <i data-lucide="circle-alert" class="w-5 h-5 shrink-0"></i> <span>{{ session('error') }}</span>
    </div>
@endif
@if ($errors->any())
    <div role="alert" class="mb-5 rounded-2xl bg-rose-50 text-rose-700 px-4 py-3 text-sm">
        <p class="font-bold mb-1">Periksa kembali isian berikut:</p>
        <ul class="list-disc pl-5 space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif
