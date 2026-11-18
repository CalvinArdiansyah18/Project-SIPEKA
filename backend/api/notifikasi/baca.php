<?php
// POST /backend/api/notifikasi/baca.php
// Body JSON : { "id_notifikasi": 12 }   -> tandai satu notifikasi sudah dibaca
//         atau { "semua": true }        -> tandai semua notifikasi milik akun ini
// Hanya notifikasi milik akun yang sedang login yang bisa diubah.
//
// Jawaban: { "belum_dibaca": 2 }

require __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$user = butuh_login();
$in = json_in();

if (!empty($in['semua'])) {
    $st = $pdo->prepare(
        'UPDATE notifikasi SET sudah_dibaca = TRUE, dibaca_at = now()
          WHERE id_user = :u AND NOT sudah_dibaca'
    );
    $st->execute([':u' => $user['id_user']]);
} else {
    $id = (int)($in['id_notifikasi'] ?? 0);
    if ($id <= 0) {
        json_out(['message' => 'id_notifikasi wajib diisi'], 400);
    }
    $st = $pdo->prepare(
        'UPDATE notifikasi SET sudah_dibaca = TRUE, dibaca_at = now()
          WHERE id_notifikasi = :id AND id_user = :u AND NOT sudah_dibaca'
    );
    $st->execute([':id' => $id, ':u' => $user['id_user']]);
}

$st = $pdo->prepare('SELECT COUNT(*) FROM notifikasi WHERE id_user = :u AND NOT sudah_dibaca');
$st->execute([':u' => $user['id_user']]);

json_out(['belum_dibaca' => (int)$st->fetchColumn()]);
