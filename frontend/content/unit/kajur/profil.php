<?php

/**
 * SIPEKA - Profil Ketua Jurusan (Kajur)
 */

?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Ketua Jurusan - SIPEKA</title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
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
      background: linear-gradient(135deg, #b45309, #d97706);
      color: #ffffff;
      font-size: 2rem;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      box-shadow: 0 4px 12px rgba(180, 83, 9, 0.25);
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
      color: var(--text-main);
      font-weight: 500;
      margin-top: 2px;
    }

    .stat-badge-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 0.75rem;
      margin-bottom: 1.5rem;
    }

    .stat-badge-card {
      background: #f8fafc;
      border: 1px solid var(--border-color);
      border-radius: var(--radius-sm);
      padding: 0.85rem;
      text-align: center;
    }

    .stat-badge-card .num {
      font-size: 1.35rem;
      font-weight: 700;
      color: var(--primary-navy);
    }

    .stat-badge-card .txt {
      font-size: 0.75rem;
      color: var(--text-muted);
      margin-top: 2px;
    }

    @media (max-width: 900px) {
      .profile-grid {
        grid-template-columns: 1fr;
      }

      .stat-badge-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }
  </style>
</head>

<body data-unit="kajur">

  <!-- Mobile Topbar -->
  <div class="mobile-topbar" style="background-color: #1e293b;">
    <div style="display: flex; align-items: center; gap: 10px;">
      <div class="logo-badge" style="background: #b45309; width: 30px; height: 30px; font-size: 0.9rem;">KJ</div>
      <span style="font-weight: 700; font-size: 1.05rem; letter-spacing: -0.5px;">SIPEKA Kajur</span>
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
          <div class="logo-badge" style="background: #b45309;">KJ</div>
          <div class="logo-text">
            <h1 style="color: #fff; font-size: 1.15rem;">SIPEKA Kajur</h1>
            <p style="font-size: 0.7rem; color: #94a3b8;">Ketua Jurusan TI Polinema</p>
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
          <span style="color: var(--text-muted); font-size: 0.85rem;">Portal Penanganan Unit Resmi &rsaquo; <strong>Profil Ketua Jurusan</strong></span>
        </div>
        <div class="user-profile">
          <div class="avatar" style="background-color: #b45309;">KJ</div>
          <div class="user-info">
            <div class="user-name">Ketua Jurusan TI</div>
            <div class="user-role">Pimpinan Jurusan &amp; Pengawasan Etika</div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-title" style="margin-bottom: 1.5rem;">
          <h2>Profil Pimpinan &amp; Petugas Kajur</h2>
          <p>Informasi identitas kedinasan pimpinan jurusan, cakupan wewenang etika &amp; pungli, serta pengaturan akun.</p>
        </div>

        <div class="profile-grid">
          <!-- Left: Identity Card -->
          <div class="profile-card">
            <div class="avatar-large" id="profAvatar">KJ</div>
            <h3 id="profCardNama" style="color: var(--primary-navy); margin-bottom: 0.25rem;">Ketua Jurusan TI Polinema</h3>
            <span class="badge" style="background: #fef3c7; color: #b45309; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 600;">
              ⚖️ Pimpinan Jurusan &amp; Etika
            </span>

            <div class="profile-detail-list">
              <div class="profile-detail-item">
                <span class="label">Jabatan Kedinasan</span>
                <span class="value" id="profDetailUnit">Ketua Jurusan (Kajur)</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Kode Unit</span>
                <span class="value" id="profDetailKode">kajur</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Status Jabatan</span>
                <span class="value" style="color: #16a34a; font-weight: 600;">Aktif &amp; Terverifikasi</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Email Kedinasan</span>
                <span class="value" id="profDetailEmail">kajur@polinema.ac.id</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Lokasi Kantor</span>
                <span class="value">Gedung Sipil Lt. 2 - Ruang Pimpinan Jurusan TI</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Jadwal Koordinasi</span>
                <span class="value">Senin - Jumat, 08.00 - 16.00 WIB</span>
              </div>
            </div>
          </div>

          <!-- Right: Forms & Scope -->
          <div style="display: flex; flex-direction: column; gap: 1.5rem;">
            <!-- Authority Scope Info Box -->
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md); padding: 1.25rem;">
              <h4 style="color: #166534; margin: 0 0 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
                Wewenang Penanganan unit
              </h4>
              <p id="profDetailWewenang" style="font-size: 0.85rem; color: #15803d; line-height: 1.6; margin: 0;">
                Menangani secara langsung kode etik tenaga pengajar dan staf kependidikan, transparansi akademik, ketidakhadiran dosen, serta pencegahan dan penindakan tegas segala bentuk dugaan pungutan liar (pungli) di luar ketentuan resmi kampus.
              </p>
            </div>

            <!-- Workload Stats -->
            <div class="data-card" style="padding: 1.25rem;">
              <h4 style="color: var(--primary-navy); margin: 0 0 1rem; font-size: 0.95rem;">Ringkasan Beban Pengawasan Kajur</h4>
              <div class="stat-badge-grid">
                <div class="stat-badge-card">
                  <div class="num" id="statProfTotal">0</div>
                  <div class="txt">Total Masuk</div>
                </div>
                <div class="stat-badge-card">
                  <div class="num" id="statProfMenunggu" style="color: #ea580c;">0</div>
                  <div class="txt">Perlu Validasi</div>
                </div>
                <div class="stat-badge-card">
                  <div class="num" id="statProfProses" style="color: #2563eb;">0</div>
                  <div class="txt">Klarifikasi Aktif</div>
                </div>
                <div class="stat-badge-card">
                  <div class="num" id="statProfSelesai" style="color: #16a34a;">0</div>
                  <div class="txt">Terselesaikan</div>
                </div>
              </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="data-card" style="padding: 1.5rem;">
              <h4 style="color: var(--primary-navy); margin: 0 0 1.25rem;">Perbarui Data Kontak Pimpinan / Staf Ahli</h4>
              <form id="editProfileForm">
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                  <div class="form-group">
                    <label for="editNama">Nama Lengkap &amp; Gelar</label>
                    <input type="text" id="editNama" class="form-input" placeholder="Nama Ketua Jurusan" required>
                  </div>
                  <div class="form-group">
                    <label for="editTelepon">Nomor WhatsApp / Kantor</label>
                    <input type="text" id="editTelepon" class="form-input" placeholder="Contoh: 081234567890">
                  </div>
                </div>
                <div style="text-align: right; margin-top: 1rem;">
                  <button type="submit" class="btn-primary" style="width: auto; padding: 0.65rem 1.5rem;">Simpan Perubahan</button>
                </div>
              </form>
            </div>

            <!-- Change Password Form -->
            <div class="data-card" style="padding: 1.5rem;">
              <h4 style="color: var(--primary-navy); margin: 0 0 1.25rem;">Ganti Kata Sandi Akun Pimpinan</h4>
              <form id="changePasswordForm">
                <div class="form-group">
                  <label for="passLama">Kata Sandi Saat Ini</label>
                  <input type="password" id="passLama" class="form-input" placeholder="Masukkan kata sandi lama Anda" required>
                </div>
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                  <div class="form-group">
                    <label for="passBaru">Kata Sandi Baru</label>
                    <input type="password" id="passBaru" class="form-input" placeholder="Minimal 6 karakter" required>
                  </div>
                  <div class="form-group">
                    <label for="passKonfirm">Ulangi Kata Sandi Baru</label>
                    <input type="password" id="passKonfirm" class="form-input" placeholder="Konfirmasi kata sandi" required>
                  </div>
                </div>
                <div style="text-align: right; margin-top: 1rem;">
                  <button type="submit" class="btn-primary" style="width: auto; padding: 0.65rem 1.5rem; background: #0f172a;">Perbarui Kata Sandi</button>
                </div>
              </form>
            </div>

          </div>
        </div>

      </div>
    </main>
  </div>

  <?php include __DIR__ . '/../../../includes/scripts.php'; ?>
</body>

</html>