<?php
// GET /backend/api/auth/validasi.php
// Dipanggil di awal setiap halaman dashboard untuk memastikan user sudah login.
// 200 => data user dari session, 401 => belum login (frontend redirect ke login.html)

require __DIR__ . '/../../config/bootstrap.php';

$user = butuh_login();   // tanpa argumen: semua role boleh

$nama_unit = null;
if ($user['id_unit'] !== null) {
    $st = $pdo->prepare('SELECT nama_unit FROM unit WHERE id_unit = :id');
    $st->execute([':id' => $user['id_unit']]);
    $nama_unit = $st->fetchColumn() ?: null;
}

json_out([
    'nama'      => $user['nama'],
    'role'      => $user['role'],
    'nama_unit' => $nama_unit,
]);