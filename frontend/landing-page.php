<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>SIPEKA - Sistem Pengaduan Mahasiswa JTI Polinema</title>
    <?php include __DIR__ . '/includes/styles.php'; ?>
</head>

<body data-page="index" class="landing">
    <header class="landing-nav">
        <a class="landing-brand" href="index.php" aria-label="SIPEKA - beranda">
            <span class="logo-badge logo-badge--sm"><img src="assets/images/logo/JTI.webp" alt="Logo SIPEKA"
                    class="logo-JTI"></span>
            <span>SIPEKA</span>
        </a>
        <nav class="landing-links" aria-label="Navigasi halaman depan">
            <a href="#cara-kerja">Cara Kerja</a>
            <a href="#unit">Unit Penanganan</a>
            <a href="#ulasan">Ulasan</a>
        </nav>
        <a class="landing-btn landing-btn--small" href="content/auth/login.php" data-auth="login">Masuk</a>
    </header>

    <main>
        <!-- Hero -->
        <section class="landing-hero">
            <div class="landing-hero-inner">
                <p class="landing-eyebrow">Sistem Pengaduan Mahasiswa &middot; JTI Polinema</p>
                <h1>Selamat datang</h1>

                <!-- Tombol login tepat setelah kata "Selamat datang" -->
                <div class="landing-cta">
                    <a class="landing-btn" href="content/auth/login.php" data-auth="login">Masuk</a>
                    <a class="landing-btn landing-btn--ghost" href="content/auth/register.php"
                        data-auth="register">Daftar Akun</a>
                </div>

                <p class="landing-lead">
                    <strong>SIPEKA</strong> membantu mahasiswa menyampaikan pengaduan seputar fasilitas, layanan akademik,
                    etika, dan tata tertib kampus, lalu memantau penanganannya sampai tuntas.
                </p>
            </div>
        </section>

        <!-- Cara kerja -->
        <section class="landing-section" id="cara-kerja">
            <h2>Cara Kerja</h2>
            <p class="landing-sub">Empat langkah dari laporan sampai selesai.</p>
            <ol class="landing-steps">
                <li><span class="landing-step-no">1</span>
                    <h3>Buat Pengaduan</h3>
                    <p>Isi judul, lokasi, kronologi, dan lampirkan bukti.</p>
                </li>
                <li><span class="landing-step-no">2</span>
                    <h3>Proses Penanganan</h3>
                    <p>Unit terkait menindaklanjuti pengaduan sesuai prosedur.</p>
                </li>
                <li><span class="landing-step-no">3</span>
                    <h3>Notifikasi</h3>
                    <p>Mahasiswa menerima pembaruan status pengaduan melalui email.</p>
                </li>
                <li><span class="landing-step-no">4</span>
                    <h3>Selesai</h3>
                    <p>Pengaduan ditutup setelah ditangani, dan mahasiswa dapat memberikan ulasan.</p>
                </li>
            </ol>
        </section>

        <!-- Unit -->
        <section class="landing-section" id="unit">
            <h2>Unit Penanganan</h2>
            <p class="landing-sub">Laporan diteruskan otomatis ke unit sesuai kategori.</p>
            <div class="landing-cards">
                <article class="landing-card">
                    <h3>Sekretaris Jurusan</h3>
                    <p>Fasilitas kampus, seperti ruang kelas, laboratorium, AC dan lainnya.</p>
                </article>
                <article class="landing-card">
                    <h3>Admin Program Studi</h3>
                    <p>Pelayanan akademik, jadwal kuliah, dan administrasi akademik.</p>
                </article>
                <article class="landing-card">
                    <h3>Ketua Jurusan</h3>
                    <p>Etika Dosen dan Pungutan Liar.</p>
                </article>
                <article class="landing-card">
                    <h3>KPS</h3>
                    <p>Pengaduan terkait Mahasiswa.</p>
                </article>
            </div>
        </section>

        <!-- Ulasan -->
        <section class="landing-section landing-section--alt" id="ulasan">
            <h2>Ulasan</h2>
            <p class="landing-sub" id="reviewSummary"></p>
            <div class="review-grid" id="reviewGrid">
                <p class="review-empty">Memuat ulasan...</p>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <span>&copy; <?= date('Y') ?> SIPEKA - Sistem Pengaduan Mahasiswa JTI Polinema</span>
    </footer>

    <div class="bubble-help">
        <div class="help-panel" id="helpPanel" hidden role="dialog" aria-label="Bantuan singkat">
            <h3>Bantuan</h3>
            <p>Untuk membuat pengaduan, silakan masuk atau daftar akun terlebih dahulu.</p>
            <div class="help-actions">
                <a class="landing-btn landing-btn--small" href="content/auth/login.php" data-auth="login">Masuk</a>
                <a class="landing-btn landing-btn--small landing-btn--ghost" href="content/auth/register.php"
                    data-auth="register">Daftar Akun</a>
            </div>
            <a class="help-panelLink" href="#ulasan">Lihat Ulasan</a>
        </div>
        
        <button type="button" class="help-bubble" id="helpBubble" aria-expanded="false" aria-controls="helpPanel"
            aria-label="Buka Bantuan">
            <svg viewBox="0 0 24 24" width="26" height="26" stroke="currentColor" stroke-width="2" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
            </svg>
        </button>
    </div>

    <?php include __DIR__ . '/includes/scripts.php'; ?>
</body>


</html>