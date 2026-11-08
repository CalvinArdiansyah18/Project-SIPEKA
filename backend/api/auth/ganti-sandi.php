<?php
// POST /backend/api/auth/ganti-sandi.php
// Body JSON : { "sandi_lama": "...", "sandi_baru": "...", "konfirmasi": "..." }
// Untuk semua peran (mahasiswa dan unit). Akun ditentukan dari SESSION.
//
// Jawaban: { "message": "..." }  | 400 input salah / sandi lama salah

require __DIR__ . '/../../config/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$user = butuh_login();

$in = json_in();
$lama = (string)($in['sandi_lama'] ?? '');
$baru = (string)($in['sandi_baru'] ?? '');
$konfirmasi = (string)($in['konfirmasi'] ?? '');

if ($lama === '' || $baru === '' || $konfirmasi === '') {
    json_out(['message' => 'Kata sandi lama, baru, dan konfirmasi wajib diisi'], 400);
}
if (strlen($baru) < 8) { // sama dengan aturan registrasi
    json_out(['message' => 'Kata sandi baru minimal 8 karakter'], 400);
}
if (strlen($baru) > 72) { // bcrypt hanya membaca 72 byte pertama
    json_out(['message' => 'Kata sandi baru maksimal 72 karakter'], 400);
}
if ($baru !== $konfirmasi) {
    json_out(['message' => 'Konfirmasi kata sandi baru tidak cocok'], 400);
}
if ($baru === $lama) {
    json_out(['message' => 'Kata sandi baru harus berbeda dari kata sandi lama'], 400);
}

$st = $pdo->prepare('SELECT password_hash FROM users WHERE id_user = :id');
$st->execute([':id' => $user['id_user']]);
$hash = $st->fetchColumn();

if (!$hash || !password_verify($lama, $hash)) {
    json_out(['message' => 'Kata sandi lama salah'], 400);
}

$st = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id_user = :id');
$st->execute([
    ':hash' => password_hash($baru, PASSWORD_BCRYPT),
    ':id' => $user['id_user'],
]);

session_regenerate_id(true);   // ID session baru setelah kredensial berubah

json_out(['message' => 'Kata sandi berhasil diperbarui.']);