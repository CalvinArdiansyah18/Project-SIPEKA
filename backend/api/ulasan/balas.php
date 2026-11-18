<?php
// Balasan pada ulasan MAHASISWA. Petugas unit dan mahasiswa boleh membalas.
//
// POST /backend/api/ulasan/balas.php  Body JSON: { "id_ulasan": 3, "isi": "2-300 karakter" }
//      -> 201 { "message", "id_balasan" }   (pemilik ulasan & peserta lain mendapat notifikasi dari trigger)
// POST /backend/api/ulasan/balas.php  Body JSON: { "hapus": true, "id_balasan": 9 }
//      -> hapus balasan MILIK SENDIRI

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$user = butuh_login();
$in = json_in();

// ---------- hapus ----------
if (!empty($in['hapus'])) {
    $id = (int)($in['id_balasan'] ?? 0);
    if ($id <= 0) {
        json_out(['message' => 'id_balasan wajib diisi'], 400);
    }
    $st = $pdo->prepare('DELETE FROM ulasan_balasan WHERE id_balasan = :id AND id_user = :u');
    $st->execute([':id' => $id, ':u' => $user['id_user']]);
    if ($st->rowCount() === 0) {
        json_out(['message' => 'Balasan tidak ditemukan atau bukan milik Anda'], 404);
    }
    json_out(['message' => 'Balasan dihapus.']);
}

// ---------- kirim ----------
$idUlasan = (int)($in['id_ulasan'] ?? 0);
$isi = trim((string)($in['isi'] ?? ''));

if ($idUlasan <= 0) {
    json_out(['message' => 'id_ulasan wajib diisi'], 400);
}
if (panjang_teks($isi) < 2 || panjang_teks($isi) > 300) {
    json_out(['message' => 'Balasan harus 2-300 karakter'], 400);
}

// hanya ulasan yang ditulis mahasiswa dan masih tampil
$st = $pdo->prepare(
    "SELECT 1 FROM ulasan ul JOIN users u ON u.id_user = ul.id_user
      WHERE ul.id_ulasan = :id AND ul.tampil AND u.role::text = 'mahasiswa'"
);
$st->execute([':id' => $idUlasan]);
if (!$st->fetchColumn()) {
    json_out(['message' => 'Ulasan tidak ditemukan'], 404);
}

$st = $pdo->prepare(
    'INSERT INTO ulasan_balasan (id_ulasan, id_user, isi_balasan)
     VALUES (:ul, :u, :isi) RETURNING id_balasan'
);
$st->execute([':ul' => $idUlasan, ':u' => $user['id_user'], ':isi' => $isi]);

json_out(['message' => 'Balasan terkirim.', 'id_balasan' => (int)$st->fetchColumn()], 201);
