<?php
// POST /backend/api/laporan/create.php   (multipart/form-data)
//
// Field teks : id_kategori, judul_laporan, lokasi, tanggal_kejadian (YYYY-MM-DD), kronologi
// Field berkas (opsional): bukti  -> JPG / PNG / WEBP / PDF, maksimal 5 MB
//
// Yang dilakukan:
//   1. validasi semua input di server (validasi di browser hanya kenyamanan, bisa dilewati)
//   2. INSERT ke tabel laporan  -> trigger otomatis membuat riwayat awal "menunggu"
//   3. simpan berkas ke backend/uploads/laporan/<id_laporan>/ + INSERT ke tabel bukti_laporan
//   Semuanya dalam SATU transaksi: jika ada yang gagal, laporan dan berkas dibatalkan.
//
// Sukses (201): { "message", "id_laporan", "no_tiket" }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$user = butuh_login('mahasiswa');
$id_mahasiswa = $user['id_mahasiswa']; // dari SESSION, bukan dari input frontend
if ($id_mahasiswa === null) {
    json_out(['message' => 'Profil mahasiswa tidak ditemukan'], 404);
}

// Jika ukuran total kiriman melebihi post_max_size, PHP mengosongkan $_POST dan $_FILES
if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    json_out(['message' => 'Ukuran data yang dikirim melebihi batas server (post_max_size = '. ini_get('post_max_size') . ').'], 413);
}

// ---------- 1. ambil & validasi input ----------
$id_kategori = (int)($_POST['id_kategori'] ?? 0);
$judul = trim((string)($_POST['judul_laporan'] ?? ''));
$lokasi = trim((string)($_POST['lokasi'] ?? ''));
$tanggal = trim((string)($_POST['tanggal_kejadian'] ?? ''));
$kronologi = trim((string)($_POST['kronologi'] ?? ''));

if ($id_kategori <= 0) {
    json_out(['message' => 'Kategori pengaduan wajib dipilih'], 400);
}
if (panjang_teks($judul) < 10 || panjang_teks($judul) > 150) {
    json_out(['message' => 'Judul pengaduan harus 10-150 karakter'], 400);
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
if (panjang_teks($kronologi) < 30) {
    json_out(['message' => 'Kronologi terlalu singkat, minimal 30 karakter'], 400);
}

$st = $pdo->prepare('SELECT 1 FROM kategori WHERE id_kategori = :id');
$st->execute([':id' => $id_kategori]);
if (!$st->fetchColumn()) {
    json_out(['message' => 'Kategori pengaduan tidak ditemukan'], 400);
}

// Validasi berkas SEBELUM menyentuh database
$berkas = periksa_berkas_upload('bukti');

// ---------- 2 & 3. simpan dalam satu transaksi ----------
$path_tersimpan = null;
try {
    $pdo->beginTransaction();

    $st = $pdo->prepare(
        'INSERT INTO laporan (id_mahasiswa, id_kategori, judul_laporan, lokasi, tanggal_kejadian, kronologi)
         VALUES (:mhs, :kat, :judul, :lokasi, :tgl, :kronologi)
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

    if ($berkas !== null) {
        $path_tersimpan = simpan_berkas_upload($berkas, 'laporan/' . $id_laporan);

        $st = $pdo->prepare(
            'INSERT INTO bukti_laporan (id_laporan, nama_file, path_file, jenis_file)
             VALUES (:laporan, :nama, :path, :jenis)'
        );
        $st->execute([
            ':laporan' => $id_laporan,
            ':nama' => $berkas['nama'],
            ':path' => $path_tersimpan,
            ':jenis' => $berkas['mime'],
        ]);
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($path_tersimpan !== null) {
        @unlink(folder_uploads() . '/' . $path_tersimpan);   // jangan tinggalkan berkas yatim
    }
    throw $e; // ditangani handler di bootstrap.php (jawaban JSON 500)
}

json_out([
    'message' => 'Pengaduan berhasil diajukan',
    'id_laporan' => $id_laporan,
    'no_tiket' => $baris['no_tiket'],
], 201);
