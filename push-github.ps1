# ============================================================
#  push-github.ps1 — simpan semua perubahan TK Ceria ke GitHub
#  Cara pakai (di terminal VS Code, folder C:\laragon\www\tk-ceria):
#     powershell -ExecutionPolicy Bypass -File .\push-github.ps1
#  atau dengan pesan sendiri:
#     powershell -ExecutionPolicy Bypass -File .\push-github.ps1 "Migrasi ke Supabase"
# ============================================================
param([string]$Pesan = "Update TK Ceria $(Get-Date -Format 'yyyy-MM-dd HH:mm')")

$ErrorActionPreference = 'Stop'
Set-Location -Path $PSScriptRoot

function Berhenti($teks) {
    Write-Host ""
    Write-Host "DIBATALKAN: $teks" -ForegroundColor Red
    git reset -q
    exit 1
}

Write-Host "1/4  Menyiapkan perubahan..." -ForegroundColor Cyan
git add .

$berubah = git diff --cached --name-only
if (-not $berubah) {
    Write-Host "Tidak ada perubahan baru. GitHub sudah sama dengan komputer Anda." -ForegroundColor Green
    exit 0
}
Write-Host "     $(@($berubah).Count) file berubah."

Write-Host "2/4  Memeriksa file rahasia..." -ForegroundColor Cyan
$terlarang = @($berubah | Where-Object { $_ -match '(^|/)\.env$|\.sqlite$|^storage/app/private/' })
if ($terlarang.Count) {
    $terlarang | ForEach-Object { Write-Host "     - $_" -ForegroundColor Yellow }
    Berhenti "file di atas tidak boleh di-upload. Tambahkan ke .gitignore lalu jalankan lagi."
}

Write-Host "3/4  Mencari password / kunci Supabase di dalam file..." -ForegroundColor Cyan
$pola = 'supabase\.co|pooler\.supabase|DB_PASSWORD=.+|sb_secret_|service_role|eyJhbGci'
$bocor = @()
foreach ($f in $berubah) {
    if ($f -eq 'push-github.ps1') { continue }   # skrip ini sendiri berisi pola pencarian
    $isi = git diff --cached -- "$f" | Select-String -Pattern $pola
    if ($isi) { $bocor += $f }
}
if ($bocor.Count) {
    $bocor | ForEach-Object { Write-Host "     - $_" -ForegroundColor Yellow }
    Berhenti "file di atas berisi alamat/password/kunci Supabase. Pindahkan isinya ke .env. (Kirim NAMA file-nya saja ke Claude, jangan isinya.)"
}
Write-Host "     Aman, tidak ada rahasia yang ikut."

Write-Host "4/4  Menyimpan dan meng-upload ke GitHub..." -ForegroundColor Cyan
git commit -q -m $Pesan
git push
if ($LASTEXITCODE -ne 0) { Write-Host "Push gagal. Kirim pesan error di atas ke Claude." -ForegroundColor Red; exit 1 }

Write-Host ""
Write-Host "BERHASIL: '$Pesan' sudah ada di GitHub." -ForegroundColor Green
git log -1 --format="Commit %h  ·  %ad" --date=format:"%d %b %Y %H:%M"
