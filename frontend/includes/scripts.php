<?php
/**
 * includes/scripts.php
 * SATU-SATUNYA tempat yang memuat file JavaScript SIPEKA.
 * Dipakai di setiap halaman dengan satu baris sebelum </body>:
 *   <?php include __DIR__ . '/../../includes/scripts.php'; ?>        (content/auth, content/mahasiswa)
 *   <?php include __DIR__ . '/../../../includes/scripts.php'; ?>     (content/unit/{unit})
 *
 * Skrip inti dimuat di semua halaman; skrip halaman hanya dimuat pada halaman
 * yang membutuhkannya (ditentukan dari nama berkas yang sedang dieksekusi).
 * Urutan di bawah penting: skrip inti -> skrip halaman -> main.js (router).
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

// Skrip inti (selalu dimuat)
$files = [
    'core/store.js', // data lokal (localStorage)
    'core/ui.js', // toast, modal, utilitas tampilan
    'core/api.js', // pemanggilan API backend (fetch)
    'shared/helpers.js', // fungsi bantu lintas halaman
    'core/notifikasi.js', // bel notifikasi + API notifikasi/ulasan (butuh api.js)
];

// Skrip khusus halaman, dipilih dari nama berkas yang sedang dieksekusi
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$halaman    = basename($scriptName);
$diMahasiswa = strpos($scriptName, '/mahasiswa/') !== false;

$skripHalaman = match ($halaman) {
    'landing-page.php'   => ['pages/landing-ulasan.js'],
    'login.php'          => ['pages/auth/login.js'],
    'register.php'       => ['pages/auth/registrasi.js'],
    'dashboard.php'      => $diMahasiswa
        ? ['pages/mahasiswa/mahasiswa-dashboard.js']
        : ['pages/unit/unit-dashboard.js'],
    'buat-laporan.php'   => ['pages/mahasiswa/buat-laporan.js'],
    'riwayat.php'        => ['pages/riwayat.js'],
    'profil.php'         => ['pages/profile.js'],
    'verifikasi.php'     => ['pages/unit/tanggapan.js', 'pages/unit/verifikasi.js'],
    'penanganan.php'     => ['pages/unit/tanggapan.js', 'pages/unit/penanganan.js'],
    'detail-selesai.php' => ['pages/unit/detail-selesai.js'],
    'ulasan.php'         => ['pages/landing-ulasan.js', 'pages/ulasan.js'],
    default              => [],
};

$files = array_merge($files, $skripHalaman, ['main.js']); // router HARUS terakhir
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
