<?php
// GET /backend/api/unit/rekap.php[?format=json|csv]
// Rekap laporan 30 hari terakhir untuk UNIT petugas yang sedang login.
// Unit ditentukan dari SESSION (users.id_unit), bukan dari input frontend,
// sehingga petugas tidak bisa melihat rekap unit lain.
//
// Parameter:
//   format : json (default) | csv  (csv = unduh berkas)
//
// Jawaban JSON:
// { "unit": {"id_unit","nama_unit"}, "periode": {"dari","sampai"},
//   "ringkasan": {"total","menunggu","dalam_penanganan","selesai","ditolak","rata_hari_penyelesaian"},
//   "per_kategori": [ { "nama_kategori","total","menunggu","dalam_penanganan","selesai","ditolak" } ],
//   "laporan": [ { "no_tiket","tanggal_lapor","nama_kategori","judul_laporan","lokasi",
//                  "nama_pelapor","nim","program_studi","status","updated_at" } ] }

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

$format = strtolower(trim((string)($_GET['format'] ?? 'json')));
if (!in_array($format, ['json', 'csv'], true)) {
    json_out(['message' => 'Format tidak dikenal, gunakan json atau csv'], 400);
}

// ---------- 1. nama unit & periode ----------
$st = $pdo->prepare('SELECT id_unit, nama_unit FROM unit WHERE id_unit = :u');
$st->execute([':u' => $id_unit]);
$unit = $st->fetch();
if (!$unit) {
    json_out(['message' => 'Unit tidak ditemukan'], 404);
}
$unit['id_unit'] = (int)$unit['id_unit'];

$st = $pdo->query("SELECT to_char(now() - INTERVAL '30 days', 'YYYY-MM-DD') AS dari,
                          to_char(now(), 'YYYY-MM-DD') AS sampai");
$periode = $st->fetch();

// Syarat periode: laporan masuk dalam 30 hari terakhir.
$kondisiPeriode = "l.tanggal_lapor >= now() - INTERVAL '30 days'";

// ---------- 2. ringkasan ----------
$st = $pdo->prepare(
    "SELECT COUNT(l.id_laporan) AS total,
            COUNT(*) FILTER (WHERE l.status = 'menunggu') AS menunggu,
            COUNT(*) FILTER (WHERE l.status = 'dalam_penanganan') AS dalam_penanganan,
            COUNT(*) FILTER (WHERE l.status = 'selesai') AS selesai,
            COUNT(*) FILTER (WHERE l.status = 'ditolak') AS ditolak,
            ROUND((
                AVG(EXTRACT(EPOCH FROM (l.updated_at - l.tanggal_lapor)) / 86400)
                FILTER (WHERE l.status = 'selesai')
            )::numeric, 1) AS rata_hari_penyelesaian
       FROM laporan l
       JOIN kategori k ON k.id_kategori = l.id_kategori
      WHERE k.id_unit = :unit
        AND {$kondisiPeriode}"
);
$st->execute([':unit' => $id_unit]);
$ringkasan = $st->fetch();
$ringkasan['total'] = (int)$ringkasan['total'];
$ringkasan['menunggu'] = (int)$ringkasan['menunggu'];
$ringkasan['dalam_penanganan'] = (int)$ringkasan['dalam_penanganan'];
$ringkasan['selesai'] = (int)$ringkasan['selesai'];
$ringkasan['ditolak'] = (int)$ringkasan['ditolak'];
$ringkasan['rata_hari_penyelesaian'] = $ringkasan['rata_hari_penyelesaian'] === null
    ? null
    : (float)$ringkasan['rata_hari_penyelesaian'];

// ---------- 3. rekap per kategori ----------
$st = $pdo->prepare(
    "SELECT k.nama_kategori,
            COUNT(l.id_laporan) AS total,
            COUNT(*) FILTER (WHERE l.status = 'menunggu') AS menunggu,
            COUNT(*) FILTER (WHERE l.status = 'dalam_penanganan') AS dalam_penanganan,
            COUNT(*) FILTER (WHERE l.status = 'selesai') AS selesai,
            COUNT(*) FILTER (WHERE l.status = 'ditolak') AS ditolak
       FROM kategori k
       LEFT JOIN laporan l ON l.id_kategori = k.id_kategori
                          AND {$kondisiPeriode}
      WHERE k.id_unit = :unit
      GROUP BY k.id_kategori, k.nama_kategori
      ORDER BY k.nama_kategori"
);
$st->execute([':unit' => $id_unit]);
$perKategori = array_map(function ($r) {
    foreach (['total', 'menunggu', 'dalam_penanganan', 'selesai', 'ditolak'] as $kolom) {
        $r[$kolom] = (int)$r[$kolom];
    }
    return $r;
}, $st->fetchAll());

// ---------- 4. daftar detail laporan ----------
$st = $pdo->prepare(
    "SELECT " . sql_no_tiket('l.') . " AS no_tiket,
            l.tanggal_lapor, l.updated_at,
            k.nama_kategori, l.judul_laporan, l.lokasi, l.status,
            u.nama AS nama_pelapor, m.nim, m.program_studi
       FROM laporan l
       JOIN kategori k ON k.id_kategori = l.id_kategori
       JOIN mahasiswa m ON m.id_mahasiswa = l.id_mahasiswa
       JOIN users u ON u.id_user = m.id_user
      WHERE k.id_unit = :unit
        AND {$kondisiPeriode}
      ORDER BY l.tanggal_lapor DESC, l.id_laporan DESC"
);
$st->execute([':unit' => $id_unit]);
$laporan = array_map(function ($r) {
    $r['tanggal_lapor'] = iso_waktu($r['tanggal_lapor']);
    $r['updated_at'] = iso_waktu($r['updated_at']);
    return $r;
}, $st->fetchAll());

// ---------- 5. keluaran CSV ----------
if ($format === 'csv') {
    session_write_close(); // lepaskan kunci session agar permintaan lain tidak menunggu

    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $unit['nama_unit']));
    $slug = trim($slug, '-') ?: 'unit';
    $namaFile = 'rekap-' . $slug . '-' . date('Ymd') . '.csv';

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $namaFile . '"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: no-store');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // BOM UTF-8 agar Excel membaca huruf dengan benar

    $labelStatus = [
        'menunggu' => 'Menunggu Validasi',
        'dalam_penanganan' => 'Dalam Penanganan',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    $formatWaktu = function ($iso) {
        if (!$iso) return '-';
        try {
            return (new DateTime((string)$iso))->format('Y-m-d H:i');
        } catch (Exception $e) {
            return (string)$iso;
        }
    };

    fputcsv($out, ['Rekap Laporan Unit'], ';');
    fputcsv($out, ['Unit', $unit['nama_unit']], ';');
    fputcsv($out, ['Periode', $periode['dari'] . ' s.d. ' . $periode['sampai']], ';');
    fputcsv($out, [], ';');

    fputcsv($out, ['Ringkasan'], ';');
    fputcsv($out, ['Status', 'Jumlah'], ';');
    fputcsv($out, ['Total Laporan', $ringkasan['total']], ';');
    foreach ($labelStatus as $kode => $label) {
        fputcsv($out, [$label, $ringkasan[$kode]], ';');
    }
    fputcsv($out, ['Rata-rata Penyelesaian (hari)', $ringkasan['rata_hari_penyelesaian'] ?? '-'], ';');
    fputcsv($out, [], ';');

    fputcsv($out, ['Rekap per Kategori'], ';');
    fputcsv($out, ['Kategori', 'Total', 'Menunggu', 'Dalam Penanganan', 'Selesai', 'Ditolak'], ';');
    foreach ($perKategori as $r) {
        fputcsv($out, [
            $r['nama_kategori'],
            $r['total'],
            $r['menunggu'],
            $r['dalam_penanganan'],
            $r['selesai'],
            $r['ditolak'],
        ], ';');
    }
    fputcsv($out, [], ';');

    fputcsv($out, ['Daftar Laporan'], ';');
    fputcsv($out, [
        'No. Tiket',
        'Tanggal Lapor',
        'Kategori',
        'Judul Laporan',
        'Lokasi',
        'Pelapor',
        'NIM',
        'Program Studi',
        'Status',
    ], ';');
    foreach ($laporan as $r) {
        fputcsv($out, [
            $r['no_tiket'],
            $formatWaktu($r['tanggal_lapor']),
            $r['nama_kategori'],
            $r['judul_laporan'],
            $r['lokasi'] ?? '-',
            $r['nama_pelapor'],
            $r['nim'],
            $r['program_studi'],
            $labelStatus[$r['status']] ?? $r['status'],
        ], ';');
    }

    fclose($out);
    exit;
}

// ---------- 6. keluaran JSON (default) ----------
json_out([
    'unit' => $unit,
    'periode' => $periode,
    'ringkasan' => $ringkasan,
    'per_kategori' => $perKategori,
    'laporan' => $laporan,
]);
