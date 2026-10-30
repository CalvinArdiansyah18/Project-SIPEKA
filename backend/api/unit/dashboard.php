<?php
// GET /backend/api/unit/dashboard.php?status=all&q=
// Data dashboard petugas unit: ringkasan + daftar laporan yang masuk ke UNIT-nya.
// Unit ditentukan dari SESSION (users.id_unit), bukan dari input frontend,
// sehingga petugas tidak bisa melihat laporan unit lain.
// Parameter (opsional):
//   status : all | menunggu | proses (diproses + dalam_penanganan) | diproses |
//            dalam_penanganan | selesai | ditolak
//   q      : kata kunci (no. tiket, judul, nama pelapor, atau NIM)
//   limit  : jumlah baris maksimal (default 100, maks 200)
//
// Jawaban:
// { "unit": {"id_unit","nama_unit"}, "stats": {...}, "laporan": [ {...} ] }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}

$user = butuh_login('unit');                   // mahasiswa yang memanggil -> 403
$id_unit = $user['id_unit'];
if ($id_unit === null) {
    json_out(['message' => 'Akun unit tidak terhubung ke unit manapun'], 403);
}

//filter (daftar putih, bukan memasukkan input langsung ke SQL)
$kondisiStatus = [
    'menunggu' => "status = 'menunggu'",
    'proses' => "status IN ('diproses','dalam_penanganan')",
    'diproses' => "status = 'diproses'",
    'dalam_penanganan' => "status = 'dalam_penanganan'",
    'selesai' => "status = 'selesai'",
    'ditolak' => "status = 'ditolak'",
];

$where = ['id_unit = :unit'];
$params = [':unit' => $id_unit];

$status = trim($_GET['status'] ?? 'all');
if ($status !== '' && $status !== 'all') {
    if (!isset($kondisiStatus[$status])) {
        json_out(['message' => 'Filter status tidak dikenal'], 400);
    }
    $where[] = $kondisiStatus[$status];
}

$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    // nama parameter harus berbeda-beda pada PDO pgsql
    $where[] = '(' . sql_no_tiket() . ' ILIKE :q1 OR judul_laporan ILIKE :q2 OR nama_pelapor ILIKE :q3 OR nim ILIKE :q4)';
    $like = '%' . addcslashes($q, '%_\\') . '%';
    foreach ([':q1', ':q2', ':q3', ':q4'] as $p) {
        $params[$p] = $like;
    }
}

$limit = max(1, min(200, (int)($_GET['limit'] ?? 100)));

//1. nama unit
$st = $pdo->prepare('SELECT id_unit, nama_unit FROM unit WHERE id_unit = :u');
$st->execute([':u' => $id_unit]);
$unit = $st->fetch();

//2. ringkasan, dari VIEW v_rekap_unit (tidak terpengaruh filter)
$st = $pdo->prepare(
    'SELECT COALESCE(SUM(total_laporan), 0) AS total,
            COALESCE(SUM(menunggu), 0)      AS menunggu,
            COALESCE(SUM(diproses), 0) + COALESCE(SUM(dalam_penanganan), 0) AS proses,
            COALESCE(SUM(selesai), 0)       AS selesai,
            COALESCE(SUM(ditolak), 0)       AS ditolak
        FROM v_rekap_unit
    WHERE id_unit = :u'
);
$st->execute([':u' => $id_unit]);
$stats = rapikan_stats($st->fetch());

// 3. daftar laporan, dari VIEW v_laporan_unit
// Urutan: laporan yang masih aktif lebih dulu (yang paling lama menunggu di atas),
// lalu laporan selesai/ditolak (terbaru di atas).
$sql = "SELECT id_laporan, " . sql_no_tiket() . " AS no_tiket,
            nama_kategori, judul_laporan, lokasi, tanggal_kejadian, tanggal_lapor, status, nama_pelapor, nim, program_studi, jumlah_bukti, jumlah_tanggapan,
            EXTRACT(DAY FROM now() - tanggal_lapor)::int AS hari_sejak_lapor, (status = 'menunggu' AND jumlah_tanggapan = 0 AND tanggal_lapor < now() - INTERVAL '3 days') AS perlu_perhatian
        FROM v_laporan_unit
    WHERE " . implode(' AND ', $where) . "
    ORDER BY (status IN ('selesai','ditolak')) ASC, CASE WHEN status IN ('selesai','ditolak') THEN NULL ELSE tanggal_lapor END ASC, tanggal_lapor DESC
    LIMIT :lim";

$st = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$st->bindValue(':lim', $limit, PDO::PARAM_INT);
$st->execute();

$laporan = array_map(function ($r) {
    $r['id_laporan'] = (int)$r['id_laporan'];
    $r['jumlah_bukti'] = (int)$r['jumlah_bukti'];
    $r['jumlah_tanggapan'] = (int)$r['jumlah_tanggapan'];
    $r['hari_sejak_lapor'] = (int)$r['hari_sejak_lapor'];
    $r['perlu_perhatian'] = ke_bool($r['perlu_perhatian']);
    return $r;
}, $st->fetchAll());

json_out(['unit' => $unit, 'stats' => $stats, 'laporan' => $laporan]);
