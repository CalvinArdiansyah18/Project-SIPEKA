<?php
// GET /backend/api/laporan/list.php?status=all&q=
// Riwayat pengaduan milik mahasiswa yang sedang login.
//
// Parameter (opsional):
//   status : all | menunggu | proses (diproses + dalam_penanganan) | diproses |
//            dalam_penanganan | selesai | ditolak
//   q      : kata kunci (no. tiket, judul, atau nama kategori)
//   limit  : jumlah baris maksimal (default 100, maks 200)
//
// Jawaban: { "laporan": [ { id_laporan, no_tiket, nama_kategori, nama_unit, judul_laporan,
//                           lokasi, tanggal_kejadian, tanggal_lapor, status } ] }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}

$user = butuh_login('mahasiswa');
$id_mahasiswa = $user['id_mahasiswa'];
if ($id_mahasiswa === null) {
    json_out(['message' => 'Profil mahasiswa tidak ditemukan'], 404);
}

$where  = ['l.id_mahasiswa = :me'];
$params = [':me' => $id_mahasiswa];

$status = trim($_GET['status'] ?? 'all');
if ($status !== '' && $status !== 'all') {
    $kondisi = kondisi_status('l.status');
    if (!isset($kondisi[$status])) {
        json_out(['message' => 'Filter status tidak dikenal'], 400);
    }
    $where[] = $kondisi[$status];
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    // nama parameter harus berbeda-beda pada PDO pgsql
    $where[] = '(' . sql_no_tiket('l.') . ' ILIKE :q1 OR l.judul_laporan ILIKE :q2 OR k.nama_kategori ILIKE :q3)';
    $like = '%' . addcslashes($q, '%_\\') . '%';
    foreach ([':q1', ':q2', ':q3'] as $p) {
        $params[$p] = $like;
    }
}

$limit = max(1, min(200, (int)($_GET['limit'] ?? 100)));

$sql = 'SELECT l.id_laporan,
               ' . sql_no_tiket('l.') . ' AS no_tiket,
               k.nama_kategori,
               un.nama_unit,
               l.judul_laporan,
               l.lokasi,
               l.tanggal_kejadian,
               l.tanggal_lapor,
               l.status
          FROM laporan  l
          JOIN kategori k  ON k.id_kategori = l.id_kategori
          JOIN unit     un ON un.id_unit    = k.id_unit
         WHERE ' . implode(' AND ', $where) . '
         ORDER BY l.tanggal_lapor DESC, l.id_laporan DESC
         LIMIT :lim';

$st = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$st->bindValue(':lim', $limit, PDO::PARAM_INT);
$st->execute();

$laporan = array_map(function ($r) {
    $r['id_laporan']    = (int)$r['id_laporan'];
    $r['tanggal_lapor'] = iso_waktu($r['tanggal_lapor']);
    return $r;
}, $st->fetchAll());

json_out(['laporan' => $laporan]);
