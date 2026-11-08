<?php
// GET /backend/api/laporan/detail.php?tiket=SPK-2026-00012
// Detail satu laporan milik mahasiswa yang sedang login: data laporan, linimasa status,
// tanggapan/penanganan unit, hasil penanganan (beserta bukti penanganan), dan daftar bukti.
// Laporan milik mahasiswa lain dijawab 404 (keberadaannya tidak dibocorkan).
//
// Jawaban:
// { "laporan": {...}, "riwayat": [...], "catatan_unit": [...], "bukti": [...],
//   "penanganan": [ { id_penanganan, keterangan, tanggal, petugas, bukti: [...] } ] }

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

$id_laporan = id_dari_tiket((string)($_GET['tiket'] ?? ''));
if ($id_laporan === null) {
    json_out(['message' => 'Format nomor tiket tidak valid'], 400);
}

// ---------- 1. data laporan (hanya jika milik mahasiswa ini) ----------
$st = $pdo->prepare(
    'SELECT l.id_laporan, ' . sql_no_tiket('l.') . ' AS no_tiket,
            l.judul_laporan, l.lokasi, l.tanggal_kejadian, l.kronologi, l.status,
            l.tanggal_lapor, l.updated_at,
            k.nama_kategori, un.nama_unit
    FROM laporan  l
    JOIN kategori k  ON k.id_kategori = l.id_kategori
    JOIN unit     un ON un.id_unit    = k.id_unit
    WHERE l.id_laporan = :id AND l.id_mahasiswa = :me'
);
$st->execute([':id' => $id_laporan, ':me' => $id_mahasiswa]);
$laporan = $st->fetch();
if (!$laporan) {
    json_out(['message' => 'Laporan tidak ditemukan'], 404);
}
$laporan['id_laporan'] = (int)$laporan['id_laporan'];
$laporan['tanggal_lapor'] = iso_waktu($laporan['tanggal_lapor']);
$laporan['updated_at'] = iso_waktu($laporan['updated_at']);

// ---------- 2. linimasa status (riwayat_status) ----------
$st = $pdo->prepare(
    'SELECT r.status, r.tanggal_perubahan, r.keterangan, u.nama AS diubah_oleh
    FROM riwayat_status r
    JOIN users u ON u.id_user = r.id_user
    WHERE r.id_laporan = :id
    ORDER BY r.tanggal_perubahan, r.id_riwayat'
);
$st->execute([':id' => $id_laporan]);
$riwayat = array_map(function ($r) {
    $r['tanggal_perubahan'] = iso_waktu($r['tanggal_perubahan']);
    return $r;
}, $st->fetchAll());

// ---------- 3. tanggapan + penanganan dari unit (digabung, urut waktu) ----------
$st = $pdo->prepare(
    "SELECT 'Tanggapan' AS jenis, t.isi_tanggapan AS isi, t.tanggal_tanggapan AS waktu, u.nama AS petugas
    FROM tanggapan t
    JOIN users u ON u.id_user = t.id_user
    WHERE t.id_laporan = :id1
    UNION ALL
    SELECT 'Penanganan', p.keterangan_penanganan, p.tanggal_penanganan, u.nama
    FROM penanganan p
    JOIN users u ON u.id_user = p.id_user
    WHERE p.id_laporan = :id2
    ORDER BY waktu"
);
$st->execute([':id1' => $id_laporan, ':id2' => $id_laporan]);
$catatan_unit = array_map(function ($r) {
    $r['waktu'] = iso_waktu($r['waktu']);
    return $r;
}, $st->fetchAll());

// ---------- 4. bukti yang diunggah pelapor ----------
$st = $pdo->prepare(
    'SELECT id_bukti, nama_file, jenis_file, uploaded_at
    FROM bukti_laporan
    WHERE id_laporan = :id
    ORDER BY id_bukti'
);
$st->execute([':id' => $id_laporan]);
$bukti = array_map(function ($r) {
    $r['id_bukti'] = (int)$r['id_bukti'];
    $r['uploaded_at'] = iso_waktu($r['uploaded_at']);
    return $r;
}, $st->fetchAll());

// ---------- 5. hasil penanganan + bukti penanganan dari unit ----------
// Berkas bukti dibuka lewat bukti.php?id=...&sumber=penanganan (pemilik laporan diizinkan).
$st = $pdo->prepare(
    'SELECT p.id_penanganan, p.keterangan_penanganan AS keterangan, p.tanggal_penanganan AS tanggal,
            u.nama AS petugas
    FROM penanganan p
    JOIN users u ON u.id_user = p.id_user
    WHERE p.id_laporan = :id
    ORDER BY p.tanggal_penanganan, p.id_penanganan'
);
$st->execute([':id' => $id_laporan]);
$penanganan = array_map(function ($r) {
    $r['id_penanganan'] = (int)$r['id_penanganan'];
    $r['tanggal'] = iso_waktu($r['tanggal']);
    $r['bukti'] = [];
    return $r;
}, $st->fetchAll());

if ($penanganan) {
    $st = $pdo->prepare(
        'SELECT b.id_bukti, b.id_penanganan, b.nama_file, b.jenis_file, b.uploaded_at
        FROM bukti_penanganan b
        JOIN penanganan p ON p.id_penanganan = b.id_penanganan
        WHERE p.id_laporan = :id
        ORDER BY b.id_bukti'
    );
    $st->execute([':id' => $id_laporan]);
    $buktiPenanganan = $st->fetchAll();
    foreach ($penanganan as &$p) {
        foreach ($buktiPenanganan as $b) {
            if ((int)$b['id_penanganan'] === $p['id_penanganan']) {
                $p['bukti'][] = [
                    'id_bukti' => (int)$b['id_bukti'],
                    'nama_file' => $b['nama_file'],
                    'jenis_file' => $b['jenis_file'],
                    'uploaded_at' => iso_waktu($b['uploaded_at']),
                ];
            }
        }
    }
    unset($p);
}

json_out([
    'laporan' => $laporan,
    'riwayat' => $riwayat,
    'catatan_unit' => $catatan_unit,
    'bukti' => $bukti,
    'penanganan' => $penanganan,
]);
