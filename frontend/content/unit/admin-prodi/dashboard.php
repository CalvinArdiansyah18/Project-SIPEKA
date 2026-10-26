<?php
/**
 * SIPEKA - Dashboard Admin Program Studi (Admin Prodi)
 * Unit Wewenang: Pelayanan Akademik (KRS, Nilai, Jadwal Perkuliahan, Administrasi Prodi)
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin Prodi - SIPEKA Polinema</title>
  <link rel="stylesheet" href="../../../assets/css/style.css">
</head>
<body data-unit="prodi">

  <!-- Mobile Topbar -->
  <div class="mobile-topbar" style="background-color: #1e293b;">
    <div style="display: flex; align-items: center; gap: 10px;">
      <div class="logo-badge" style="background: #0284c7; width: 30px; height: 30px; font-size: 0.9rem;">AP</div>
      <span style="font-weight: 700; font-size: 1.05rem; letter-spacing: -0.5px;">SIPEKA Admin Prodi</span>
    </div>
    <button class="hamburger-btn" aria-label="Buka Menu">
      <svg class="icon" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
        <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
      </svg>
    </button>
  </div>

  <div class="sidebar-backdrop"></div>

  <div class="app-layout">
    <aside class="app-sidebar" style="background-color: #1e293b;">
      <div>
        <div class="sidebar-logo">
          <div class="logo-badge" style="background: #0284c7;">AP</div>
          <div class="logo-text">
            <h1 style="color: #fff; font-size: 1.15rem;">SIPEKA Prodi</h1>
            <p style="font-size: 0.7rem; color: #94a3b8;">Admin Program Studi JTI</p>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li class="active">
            <a href="dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1 2-2h2"/><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/>
              </svg>
              <span>Daftar Laporan Masuk</span>
            </a>
          </li>
          <li>
            <a href="profil.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              <span>Profil Unit</span>
            </a>
          </li>
          <li>
            <a href="../../mahasiswa/dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="m16 16 4-4-4-4"/><path d="M4 12h16"/><path d="M8 8v8"/>
              </svg>
              <span>Beralih ke Portal Mhs</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="sidebar-footer">
        <a href="../../auth/login.php" onclick="if(window.sipekaAPI) window.sipekaAPI.logout();">
          <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
          </svg>
          <span>Keluar</span>
        </a>
      </div>
    </aside>

    <main class="app-main">
      <header class="app-header">
        <div class="header-breadcrumb">
          <span style="color: var(--text-muted); font-size: 0.85rem;">Portal Penanganan Unit Resmi &rsaquo; <strong>Admin Program Studi</strong></span>
        </div>
        <div class="user-profile" style="cursor: pointer;" onclick="window.location.href='profil.php'">
          <div class="avatar" style="background-color: #0284c7;">AP</div>
          <div class="user-info">
            <div class="user-name">Admin Program Studi</div>
            <div class="user-role">Unit Pelayanan Akademik</div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-header-row" style="align-items: flex-start;">
          <div class="page-title">
            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.25rem;">
              <h2>Dashboard Admin Program Studi (Admin Prodi)</h2>
              <span class="badge" style="background: #f0fdf4; color: #15803d; padding: 0.25rem 0.65rem; font-size: 0.75rem; font-weight: 700; border-radius: 9999px;">Pelayanan Akademik</span>
            </div>
            <p>Kelola kendala akademik mahasiswa: pengurusan KRS, sinkronisasi nilai, verifikasi presensi, dan surat izin perkuliahan.</p>
          </div>
          <div>
            <select id="statusFilterUnit" class="filter-select" style="font-weight: 600; background: #ffffff; border-color: var(--accent-blue);">
              <option value="all">Semua Status</option>
              <option value="Menunggu Validasi">Menunggu Validasi</option>
              <option value="Dalam Penanganan">Dalam Penanganan</option>
              <option value="Selesai">Selesai Ditangani</option>
            </select>
          </div>
        </div>

        <!-- Metric Cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-indicator orange"></div>
            <div>
              <div class="stat-number" id="unitStatValidasi">0</div>
              <div class="stat-label">Perlu Divalidasi</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-indicator blue"></div>
            <div>
              <div class="stat-number" id="unitStatProses">0</div>
              <div class="stat-label">Dalam Penanganan</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-indicator green"></div>
            <div>
              <div class="stat-number" id="unitStatSelesai">0</div>
              <div class="stat-label">Selesai Ditangani</div>
            </div>
          </div>
        </div>

        <!-- Search Toolbar -->
        <div class="toolbar-row">
          <div class="search-box">
            <svg class="icon search-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <input type="text" id="searchUnitReports" class="search-input" placeholder="Cari keluhan akademik berdasarkan nomor tiket, judul, atau nama mahasiswa...">
          </div>
        </div>

        <!-- Reports Table -->
        <div class="data-card">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>No. Tiket</th>
                  <th>Data Pelapor</th>
                  <th>Keluhan Layanan Akademik</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                  <th>Aksi Tindakan</th>
                </tr>
              </thead>
              <tbody id="unitReportsBody">
                <!-- Dynamically populated by app.js for unit: prodi -->
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </main>
  </div>

  <!-- Validation Modal (Approve / Reject) -->
  <div class="modal" id="validationModal">
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
      <div class="modal-header">
        <div>
          <h3>Verifikasi Laporan Akademik</h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Tiket #<span id="valTiket"></span></p>
        </div>
        <button class="modal-close-btn" onclick="closeModal('validationModal')">✕</button>
      </div>

      <table class="summary-table" style="margin-bottom: 1.5rem;">
        <tr>
          <td class="label">Mahasiswa</td>
          <td id="valPelapor"></td>
        </tr>
        <tr>
          <td class="label">Judul Kendala</td>
          <td id="valJudul"></td>
        </tr>
        <tr>
          <td class="label">Lokasi / Ruang</td>
          <td id="valLokasi"></td>
        </tr>
        <tr>
          <td class="label">Kronologi</td>
          <td id="valKronologi"></td>
        </tr>
      </table>

      <div style="background-color: #f8fafc; border: 1px solid var(--border-color); padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem;">
        <p style="font-size: 0.85rem; color: var(--text-main); font-weight: 600;">Keputusan Validasi Admin Prodi:</p>
        <p style="font-size: 0.8rem; color: var(--text-muted);">Jika disetujui, laporan masuk status <strong>Dalam Penanganan</strong> untuk diverifikasi bersama dosen pengampu atau bagian akademik.</p>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
        <button type="button" class="btn-primary" id="btnReject" style="background-color: #ef4444; width: auto; margin-top: 0;">✕ Tolak (Tidak Valid)</button>
        <button type="button" class="btn-primary" id="btnApprove" style="background-color: #10b981; width: auto; margin-top: 0;">✓ Setujui (Mulai Penanganan)</button>
      </div>
    </div>
  </div>

  <!-- Resolution Modal (Record action & mark Selesai) -->
  <div class="modal" id="resolutionModal">
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
      <div class="modal-header">
        <div>
          <h3>Penyelesaian Layanan Akademik</h3>
          <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">Tiket #<span id="resTiket"></span></p>
        </div>
        <button class="modal-close-btn" onclick="closeModal('resolutionModal')">✕</button>
      </div>

      <table class="summary-table" style="margin-bottom: 1.5rem;">
        <tr>
          <td class="label">Judul Kendala</td>
          <td id="resJudul"></td>
        </tr>
        <tr>
          <td class="label">Lokasi</td>
          <td id="resLokasi"></td>
        </tr>
      </table>

      <form id="resolutionForm">
        <div class="form-group">
          <label for="resTindakan">Keterangan Tindakan Akademik / Solusi <span style="color: #ef4444;">*</span></label>
          <textarea id="resTindakan" class="form-input" placeholder="Tuliskan tindakan koreksi nilai, perbaikan KRS, atau konfirmasi dosen..." required style="min-height: 100px;"></textarea>
        </div>

        <div class="form-group">
          <label>Unggah Berita Acara / Surat Bukti Selesai</label>
          <input type="file" class="form-input" accept="image/*,.pdf">
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
          <button type="button" class="btn-detail" onclick="closeModal('resolutionModal')">Batal</button>
          <button type="submit" class="btn-primary" style="background-color: #10b981; width: auto; margin-top: 0;">✓ Simpan & Tandai SELESAI</button>
        </div>
      </form>
    </div>
  </div>

  <script src="../../../assets/js/store.js"></script>
  <script src="../../../assets/js/ui.js"></script>
  <script src="../../../assets/js/app.js"></script>
  <script src="../../../assets/js/api.js"></script>
</body>
</html>
