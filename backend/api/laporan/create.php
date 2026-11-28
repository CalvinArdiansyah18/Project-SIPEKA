<?php
// POST /backend/api/laporan/create.php

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';
require_once __DIR__ . '/../../config/mail.php';

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$user = butuh_login('mahasiswa');
$id_mahasiswa = $user['id_mahasiswa'];

if ($id_mahasiswa === null) {
    json_out(['message' => 'Profil mahasiswa tidak ditemukan'], 404);
}

// Periksa batas ukuran kiriman
if (
    empty($_POST) &&
    empty($_FILES) &&
    (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
) {
    json_out([
        'message' => 'Ukuran data melebihi batas server (post_max_size = '
            . ini_get('post_max_size') . ').'
    ], 413);
}

// ---------- 1. Validasi input ----------
$id_kategori = (int)($_POST['id_kategori'] ?? 0);
$judul = trim((string)($_POST['judul_laporan'] ?? ''));
$lokasi = trim((string)($_POST['lokasi'] ?? ''));
$tanggal = trim((string)($_POST['tanggal_kejadian'] ?? ''));
$kronologi = trim((string)($_POST['kronologi'] ?? ''));

if ($id_kategori <= 0) {
    json_out(['message' => 'Kategori pengaduan wajib dipilih'], 400);
}

if (panjang_teks($judul) < 5 || panjang_teks($judul) > 150) {
    json_out(['message' => 'Judul pengaduan harus 5-150 karakter'], 400);
}

if ($lokasi === '' || panjang_teks($lokasi) > 150) {
    json_out(['message' => 'Lokasi kejadian wajib diisi (maksimal 150 karakter)'], 400);
}

$tgl = DateTime::createFromFormat('!Y-m-d', $tanggal);

if (!$tgl || $tgl->format('Y-m-d') !== $tanggal) {
    json_out(['message' => 'Format tanggal kejadian tidak valid'], 400);
}

if ($tgl > new DateTime('today')) {
    json_out(['message' => 'Tanggal kejadian tidak boleh lebih dari hari ini'], 400);
}

if (panjang_teks($kronologi) < 10) {
    json_out(['message' => 'Kronologi terlalu singkat, minimal 10 karakter'], 400);
}

$st = $pdo->prepare(
    'SELECT 1 FROM kategori WHERE id_kategori = :id'
);
$st->execute([':id' => $id_kategori]);

if (!$st->fetchColumn()) {
    json_out(['message' => 'Kategori pengaduan tidak ditemukan'], 400);
}

// Validasi berkas
$daftar_berkas = periksa_berkas_upload_multi('bukti');

// ---------- 2. Simpan laporan dalam transaksi ----------
$path_tersimpan = [];

try {
    $pdo->beginTransaction();

    $st = $pdo->prepare(
        'INSERT INTO laporan
            (id_mahasiswa, id_kategori, judul_laporan,
             lokasi, tanggal_kejadian, kronologi)
         VALUES
            (:mhs, :kat, :judul, :lokasi, :tgl, :kronologi)
         RETURNING id_laporan, ' . sql_no_tiket() . ' AS no_tiket'
    );

    $st->execute([
        ':mhs' => $id_mahasiswa,
        ':kat' => $id_kategori,
        ':judul' => $judul,
        ':lokasi' => $lokasi,
        ':tgl' => $tanggal,
        ':kronologi' => $kronologi,
    ]);

    $baris = $st->fetch();
    $id_laporan = (int)$baris['id_laporan'];

    // Simpan bukti laporan
    if ($daftar_berkas !== []) {
        $st = $pdo->prepare(
            'INSERT INTO bukti_laporan
                (id_laporan, nama_file, path_file, jenis_file)
             VALUES (:laporan, :nama, :path, :jenis)'
        );

        foreach ($daftar_berkas as $berkas) {
            $path = simpan_berkas_upload(
                $berkas,
                'laporan/' . $id_laporan
            );

            $path_tersimpan[] = $path;

            $st->execute([
                ':laporan' => $id_laporan,
                ':nama' => $berkas['nama'],
                ':path' => $path,
                ':jenis' => $berkas['mime'],
            ]);
        }
    }

    // Pastikan semua data berhasil disimpan
    $pdo->commit();

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    foreach ($path_tersimpan as $path) {
        @unlink(folder_uploads() . '/' . $path);
    }

    throw $e;
}

// ---------- 3. Kirim email kepada petugas unit ----------
// Bagian ini berjalan SETELAH commit.
// Kegagalan email tidak membatalkan laporan.

try {
    $st = $pdo->prepare(
        "SELECT
            u.nama,
            u.email
         FROM kategori k
         JOIN users u ON u.id_unit = k.id_unit
         WHERE k.id_kategori = :kategori
           AND u.role = 'unit'
         ORDER BY u.id_user"
    );

    $st->execute([':kategori' => $id_kategori]);
    $petugas = $st->fetchAll();

    $noTiket = $baris['no_tiket'];

    $pesan = 
        "Ada laporan pengaduan baru yang masuk ke SIPEKA.\n\n" .
        "Nomor tiket: {$noTiket}\n" .
        "Judul laporan: {$judul}\n" .
        "Lokasi kejadian: {$lokasi}\n" .
        "Tanggal kejadian: {$tanggal}\n\n" .
        "Silakan login ke portal unit SIPEKA untuk memeriksa " .
        "dan memproses laporan tersebut.";

    foreach ($petugas as $p) {
        kirimEmailSipeka(
            $p['email'],
            $p['nama'],
            'Laporan Baru - ' . $noTiket,
            $pesan
        );
    }

} catch (Throwable $e) {
    // Jangan gagalkan laporan jika pengiriman email bermasalah.
    error_log(
        'Notifikasi Gmail laporan baru gagal: ' . $e->getMessage()
    );
}

// ---------- 4. Respons API ----------
json_out([
    'message' => 'Pengaduan berhasil diajukan',
    'id_laporan' => $id_laporan,
    'no_tiket' => $baris['no_tiket'],
], 201);
