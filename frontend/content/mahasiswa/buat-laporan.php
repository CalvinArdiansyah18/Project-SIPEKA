<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Pengaduan - SIPEKA</title>
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
    <!-- Sidebar -->
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
          <li class="active">
            <a href="kategori.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 20h9" />
                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z" />
              </svg>
              <span>Buat Pengaduan</span>
            </a>
          </li>
          <li>
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

    <!-- Main Content -->
    <main class="app-main">
      <header class="app-header">
        <div class="header-breadcrumb">
          <a href="kategori.php" class="breadcrumb-link">
            <svg class="icon" viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2"
              fill="none" stroke-linecap="round" stroke-linejoin="round">
              <path d="m15 18-6-6 6-6" />
            </svg>
            Kembali Pilih Kategori
          </a>
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
        <div class="page-title page-title--spaced">
          <h2>Detail Formulir Pengaduan</h2>
          <p>Lengkapi informasi laporan dengan jelas dan sertakan bukti pendukung jika ada.</p>
        </div>

        <div class="form-card">
          <form id="reportForm">
            <div class="form-group">
              <label for="kategoriText">Kategori Terpilih</label>
              <input type="text" id="kategoriText" class="form-input form-input--readonly" readonly>
            </div>

            <div class="form-group">
              <label for="judul">Judul Pengaduan <span class="required-mark">*</span></label>
              <input type="text" id="judul" name="judul" class="form-input" minlength="10" required>
              <small class="form-hint">Minimal 10 karakter.</small>
            </div>

            <div class="form-grid">
              <div class="form-group">
                <label for="lokasi">Lokasi Kejadian <span class="required-mark">*</span></label>
                <input type="text" id="lokasi" name="lokasi" class="form-input"
                  placeholder="Contoh: Gedung Sipil Lt. 7 Ruang 7.02" required>
              </div>

              <div class="form-group">
                <label for="tanggal">Tanggal Kejadian <span class="required-mark">*</span></label>
                <input type="date" id="tanggal" name="tanggal" class="form-input" required>
              </div>
            </div>

            <div class="form-group">
              <label for="kronologi">Kronologi / Deskripsi Masalah <span class="required-mark">*</span></label>
              <textarea id="kronologi" name="kronologi" class="form-input"
                placeholder="Jelaskan detail permasalahan yang Anda alami atau temukan secara jelas..." rows="4"
                minlength="30" required></textarea>
              <small class="form-hint">Minimal 30 karakter agar dapat ditelaah oleh unit penanggung jawab.</small>
            </div>

            <!-- Upload Area with Drag-and-Drop & Instant Thumbnail -->
            <div class="form-group">
              <label>Lampiran Bukti Pendukung (Foto / Dokumen PDF)</label>
              <div class="file-upload-box" id="uploadBox" onclick="document.getElementById('fileUpload').click()">
                <svg class="icon" viewBox="0 0 24 24" width="36" height="36" stroke="var(--accent-blue)"
                  stroke-width="1.8" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242" />
                  <path d="M12 12v9" />
                  <path d="m16 16-4-4-4 4" />
                </svg>
                <p class="upload-title">Klik untuk unggah foto atau seret berkas ke sini</p>
                <p class="upload-subtitle">Format didukung: JPG, PNG, WEBP, PDF (Maks. 5 MB)</p>
                <input type="file" id="fileUpload" class="file-input-hidden" accept="image/*,video/*,.pdf" multiple
                  required>
              </div>

              <!-- Container for Live Thumbnail Preview -->
              <div id="previewContainer"></div>
            </div>

            <div class="info-box info-box--blue">
              <svg class="icon" viewBox="0 0 24 24" width="20" height="20" stroke="#1d4ed8" stroke-width="2" fill="none"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" x2="12" y1="8" y2="12" />
                <line x1="12" x2="12.01" y1="16" y2="16" />
              </svg>
              <p>
                <strong>Laporan Formal Terverifikasi:</strong> Laporan dikirim atas nama akun mahasiswa Anda untuk
                menjamin akuntabilitas. Identitas aman dan diteruskan langsung ke unit penanggung jawab.
              </p>
            </div>

            <div class="form-actions">
              <a href="kategori.php" class="btn-ghost">Batal</a>
              <button type="submit" class="btn-primary btn-inline btn-inline--lg">
                <span>Kirim Pengaduan Sekarang</span>
                <svg class="icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5"
                  fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M5 12h14" />
                  <path d="m12 5 7 7-7 7" />
                </svg>
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../includes/scripts.php'; ?>
</body>

</html>