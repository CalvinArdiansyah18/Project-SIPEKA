/**
 * SIPEKA - main.js (router halaman)
 * Dijalankan terakhir. Tugasnya hanya:
 *   1. mengecek sesi login,
 *   2. mengalihkan pengguna yang sudah login dari halaman login/registrasi,
 *   3. memilih fungsi init halaman yang sesuai dengan URL (lihat tabel ROUTES).
 *
 * Logika tiap halaman ada di assets/js/pages/*.js
 * Aktifkan log debug: ubah debug menjadi true di includes/scripts.php
 */

// Tabel rute: urutan penting, rute pertama yang cocok yang dijalankan.
const ROUTES = [
    { nama: 'mahasiswa-dashboard', cocok: (p) => p.includes('dashboard.php') && p.includes('mahasiswa'), jalankan: () => initMahasiswaDashboard() },
    { nama: 'riwayat', cocok: (p) => p.includes('riwayat.php'), jalankan: () => initRiwayatPage() },
    { nama: 'buat-laporan', cocok: (p) => p.includes('buat-laporan.php'), jalankan: () => initBuatLaporanPage() },
    { nama: 'unit-dashboard', cocok: (p) => p.includes('dashboard.php') && p.includes('unit'), jalankan: () => initUnitDashboard() },
    { nama: 'login', cocok: (p) => p.includes('login.php'), jalankan: () => initLoginPage() },
    { nama: 'register', cocok: (p) => p.includes('register.php'), jalankan: () => initRegisterPage() },
    { nama: 'profil', cocok: (p) => p.includes('profil.php'), jalankan: () => initProfilePage() },
    { nama: 'verifikasi', cocok: (p) => p.includes('verifikasi.php'), jalankan: () => initVerifikasiPage() },
    { nama: 'penanganan', cocok: (p) => p.includes('penanganan.php'), jalankan: () => initPenangananPage() },
];

function sipekaLog(...args) {
    if (window.SIPEKA_CONFIG && window.SIPEKA_CONFIG.debug) console.log('[SIPEKA]', ...args);
}

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Ambil profil sesi aktif
    let session = null;
    if (window.sipekaAPI) {
        session = await window.sipekaAPI.getSession();
    }
    sipekaLog('sesi:', session);
    renderUserProfile();

    const path = window.location.pathname;

    // 2. Sudah login tapi membuka halaman login/register -> langsung ke dashboard
    if (session && (path.includes('login.php') || path.includes('register.php'))) {
        window.location.replace(
            session.role === 'unit'
                ? getUnitDashboardPath(session.nama_unit)
                : '../mahasiswa/dashboard.php'
        );
        return;
    }

    // 3. Jalankan init halaman yang cocok
    const rute = ROUTES.find((r) => r.cocok(path));
    if (!rute) {
        sipekaLog('tidak ada rute untuk', path);
        return;
    }
    sipekaLog('menjalankan halaman:', rute.nama);
    try {
        await rute.jalankan();
    } catch (err) {
        console.error('[SIPEKA] Gagal menjalankan halaman "' + rute.nama + '":', err);
    }
});
