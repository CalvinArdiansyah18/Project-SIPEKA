<?php
// GET /backend/api/auth/me.php
// Dipanggil di awal setiap halaman dashboard untuk memastikan user sudah login.
// 200 => data user dari session, 401 => belum login (frontend redirect ke login.html)

require __DIR__ . '/../../config/bootstrap.php';

$user = butuh_login();   // tanpa argumen: semua role boleh

json_out([
    'nama'    => $user['nama'],
    'role'    => $user['role'],
    'id_unit' => $user['id_unit'],
]);