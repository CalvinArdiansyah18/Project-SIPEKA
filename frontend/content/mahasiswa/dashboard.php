<?php
/**
 * SIPEKA - Dashboard Mahasiswa
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa - SIPEKA</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>

  <!-- Mobile Topbar for Smartphone Screens -->
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
    <!-- Sidebar -->
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
          <li class="active">
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
          <li>
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

    <!-- Main Content -->
    <main class="app-main">
      <header class="app-header">
        <div class="header-breadcrumb">
          <span style="color: var(--text-muted); font-size: 0.85rem;">Portal Mahasiswa JTI Polinema</span>
        </div>
        <div class="user-profile" style="cursor: pointer;" onclick="window.location.href='profil.php'">
          <div class="avatar">GM</div>
          <div class="user-info">
            <div class="user-name">Galih Maula Mumtaza</div>
            <div class="user-role">NIM: 254107020095 (TI-2F)</div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-header-row">
          <div class="page-title">
            <h2>Dashboard Mahasiswa</h2>
            <p>Pantau seluruh status laporan pengaduan Anda di Jurusan Teknologi Informasi</p>

        <!-- 4 Reactive Stat Cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-indicator blue"></div>
            <div>
              <div class="stat-number" id="statTotal">0</div>
              <div class="stat-label">Total Pengaduan</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-indicator orange"></div>
            <div>
              <div class="stat-number" id="statProses">0</div>
              <div class="stat-label">Sedang Diproses</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-indicator green"></div>
            <div>
              <div class="stat-number" id="statSelesai">0</div>
              <div class="stat-label">Selesai Ditangani</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-indicator red"></div>
            <div>
              <div class="stat-number" id="statDitolak">0</div>
              <div class="stat-label">Ditolak / Tidak Valid</div>
            </div>
          </div>
        </div>

        <!-- Recent Table Section -->
        <div class="data-card">
          <div class="data-card-header">
            <h3>Pengaduan Terbaru</h3>
            <a href="riwayat.php" style="font-size: 0.85rem; color: var(--accent-blue); text-decoration: none; font-weight: 600;">Lihat Semua & Pelacakan →</a>
          </div>

          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>No. Tiket</th>
                  <th>Kategori</th>
                  <th>Judul Laporan</th>
                  <th>Tanggal Kejadian</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody id="recentReportsBody">
                <!-- Dynamically populated by app.js -->
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <script src="../../assets/js/store.js"></script>
  <script src="../../assets/js/ui.js"></script>
  <script src="../../assets/js/app.js"></script>
</body>
</html>
