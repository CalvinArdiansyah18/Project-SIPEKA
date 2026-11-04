<?php

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi Akun - SIPEKA Polinema</title>
  <?php include __DIR__ . '/../../includes/styles.php'; ?>
</head>
<body>

  <div class="auth-wrapper">
    <!-- Left Hero Side -->
    <aside class="auth-hero">
      <div class="auth-hero-header">
        <div class="logo-group">  
          <div class="logo-badge-1"><img src="../../assets/images/logo/JTI.webp" class="auth-logo-img"></div>
          <div class="logo-text">
            <h1>SIPEKA</h1>
            <p>Sistem Pengaduan Kampus</p>
          </div>
        </div>
      </div>

      <div class="auth-hero-body">
        <h2>Layanan Pengaduan Terintegrasi & Disposisi Langsung</h2>
        <p>Hubungkan aspirasi mahasiswa dan respon cepat unit penanggung jawab di lingkungan Jurusan Teknologi Informasi Politeknik Negeri Malang.</p>
      </div>

      <div class="auth-hero-footer">
        <div class="logo-badge-2"><img src="../../assets/images/logo/polinema.png" class="auth-logo-img"></div>
        <div class="campus-info">
          <h3>Jurusan Teknologi Informasi</h3>
          <p>Politeknik Negeri Malang</p>
        </div>
      </div>
    </aside>

    <!-- Right Form Side -->
    <main class="auth-form-side">
      <div class="auth-card register-card">
        <header class="auth-card-header">
          <h2>Buat Akun Baru</h2>
        </header>

        <form id="registerForm">
          <!-- 2. NAMA LENGKAP -->
          <div class="form-group">
            <label for="regNama" id="labelNama">Nama Lengkap Mahasiswa <span style="color: #ef4444;">*</span></label>
            <input 
              type="text" 
              id="regNama" 
              name="nama" 
              class="form-input" 
              placeholder=" Masukkan Nama " 
              required
            >
            <div id="errNama" class="error-msg">Nama lengkap minimal 3 karakter.</div>
          </div>

          <!-- NIM & Prodi -->
          <div id="mhsFields">
            <div class="form-grid-2">
              <div class="form-group">
                <label for="regNim">NIM (Nomor Induk Mahasiswa) <span style="color: #ef4444;">*</span></label>
                <input 
                  type="text" 
                  id="regNim" 
                  name="nim" 
                  class="form-input" 
                  placeholder="Contoh: 254100000000" 
                  maxlength="15"
                >
                <div id="errNim" class="error-msg">NIM harus angka (8-15 digit).</div>
              </div>

              <div class="form-group">
                <label for="regProdi">Program Studi <span style="color: #ef4444;">*</span></label>
                <select id="regProdi" name="program_studi" class="form-input" style="font-weight: 500; cursor: pointer;">
                  <option value="D4 Teknik Informatika">D4 Teknik Informatika</option>
                  <option value="D4 Sistem Informasi Bisnis">D4 Sistem Informasi Bisnis</option>
                  <option value="D3 Manajemen Informatika">D3 Manajemen Informatika</option>
                </select>
              </div>
            </div>
          </div>

          <!-- 4. EMAIL & TELEPON  -->
          <div class="form-grid-2">
            <div class="form-group">
              <label for="regEmail" id="labelEmail">Email Mahasiswa <span style="color: #ef4444;">*</span></label>
              <input 
                type="email" 
                id="regEmail" 
                name="email" 
                class="form-input" 
                placeholder="nama@student.polinema.ac.id" 
                required
              >
              <div id="errEmail" class="error-msg">Format email tidak valid.</div>
            </div>

            <div class="form-group">
              <label for="regTelepon">No. WhatsApp / HP</label>
              <input 
                type="tel" 
                id="regTelepon" 
                name="nomor_telepon" 
                class="form-input" 
                placeholder="Contoh: 081234567890"
              >
            </div>
          </div>

          <!-- 5. PASSWORD & KONFIRMASI -->
          <div class="form-grid-2">
            <div class="form-group">
              <label for="regPassword">Kata Sandi <span style="color: #ef4444;">*</span></label>
              <input 
                type="password" 
                id="regPassword" 
                name="password" 
                class="form-input" 
                placeholder="Minimal 8 karakter" 
                required
              >
              <div id="errPassword" class="error-msg">Password minimal 8 karakter.</div>
            </div>

            <div class="form-group">
              <label for="regConfirmPassword">Ulangi Kata Sandi <span style="color: #ef4444;">*</span></label>
              <input 
                type="password" 
                id="regConfirmPassword" 
                name="confirm_password" 
                class="form-input" 
                placeholder="Ketik ulang kata sandi" 
                required
              >
              <div id="errConfirm" class="error-msg">Konfirmasi kata sandi tidak cocok.</div>
            </div>
          </div>

          <button type="submit" id="btnRegister" class="btn-primary" style="margin-top: 0.5rem;">
            DAFTAR 
          </button>
        </form>

        <p class="auth-footer-text">
          Sudah memiliki akun? <a href="login.php">Masuk ke SIPEKA</a>
        </p>
      </div>
    </main>
  </div>
  <!-- tes -->
  <?php include __DIR__ . '/../../includes/scripts.php'; ?>

   </body>
   </html>
