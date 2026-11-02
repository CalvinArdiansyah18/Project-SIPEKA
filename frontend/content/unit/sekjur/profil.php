<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Unit Sekjur - SIPEKA</title>
  <?php include __DIR__ . '/../../../includes/styles.php'; ?>
</head>
<body data-unit="sekjur">
  <!-- Mobile Topbar -->
  <div class="mobile-topbar topbar--unit">
    <div class="topbar-brand">
      <div class="logo-badge logo-badge--sm">SJ</div>
      <span class="topbar-title">SIPEKA Sekjur</span>
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
    <aside class="app-sidebar sidebar--unit">
      <div>
        <div class="sidebar-logo">
          <div class="logo-badge">SJ</div>
          <div class="logo-text">
            <h1>SIPEKA Sekjur</h1>
            <p>Sekretariat Jurusan (Sarpras)</p>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li>
            <a href="dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1 2-2h2" />
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
              </svg>
              <span>Daftar Laporan Masuk</span>
            </a>
          </li>
          <li class="active">
            <a href="profil.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
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
          <span class="breadcrumb-text">Portal Penanganan Unit Resmi &rsaquo; <strong>Profil Sekretariat Jurusan</strong></span>
        </div>
        <div class="user-profile">
          <div class="avatar avatar--unit">SJ</div>
          <div class="user-info">
            <div class="user-name">Staff Sekretariat Jurusan</div>
            <div class="user-role">Unit Sarana &amp; Prasarana</div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-title page-title--tight">
          <h2>Profil Unit &amp; Petugas Sekjur</h2>
          <p>Informasi identitas kedinasan, cakupan wewenang sarpras kampus, serta pengaturan akun.</p>
        </div>

        <div class="profile-grid">
          <!-- Left: Identity Card -->
          <div class="profile-card">
            <div class="avatar-large" id="profAvatar">SJ</div>
            <h3 id="profCardNama" class="profile-name">Staff Sekretariat Jurusan</h3>
            <span class="badge badge-soft">
              🏛️ Unit Sarana &amp; Prasarana
            </span>

            <div class="profile-detail-list">
              <div class="profile-detail-item">
                <span class="label">Nama Unit Resmi</span>
                <span class="value" id="profDetailUnit">Sekretariat Jurusan (Sekjur)</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Kode Unit</span>
                <span class="value" id="profDetailKode">sekjur</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Status Unit</span>
                <span class="value text-success">Aktif &amp; Terverifikasi</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Email Kedinasan</span>
                <span class="value" id="profDetailEmail">sekjur@polinema.ac.id</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Lokasi Kantor</span>
                <span class="value">Gedung Sipil Lt. 1 - Ruang Sekjur JTI</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Jam Pelayanan</span>
                <span class="value">Senin - Jumat, 08.00 - 16.00 WIB</span>
              </div>
            </div>
          </div>

          <!-- Right: Forms & Scope -->
          <div class="profile-side">
            <!-- Authority Scope Info Box -->
            <div class="info-box info-box--green">
              <h4>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
                Wewenang Penanganan Unit (Sesuai Proposal PBL)
              </h4>
              <p id="profDetailWewenang">
                Menangani perbaikan, perawatan, dan pengadaan sarana kelas, lab komputer, proyektor, AC, stop kontak, toilet umum, dan sarana prasarana fisik di lingkungan Jurusan Teknologi Informasi Politeknik Negeri Malang.
              </p>
            </div>

            <!-- Workload Stats -->
            <div class="data-card card-padded">
              <h4 class="card-subtitle">Ringkasan Beban Kerja Sarpras</h4>
              <div class="stat-badge-grid">
                <div class="stat-badge-card">
                  <div class="num" id="statProfTotal">0</div>
                  <div class="txt">Total Masuk</div>
                </div>
                <div class="stat-badge-card">
                  <div class="num text-warning" id="statProfMenunggu">0</div>
                  <div class="txt">Perlu Validasi</div>
                </div>
                <div class="stat-badge-card">
                  <div class="num text-info" id="statProfProses">0</div>
                  <div class="txt">Dalam Pengerjaan</div>
                </div>
                <div class="stat-badge-card">
                  <div class="num text-success" id="statProfSelesai">0</div>
                  <div class="txt">Telah Selesai</div>
                </div>
              </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="data-card card-padded-lg">
              <h4 class="card-subtitle card-subtitle--spaced">Perbarui Data Kontak Petugas</h4>
              <form id="editProfileForm">
                <div class="form-grid">
                  <div class="form-group">
                    <label for="editNama">Nama Lengkap Petugas</label>
                    <input type="text" id="editNama" class="form-input" placeholder="Nama Petugas Sekjur" required>
                  </div>
                  <div class="form-group">
                    <label for="editTelepon">Nomor WhatsApp / Kantor</label>
                    <input type="text" id="editTelepon" class="form-input" placeholder="Contoh: 081234567890">
                  </div>
                </div>
                <div class="text-right mt-md">
                  <button type="submit" class="btn-primary btn-inline">Simpan Perubahan</button>
                </div>
              </form>
            </div>

            <!-- Change Password Form -->
            <!-- <div class="data-card card-padded-lg">
              <h4 class="card-subtitle card-subtitle--spaced">Ganti Kata Sandi Akun Dinas</h4>
              <form id="changePasswordForm">
                <div class="form-group">
                  <label for="passLama">Kata Sandi Saat Ini</label>
                  <input type="password" id="passLama" class="form-input" placeholder="Masukkan kata sandi lama Anda" required>
                </div>
                <div class="form-grid">
                  <div class="form-group">
                    <label for="passBaru">Kata Sandi Baru</label>
                    <input type="password" id="passBaru" class="form-input" placeholder="Minimal 6 karakter" required>
                  </div>
                  <div class="form-group">
                    <label for="passKonfirm">Ulangi Kata Sandi Baru</label>
                    <input type="password" id="passKonfirm" class="form-input" placeholder="Konfirmasi kata sandi" required>
                  </div>
                </div>
                <div class="text-right mt-md">
                  <button type="submit" class="btn-primary btn-navy btn-inline">Perbarui Kata Sandi</button>
                </div>
              </form>
            </div> -->

          </div>
        </div>

      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../../includes/scripts.php'; ?>
</body>
</html>