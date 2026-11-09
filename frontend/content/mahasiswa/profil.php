<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya - SIPEKA Mahasiswa</title>
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
          <li class="active">
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
          <span class="breadcrumb-text">Portal Mahasiswa JTI Polinema &rsaquo; <strong>Profil</strong></span>
        </div>
        <div class="user-profile user-profile--clickable" onclick="window.location.href='profil.php'">
          <div class="avatar " id="topbarAvatar"> </div>
          <div class="user-info">
            <div class="user-name" id="topbarName"> </div>
            <div class="user-role" id="topbarRole"> </div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-title page-title--spaced">
          <h2>Profil Pengguna Mahasiswa</h2>
          <p>Informasi identitas akademik resmi dan pengelolaan keamanan akun SIPEKA Anda.</p>
        </div>

        <div class="profile-grid">
          <!-- Left Column: Summary Profile Card -->
          <div class="profile-card">
            <div class="avatar-large avatar" id="profAvatar"> </div>
            <h3 id="profCardNama" class="profile-name"> </h3>
            <div class="profile-badge">
              ✓ Mahasiswa Aktif Polinema
            </div>

            <div class="stat-mini-grid">
              <div class="stat-mini-item">
                <div class="stat-mini-num" id="statProfTotal">0</div>
                <div class="stat-mini-label">Total Laporan</div>
              </div>
              <div class="stat-mini-item">
                <div class="stat-mini-num text-orange" id="statProfProses">0</div>
                <div class="stat-mini-label">Dalam Penanganan</div>
              </div>
              <div class="stat-mini-item">
                <div class="stat-mini-num text-emerald" id="statProfSelesai">0</div>
                <div class="stat-mini-label">Selesai Ditangani</div>
              </div>
              <div class="stat-mini-item">
                <div class="stat-mini-num text-danger" id="statProfDitolak">0</div>
                <div class="stat-mini-label">Ditolak</div>
              </div>
            </div>

            <div class="profile-detail-list">
              <div class="profile-detail-item">
                <span class="label">Nomor Induk Mahasiswa (NIM)</span>
                <span class="value" id="profDetailNim"> </span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Program Studi</span>
                <span class="value" id="profDetailProdi"> </span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Jurusan</span>
                <span class="value" id="profDetailJurusan"> </span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Email SSO Kampus</span>
                <span class="value" id="profDetailEmail"> </span>
              </div>
            </div>
          </div>

          <!-- Right Column: Settings and Change Password Cards -->
          <div class="profile-side">

            <!-- Card 1: Ubah Informasi Kontak -->
            <div class="form-card form-card--full">
              <h3 class="card-title">Perbarui Data Profil</h3>
              <p class="card-note card-note--tight">
                Nama dan kontak dapat diperbarui untuk keperluan korespondensi penanganan laporan.
              </p>

              <form id="editProfileForm">
                <div class="form-group">
                  <label for="editNama">Nama Lengkap Mahasiswa <span class="required-mark">*</span></label>
                  <input type="text" id="editNama" name="nama" class="form-input" required>
                </div>

                <div class="form-grid">
                  <div class="form-group">
                    <label for="editNim">NIM (Terikat Resmi)</label>
                    <input type="text" id="editNim" class="form-input form-input--locked" readonly>
                  </div>

                  <div class="form-group">
                    <label for="editProdi">Program Studi</label>
                    <input type="text" id="editProdi" class="form-input form-input--locked" readonly>
                  </div>
                </div>

                <div class="form-group">
                  <label for="editTelepon">Nomor WhatsApp / HP Aktif</label>
                  <input type="tel" id="editTelepon" name="nomor_telepon" class="form-input"
                    placeholder="Contoh: 081234567890">
                </div>

                <div class="text-right mt-md">
                  <button type="submit" class="btn-primary btn-inline btn-inline--md">
                    Simpan Perubahan Data
                  </button>
                </div>
              </form>
            </div>

            <!-- Card 2: Ubah Kata Sandi -->
            <div class="form-card form-card--full">
              <h3 class="card-title">Ubah Kata Sandi Akun</h3>
              <p class="card-note card-note--tight">
                Gunakan minimal 8 karakter kombinasi huruf dan angka untuk menjaga keamanan akun Anda.
              </p>

              <form id="changePasswordForm">
                <div class="form-group">
                  <label for="passLama">Kata Sandi Saat Ini <span class="required-mark">*</span></label>
                  <input type="password" id="passLama" name="old_password" class="form-input"
                    placeholder="Masukkan kata sandi lama Anda" required>
                </div>

                <div class="form-grid">
                  <div class="form-group">
                    <label for="passBaru">Kata Sandi Baru <span class="required-mark">*</span></label>
                    <input type="password" id="passBaru" name="new_password" class="form-input"
                      placeholder="Minimal 8 karakter" required>
                  </div>

                  <div class="form-group">
                    <label for="passKonfirm">Ulangi Kata Sandi Baru <span class="required-mark">*</span></label>
                    <input type="password" id="passKonfirm" name="confirm_password" class="form-input"
                      placeholder="Ketik ulang kata sandi baru" required>
                  </div>
                </div>

                <div class="text-right mt-md">
                  <button type="submit" class="btn-primary btn-navy btn-inline btn-inline--md">
                    Perbarui Kata Sandi
                  </button>
                </div>
              </form>
            </div>

          </div>
        </div>

      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../includes/scripts.php'; ?>
</body>

</html>