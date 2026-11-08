<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Penyelesaian Tata Tertib - KPS SIPEKA</title>
  <?php include __DIR__ . '/../../../includes/styles.php'; ?>
</head>

<body data-unit="kps">
  <!-- Mobile Topbar -->
  <div class="mobile-topbar topbar--unit">
    <div class="topbar-brand">
      <div class="logo-badge logo-badge--sm"><img src="../../../assets/images/logo/Jti.webp" alt="Logo SIPEKA"
          class="logo-JTI"></div>
      <span class="topbar-title">SIPEKA KPS</span>
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
            <h1>SIPEKA KPS</h1>
            <p>Ketua Program Studi JTI</p>
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
          <span class="breadcrumb-text">Portal KPS &rsaquo; <strong>Tindakan Mediasi &amp; Tata Tertib</strong></span>
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
          <h2>Catat Progres Mediasi &amp; Penyelesaian Kasus</h2>
          <p>Dokumentasikan hasil mediasi, sanksi peringatan, atau surat pernyataan komitmen tata tertib mahasiswa.</p>
        </div>

        <div class="detail-grid detail-grid--even">
          <!-- Left: Report Summary -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Ringkasan Pelanggaran</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor Tiket</td>
                <td><strong id="penangTiket">KP-0015</strong></td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="penangKategori">Pelanggaran Mahasiswa</td>
              </tr>
              <tr>
                <td class="label">Judul Kasus</td>
                <td id="penangJudul">Indikasi Vandalisme di Fasilitas Lab Komputer</td>
              </tr>
              <tr>
                <td class="label">Lokasi Kejadian</td>
                <td id="penangLokasi">Gedung Sipil Lt. 6 - Lab Jaringan</td>
              </tr>
            </table>
          </div>

          <!-- Right: Resolution Form -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Form Berita Acara Penyelesaian</h3>
            <form id="pageResolutionForm">
              <div class="form-group">
                <label for="pageResTindakan">Keterangan Tindakan Pembinaan / Mediasi <span
                    class="required-mark">*</span></label>
                <textarea id="pageResTindakan" class="form-input"
                  placeholder="Tuliskan hasil mediasi antar mahasiswa, surat peringatan, komitmen ganti rugi, dll..."
                  required></textarea>
              </div>

              <div class="form-group">
                <label>Surat Berita Acara / Pernyataan Bermaterai</label>
                <input type="file" class="form-input" accept="image/*,.pdf">
              </div>

              <div class="modal-actions mt-lg">
                <a href="dashboard.php" class="btn-detail">Batal</a>
                <button type="submit" class="btn-primary btn-success btn-inline">✓ Simpan &amp; Tandai SELESAI</button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../../includes/scripts.php'; ?>
</body>

</html>