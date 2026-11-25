<?php
// GET /backend/api/unit/dashboard.php?status=all&q=
// Data dashboard petugas unit: ringkasan + daftar laporan yang masuk ke UNIT-nya.
// Unit ditentukan dari SESSION (users.id_unit), bukan dari input frontend,
// sehingga petugas tidak bisa melihat laporan unit lain.
// Parameter (opsional):
//   status : all | menunggu | dalam_penanganan | selesai | ditolak
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
    'menunggu' => "v.status = 'menunggu'",
    // 'proses' => "status IN ('diproses','dalam_penanganan')",
    // 'diproses' => "status = 'diproses'",
    'dalam_penanganan' => "v.status = 'dalam_penanganan'",
    'selesai' => "v.status = 'selesai'",
    'ditolak' => "v.status = 'ditolak'",
];

$where = ['v.id_unit = :unit'];
$params = [':unit' => $id_unit];

// Waktu acuan SLA per laporan (lihat laporan-helper.php).
$acuanSla = sql_acuan_sla('v.tanggal_lapor', 'v.id_laporan');

$status = trim($_GET['status'] ?? 'all');
if ($status !== '' && $status !== 'all') {
    if (!isset($kondisiStatus[$status])) {
        json_out(['message' => 'Filter status tidak dikenal'], 400);
    }
    $where[] = $kondisiStatus[$status];
}

// Filter "perlu perhatian": laporan yang melewati SLA.
//   - menunggu          : lewat SLA dan belum ada tanggapan
//   - dalam_penanganan  : tidak ada aktivitas baru melewati SLA
if (($_GET['perhatian'] ?? '') === '1') {
    $terlambat = sql_sla_terlambat($acuanSla);
    $where[] = "((v.status = 'menunggu' AND v.jumlah_tanggapan = 0 AND {$terlambat})
                OR (v.status = 'dalam_penanganan' AND {$terlambat}))";
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
    'SELECT COALESCE(SUM(menunggu), 0) AS menunggu,
            COALESCE(SUM(dalam_penanganan), 0) AS dalam_penanganan,
            COALESCE(SUM(selesai), 0) AS selesai,
            COALESCE(SUM(ditolak), 0) AS ditolak
    FROM v_rekap_unit
    WHERE id_unit = :u'
);
$st->execute([':u' => $id_unit]);
$stats = rapikan_stats($st->fetch());

// 2b. jumlah laporan yang melewati SLA (tidak terpengaruh filter)
$terlambatCount = sql_sla_terlambat($acuanSla);
$st = $pdo->prepare(
    "SELECT COUNT(*)
       FROM v_laporan_unit v
      WHERE v.id_unit = :u
        AND ((v.status = 'menunggu' AND v.jumlah_tanggapan = 0 AND {$terlambatCount})
             OR (v.status = 'dalam_penanganan' AND {$terlambatCount}))"
);
$st->execute([':u' => $id_unit]);
$stats['perlu_perhatian'] = (int)$st->fetchColumn();

// 3. daftar laporan, dari VIEW v_laporan_unit
// Urutan: laporan yang masih aktif lebih dulu (yang paling lama menunggu di atas),
// lalu laporan selesai/ditolak (terbaru di atas).
$sql = "SELECT v.id_laporan, "
            . sql_no_tiket('v.') . " AS no_tiket,
            v.nama_kategori, v.judul_laporan, v.lokasi, v.tanggal_kejadian, v.tanggal_lapor,
            v.status, v.nama_pelapor, v.nim, v.program_studi, v.jumlah_bukti, v.jumlah_tanggapan,
            {$acuanSla} AS sla_acuan
        FROM v_laporan_unit v
        WHERE " . implode(' AND ', $where) . "
        ORDER BY (v.status IN ('selesai','ditolak')) ASC,
                 CASE WHEN v.status IN ('selesai','ditolak') THEN NULL ELSE v.tanggal_lapor END ASC,
                 v.tanggal_lapor DESC
        LIMIT :lim";

$st = $pdo->prepare($sql);
foreach ($params as $k => $v) {
    $st->bindValue($k, $v, is_int($v) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$st->bindValue(':lim', $limit, PDO::PARAM_INT);
$st->execute();

$laporan = array_map(function ($r) {
    $jumlahTanggapan = (int)$r['jumlah_tanggapan'];
    $sla = sla_meta((string)$r['status'], $r['sla_acuan'], $jumlahTanggapan);

    $r['id_laporan'] = (int)$r['id_laporan'];
    $r['jumlah_bukti'] = (int)$r['jumlah_bukti'];
    $r['jumlah_tanggapan'] = $jumlahTanggapan;
    unset($r['sla_acuan']);
    return array_merge($r, $sla);
}, $st->fetchAll());

json_out(['unit' => $unit, 'stats' => $stats, 'laporan' => $laporan]);
