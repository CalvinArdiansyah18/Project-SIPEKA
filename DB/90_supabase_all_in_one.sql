-- =====================================================================
-- SIPEKA - SKEMA BERSIH UNTUK SUPABASE (PostgreSQL 15+)
-- =====================================================================
-- File ini adalah versi "all-in-one" ALTERNATIF untuk Supabase, berisi
-- gabungan skema utama + migrasi WEBP + migrasi notifikasi/ulasan
-- (setara DB/01_skema_gabungan.sql + DB/02_migrasi_webp.sql).
-- Gunakan SALAH SATU: file ini (Supabase) ATAU 01 + 02 (PostgreSQL lokal).
--
-- ISI DATA:
--   Hanya data referensi/master:
--     * 4 unit penanggung jawab
--     * 5 kategori pengaduan
--     * 4 akun petugas unit (agar portal unit bisa langsung dipakai)
--   TIDAK ada data laporan, riwayat, tanggapan, penanganan, bukti,
--   mahasiswa, ulasan, maupun notifikasi.
--
-- CARA PAKAI DI SUPABASE:
--   1. Buka Supabase Dashboard > SQL Editor > New query.
--   2. Tempel seluruh isi file ini, lalu Run.
--   3. Sesuaikan backend/config/koneksi.php dengan Connection String
--      Supabase (Project Settings > Database > Connection string).
--   4. Password akun petugas unit: Sipeka123!
--      Segera ganti setelah login pertama (menu Profil / Ganti Sandi).
--
-- CATATAN KEAMANAN:
--   * RLS diaktifkan pada semua tabel TANPA policy, sehingga anon key
--     Supabase tidak bisa membaca/menulis data. Backend PHP memakai
--     koneksi PostgreSQL langsung (role postgres) yang melewati RLS.
--   * Jika nanti memakai Supabase Client (anon key) dari frontend,
--     tambahkan policy RLS sesuai kebutuhan.
--
-- PERINGATAN: bagian RESET menghapus objek SIPEKA yang sudah ada.
-- =====================================================================

BEGIN;

SET search_path = public, pg_temp;

-- ---------------------------------------------------------------------
-- 1. RESET (aman dijalankan berulang pada database SIPEKA)
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_ulasan_publik, v_rekap_unit, v_laporan_unit;

DROP TABLE IF EXISTS ulasan_balasan,
                     ulasan,
                     notifikasi,
                     bukti_penanganan,
                     penanganan,
                     tanggapan,
                     riwayat_status,
                     bukti_laporan,
                     laporan,
                     mahasiswa,
                     users,
                     kategori,
                     unit CASCADE;

DROP PROCEDURE IF EXISTS sp_ubah_status;

DROP FUNCTION IF EXISTS fn_riwayat_awal(),
                      fn_sinkron_status_laporan(),
                      fn_validasi_unit_penanggung_jawab(),
                      fn_notif_laporan_baru(),
                      fn_notif_status_berubah(),
                      fn_notif_tanggapan_baru(),
                      fn_notif_balasan_ulasan(),
                      fn_validasi_ulasan(),
                      fn_notif_ulasan_baru() CASCADE;

DROP TYPE IF EXISTS status_laporan, role_user CASCADE;

-- ---------------------------------------------------------------------
-- 2. TIPE ENUM
-- ---------------------------------------------------------------------
CREATE TYPE role_user AS ENUM ('mahasiswa', 'unit');

CREATE TYPE status_laporan AS ENUM (
    'menunggu',
    'dalam_penanganan',
    'selesai',
    'ditolak'
);

-- ---------------------------------------------------------------------
-- 3. TABEL
-- ---------------------------------------------------------------------

-- Unit penanggung jawab: Sekjur, Admin Prodi, Kajur, KPS
CREATE TABLE unit (
    id_unit   SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama_unit VARCHAR(50) NOT NULL,
    CONSTRAINT uq_unit_nama UNIQUE (nama_unit)
);

-- Kategori membawa id_unit => aturan "direct routing" tersimpan di data,
-- sehingga laporan tidak perlu menyimpan id_unit (menghindari dependensi transitif).
CREATE TABLE kategori (
    id_kategori   SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_unit       SMALLINT NOT NULL,
    nama_kategori VARCHAR(60) NOT NULL,
    deskripsi     TEXT,
    CONSTRAINT uq_kategori_nama UNIQUE (nama_kategori),
    CONSTRAINT fk_kategori_unit FOREIGN KEY (id_unit)
        REFERENCES unit (id_unit) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- "user" adalah reserved word di PostgreSQL, maka tabel diberi nama "users".
-- Akun mahasiswa: role='mahasiswa', id_unit NULL.
-- Akun petugas  : role='unit',      id_unit terisi.
CREATE TABLE users (
    id_user       INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_unit       SMALLINT,
    nama          VARCHAR(100) NOT NULL,
    email         VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          role_user NOT NULL,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT ck_users_email CHECK (
        email ~* '^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$'
    ),
    CONSTRAINT ck_users_role_unit CHECK (
        (role = 'mahasiswa' AND id_unit IS NULL) OR
        (role = 'unit' AND id_unit IS NOT NULL)
    ),
    CONSTRAINT fk_users_unit FOREIGN KEY (id_unit)
        REFERENCES unit (id_unit) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Profil khusus mahasiswa (1 user : 0..1 mahasiswa). Nama diambil dari users.nama.
CREATE TABLE mahasiswa (
    id_mahasiswa   INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user        INT NOT NULL,
    nim            VARCHAR(15) NOT NULL,
    program_studi  VARCHAR(60) NOT NULL,
    nomor_telepon  VARCHAR(20),
    CONSTRAINT uq_mahasiswa_user UNIQUE (id_user),
    CONSTRAINT uq_mahasiswa_nim UNIQUE (nim),
    CONSTRAINT ck_mahasiswa_nim CHECK (nim ~ '^[0-9]{8,15}$'),
    CONSTRAINT ck_mahasiswa_telp CHECK (
        nomor_telepon IS NULL
        OR nomor_telepon ~ '^(\+62|0)[0-9]{8,13}$'
    ),
    CONSTRAINT fk_mahasiswa_user FOREIGN KEY (id_user)
        REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Entitas inti. Unit penanggung jawab diturunkan lewat kategori.
-- status = status TERKINI (disinkronkan otomatis oleh trigger dari riwayat_status).
CREATE TABLE laporan (
    id_laporan      BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_mahasiswa    INT NOT NULL,
    id_kategori     SMALLINT NOT NULL,
    judul_laporan   VARCHAR(150) NOT NULL,
    lokasi          VARCHAR(150),
    tanggal_kejadian DATE NOT NULL,
    kronologi       TEXT NOT NULL,
    status          status_laporan NOT NULL DEFAULT 'menunggu',
    tanggal_lapor   TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_laporan_judul CHECK (char_length(btrim(judul_laporan)) >= 5),
    CONSTRAINT ck_laporan_kronologi CHECK (char_length(btrim(kronologi)) >= 20),
    CONSTRAINT fk_laporan_mahasiswa FOREIGN KEY (id_mahasiswa)
        REFERENCES mahasiswa (id_mahasiswa) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_laporan_kategori FOREIGN KEY (id_kategori)
        REFERENCES kategori (id_kategori) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE bukti_laporan (
    id_bukti     BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_laporan   BIGINT NOT NULL,
    nama_file    VARCHAR(255) NOT NULL,
    path_file    VARCHAR(500) NOT NULL,
    jenis_file   VARCHAR(100) NOT NULL,
    uploaded_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_bukti_laporan_path UNIQUE (path_file),
    CONSTRAINT ck_bukti_laporan_jenis CHECK (
        jenis_file IN ('image/jpeg', 'image/png', 'image/webp', 'application/pdf')
    ),
    CONSTRAINT fk_bukti_laporan_laporan FOREIGN KEY (id_laporan)
        REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Linimasa perubahan status. id_user = siapa yang mengubah (akuntabilitas).
CREATE TABLE riwayat_status (
    id_riwayat         BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_laporan         BIGINT NOT NULL,
    id_user            INT NOT NULL,
    status             status_laporan NOT NULL,
    tanggal_perubahan  TIMESTAMPTZ NOT NULL DEFAULT now(),
    keterangan         TEXT,
    CONSTRAINT fk_riwayat_laporan FOREIGN KEY (id_laporan)
        REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_riwayat_user FOREIGN KEY (id_user)
        REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- id_user = petugas unit yang menulis (unit-nya divalidasi oleh trigger).
CREATE TABLE tanggapan (
    id_tanggapan       BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_laporan         BIGINT NOT NULL,
    id_user            INT NOT NULL,
    isi_tanggapan      TEXT NOT NULL,
    tanggal_tanggapan  TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_tanggapan_isi CHECK (char_length(btrim(isi_tanggapan)) > 0),
    CONSTRAINT fk_tanggapan_laporan FOREIGN KEY (id_laporan)
        REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_tanggapan_user FOREIGN KEY (id_user)
        REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE penanganan (
    id_penanganan           BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_laporan              BIGINT NOT NULL,
    id_user                 INT NOT NULL,
    keterangan_penanganan   TEXT NOT NULL,
    tanggal_penanganan      TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_penanganan_ket CHECK (char_length(btrim(keterangan_penanganan)) > 0),
    CONSTRAINT fk_penanganan_laporan FOREIGN KEY (id_laporan)
        REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_penanganan_user FOREIGN KEY (id_user)
        REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Struktur kolom diseragamkan dengan bukti_laporan.
CREATE TABLE bukti_penanganan (
    id_bukti      BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_penanganan BIGINT NOT NULL,
    nama_file     VARCHAR(255) NOT NULL,
    path_file     VARCHAR(500) NOT NULL,
    jenis_file    VARCHAR(100) NOT NULL,
    uploaded_at   TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_bukti_penanganan_path UNIQUE (path_file),
    CONSTRAINT ck_bukti_penanganan_jenis CHECK (
        jenis_file IN ('image/jpeg', 'image/png', 'image/webp', 'application/pdf')
    ),
    CONSTRAINT fk_bukti_penanganan_penanganan FOREIGN KEY (id_penanganan)
        REFERENCES penanganan (id_penanganan) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Notifikasi: satu baris per penerima (mahasiswa atau petugas unit).
CREATE TABLE notifikasi (
    id_notifikasi BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user       INT NOT NULL,
    id_laporan    BIGINT,
    jenis         VARCHAR(30) NOT NULL,
    judul         VARCHAR(150) NOT NULL,
    pesan         TEXT NOT NULL,
    sudah_dibaca  BOOLEAN NOT NULL DEFAULT FALSE,
    created_at    TIMESTAMPTZ NOT NULL DEFAULT now(),
    dibaca_at     TIMESTAMPTZ,
    CONSTRAINT ck_notifikasi_jenis CHECK (jenis IN (
        'laporan_baru',
        'laporan_divalidasi',
        'laporan_ditolak',
        'tanggapan_baru',
        'laporan_selesai',
        'balasan_ulasan',
        'ulasan_baru'
    )),
    CONSTRAINT fk_notifikasi_user FOREIGN KEY (id_user)
        REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_notifikasi_laporan FOREIGN KEY (id_laporan)
        REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Ulasan berupa komentar (tanpa bintang wajib), boleh dikaitkan ke
-- satu laporan yang sudah selesai. Hanya mahasiswa yang menulis ulasan.
CREATE TABLE ulasan (
    id_ulasan  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user    INT NOT NULL,
    id_laporan BIGINT,
    rating     SMALLINT,
    isi_ulasan TEXT NOT NULL,
    tampil     BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_ulasan_rating CHECK (rating IS NULL OR rating BETWEEN 1 AND 5),
    CONSTRAINT ck_ulasan_isi CHECK (char_length(btrim(isi_ulasan)) BETWEEN 3 AND 500),
    CONSTRAINT fk_ulasan_user FOREIGN KEY (id_user)
        REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_ulasan_laporan FOREIGN KEY (id_laporan)
        REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE SET NULL
);

-- Balasan ulasan: mahasiswa & petugas unit saling menjawab.
CREATE TABLE ulasan_balasan (
    id_balasan  BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_ulasan   BIGINT NOT NULL,
    id_user     INT NOT NULL,
    isi_balasan TEXT NOT NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_balasan_isi CHECK (char_length(btrim(isi_balasan)) BETWEEN 2 AND 300),
    CONSTRAINT fk_balasan_ulasan FOREIGN KEY (id_ulasan)
        REFERENCES ulasan (id_ulasan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_balasan_user FOREIGN KEY (id_user)
        REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- 4. INDEX (PK dan UNIQUE sudah otomatis ter-index; ini untuk FK & filter)
-- ---------------------------------------------------------------------
CREATE INDEX idx_users_unit ON users (id_unit) WHERE id_unit IS NOT NULL;
CREATE INDEX idx_kategori_unit ON kategori (id_unit);
CREATE INDEX idx_laporan_mahasiswa ON laporan (id_mahasiswa, tanggal_lapor DESC);
CREATE INDEX idx_laporan_kategori_stat ON laporan (id_kategori, status);
CREATE INDEX idx_bukti_laporan_laporan ON bukti_laporan (id_laporan);
CREATE INDEX idx_riwayat_laporan ON riwayat_status (id_laporan, tanggal_perubahan);
CREATE INDEX idx_tanggapan_laporan ON tanggapan (id_laporan);
CREATE INDEX idx_penanganan_laporan ON penanganan (id_laporan);
CREATE INDEX idx_bukti_penanganan_pen ON bukti_penanganan (id_penanganan);

CREATE INDEX idx_notifikasi_user_terbaru ON notifikasi (id_user, id_notifikasi DESC);
CREATE INDEX idx_notifikasi_belum_dibaca ON notifikasi (id_user) WHERE NOT sudah_dibaca;

CREATE INDEX idx_ulasan_tampil_terbaru ON ulasan (created_at DESC) WHERE tampil;
CREATE INDEX idx_ulasan_user ON ulasan (id_user, id_ulasan DESC);
CREATE INDEX idx_ulasan_waktu ON ulasan (created_at DESC);
CREATE INDEX idx_ulasan_laporan ON ulasan (id_laporan) WHERE id_laporan IS NOT NULL;

CREATE INDEX idx_balasan_ulasan ON ulasan_balasan (id_ulasan, id_balasan);

-- ---------------------------------------------------------------------
-- 5. TRIGGER & STORED PROCEDURE
-- ---------------------------------------------------------------------

-- 5a. Setiap laporan baru otomatis punya riwayat pertama 'menunggu'.
CREATE FUNCTION fn_riwayat_awal() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
BEGIN
    INSERT INTO riwayat_status (id_laporan, id_user, status, tanggal_perubahan, keterangan)
    SELECT NEW.id_laporan, m.id_user, 'menunggu'::status_laporan, NEW.tanggal_lapor,
           'Laporan dikirim dan diteruskan otomatis ke unit penanggung jawab'
      FROM mahasiswa m
     WHERE m.id_mahasiswa = NEW.id_mahasiswa;
    RETURN NULL;
END $$;

CREATE TRIGGER trg_laporan_riwayat_awal
AFTER INSERT ON laporan
FOR EACH ROW EXECUTE FUNCTION fn_riwayat_awal();

-- 5b. Setiap riwayat baru menyinkronkan laporan.status & updated_at.
CREATE FUNCTION fn_sinkron_status_laporan() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
BEGIN
    UPDATE laporan
       SET status = NEW.status, updated_at = NEW.tanggal_perubahan
     WHERE id_laporan = NEW.id_laporan;
    RETURN NULL;
END $$;

CREATE TRIGGER trg_riwayat_sinkron_status
AFTER INSERT ON riwayat_status
FOR EACH ROW EXECUTE FUNCTION fn_sinkron_status_laporan();

-- 5c. Hanya petugas dari unit penanggung jawab yang boleh menanggapi/menangani.
CREATE FUNCTION fn_validasi_unit_penanggung_jawab() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_unit_user    SMALLINT;
    v_unit_laporan SMALLINT;
BEGIN
    SELECT id_unit INTO v_unit_user FROM users WHERE id_user = NEW.id_user;

    SELECT k.id_unit INTO v_unit_laporan
      FROM laporan l
      JOIN kategori k ON k.id_kategori = l.id_kategori
     WHERE l.id_laporan = NEW.id_laporan;

    IF v_unit_user IS DISTINCT FROM v_unit_laporan THEN
        RAISE EXCEPTION 'User % bukan petugas unit penanggung jawab laporan %',
              NEW.id_user, NEW.id_laporan
              USING ERRCODE = '42501';
    END IF;
    RETURN NEW;
END $$;

CREATE TRIGGER trg_tanggapan_validasi_unit
BEFORE INSERT ON tanggapan
FOR EACH ROW EXECUTE FUNCTION fn_validasi_unit_penanggung_jawab();

CREATE TRIGGER trg_penanganan_validasi_unit
BEFORE INSERT ON penanganan
FOR EACH ROW EXECUTE FUNCTION fn_validasi_unit_penanggung_jawab();

-- 5d. Stored procedure ubah status (satu transaksi, row lock, validasi alur).
--     Alur: menunggu -> dalam_penanganan -> selesai
--           (menunggu boleh langsung ke ditolak)
--     'selesai' mensyaratkan minimal satu catatan penanganan.
CREATE OR REPLACE PROCEDURE sp_ubah_status(
    p_id_laporan  BIGINT,
    p_id_user     INT,
    p_status_baru status_laporan,
    p_keterangan  TEXT DEFAULT NULL,
    p_waktu       TIMESTAMPTZ DEFAULT now()
)
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_status_lama  status_laporan;
    v_unit_laporan SMALLINT;
    v_unit_user    SMALLINT;
BEGIN
    SELECT l.status, k.id_unit
      INTO v_status_lama, v_unit_laporan
      FROM laporan l
      JOIN kategori k ON k.id_kategori = l.id_kategori
     WHERE l.id_laporan = p_id_laporan
       FOR UPDATE OF l;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Laporan % tidak ditemukan', p_id_laporan;
    END IF;

    SELECT id_unit INTO v_unit_user FROM users WHERE id_user = p_id_user;
    IF v_unit_user IS DISTINCT FROM v_unit_laporan THEN
        RAISE EXCEPTION 'User % bukan petugas unit penanggung jawab laporan %',
              p_id_user, p_id_laporan USING ERRCODE = '42501';
    END IF;

    IF NOT (
        (v_status_lama = 'menunggu' AND p_status_baru IN ('dalam_penanganan', 'ditolak')) OR
        (v_status_lama = 'dalam_penanganan' AND p_status_baru = 'selesai')
    ) THEN
        RAISE EXCEPTION 'Perubahan status % -> % tidak diizinkan',
              v_status_lama, p_status_baru;
    END IF;

    IF p_status_baru = 'selesai'
       AND NOT EXISTS (SELECT 1 FROM penanganan WHERE id_laporan = p_id_laporan) THEN
        RAISE EXCEPTION 'Laporan % belum memiliki catatan penanganan', p_id_laporan;
    END IF;

    INSERT INTO riwayat_status (id_laporan, id_user, status, tanggal_perubahan, keterangan)
    VALUES (p_id_laporan, p_id_user, p_status_baru, p_waktu, p_keterangan);
END $$;

-- 5e. Notifikasi otomatis (dibuat database, backend tidak perlu mengubah apa pun).

-- Laporan baru -> semua petugas unit penanggung jawab kategori tsb.
CREATE FUNCTION fn_notif_laporan_baru() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_tiket TEXT := 'SPK-' || to_char(NEW.tanggal_lapor, 'YYYY') || '-' || lpad(NEW.id_laporan::text, 5, '0');
BEGIN
    INSERT INTO notifikasi (id_user, id_laporan, jenis, judul, pesan)
    SELECT u.id_user, NEW.id_laporan, 'laporan_baru', 'Laporan baru masuk',
           v_tiket || ' - ' || NEW.judul_laporan
      FROM kategori k
      JOIN users u ON u.id_unit = k.id_unit
     WHERE k.id_kategori = NEW.id_kategori;
    RETURN NEW;
END $$;

CREATE TRIGGER trg_notif_laporan_baru
AFTER INSERT ON laporan
FOR EACH ROW EXECUTE FUNCTION fn_notif_laporan_baru();

-- Status berubah -> mahasiswa pelapor.
CREATE FUNCTION fn_notif_status_berubah() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_user  INT;
    v_judul TEXT;
    v_tiket TEXT;
    v_jenis TEXT;
    v_title TEXT;
    v_pesan TEXT;
BEGIN
    IF NEW.status::text NOT IN ('dalam_penanganan', 'ditolak', 'selesai') THEN
        RETURN NEW;
    END IF;

    SELECT m.id_user, l.judul_laporan,
           'SPK-' || to_char(l.tanggal_lapor, 'YYYY') || '-' || lpad(l.id_laporan::text, 5, '0')
      INTO v_user, v_judul, v_tiket
      FROM laporan l
      JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
     WHERE l.id_laporan = NEW.id_laporan;

    IF v_user IS NULL THEN
        RETURN NEW;
    END IF;

    IF NEW.status::text = 'dalam_penanganan' THEN
        v_jenis := 'laporan_divalidasi'; v_title := 'Laporan Anda diterima';
        v_pesan := v_tiket || ' telah divalidasi dan sedang ditangani.';
    ELSIF NEW.status::text = 'ditolak' THEN
        v_jenis := 'laporan_ditolak';    v_title := 'Laporan Anda ditolak';
        v_pesan := v_tiket || ' ditolak oleh unit. Buka detail untuk melihat alasannya.';
    ELSE
        v_jenis := 'laporan_selesai';    v_title := 'Laporan Anda selesai ditangani';
        v_pesan := v_tiket || ' - ' || v_judul;
    END IF;

    INSERT INTO notifikasi (id_user, id_laporan, jenis, judul, pesan)
    VALUES (v_user, NEW.id_laporan, v_jenis, v_title, v_pesan);
    RETURN NEW;
END $$;

CREATE TRIGGER trg_notif_status_berubah
AFTER INSERT ON riwayat_status
FOR EACH ROW EXECUTE FUNCTION fn_notif_status_berubah();

-- Tanggapan unit -> mahasiswa pelapor.
CREATE FUNCTION fn_notif_tanggapan_baru() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_user  INT;
    v_tiket TEXT;
BEGIN
    SELECT m.id_user,
           'SPK-' || to_char(l.tanggal_lapor, 'YYYY') || '-' || lpad(l.id_laporan::text, 5, '0')
      INTO v_user, v_tiket
      FROM laporan l
      JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
     WHERE l.id_laporan = NEW.id_laporan;

    IF v_user IS NOT NULL THEN
        INSERT INTO notifikasi (id_user, id_laporan, jenis, judul, pesan)
        VALUES (v_user, NEW.id_laporan, 'tanggapan_baru', 'Tanggapan baru dari unit',
                v_tiket || ' - ' || left(NEW.isi_tanggapan, 120));
    END IF;
    RETURN NEW;
END $$;

CREATE TRIGGER trg_notif_tanggapan_baru
AFTER INSERT ON tanggapan
FOR EACH ROW EXECUTE FUNCTION fn_notif_tanggapan_baru();

-- Balasan ulasan -> pemilik ulasan + peserta lain (kecuali penulis balasan).
CREATE FUNCTION fn_notif_balasan_ulasan() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
BEGIN
    INSERT INTO notifikasi (id_user, id_laporan, jenis, judul, pesan)
    SELECT p.id_user, NULL, 'balasan_ulasan', 'Balasan baru pada ulasan', left(NEW.isi_balasan, 120)
      FROM (
          SELECT ul.id_user FROM ulasan ul WHERE ul.id_ulasan = NEW.id_ulasan
          UNION
          SELECT b.id_user FROM ulasan_balasan b WHERE b.id_ulasan = NEW.id_ulasan
      ) p
     WHERE p.id_user <> NEW.id_user;
    RETURN NEW;
END $$;

CREATE TRIGGER trg_notif_balasan_ulasan
AFTER INSERT ON ulasan_balasan
FOR EACH ROW EXECUTE FUNCTION fn_notif_balasan_ulasan();

-- Validasi ulasan: penulis harus mahasiswa; laporan harus milik penulis & selesai.
CREATE FUNCTION fn_validasi_ulasan() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
DECLARE
    v_role   TEXT;
    v_status TEXT;
    v_pemilik INT;
BEGIN
    SELECT role::text INTO v_role FROM users WHERE id_user = NEW.id_user;
    IF v_role IS DISTINCT FROM 'mahasiswa' THEN
        RAISE EXCEPTION 'Hanya mahasiswa yang dapat menulis ulasan' USING ERRCODE = '23514';
    END IF;

    IF NEW.id_laporan IS NOT NULL THEN
        SELECT l.status::text, m.id_user INTO v_status, v_pemilik
          FROM laporan l
          JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
         WHERE l.id_laporan = NEW.id_laporan;
        IF v_pemilik IS DISTINCT FROM NEW.id_user THEN
            RAISE EXCEPTION 'Penanganan yang diulas bukan laporan Anda' USING ERRCODE = '23514';
        END IF;
        IF v_status <> 'selesai' THEN
            RAISE EXCEPTION 'Hanya penanganan yang sudah selesai yang dapat diulas' USING ERRCODE = '23514';
        END IF;
    END IF;
    RETURN NEW;
END $$;

CREATE TRIGGER trg_validasi_ulasan
BEFORE INSERT ON ulasan
FOR EACH ROW EXECUTE FUNCTION fn_validasi_ulasan();

-- Ulasan baru -> petugas unit penanggung jawab laporan yang diulas.
CREATE FUNCTION fn_notif_ulasan_baru() RETURNS trigger
LANGUAGE plpgsql
SET search_path = public, pg_temp
AS $$
BEGIN
    IF NEW.id_laporan IS NULL THEN
        RETURN NEW;
    END IF;
    INSERT INTO notifikasi (id_user, id_laporan, jenis, judul, pesan)
    SELECT u.id_user, NEW.id_laporan, 'ulasan_baru', 'Ulasan baru dari mahasiswa',
           left(NEW.isi_ulasan, 120)
      FROM laporan l
      JOIN kategori k ON k.id_kategori = l.id_kategori
      JOIN users u    ON u.id_unit = k.id_unit
     WHERE l.id_laporan = NEW.id_laporan;
    RETURN NEW;
END $$;

CREATE TRIGGER trg_notif_ulasan_baru
AFTER INSERT ON ulasan
FOR EACH ROW EXECUTE FUNCTION fn_notif_ulasan_baru();

-- ---------------------------------------------------------------------
-- 6. VIEW
--    security_invoker = true agar RLS tabel tetap berlaku saat view
--    diakses lewat Supabase Client (anon/authenticated).
-- ---------------------------------------------------------------------

-- Dashboard unit: daftar laporan masuk (aplikasi cukup menambah WHERE id_unit = ?)
CREATE VIEW v_laporan_unit WITH (security_invoker = true) AS
SELECT l.id_laporan,
       un.id_unit,
       un.nama_unit,
       k.nama_kategori,
       l.judul_laporan,
       l.lokasi,
       l.tanggal_kejadian,
       l.tanggal_lapor,
       l.status,
       l.updated_at,
       u.nama AS nama_pelapor,
       m.nim,
       m.program_studi,
       (SELECT COUNT(*) FROM bukti_laporan b WHERE b.id_laporan = l.id_laporan) AS jumlah_bukti,
       (SELECT COUNT(*) FROM tanggapan t WHERE t.id_laporan = l.id_laporan) AS jumlah_tanggapan
FROM laporan l
JOIN kategori k  ON k.id_kategori = l.id_kategori
JOIN unit un     ON un.id_unit = k.id_unit
JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
JOIN users u     ON u.id_user = m.id_user;

-- Rekap & statistik per unit (fitur "rekap per unit")
CREATE VIEW v_rekap_unit WITH (security_invoker = true) AS
SELECT un.id_unit,
       un.nama_unit,
       k.nama_kategori,
       COUNT(l.id_laporan) AS total_laporan,
       COUNT(*) FILTER (WHERE l.status = 'menunggu') AS menunggu,
       COUNT(*) FILTER (WHERE l.status = 'dalam_penanganan') AS dalam_penanganan,
       COUNT(*) FILTER (WHERE l.status = 'selesai') AS selesai,
       COUNT(*) FILTER (WHERE l.status = 'ditolak') AS ditolak,
       ROUND((AVG(EXTRACT(EPOCH FROM (l.updated_at - l.tanggal_lapor)) / 86400)
              FILTER (WHERE l.status = 'selesai'))::numeric, 1) AS rata_hari_penyelesaian
FROM unit un
JOIN kategori k ON k.id_unit = un.id_unit
LEFT JOIN laporan l ON l.id_kategori = k.id_kategori
GROUP BY un.id_unit, un.nama_unit, k.id_kategori, k.nama_kategori;

-- Tampilan publik ulasan (tanpa email / data pribadi lain)
CREATE VIEW v_ulasan_publik WITH (security_invoker = true) AS
SELECT ul.id_ulasan,
       u.nama,
       u.role::text AS peran,
       un.nama_unit,
       ul.rating,
       ul.isi_ulasan,
       ul.created_at,
       ul.updated_at
FROM ulasan ul
JOIN users u      ON u.id_user = ul.id_user
LEFT JOIN unit un ON un.id_unit = u.id_unit
WHERE ul.tampil;

-- ---------------------------------------------------------------------
-- 7. KEAMANAN: aktifkan RLS tanpa policy (akses hanya lewat koneksi
--    PostgreSQL langsung dari backend PHP). Tambahkan policy jika nanti
--    memakai Supabase Client dengan anon key.
-- ---------------------------------------------------------------------
ALTER TABLE unit              ENABLE ROW LEVEL SECURITY;
ALTER TABLE kategori          ENABLE ROW LEVEL SECURITY;
ALTER TABLE users             ENABLE ROW LEVEL SECURITY;
ALTER TABLE mahasiswa         ENABLE ROW LEVEL SECURITY;
ALTER TABLE laporan           ENABLE ROW LEVEL SECURITY;
ALTER TABLE bukti_laporan     ENABLE ROW LEVEL SECURITY;
ALTER TABLE riwayat_status    ENABLE ROW LEVEL SECURITY;
ALTER TABLE tanggapan         ENABLE ROW LEVEL SECURITY;
ALTER TABLE penanganan        ENABLE ROW LEVEL SECURITY;
ALTER TABLE bukti_penanganan  ENABLE ROW LEVEL SECURITY;
ALTER TABLE notifikasi        ENABLE ROW LEVEL SECURITY;
ALTER TABLE ulasan            ENABLE ROW LEVEL SECURITY;
ALTER TABLE ulasan_balasan    ENABLE ROW LEVEL SECURITY;

-- ---------------------------------------------------------------------
-- 8. DATA REFERENSI (master) - TANPA DATA LAPORAN
-- ---------------------------------------------------------------------
INSERT INTO unit (nama_unit) VALUES
    ('Sekjur'),
    ('Admin Prodi'),
    ('Kajur'),
    ('KPS');

INSERT INTO kategori (id_unit, nama_kategori, deskripsi)
SELECT un.id_unit, v.nama_kategori, v.deskripsi
FROM (
    VALUES
        ('Sekjur',      'Fasilitas Kampus',    'Kerusakan kelas, laboratorium, perpustakaan, toilet, dan sarana umum'),
        ('Admin Prodi', 'Pelayanan Akademik',  'KRS, jadwal kuliah, nilai, dan administrasi prodi'),
        ('Kajur',       'Perilaku Dosen/Staf', 'Ketidakdisiplinan atau dugaan pelanggaran kode etik dosen/staf'),
        ('Kajur',       'Pungutan Liar',       'Biaya tidak resmi di luar ketentuan kampus'),
        ('KPS',         'Pelanggaran Mahasiswa', 'Kecurangan akademik, penyalahgunaan fasilitas, pelanggaran tata tertib')
) AS v(nama_unit, nama_kategori, deskripsi)
JOIN unit un ON un.nama_unit = v.nama_unit;

-- Akun petugas unit (wajib ada agar portal unit dapat login).
-- Password awal: Sipeka123!  ->  segera ganti setelah login pertama.
INSERT INTO users (id_unit, nama, email, password_hash, role)
SELECT un.id_unit, v.nama, v.email,
       '$2y$12$2LrTRvYMmbFN/a/VPZkSvOvyJltCxHp3rJFjAg/VWsbkO7ck7LksW',
       'unit'::role_user
FROM (
    VALUES
        ('Sekjur',      'Petugas Sekjur',   'sekjur@sipeka.test'),
        ('Admin Prodi', 'Admin Prodi TI',   'adminprodi@sipeka.test'),
        ('Kajur',       'Ketua Jurusan',    'kajur@sipeka.test'),
        ('KPS',         'Koordinator Prodi', 'kps@sipeka.test')
) AS v(nama_unit, nama, email)
JOIN unit un ON un.nama_unit = v.nama_unit;

COMMIT;

-- =====================================================================
-- SELESAI.
-- Database bersih: hanya unit, kategori, dan akun petugas unit.
-- Tabel laporan, riwayat_status, tanggapan, penanganan, bukti_laporan,
-- bukti_penanganan, mahasiswa, ulasan, ulasan_balasan, dan notifikasi
-- kosong (siap diisi lewat aplikasi).
-- =====================================================================
