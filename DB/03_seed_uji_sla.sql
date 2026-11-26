-- =====================================================================
-- SIPEKA - SEED DATA UJI FITUR SLA / "LAPORAN TENGGELAM"
-- PostgreSQL 12+
--
-- Tujuan : mengisi data untuk menguji peringatan visual SLA 3x24 jam
--          (badge merah/kuning, baris tersorot, kartu "Perlu Perhatian",
--          filter ?perhatian=1) yang dipakai endpoint:
--            - backend/api/unit/dashboard.php
--            - backend/api/mahasiswa/dashboard.php
--            - backend/api/laporan/list.php
--
-- Aturan yang diuji (lihat backend/config/laporan-helper.php):
--   * SLA = 72 jam (3x24 jam) kalender sejak tanggal_lapor.
--   * sla_status : aman | mendekati (>54 jam) | terlambat (>72 jam) | selesai.
--   * Waktu acuan = aktivitas terakhir unit (tanggapan/penanganan),
--     fallback tanggal_lapor.
--   * perlu_perhatian :
--       - menunggu          -> lewat SLA DAN belum ada tanggapan
--       - dalam_penanganan  -> lewat SLA (tanpa aktivitas baru)
--       - selesai/ditolak   -> tidak pernah perlu perhatian
--
-- Prasyarat (jalankan berurutan lebih dulu):
--   * DB/01_skema_gabungan.sql  (skema + semua migrasi)
--   * DB/02_migrasi_webp.sql    (izinkan berkas WEBP)
--   * Ekstensi pgcrypto tersedia (untuk hash password akun uji).
--
-- Cara pakai:
--   psql -U postgres -h localhost -d db_sipeka -f DB/03_seed_uji_sla.sql
--
-- Akun uji (dibuat oleh skrip ini):
--   Unit     : sekjur@test.local      / Password123!   (Sekjur)
--   Mahasiswa: mhs@test.local         / Password123!   (NIM 254107029999)
--
-- Skrip aman dijalankan berulang (data uji lama dihapus lebih dulu).
-- =====================================================================

BEGIN;

-- ---------------------------------------------------------------------
-- 0. PERSIAPAN
-- ---------------------------------------------------------------------
CREATE EXTENSION IF NOT EXISTS pgcrypto;

-- Hapus data uji sebelumnya (urutan penting: laporan dulu, lalu akun).
DELETE FROM laporan WHERE judul_laporan LIKE 'TES SLA %';

DELETE FROM users
 WHERE email IN ('sekjur@test.local', 'mhs@test.local');

-- Kategori uji: hapus hanya jika tidak dipakai laporan lain.
DELETE FROM kategori k
 WHERE k.nama_kategori = 'Uji SLA'
   AND NOT EXISTS (SELECT 1 FROM laporan l WHERE l.id_kategori = k.id_kategori);

-- ---------------------------------------------------------------------
-- 1. UNIT & KATEGORI UJI
-- ---------------------------------------------------------------------
INSERT INTO unit (nama_unit)
VALUES ('Sekjur')
ON CONFLICT (nama_unit) DO NOTHING;

INSERT INTO kategori (id_unit, nama_kategori, deskripsi)
VALUES (
    (SELECT id_unit FROM unit WHERE nama_unit = 'Sekjur'),
    'Uji SLA',
    'Kategori khusus pengujian fitur SLA (boleh dihapus setelah uji)'
);

-- ---------------------------------------------------------------------
-- 2. AKUN UJI (password: Password123!)
--    crypt(..., gen_salt('bf', 12)) menghasilkan bcrypt $2a$ yang
--    kompatibel dengan password_verify() di PHP.
-- ---------------------------------------------------------------------
INSERT INTO users (id_unit, nama, email, password_hash, role)
VALUES (
    (SELECT id_unit FROM unit WHERE nama_unit = 'Sekjur'),
    'Petugas Sekjur Uji',
    'sekjur@test.local',
    crypt('Password123!', gen_salt('bf', 12)),
    'unit'
);

INSERT INTO users (nama, email, password_hash, role)
VALUES (
    'Mahasiswa Uji',
    'mhs@test.local',
    crypt('Password123!', gen_salt('bf', 12)),
    'mahasiswa'
);

INSERT INTO mahasiswa (id_user, nim, program_studi, nomor_telepon)
VALUES (
    (SELECT id_user FROM users WHERE email = 'mhs@test.local'),
    '254107029999',
    'Teknologi Informasi',
    '081234567890'
);

-- ---------------------------------------------------------------------
-- 3. LAPORAN UJI
--    Variabel bantu (diambil ulang setiap INSERT agar skrip mandiri).
-- ---------------------------------------------------------------------
-- A. menunggu 10 jam  -> AMAN
INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi, tanggal_lapor)
VALUES (
    (SELECT m.id_mahasiswa FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE u.email = 'mhs@test.local'),
    (SELECT id_kategori FROM kategori WHERE nama_kategori = 'Uji SLA'),
    'TES SLA A aman', 'Lab Jaringan', current_date - 1,
    'Data uji SLA: laporan baru masuk, belum melewati batas 72 jam.',
    now() - INTERVAL '10 hours'
);

-- B. menunggu 56 jam  -> MENDEKATI (75%-100% dari 72 jam)
INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi, tanggal_lapor)
VALUES (
    (SELECT m.id_mahasiswa FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE u.email = 'mhs@test.local'),
    (SELECT id_kategori FROM kategori WHERE nama_kategori = 'Uji SLA'),
    'TES SLA B mendekati', 'Lab Komputer', current_date - 2,
    'Data uji SLA: laporan mendekati batas SLA (badge kuning).',
    now() - INTERVAL '56 hours'
);

-- C. menunggu 96 jam, tanpa tanggapan -> TERLAMBAT + PERLU PERHATIAN
INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi, tanggal_lapor)
VALUES (
    (SELECT m.id_mahasiswa FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE u.email = 'mhs@test.local'),
    (SELECT id_kategori FROM kategori WHERE nama_kategori = 'Uji SLA'),
    'TES SLA C terlambat', 'Ruang Kelas', current_date - 5,
    'Data uji SLA: lewat 72 jam dan belum ada tanggapan sama sekali.',
    now() - INTERVAL '96 hours'
);

-- D. menunggu 120 jam, tanggapan terakhir 96 jam lalu
--    -> TERLAMBAT (badge merah) tetapi TIDAK perlu perhatian,
--       karena laporan 'menunggu' yang sudah ditanggapi tidak disorot.
INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi, tanggal_lapor)
VALUES (
    (SELECT m.id_mahasiswa FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE u.email = 'mhs@test.local'),
    (SELECT id_kategori FROM kategori WHERE nama_kategori = 'Uji SLA'),
    'TES SLA D terlambat ada tanggapan', 'Gedung Sipil', current_date - 6,
    'Data uji SLA: sudah pernah ditanggapi unit, tetapi belum divalidasi.',
    now() - INTERVAL '120 hours'
);

INSERT INTO tanggapan (id_laporan, id_user, isi_tanggapan, tanggal_tanggapan)
VALUES (
    (SELECT id_laporan FROM laporan WHERE judul_laporan = 'TES SLA D terlambat ada tanggapan'),
    (SELECT id_user FROM users WHERE email = 'sekjur@test.local'),
    'Sudah kami tindak lanjuti sebagian, mohon menunggu.',
    now() - INTERVAL '96 hours'
);

-- E. dalam_penanganan, lapor 10 hari lalu tetapi ada penanganan 1 hari lalu
--    -> AMAN (aktivitas terakhir mereset jam SLA).
INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi, tanggal_lapor)
VALUES (
    (SELECT m.id_mahasiswa FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE u.email = 'mhs@test.local'),
    (SELECT id_kategori FROM kategori WHERE nama_kategori = 'Uji SLA'),
    'TES SLA E penanganan aktif', 'Lab Jaringan', current_date - 11,
    'Data uji SLA: dalam penanganan dan baru ada progres dari unit.',
    now() - INTERVAL '10 days'
);

INSERT INTO riwayat_status (id_laporan, id_user, status, tanggal_perubahan, keterangan)
VALUES (
    (SELECT id_laporan FROM laporan WHERE judul_laporan = 'TES SLA E penanganan aktif'),
    (SELECT id_user FROM users WHERE email = 'sekjur@test.local'),
    'dalam_penanganan', now() - INTERVAL '9 days', 'Divalidasi unit'
);

INSERT INTO penanganan (id_laporan, id_user, keterangan_penanganan, tanggal_penanganan)
VALUES (
    (SELECT id_laporan FROM laporan WHERE judul_laporan = 'TES SLA E penanganan aktif'),
    (SELECT id_user FROM users WHERE email = 'sekjur@test.local'),
    'Teknisi mengecek perangkat, menunggu sparepart.',
    now() - INTERVAL '1 day'
);

-- F. dalam_penanganan, lapor 10 hari lalu, TANPA aktivitas baru
--    -> TERLAMBAT + PERLU PERHATIAN.
INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi, tanggal_lapor)
VALUES (
    (SELECT m.id_mahasiswa FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE u.email = 'mhs@test.local'),
    (SELECT id_kategori FROM kategori WHERE nama_kategori = 'Uji SLA'),
    'TES SLA F penanganan idle', 'Ruang Dosen', current_date - 11,
    'Data uji SLA: sudah divalidasi tetapi tidak ada progres melewati 72 jam.',
    now() - INTERVAL '10 days'
);

INSERT INTO riwayat_status (id_laporan, id_user, status, tanggal_perubahan, keterangan)
VALUES (
    (SELECT id_laporan FROM laporan WHERE judul_laporan = 'TES SLA F penanganan idle'),
    (SELECT id_user FROM users WHERE email = 'sekjur@test.local'),
    'dalam_penanganan', now() - INTERVAL '9 days', 'Divalidasi unit'
);

-- G. selesai -> tidak dihitung (final)
INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi, tanggal_lapor)
VALUES (
    (SELECT m.id_mahasiswa FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE u.email = 'mhs@test.local'),
    (SELECT id_kategori FROM kategori WHERE nama_kategori = 'Uji SLA'),
    'TES SLA G selesai', 'Lab Komputer', current_date - 6,
    'Data uji SLA: laporan sudah selesai ditangani (tidak dihitung SLA).',
    now() - INTERVAL '5 days'
);

INSERT INTO penanganan (id_laporan, id_user, keterangan_penanganan, tanggal_penanganan)
VALUES (
    (SELECT id_laporan FROM laporan WHERE judul_laporan = 'TES SLA G selesai'),
    (SELECT id_user FROM users WHERE email = 'sekjur@test.local'),
    'Perangkat telah diperbaiki dan diuji.',
    now() - INTERVAL '3 days'
);

INSERT INTO riwayat_status (id_laporan, id_user, status, tanggal_perubahan, keterangan)
VALUES (
    (SELECT id_laporan FROM laporan WHERE judul_laporan = 'TES SLA G selesai'),
    (SELECT id_user FROM users WHERE email = 'sekjur@test.local'),
    'selesai', now() - INTERVAL '2 days', 'Selesai ditangani'
);

-- H. ditolak -> tidak dihitung (final)
INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi, tanggal_lapor)
VALUES (
    (SELECT m.id_mahasiswa FROM mahasiswa m JOIN users u ON u.id_user = m.id_user WHERE u.email = 'mhs@test.local'),
    (SELECT id_kategori FROM kategori WHERE nama_kategori = 'Uji SLA'),
    'TES SLA H ditolak', 'Gedung Sipil', current_date - 6,
    'Data uji SLA: laporan ditolak unit (tidak dihitung SLA).',
    now() - INTERVAL '5 days'
);

INSERT INTO riwayat_status (id_laporan, id_user, status, tanggal_perubahan, keterangan)
VALUES (
    (SELECT id_laporan FROM laporan WHERE judul_laporan = 'TES SLA H ditolak'),
    (SELECT id_user FROM users WHERE email = 'sekjur@test.local'),
    'ditolak', now() - INTERVAL '4 days', 'Bukti tidak sesuai dengan lokasi kejadian'
);

COMMIT;

-- =====================================================================
-- 4. VERIFIKASI
--    Query di bawah meniru logika backend (laporan-helper.php).
--    Jalankan manual, mis.:
--      psql -U postgres -h localhost -d db_sipeka -f DB/03_seed_uji_sla.sql
--    (SELECT di bawah hanya menampilkan hasil, tidak mengubah data)
-- =====================================================================

-- 4a. Tabel status SLA per laporan uji.
WITH dasar AS (
    SELECT v.id_laporan,
           v.judul_laporan,
           v.status,
           v.jumlah_tanggapan,
           GREATEST(
               v.tanggal_lapor,
               COALESCE((SELECT MAX(t.tanggal_tanggapan) FROM tanggapan t WHERE t.id_laporan = v.id_laporan), v.tanggal_lapor),
               COALESCE((SELECT MAX(p.tanggal_penanganan) FROM penanganan p WHERE p.id_laporan = v.id_laporan), v.tanggal_lapor)
           ) AS acuan
      FROM v_laporan_unit v
     WHERE v.nama_unit = 'Sekjur'
       AND v.judul_laporan LIKE 'TES SLA %'
)
SELECT id_laporan,
       judul_laporan,
       status,
       jumlah_tanggapan,
       ROUND(EXTRACT(EPOCH FROM (now() - acuan)) / 3600.0, 1) AS umur_jam,
       CASE
           WHEN status IN ('selesai', 'ditolak') THEN 'selesai'
           WHEN now() - acuan > INTERVAL '72 hours' THEN 'terlambat'
           WHEN now() - acuan > INTERVAL '54 hours' THEN 'mendekati'
           ELSE 'aman'
       END AS sla_status,
       ((status = 'menunggu' AND jumlah_tanggapan = 0 AND now() - acuan > INTERVAL '72 hours')
        OR (status = 'dalam_penanganan' AND now() - acuan > INTERVAL '72 hours')) AS perlu_perhatian
  FROM dasar
 ORDER BY id_laporan;

-- 4b. Jumlah laporan UJI yang "perlu perhatian" (harus 2: C dan F).
--     Catatan: endpoint unit/dashboard.php menghitung SEMUA laporan unit,
--     jadi pada database berisi data demo nilainya bisa lebih besar dari 2.
SELECT COUNT(*) AS perlu_perhatian
  FROM v_laporan_unit v
 WHERE v.nama_unit = 'Sekjur'
   AND v.judul_laporan LIKE 'TES SLA %'
   AND (
        (v.status = 'menunggu' AND v.jumlah_tanggapan = 0
         AND GREATEST(v.tanggal_lapor,
                      COALESCE((SELECT MAX(t.tanggal_tanggapan) FROM tanggapan t WHERE t.id_laporan = v.id_laporan), v.tanggal_lapor),
                      COALESCE((SELECT MAX(p.tanggal_penanganan) FROM penanganan p WHERE p.id_laporan = v.id_laporan), v.tanggal_lapor)
             ) < now() - INTERVAL '72 hours')
        OR
        (v.status = 'dalam_penanganan'
         AND GREATEST(v.tanggal_lapor,
                      COALESCE((SELECT MAX(t.tanggal_tanggapan) FROM tanggapan t WHERE t.id_laporan = v.id_laporan), v.tanggal_lapor),
                      COALESCE((SELECT MAX(p.tanggal_penanganan) FROM penanganan p WHERE p.id_laporan = v.id_laporan), v.tanggal_lapor)
             ) < now() - INTERVAL '72 hours')
       );

-- Hasil yang diharapkan:
--   A aman                    -> aman       | perlu_perhatian = false
--   B mendekati               -> mendekati  | false
--   C terlambat               -> terlambat  | TRUE
--   D terlambat ada tanggapan -> terlambat  | false (sudah ditanggapi)
--   E penanganan aktif        -> aman       | false
--   F penanganan idle         -> terlambat  | TRUE
--   G selesai                 -> selesai    | false
--   H ditolak                 -> selesai    | false
--   perlu_perhatian (4b)      = 2 (khusus baris TES SLA)

-- 4c. Cek cepat akun uji bisa login (hash bcrypt valid).
SELECT email, role,
       (password_hash LIKE '$2a$%' OR password_hash LIKE '$2b$%' OR password_hash LIKE '$2y$%') AS hash_bcrypt
  FROM users
 WHERE email IN ('sekjur@test.local', 'mhs@test.local');

-- =====================================================================
-- 5. CLEANUP (hapus data uji)
--    Blok ini sengaja dikomentari agar tidak ikut menghapus saat seed.
--    Buka komentar bila ingin membersihkan setelah pengujian.
-- =====================================================================
-- BEGIN;
-- DELETE FROM laporan WHERE judul_laporan LIKE 'TES SLA %';
-- DELETE FROM users   WHERE email IN ('sekjur@test.local', 'mhs@test.local');
-- DELETE FROM kategori k
--  WHERE k.nama_kategori = 'Uji SLA'
--    AND NOT EXISTS (SELECT 1 FROM laporan l WHERE l.id_kategori = k.id_kategori);
-- COMMIT;
