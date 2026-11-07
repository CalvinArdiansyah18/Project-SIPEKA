<?php
// GET /backend/api/laporan/bukti.php?id=<id_bukti>
// Menyajikan berkas bukti laporan. Folder backend/uploads tidak bisa dibuka langsung dari browser,
// jadi SEMUA akses berkas harus lewat endpoint ini.
//
// Hak akses:
//   - mahasiswa : hanya bukti dari laporan miliknya
//   - unit      : hanya bukti dari laporan yang masuk ke unitnya
// Selain itu dijawab 404 (keberadaan berkas tidak dibocorkan).

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}

$user = butuh_login(); // semua peran boleh masuk; hak akses dicek per berkas di bawah
$id_bukti = (int)($_GET['id'] ?? 0);
if ($id_bukti <= 0) {
    json_out(['message' => 'ID bukti tidak valid'], 400);
}

$sumber = ($_GET['sumber'] ?? '') === 'penanganan' ? 'penanganan' : 'laporan';
if ($sumber === 'penanganan') {
    $sql = 'SELECT b.nama_file, b.path_file, b.jenis_file, l.id_mahasiswa, k.id_unit
        FROM bukti_penanganan b
        JOIN penanganan p ON p.id_penanganan = b.id_penanganan
        JOIN laporan l ON l.id_laporan  = p.id_laporan
        JOIN kategori k ON k.id_kategori = l.id_kategori
        WHERE b.id_bukti = :id';
} else {
    $sql = 'SELECT b.nama_file, b.path_file, b.jenis_file, l.id_mahasiswa, k.id_unit
        FROM bukti_laporan b
        JOIN laporan l ON l.id_laporan  = b.id_laporan
        JOIN kategori k ON k.id_kategori = l.id_kategori
        WHERE b.id_bukti = :id';
}

$st = $pdo->prepare($sql);
$st->execute([':id' => $id_bukti]);
$b = $st->fetch();

$boleh = false;
if ($b) {
    if ($user['role'] === 'mahasiswa') {
        $boleh = ($user['id_mahasiswa'] !== null && (int)$b['id_mahasiswa'] === (int)$user['id_mahasiswa']);
    } elseif ($user['role'] === 'unit') {
        $boleh = ($user['id_unit'] !== null && (int)$b['id_unit'] === (int)$user['id_unit']);
    }
}
if (!$boleh) {
    json_out(['message' => 'Berkas tidak ditemukan'], 404);
}

// Pastikan path akhir benar-benar berada di dalam folder uploads (anti path traversal)
$root = realpath(folder_uploads());
$full = realpath($root . '/' . $b['path_file']);
if ($root === false || $full === false || strncmp($full, $root . DIRECTORY_SEPARATOR, strlen($root) + 1) !== 0 || !is_file($full)) {
    json_out(['message' => 'Berkas tidak ditemukan di server'], 404);
}

// Nama untuk unduhan: ASCII sederhana + versi UTF-8
$nama_ascii = preg_replace('/[^\x20-\x7E]/', '_', $b['nama_file']);
$nama_ascii = str_replace(['"', '\\'], '_', $nama_ascii);

session_write_close(); // lepaskan kunci session agar permintaan lain tidak menunggu

header('Content-Type: ' . $b['jenis_file']);
header('Content-Length: ' . filesize($full));
header('Content-Disposition: inline; filename="' . $nama_ascii . '"; filename*=UTF-8\'\'' . rawurlencode($b['nama_file']));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, max-age=3600');
readfile($full);
exit;
