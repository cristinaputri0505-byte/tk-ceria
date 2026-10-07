<form method="POST" action="{{ $action }}" class="inline" onsubmit="return confirm('{{ $confirm ?? 'Hapus data ini? Tindakan ini tidak bisa dibatalkan.' }}')">
    @csrf @method('DELETE')
    <button class="p-2 rounded-lg text-rose-500 hover:bg-rose-50" aria-label="Hapus"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
</form>
