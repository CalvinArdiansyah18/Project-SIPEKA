<?php
/**
 * includes/styles.php
 * SATU-SATUNYA tempat yang memuat stylesheet SIPEKA.
 * Dipakai di setiap halaman dengan satu baris di dalam <head>:
 *   <?php include __DIR__ . '/../../includes/styles.php'; ?>        (content/auth, content/mahasiswa)
 *   <?php include __DIR__ . '/../../../includes/styles.php'; ?>     (content/unit/{unit})
 *
 * Urutan pemuatan penting: base -> layout -> components -> modal -> utilities
 * -> CSS khusus halaman (auth/profile/riwayat).
 */

// Hitung alamat (URL) folder CSS dari posisi file ini terhadap document root,
// sehingga tidak bergantung pada kedalaman folder halaman atau nama virtual host.
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
$cssDir  = str_replace('\\', '/', realpath(__DIR__ . '/../assets/css'));
$cssUrl  = substr($cssDir, strlen($docRoot));               // contoh: /frontend/assets/css

$files = [
    'base.css',       // variabel, reset, body
    'layout.css',     // sidebar, header, konten, responsif
    'components.css', // form, tombol, kartu, tabel, badge
    'landing.css',    // gaya khusus halaman landing
    'themes.css',     // warna brand per unit (body[data-unit])
    'modal.css',      // toast, modal, prompt
    'utilities.css',  // kelas bantu (warna teks, jarak)
    'auth.css',       // halaman login & registrasi
    'profile.css',    // halaman profil (mahasiswa & unit)
    'riwayat.css',    // halaman riwayat (linimasa)
];
?>
<?php foreach ($files as $f):
    $path = __DIR__ . '/../assets/css/' . $f;
    $ver  = is_file($path) ? filemtime($path) : time(); // ?v= agar browser selalu memuat versi terbaru
?>
<link rel="stylesheet" href="<?= $cssUrl . '/' . $f . '?v=' . $ver ?>">
<?php endforeach; ?>