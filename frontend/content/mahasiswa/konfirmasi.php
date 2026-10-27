<?php

/**
 * SIPEKA - Konfirmasi Pengaduan Mahasiswa
 */
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konfirmasi Pengaduan - SIPEKA</title>
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
          <a href="buat-laporan.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">← Kembali ke Form</a>
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
          <h2>Konfirmasi Pengaduan</h2>
          <p>Periksa kembali ringkasan laporan Anda sebelum dikirimkan ke unit penanggung jawab.</p>
        </div>

        <div class="form-card">
          <h3 style="margin-bottom: 1.5rem; color: var(--primary-navy); border-bottom: 2px solid var(--bg-light); padding-bottom: 0.5rem;">Ringkasan Laporan</h3>

          <table class="summary-table">
            <tbody>
              <tr>
                <td class="label">Kategori Masalah</td>
                <td><strong>Fasilitas Kampus</strong></td>
              </tr>
              <tr>
                <td class="label">Unit Disposisi Tujuan</td>
                <td><span class="badge badge-info">Sekjur (Sekretariat Jurusan)</span></td>
              </tr>
              <tr>
                <td class="label">Judul Pengaduan</td>
                <td>Kerusakan Proyektor & Stop Kontak Ruang Lab 3</td>
              </tr>
              <tr>
                <td class="label">Lokasi Kejadian</td>
                <td>Gedung Sipil Lt. 7 - Lab Komputer 3</td>
              </tr>
              <tr>
                <td class="label">Tanggal Kejadian</td>
                <td>6 Oktober 2026</td>
              </tr>
              <tr>
                <td class="label">Deskripsi / Kronologi</td>
                <td>Proyektor mati mendadak saat sesi praktikum berlangsung dan stop kontak di baris tengah mengeluarkan percikan kecil.</td>
              </tr>
              <tr>
                <td class="label">Berkas Lampiran</td>
                <td>📄 <code>foto-kerusakan-proyektor.jpg</code> (1.2 MB)</td>
              </tr>
              <tr>
                <td class="label">Data Pelapor</td>
                <td>Galih Maula Mumtaza (NIM: 254107020095 - TI 2F)</td>
              </tr>
            </tbody>
          </table>

          <div style="background-color: #fefce8; border-left: 4px solid var(--accent-orange); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 2rem;">
            <p style="font-size: 0.85rem; color: #854d0e;">
              ⚠️ Setelah dikirim, laporan akan langsung diteruskan ke dashboard <strong>Sekjur</strong> untuk diverifikasi dan ditindaklanjuti.
            </p>
          </div>

          <div style="display: flex; justify-content: flex-end; gap: 1rem;">
            <a href="buat-laporan.php" style="padding: 0.85rem 1.5rem; text-decoration: none; color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">Ubah Data</a>
            <a href="riwayat.php?success=true" class="btn-primary" style="width: auto; padding: 0.85rem 2.5rem; margin-top: 0; text-decoration: none; text-align: center;">Kirim Pengaduan Sekarang ✓</a>
          </div>

        </div>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../includes/scripts.php'; ?>
</body>

</html>