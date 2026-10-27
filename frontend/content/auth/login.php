<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk ke SIPEKA - Sistem Pengaduan Kampus JTI Polinema</title>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>

<body>

  <div class="auth-wrapper">
    <!-- Left Hero Side -->
    <aside class="auth-hero">
      <div class="auth-hero-header">
        <div class="logo-group">
          <div class="logo-badge-1">  <img src="../../assets/images/logo/Jti.webp" style="width: 450%; height: auto;"> </div>
          <div class="logo-text">
            <h1>SIPEKA</h1>
            <p>Sistem Pengaduan Kampus</p>
          </div>
        </div>
      </div>

      <div class="auth-hero-body">
        <h2>Bersama Mewujudkan Kampus yang Aman, Nyaman dan Berintegritas</h2>
        <p>Layanan terpusat pengaduan fasilitas, pelayanan akademik, dan ketertiban di lingkungan Jurusan Teknologi Informasi Politeknik Negeri Malang.</p>
      </div>

      <div class="auth-hero-footer">
        <div class="logo-badge-2">  <img src="../../assets/images/logo/polinema.png" style="width: 450%; height: auto;"> </div>
        <div class="campus-info">
          <h3>Jurusan Teknologi Informasi</h3>
          <p>Politeknik Negeri Malang</p>
        </div>
      </div>
    </aside>

    <!-- Right Form Side -->
    <main class="auth-form-side">
      <div class="auth-card">
        <header class="auth-card-header">
          <h2>Masuk ke SIPEKA</h2>
        </header>

      <form id="loginForm">
          <div class="form-group">
            <label for="username">Email</label>
            <input 
              type="text" 
              id="username" 
              name="username" 
              class="form-input" 
              value=""
              placeholder="Masukkan email akun anda" 
              required
            >
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-input" 
              value=""
              placeholder="Masukkan password akun Anda" 
              required
            >
          </div>

          <button type="submit" class="btn-primary">LOGIN</button>
        </form>

        <p class="auth-footer-text">
         Belum memiliki akun? <a href="register.php">Daftar Akun</a>
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
