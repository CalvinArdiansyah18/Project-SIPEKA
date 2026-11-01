<?php
// GET /backend/api/laporan/kategori.php
// Daftar kategori beserta unit penanggung jawabnya (untuk form buat laporan).
// Jawaban: { "kategori": [ { "id_kategori", "nama_kategori", "deskripsi", "id_unit", "nama_unit" } ] }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}
butuh_login('mahasiswa');

$rows = $pdo->query(
    'SELECT k.id_kategori, k.nama_kategori, k.deskripsi, un.id_unit, un.nama_unit
       FROM kategori k
       JOIN unit un ON un.id_unit = k.id_unit
      ORDER BY k.id_kategori'
)->fetchAll();

foreach ($rows as &$r) {
    $r['id_kategori'] = (int)$r['id_kategori'];
    $r['id_unit']     = (int)$r['id_unit'];
}
unset($r);

json_out(['kategori' => $rows]);
