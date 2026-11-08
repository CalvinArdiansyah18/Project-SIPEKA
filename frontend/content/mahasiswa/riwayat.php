<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pengaduan & Tracking - SIPEKA</title>
  <?php include __DIR__ . '/../../includes/styles.php'; ?>
</head>

<body>
  <!-- Mobile Topbar -->
  <div class="mobile-topbar">
    <div class="topbar-brand">
      <div class="logo-badge logo-badge--sm"><img src="../../assets/images/logo/Jti.webp" alt="Logo SIPEKA"
          class="logo-JTI"></div>
      <span class="topbar-title">SIPEKA</span>
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
    <aside class="app-sidebar">
      <div>
        <div class="sidebar-logo">
          <div class="logo-badge"><img src="../../assets/images/logo/Jti.webp" alt="Logo SIPEKA" class="logo-JTI"></div>
          <div class="logo-text">
            <h1>SIPEKA</h1>
            <p>Sistem Pengaduan Kampus</p>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li>
            <a href="dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1" />
                <rect width="7" height="5" x="14" y="3" rx="1" />
                <rect width="7" height="9" x="14" y="12" rx="1" />
                <rect width="7" height="5" x="3" y="16" rx="1" />
              </svg>
              <span>Dashboard</span>
            </a>
          </li>
          <li>
            <a href="kategori.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9" />
                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
              </svg>
              <span>Buat Pengaduan</span>
            </a>
          </li>
          <li class="active">
            <a href="riwayat.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
              <span>Riwayat Pengaduan</span>
            </a>
          </li>
          <li>
            <a href="profil.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              <span>Profil Saya</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="sidebar-footer">
        <a href="../auth/login.php" data-logout>
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
          <span class="breadcrumb-text">Portal Mahasiswa JTI Polinema &rsaquo; <strong>Riwayat Pengaduan</strong></span>
        </div>
        <div class="user-profile user-profile--clickable" onclick="window.location.href='profil.php'">
          <div class="avatar">GM</div>
          <div class="user-info">
            <div class="user-name">Galih Maula Mumtaza</div>
            <div class="user-role">NIM: 254107020095</div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-header-row">
          <div class="page-title">
            <h2>Riwayat Pengaduan & Tracking Status</h2>
            <p>Daftar lengkap laporan pengaduan beserta linimasa penanganan real-time</p>
          </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="toolbar-row">
          <div class="search-box">
            <svg class="icon search-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor"
              stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="searchReport" class="search-input"
              placeholder="Cari berdasarkan nomor tiket, judul, atau kategori...">
          </div>

          <div class="filter-group">
            <select id="statusFilter" class="filter-select">
              <option value="all">Semua Status</option>
              <option value="menunggu">Menunggu Validasi</option>
              <option value="diproses">Diproses</option>
              <option value="dalam_penanganan">Dalam Penanganan</option>
              <option value="selesai">Selesai</option>
              <option value="ditolak">Tidak Valid</option>
            </select>
          </div>
        </div>

        <!-- Complaints Table -->
        <div class="data-card">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>No. Tiket</th>
                  <th>Kategori</th>
                  <th>Unit Penanggung Jawab</th>
                  <th>Judul Laporan</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="riwayatTableBody">
                <!-- Diisi dari database oleh pages/riwayat.js -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>

  <!-- Interactive Tracking Modal (Popup Linimasa) -->
  <div class="modal" id="trackingModal">
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
      <div class="modal-header">
        <div>
          <h3 id="modalTiket"> </h3>
          <p id="modalJudul" class="modal-subtitle"></p>
        </div>
        <button class="modal-close-btn" onclick="closeModal('trackingModal')">✕</button>
      </div>

      <div class="mb-lg">
        <table class="summary-table summary-table--compact">
          <tr>
            <td class="label">Kategori</td>
            <td id="modalKategori">-</td>
          </tr>
          <tr>
            <td class="label">Unit Tujuan</td>
            <td id="modalUnit">-</td>
          </tr>
          <tr>
            <td class="label">Lokasi</td>
            <td id="modalLokasi">-</td>
          </tr>
          <tr>
            <td class="label">Kronologi</td>
            <td id="modalKronologi">-</td>
          </tr>
          <tr>
            <td class="label">Tanggal</td>
            <td id="modalTanggal">-</td>
          </tr>
        </table>
      </div>

      <h4 class="modal-section-title">Linimasa Penanganan (Status History)</h4>
      <div class="timeline" id="modalTimeline">
        <!-- Diisi dari database oleh pages/riwayat.js -->
      </div>

      <h4 class="modal-section-title modal-section-title--spaced">Hasil Penanganan</h4>
      <div id="modalHasil">
        <!-- Diisi dari database oleh pages/riwayat.js -->
      </div>
      
      <h4 class="modal-section-title modal-section-title--spaced">Tanggapan &amp; Penanganan Unit</h4>
      <div id="modalCatatan">
        <!-- Diisi dari database oleh pages/riwayat.js -->
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/../../includes/scripts.php'; ?>
</body>

</html>