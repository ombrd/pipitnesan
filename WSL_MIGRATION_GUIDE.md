# Panduan Migrasi Proyek ke WSL 2 (Native Speed)

Menjalankan Docker di Windows dengan file yang berada di drive `D:` atau `C:` (Windows File System) sangat lambat karena adanya lapisan sinkronisasi antar OS. Dengan memindahkan proyek ke dalam sistem file WSL 2, Anda akan mendapatkan kecepatan **10x - 50x lebih cepat**.

## Langkah 1: Memindahkan File ke WSL

1. Buka terminal (PowerShell atau Command Prompt).
2. Masuk ke distribusi Linux Anda (biasanya Ubuntu) dengan mengetik:
   ```bash
   wsl
   ```
3. Buat direktori untuk proyek Anda di dalam folder home Linux:
   ```bash
   mkdir -p ~/projects
   cd ~/projects
   ```
4. Salin proyek dari Windows ke WSL (Ganti `d/Workspace/pipitnesan` sesuai lokasi asli Anda):
   ```bash
   cp -r /mnt/d/Workspace/pipitnesan .
   cd pipitnesan
   ```

## Langkah 2: Membuka Proyek dengan VS Code

Agar nyaman mengedit file di dalam WSL, gunakan ekstensi **WSL** di VS Code.

1. Di terminal WSL tadi (di dalam folder proyek), ketik:
   ```bash
   code .
   ```
2. VS Code akan terbuka dan menginstal server kecil di dalam Linux. Anda sekarang mengedit file secara langsung di dalam Linux dengan performa native.

## Langkah 3: Menjalankan Docker di WSL

Karena file sudah berada di Linux, Docker tidak lagi perlu melakukan sinkronisasi berat ke Windows.

1. Di terminal VS Code (yang sudah terhubung ke WSL), masuk ke folder backend:
   ```bash
   cd backend
   ```
2. Jalankan docker seperti biasa:
   ```bash
   docker compose up -d
   ```
3. Lakukan instalasi awal (karena ini lingkungan baru):
   ```bash
   docker compose exec -u sail laravel.test composer install
   docker compose exec -u sail laravel.test npm install
   docker compose exec -u sail laravel.test php artisan migrate --seed
   ```

## Langkah 4: Menghapus "Quick Fix" Sebelumnya (Opsional)

Jika Anda sudah pindah ke WSL, Anda tidak perlu lagi "Named Volumes" yang kita buat sebelumnya di `compose.yaml`, karena sinkronisasi file sudah sangat cepat secara default.

Anda bisa mengembalikan `compose.yaml` ke setelan awal agar folder `vendor` dan `node_modules` kembali terlihat di VS Code Anda.

---

## Ringkasan Perbedaan Performa

| Fitur | Windows Drive (D:) | WSL 2 Filesystem (Native) |
| :--- | :--- | :--- |
| **Page Load Speed** | 2 - 10 detik | < 500ms |
| **Composer Install** | 5 - 10 menit | < 1 menit |
| **NPM Build** | Sangat lambat | Sangat cepat |
| **Hot Reload** | Kadang tidak jalan | Instan |

> [!TIP]
> Selalu simpan kode sumber Anda di `\\wsl$\Ubuntu\home\user\...` untuk pengalaman development terbaik di Windows.
