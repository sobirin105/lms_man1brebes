# 🏫 LMS MAN 1 Brebes
### Learning Management System — Madrasah Aliyah Negeri 1 Brebes

Aplikasi **Learning Management System (LMS)** berbasis web yang dirancang khusus untuk mendukung kegiatan belajar mengajar di MAN 1 Brebes. Aplikasi ini memudahkan Admin, Guru, dan Siswa dalam mengelola materi pembelajaran, tugas, kuis/ujian (CBT), nilai, presensi, dan pengumuman secara digital.

---

## 🚀 Fitur Utama

| Role | Fitur |
|------|-------|
| **Admin** | Manajemen pengguna, kelas, mata pelajaran, pengumuman, jadwal, dan pengaturan aplikasi |
| **Guru** | Upload materi, buat tugas & kuis (CBT), input nilai, kelola presensi, lihat jadwal mengajar |
| **Siswa** | Akses materi, kumpul tugas, ikuti kuis (CBT), lihat nilai & presensi, lihat jadwal pelajaran |

---

## 🛠️ Teknologi yang Digunakan

### Backend
- **PHP 8.2+**
- **Laravel 11** — Framework PHP utama
- **Laravel Sanctum** — Autentikasi berbasis token (API)
- **Laravel DomPDF** — Generate laporan PDF
- **Maatwebsite Excel** — Export data ke file Excel

### Frontend
- **Vue.js 3** — Framework JavaScript reaktif
- **Vuetify 3** — Komponen UI Material Design
- **Vue Router 4** — Navigasi SPA (Single Page Application)
- **Vite** — Build tool & dev server
- **Tailwind CSS 3** — Utility-first CSS framework
- **SweetAlert2** — Notifikasi & dialog interaktif
- **Axios** — HTTP client untuk request API

### Database
- **MySQL** — Database utama

---

## 📦 Aplikasi yang Dibutuhkan

Pastikan semua aplikasi berikut sudah terinstal di komputer Anda sebelum menjalankan program:

| Aplikasi | Versi Minimal | Keterangan |
|----------|--------------|------------|
| **PHP** | 8.2+ | Runtime backend |
| **Composer** | 2.x | Package manager PHP |
| **Node.js** | 18+ | Runtime JavaScript |
| **NPM** | 9+ | Package manager JavaScript (bawaan Node.js) |
| **MySQL** | 8.0+ | Database server |
| **Git** | 2.x | Version control |
| **Laragon / XAMPP / WAMP** | Terbaru | (Opsional) Web server lokal untuk kemudahan instalasi |

---

## ⚙️ Cara Menjalankan Program

### 1. Clone Repository
```bash
git clone https://github.com/sobirin105/lms_man1brebes.git
cd lms_man1brebes
```

### 2. Install Dependency PHP
```bash
composer install
```

### 3. Install Dependency JavaScript
```bash
npm install
```

### 4. Konfigurasi Environment
```bash
# Salin file .env contoh
cp .env.example .env

# Generate application key
php artisan key:generate
```

Kemudian edit file `.env` dan sesuaikan konfigurasi database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=lms_man1brebes
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Siapkan Database
```bash
# Buat database baru di MySQL dengan nama: lms_man1brebes
# Kemudian jalankan migrasi dan seeder

php artisan migrate
php artisan db:seed
```

### 6. Link Storage (untuk upload file)
```bash
php artisan storage:link
```

### 7. Jalankan Aplikasi

**Menggunakan Laragon:** Cukup letakkan folder proyek di dalam `C:\laragon\www\` dan aktifkan Laragon, lalu akses melalui `http://lms_man1brebes.test`.

**Menggunakan Terminal (Manual):**

Buka **dua terminal** secara bersamaan:

*Terminal 1 — Jalankan Backend Laravel:*
```bash
php artisan serve
```

*Terminal 2 — Jalankan Frontend Vite:*
```bash
npm run dev
```

Kemudian buka browser dan akses: **http://localhost:8000**

---

## 👤 Akun Default (Seeder)

Setelah menjalankan `db:seed`, akun berikut tersedia untuk login:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@man1brebes.sch.id | password |
| Guru | guru@man1brebes.sch.id | password |
| Siswa | siswa@man1brebes.sch.id | password |

---

## 📂 Struktur Folder Penting

```
lms_man1brebes/
├── app/
│   ├── Http/Controllers/Api/   # Controller API backend
│   └── Models/                 # Model database
├── database/
│   ├── migrations/             # Skema tabel database
│   └── seeders/                # Data awal database
├── resources/
│   ├── js/
│   │   ├── components/         # Komponen Vue (Admin, Guru, Siswa)
│   │   └── router/             # Konfigurasi routing Vue
│   └── css/                    # File styling
├── routes/
│   └── api.php                 # Definisi route API
└── public/                     # Asset publik & entry point
```

---

## 📄 Lisensi

Proyek ini dikembangkan untuk keperluan internal **MAN 1 Brebes**. Hak cipta dilindungi.

---

<p align="center">Dikembangkan dengan ❤️ untuk MAN 1 Brebes</p>
