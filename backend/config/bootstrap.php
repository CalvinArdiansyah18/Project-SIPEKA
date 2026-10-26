<?php
// File persiapan bersama. Di-require di baris pertama setiap endpoint API.

const APP_DEBUG = true;   // ubah ke false saat demo/produksi supaya pesan error internal tidak bocor

ini_set('display_errors', '0');      // jangan tampilkan error PHP sebagai HTML
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Semua error/exception dijawab sebagai JSON (bukan HTML), supaya frontend bisa membacanya
set_exception_handler(function (Throwable $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'message' => APP_DEBUG ? $e->getMessage() : 'Terjadi kesalahan pada server',
    ]);
});

// Cookie session: tidak bisa dibaca JavaScript, hanya dikirim dari situs yang sama
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

require __DIR__ . '/koneksi.php';   // menyediakan $pdo

// ---------- fungsi bantu ----------

function json_out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

// Membaca body JSON dari frontend (fetch ... body: JSON.stringify(...))
function json_in(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// Wajib login. Isi $role ('mahasiswa' / 'unit') untuk membatasi peran.
function butuh_login(?string $role = null): array {
    if (empty($_SESSION['user'])) {
        json_out(['message' => 'Belum login'], 401);
    }
    if ($role !== null && $_SESSION['user']['role'] !== $role) {
        json_out(['message' => 'Tidak diizinkan'], 403);
    }
    return $_SESSION['user'];
}