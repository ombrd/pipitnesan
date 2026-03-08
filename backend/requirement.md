# Software Requirements Document (SRS) - Gym Pipitnesan Management System

---

## 1. Project Overview

Sistem Manajemen Gym Pipitnesan adalah sistem digital terintegrasi yang dikembangkan untuk menggantikan pencatatan manual dan sistem tidak terintegrasi dalam pengelolaan keanggotaan, pembayaran, absensi, serta penjadwalan Personal Trainer.

Sistem terdiri dari:
- Web CMS (Back-office)
- REST API
- Mobile Application (Android & iOS)

### Objective
Membangun sistem manajemen gym yang:
- Terintegrasi (Web + API + Mobile)
- Menggunakan QR dinamis untuk absensi
- Mendukung booking Personal Trainer
- Aman, scalable, dan berjalan pada perangkat spesifikasi rendah

### Target Audience
- Administrator Gym
- Kasir
- Manager / Branch Manager
- Member Gym

### Platform Scope
- Web CMS (Laravel + Filament)
- REST API (Laravel Sanctum)
- Mobile Apps (React Native – Android & iOS)

---

## 2. User Personas

### 2.1 Administrator (Web CMS)
- Mengelola cabang, member, personal trainer, promosi
- Mengatur jadwal Personal Trainer
- Melihat laporan pembayaran
- Monitoring member aktif & jatuh tempo
- Monitoring booking PT

### 2.2 Kasir (Web CMS)
- Input pembayaran member
- Mengaktifkan masa keanggotaan
- Melihat laporan pembayaran harian

### 2.3 Manager / Branch Manager (Web CMS)
- Melihat laporan total member aktif
- Monitoring pendapatan harian
- Monitoring member jatuh tempo
- Monitoring performa PT booking

### 2.4 End User / Member (Mobile App)
- Login menggunakan nomor member
- Generate QR absensi (dinamis)
- Booking Personal Trainer
- Melihat jadwal booking
- Melihat status keanggotaan

---

## 3. System Architecture

### High-Level Architecture

Mobile App  
⬇ HTTPS  
Laravel Backend (Web + API)  
⬇  
PostgreSQL Database  

### Technology Stack

**Frontend (Web CMS):**
- Laravel Blade
- Filament Admin Panel

**Backend/API:**
- Laravel 11/12
- Laravel Sanctum (Token Authentication)
- RESTful API

**Mobile:**
- React Native (Android & iOS)

**Database:**
- PostgreSQL 15

**Deployment:**
- Docker (Nginx + PHP-FPM + PostgreSQL)
- Makefile orchestration

---

## 4. Functional Requirements

---

### 4.1 Web CMS (Back-office)

| ID | Requirement | Description |
|----|-------------|------------|
| F-CMS-01 | Login/Authentication | Secure login dengan Role-Based Access Control (Admin, Kasir, Manager). |
| F-CMS-02 | Dashboard | Ringkasan: total member aktif, pembayaran hari ini, booking PT hari ini. |
| F-CMS-03 | Branch Management | CRUD data cabang (kode otomatis mulai 100). |
| F-CMS-04 | Account Officer Management | CRUD AO dengan kode otomatis per cabang. |
| F-CMS-05 | Personal Trainer Management | CRUD PT dengan status aktif/tidak aktif. |
| F-CMS-06 | Promotion Management | CRUD paket promosi dan biaya member. |
| F-CMS-07 | Member Management | CRUD member, generate nomor member otomatis 14 digit. |
| F-CMS-08 | Payment Processing | Input pembayaran dan aktivasi masa aktif member. |
| F-CMS-09 | Daily Payment Report | Laporan detail & total penerimaan kasir per hari. |
| F-CMS-10 | Expired Member Monitoring | Monitoring member jatuh tempo 7 hari & 1 bulan ke depan. |
| F-CMS-11 | PT Schedule Management | Admin dapat membuat jadwal PT berdasarkan tanggal, jam, dan quota. |
| F-CMS-12 | PT Booking Monitoring | Monitoring booking PT dan status slot (OPEN/FULL). |
| F-CMS-13 | Activity Log | Sistem mencatat aktivitas user secara real-time. |

---

### 4.2 Backend API

| ID | Requirement | Description |
|----|-------------|------------|
| F-API-01 | Authentication | Token-based authentication menggunakan Laravel Sanctum. |
| F-API-02 | Member Login | Login menggunakan nomor member aktif. |
| F-API-03 | QR Generation | Generate QR token dinamis berlaku 3–5 menit. |
| F-API-04 | QR Validation | Validasi QR saat scan dan pencatatan kehadiran. |
| F-API-05 | PT List Endpoint | Endpoint daftar PT aktif. |
| F-API-06 | PT Schedule Endpoint | Endpoint jadwal PT per tanggal. |
| F-API-07 | PT Booking Endpoint | Booking slot PT dengan validasi quota dan overlap. |
| F-API-08 | Booking History | Endpoint riwayat booking member. |
| F-API-09 | Concurrency Control | Mencegah double booking melalui database transaction. |
| F-API-10 | API Documentation | Dokumentasi API menggunakan Swagger/OpenAPI. |

---

### 4.3 Mobile Application

| ID | Requirement | Description |
|----|-------------|------------|
| F-MOB-01 | Login | Login menggunakan nomor member aktif. |
| F-MOB-02 | QR Absensi | Generate QR dinamis untuk akses masuk gym. |
| F-MOB-03 | Booking PT | Member memilih PT, tanggal, dan jam untuk booking. |
| F-MOB-04 | Cancel Booking | Member dapat membatalkan booking sesuai aturan waktu. |
| F-MOB-05 | Booking History | Melihat riwayat dan status booking. |
| F-MOB-06 | Membership Status | Melihat masa aktif dan tanggal berakhir member. |
| F-MOB-07 | Session Timeout | Logout otomatis jika token expired. |

---

## 5. Non-Functional Requirements

### 5.1 Security
- HTTPS mandatory.
- Password hashing menggunakan bcrypt.
- Token expiration.
- Role-Based Access Control (RBAC).
- Logging aktivitas sistem.
- Validasi input untuk mencegah SQL Injection.
- Kepatuhan terhadap UU Perlindungan Data Pribadi.

---

### 5.2 Performance
- API response time < 500ms untuk query standar.
- QR generation < 1 detik.
- Booking PT transaction-safe < 2 detik.
- Optimasi database dengan indexing.
- Berjalan pada PC RAM ≥ 4GB dan HP RAM ≥ 2GB.

---

### 5.3 Scalability
- Mendukung minimal 300–500 member aktif.
- Mendukung hingga 50 concurrent booking requests.
- Arsitektur containerized untuk scaling horizontal.

---

### 5.4 Reliability
- Database backup harian.
- Transaction rollback pada error booking.
- Logging untuk audit trail.

---

## 6. UI/UX Design References

### Design Concept
- Clean & minimal
- Dominasi warna:
  - Hitam (fitness identity)
  - Merah (energi)
  - Putih (clean interface)

### Web CMS
- Menggunakan Filament Admin UI
- Layout tabel data + filter
- Dashboard statistik sederhana

### Mobile App
- Bottom navigation
- QR full screen mode
- Calendar-based PT booking

---

# End of Document