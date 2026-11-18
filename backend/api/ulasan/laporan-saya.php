<?php
// GET /backend/api/ulasan/laporan-saya.php   (khusus mahasiswa)
// Daftar penanganan (laporan berstatus SELESAI milik sendiri) yang bisa dipilih untuk diulas.
//
// Jawaban:
// { "laporan": [ { "id_laporan", "no_tiket", "judul", "kategori", "unit", "jumlah_ulasan" }, ... ] }
//   jumlah_ulasan = berapa ulasan yang sudah Anda tulis untuk penanganan itu (boleh lebih dari satu)

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}

$user = butuh_login('mahasiswa');

$st = $pdo->prepare(
    'SELECT l.id_laporan, ' . sql_no_tiket('l.') . ' AS no_tiket, l.judul_laporan,
            k.nama_kategori, un.nama_unit,
            (SELECT COUNT(*) FROM ulasan x WHERE x.id_laporan = l.id_laporan AND x.id_user = :u2) AS jumlah_ulasan
       FROM laporan l
       JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
       JOIN kategori k  ON k.id_kategori = l.id_kategori
       JOIN unit un     ON un.id_unit = k.id_unit
      WHERE m.id_user = :u1 AND l.status = \'selesai\'
      ORDER BY l.updated_at DESC, l.id_laporan DESC'
);
$st->execute([':u1' => $user['id_user'], ':u2' => $user['id_user']]);

json_out(['laporan' => array_map(fn($r) => [
    'id_laporan' => (int)$r['id_laporan'],
    'no_tiket' => $r['no_tiket'],
    'judul' => $r['judul_laporan'],
    'kategori' => $r['nama_kategori'],
    'unit' => $r['nama_unit'],
    'jumlah_ulasan' => (int)$r['jumlah_ulasan'],
], $st->fetchAll())]);
