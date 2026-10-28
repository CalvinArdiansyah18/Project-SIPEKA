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

// ---------------------------------------------------------------
// PENJAGA SESI
// Halaman publik   : login.php, register.php  (jika sudah login -> ke dashboard)
// Halaman lainnya  : wajib login, dan harus sesuai peran (mahasiswa / unit)
// ---------------------------------------------------------------
function halamanPublik(path) {
    return path.includes('login.php') || path.includes('register.php');
}

// Contoh hasil: "/frontend/content/"  (null jika bukan halaman di bawah /content/)
function basePathContent(path) {
    const i = path.indexOf('/content/');
    return i === -1 ? null : path.substring(0, i + '/content/'.length);
}

function tujuanDashboard(session, base) {
    return session.role === 'unit'
        ? getUnitDashboardPath(session.nama_unit, base.slice(0, -1))   // /frontend/content/unit/<unit>/dashboard.php
        : base + 'mahasiswa/dashboard.php';
}

// Mengembalikan { session, lanjut }. lanjut=false berarti pengguna sedang dialihkan.
async function periksaSesi() {
    const session = window.sipekaAPI ? await window.sipekaAPI.getSession() : null;
    const path = window.location.pathname;
    const base = basePathContent(path);
    sipekaLog('sesi:', session);

    if (base === null) return { session, lanjut: true };

    if (halamanPublik(path)) {
        if (session) {
            window.location.replace(tujuanDashboard(session, base));
            return { session, lanjut: false };
        }
        return { session, lanjut: true };
    }

    // ---- halaman terproteksi ----
    if (!session) {
        window.location.replace(base + 'auth/login.php');
        return { session, lanjut: false };
    }
    const tujuan = tujuanDashboard(session, base);
    const diAreaMahasiswa = path.includes('/content/mahasiswa/');
    const diAreaUnit = path.includes('/content/unit/');
    if ((diAreaMahasiswa && session.role !== 'mahasiswa') || (diAreaUnit && session.role !== 'unit')) {
        window.location.replace(tujuan);          // peran tidak sesuai area halaman
        return { session, lanjut: false };
    }
    if (diAreaUnit) {
        const folderUnit = tujuan.split('/unit/')[1].split('/')[0];
        if (!path.includes('/unit/' + folderUnit + '/')) {
            window.location.replace(tujuan);      // unit membuka area unit lain
            return { session, lanjut: false };
        }
    }
    return { session, lanjut: true };
}

// ---------------------------------------------------------------
// LOGOUT: tunggu server menghapus sesi SEBELUM pindah halaman.
// Pasang atribut data-logout pada link "Keluar" (tanpa onclick).
// ---------------------------------------------------------------
async function sipekaLogout(urlLogin) {
    try {
        if (window.sipekaAPI) await window.sipekaAPI.logout();
    } finally {
        window.location.replace(urlLogin);
    }
}

document.addEventListener('click', (e) => {
    const link = e.target.closest ? e.target.closest('[data-logout]') : null;
    if (!link) return;
    e.preventDefault();
    sipekaLogout(link.href);
});

// Halaman dipulihkan dari cache browser (tombol Back): cek ulang sesi & pulihkan tombol
window.addEventListener('pageshow', async (e) => {
    if (!e.persisted) return;
    document.querySelectorAll('button[type="submit"]').forEach((b) => { b.disabled = false; });
    await periksaSesi();
});

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Cek sesi (dan alihkan jika perlu)
    const cek = await periksaSesi();
    if (!cek.lanjut) return;
    renderUserProfile();

    // 2. Jalankan init halaman yang cocok
    const path = window.location.pathname;
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