<?php
// GET /backend/api/ulasan/list.php
// Ulasan MAHASISWA (tampil = TRUE) beserta balasannya, dengan FILTER. Dipakai:
//   - halaman depan (tanpa login, hanya dibaca; kirim hanya_unit=1 agar hanya balasan unit yang tampil)
//   - halaman Ulasan mahasiswa & unit (login -> "milik_saya" terisi untuk ulasan/balasan sendiri)
// Ulasan hanya berasal dari mahasiswa; unit hanya membalas. Nama mahasiswa disamarkan ("Budi P.").
// Ulasan bisa terkait satu penanganan: yang dibagikan ke publik hanya kategori & unitnya
// (judul laporan tidak pernah ditampilkan; nomor tiket hanya untuk pemilik ulasan).
//
// Parameter (semua opsional):
//   limit=10  offset=0
//   mode   = semua (default) | harian | bulanan | tahunan | rentang
//   tanggal= YYYY-MM-DD            (mode harian)
//   bulan  = YYYY-MM               (mode bulanan)
//   tahun  = YYYY                  (mode tahunan)
//   dari, sampai = YYYY-MM-DD      (mode rentang)
//   kategori = id kategori laporan yang diulas
//   q      = kata kunci di isi ulasan
//   hanya_unit = 1                 (hanya balasan petugas unit)
// Tanggal dihitung menurut zona waktu Asia/Jakarta.
//
// Jawaban:
// { "ringkasan": { "total": <sesuai filter>, "total_semua": <tanpa filter> },
//   "ada_lagi": true|false,
//   "kategori": [ {id_kategori, nama_kategori}, ... ],          // pilihan untuk filter
//   "ulasan": [ { "id_ulasan", "nama", "peran", "isi", "waktu", "milik_saya",
//                 "laporan": null | {kategori, unit, no_tiket|null},
//                 "jumlah_balasan", "balasan": [ {id_balasan, nama, peran, isi, waktu, milik_saya} ] } ] }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';
require __DIR__ . '/../../config/ulasan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}

$limit = max(1, min(50, (int)($_GET['limit'] ?? 10)));
$offset = max(0, (int)($_GET['offset'] ?? 0));
$saya = isset($_SESSION['user']['id_user']) ? (int)$_SESSION['user']['id_user'] : null;

$WAKTU = "(ul.created_at AT TIME ZONE 'Asia/Jakarta')";
$where = ['ul.tampil', "u.role::text = 'mahasiswa'"];   // ulasan hanya dari mahasiswa
$param = [];

function tanggal_valid(string $s): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $s);
    return $d && $d->format('Y-m-d') === $s;
}

// ---------- filter waktu ----------
$mode = (string)($_GET['mode'] ?? 'semua');
switch ($mode) {
    case 'semua':
        break;
    case 'harian':
        $t = (string)($_GET['tanggal'] ?? '');
        if (!tanggal_valid($t)) json_out(['message' => 'Tanggal tidak valid (YYYY-MM-DD)'], 400);
        $where[] = "$WAKTU::date = CAST(:tanggal AS date)";
        $param[':tanggal'] = $t;
        break;
    case 'bulanan':
        $b = (string)($_GET['bulan'] ?? '');
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $b)) json_out(['message' => 'Bulan tidak valid (YYYY-MM)'], 400);
        $where[] = "to_char($WAKTU, 'YYYY-MM') = :bulan";
        $param[':bulan'] = $b;
        break;
    case 'tahunan':
        $y = (string)($_GET['tahun'] ?? '');
        if (!preg_match('/^\d{4}$/', $y)) json_out(['message' => 'Tahun tidak valid (YYYY)'], 400);
        $where[] = "to_char($WAKTU, 'YYYY') = :tahun";
        $param[':tahun'] = $y;
        break;
    case 'rentang':
        $dari = (string)($_GET['dari'] ?? '');
        $sampai = (string)($_GET['sampai'] ?? '');
        if (!tanggal_valid($dari) || !tanggal_valid($sampai)) json_out(['message' => 'Rentang tanggal tidak valid (YYYY-MM-DD)'], 400);
        if ($dari > $sampai) { [$dari, $sampai] = [$sampai, $dari]; }
        $where[] = "$WAKTU::date BETWEEN CAST(:dari AS date) AND CAST(:sampai AS date)";
        $param[':dari'] = $dari;
        $param[':sampai'] = $sampai;
        break;
    default:
        json_out(['message' => 'mode tidak dikenal'], 400);
}

// ---------- filter kategori & kata kunci ----------
$kategori = (int)($_GET['kategori'] ?? 0);
if ($kategori > 0) {
    $where[] = 'k.id_kategori = :kategori';
    $param[':kategori'] = $kategori;
}
$q = trim((string)($_GET['q'] ?? ''));
if ($q !== '') {
    $where[] = "ul.isi_ulasan ILIKE :q ESCAPE '\\'";
    $param[':q'] = '%' . addcslashes($q, '%_\\') . '%';
}

$dari_sql = 'FROM ulasan ul
       JOIN users u         ON u.id_user = ul.id_user
       LEFT JOIN laporan l  ON l.id_laporan = ul.id_laporan
       LEFT JOIN kategori k ON k.id_kategori = l.id_kategori
       LEFT JOIN unit pun   ON pun.id_unit = k.id_unit
      WHERE ' . implode(' AND ', $where);

// ---------- hitung ----------
$st = $pdo->prepare("SELECT COUNT(*) $dari_sql");
$st->execute($param);
$total = (int)$st->fetchColumn();
$totalSemua = (int)$pdo->query(
    "SELECT COUNT(*) FROM ulasan ul JOIN users u ON u.id_user = ul.id_user
      WHERE ul.tampil AND u.role::text = 'mahasiswa'"
)->fetchColumn();
$daftarKategori = array_map(
    fn($k) => ['id_kategori' => (int)$k['id_kategori'], 'nama_kategori' => $k['nama_kategori']],
    $pdo->query('SELECT id_kategori, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll()
);

// ---------- data ----------
$st = $pdo->prepare(
    "SELECT ul.id_ulasan, ul.id_user, ul.isi_ulasan, ul.created_at,
            u.nama, u.role::text AS peran,
            l.id_laporan AS lap_id, " . sql_no_tiket('l.') . " AS no_tiket,
            k.nama_kategori, pun.nama_unit AS unit_penanganan
       $dari_sql
      ORDER BY ul.created_at DESC, ul.id_ulasan DESC
      LIMIT :lim OFFSET :off"
);
foreach ($param as $k => $v) $st->bindValue($k, $v);
$st->bindValue(':lim', $limit, PDO::PARAM_INT);
$st->bindValue(':off', $offset, PDO::PARAM_INT);
$st->execute();
$baris = $st->fetchAll();

$hanyaUnit = ($_GET['hanya_unit'] ?? '') === '1';
$balasan = muat_balasan($pdo, array_map(fn($r) => (int)$r['id_ulasan'], $baris), $saya, $hanyaUnit);

$ulasan = array_map(function ($r) use ($balasan, $saya) {
    $id = (int)$r['id_ulasan'];
    $b = $balasan[$id] ?? [];
    $milik = $saya !== null && (int)$r['id_user'] === $saya;
    return [
        'id_ulasan' => $id,
        'nama' => nama_tampil($r),
        'peran' => peran_tampil($r['peran']),
        'isi' => $r['isi_ulasan'],
        'waktu' => iso_waktu($r['created_at']),
        'milik_saya' => $milik,
        'laporan' => $r['lap_id'] ? [
            'kategori' => $r['nama_kategori'],
            'unit' => $r['unit_penanganan'],
            'no_tiket' => $milik ? $r['no_tiket'] : null,     // tiket hanya terlihat oleh pemilik
        ] : null,
        'jumlah_balasan' => count($b),
        'balasan' => $b,
    ];
}, $baris);

json_out([
    'ringkasan' => ['total' => $total, 'total_semua' => $totalSemua],
    'kategori' => $daftarKategori,
    'ada_lagi' => ($offset + count($ulasan)) < $total,
    'ulasan' => $ulasan,
]);
