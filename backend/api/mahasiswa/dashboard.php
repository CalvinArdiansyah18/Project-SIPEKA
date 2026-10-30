<?php
// GET /backend/api/mahasiswa/dashboard.php?limit=5
// Data dashboard mahasiswa yang sedang login: ringkasan jumlah + laporan terbaru.
// Jawaban:
// {
//   "stats":   { "total", "menunggu", "proses", "selesai", "ditolak" },
//   "laporan": [ { "id_laporan", "no_tiket", "nama_kategori", "nama_unit", "judul_laporan",
//                  "tanggal_kejadian", "tanggal_lapor", "status" }, ... ]
// }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}

$user = butuh_login('mahasiswa');                 // unit yang memanggil -> 403
$id_mahasiswa = $user['id_mahasiswa'];            // diambil dari SESSION, bukan dari input frontend
if ($id_mahasiswa === null) {
    json_out(['message' => 'Profil mahasiswa tidak ditemukan'], 404);
}

$limit = max(1, min(20, (int)($_GET['limit'] ?? 5)));

// 1. Ringkasan jumlah laporan milik mahasiswa ini
$st = $pdo->prepare(
    "SELECT COUNT(*) AS total,
            COUNT(*) FILTER (WHERE status = 'menunggu') AS menunggu,
            COUNT(*) FILTER (WHERE status IN ('diproses','dalam_penanganan')) AS proses,
            COUNT(*) FILTER (WHERE status = 'selesai') AS selesai,
            COUNT(*) FILTER (WHERE status = 'ditolak') AS ditolak
       FROM laporan
      WHERE id_mahasiswa = :id"
);
$st->execute([':id' => $id_mahasiswa]);
$stats = rapikan_stats($st->fetch());

// 2. Laporan terbaru (JOIN laporan - kategori - unit)
$st = $pdo->prepare(
    'SELECT l.id_laporan,
            ' . sql_no_tiket('l.') . ' AS no_tiket,
            k.nama_kategori,
            un.nama_unit,
            l.judul_laporan,
            l.tanggal_kejadian,
            l.tanggal_lapor,
            l.status
        FROM laporan  l
        JOIN kategori k  ON k.id_kategori = l.id_kategori
        JOIN unit     un ON un.id_unit    = k.id_unit
    WHERE l.id_mahasiswa = :id
    ORDER BY l.tanggal_lapor DESC, l.id_laporan DESC
    LIMIT :lim'
);
$st->bindValue(':id', $id_mahasiswa, PDO::PARAM_INT);
$st->bindValue(':lim', $limit, PDO::PARAM_INT);
$st->execute();

$laporan = array_map(function ($r) {
    $r['id_laporan'] = (int)$r['id_laporan'];
    return $r;
}, $st->fetchAll());

json_out(['stats' => $stats, 'laporan' => $laporan]);
