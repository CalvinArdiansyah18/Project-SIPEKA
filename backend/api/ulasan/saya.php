<?php
// Menulis / menghapus ULASAN milik akun yang sedang login.
// HANYA MAHASISWA yang boleh menulis ulasan (petugas unit hanya membalas lewat ulasan/balas.php).
// Ulasan berupa komentar (tanpa bintang); satu akun boleh menulis banyak ulasan.
//
// POST /backend/api/ulasan/saya.php  Body JSON: { "isi": "3-500 karakter", "id_laporan": 12 | null }
//      id_laporan (opsional) = penanganan yang diulas: harus laporan milik sendiri berstatus selesai.
//      -> 201 { "message", "id_ulasan" }
// POST /backend/api/ulasan/saya.php  Body JSON: { "hapus": true, "id_ulasan": 7 }
//      -> hapus ulasan MILIK SENDIRI (balasan pada ulasan itu ikut terhapus)
//
// Daftar ulasan dibaca lewat ulasan/list.php; pilihan penanganan lewat ulasan/laporan-saya.php.

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$user = butuh_login();
$in = json_in();

// ---------- hapus ----------
if (!empty($in['hapus'])) {
    $id = (int)($in['id_ulasan'] ?? 0);
    if ($id <= 0) {
        json_out(['message' => 'id_ulasan wajib diisi'], 400);
    }
    $st = $pdo->prepare('DELETE FROM ulasan WHERE id_ulasan = :id AND id_user = :u');
    $st->execute([':id' => $id, ':u' => $user['id_user']]);
    if ($st->rowCount() === 0) {
        json_out(['message' => 'Ulasan tidak ditemukan atau bukan milik Anda'], 404);
    }
    json_out(['message' => 'Ulasan dihapus.']);
}

// ---------- tulis (hanya mahasiswa) ----------
if ($user['role'] !== 'mahasiswa') {
    json_out(['message' => 'Hanya mahasiswa yang dapat menulis ulasan. Petugas unit dapat membalas ulasan.'], 403);
}

$isi = trim((string)($in['isi'] ?? ''));
if (panjang_teks($isi) < 3 || panjang_teks($isi) > 500) {
    json_out(['message' => 'Ulasan harus 3-500 karakter'], 400);
}

$idLaporan = isset($in['id_laporan']) && $in['id_laporan'] !== '' ? (int)$in['id_laporan'] : null;
if ($idLaporan !== null) {
    $st = $pdo->prepare(
        'SELECT l.status::text
           FROM laporan l
           JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
          WHERE l.id_laporan = :l AND m.id_user = :u'
    );
    $st->execute([':l' => $idLaporan, ':u' => $user['id_user']]);
    $status = $st->fetchColumn();
    if ($status === false) {
        json_out(['message' => 'Penanganan yang dipilih tidak ditemukan'], 400);
    }
    if ($status !== 'selesai') {
        json_out(['message' => 'Hanya penanganan yang sudah selesai yang dapat diulas'], 400);
    }
}

try {
    $st = $pdo->prepare(
        'INSERT INTO ulasan (id_user, id_laporan, isi_ulasan) VALUES (:u, :l, :isi) RETURNING id_ulasan'
    );
    $st->execute([':u' => $user['id_user'], ':l' => $idLaporan, ':isi' => $isi]);
} catch (PDOException $e) {
    if ($e->getCode() === '23514') {   // ditolak trigger/CHECK di database
        json_out(['message' => 'Ulasan tidak memenuhi aturan validasi'], 400);
    }
    throw $e;
}

json_out(['message' => 'Ulasan Anda terkirim.', 'id_ulasan' => (int)$st->fetchColumn()], 201);
