<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Laporan Etika/Pungli - Kajur SIPEKA</title>
  <?php include __DIR__ . '/../../../includes/styles.php'; ?>
</head>

<body data-unit="kajur">
  <!-- Mobile Topbar -->
  <div class="mobile-topbar topbar--unit">
    <div class="topbar-brand">
      <div class="logo-badge logo-badge--sm"><img src="../../../assets/images/logo/Jti.webp" alt="Logo SIPEKA"
          class="logo-JTI"></div>
      <span class="topbar-title">SIPEKA Kajur</span>
    </div>
    <button class="hamburger-btn" aria-label="Buka Menu">
      <svg class="icon" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
        stroke-linecap="round" stroke-linejoin="round">
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
          <div class="logo-badge"><img src="../../../assets/images/logo/Jti.webp" alt="Logo SIPEKA" class="logo-JTI">
          </div>
          <div class="logo-text">
            <h1>SIPEKA Kajur</h1>
            <p>Ketua Jurusan TI Polinema</p>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li class="active">
            <a href="dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1 2-2h2" />
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
              </svg>
              <span>Daftar Laporan Masuk</span>
            </a>
          </li>
          <li>
            <a href="profil.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
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
          <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
            fill="none" stroke-linecap="round" stroke-linejoin="round">
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
          <span class="breadcrumb-text">Portal Kajur &rsaquo; <strong>Verifikasi Pengaduan Masuk</strong></span>
        </div>
        <div class="user-profile user-profile--clickable" onclick="window.location.href='profil.php'">
          <div class="avatar avatar--unit"> </div>
          <div class="user-info">
            <div class="user-name"> </div>
            <div class="user-role"> </div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-title">
          <h2>Verifikasi Bukti Pengaduan Kode Etik / Pungli</h2>
          <p>Tinjau kronologi dan berkas pengaduan secara rahasia dan objektif sebelum memulai pemanggilan.</p>
        </div>

        <div class="detail-grid">
          <!-- Left: Report Details -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Informasi Kasus</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor</td>
                <td><strong id="verifTiket">KP-0016</strong></td>
              </tr>
              <tr>
                <td class="label">Data Pelapor</td>
                <td id="verifPelapor">Rizka Nurhaliza (NIM: 254107020045)</td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="verifKategori">Pungutan Liar (Pungli)</td>
              </tr>
              <tr>
                <td class="label">Judul Kasus</td>
                <td id="verifJudul">Pungutan Biaya Modul Praktikum Tak Resmi</td>
              </tr>
              <tr>
                <td class="label">Lokasi Kejadian</td>
                <td id="verifLokasi">Ruang Kelas 4.05</td>
              </tr>
              <tr>
                <td class="label">Kronologi Lengkap</td>
                <td id="verifKronologi">Penarikan biaya cetak modul tanpa kwitansi resmi dari pihak jurusan di luar
                  ketentuan UKT.</td>
              </tr>
            </table>

            <div class="bukti-section">
              <h3 class="card-title">Bukti Pelapor</h3>

              <div id="verifBukti" class="bukti-container">
                <p>Memuat bukti pelapor...</p>
              </div>
            </div>

          </div>

        <!-- Right: Action Card -->
        <div class="form-card form-card--full">

          <h3 class="card-title">Keputusan Verifikasi Kajur</h3>

          <p class="card-note">
            Tentukan keabsahan pengaduan. Jika valid, pimpinan akan menerbitkan surat panggilan klarifikasi resmi.
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
      </div>

  </div>
  </main>
  </div>

  <?php include __DIR__ . '/../../../includes/scripts.php'; ?>
</body>


</html>