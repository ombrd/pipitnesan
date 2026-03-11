# Pipitnesan Gym Studio (Backend)

Ini adalah *source code* backend untuk sistem manajemen gym Pipitnesan. Aplikasi ini dibangun di atas kerangka kerja **Laravel 11** dengan PHP 8.4, PostgreSQL, dan antarmuka panel admin berbasis **Filament v3**.

Sistem sudah sepenuhnya dikemas ke dalam *container* menggunakan **Docker (Laravel Sail)** untuk menjamin konsistensi di setiap lingkungan pengembang (*development environment*).

---

## 🛠 Prasyarat (*Prerequisites*)

Sebelum memulai instalasi, pastikan sistem operasi Anda (Mac/Linux/Windows) sudah terpasang:
1. **Docker Desktop** (Pastikan mesin Docker sudah dalam keadaan *Running* sebelum melanjutkan).
2. **Ngrok Account** (Opsional, untuk mempublikasikan API ke publik agar bisa diakses aplikasi *mobile*).

---

## 🚀 Panduan Instalasi & Menjalankan Aplikasi (Lokal)

Tidak perlu menginstal PHP atau Composer di komputer lokal Anda, semuanya akan ditangani oleh Docker. Ikuti langkah sederhana berikut:

### 1. Persiapkan Lingkungan Lokal & Vendor

Karena folder `vendor` tidak disertakan di dalam repositori Git, dan Anda mungkin belum menginstal PHP secara lokal, kami mempermudahnya dengan perintah `make`. Buka terminal di direktori proyek ini dan jalankan:

```bash
make setup
```

*(Catatan untuk pengguna Windows: Anda dapat menjalankan perintah yang sama di Command Prompt karena kami telah menyediakan file `make.bat` pengganti Makefile).*

Perintah di atas akan secara otomatis menyalin `.env.example` ke `.env` (jika belum ada), melakukan intalasi **Composer dependencies**, men-_generate_ APP_KEY, menjalankan migrasi database otomatis, serta mem-_build_ aset *frontend* Vite secara instan menggunakan bantuan Docker.

### 2. Nyalakan Docker Container (Sail)

Setelah proses setup dan instalasi `vendor` selesai, jalankan perintah berikut untuk menghidupkan server PHP 8.4 dan Database PostgreSQL 18:

```bash
make up
```

### 3. Otomasi Database (Migrasi & Seeder)
Anda **TIDAK PERLU** menjalankan proses migrasi database (`php artisan migrate`) secara manual. 
Sistem ini sudah dikonfigurasi melalui *startup script* Docker (`start-container`). Saat kontainer pertama kali menyala, Laravel otomatis akan mengeksekusi migrasi database dan melakukan *Seeding* data awal.

---

## 💻 Mengakses Aplikasi

Setelah proses instalasi selesai, Panel Admin Filament bisa diakses melalui browser di:

* **URL Panel Admin:** [http://localhost/admin](http://localhost/admin)

### 🔑 Kredensial Login Awal (Default)
Gunakan kredensial ini untuk menguji akses sistem. *Role* dan *Permissions* (Dynamic RBAC) telah di-*seeding* otomatis:

| Role | Email | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@admin.com` | `password` | Akses penuh, termasuk mengatur hak akses menu (*Resource* RBAC). |
| **Manager** | `manager@gym.com` | `password` | Hanya bisa mengakses menu tertentu sesuai matriks persetujuan. |
| **Kasir** | `kasir@gym.com` | `password` | Akses sangat terbatas (Melihat tarif, membuat transaksi, dll). |

*(Untuk mengubah batas hak akses spesifik Admin/Manager/Kasir, Super Admin dapat melakukannya melalui menu **Shield -> Roles**).*

---

## 🌐 Menjalankan Aplikasi Secara Publik (Menggunakan Ngrok)

Jika Anda perlu menautkan backend ini ke aplikasi Mobile / Frontend lain melalui internet, Anda bisa melakukan *port forwarding* lokal ke IP publik menggunakan layanan Ngrok, yang sudah didukung secara *native* oleh Laravel Sail.

### 1. Jalankan Ngrok Tunnel
Di terminal baru (pastikan kontainer backend masih menyala), jalankan perintah _make_ ini:
```bash
make share
```

Proses ini akan menghasilkan sebuah tautan publik, contohnya:
`https://1a2b-3c4d-5e6f.ngrok-free.app`

### 2. Update .env (Penting!)
Pastikan Anda mengubah atau mengecek `APP_URL` di dalam file `.env` sistem Anda menjadi URL publik ngrok tersebut agar semua _asset_, _image hook_, dan _routing_ email bisa berfungsi dengan _endpoint_ yang benar jika dibutuhkan integrasi lintas *platform*:
```env
APP_URL=https://1a2b-3c4d-5e6f.ngrok-free.app
```

> **Untuk menghentikan _container_** Laravel Sail, Anda cukup menjalankan perintah:
> `make down`
