<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Laporan Sarpras - Sekjur SIPEKA</title>
  <?php include __DIR__ . '/../../../includes/styles.php'; ?>
</head>
<body data-unit="sekjur">

  <!-- Mobile Topbar -->
  <div class="mobile-topbar topbar--unit">
    <div class="topbar-brand">
      <div class="logo-badge logo-badge--sm">SJ</div>
      <span class="topbar-title">SIPEKA Sekjur</span>
    </div>
    <button class="hamburger-btn" aria-label="Buka Menu">
      <svg class="icon" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="4" x2="20" y1="12" y2="12" />
        <line x1="4" x2="20" y1="6" y2="6" />
        <line x1="4" x2="20" y1="18" y2="18" />
      </svg>
    </button>
  </div>

  <div class="sidebar-backdrop"></div>

  <div class="app-layout">
    <aside class="app-sidebar sidebar--unit">
      <div>
        <div class="sidebar-logo">
          <div class="logo-badge">SJ</div>
          <div class="logo-text">
            <h1>SIPEKA Sekjur</h1>
            <p>Sekretariat Jurusan (Sarpras)</p>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li class="active">
            <a href="dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1 2-2h2" />
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
              </svg>
              <span>Daftar Laporan Masuk</span>
            </a>
          </li>
          <li>
            <a href="profil.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              <span>Profil Unit</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="sidebar-footer">
        <a href="../../auth/login.php" data-logout>
          <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" x2="9" y1="12" y2="12" />
          </svg>
          <span>Keluar</span>
        </a>
      </div>
    </aside>

    <main class="app-main">
      <header class="app-header">
        <div class="header-breadcrumb">
          <span class="breadcrumb-text">Portal Sekjur &rsaquo; <strong>Verifikasi Laporan Masuk</strong></span>
        </div>

        <div class="user-profile user-profile--clickable" onclick="window.location.href='profil.php'">
          <div class="avatar avatar--unit">SJ</div>
          <div class="user-info">
            <div class="user-name">Staff Sekretariat Jurusan</div>
            <div class="user-role">Unit Sarana &amp; Prasarana</div>
          </div>
        </div>
      </header>

      <div class="content-container">

        <div class="page-title">
          <h2>Verifikasi Bukti &amp; Keabsahan Laporan Sarpras</h2>
          <p>Tinjau kelengkapan laporan mahasiswa sebelum menugaskan teknisi sarpras untuk perbaikan.</p>
        </div>

        <!-- Grid Verifikasi -->
        <div class="detail-grid">

          <!-- KIRI: Informasi Pengaduan dan Bukti Pelapor -->
          <div class="form-card form-card--full">

            <h3 class="card-title card-title--bordered">Informasi Pengaduan</h3>

            <table class="summary-table">
              <tr>
                <td class="label">Nomor</td>
                <td><strong id="verifTiket">KP-0013</strong></td>
              </tr>
              <tr>
                <td class="label">Data Pelapor</td>
                <td id="verifPelapor">Galih Maula Mumtaza (NIM: 254107020095)</td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="verifKategori">Fasilitas Kampus</td>
              </tr>
              <tr>
                <td class="label">Judul Kerusakan</td>
                <td id="verifJudul">Kerusakan Proyektor &amp; Stop Kontak Lab 3</td>
              </tr>
              <tr>
                <td class="label">Lokasi</td>
                <td id="verifLokasi">Gedung Sipil Lt. 7 - Lab Komputer 3</td>
              </tr>
              <tr>
                <td class="label">Kronologi Lengkap</td>
                <td id="verifKronologi">Proyektor mati mendadak saat sesi praktikum dan stop kontak di baris tengah mengeluarkan percikan kecil.</td>
              </tr>
            </table>

            <!-- Bukti Pelapor -->
            <div class="bukti-section">
              <h3 class="card-title">Bukti Pelapor</h3>

              <div id="verifBukti" class="bukti-container">
                <p>Memuat bukti pelapor...</p>
              </div>
            </div>

          </div>
          <!-- PENUTUP KOLOM KIRI -->


          <!-- KANAN: Keputusan Verifikasi -->
          <div class="form-card form-card--full">

            <h3 class="card-title">Keputusan Verifikasi Sekjur</h3>

            <p class="card-note">
              Tentukan keabsahan laporan. Laporan yang valid akan langsung diteruskan ke teknisi sarpras.
            </p>

            <div class="action-stack">
              <button type="button" id="btnApprovePage" class="btn-primary btn-success">
                ✓ Setujui (Laporan Valid)
              </button>

              <button type="button" id="btnRejectPage" class="btn-primary btn-danger">
                ✕ Tolak (Tidak Valid)
              </button>

              <a href="dashboard.php" class="btn-detail mt-sm">
                Kembali ke Dashboard
              </a>
            </div>

          </div>
          <!-- PENUTUP KOLOM KANAN -->

        </div>
        <!-- PENUTUP DETAIL GRID -->

      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../../includes/scripts.php'; ?>

</body>
</html>