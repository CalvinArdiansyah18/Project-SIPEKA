# Kamus Data SIPEKA (PostgreSQL)

Tipe ENUM:
- `role_user` = `mahasiswa`, `unit`
- `status_laporan` = `menunggu`, `dalam_penanganan`, `selesai`, `ditolak`

## 1. unit
Unit penanggung jawab (Sekjur, Admin Prodi, Kajur, KPS).

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_unit | SMALLINT (identity) | PK | ID unit |
| nama_unit | VARCHAR(50) | NOT NULL, UNIQUE | Nama unit |

## 2. kategori
Lima kategori pengaduan; tiap kategori dipetakan ke satu unit (dasar *direct routing*).

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_kategori | SMALLINT (identity) | PK | ID kategori |
| id_unit | SMALLINT | NOT NULL, FK → unit | Unit penanggung jawab |
| nama_kategori | VARCHAR(60) | NOT NULL, UNIQUE | Nama kategori |
| deskripsi | TEXT | – | Penjelasan kategori |

## 3. users
Akun login mahasiswa dan petugas unit.

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_user | INT (identity) | PK | ID akun |
| id_unit | SMALLINT | FK → unit, NULL untuk mahasiswa | Unit asal petugas |
| nama | VARCHAR(100) | NOT NULL | Nama pengguna |
| email | VARCHAR(100) | NOT NULL, UNIQUE, CHECK format email | Email login |
| password_hash | VARCHAR(255) | NOT NULL | Hash kata sandi (bcrypt/argon2) |
| role | role_user | NOT NULL | Peran akun |
| created_at | TIMESTAMPTZ | NOT NULL, default now() | Waktu dibuat |

CHECK `ck_users_role_unit`: `mahasiswa` ⇒ `id_unit` NULL; `unit` ⇒ `id_unit` terisi.

## 4. mahasiswa
Profil tambahan akun bermahasiswa (1 user : 0..1 mahasiswa).

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_mahasiswa | INT (identity) | PK | ID mahasiswa |
| id_user | INT | NOT NULL, UNIQUE, FK → users (CASCADE) | Akun terkait |
| nim | VARCHAR(15) | NOT NULL, UNIQUE, CHECK 8–15 digit | NIM |
| program_studi | VARCHAR(60) | NOT NULL | Program studi |
| nomor_telepon | VARCHAR(20) | CHECK format +62/0xxx | Kontak (opsional) |

## 5. laporan
Entitas inti pengaduan.

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_laporan | BIGINT (identity) | PK | ID laporan |
| id_mahasiswa | INT | NOT NULL, FK → mahasiswa | Pelapor |
| id_kategori | SMALLINT | NOT NULL, FK → kategori | Kategori (unit diturunkan darinya) |
| judul_laporan | VARCHAR(150) | NOT NULL, CHECK ≥ 5 karakter | Judul |
| lokasi | VARCHAR(150) | – | Lokasi kejadian (opsional) |
| tanggal_kejadian | DATE | NOT NULL | Tanggal kejadian |
| kronologi | TEXT | NOT NULL, CHECK ≥ 20 karakter | Uraian kejadian |
| status | status_laporan | NOT NULL, default `menunggu` | Status terkini (diisi trigger) |
| tanggal_lapor | TIMESTAMPTZ | NOT NULL, default now() | Waktu laporan dikirim |
| updated_at | TIMESTAMPTZ | NOT NULL, default now() | Waktu perubahan terakhir (diisi trigger) |

## 6. bukti_laporan

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_bukti | BIGINT (identity) | PK | ID bukti |
| id_laporan | BIGINT | NOT NULL, FK → laporan (CASCADE) | Laporan terkait |
| nama_file | VARCHAR(255) | NOT NULL | Nama berkas |
| path_file | VARCHAR(500) | NOT NULL, UNIQUE | Lokasi penyimpanan |
| jenis_file | VARCHAR(100) | NOT NULL, CHECK jpeg/png/pdf | MIME type |
| uploaded_at | TIMESTAMPTZ | NOT NULL, default now() | Waktu unggah |

## 7. riwayat_status

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_riwayat | BIGINT (identity) | PK | ID riwayat |
| id_laporan | BIGINT | NOT NULL, FK → laporan (CASCADE) | Laporan terkait |
| id_user | INT | NOT NULL, FK → users | Pengubah status |
| status | status_laporan | NOT NULL | Status baru |
| tanggal_perubahan | TIMESTAMPTZ | NOT NULL, default now() | Waktu perubahan |
| keterangan | TEXT | – | Catatan perubahan |

## 8. tanggapan

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_tanggapan | BIGINT (identity) | PK | ID tanggapan |
| id_laporan | BIGINT | NOT NULL, FK → laporan (CASCADE) | Laporan terkait |
| id_user | INT | NOT NULL, FK → users; divalidasi trigger | Petugas penanggap |
| isi_tanggapan | TEXT | NOT NULL, tidak kosong | Isi tanggapan |
| tanggal_tanggapan | TIMESTAMPTZ | NOT NULL, default now() | Waktu tanggapan |

## 9. penanganan

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_penanganan | BIGINT (identity) | PK | ID penanganan |
| id_laporan | BIGINT | NOT NULL, FK → laporan (CASCADE) | Laporan terkait |
| id_user | INT | NOT NULL, FK → users; divalidasi trigger | Petugas penangan |
| keterangan_penanganan | TEXT | NOT NULL, tidak kosong | Uraian penanganan |
| tanggal_penanganan | TIMESTAMPTZ | NOT NULL, default now() | Waktu penanganan |

## 10. bukti_penanganan

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id_bukti | BIGINT (identity) | PK | ID bukti |
| id_penanganan | BIGINT | NOT NULL, FK → penanganan (CASCADE) | Penanganan terkait |
| nama_file | VARCHAR(255) | NOT NULL | Nama berkas |
| path_file | VARCHAR(500) | NOT NULL, UNIQUE | Lokasi penyimpanan |
| jenis_file | VARCHAR(100) | NOT NULL, CHECK jpeg/png/pdf | MIME type |
| uploaded_at | TIMESTAMPTZ | NOT NULL, default now() | Waktu unggah |

## Objek lanjutan

| Objek | Jenis | Kegunaan |
|---|---|---|
| fn_riwayat_awal | Trigger (AFTER INSERT laporan) | Membuat riwayat awal `menunggu` otomatis |
| fn_sinkron_status_laporan | Trigger (AFTER INSERT riwayat_status) | Menyinkronkan `laporan.status` dan `updated_at` |
| fn_validasi_unit_penanggung_jawab | Trigger (BEFORE INSERT tanggapan/penanganan) | Hanya petugas unit penanggung jawab yang boleh menulis |
| sp_ubah_status | Stored procedure | Ubah status dalam satu transaksi: validasi unit, alur status, syarat penanganan sebelum `selesai` |
| v_laporan_unit | View | Daftar laporan masuk di dashboard unit |
| v_rekap_unit | View | Rekap dan statistik pengaduan per unit |
