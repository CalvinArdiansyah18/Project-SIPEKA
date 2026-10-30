<?php
// POST /backend/api/auth/register.php
// Registrasi KHUSUS mahasiswa. Role selalu 'mahasiswa' (ditentukan di sini, bukan dari frontend).
// Akun unit dibuat langsung lewat database.
// Body JSON: { "nama", "email", "password", "nim", "program_studi", "nomor_telepon" (opsional) }

require __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$in = json_in();

// Hanya field ini yang diambil. "role" dan "id_unit" dari frontend DIABAIKAN.
$nama          = trim($in['nama'] ?? '');
$email         = strtolower(trim($in['email'] ?? ''));
$password      = $in['password'] ?? '';
$nim           = trim($in['nim'] ?? '');
$program_studi = trim($in['program_studi'] ?? '');
$telepon       = trim($in['nomor_telepon'] ?? '');
$telepon       = $telepon === '' ? null : $telepon;

// ---------- validasi ----------
if ($nama === '' || $email === '' || $password === '' || $nim === '' || $program_studi === '') {
    json_out(['message' => 'Nama, email, password, NIM, dan program studi wajib diisi'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_out(['message' => 'Format email tidak valid'], 400);
}
if (strlen($password) < 8) {
    json_out(['message' => 'Password minimal 8 karakter'], 400);
}
if (!preg_match('/^[0-9]{8,15}$/', $nim)) {
    json_out(['message' => 'NIM harus berupa 8-15 digit angka'], 400);
}
if ($telepon !== null && !preg_match('/^(\+62|0)[0-9]{8,13}$/', $telepon)) {
    json_out(['message' => 'Format nomor telepon tidak valid'], 400);
}

// ---------- simpan: users + mahasiswa dalam satu transaksi ----------
try {
    $pdo->beginTransaction();

    $st = $pdo->prepare(
        "INSERT INTO users (nama, email, password_hash, role)
         VALUES (:nama, :email, :hash, 'mahasiswa')
         RETURNING id_user"
    );
    $st->execute([
        ':nama'  => $nama,
        ':email' => $email,
        ':hash'  => password_hash($password, PASSWORD_BCRYPT),
    ]);
    $id_user = $st->fetchColumn();

    $st = $pdo->prepare(
        'INSERT INTO mahasiswa (id_user, nim, program_studi, nomor_telepon)
         VALUES (:id_user, :nim, :prodi, :telp)'
    );
    $st->execute([
        ':id_user' => $id_user,
        ':nim'     => $nim,
        ':prodi'   => $program_studi,
        ':telp'    => $telepon,
    ]);

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // 23505 = pelanggaran UNIQUE (email atau NIM sudah terdaftar)
    if ($e->getCode() === '23505') {
        if (strpos($e->getMessage(), 'uq_users_email') !== false) {
            json_out(['message' => 'Email sudah terdaftar'], 409);
        }
        if (strpos($e->getMessage(), 'uq_mahasiswa_nim') !== false) {
            json_out(['message' => 'NIM sudah terdaftar'], 409);
        }
        json_out(['message' => 'Data sudah terdaftar'], 409);
    }
    // 23514 = pelanggaran CHECK
    if ($e->getCode() === '23514') {
        json_out(['message' => 'Data tidak memenuhi aturan validasi'], 400);
    }
    throw $e;   // error lain ditangani handler di bootstrap.php
}

json_out(['message' => 'Registrasi berhasil, silakan login'], 201);