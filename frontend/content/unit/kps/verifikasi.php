<?php

/**
 * SIPEKA - Verifikasi Laporan Tata Tertib (KPS)
 */

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verifikasi Tata Tertib Mahasiswa - KPS SIPEKA</title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
</head>

<body data-unit="kps">

  <!-- Mobile Topbar -->
  <div class="mobile-topbar" style="background-color: #1e293b;">
    <div style="display: flex; align-items: center; gap: 10px;">
      <div class="logo-badge" style="background: #4338ca; width: 30px; height: 30px; font-size: 0.9rem;">KP</div>
      <span style="font-weight: 700; font-size: 1.05rem; letter-spacing: -0.5px;">SIPEKA KPS</span>
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
    <aside class="app-sidebar" style="background-color: #1e293b;">
      <div>
        <div class="sidebar-logo">
          <div class="logo-badge" style="background: #4338ca;">KP</div>
          <div class="logo-text">
            <h1 style="color: #fff; font-size: 1.15rem;">SIPEKA KPS</h1>
            <p style="font-size: 0.7rem; color: #94a3b8;">Ketua Program Studi JTI</p>
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
          <li>
            <a href="../../mahasiswa/dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="m16 16 4-4-4-4" />
                <path d="M4 12h16" />
                <path d="M8 8v8" />
              </svg>
              <span>Beralih ke Portal Mhs</span>
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
          <span style="color: var(--text-muted); font-size: 0.85rem;">Portal KPS &rsaquo; <strong>Verifikasi Laporan Tata Tertib</strong></span>
        </div>
        <div class="user-profile" style="cursor: pointer;" onclick="window.location.href='profil.php'">
          <div class="avatar" style="background-color: #4338ca;">KP</div>
          <div class="user-info">
            <div class="user-name">Ketua Program Studi</div>
            <div class="user-role">Unit Pembinaan &amp; Tata Tertib Mahasiswa</div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-title">
          <h2>Verifikasi Bukti Pelanggaran Tata Tertib</h2>
          <p>Tinjau data pengaduan ketertiban mahasiswa sebelum melakukan mediasi atau pembinaan.</p>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; margin-top: 1.5rem;">
          <!-- Left: Report Details -->
          <div class="form-card" style="max-width: 100%;">
            <h3 style="margin-bottom: 1.5rem; color: var(--primary-navy); border-bottom: 1px solid var(--border-color); padding-bottom: 0.5rem;">Informasi Pelanggaran</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor Tiket</td>
                <td><strong id="verifTiket">KP-0015</strong></td>
              </tr>
              <tr>
                <td class="label">Data Pelapor</td>
                <td id="verifPelapor">Naufal Rizky (NIM: 254107020112)</td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="verifKategori">Pelanggaran Mahasiswa</td>
              </tr>
              <tr>
                <td class="label">Judul Pelanggaran</td>
                <td id="verifJudul">Indikasi Vandalisme di Fasilitas Lab Komputer</td>
              </tr>
              <tr>
                <td class="label">Lokasi Kejadian</td>
                <td id="verifLokasi">Gedung Sipil Lt. 6 - Lab Jaringan</td>
              </tr>
              <tr>
                <td class="label">Kronologi Lengkap</td>
                <td id="verifKronologi">Terdapat coretan cat semprot dan kerusakan pada keyboard komputer nomor 12 di Lab Jaringan.</td>
              </tr>
            </table>
          </div>

          <!-- Right: Action Card -->
          <div class="form-card" style="max-width: 100%;">
            <h3 style="margin-bottom: 1rem; color: var(--primary-navy);">Keputusan Validasi KPS</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; line-height: 1.5;">
              Tentukan keabsahan laporan. Laporan yang valid akan langsung diteruskan ke proses pemanggilan mediasi dan pembinaan mahasiswa.
            </p>

            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
              <button type="button" id="btnApprovePage" class="btn-primary" style="background-color: #16a34a; text-align: center; border: none; cursor: pointer;">✓ Setujui (Laporan Valid)</button>
              <button type="button" id="btnRejectPage" class="btn-primary" style="background-color: #dc2626; text-align: center; border: none; cursor: pointer;">✕ Tolak (Tidak Valid)</button>
              <a href="dashboard.php" class="btn-detail" style="text-align: center; text-decoration: none; margin-top: 0.5rem;">Kembali ke Dashboard</a>
            </div>
          </div>
        </div>

      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../../includes/scripts.php'; ?>
</body>

</html>