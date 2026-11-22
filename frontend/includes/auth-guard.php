<?php
/**
 * includes/auth-guard.php
 * Penjaga sesi sisi server. Di-include paling atas setiap halaman terlindungi,
 * SEBELUM ada output apa pun, supaya redirect (header Location) tetap sah.
 *
 * Variabel yang dibaca:
 *   $authRole   string|null  peran yang diwajibkan ('mahasiswa' / 'unit'); null = semua peran
 *   $authPublic bool         true untuk halaman publik (login/register/landing) -> tidak dijaga
 *
 * Sesi berasal dari backend (backend/config/bootstrap.php) dengan nama cookie
 * bawaan PHP, sehingga cukup session_start() tanpa parameter tambahan.
 */

if (!function_exists('sipeka_url_login')) {
    // URL absolut (berbasis document root) menuju halaman login, agar tidak
    // bergantung pada kedalaman folder halaman pemanggil.
    function sipeka_url_login(): string
    {
        $docRoot = rtrim(str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
        $login   = str_replace('\\', '/', (string)realpath(__DIR__ . '/../content/auth/login.php'));

        if ($docRoot !== '' && $login !== '' && strpos($login, $docRoot) === 0) {
            return substr($login, strlen($docRoot));
        }
        return '/frontend/content/auth/login.php';
    }
}

$authRole   = $authRole   ?? null;
$authPublic = $authPublic ?? false;

if ($authPublic) {
    return;
}

if (session_status() === PHP_SESSION_NONE) {
    // Tanpa cookie sesi tidak mungkin ada login: langsung alihkan tanpa membuat
    // sesi kosong baru di server.
    if (!isset($_COOKIE[session_name()])) {
        header('Location: ' . sipeka_url_login());
        exit;
    }
    session_start();
}

$sipekaUser = $_SESSION['user'] ?? null;

if ($sipekaUser === null) {
    header('Location: ' . sipeka_url_login());
    exit;
}

if ($authRole !== null && ($sipekaUser['role'] ?? '') !== $authRole) {
    header('Location: ' . sipeka_url_login());
    exit;
}
