<?php

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi Akun - SIPEKA Polinema</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
  <style>
    .register-card {
      width: 100%;
      max-width: 540px;
    }
    .form-grid-2 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
    /* .role-tabs {
      display: flex;
      background-color: #e2e8f0;
      border-radius: var(--radius-sm);
      padding: 4px;
      margin-bottom: 1.5rem;
      gap: 4px;
    } */
    /* .role-tab-btn {
      flex: 1;
      padding: 0.65rem 0.5rem;
      border: none;
      background: transparent;
      font-size: 0.85rem;
      font-weight: 600;
      color: #64748b;
      border-radius: calc(var(--radius-sm) - 2px);
      cursor: pointer;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
    } */
    .role-tab-btn.active {
      background-color: #ffffff;
      color: var(--primary-navy);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    }
    .role-tab-btn:hover:not(.active) {
      color: var(--text-main);
    }
    .unit-info-box {
      background-color: #f1f5f9;
      border-left: 3px solid var(--accent-blue);
      padding: 0.75rem 1rem;
      border-radius: 0 var(--radius-sm) var(--radius-sm) 0;
      font-size: 0.8rem;
      color: #334155;
      margin-top: 0.5rem;
      line-height: 1.4;
    }
    .input-error {
      border-color: #ef4444 !important;
      background-color: #fef2f2 !important;
    }
    .error-msg {
      font-size: 0.75rem;
      color: #ef4444;
      margin-top: 0.25rem;
      display: none;
    }
    @media (max-width: 640px) {
      .form-grid-2 {
        grid-template-columns: 1fr;
        gap: 0;
      }
    }
  </style>
</head>
<body>

  <div class="auth-wrapper">
    <!-- Left Hero Side -->
    <aside class="auth-hero">
      <div class="auth-hero-header">
        <div class="logo-group">  
          <div class="logo-badge-1"> <img src="../../assets/images/logo/JTI.webp" style="width: 450%; height: auto;"> </div>
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
        <div class="logo-badge-2"> <img src="../../assets/images/logo/polinema.png" style="width: 450%; height: auto;"> </div>
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
          <!-- 2. NAMA LENGKAP (Bersama) -->
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
                placeholder="Minimal 6 karakter" 
                required
              >
              <div id="errPassword" class="error-msg">Password minimal 6 karakter.</div>
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
  <script src="../../assets/js/store.js"></script>
  <script src="../../assets/js/ui.js"></script>
  <script src="../../assets/js/app.js"></script>
  <script src="../../assets/js/api.js"></script>

   </body>
   </html>
