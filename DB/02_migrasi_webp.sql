-- Migrasi 001: izinkan berkas WEBP pada bukti laporan dan bukti penanganan.
-- Form buat laporan menerima JPG, PNG, WEBP, dan PDF, tetapi constraint awal belum memuat image/webp.
--
-- URUTAN EKSEKUSI (PostgreSQL lokal / db_sipeka):
--   01_skema_gabungan.sql -> 02_migrasi_webp.sql (file ini) -> 03_seed_uji_sla.sql (opsional)
--
-- Jalankan SEKALI pada database yang sudah ada (db_sipeka). Aman dijalankan ulang.

ALTER TABLE bukti_laporan
    DROP CONSTRAINT IF EXISTS ck_bukti_laporan_jenis,
    ADD  CONSTRAINT ck_bukti_laporan_jenis
         CHECK (jenis_file IN ('image/jpeg', 'image/png', 'image/webp', 'application/pdf'));

ALTER TABLE bukti_penanganan
    DROP CONSTRAINT IF EXISTS ck_bukti_penanganan_jenis,
    ADD  CONSTRAINT ck_bukti_penanganan_jenis
         CHECK (jenis_file IN ('image/jpeg', 'image/png', 'image/webp', 'application/pdf'));