<?php

/**
 * SIPEKA - Pemilihan Kategori Pengaduan Mahasiswa
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pilih Kategori Pengaduan - SIPEKA</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
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
        <line x1="4" x2="20" y1="12" y2="12" />
        <line x1="4" x2="20" y1="6" y2="6" />
        <line x1="4" x2="20" y1="18" y2="18" />
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
          <li>
            <a href="dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <rect width="7" height="9" x="3" y="3" rx="1" />
                <rect width="7" height="5" x="14" y="3" rx="1" />
                <rect width="7" height="9" x="14" y="12" rx="1" />
                <rect width="7" height="5" x="3" y="16" rx="1" />
              </svg>
              <span>Dashboard</span>
            </a>
          </li>
          <li class="active">
            <a href="kategori.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9" />
                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
              </svg>
              <span>Buat Pengaduan</span>
            </a>
          </li>
          <li>
            <a href="riwayat.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
              </svg>
              <span>Riwayat Pengaduan</span>
            </a>
          </li>
          <li>
            <a href="profil.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
              </svg>
              <span>Profil Saya</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="sidebar-footer">
        <a href="../auth/login.php" onclick="if(window.sipekaAPI) window.sipekaAPI.logout();">
          <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" x2="9" y1="12" y2="12" />
          </svg>
          <span>Keluar</span>
        </a>
      </div>
    </aside>

    <!-- Main Content -->
    <main class="app-main">
      <header class="app-header">
        <div class="header-breadcrumb">
          <a href="dashboard.php" style="color: var(--accent-blue); text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px;">
            <svg class="icon" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path d="m15 18-6-6 6-6" />
            </svg>
            Dashboard
          </a>
          <span style="color: var(--text-muted); font-size: 0.85rem;"> / Buat Pengaduan</span>
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
        <div class="page-title" style="margin-bottom: 2rem;">
          <h2>Buat Pengaduan</h2>
          <p>Pilih kategori pengaduan yang sesuai agar laporan diteruskan langsung ke unit penanggung jawab.</p>
        </div>

        <!-- 5 Categories (Direct Routing according to Proposal & Figma M03) -->
        <div class="category-grid">
          <a href="buat-laporan.php?kategori=fasilitas" class="category-card">
            <div class="category-info">
              <h4>Fasilitas Kampus</h4>
              <p>Kerusakan sarana umum: ruang kelas, proyektor, AC, laboratorium, toilet, lift, dll. (Diteruskan ke: Sekjur)</p>
            </div>
            <div class="category-arrow">→</div>
          </a>

          <a href="buat-laporan.php?kategori=akademik" class="category-card">
            <div class="category-info">
              <h4>Pelayanan Akademik</h4>
              <p>Kendala KRS, pengurusan surat aktif kuliah, nilai belum keluar, jadwal bentrok. (Diteruskan ke: Admin Prodi)</p>
            </div>
            <div class="category-arrow">→</div>
          </a>

          <a href="buat-laporan.php?kategori=dosen" class="category-card">
            <div class="category-info">
              <h4>Perilaku Dosen atau Staf</h4>
              <p>Ketidakhadiran perkuliahan tanpa kabar, dugaan pelanggaran kode etik, transparansi penilaian. (Diteruskan ke: Kajur)</p>
            </div>
            <div class="category-arrow">→</div>
          </a>

          <a href="buat-laporan.php?kategori=pungli" class="category-card">
            <div class="category-info">
              <h4>Pungutan Liar (Pungli)</h4>
              <p>Biaya tidak resmi di luar UKT/ketentuan kampus, pembebanan biaya modul tanpa izin. (Diteruskan ke: Kajur)</p>
            </div>
            <div class="category-arrow">→</div>
          </a>

          <a href="buat-laporan.php?kategori=mahasiswa" class="category-card">
            <div class="category-info">
              <h4>Pelanggaran Mahasiswa</h4>
              <p>Kecurangan akademik, penyalahgunaan fasilitas, atau pelanggaran tata tertib sesama mahasiswa. (Diteruskan ke: KPS)</p>
            </div>
            <div class="category-arrow">→</div>
          </a>
        </div>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../includes/scripts.php'; ?>
</body>

</html>