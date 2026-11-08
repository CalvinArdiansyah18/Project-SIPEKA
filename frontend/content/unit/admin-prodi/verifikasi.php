<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Kendala Akademik - Admin Prodi SIPEKA</title>
  <?php include __DIR__ . '/../../../includes/styles.php'; ?>
</head>

<body data-unit="prodi">
  <!-- Mobile Topbar -->
  <div class="mobile-topbar topbar--unit">
    <div class="topbar-brand">
      <div class="logo-badge logo-badge--sm"><img src="../../../assets/images/logo/Jti.webp" alt="Logo SIPEKA"
          class="logo-JTI"></div>
      <span class="topbar-title">SIPEKA Admin Prodi</span>
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
            <h1>SIPEKA Prodi</h1>
            <p>Admin Program Studi JTI</p>
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
          <span class="breadcrumb-text">Portal Admin Prodi &rsaquo; <strong>Verifikasi Kendala Akademik</strong></span>
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
          <h2>Verifikasi Bukti &amp; Permasalahan Akademik</h2>
          <p>Tinjau data pengaduan akademik mahasiswa sebelum melakukan klarifikasi dengan dosen atau staf prodi.</p>
        </div>

        <div class="detail-grid">
          <!-- Left: Report Details -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Informasi Kendala Akademik</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor</td>
                <td><strong id="verifTiket">KP-0011</strong></td>
              </tr>
              <tr>
                <td class="label">Data Mahasiswa</td>
                <td id="verifPelapor">Calvin Ardiansyah (NIM: 254107020214)</td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="verifKategori">Pelayanan Akademik</td>
              </tr>
              <tr>
                <td class="label">Judul Kendala</td>
                <td id="verifJudul">Koreksi Nilai Praktikum Basis Data</td>
              </tr>
              <tr>
                <td class="label">Lokasi / Ruang</td>
                <td id="verifLokasi">Ruang Dosen JTI</td>
              </tr>
              <tr>
                <td class="label">Kronologi Lengkap</td>
                <td id="verifKronologi">Nilai tugas praktikum 3 belum terinput di portal akademik padahal sudah dinilai
                  oleh asisten lab.</td>
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

          <h3 class="card-title">Keputusan Verifikasi Prodi</h3>

          <p class="card-note">
            Tentukan keabsahan permohonan. Laporan yang valid akan langsung diteruskan ke proses tindak lanjut
            akademik prodi.
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