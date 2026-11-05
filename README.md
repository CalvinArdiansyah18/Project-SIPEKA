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
- **Laporan & rekapitulasi:** ekspor dan cetak rekap pengaduan.

## Teknologi yang Digunakan

| Kategori | Teknologi |
| :-- | :-- |
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Database | PostgreSQL |
| Tools | Git, GitHub, VS Code, Figma |

## Struktur Folder

```text
Project-SIPEKA/
│
├── backend/                         # Bagian backend (server-side)
│   │
│   ├── api/                          # Kumpulan API aplikasi
│   │   ├── auth/                     # API autentikasi (login, registrasi)
│   │   ├── laporan/                  # API pengelolaan laporan pengaduan
│   │   ├── mahasiswa/                # API khusus mahasiswa
│   │   └── unit/                     # API khusus pengelola unit
│   │
│   └── config/                       # Konfigurasi backend
│
├── DB/                               # Skema & dump database
│
├── frontend/                         # Bagian frontend (tampilan aplikasi)
│   │
│   ├── assets/                       # Berkas aset pendukung tampilan
│   │   │
│   │   ├── css/                      # Stylesheet untuk desain aplikasi
│   │   │
│   │   ├── images/                   # Penyimpanan gambar aplikasi
│   │   │   ├── backgrounds/          # Gambar latar belakang
│   │   │   └── logo/                 # Logo aplikasi
│   │   │
│   │   └── js/                       # Script JavaScript frontend
│   │       │
│   │       ├── core/                 # Fungsi inti aplikasi
│   │       │
│   │       ├── pages/                # Script khusus setiap halaman
│   │       │   ├── auth/             # Script halaman autentikasi
│   │       │   ├── mahasiswa/        # Script fitur mahasiswa
│   │       │   └── unit/             # Script fitur unit
│   │       │
│   │       └── shared/               # Script yang digunakan bersama
│   │
│   ├── content/                      # Halaman-halaman aplikasi
│   │   │
│   │   ├── auth/                     # Halaman login dan registrasi
│   │   │
│   │   ├── mahasiswa/                # Halaman khusus mahasiswa
│   │   │
│   │   └── unit/                     # Halaman khusus pengelola unit
│   │       │
│   │       ├── admin-prodi/          # Halaman administrator program studi
│   │       ├── kajur/                # Halaman ketua jurusan
│   │       ├── kps/                  # Halaman koordinator program studi
│   │       └── sekjur/               # Halaman sekretaris jurusan
│   │
│   └── includes/                     # Komponen tampilan yang digunakan berulang
│
└── README.md                         # Dokumentasi dan panduan proyek
```

## Instalasi & Menjalankan

### Prasyarat

- PHP >= 8.0 (ekstensi `pdo_pgsql` aktif)
- PostgreSQL
- Web server lokal (XAMPP / Laragon) atau PHP built-in server
- Git

### Langkah-langkah

```bash
# 1. Clone repository
git clone https://github.com/CalvinArdiansyah18/Project-SIPEKA.git
cd Project-SIPEKA

# 2. Buat database
createdb sipeka_db

# 3. Import skema database
psql -U postgres -d sipeka_db -f database/sipeka_db.sql
```

4. Sesuaikan koneksi database di `config/database.php` (host, nama database, username, password).
5. Sesuaikan base URL di `config/config.php`.
6. Jalankan aplikasi:

```bash
php -S localhost:8000
```

Lalu buka `http://localhost:8000` di browser.
