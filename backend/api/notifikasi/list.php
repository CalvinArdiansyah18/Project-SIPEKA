<?php
// GET /backend/api/notifikasi/list.php?limit=20
// Daftar notifikasi milik akun yang sedang login (mahasiswa ATAU unit) + jumlah yang belum dibaca.
// Notifikasi dibuat otomatis oleh trigger database (lihat DB/migrasi_ulasan_notifikasi.sql).
//
// Jawaban:
// { "belum_dibaca": 3,
//   "notifikasi": [ { "id_notifikasi", "jenis", "judul", "pesan", "sudah_dibaca", "waktu",
//                     "no_tiket", "status_laporan" }, ... ] }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET'], 405);
}

$user = butuh_login();                       // semua peran boleh; yang terbaca hanya milik sendiri
$limit = max(1, min(50, (int)($_GET['limit'] ?? 20)));

$st = $pdo->prepare('SELECT COUNT(*) FROM notifikasi WHERE id_user = :u AND NOT sudah_dibaca');
$st->execute([':u' => $user['id_user']]);
$belum = (int)$st->fetchColumn();

$st = $pdo->prepare(
    'SELECT n.id_notifikasi, n.jenis, n.judul, n.pesan, n.sudah_dibaca, n.created_at,
            ' . sql_no_tiket('l.') . ' AS no_tiket,
            l.status AS status_laporan
       FROM notifikasi n
       LEFT JOIN laporan l ON l.id_laporan = n.id_laporan
      WHERE n.id_user = :u
      ORDER BY n.id_notifikasi DESC
      LIMIT :lim'
);
$st->bindValue(':u', $user['id_user'], PDO::PARAM_INT);
$st->bindValue(':lim', $limit, PDO::PARAM_INT);
$st->execute();

$notifikasi = array_map(function ($r) {
    return [
        'id_notifikasi' => (int)$r['id_notifikasi'],
        'jenis' => $r['jenis'],
        'judul' => $r['judul'],
        'pesan' => $r['pesan'],
        'sudah_dibaca' => ke_bool($r['sudah_dibaca']),
        'waktu' => iso_waktu($r['created_at']),
        'no_tiket' => $r['no_tiket'],
        'status_laporan' => $r['status_laporan'],
    ];
}, $st->fetchAll());

json_out(['belum_dibaca' => $belum, 'notifikasi' => $notifikasi]);
