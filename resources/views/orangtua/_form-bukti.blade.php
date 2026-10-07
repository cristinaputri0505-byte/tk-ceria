<form method="POST" action="{{ route('orangtua.bayar', $t) }}" enctype="multipart/form-data" class="mt-3 grid sm:grid-cols-[1fr_1fr_auto] gap-3 items-end bg-cloud rounded-2xl p-4">
    @csrf
    <input type="hidden" name="tagihan_id" value="{{ $t->id }}">
    <div>
        <label for="bukti_{{ $t->id }}" class="block text-xs font-bold text-navy mb-1">Foto / PDF bukti transfer</label>
        <input id="bukti_{{ $t->id }}" type="file" name="bukti" accept="image/*,.pdf" required class="block w-full text-sm file:mr-2 file:rounded-full file:border-0 file:bg-white file:px-3 file:py-1.5 file:font-bold file:text-sky">
        @if (old('tagihan_id') == $t->id) @error('bukti')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror @endif
    </div>
    <div>
        <label for="cat_{{ $t->id }}" class="block text-xs font-bold text-navy mb-1">Catatan (opsional)</label>
        <input id="cat_{{ $t->id }}" name="catatan" placeholder="Contoh: transfer dari rek. a.n. Budi" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm">
    </div>
    <button class="bg-navy text-white text-sm font-extrabold px-4 py-2.5 rounded-xl">Kirim bukti</button>
</form>
