<?php
// POST /backend/api/auth/login.php
// Body JSON : { "email": "<email atau NIM>", "password": "..." }
// Sukses    : { "nama": "...", "role": "mahasiswa|unit", "nama_unit": null|"..." }

require __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$in       = json_in();
$identitas = trim($in['email'] ?? '');       // boleh email atau NIM
$password  = $in['password'] ?? '';

if ($identitas === '' || $password === '') {
    json_out(['message' => 'Email/NIM dan password wajib diisi'], 400);
}

// Cari akun berdasarkan email ATAU NIM (nama parameter harus berbeda di PDO pgsql)
$st = $pdo->prepare(
    'SELECT u.id_user, u.nama, u.password_hash, u.role, u.id_unit, m.id_mahasiswa, un.nama_unit
    FROM users u
    LEFT JOIN mahasiswa m  ON m.id_user = u.id_user
    LEFT JOIN unit      un ON un.id_unit = u.id_unit
    WHERE LOWER(u.email) = LOWER(:ident_email)
    OR m.nim = :ident_nim
    LIMIT 1'
);
$st->execute([
    ':ident_email' => $identitas,
    ':ident_nim'   => $identitas,
]);
$u = $st->fetch();

// Pesan sengaja dibuat sama untuk "akun tidak ada" dan "password salah"
if (!$u || !password_verify($password, $u['password_hash'])) {
    json_out(['message' => 'Email/NIM atau password salah'], 401);
}

// Cegah session fixation: buat ID session baru setelah login berhasil
session_regenerate_id(true);

$_SESSION['user'] = [
    'id_user'      => (int)$u['id_user'],
    'nama'         => $u['nama'],
    'role'         => $u['role'],
    'id_unit'      => $u['id_unit'] !== null ? (int)$u['id_unit'] : null,
    'id_mahasiswa' => $u['id_mahasiswa'] !== null ? (int)$u['id_mahasiswa'] : null,
];

json_out([
    'nama'      => $u['nama'],
    'role'      => $u['role'],
    'nama_unit' => $u['nama_unit'],
]);
