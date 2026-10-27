<?php
/**
 * includes/scripts.php
 * SATU-SATUNYA tempat yang memuat file JavaScript SIPEKA.
 * Dipakai di setiap halaman dengan satu baris sebelum </body>:
 *   <?php include __DIR__ . '/../../includes/scripts.php'; ?>        (content/auth, content/mahasiswa)
 *   <?php include __DIR__ . '/../../../includes/scripts.php'; ?>     (content/unit/{unit})
 *
 * Menambah/mengganti file JS cukup di sini. Urutan di bawah penting.
 */

// Ubah ke true untuk menampilkan log "[SIPEKA] ..." di Console saat debugging
$debug = false;

// Hitung alamat (URL) folder JS dan API dari posisi file ini terhadap document root,
// sehingga tidak bergantung pada kedalaman folder halaman atau nama virtual host.
$docRoot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])), '/');
$jsDir   = str_replace('\\', '/', realpath(__DIR__ . '/../assets/js'));
$jsUrl   = substr($jsDir, strlen($docRoot));                 // contoh: /frontend/assets/js

$apiDir  = realpath(__DIR__ . '/../../backend/api/auth');
$apiUrl  = $apiDir ? substr(str_replace('\\', '/', $apiDir), strlen($docRoot)) : '/backend/api/auth';

// Urutan pemuatan
$files = [
    'core/store.js',            // data lokal (localStorage)
    'core/ui.js',               // toast, modal, utilitas tampilan
    'core/api.js',              // pemanggilan API backend (fetch)
    'shared/helpers.js',        // fungsi bantu lintas halaman
    'pages/login.js',
    'pages/registrasi.js',
    'pages/profile.js',
    'pages/mahasiswa-dashboard.js',
    'pages/buat-laporan.js',
    'pages/riwayat.js',
    'pages/unit-dashboard.js',
    'pages/verifikasi.js',
    'pages/penanganan.js',
    'main.js',                  // router, HARUS terakhir
];
?>
<script>
  window.SIPEKA_CONFIG = <?= json_encode(['apiBase' => $apiUrl, 'debug' => $debug]) ?>;
</script>
<?php foreach ($files as $f):
    $path = __DIR__ . '/../assets/js/' . $f;
    $ver  = is_file($path) ? filemtime($path) : time(); // ?v= agar browser selalu memuat versi terbaru
?>
<script src="<?= $jsUrl . '/' . $f . '?v=' . $ver ?>"></script>
<?php endforeach; ?>