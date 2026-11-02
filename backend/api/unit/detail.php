<?php
// GET /backend/api/unit/detail.php?tiket=SPK-2026-00012
// Detail satu laporan untuk petugas unit yang sedang login.
// Unit ditentukan dari SESSION (users.id_unit). Laporan milik unit lain dijawab 404
// supaya keberadaannya tidak dibocorkan.
//
// Jawaban:
// { "laporan": {...}, "riwayat": [...], "catatan_unit": [...], "bukti": [...] }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}

$user = butuh_login('unit');
$id_unit = $user['id_unit'];
if ($id_unit === null) {
    json_out(['message' => 'Akun unit tidak terhubung ke unit manapun'], 403);
}

$id_laporan = id_dari_tiket((string)($_GET['tiket'] ?? ''));
if ($id_laporan === null) {
    json_out(['message' => 'Format nomor tiket tidak valid'], 400);
}

// ---------- 1. data laporan (hanya jika unit ini penanggung jawabnya) ----------
$st = $pdo->prepare(
    'SELECT l.id_laporan, ' . sql_no_tiket('l.') . ' AS no_tiket,
            l.judul_laporan, l.lokasi, l.tanggal_kejadian, l.kronologi, l.status,
            l.tanggal_lapor, l.updated_at,
            k.nama_kategori, un.id_unit, un.nama_unit,
            u.nama AS nama_pelapor, m.nim, m.program_studi
    FROM laporan  l
    JOIN kategori k  ON k.id_kategori = l.id_kategori
    JOIN unit     un ON un.id_unit    = k.id_unit
    JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
    JOIN users    u  ON u.id_user     = m.id_user
    WHERE l.id_laporan = :id AND k.id_unit = :unit'
);
$st->execute([':id' => $id_laporan, ':unit' => $id_unit]);
$laporan = $st->fetch();
if (!$laporan) {
    json_out(['message' => 'Laporan tidak ditemukan pada unit Anda'], 404);
}
$laporan['id_laporan'] = (int)$laporan['id_laporan'];
$laporan['id_unit'] = (int)$laporan['id_unit'];
$laporan['tanggal_lapor'] = iso_waktu($laporan['tanggal_lapor']);
$laporan['updated_at'] = iso_waktu($laporan['updated_at']);

// ---------- 2. linimasa status ----------
$st = $pdo->prepare(
    'SELECT r.status, r.tanggal_perubahan, r.keterangan, u.nama AS diubah_oleh
    FROM riwayat_status r
    JOIN users u ON u.id_user = r.id_user
    WHERE r.id_laporan = :id
    ORDER BY r.tanggal_perubahan, r.id_riwayat'
);
$st->execute([':id' => $id_laporan]);
$riwayat = array_map(function ($r) {
    $r['tanggal_perubahan'] = iso_waktu($r['tanggal_perubahan']);
    return $r;
}, $st->fetchAll());

// ---------- 3. tanggapan + penanganan unit ----------
$st = $pdo->prepare(
    "SELECT 'Tanggapan' AS jenis, t.isi_tanggapan AS isi, t.tanggal_tanggapan AS waktu, u.nama AS petugas
    FROM tanggapan t
    JOIN users u ON u.id_user = t.id_user
    WHERE t.id_laporan = :id1
    UNION ALL
    SELECT 'Penanganan', p.keterangan_penanganan, p.tanggal_penanganan, u.nama
    FROM penanganan p
    JOIN users u ON u.id_user = p.id_user
    WHERE p.id_laporan = :id2
    ORDER BY waktu"
);
$st->execute([':id1' => $id_laporan, ':id2' => $id_laporan]);
$catatan_unit = array_map(function ($r) {
    $r['waktu'] = iso_waktu($r['waktu']);
    return $r;
}, $st->fetchAll());

// ---------- 4. bukti yang diunggah pelapor ----------
$st = $pdo->prepare(
    'SELECT id_bukti, nama_file, jenis_file, uploaded_at
    FROM bukti_laporan
    WHERE id_laporan = :id
    ORDER BY id_bukti'
);
$st->execute([':id' => $id_laporan]);
$bukti = array_map(function ($r) {
    $r['id_bukti'] = (int)$r['id_bukti'];
    $r['uploaded_at'] = iso_waktu($r['uploaded_at']);
    return $r;
}, $st->fetchAll());

json_out([
    'laporan' => $laporan,
    'riwayat' => $riwayat,
    'catatan_unit' => $catatan_unit,
    'bukti' => $bukti,
]);
