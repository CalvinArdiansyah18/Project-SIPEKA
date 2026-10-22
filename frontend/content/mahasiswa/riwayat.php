<?php
/**
 * SIPEKA - Riwayat Pengaduan & Tracking Status Mahasiswa
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pengaduan & Tracking - SIPEKA</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    .timeline {
      position: relative;
      padding-left: 2rem;
      margin-top: 1.5rem;
    }
    .timeline::before {
      content: '';
      position: absolute;
      left: 7px;
      top: 0;
      bottom: 0;
      width: 2px;
      background-color: var(--border-color);
    }
    .timeline-item {
      position: relative;
      margin-bottom: 1.5rem;
    }
    .timeline-dot {
      position: absolute;
      left: -2rem;
      top: 4px;
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background-color: var(--accent-blue);
      border: 3px solid #ffffff;
      box-shadow: 0 0 0 2px var(--accent-blue);
    }
    .timeline-dot.done {
      background-color: #10b981;
      box-shadow: 0 0 0 2px #10b981;
    }
    .timeline-content h5 {
      font-size: 0.95rem;
      font-weight: 700;
      color: var(--primary-navy);
      margin-bottom: 2px;
    }
    .timeline-content .time {
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-bottom: 4px;
    }
    .timeline-content p {
      font-size: 0.85rem;
      color: var(--text-main);
    }
  </style>
</head>
<body>

  <!-- Mobile Topbar -->
  <div class="mobile-topbar">
    <div style="display: flex; align-items: center; gap: 10px;">
      <div class="logo-badge" style="width: 30px; height: 30px; font-size: 1rem;">i</div>
      <span style="font-weight: 700; font-size: 1.1rem; letter-spacing: -0.5px;">SIPEKA</span>
    </div>
    <button class="hamburger-btn" aria-label="Buka Menu">
      <svg class="icon" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
      </svg>
    </button>
  </div>

  <div class="sidebar-backdrop"></div>

  <div class="app-layout">
    <aside class="app-sidebar">
      <div>
        <div class="sidebar-logo">
          <div class="logo-badge">i</div>
          <div class="logo-text">
            <h1 style="color: #fff; font-size: 1.25rem;">SIPEKA</h1>
            <p style="font-size: 0.7rem; color: #cbd5e1;">Sistem Pengaduan Kampus</p>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li>
            <a href="dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/>
                <rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
              </svg>
              <span>Dashboard</span>
            </a>
          </li>
          <li>
            <a href="kategori.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
              </svg>
              <span>Buat Pengaduan</span>
            </a>
          </li>
          <li class="active">
            <a href="riwayat.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
              </svg>
              <span>Riwayat Pengaduan</span>
            </a>
          </li>
          <li>
            <a href="profil.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              <span>Profil Saya</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="sidebar-footer">
        <a href="../auth/login.php" onclick="if(window.sipekaAPI) window.sipekaAPI.logout();">
          <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
          </svg>
          <span>Keluar</span>
        </a>
      </div>
    </aside>

    <main class="app-main">
      <header class="app-header">
        <div class="header-breadcrumb">
          <a href="dashboard.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">Dashboard</a>
          <span style="color: var(--text-muted); font-size: 0.85rem;"> / Riwayat & Tracking Status</span>
        </div>
        <div class="user-profile" style="cursor: pointer;" onclick="window.location.href='profil.php'">
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
          <a href="kategori.php" class="btn-action">
            <svg class="icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Buat Laporan Baru</span>
          </a>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="toolbar-row">
          <div class="search-box">
            <svg class="icon search-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input type="text" id="searchReport" class="search-input" placeholder="Cari berdasarkan nomor tiket, judul, atau kategori...">
          </div>

          <div style="display: flex; gap: 0.75rem;">
            <select id="statusFilter" class="filter-select">
              <option value="all">Semua Status</option>
              <option value="Menunggu Validasi">Menunggu Validasi</option>
              <option value="Dalam Penanganan">Dalam Penanganan</option>
              <option value="Selesai">Selesai</option>
              <option value="Tidak Valid">Tidak Valid</option>
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
                <!-- Dynamically rendered by app.js -->
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
          <h3 id="modalTiket">Tiket #KP-0000</h3>
          <p id="modalJudul" style="font-size: 0.85rem; color: var(--text-muted); font-weight: 500; margin-top: 4px;"></p>
        </div>
        <button class="modal-close-btn" onclick="closeModal('trackingModal')">✕</button>
      </div>

      <div style="margin-bottom: 1.5rem;">
        <table class="summary-table" style="margin-bottom: 1rem;">
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
            <td class="label">Status Saat Ini</td>
            <td id="modalStatusBadge">-</td>
          </tr>
        </table>
      </div>

      <h4 style="font-size: 1rem; color: var(--primary-navy); border-top: 1px solid var(--border-color); padding-top: 1.25rem;">Linimasa Penanganan (Status History)</h4>
      <div class="timeline" id="modalTimeline">
        <!-- Rendered dynamically from store.js -->
      </div>

      <div style="text-align: right; margin-top: 1.5rem;">
        <button class="btn-detail" onclick="closeModal('trackingModal')" style="padding: 0.6rem 1.5rem;">Tutup</button>
      </div>
    </div>
  </div>

  <script src="../../assets/js/store.js"></script>
  <script src="../../assets/js/ui.js"></script>
  <script src="../../assets/js/app.js"></script>
</body>
</html>
