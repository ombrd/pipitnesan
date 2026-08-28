# Panduan Migrasi & Instalasi Proyek di WSL 2 (Windows)

Menjalankan Docker di Windows dengan file yang berada di drive `D:` atau `C:` (Windows File System) sangat lambat karena adanya lapisan sinkronisasi antar OS. Dengan memindahkan proyek ke dalam sistem file WSL 2, Anda akan mendapatkan kecepatan **10x - 50x lebih cepat**.

Dokumen ini mencakup:
1. Instalasi WSL 2 dari nol di Windows.
2. Instalasi Docker + integrasi WSL.
3. Instalasi `make` dan tools pendukung.
4. Migrasi/clone proyek ke filesystem WSL.
5. Instalasi backend menggunakan perintah `make`.
6. Daftar lengkap perintah `make` untuk workflow harian.
7. Troubleshooting masalah umum.

> [!NOTE]
> Semua perintah `make` untuk backend dijalankan dari dalam terminal **WSL (Ubuntu)**, di folder `backend/`. Bukan dari PowerShell/CMD Windows.

---

## Daftar Isi

- [Bagian 1: Instalasi WSL 2 dari Nol](#bagian-1-instalasi-wsl-2-dari-nol)
- [Bagian 2: Instalasi Docker untuk WSL](#bagian-2-instalasi-docker-untuk-wsl)
- [Bagian 3: Instalasi Make & Tools Pendukung](#bagian-3-instalasi-make--tools-pendukung)
- [Bagian 4: Memindahkan Proyek ke WSL](#bagian-4-memindahkan-proyek-ke-wsl)
- [Bagian 5: Membuka Proyek dengan VS Code](#bagian-5-membuka-proyek-dengan-vs-code)
- [Bagian 6: Instalasi Backend Menggunakan Make](#bagian-6-instalasi-backend-menggunakan-make)
- [Bagian 7: Daftar Perintah Make (Backend)](#bagian-7-daftar-perintah-make-backend)
- [Bagian 8: Menjalankan Artisan/Composer/NPM via Sail](#bagian-8-menjalankan-artisancomposernpm-via-sail)
- [Bagian 9: Catatan Mobile App di WSL](#bagian-9-catatan-mobile-app-di-wsl)
- [Bagian 10: Troubleshooting](#bagian-10-troubleshooting)
- [Ringkasan Perbedaan Performa](#ringkasan-perbedaan-performa)

---

## Bagian 1: Instalasi WSL 2 dari Nol

### 1.1 Persyaratan Sistem

- Windows 10 versi 2004+ (Build 19041+) atau Windows 11.
- Virtualisasi (VT-x/AMD-V) aktif di BIOS/UEFI.
- RAM minimal 8 GB (16 GB direkomendasikan untuk Docker).

### 1.2 Install WSL + Ubuntu

1. Buka **PowerShell sebagai Administrator** (klik kanan > Run as Administrator).
2. Jalankan perintah berikut untuk menginstal WSL beserta Ubuntu secara otomatis:
   ```powershell
   wsl --install
   ```
   Atau jika ingin memilih distribusi secara eksplisit:
   ```powershell
   wsl --install -d Ubuntu
   ```
3. **Restart** komputer setelah instalasi selesai.
4. Setelah restart, jendela Ubuntu akan terbuka otomatis. Buat **username** dan **password** Linux Anda (password tidak terlihat saat diketik — ini normal).

### 1.3 Pastikan Menggunakan WSL 2

Di PowerShell, jalankan:

```powershell
wsl --set-default-version 2
wsl --update
wsl -l -v
```

Pastikan output `wsl -l -v` menunjukkan `Ubuntu` dengan `VERSION` = `2`.

### 1.4 Update Sistem Ubuntu

Masuk ke WSL (ketik `wsl` di PowerShell, atau buka aplikasi **Ubuntu** dari Start Menu), lalu jalankan:

```bash
sudo apt update && sudo apt upgrade -y
```

### 1.5 (Opsional) Batasi Resource WSL

Agar WSL tidak memakan seluruh RAM Windows, buat file `C:\Users\<NamaAnda>\.wslconfig` di sisi Windows dengan isi:

```ini
[wsl2]
memory=8GB
processors=4
```

Lalu restart WSL dari PowerShell:

```powershell
wsl --shutdown
```

---

## Bagian 2: Instalasi Docker untuk WSL

Ada 2 opsi. **Opsi A direkomendasikan** karena paling mudah dan stabil di Windows.

### Opsi A: Docker Desktop (Direkomendasikan)

1. Download dan install [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop/).
2. Buka Docker Desktop, masuk ke **Settings**:
   - **General** > centang **"Use the WSL 2 based engine"**.
   - **Resources** > **WSL Integration** > aktifkan integrasi untuk distribusi **Ubuntu** Anda.
3. Klik **Apply & Restart**.
4. Verifikasi dari dalam terminal WSL:
   ```bash
   docker --version
   docker compose version
   docker run hello-world
   ```

> [!IMPORTANT]
> Docker Desktop harus dalam keadaan **running** di Windows sebelum Anda menjalankan `make` atau `docker` di WSL. Jika tidak, akan muncul error `Cannot connect to the Docker daemon`.

### Opsi B: Docker Engine Native di dalam WSL (Tanpa Docker Desktop)

Jika tidak ingin menggunakan Docker Desktop (misalnya untuk lisensi), install Docker langsung di Ubuntu:

```bash
# Tambahkan repository resmi Docker
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker Engine + plugin Compose
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Tambahkan user Anda ke group docker (agar tidak perlu sudo)
sudo usermod -aG docker $USER
```

Logout lalu login kembali ke WSL (atau jalankan `wsl --shutdown` di PowerShell lalu buka lagi) agar group `docker` aktif. Jalankan service Docker:

```bash
sudo service docker start
```

> [!TIP]
> Untuk Opsi B, Docker daemon tidak otomatis menyala saat WSL dibuka. Anda perlu menjalankan `sudo service docker start` setiap kali memulai sesi baru, atau aktifkan `systemd` di `/etc/wsl.conf`.

---

## Bagian 3: Instalasi Make & Tools Pendukung

`make` **tidak terinstal secara default** di Ubuntu WSL. Install bersama tools lain yang dibutuhkan:

```bash
sudo apt update
sudo apt install -y make build-essential git curl unzip rsync
```

Verifikasi:

```bash
make --version
git --version
```

> [!NOTE]
> `build-essential` menyertakan compiler (gcc/g++) yang kadang dibutuhkan saat ada paket npm/pip yang harus dikompilasi native. `rsync` dipakai untuk migrasi file yang lebih cepat di Bagian 4.

---

## Bagian 4: Memindahkan Proyek ke WSL

### Skenario A: Menyalin dari Drive Windows (Migrasi)

1. Buka terminal WSL, buat direktori proyek:
   ```bash
   mkdir -p ~/projects
   cd ~/projects
   ```
2. Salin proyek dari Windows (ganti `/mnt/d/Workspace/pipitnesan` sesuai lokasi asli Anda):
   ```bash
   cp -r /mnt/d/Workspace/pipitnesan .
   cd pipitnesan
   ```

> [!WARNING]
> Menyalin folder `node_modules` dan `vendor` melintasi `/mnt/d` sangat lambat (bisa puluhan menit). Jauh lebih cepat menyalin **tanpa** folder tersebut lalu menginstal ulang dependensi di WSL:
> ```bash
> rsync -av --progress \
>   --exclude 'backend/vendor' \
>   --exclude 'backend/node_modules' \
>   --exclude 'mobile_app/node_modules' \
>   --exclude 'mobile_app/android/build' \
>   --exclude 'mobile_app/android/.gradle' \
>   --exclude 'mobile_app/ios/build' \
>   --exclude 'mobile_app/ios/Pods' \
>   /mnt/d/Workspace/pipitnesan/ ~/projects/pipitnesan/
> ```
> File `.env` akan ikut tersalin, sehingga konfigurasi Anda tetap terbawa.

### Skenario B: Clone Langsung dari Git (Lebih Bersih)

Jika proyek tersedia di remote repository (GitHub/GitLab/dsb), clone langsung di dalam WSL:

```bash
cd ~/projects
git clone <url-repository> pipitnesan
cd pipitnesan
```

Dengan skenario ini Anda harus membuat `.env` baru (dibahas di Bagian 6).

### Catatan Penting: Line Endings (CRLF vs LF)

File yang dibuat/di-checkout di Windows sering menggunakan line ending `CRLF`, sedangkan script Linux (termasuk `vendor/bin/sail`) membutuhkan `LF`. Gejalanya: error aneh seperti `\r: command not found` saat menjalankan perintah.

Cegah dengan mengatur git di dalam WSL:

```bash
git config --global core.autocrlf input
```

Jika sudah terlanjur ada file CRLF, perbaiki dengan:

```bash
# Contoh memperbaiki satu file
sed -i 's/\r$//' backend/vendor/bin/sail
```

---

## Bagian 5: Membuka Proyek dengan VS Code

Agar nyaman mengedit file di dalam WSL, gunakan ekstensi **WSL** di VS Code.

1. Install ekstensi **"WSL"** (dari Microsoft) di VS Code Windows.
2. Di terminal WSL (di dalam folder proyek), ketik:
   ```bash
   code .
   ```
3. VS Code akan terbuka dan menginstal server kecil di dalam Linux. Anda sekarang mengedit file secara langsung di dalam Linux dengan performa native.
4. Buka terminal di VS Code (`Ctrl + `` ` ``) — terminal ini otomatis sudah berada di dalam WSL, sehingga semua perintah `make` di bawah bisa langsung dijalankan dari sana.

> [!TIP]
> File proyek di WSL juga bisa diakses dari Windows Explorer melalui alamat `\\wsl$\Ubuntu\home\<username>\projects\pipitnesan` (atau `\\wsl.localhost\Ubuntu\...`).

---

## Bagian 6: Instalasi Backend Menggunakan Make

### 6.1 Persiapan File `.env`

Masuk ke folder backend:

```bash
cd ~/projects/pipitnesan/backend
```

Periksa apakah `.env` sudah ada:

```bash
ls -la .env
```

- **Jika sudah ada** (hasil copy dari Windows): tidak perlu dibuat ulang. Perintah `make setup` tidak akan menimpanya (`cp -n`).
- **Jika belum ada** (hasil git clone): buat dari template lalu sesuaikan konfigurasi database agar mengarah ke container PostgreSQL:
  ```bash
  cp .env.example .env
  nano .env
  ```
  Ubah bagian database menjadi:
  ```env
  DB_CONNECTION=pgsql
  DB_HOST=pgsql
  DB_PORT=5432
  DB_DATABASE=pipitnesan
  DB_USERNAME=sail
  DB_PASSWORD=password
  ```

> [!IMPORTANT]
> Sesuaikan konfigurasi `.env` **sebelum** menjalankan `make setup`, karena proses setup akan langsung menjalankan migrasi database.

Tambahkan juga baris berikut di `.env` agar kepemilikan file di dalam container sesuai dengan user Linux Anda (menghindari masalah permission):

```env
WWWUSER=1000
WWWGROUP=1000
```

> [!TIP]
> Nilai `1000` adalah UID/GID default user pertama di Ubuntu. Pastikan dengan perintah `id -u` dan `id -g`.

### 6.2 Jalankan Instalasi Penuh

Pastikan Docker sudah berjalan, lalu dari folder `backend/` jalankan:

```bash
make setup
```

Perintah ini menjalankan seluruh instalasi secara berurutan:

| Tahap | Yang Dilakukan |
| :--- | :--- |
| 1 | Menyalin `.env.example` ke `.env` (dilewati jika `.env` sudah ada) |
| 2 | Menginstal dependensi Composer menggunakan container sementara `laravelsail/php84-composer` (Anda tidak perlu PHP terinstal di WSL). File `vendor/` dibuat dengan kepemilikan user Anda |
| 3 | Menjalankan `make up` — mem-build image `sail-8.4/app` dan menyalakan container Laravel + PostgreSQL |
| 4 | Membuat `APP_KEY` via `php artisan key:generate` (dilewati jika sudah ada) |
| 5 | Menjalankan `php artisan migrate --force` |
| 6 | Menginstal dependensi Node.js (`npm install`) di dalam container |
| 7 | Mem-build asset frontend (`npm run build`) |

> [!WARNING]
> Eksekusi pertama kali bisa memakan waktu **10–30 menit** karena harus mengunduh image Docker (PHP 8.4, PostgreSQL 17) dan seluruh dependensi Composer/NPM. Eksekusi berikutnya akan jauh lebih cepat karena cache.

> [!CAUTION]
> Jangan menjalankan `make` tanpa argumen — target pertama di Makefile adalah `setup`, sehingga `make` saja sama dengan `make setup` (instalasi penuh).

### 6.3 Verifikasi Instalasi

1. Pastikan container berjalan:
   ```bash
   docker compose ps
   ```
   Anda harus melihat service `laravel.test` dan `pgsql` dengan status `running`/`healthy`.
2. Buka aplikasi di browser Windows: [http://localhost](http://localhost)
3. (Opsional) Jalankan seeder jika tersedia:
   ```bash
   ./vendor/bin/sail artisan db:seed
   ```

---

## Bagian 7: Daftar Perintah Make (Backend)

Semua perintah di bawah dijalankan dari folder `backend/` di dalam terminal WSL:

| Perintah | Fungsi |
| :--- | :--- |
| `make setup` | Instalasi penuh pertama kali (env, composer, container, key, migrasi, npm build) |
| `make up` | Menyalakan container Laravel Sail di background (`sail up -d`) |
| `make down` | Mematikan seluruh container |
| `make restart` | Mematikan lalu menyalakan ulang container |
| `make shell` | Masuk ke terminal bash di dalam container aplikasi |
| `make migrate` | Menjalankan migrasi database (`sail artisan migrate`) |
| `make share` | Mempublikasikan aplikasi ke internet via tunnel Ngrok (`sail share`) |

### Workflow Harian yang Direkomendasikan

```bash
# Pagi hari — mulai bekerja
cd ~/projects/pipitnesan/backend
make up

# ... ngoding ... aplikasi jalan di http://localhost

# Jalankan migrasi setelah pull perubahan dari git
make migrate

# Jika ada perubahan aneh / perlu refresh container
make restart

# Selesai bekerja
make down
```

### Untuk Mobile App (jika dikembangkan di Linux/macOS)

Folder `mobile_app/` juga memiliki Makefile. Perintah utamanya:

| Perintah | Fungsi |
| :--- | :--- |
| `make install` | `npm install` + `pod install` (iOS, hanya di macOS) |
| `make start` | Menjalankan Metro bundler |
| `make android` | Menjalankan aplikasi di Android (auto-start Metro di background) |
| `make build-android-debug` | Build APK debug |
| `make clean` | Bersihkan cache & `node_modules` |

Lihat [Bagian 9](#bagian-9-catatan-mobile-app-di-wsl) untuk keterbatasan mobile development di WSL.

---

## Bagian 8: Menjalankan Artisan/Composer/NPM via Sail

Di luar perintah `make`, Anda bisa menjalankan perintah Laravel apa pun lewat script `sail` (semuanya dieksekusi di dalam container, jadi WSL Anda tidak perlu PHP/Node terinstal):

```bash
# Artisan
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan tinker
./vendor/bin/sail artisan make:model Product -mcr

# Composer
./vendor/bin/sail composer require vendor/package

# NPM
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev      # Vite dev server (hot reload)
./vendor/bin/sail npm run build

# Testing
./vendor/bin/sail test
./vendor/bin/sail test --filter=NamaTest
```

> [!TIP]
> Buat alias agar lebih singkat. Tambahkan ke `~/.bashrc`:
> ```bash
> alias sail='./vendor/bin/sail'
> ```
> Lalu jalankan `source ~/.bashrc`. Setelah itu cukup ketik `sail artisan migrate`.

Untuk pengembangan frontend dengan hot reload, jalankan di terminal terpisah (biarkan menyala):

```bash
./vendor/bin/sail npm run dev
```

---

## Bagian 9: Catatan Mobile App di WSL

Backend Laravel berjalan penuh di WSL, tetapi pengembangan mobile (React Native) memiliki keterbatasan di WSL:

- **iOS**: tidak bisa sama sekali di WSL (butuh Xcode/macOS).
- **Android**: emulator Android sebaiknya dijalankan di sisi **Windows** (Android Studio), bukan di dalam WSL. Instalasi dependensi JS (`npm install`) tetap bisa dilakukan di WSL.

Agar aplikasi Android di emulator Windows bisa mengakses backend yang berjalan di WSL:

1. Pastikan container backend berjalan (`make up`).
2. Dari emulator Android, akses API melalui alamat khusus emulator ke host: `http://10.0.2.2` (merujuk ke `localhost` Windows). Sesuaikan base URL API di konfigurasi aplikasi mobile.

---

## Bagian 10: Troubleshooting

### `make: command not found`

`make` belum terinstal di WSL:

```bash
sudo apt update && sudo apt install -y make
```

### `docker: command not found` di WSL

- **Docker Desktop**: buka Settings > Resources > WSL Integration > aktifkan untuk Ubuntu, lalu Apply & Restart.
- **Docker Engine native**: ulangi langkah instalasi di Bagian 2 Opsi B.

### `Cannot connect to the Docker daemon` / `error during connect`

Docker daemon belum berjalan:

- Docker Desktop: buka aplikasi Docker Desktop di Windows, tunggu hingga statusnya hijau ("Engine running").
- Docker native: jalankan `sudo service docker start`.

### `permission denied` saat `docker compose` / `docker run`

User Anda belum masuk group `docker` (khusus Opsi B):

```bash
sudo usermod -aG docker $USER
# Lalu tutup dan buka ulang terminal WSL
```

### `./vendor/bin/sail: No such file or directory`

Dependensi Composer belum terinstal. Jalankan `make setup` terlebih dahulu (tahap 2 akan membuat folder `vendor/`).

### Port 80 atau 5432 sudah dipakai

Jika ada aplikasi lain di Windows/WSL yang memakai port tersebut, ubah di `.env`:

```env
APP_PORT=8080
FORWARD_DB_PORT=5433
```

Lalu `make restart` dan akses aplikasi di `http://localhost:8080`.

### Error `\r: command not found` atau `bad interpreter`

File memiliki line ending Windows (CRLF). Perbaiki:

```bash
sed -i 's/\r$//' nama-file
git config --global core.autocrlf input
```

### Error `missing separator` saat menjalankan make

File `Makefile` wajib menggunakan **TAB** untuk indentasi, bukan spasi. Jika Anda mengedit Makefile di editor Windows, pastikan tidak mengubah TAB menjadi spasi.

### File `vendor/` dimiliki root, tidak bisa diedit

Biasanya terjadi jika pernah menjalankan docker dengan `sudo`. Perbaiki kepemilikan:

```bash
sudo chown -R $USER:$USER ~/projects/pipitnesan
```

### Migrasi gagal: `could not connect to server` / database tidak ditemukan

- Pastikan container database sehat: `docker compose ps` (status `pgsql` harus `healthy`).
- Pastikan `.env` menggunakan `DB_HOST=pgsql` (nama service di `compose.yaml`), bukan `127.0.0.1`.
- Tunggu beberapa detik setelah `make up` — database butuh waktu untuk siap menerima koneksi.

### Halaman lambat / perubahan file tidak terdeteksi

Hampir pasti proyek masih berada di drive Windows (`/mnt/c` atau `/mnt/d`). Pindahkan ke filesystem WSL (`~/projects`) seperti di Bagian 4 — ini adalah inti dari panduan ini.

---

## Ringkasan Perbedaan Performa

| Fitur | Windows Drive (D:) | WSL 2 Filesystem (Native) |
| :--- | :--- | :--- |
| **Page Load Speed** | 2 - 10 detik | < 500ms |
| **Composer Install** | 5 - 10 menit | < 1 menit |
| **NPM Build** | Sangat lambat | Sangat cepat |
| **Hot Reload** | Kadang tidak jalan | Instan |

> [!TIP]
> Selalu simpan kode sumber Anda di `\\wsl$\Ubuntu\home\<user>\projects\...` untuk pengalaman development terbaik di Windows.

> [!NOTE]
> Jika sebelumnya `compose.yaml` Anda pernah dimodifikasi dengan "Named Volumes" sebagai quick fix performa, kembalikan ke setelan default (mount langsung `.:/var/www/html`). Di dalam WSL, mount langsung sudah sangat cepat, dan folder `vendor`/`node_modules` akan terlihat normal di VS Code.
