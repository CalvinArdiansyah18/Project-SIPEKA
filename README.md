# SIPEKA: Sistem Pengaduan Kampus (Khusus Jurusan JTI)

**SIPEKA** adalah platform pengaduan berbasis web yang dirancang khusus untuk lingkungan Jurusan Teknologi Informasi (JTI) Politeknik Negeri Malang. Sistem ini memfasilitasi mahasiswa untuk menyampaikan aspirasi atau keluhan secara terstruktur, yang kemudian secara otomatis didisposisikan ke unit berwenang (Sekretaris Jurusan, Admin Prodi, Ketua Jurusan, dan KPS) tanpa melalui admin utama.

Proyek ini menggunakan arsitektur **Decoupled/Separated** yang memisahkan modul Frontend (*client-side*) dan Backend (*server-side*) untuk efisiensi pengembangan dan integrasi tim.

---

| No | Nama | NIM | Peran Utama di Proyek |
| :--- | :--- | :--- | :--- |
| 1 | **Salsabila Widyadhana** | 254107020200 | Project Manager (Ketua Tim) merangkap Dokumentator |
| 2 | **Calvin Ardiansyah** | 254107020214 | Backend Developer |
| 3 | **Ghafin Fitrah Ramadhan** | 254107020170 | UI/UX Designer |
| 4 | **Galih Maula Mumtaza** | 254107020095 | Frontend Developer |

---

## Fitur Utama

- **Autentikasi & hak akses berbasis peran:** mahasiswa (pengguna) dan unit/petugas (Sekjur, Admin Prodi, Kajur, KPS).
- **Pengajuan pengaduan:** mahasiswa mengirim keluhan atau aspirasi lengkap dengan kategori dan lampiran foto/berkas.
- **Disposisi otomatis:** pengaduan langsung diteruskan ke unit yang berwenang tanpa perantara admin utama.
- **Pelacakan status:** mahasiswa dapat memantau progres dan tanggapan atas pengaduannya.
- **Dashboard interaktif:** ringkasan dan statistik pengaduan.
- **Ulasan & tanggapan:** mahasiswa memberi ulasan, unit membalas.
- **Notifikasi:** pemberitahuan perubahan status dan balasan.
- **Laporan & rekapitulasi:** ekspor dan cetak rekap pengaduan.

## Teknologi yang Digunakan

| Kategori | Teknologi |
| :-- | :-- |
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP (PDO) |
| Database | PostgreSQL (rencana: Supabase) |
| Tools | Git, GitHub, VS Code, Figma |

## Struktur Folder

```text
Project-SIPEKA/
│
├── index.php                         # Redirect ke frontend/landing-page.php
│
├── backend/                          # Bagian backend (server-side)
│   │
│   ├── api/                          # Kumpulan endpoint API (JSON)
│   │   ├── auth/                     # login, registrasi, logout, profil, ganti-sandi, validasi
│   │   ├── laporan/                  # create, list, detail, kategori, bukti
│   │   ├── mahasiswa/                # dashboard
│   │   ├── notifikasi/               # list, baca
│   │   ├── ulasan/                   # list, saya, balas, laporan-saya
│   │   └── unit/                     # dashboard, detail, validasi, tanggapan, selesai
│   │
│   ├── config/                       # Konfigurasi & helper bersama
│   │   ├── bootstrap.php             # session, error handler JSON, json_out/json_in/butuh_login
│   │   ├── koneksi.php               # koneksi PDO PostgreSQL
│   │   ├── laporan-helper.php        # helper laporan & upload berkas
│   │   └── ulasan-helper.php         # helper ulasan
│   │
│   └── uploads/                      # Berkas bukti (tidak diakses langsung; via API)
│       ├── .htaccess                 # Blokir akses langsung & eksekusi PHP
│       ├── laporan/
│       └── penanganan/
│
├── DB/                               # Skema & dump database
│   ├── sipeka_postgresql.sql         # Skema lengkap
│   ├── migrasi_001_izinkan_webp.sql  # Migrasi bertahap
│   └── sipeka_kamus_data.md          # Kamus data
│
├── frontend/                         # Bagian frontend (tampilan aplikasi)
│   │
│   ├── landing-page.php              # Halaman depan publik
│   │
│   ├── assets/                       # Berkas aset pendukung tampilan
│   │   ├── css/                      # base, layout, components, landing, themes, modal,
│   │   │                             #   utilities, auth, profile, riwayat, ulasan
│   │   ├── images/                   # Gambar aplikasi
│   │   │   ├── backgrounds/
│   │   │   └── logo/
│   │   └── js/                       # Script JavaScript frontend
│   │       ├── core/                 # store, ui, api, notifikasi (inti)
│   │       ├── pages/                # Script khusus setiap halaman
│   │       │   ├── auth/
│   │       │   ├── mahasiswa/
│   │       │   └── unit/
│   │       ├── shared/               # helpers lintas halaman
│   │       └── main.js               # router + penjaga sesi (dimuat terakhir)
│   │
│   ├── content/                      # Halaman-halaman aplikasi
│   │   ├── auth/                     # login, register
│   │   ├── mahasiswa/                # dashboard, kategori, buat-laporan, riwayat, profil, ulasan
│   │   └── unit/                     # Halaman pengelola unit
│   │       ├── admin-prodi/
│   │       ├── kajur/
│   │       ├── kps/
│   │       └── sekjur/
│   │
│   └── includes/                     # Komponen tampilan yang digunakan berulang
│       ├── styles.php                # Daftar stylesheet terpusat
│       ├── scripts.php               # Daftar script terpusat (per halaman)
│       ├── auth-guard.php            # Penjaga sesi sisi server
│       ├── ulasan-content.php        # Markup bersama halaman ulasan
│       └── unit/                     # Partial layout halaman unit
│           ├── config.php            # Identitas tiap unit
│           ├── layout-start.php      # head, topbar, sidebar, header
│           └── layout-end.php        # penutup layout + script
│
└── README.md                         # Dokumentasi proyek
```

## Arsitektur Backend

Seluruh endpoint mengembalikan JSON dan dipanggil frontend melalui `fetch`
(lihat `frontend/assets/js/core/api.js`).

### bootstrap.php

Di-require di baris pertama setiap endpoint. Menyediakan:

- `APP_DEBUG` — diaktifkan lewat env `SIPEKA_DEBUG=1` (bawaan: mati).
- `set_exception_handler` — semua error dijawab JSON (bukan HTML).
- `session_start()` dengan cookie `httponly` + `samesite=Lax`.
- `json_out($data, $code)` — kirim JSON lalu `exit`.
- `json_in()` — baca body JSON.
- `butuh_login($role)` — wajib login, opsional batasi peran `mahasiswa`/`unit`.
- `$pdo` — koneksi database dari `koneksi.php`.

### Konvensi endpoint

- Nama file = aksi (`create.php`, `list.php`, `detail.php`, dst.).
- Identitas pengguna selalu diambil dari `$_SESSION['user']`, **bukan** dari input frontend.
- Semua query memakai prepared statement.
- Berkas unggahan divalidasi dari isi (bukan ekstensi/nama), disimpan dengan nama acak,
  lalu disajikan lewat `api/laporan/bukti.php` setelah pengecekan hak akses.

### Daftar endpoint

**auth/**

| Endpoint | Method | Keterangan |
| :-- | :-- | :-- |
| `auth/login.php` | POST | Login (email atau NIM + password). |
| `auth/registrasi.php` | POST | Registrasi akun mahasiswa. |
| `auth/logout.php` | POST | Hapus sesi. |
| `auth/profil.php` | GET | Profil pengguna yang sedang login. |
| `auth/ganti-sandi.php` | POST | Ganti kata sandi. |
| `auth/validasi.php` | GET | Cek status sesi/peran. |

**laporan/**

| Endpoint | Method | Keterangan |
| :-- | :-- | :-- |
| `laporan/create.php` | POST | Buat laporan (multipart, `bukti[]` opsional). |
| `laporan/list.php` | GET | Daftar laporan milik mahasiswa. |
| `laporan/detail.php` | GET | Detail laporan milik mahasiswa (`?tiket=`). |
| `laporan/kategori.php` | GET | Daftar kategori & unit tujuan. |
| `laporan/bukti.php` | GET | Sajikan berkas bukti (`?id=`), dengan cek hak akses. |

**mahasiswa/**

| Endpoint | Method | Keterangan |
| :-- | :-- | :-- |
| `mahasiswa/dashboard.php` | GET | Ringkasan & statistik laporan mahasiswa. |

**unit/**

| Endpoint | Method | Keterangan |
| :-- | :-- | :-- |
| `unit/dashboard.php` | GET | Daftar laporan masuk untuk unit (dari session). |
| `unit/detail.php` | GET | Detail laporan untuk unit (`?tiket=`). |
| `unit/validasi.php` | POST | Setujui/tolak laporan. |
| `unit/tanggapan.php` | POST | Kirim tanggapan ke pelapor. |
| `unit/selesai.php` | POST | Tandai laporan selesai (multipart, bukti opsional). |

**notifikasi/**

| Endpoint | Method | Keterangan |
| :-- | :-- | :-- |
| `notifikasi/list.php` | GET | Daftar notifikasi pengguna. |
| `notifikasi/baca.php` | POST | Tandai notifikasi dibaca. |

**ulasan/**

| Endpoint | Method | Keterangan |
| :-- | :-- | :-- |
| `ulasan/list.php` | GET | Daftar ulasan (publik/berfilter). |
| `ulasan/saya.php` | GET | Ulasan milik pengguna. |
| `ulasan/balas.php` | POST | Balas ulasan. |
| `ulasan/laporan-saya.php` | GET | Laporan yang bisa diulas pengguna. |

### Format error

Selalu JSON: `{ "message": "..." }` dengan status HTTP yang sesuai
(400 validasi, 401 belum login, 403 tidak diizinkan, 404 tidak ditemukan,
413/415 berkas, 500 server).

## Arsitektur Frontend

Halaman PHP hanya berisi markup; data diambil dari API, sehingga tidak ada query
database di sisi halaman.

### Memuat aset

Jangan menulis `<link>`/`<script>` manual di halaman. Gunakan:

```php
<?php include __DIR__ . '/../../includes/styles.php'; ?>   <!-- di <head> -->
<?php include __DIR__ . '/../../includes/scripts.php'; ?>  <!-- sebelum </body> -->
```

- `styles.php` memuat seluruh stylesheet dengan cache-busting `?v=filemtime`.
- `scripts.php` memuat skrip inti di semua halaman dan **skrip halaman hanya pada
  halaman yang membutuhkannya** (ditentukan dari nama berkas yang dieksekusi),
  dengan `main.js` (router) selalu terakhir.

### Penjaga sesi

Halaman terlindungi menyertakan `includes/auth-guard.php` di baris paling atas,
sebelum output apa pun:

```php
<?php
$authRole = 'mahasiswa'; // atau 'unit'
require __DIR__ . '/../../includes/auth-guard.php';
?>
```

Halaman unit sudah otomatis dijaga lewat `includes/unit/layout-start.php`.
`main.js` tetap melakukan pengecekan tambahan di sisi klien untuk pengalihan UX.

### Halaman unit

Keempat unit (admin-prodi, kajur, kps, sekjur) memakai partial layout yang sama:

```php
<?php
$unitKey    = 'sekjur';
$pageTitle  = 'Dashboard Sekjur - SIPEKA Polinema';
$breadcrumb = 'Portal Penanganan Unit Resmi &rsaquo; <strong>Sekretariat Jurusan</strong>';
$activeMenu = 'dashboard'; // dashboard | ulasan | profil
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>
   <!-- konten halaman -->
<?php include __DIR__ . '/../../../includes/unit/layout-end.php'; ?>
```

Identitas tiap unit (label sidebar, topbar, breadcrumb portal) ada di
`includes/unit/config.php`.

## Database

Skema database dan migrasi tersimpan di folder `DB/`:

- `DB/sipeka_postgresql.sql` — skema lengkap.
- `DB/migrasi_001_izinkan_webp.sql` — migrasi bertahap (dijalankan berurutan).
- `DB/sipeka_kamus_data.md` — kamus data.

Rencana ke depan: database dipindahkan ke **Supabase** (PostgreSQL terkelola),
sehingga koneksi disesuaikan pada `backend/config/koneksi.php`.
