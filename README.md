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

## Struktur Folder Proyek

Berikut adalah gambaran struktur direktori dan berkas utama dalam proyek **SIPEKA**:

```text
Project-SIPEKA/
├── assets/                  # Berkas statis (CSS, JS, Gambar, Vendor)
│   ├── css/                 # Stylesheet / Tailwind / Bootstrap
│   ├── js/                  # Scripts & Logika Frontend
│   └── images/              # Gambar & Logo Aplikasi
├── config/                  # Pengaturan & Koneksi Database
│   ├── database.php         # Konfigurasi Koneksi MySQL
│   └── config.php           # Pengaturan Aplikasi & URL Base
├── controllers/             # Alur Logika Pemrosesan (Controllers)
├── models/                  # Pengelolaan Logika Data (Models)
├── views/                   # Tampilan Antarmuka Pengguna (Views)
│   ├── admin/               # Halaman Dashboard Admin
│   ├── user/                # Halaman Antarmuka Pengguna
│   └── templates/           # Header, Footer, Sidebar Modul
├── database/                # Skema & Berkas SQL Database
│   └── sipeka_db.sql        # Database Dump
├── uploads/                 # Folder Penyimpanan Berkas/Foto Terunggah
├── .gitignore               # Berkas yang Diabaikan oleh Git
├── index.php                # Berkas Utama / Entry Point Aplikasi
└── README.md                # Dokumentasi Proyek
```

---


## Fitur Utama

* **Autentikasi & Manajemen Pengguna:** Pengaturan hak akses berbasis peran (Pengguna, Unit/Petugas per kasus.)
* **Dashboard Interaktif:** Visualisasi ringkasan data dan statistik secara *real-time*.
* **Manajemen Data Utama (Master Data):** Kemudahan dalam menambah, mengubah, menghapus, dan mencari data.
* **Pencatatan Transaksi / Pelayanan:** Alur kerja sistematis untuk pemrosesan data pelayanan.
* **Laporan & Rekapitulasi:** Ekspor dan pencetakan rekap data laporan secara akurat.

---
