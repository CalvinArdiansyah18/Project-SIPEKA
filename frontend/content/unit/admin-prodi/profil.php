<?php
$unitKey    = 'admin-prodi';
$pageTitle  = 'Profil Unit Admin Prodi - SIPEKA';
$breadcrumb = 'Portal Penanganan Unit Resmi &rsaquo; <strong>Profil Admin Prodi</strong>';
$activeMenu = 'profil';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-title page-title--tight">
          <h2>Profil Unit &amp; Petugas Admin Prodi</h2>
          <p>Informasi identitas kedinasan, cakupan wewenang pelayanan akademik, serta pengaturan akun.</p>
        </div>

        <div class="profile-grid">
          <!-- Left: Identity Card -->
          <div class="profile-card">
            <div class="avatar-large avatar--unit" id="profAvatar"> </div>
            <h3 id="profCardNama" class="profile-name"> </h3>
            <span class="badge badge-soft">
              🎓 Unit Pelayanan Akademik
            </span>

            <div class="profile-detail-list">
              <div class="profile-detail-item">
                <span class="label">Nama Unit Resmi</span>
                <span class="value" id="profDetailUnit"> </span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Kode Unit</span>
                <span class="value" id="profDetailKode"> </span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Status Unit</span>
                <span class="value text-success">Aktif &amp; Terverifikasi</span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Email Kedinasan</span>
                <span class="value" id="profDetailEmail"> </span>
              </div>
              <div class="profile-detail-item">
                <span class="label">Lokasi Kantor</span>
                <span class="value">Gedung Sipil Lt. 3 - Ruang Admin Prodi JTI</span>
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
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
                Wewenang Penanganan Unit
              </h4>
              <p id="profDetailWewenang">
                Menangani kendala pengurusan KRS, sinkronisasi dan koreksi nilai praktikum/teori, jadwal perkuliahan,
                pengajuan dispensasi/surat izin, dan administrasi akademik mahasiswa di lingkungan Jurusan Teknologi
                Informasi Politeknik Negeri Malang.
              </p>
            </div>

            <!-- Workload Stats -->
            <div class="data-card card-padded">
              <h4 class="card-subtitle">Ringkasan Hasil Kerja</h4>
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
                  <div class="txt">Dalam Penanganan</div>
                </div>
                <div class="stat-badge-card">
                  <div class="num text-success" id="statProfSelesai">0</div>
                  <div class="txt">Selesai</div>
                </div>
              </div>
            </div>

            <!-- Edit Profile Form -->
            <!-- <div class="data-card card-padded-lg">
              <h4 class="card-subtitle card-subtitle--spaced">Perbarui Data Admin Prodi</h4>
              <form id="editProfileForm">
                <div class="form-grid">
                  <div class="form-group">
                    <label for="editNama">Nama Lengkap Petugas</label>
                    <input type="text" id="editNama" class="form-input" placeholder="Nama Petugas Admin Prodi" required>
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
            </div> -->

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

<?php include __DIR__ . '/../../../includes/unit/layout-end.php'; ?>
