-- =====================================================================
-- SIPEKA - DATABASE GABUNGAN
-- PostgreSQL 12+
--
-- URUTAN EKSEKUSI (PostgreSQL lokal / db_sipeka):
--   01_skema_gabungan.sql  <- file ini (skema + semua migrasi)
--   02_migrasi_webp.sql    (izinkan berkas WEBP)
--   03_seed_uji_sla.sql    (OPSIONAL: data uji fitur SLA)
--   (90_supabase_all_in_one.sql = alternatif khusus Supabase, jangan dijalankan bersama 01+02)
--
-- Isi: skema utama + migrasi notifikasi/ulasan + balasan + komentar
--      + ulasan per penanganan.
--
-- PENTING:
-- 1. Jalankan pada database BARU/kosong. Bagian RESET pada file ini
--    menghapus tabel inti lama beserta datanya (CASCADE).
-- 2. Jalankan file ini SATU KALI dari atas sampai bawah.
-- 3. Tidak perlu menjalankan file migrasi terpisah atau perbaikan.sql.
-- =====================================================================

-- ======================= 1. SKEMA UTAMA =============================
-- =====================================================================
-- SIPEKA - Sistem Pengaduan Kampus
-- Skema basis data revisi (PostgreSQL 12+)
-- Mata kuliah: Basis Data Lanjut
--
-- Isi file:
--   1. Reset (aman dijalankan ulang)
--   2. Tipe ENUM & tabel (10 tabel, 3NF) + constraint
--   3. Index
--   4. Trigger & stored procedure
--   5. View
--   6. Data uji realistis
--   7. Query lanjut yang dipakai fitur aplikasi
-- =====================================================================
-- ---------------------------------------------------------------------
-- 1. RESET
-- ---------------------------------------------------------------------
DROP VIEW IF EXISTS v_rekap_unit,
v_laporan_unit;

DROP TABLE IF EXISTS bukti_penanganan,
penanganan,
tanggapan,
riwayat_status,
bukti_laporan,
laporan,
mahasiswa,
users,
kategori,
unit CASCADE;

DROP FUNCTION IF EXISTS fn_riwayat_awal(),
fn_sinkron_status_laporan(),
fn_validasi_unit_penanggung_jawab() CASCADE;

DROP TYPE IF EXISTS status_laporan,
role_user CASCADE;

-- ---------------------------------------------------------------------
-- 2. TIPE & TABEL
-- ---------------------------------------------------------------------
CREATE TYPE role_user AS ENUM ('mahasiswa', 'unit');

CREATE TYPE status_laporan AS ENUM (
    'menunggu',
    'dalam_penanganan',
    'selesai',
    'ditolak'
);

-- Unit penanggung jawab: Sekjur, Admin Prodi, Kajur, KPS
CREATE TABLE unit (
    id_unit SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    nama_unit VARCHAR(50) NOT NULL,
    CONSTRAINT uq_unit_nama UNIQUE (nama_unit)
);

-- Kategori membawa id_unit => aturan "direct routing" tersimpan di data,
-- sehingga laporan tidak perlu menyimpan id_unit (menghindari dependensi transitif).
CREATE TABLE kategori (
    id_kategori SMALLINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_unit SMALLINT NOT NULL,
    nama_kategori VARCHAR(60) NOT NULL,
    deskripsi TEXT,
    CONSTRAINT uq_kategori_nama UNIQUE (nama_kategori),
    CONSTRAINT fk_kategori_unit FOREIGN KEY (id_unit) REFERENCES unit (id_unit) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- "user" adalah reserved word di PostgreSQL, maka tabel diberi nama "users".
-- Akun mahasiswa: role='mahasiswa', id_unit NULL.
-- Akun petugas  : role='unit',      id_unit terisi.
CREATE TABLE users (
    id_user INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_unit SMALLINT,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role role_user NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_users_email UNIQUE (email),
    CONSTRAINT ck_users_email CHECK (
        email ~* '^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$'
    ),
    CONSTRAINT ck_users_role_unit CHECK (
        (
            role = 'mahasiswa'
            AND id_unit IS NULL
        )
        OR (
            role = 'unit'
            AND id_unit IS NOT NULL
        )
    ),
    CONSTRAINT fk_users_unit FOREIGN KEY (id_unit) REFERENCES unit (id_unit) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Profil khusus mahasiswa (1 user : 0..1 mahasiswa). Nama diambil dari users.nama.
CREATE TABLE mahasiswa (
    id_mahasiswa INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_user INT NOT NULL,
    nim VARCHAR(15) NOT NULL,
    program_studi VARCHAR(60) NOT NULL,
    nomor_telepon VARCHAR(20),
    CONSTRAINT uq_mahasiswa_user UNIQUE (id_user),
    CONSTRAINT uq_mahasiswa_nim UNIQUE (nim),
    CONSTRAINT ck_mahasiswa_nim CHECK (nim ~ '^[0-9]{8,15}$'),
    CONSTRAINT ck_mahasiswa_telp CHECK (
        nomor_telepon IS NULL
        OR nomor_telepon ~ '^(\+62|0)[0-9]{8,13}$'
    ),
    CONSTRAINT fk_mahasiswa_user FOREIGN KEY (id_user) REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Entitas inti. Unit penanggung jawab diturunkan lewat kategori.
-- status = status TERKINI (disinkronkan otomatis oleh trigger dari riwayat_status).
CREATE TABLE laporan (
    id_laporan BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_mahasiswa INT NOT NULL,
    id_kategori SMALLINT NOT NULL,
    judul_laporan VARCHAR(150) NOT NULL,
    lokasi VARCHAR(150),
    tanggal_kejadian DATE NOT NULL,
    kronologi TEXT NOT NULL,
    status status_laporan NOT NULL DEFAULT 'menunggu',
    tanggal_lapor TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_laporan_judul CHECK (char_length(btrim(judul_laporan)) >= 5),
    CONSTRAINT ck_laporan_kronologi CHECK (char_length(btrim(kronologi)) >= 20),
    CONSTRAINT fk_laporan_mahasiswa FOREIGN KEY (id_mahasiswa) REFERENCES mahasiswa (id_mahasiswa) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_laporan_kategori FOREIGN KEY (id_kategori) REFERENCES kategori (id_kategori) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE bukti_laporan (
    id_bukti BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_laporan BIGINT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(500) NOT NULL,
    jenis_file VARCHAR(100) NOT NULL,
    -- MIME type
    uploaded_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_bukti_laporan_path UNIQUE (path_file),
    CONSTRAINT ck_bukti_laporan_jenis CHECK (
        jenis_file IN ('image/jpeg', 'image/png', 'application/pdf')
    ),
    CONSTRAINT fk_bukti_laporan_laporan FOREIGN KEY (id_laporan) REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE
);

-- Linimasa perubahan status. id_user = siapa yang mengubah (akuntabilitas).
-- Tabel Status_Laporan dihapus -> diganti ENUM status_laporan.
CREATE TABLE riwayat_status (
    id_riwayat BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_laporan BIGINT NOT NULL,
    id_user INT NOT NULL,
    status status_laporan NOT NULL,
    tanggal_perubahan TIMESTAMPTZ NOT NULL DEFAULT now(),
    keterangan TEXT,
    CONSTRAINT fk_riwayat_laporan FOREIGN KEY (id_laporan) REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_riwayat_user FOREIGN KEY (id_user) REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- id_user = petugas unit yang menulis (unit-nya divalidasi oleh trigger).
CREATE TABLE tanggapan (
    id_tanggapan BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_laporan BIGINT NOT NULL,
    id_user INT NOT NULL,
    isi_tanggapan TEXT NOT NULL,
    tanggal_tanggapan TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_tanggapan_isi CHECK (char_length(btrim(isi_tanggapan)) > 0),
    CONSTRAINT fk_tanggapan_laporan FOREIGN KEY (id_laporan) REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_tanggapan_user FOREIGN KEY (id_user) REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
);

CREATE TABLE penanganan (
    id_penanganan BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_laporan BIGINT NOT NULL,
    id_user INT NOT NULL,
    keterangan_penanganan TEXT NOT NULL,
    tanggal_penanganan TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_penanganan_ket CHECK (char_length(btrim(keterangan_penanganan)) > 0),
    CONSTRAINT fk_penanganan_laporan FOREIGN KEY (id_laporan) REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_penanganan_user FOREIGN KEY (id_user) REFERENCES users (id_user) ON UPDATE CASCADE ON DELETE RESTRICT
);

-- Struktur kolom diseragamkan dengan bukti_laporan.
CREATE TABLE bukti_penanganan (
    id_bukti BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    id_penanganan BIGINT NOT NULL,
    nama_file VARCHAR(255) NOT NULL,
    path_file VARCHAR(500) NOT NULL,
    jenis_file VARCHAR(100) NOT NULL,
    uploaded_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_bukti_penanganan_path UNIQUE (path_file),
    CONSTRAINT ck_bukti_penanganan_jenis CHECK (
        jenis_file IN ('image/jpeg', 'image/png', 'application/pdf')
    ),
    CONSTRAINT fk_bukti_penanganan_penanganan FOREIGN KEY (id_penanganan) REFERENCES penanganan (id_penanganan) ON UPDATE CASCADE ON DELETE CASCADE
);

-- ---------------------------------------------------------------------
-- 3. INDEX (PK dan UNIQUE sudah otomatis ter-index; ini untuk FK & filter)
-- ---------------------------------------------------------------------
CREATE INDEX idx_users_unit ON users (id_unit)
WHERE
    id_unit IS NOT NULL;

CREATE INDEX idx_kategori_unit ON kategori (id_unit);

CREATE INDEX idx_laporan_mahasiswa ON laporan (id_mahasiswa, tanggal_lapor DESC);

CREATE INDEX idx_laporan_kategori_stat ON laporan (id_kategori, status);

CREATE INDEX idx_bukti_laporan_laporan ON bukti_laporan (id_laporan);

CREATE INDEX idx_riwayat_laporan ON riwayat_status (id_laporan, tanggal_perubahan);

CREATE INDEX idx_tanggapan_laporan ON tanggapan (id_laporan);

CREATE INDEX idx_penanganan_laporan ON penanganan (id_laporan);

CREATE INDEX idx_bukti_penanganan_pen ON bukti_penanganan (id_penanganan);

-- ---------------------------------------------------------------------
-- 4. TRIGGER & STORED PROCEDURE
-- ---------------------------------------------------------------------
-- 4a. Setiap laporan baru otomatis punya riwayat pertama 'menunggu'.
CREATE FUNCTION fn_riwayat_awal() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN
INSERT INTO
    riwayat_status (
        id_laporan,
        id_user,
        status,
        tanggal_perubahan,
        keterangan
    )
SELECT
    NEW.id_laporan,
    m.id_user,
    'menunggu' :: status_laporan,
    NEW.tanggal_lapor,
    'Laporan dikirim dan diteruskan otomatis ke unit penanggung jawab'
FROM
    mahasiswa m
WHERE
    m.id_mahasiswa = NEW.id_mahasiswa;

RETURN NULL;

END $$;

CREATE TRIGGER trg_laporan_riwayat_awal
AFTER
INSERT
    ON laporan FOR EACH ROW EXECUTE FUNCTION fn_riwayat_awal();

-- 4b. Setiap riwayat baru menyinkronkan laporan.status & updated_at.
--     Status hanya punya satu "penulis" (riwayat_status) -> tidak bisa tidak konsisten.
CREATE FUNCTION fn_sinkron_status_laporan() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN
UPDATE
    laporan
SET
    status = NEW.status,
    updated_at = NEW.tanggal_perubahan
WHERE
    id_laporan = NEW.id_laporan;

RETURN NULL;

END $$;

CREATE TRIGGER trg_riwayat_sinkron_status
AFTER
INSERT
    ON riwayat_status FOR EACH ROW EXECUTE FUNCTION fn_sinkron_status_laporan();

-- 4c. Hanya petugas dari unit penanggung jawab yang boleh menanggapi/menangani.
CREATE FUNCTION fn_validasi_unit_penanggung_jawab() RETURNS trigger LANGUAGE plpgsql AS $$ DECLARE v_unit_user SMALLINT;

v_unit_laporan SMALLINT;

BEGIN
SELECT
    id_unit INTO v_unit_user
FROM
    users
WHERE
    id_user = NEW.id_user;

SELECT
    k.id_unit INTO v_unit_laporan
FROM
    laporan l
    JOIN kategori k ON k.id_kategori = l.id_kategori
WHERE
    l.id_laporan = NEW.id_laporan;

IF v_unit_user IS DISTINCT
FROM
    v_unit_laporan THEN RAISE EXCEPTION 'User % bukan petugas unit penanggung jawab laporan %',
    NEW.id_user,
    NEW.id_laporan USING ERRCODE = '42501';

END IF;

RETURN NEW;

END $$;

CREATE TRIGGER trg_tanggapan_validasi_unit BEFORE
INSERT
    ON tanggapan FOR EACH ROW EXECUTE FUNCTION fn_validasi_unit_penanggung_jawab();

CREATE TRIGGER trg_penanganan_validasi_unit BEFORE
INSERT
    ON penanganan FOR EACH ROW EXECUTE FUNCTION fn_validasi_unit_penanggung_jawab();

-- 4d. Stored procedure ubah status (satu transaksi, row lock, validasi alur).
--     Alur: menunggu -> dalam_penanganan -> selesai
--           (menunggu boleh langsung ke ditolak)
--     'selesai' mensyaratkan minimal satu catatan penanganan.
CREATE
OR REPLACE PROCEDURE sp_ubah_status(
    p_id_laporan BIGINT,
    p_id_user INT,
    p_status_baru status_laporan,
    p_keterangan TEXT DEFAULT NULL,
    p_waktu TIMESTAMPTZ DEFAULT now()
) LANGUAGE plpgsql AS $$ DECLARE v_status_lama status_laporan;

v_unit_laporan SMALLINT;

v_unit_user SMALLINT;

BEGIN
SELECT
    l.status,
    k.id_unit INTO v_status_lama,
    v_unit_laporan
FROM
    laporan l
    JOIN kategori k ON k.id_kategori = l.id_kategori
WHERE
    l.id_laporan = p_id_laporan FOR
UPDATE
    OF l;

-- cegah dua petugas mengubah bersamaan
IF NOT FOUND THEN RAISE EXCEPTION 'Laporan % tidak ditemukan',
p_id_laporan;

END IF;

SELECT
    id_unit INTO v_unit_user
FROM
    users
WHERE
    id_user = p_id_user;

IF v_unit_user IS DISTINCT
FROM
    v_unit_laporan THEN RAISE EXCEPTION 'User % bukan petugas unit penanggung jawab laporan %',
    p_id_user,
    p_id_laporan USING ERRCODE = '42501';

END IF;

IF NOT (
    (
        v_status_lama = 'menunggu'
        AND p_status_baru IN ('dalam_penanganan', 'ditolak')
    )
    OR (
        v_status_lama = 'dalam_penanganan'
        AND p_status_baru = 'selesai'
    )
) THEN RAISE EXCEPTION 'Perubahan status % -> % tidak diizinkan',
v_status_lama,
p_status_baru;

END IF;

IF p_status_baru = 'selesai'
AND NOT EXISTS (
    SELECT
        1
    FROM
        penanganan
    WHERE
        id_laporan = p_id_laporan
) THEN RAISE EXCEPTION 'Laporan % belum memiliki catatan penanganan',
p_id_laporan;

END IF;

-- trigger fn_sinkron_status_laporan akan memperbarui laporan.status
INSERT INTO
    riwayat_status (
        id_laporan,
        id_user,
        status,
        tanggal_perubahan,
        keterangan
    )
VALUES
    (
        p_id_laporan,
        p_id_user,
        p_status_baru,
        p_waktu,
        p_keterangan
    );

END $$;

-- ---------------------------------------------------------------------
-- 5. VIEW
-- ---------------------------------------------------------------------
-- Dashboard unit: daftar laporan masuk (aplikasi cukup menambah WHERE id_unit = ?)
CREATE VIEW v_laporan_unit AS
SELECT
    l.id_laporan,
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
    (
        SELECT
            COUNT(*)
        FROM
            bukti_laporan b
        WHERE
            b.id_laporan = l.id_laporan
    ) AS jumlah_bukti,
    (
        SELECT
            COUNT(*)
        FROM
            tanggapan t
        WHERE
            t.id_laporan = l.id_laporan
    ) AS jumlah_tanggapan
FROM
    laporan l
    JOIN kategori k ON k.id_kategori = l.id_kategori
    JOIN unit un ON un.id_unit = k.id_unit
    JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
    JOIN users u ON u.id_user = m.id_user;

-- Rekap & statistik per unit (fitur "rekap per unit")
CREATE VIEW v_rekap_unit AS
SELECT
    un.id_unit,
    un.nama_unit,
    k.nama_kategori,
    COUNT(l.id_laporan) AS total_laporan,
    COUNT(*) FILTER (
        WHERE
            l.status = 'menunggu'
    ) AS menunggu,
    COUNT(*) FILTER (
        WHERE
            l.status = 'dalam_penanganan'
    ) AS dalam_penanganan,
    COUNT(*) FILTER (
        WHERE
            l.status = 'selesai'
    ) AS selesai,
    COUNT(*) FILTER (
        WHERE
            l.status = 'ditolak'
    ) AS ditolak,
    ROUND(
        (
            AVG(
                EXTRACT(
                    EPOCH
                    FROM
                        (l.updated_at - l.tanggal_lapor)
                ) / 86400
            ) FILTER (
                WHERE
                    l.status = 'selesai'
            )
        ) :: numeric,
        1
    ) AS rata_hari_penyelesaian
FROM
    unit un
    JOIN kategori k ON k.id_unit = un.id_unit
    LEFT JOIN laporan l ON l.id_kategori = k.id_kategori
GROUP BY
    un.id_unit,
    un.nama_unit,
    k.id_kategori,
    k.nama_kategori;

-- ---------------------------------------------------------------------
-- 6. DATA UJI
--    (password_hash hanya placeholder; di aplikasi gunakan bcrypt/argon2)
-- ---------------------------------------------------------------------
INSERT INTO
    unit (nama_unit)
VALUES
    ('Sekjur'),
    ('Admin Prodi'),
    ('Kajur'),
    ('KPS');

INSERT INTO
    kategori (id_unit, nama_kategori, deskripsi)
SELECT
    un.id_unit,
    v.nama_kategori,
    v.deskripsi
FROM
    (
        VALUES
            (
                'Sekjur',
                'Fasilitas Kampus',
                'Kerusakan kelas, laboratorium, perpustakaan, toilet, dan sarana umum'
            ),
            (
                'Admin Prodi',
                'Pelayanan Akademik',
                'KRS, jadwal kuliah, nilai, dan administrasi prodi'
            ),
            (
                'Kajur',
                'Perilaku Dosen/Staf',
                'Ketidakdisiplinan atau dugaan pelanggaran kode etik dosen/staf'
            ),
            (
                'Kajur',
                'Pungutan Liar',
                'Biaya tidak resmi di luar ketentuan kampus'
            ),
            (
                'KPS',
                'Pelanggaran Mahasiswa',
                'Kecurangan akademik, penyalahgunaan fasilitas, pelanggaran tata tertib'
            )
    ) AS v(nama_unit, nama_kategori, deskripsi)
    JOIN unit un ON un.nama_unit = v.nama_unit;

-- Akun petugas unit
INSERT INTO
    users (id_unit, nama, email, password_hash, role)
SELECT
    un.id_unit,
    v.nama,
    v.email,
    '$2y$12$xvP/UsbVZHIjbJkp8pwXRuiNXEMYe5yBYoiBB0zwSrMRf/2.vJAMW',
    'unit' :: role_user
FROM
    (
        VALUES
            (
                'Sekjur',
                'Petugas Sekjur',
                'sekjur@sipeka.test'
            ),
            (
                'Admin Prodi',
                'Admin Prodi TI',
                'adminprodi@sipeka.test'
            ),
            (
                'Kajur',
                'Ketua Jurusan',
                'kajur@sipeka.test'
            ),
            (
                'KPS',
                'Koordinator Prodi',
                'kps@sipeka.test'
            )
    ) AS v(nama_unit, nama, email)
    JOIN unit un ON un.nama_unit = v.nama_unit;

-- Akun mahasiswa
INSERT INTO
    users (nama, email, password_hash, role)
VALUES
    (
        'Rina Kusuma',
        'rina@student.sipeka.test',
        '$2y$12$xvP/UsbVZHIjbJkp8pwXRuiNXEMYe5yBYoiBB0zwSrMRf/2.vJAMW',
        'mahasiswa'
    ),
    (
        'Bagas Saputra',
        'bagas@student.sipeka.test',
        '$2y$12$xvP/UsbVZHIjbJkp8pwXRuiNXEMYe5yBYoiBB0zwSrMRf/2.vJAMW',
        'mahasiswa'
    ),
    (
        'Dewi Lestari',
        'dewi@student.sipeka.test',
        '$2y$12$xvP/UsbVZHIjbJkp8pwXRuiNXEMYe5yBYoiBB0zwSrMRf/2.vJAMW',
        'mahasiswa'
    ),
    (
        'Fajar Nugroho',
        'fajar@student.sipeka.test',
        '$2y$12$xvP/UsbVZHIjbJkp8pwXRuiNXEMYe5yBYoiBB0zwSrMRf/2.vJAMW',
        'mahasiswa'
    ),
    (
        'Maya Anggraini',
        'maya@student.sipeka.test',
        '$2y$12$xvP/UsbVZHIjbJkp8pwXRuiNXEMYe5yBYoiBB0zwSrMRf/2.vJAMW',
        'mahasiswa'
    ),
    (
        'Rizky Maulana',
        'rizky@student.sipeka.test',
        '$2y$12$xvP/UsbVZHIjbJkp8pwXRuiNXEMYe5yBYoiBB0zwSrMRf/2.vJAMW',
        'mahasiswa'
    );

INSERT INTO
    mahasiswa (id_user, nim, program_studi, nomor_telepon)
SELECT
    u.id_user,
    v.nim,
    v.prodi,
    v.telp
FROM
    (
        VALUES
            (
                'rina@student.sipeka.test',
                '254107029001',
                'D-IV Teknik Informatika',
                '081234560001'
            ),
            (
                'bagas@student.sipeka.test',
                '254107029002',
                'D-IV Sistem Informasi Bisnis',
                '081234560002'
            ),
            (
                'dewi@student.sipeka.test',
                '254107029003',
                'D-IV Teknik Informatika',
                '081234560003'
            ),
            (
                'fajar@student.sipeka.test',
                '254107029004',
                'D-II PPLS',
                '081234560004'
            ),
            (
                'maya@student.sipeka.test',
                '254107029005',
                'D-IV Sistem Informasi Bisnis',
                '081234560005'
            ),
            (
                'rizky@student.sipeka.test',
                '254107029006',
                'D-IV Teknik Informatika',
                '081234560006'
            )
    ) AS v(email, nim, prodi, telp)
    JOIN users u ON u.email = v.email;

-- 12 laporan (trigger otomatis membuat riwayat awal 'menunggu')
INSERT INTO
    laporan (
        id_mahasiswa,
        id_kategori,
        judul_laporan,
        lokasi,
        tanggal_kejadian,
        kronologi,
        tanggal_lapor
    )
SELECT
    m.id_mahasiswa,
    k.id_kategori,
    v.judul,
    v.lokasi,
    v.tgl_kejadian :: date,
    v.kronologi,
    v.tgl_lapor :: timestamptz
FROM
    (
        VALUES
            (
                '254107029001',
                'Fasilitas Kampus',
                'AC Lab Jaringan tidak berfungsi',
                'Lab Jaringan Gedung Sipil Lt.2',
                '2026-09-01',
                'AC di Lab Jaringan mati sejak awal pekan sehingga ruangan sangat panas dan praktikum terganggu.',
                '2026-09-01 08:10+07'
            ),
            (
                '254107029003',
                'Fasilitas Kampus',
                'Toilet lantai 3 tidak ada air',
                'Toilet Gedung JTI Lt.3',
                '2026-09-27',
                'Air di toilet lantai 3 tidak mengalir selama tiga hari berturut-turut dan bau mulai menyebar.',
                '2026-09-28 07:45+07'
            ),
            (
                '254107029002',
                'Fasilitas Kampus',
                'Proyektor ruang LPR 12 buram',
                'Ruang LPR 12',
                '2026-10-05',
                'Proyektor di ruang LPR 12 gambarnya buram dan bergaris sehingga materi kuliah sulit dibaca.',
                '2026-10-05 09:30+07'
            ),
            (
                '254107029004',
                'Pelayanan Akademik',
                'Nilai UTS Basis Data belum keluar',
                'Sistem akademik',
                '2026-10-06',
                'Nilai UTS Basis Data kelas TI-2F belum muncul di sistem akademik padahal sudah lewat dua minggu.',
                '2026-10-06 11:00+07'
            ),
            (
                '254107029005',
                'Pelayanan Akademik',
                'KRS tidak bisa disahkan',
                'Sistem akademik',
                '2026-09-10',
                'KRS saya berstatus belum disahkan walau sudah diajukan sesuai jadwal dan batas waktu hampir habis.',
                '2026-09-10 10:20+07'
            ),
            (
                '254107029006',
                'Pelayanan Akademik',
                'Jadwal kuliah bentrok dua mata kuliah',
                'Sistem akademik',
                '2026-10-16',
                'Mata kuliah Basis Data dan Desain Web terjadwal pada jam yang sama di hari Kamis.',
                '2026-10-16 13:15+07'
            ),
            (
                '254107029001',
                'Perilaku Dosen/Staf',
                'Dosen sering terlambat masuk kelas',
                'Ruang LPR 3',
                '2026-09-30',
                'Dosen pengampu terlambat 30 menit atau lebih hampir di setiap pertemuan sehingga materi tidak tuntas.',
                '2026-10-01 08:00+07'
            ),
            (
                '254107029003',
                'Perilaku Dosen/Staf',
                'Ucapan tidak pantas dosen di kelas',
                'Ruang LPR 7',
                '2026-10-12',
                'Dosen menggunakan kata-kata merendahkan terhadap mahasiswa yang bertanya di depan kelas.',
                '2026-10-12 14:00+07'
            ),
            (
                '254107029002',
                'Pungutan Liar',
                'Biaya legalisir ijazah di luar ketentuan',
                'Ruang administrasi',
                '2026-09-19',
                'Petugas meminta biaya legalisir ijazah lebih tinggi dari tarif resmi tanpa memberikan kuitansi.',
                '2026-09-20 09:00+07'
            ),
            (
                '254107029004',
                'Pungutan Liar',
                'Permintaan uang pengambilan transkrip',
                'Ruang administrasi',
                '2026-10-08',
                'Saat mengambil transkrip nilai saya diminta membayar sejumlah uang yang tidak tertera di aturan kampus.',
                '2026-10-08 16:00+07'
            ),
            (
                '254107029005',
                'Pelanggaran Mahasiswa',
                'Dugaan kecurangan saat ujian',
                'Ruang LPR 5',
                '2026-09-14',
                'Beberapa mahasiswa terlihat bertukar jawaban dan membuka catatan saat UTS berlangsung.',
                '2026-09-15 10:00+07'
            ),
            (
                '254107029006',
                'Pelanggaran Mahasiswa',
                'Lab dipakai untuk bermain game',
                'Lab Multimedia',
                '2026-10-02',
                'Sekelompok mahasiswa memakai komputer lab di luar jam praktikum untuk bermain game hingga malam.',
                '2026-10-03 15:00+07'
            )
    ) AS v(
        nim,
        kategori,
        judul,
        lokasi,
        tgl_kejadian,
        kronologi,
        tgl_lapor
    )
    JOIN mahasiswa m ON m.nim = v.nim
    JOIN kategori k ON k.nama_kategori = v.kategori;

-- Bukti dari pelapor
INSERT INTO
    bukti_laporan (
        id_laporan,
        nama_file,
        path_file,
        jenis_file,
        uploaded_at
    )
SELECT
    l.id_laporan,
    v.nama_file,
    '/uploads/laporan/' || l.id_laporan || '/' || v.nama_file,
    v.jenis,
    l.tanggal_lapor
FROM
    (
        VALUES
            (
                'AC Lab Jaringan tidak berfungsi',
                'ac_mati_1.jpg',
                'image/jpeg'
            ),
            (
                'AC Lab Jaringan tidak berfungsi',
                'ac_mati_2.jpg',
                'image/jpeg'
            ),
            (
                'Toilet lantai 3 tidak ada air',
                'toilet_lt3.png',
                'image/png'
            ),
            (
                'Proyektor ruang LPR 12 buram',
                'proyektor.jpg',
                'image/jpeg'
            ),
            (
                'KRS tidak bisa disahkan',
                'screenshot_krs.png',
                'image/png'
            ),
            (
                'Dosen sering terlambat masuk kelas',
                'absensi_kelas.pdf',
                'application/pdf'
            ),
            (
                'Biaya legalisir ijazah di luar ketentuan',
                'chat_petugas.png',
                'image/png'
            ),
            (
                'Biaya legalisir ijazah di luar ketentuan',
                'nota_legalisir.jpg',
                'image/jpeg'
            ),
            (
                'Permintaan uang pengambilan transkrip',
                'rekaman_chat.png',
                'image/png'
            ),
            (
                'Dugaan kecurangan saat ujian',
                'foto_ujian.jpg',
                'image/jpeg'
            ),
            (
                'Lab dipakai untuk bermain game',
                'foto_lab_malam.jpg',
                'image/jpeg'
            )
    ) AS v(judul, nama_file, jenis)
    JOIN laporan l ON l.judul_laporan = v.judul;

-- Tanggapan unit (trigger memvalidasi petugas = unit penanggung jawab)
INSERT INTO
    tanggapan (
        id_laporan,
        id_user,
        isi_tanggapan,
        tanggal_tanggapan
    )
SELECT
    l.id_laporan,
    u.id_user,
    v.isi,
    v.waktu :: timestamptz
FROM
    (
        VALUES
            (
                'AC Lab Jaringan tidak berfungsi',
                'Laporan diterima, teknisi AC dijadwalkan memeriksa besok pagi.',
                '2026-09-01 13:00+07'
            ),
            (
                'Toilet lantai 3 tidak ada air',
                'Laporan diterima, kami koordinasi dengan bagian sarana prasarana.',
                '2026-09-28 10:00+07'
            ),
            (
                'Nilai UTS Basis Data belum keluar',
                'Kami hubungi dosen pengampu untuk segera menginput nilai.',
                '2026-10-07 09:00+07'
            ),
            (
                'KRS tidak bisa disahkan',
                'Kami cek ke dosen wali, mohon tunggu proses pengesahan.',
                '2026-09-10 14:00+07'
            ),
            (
                'Dosen sering terlambat masuk kelas',
                'Laporan diterima, akan dikonfirmasi ke dosen terkait.',
                '2026-10-02 10:00+07'
            ),
            (
                'Biaya legalisir ijazah di luar ketentuan',
                'Laporan diterima dan sedang ditelusuri bersama bagian administrasi.',
                '2026-09-21 09:00+07'
            ),
            (
                'Dugaan kecurangan saat ujian',
                'Laporan diterima, bukti foto akan diverifikasi bersama pengawas ujian.',
                '2026-09-15 15:00+07'
            ),
            (
                'Lab dipakai untuk bermain game',
                'Laporan diterima, kami cek log penggunaan lab.',
                '2026-10-04 09:00+07'
            )
    ) AS v(judul, isi, waktu)
    JOIN laporan l ON l.judul_laporan = v.judul
    JOIN kategori k ON k.id_kategori = l.id_kategori
    JOIN users u ON u.id_unit = k.id_unit;

-- Penanganan
INSERT INTO
    penanganan (
        id_laporan,
        id_user,
        keterangan_penanganan,
        tanggal_penanganan
    )
SELECT
    l.id_laporan,
    u.id_user,
    v.ket,
    v.waktu :: timestamptz
FROM
    (
        VALUES
            (
                'AC Lab Jaringan tidak berfungsi',
                'Teknisi mengganti kapasitor dan mengisi ulang freon AC.',
                '2026-09-03 09:00+07'
            ),
            (
                'Toilet lantai 3 tidak ada air',
                'Pompa air dalam perbaikan, air cadangan disediakan sementara.',
                '2026-10-02 09:00+07'
            ),
            (
                'KRS tidak bisa disahkan',
                'KRS disahkan oleh dosen wali dan status sudah diperbarui.',
                '2026-09-11 09:00+07'
            ),
            (
                'Biaya legalisir ijazah di luar ketentuan',
                'Petugas dipanggil dan diberi teguran serta pengingat tarif resmi.',
                '2026-09-23 10:00+07'
            ),
            (
                'Dugaan kecurangan saat ujian',
                'Mahasiswa terkait dipanggil dan diberi sanksi sesuai tata tertib.',
                '2026-09-16 09:00+07'
            )
    ) AS v(judul, ket, waktu)
    JOIN laporan l ON l.judul_laporan = v.judul
    JOIN kategori k ON k.id_kategori = l.id_kategori
    JOIN users u ON u.id_unit = k.id_unit;

-- Bukti penanganan
INSERT INTO
    bukti_penanganan (
        id_penanganan,
        nama_file,
        path_file,
        jenis_file,
        uploaded_at
    )
SELECT
    p.id_penanganan,
    v.nama_file,
    '/uploads/penanganan/' || p.id_penanganan || '/' || v.nama_file,
    v.jenis,
    p.tanggal_penanganan
FROM
    (
        VALUES
            (
                'AC Lab Jaringan tidak berfungsi',
                'ac_setelah_perbaikan.jpg',
                'image/jpeg'
            ),
            (
                'KRS tidak bisa disahkan',
                'krs_disahkan.png',
                'image/png'
            ),
            (
                'Dugaan kecurangan saat ujian',
                'berita_acara_sanksi.pdf',
                'application/pdf'
            )
    ) AS v(judul, nama_file, jenis)
    JOIN laporan l ON l.judul_laporan = v.judul
    JOIN penanganan p ON p.id_laporan = l.id_laporan;

-- Perubahan status lewat stored procedure (alur: menunggu -> dalam_penanganan -> selesai)
-- Laporan yang tidak dapat ditindaklanjuti dapat langsung berubah dari menunggu menjadi ditolak.
DO $$ DECLARE r RECORD;

v_laporan BIGINT;

v_petugas INT;

BEGIN FOR r IN
SELECT
    *
FROM
    (
        VALUES
            (
                'AC Lab Jaringan tidak berfungsi',
                'dalam_penanganan',
                'Perbaikan sedang dilakukan',
                '2026-09-03 09:05+07'
            ),
            (
                'AC Lab Jaringan tidak berfungsi',
                'selesai',
                'AC kembali normal',
                '2026-09-04 15:00+07'
            ),
            (
                'Toilet lantai 3 tidak ada air',
                'dalam_penanganan',
                'Perbaikan pompa air',
                '2026-10-02 09:05+07'
            ),
            (
                'Nilai UTS Basis Data belum keluar',
                'dalam_penanganan',
                'Menunggu input nilai dari dosen',
                '2026-10-07 09:05+07'
            ),
            (
                'KRS tidak bisa disahkan',
                'dalam_penanganan',
                'Dikoordinasikan dengan dosen wali',
                '2026-09-10 14:05+07'
            ),
            (
                'KRS tidak bisa disahkan',
                'selesai',
                'KRS sudah disahkan',
                '2026-09-11 11:00+07'
            ),
            (
                'Dosen sering terlambat masuk kelas',
                'dalam_penanganan',
                'Dikonfirmasi ke dosen terkait',
                '2026-10-02 10:05+07'
            ),
            (
                'Biaya legalisir ijazah di luar ketentuan',
                'dalam_penanganan',
                'Pemanggilan petugas terkait',
                '2026-09-23 10:05+07'
            ),
            (
                'Dugaan kecurangan saat ujian',
                'dalam_penanganan',
                'Pemanggilan mahasiswa terkait',
                '2026-09-16 09:05+07'
            ),
            (
                'Dugaan kecurangan saat ujian',
                'selesai',
                'Sanksi telah dijatuhkan',
                '2026-09-18 10:00+07'
            ),
            (
                'Lab dipakai untuk bermain game',
                'ditolak',
                'Bukti tidak cukup untuk ditindaklanjuti',
                '2026-10-04 11:00+07'
            )
    ) AS s(judul, status_baru, ket, waktu) LOOP
SELECT
    l.id_laporan,
    u.id_user INTO v_laporan,
    v_petugas
FROM
    laporan l
    JOIN kategori k ON k.id_kategori = l.id_kategori
    JOIN users u ON u.id_unit = k.id_unit
WHERE
    l.judul_laporan = r.judul;

CALL sp_ubah_status(
    v_laporan,
    v_petugas,
    r.status_baru :: status_laporan,
    r.ket,
    r.waktu :: timestamptz
);

END LOOP;

END $$;

-- ---------------------------------------------------------------------
-- 7. QUERY LANJUT YANG DIPAKAI FITUR APLIKASI
-- ---------------------------------------------------------------------
-- Q1. Rekap & statistik per unit (JOIN multi-tabel + GROUP BY + FILTER)
--     Fitur: "Rekap dan statistik per unit". Aplikasi: ... WHERE id_unit = $1
SELECT
    *
FROM
    v_rekap_unit
ORDER BY
    nama_unit,
    nama_kategori;

-- Q2. Peringatan laporan "tenggelam": menunggu > 3 hari & belum ada tanggapan
--     (SUBQUERY NOT EXISTS). Fitur: dashboard unit, menjawab kekhawatiran
--     mahasiswa bahwa laporan tidak dibaca.
SELECT
    l.id_laporan,
    l.judul_laporan,
    k.nama_kategori,
    un.nama_unit,
    l.tanggal_lapor,
    EXTRACT(
        DAY
        FROM
            now() - l.tanggal_lapor
    ) :: int AS hari_menunggu
FROM
    laporan l
    JOIN kategori k ON k.id_kategori = l.id_kategori
    JOIN unit un ON un.id_unit = k.id_unit
WHERE
    l.status = 'menunggu'
    AND l.tanggal_lapor < now() - INTERVAL '3 days'
    AND NOT EXISTS (
        SELECT
            1
        FROM
            tanggapan t
        WHERE
            t.id_laporan = l.id_laporan
    )
ORDER BY
    l.tanggal_lapor;

-- Q3. Beban kerja unit: kategori dengan >= 2 laporan belum final (GROUP BY + HAVING)
SELECT
    un.nama_unit,
    k.nama_kategori,
    COUNT(*) AS belum_selesai
FROM
    laporan l
    JOIN kategori k ON k.id_kategori = l.id_kategori
    JOIN unit un ON un.id_unit = k.id_unit
WHERE
    l.status NOT IN ('selesai', 'ditolak')
GROUP BY
    un.nama_unit,
    k.nama_kategori
HAVING
    COUNT(*) >= 2
ORDER BY
    belum_selesai DESC,
    un.nama_unit;

-- Q4. Linimasa status untuk mahasiswa (Riwayat & Pelacakan Status)
SELECT
    r.tanggal_perubahan,
    r.status,
    u.nama AS diubah_oleh,
    r.keterangan
FROM
    riwayat_status r
    JOIN users u ON u.id_user = r.id_user
WHERE
    r.id_laporan = (
        SELECT
            id_laporan
        FROM
            laporan
        WHERE
            judul_laporan = 'AC Lab Jaringan tidak berfungsi'
    )
ORDER BY
    r.tanggal_perubahan;

-- Q5. Dashboard satu unit (contoh: Kajur)
SELECT
    *
FROM
    v_laporan_unit
WHERE
    nama_unit = 'Kajur'
ORDER BY
    tanggal_lapor DESC;

-- ---------------------------------------------------------------------
-- UJI CEPAT (hapus tanda komentar untuk mencoba; semuanya HARUS gagal)
-- ---------------------------------------------------------------------
-- a) Petugas KPS menanggapi laporan fasilitas -> ditolak trigger
-- INSERT INTO tanggapan (id_laporan, id_user, isi_tanggapan)
-- SELECT l.id_laporan, (SELECT id_user FROM users WHERE email='kps@sipeka.test'), 'coba'
--   FROM laporan l WHERE l.judul_laporan = 'Proyektor ruang LPR 12 buram';
-- b) Loncat status menunggu -> selesai -> ditolak prosedur
-- CALL sp_ubah_status((SELECT id_laporan FROM laporan WHERE judul_laporan='Jadwal kuliah bentrok dua mata kuliah'),
--                     (SELECT id_user FROM users WHERE email='adminprodi@sipeka.test'), 'selesai');
-- c) Role mahasiswa tapi punya id_unit -> ditolak CHECK ck_users_role_unit
-- INSERT INTO users (id_unit, nama, email, password_hash, role) VALUES (1,'X','x@x.com','h','mahasiswa');

-- ======================= 2. NOTIFIKASI & ULASAN =====================
-- =====================================================================
-- SIPEKA - Migrasi tambahan: ULASAN + NOTIFIKASI
--
-- Bagian ini sudah menyatu di DB/01_skema_gabungan.sql (tidak perlu file terpisah).
--
-- Memakai tabel yang sudah dipakai kode backend:
--   users, mahasiswa, unit, kategori, laporan, tanggapan, riwayat_status
-- Isi tabel baru:
--   1. notifikasi  : satu baris per penerima (mahasiswa atau petugas unit)
--   2. ulasan      : satu ulasan per akun (mahasiswa atau unit), bisa diedit
--   3. trigger     : notifikasi dibuat OTOMATIS oleh database, jadi file PHP
--                    yang sudah ada (create.php, validasi.php, tanggapan.php,
--                    selesai.php) tidak perlu diubah.
--   4. view        : v_ulasan_publik untuk halaman depan
-- =====================================================================

BEGIN;

-- ---------------------------------------------------------------------
-- 1. NOTIFIKASI
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifikasi (
    id_notifikasi BIGSERIAL PRIMARY KEY,
    id_user       BIGINT       NOT NULL REFERENCES users(id_user)      ON DELETE CASCADE,
    id_laporan    BIGINT                REFERENCES laporan(id_laporan) ON DELETE CASCADE,
    jenis         VARCHAR(30)  NOT NULL,
    judul         VARCHAR(150) NOT NULL,
    pesan         TEXT         NOT NULL,
    sudah_dibaca  BOOLEAN      NOT NULL DEFAULT FALSE,
    created_at    TIMESTAMPTZ  NOT NULL DEFAULT now(),
    dibaca_at     TIMESTAMPTZ,
    CONSTRAINT ck_notifikasi_jenis CHECK (jenis IN (
        'laporan_baru',        -- ke petugas unit: ada laporan masuk
        'laporan_divalidasi',  -- ke mahasiswa: laporan diterima, masuk penanganan
        'laporan_ditolak',     -- ke mahasiswa: laporan ditolak
        'tanggapan_baru',      -- ke mahasiswa: unit menulis tanggapan
        'laporan_selesai'      -- ke mahasiswa: laporan selesai ditangani
    ))
);

-- daftar notifikasi terbaru per pengguna + hitung yang belum dibaca
CREATE INDEX IF NOT EXISTS idx_notifikasi_user_terbaru
    ON notifikasi (id_user, id_notifikasi DESC);
CREATE INDEX IF NOT EXISTS idx_notifikasi_belum_dibaca
    ON notifikasi (id_user) WHERE NOT sudah_dibaca;

-- ---------------------------------------------------------------------
-- 2. ULASAN  (satu akun = satu ulasan; mahasiswa maupun petugas unit)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ulasan (
    id_ulasan  BIGSERIAL PRIMARY KEY,
    id_user    BIGINT      NOT NULL REFERENCES users(id_user) ON DELETE CASCADE,
    rating     SMALLINT    NOT NULL,
    isi_ulasan TEXT        NOT NULL,
    tampil     BOOLEAN     NOT NULL DEFAULT TRUE,   -- FALSE = disembunyikan dari halaman depan
    created_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT uq_ulasan_user  UNIQUE (id_user),
    CONSTRAINT ck_ulasan_rating CHECK (rating BETWEEN 1 AND 5),
    CONSTRAINT ck_ulasan_isi    CHECK (char_length(btrim(isi_ulasan)) BETWEEN 10 AND 500)
);

CREATE INDEX IF NOT EXISTS idx_ulasan_tampil_terbaru
    ON ulasan (created_at DESC) WHERE tampil;

-- Tampilan publik: dipakai halaman depan (tanpa email / data pribadi lain)
CREATE OR REPLACE VIEW v_ulasan_publik AS
SELECT ul.id_ulasan,
       u.nama,
       u.role::text AS peran,
       un.nama_unit,
       ul.rating,
       ul.isi_ulasan,
       ul.created_at,
       ul.updated_at
FROM ulasan ul
JOIN users u        ON u.id_user = ul.id_user
LEFT JOIN unit un   ON un.id_unit = u.id_unit
WHERE ul.tampil;

-- ---------------------------------------------------------------------
-- 3. TRIGGER NOTIFIKASI
-- ---------------------------------------------------------------------

-- 3a. Laporan baru  ->  semua petugas unit penanggung jawab kategori tsb
CREATE OR REPLACE FUNCTION fn_notif_laporan_baru() RETURNS trigger
LANGUAGE plpgsql AS $$
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

DROP TRIGGER IF EXISTS trg_notif_laporan_baru ON laporan;
CREATE TRIGGER trg_notif_laporan_baru
    AFTER INSERT ON laporan
    FOR EACH ROW EXECUTE FUNCTION fn_notif_laporan_baru();

-- 3b. Status berubah (dicatat di riwayat_status oleh sp_ubah_status) -> mahasiswa pelapor
CREATE OR REPLACE FUNCTION fn_notif_status_berubah() RETURNS trigger
LANGUAGE plpgsql AS $$
DECLARE
    v_user   BIGINT;
    v_judul  TEXT;
    v_tiket  TEXT;
    v_jenis  TEXT;
    v_title  TEXT;
    v_pesan  TEXT;
BEGIN
    -- status awal 'menunggu' dibuat trigger laporan; tidak perlu diberitahukan
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

DROP TRIGGER IF EXISTS trg_notif_status_berubah ON riwayat_status;
CREATE TRIGGER trg_notif_status_berubah
    AFTER INSERT ON riwayat_status
    FOR EACH ROW EXECUTE FUNCTION fn_notif_status_berubah();

-- 3c. Tanggapan unit -> mahasiswa pelapor
CREATE OR REPLACE FUNCTION fn_notif_tanggapan_baru() RETURNS trigger
LANGUAGE plpgsql AS $$
DECLARE
    v_user  BIGINT;
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

DROP TRIGGER IF EXISTS trg_notif_tanggapan_baru ON tanggapan;
CREATE TRIGGER trg_notif_tanggapan_baru
    AFTER INSERT ON tanggapan
    FOR EACH ROW EXECUTE FUNCTION fn_notif_tanggapan_baru();

COMMIT;

-- ---------------------------------------------------------------------
-- (Opsional) Uji cepat setelah migrasi:
--   SELECT * FROM notifikasi ORDER BY id_notifikasi DESC LIMIT 5;
--   -- buat 1 laporan dari akun mahasiswa -> petugas unit terkait mendapat notifikasi
--   -- validasi / tolak / tanggapi / selesaikan dari akun unit -> mahasiswa mendapat notifikasi
--   -- sembunyikan ulasan tertentu dari halaman depan:
--   --   UPDATE ulasan SET tampil = FALSE WHERE id_ulasan = 1;
-- ---------------------------------------------------------------------


-- ======================= 3. BALASAN ULASAN ==========================
-- =====================================================================
-- SIPEKA - Migrasi 2: BALASAN ULASAN (mahasiswa & unit saling menjawab)
-- Bagian ini sudah menyatu di DB/01_skema_gabungan.sql (tidak perlu file terpisah).
-- =====================================================================
BEGIN;

CREATE TABLE IF NOT EXISTS ulasan_balasan (
    id_balasan  BIGSERIAL PRIMARY KEY,
    id_ulasan   BIGINT      NOT NULL REFERENCES ulasan(id_ulasan) ON DELETE CASCADE,
    id_user     BIGINT      NOT NULL REFERENCES users(id_user)    ON DELETE CASCADE,
    isi_balasan TEXT        NOT NULL,
    created_at  TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_balasan_isi CHECK (char_length(btrim(isi_balasan)) BETWEEN 2 AND 300)
);
CREATE INDEX IF NOT EXISTS idx_balasan_ulasan ON ulasan_balasan (id_ulasan, id_balasan);

-- Jenis notifikasi baru: 'balasan_ulasan'
ALTER TABLE notifikasi DROP CONSTRAINT IF EXISTS ck_notifikasi_jenis;
ALTER TABLE notifikasi ADD CONSTRAINT ck_notifikasi_jenis CHECK (jenis IN (
    'laporan_baru', 'laporan_divalidasi', 'laporan_ditolak',
    'tanggapan_baru', 'laporan_selesai', 'balasan_ulasan'
));

-- Balasan baru -> pemilik ulasan + peserta lain di ulasan itu (kecuali penulis balasan)
CREATE OR REPLACE FUNCTION fn_notif_balasan_ulasan() RETURNS trigger
LANGUAGE plpgsql AS $$
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

DROP TRIGGER IF EXISTS trg_notif_balasan_ulasan ON ulasan_balasan;
CREATE TRIGGER trg_notif_balasan_ulasan
    AFTER INSERT ON ulasan_balasan
    FOR EACH ROW EXECUTE FUNCTION fn_notif_balasan_ulasan();

COMMIT;


-- ======================= 4. ULASAN KOMENTAR =========================
-- =====================================================================
-- SIPEKA - Migrasi 3: ULASAN = KOMENTAR (tanpa bintang, tidak dibatasi 1 per akun)
-- Bagian ini sudah menyatu di DB/01_skema_gabungan.sql (tidak perlu file terpisah).
-- Aman diulang. Data ulasan lama tetap ada (nilai bintang lama diabaikan).
-- =====================================================================
BEGIN;

-- 1 akun boleh menulis banyak ulasan
ALTER TABLE ulasan DROP CONSTRAINT IF EXISTS uq_ulasan_user;

-- bintang tidak dipakai lagi (kolom dipertahankan agar data lama aman)
ALTER TABLE ulasan ALTER COLUMN rating DROP NOT NULL;

-- komentar boleh lebih singkat: 3-500 karakter
ALTER TABLE ulasan DROP CONSTRAINT IF EXISTS ck_ulasan_isi;
ALTER TABLE ulasan ADD CONSTRAINT ck_ulasan_isi
    CHECK (char_length(btrim(isi_ulasan)) BETWEEN 3 AND 500);

-- ulasan per akun (daftar milik sendiri) dan urutan terbaru
CREATE INDEX IF NOT EXISTS idx_ulasan_user ON ulasan (id_user, id_ulasan DESC);
CREATE INDEX IF NOT EXISTS idx_ulasan_waktu ON ulasan (created_at DESC);

COMMIT;


-- ======================= 5. ULASAN PER PENANGANAN ===================
-- =====================================================================
-- SIPEKA - Migrasi 4: ULASAN PER PENANGANAN, HANYA MAHASISWA
--   * ulasan boleh dikaitkan ke satu laporan yang SUDAH SELESAI ditangani
--     (id_laporan, opsional: kosong = ulasan umum)
--   * hanya akun mahasiswa yang bisa menulis ulasan (unit hanya membalas)
--   * unit penanggung jawab mendapat notifikasi saat ulasan penanganannya masuk
-- Bagian ini sudah menyatu di DB/01_skema_gabungan.sql (tidak perlu file terpisah).
-- Aman diulang. Ulasan lama milik unit TIDAK dihapus, hanya tidak ditampilkan lagi.
-- =====================================================================
BEGIN;

-- 1. Kaitan ke penanganan (laporan). Laporan terhapus -> ulasan menjadi ulasan umum.
ALTER TABLE ulasan
    ADD COLUMN IF NOT EXISTS id_laporan BIGINT
    REFERENCES laporan (id_laporan) ON UPDATE CASCADE ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_ulasan_laporan ON ulasan (id_laporan) WHERE id_laporan IS NOT NULL;

-- 2. Validasi di database (selain di PHP): penulis harus mahasiswa; laporan harus milik
--    penulis dan berstatus selesai.
CREATE OR REPLACE FUNCTION fn_validasi_ulasan() RETURNS trigger
LANGUAGE plpgsql AS $$
DECLARE
    v_role    TEXT;
    v_status  TEXT;
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

DROP TRIGGER IF EXISTS trg_validasi_ulasan ON ulasan;
CREATE TRIGGER trg_validasi_ulasan
    BEFORE INSERT ON ulasan
    FOR EACH ROW EXECUTE FUNCTION fn_validasi_ulasan();

-- 3. Jenis notifikasi baru: 'ulasan_baru' (ke petugas unit penanggung jawab laporan itu)
ALTER TABLE notifikasi DROP CONSTRAINT IF EXISTS ck_notifikasi_jenis;
ALTER TABLE notifikasi ADD CONSTRAINT ck_notifikasi_jenis CHECK (jenis IN (
    'laporan_baru', 'laporan_divalidasi', 'laporan_ditolak',
    'tanggapan_baru', 'laporan_selesai', 'balasan_ulasan', 'ulasan_baru'
));

CREATE OR REPLACE FUNCTION fn_notif_ulasan_baru() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
    IF NEW.id_laporan IS NULL THEN
        RETURN NEW;               -- ulasan umum tidak ditujukan ke unit tertentu
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

DROP TRIGGER IF EXISTS trg_notif_ulasan_baru ON ulasan;
CREATE TRIGGER trg_notif_ulasan_baru
    AFTER INSERT ON ulasan
    FOR EACH ROW EXECUTE FUNCTION fn_notif_ulasan_baru();

COMMIT;


-- ======================= SELESAI ====================================
