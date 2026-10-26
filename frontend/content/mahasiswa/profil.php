<?php
/**
 * SIPEKA - Halaman Profil Pengguna Mahasiswa
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Saya - SIPEKA Mahasiswa</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    .profile-grid {
      display: grid;
      grid-template-columns: 320px 1fr;
      gap: 1.5rem;
      align-items: start;
    }
    .profile-card {
      background: #ffffff;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 1.75rem;
      box-shadow: var(--shadow-sm);
      text-align: center;
    }
    .avatar-large {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--accent-blue), var(--primary-navy));
      color: #ffffff;
      font-size: 2rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
    }
    .profile-detail-list {
      text-align: left;
      margin-top: 1.5rem;
      border-top: 1px solid var(--border-color);
      padding-top: 1rem;
    }
    .profile-detail-item {
      display: flex;
      flex-direction: column;
      margin-bottom: 0.85rem;
    }
    .profile-detail-item .label {
      font-size: 0.75rem;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: 0.5px;
      font-weight: 600;
    }
    .profile-detail-item .value {
      font-size: 0.9rem;
      font-weight: 600;
      color: var(--text-main);
      margin-top: 2px;
      word-break: break-all;
    }
    .stat-mini-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 0.5rem;
      margin-top: 1.25rem;
      text-align: center;
    }
    .stat-mini-item {
      background: var(--bg-light);
      padding: 0.6rem;
      border-radius: var(--radius-sm);
      border: 1px solid #e2e8f0;
    }
    .stat-mini-num {
      font-size: 1.15rem;
      font-weight: 800;
      color: var(--primary-navy);
    }
    .stat-mini-label {
      font-size: 0.7rem;
      color: var(--text-muted);
    }
    @media (max-width: 900px) {
      .profile-grid {
        grid-template-columns: 1fr;
      }
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
          <li class="active">
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
          <a href="dashboard.php" style="color: var(--text-muted); text-decoration: none; font-size: 0.85rem;">Dashboard</a>
          <span style="color: var(--text-muted); font-size: 0.85rem;"> / Profil Saya</span>
        </div>
        <div class="user-profile" style="cursor: pointer;" onclick="window.location.href='profil.php'">
          <div class="avatar" id="topbarAvatar">GM</div>
          <div class="user-info">
            <div class="user-name" id="topbarName">Galih Maula Mumtaza</div>
            <div class="user-role" id="topbarRole">NIM: 254107020095 (TI-2F)</div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-title" style="margin-bottom: 2rem;">
          <h2>Profil Pengguna Mahasiswa</h2>
          <p>Informasi identitas akademik resmi dan pengelolaan keamanan akun SIPEKA Anda.</p>
        </div>

        <div class="profile-grid">
          <!-- Left Column: Summary Profile Card -->
          <div class="profile-card">
            <div class="avatar-large" id="profAvatar">GM</div>
            <h3 id="profCardNama" style="color: var(--primary-navy); font-size: 1.15rem; margin-bottom: 0.25rem;">Galih Maula Mumtaza</h3>
            <div style="display: inline-block; background-color: #dbeafe; color: #1e40af; font-size: 0.75rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; margin-bottom: 1rem;">
              ✓ Mahasiswa Aktif Polinema
            </div>

            <div class="stat-mini-grid">
              <div class="stat-mini-item">
                <div class="stat-mini-num" id="statProfTotal">0</div>
                <div class="stat-mini-label">Total Laporan</div>
              </div>
              <div class="stat-mini-item">
                <div class="stat-mini-num" id="statProfProses" style="color: var(--accent-orange);">0</div>
                <div class="stat-mini-label">Diproses</div>
              </div>
              <div class="stat-mini-item">
                <div class="stat-mini-num" id="statProfSelesai" style="color: #10b981;">0</div>
                <div class="stat-mini-label">Selesai</div>
              </div>
              <div class="stat-mini-item">
                <div class="stat-mini-num" id="statProfDitolak" style="color: #ef4444;">0</div>
                <div class="stat-mini-label">Tidak Valid</div>
              </div>
            </div>

            <div class="profile-detail-list">
              <div class="profile-detail-item">
                <span class="label">Nomor Induk Mahasiswa (NIM)</span>
                <span class="value" id="profDetailNim">254107020095</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Program Studi</span>
                <span class="value" id="profDetailProdi">D4 Teknik Informatika (TI-2F)</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Jurusan</span>
                <span class="value" id="profDetailJurusan">Teknologi Informasi</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Email SSO Kampus</span>
                <span class="value" id="profDetailEmail">254107020095@student.polinema.ac.id</span>
              </div>
            </div>
          </div>

          <!-- Right Column: Settings and Change Password Cards -->
          <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            
            <!-- Card 1: Ubah Informasi Kontak -->
            <div class="form-card" style="max-width: 100%;">
              <h3 style="color: var(--primary-navy); margin-bottom: 0.5rem;">Perbarui Data Profil</h3>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                Nama dan kontak dapat diperbarui untuk keperluan korespondensi penanganan laporan.
              </p>

              <form id="editProfileForm">
                <div class="form-group">
                  <label for="editNama">Nama Lengkap Mahasiswa <span style="color: #ef4444;">*</span></label>
                  <input type="text" id="editNama" name="nama" class="form-input" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                  <div class="form-group">
                    <label for="editNim">NIM (Terikat Resmi)</label>
                    <input type="text" id="editNim" class="form-input" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                  </div>

                  <div class="form-group">
                    <label for="editProdi">Program Studi</label>
                    <input type="text" id="editProdi" class="form-input" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                  </div>
                </div>

                <div class="form-group">
                  <label for="editTelepon">Nomor WhatsApp / HP Aktif</label>
                  <input type="tel" id="editTelepon" name="nomor_telepon" class="form-input" placeholder="Contoh: 081234567890">
                </div>

                <div style="text-align: right; margin-top: 1rem;">
                  <button type="submit" class="btn-primary" style="width: auto; padding: 0.75rem 2rem; margin-top: 0;">
                    Simpan Perubahan Data
                  </button>
                </div>
              </form>
            </div>

            <!-- Card 2: Ubah Kata Sandi -->
            <div class="form-card" style="max-width: 100%;">
              <h3 style="color: var(--primary-navy); margin-bottom: 0.5rem;">Ubah Kata Sandi Akun</h3>
              <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                Gunakan minimal 6 karakter kombinasi huruf dan angka untuk menjaga keamanan akun Anda.
              </p>

              <form id="changePasswordForm">
                <div class="form-group">
                  <label for="passLama">Kata Sandi Saat Ini <span style="color: #ef4444;">*</span></label>
                  <input type="password" id="passLama" name="old_password" class="form-input" placeholder="Masukkan kata sandi lama Anda" required>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                  <div class="form-group">
                    <label for="passBaru">Kata Sandi Baru <span style="color: #ef4444;">*</span></label>
                    <input type="password" id="passBaru" name="new_password" class="form-input" placeholder="Minimal 6 karakter" required>
                  </div>

                  <div class="form-group">
                    <label for="passKonfirm">Ulangi Kata Sandi Baru <span style="color: #ef4444;">*</span></label>
                    <input type="password" id="passKonfirm" name="confirm_password" class="form-input" placeholder="Ketik ulang kata sandi baru" required>
                  </div>
                </div>

                <div style="text-align: right; margin-top: 1rem;">
                  <button type="submit" class="btn-primary" style="background-color: var(--primary-navy); width: auto; padding: 0.75rem 2rem; margin-top: 0;">
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

  <script src="../assets/js/store.js"></script>
  <script src="../assets/js/api.js"></script>
  <script src="../assets/js/ui.js"></script>
  <script src="../assets/js/app.js"></script>
</body>
</html>
