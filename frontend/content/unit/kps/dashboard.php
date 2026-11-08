<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard KPS - SIPEKA Polinema</title>
  <?php include __DIR__ . '/../../../includes/styles.php'; ?>
</head>

<body data-unit="kps">
  <!-- Mobile Topbar -->
  <div class="mobile-topbar topbar--unit">
    <div class="topbar-brand">
      <div class="logo-badge logo-badge--sm"><img src="../../../assets/images/logo/Jti.webp" alt="Logo SIPEKA"
          class="logo-JTI"></div>
      <span class="topbar-title">SIPEKA KPS</span>
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
    <aside class="app-sidebar sidebar--unit">
      <div>
        <div class="sidebar-logo">
          <div class="logo-badge"><img src="../../../assets/images/logo/Jti.webp" alt="Logo SIPEKA" class="logo-JTI">
          </div>
          <div class="logo-text">
            <h1>SIPEKA KPS</h1>
            <p>Ketua Program Studi JTI</p>
          </div>
        </div>

        <ul class="sidebar-menu">
          <li class="active">
            <a href="dashboard.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1 2-2h2" />
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
              </svg>
              <span>Daftar Laporan Masuk</span>
            </a>
          </li>
          <li>
            <a href="profil.php">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
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

    <main class="app-main">
      <header class="app-header">
        <div class="header-breadcrumb">
          <span class="breadcrumb-text">Portal Penanganan Unit Resmi &rsaquo; <strong>Ketua Program Studi
              (KPS)</strong></span>
        </div>
        <div class="user-profile user-profile--clickable" onclick="window.location.href='profil.php'">
          <div class="avatar avatar--unit"> </div>
          <div class="user-info">
            <div class="user-name"> </div>
            <div class="user-role"> </div>
          </div>
        </div>
      </header>

      <div class="content-container">
        <div class="page-header-row page-header-row--top">
          <div class="page-title">
            <div class="page-title-row">
              <h2>Dashboard Ketua Program Studi (KPS)</h2>
              <span class="badge badge-soft">Tata Tertib Mahasiswa</span>
            </div>
            <p>Pengawasan ketertiban perkuliahan, mediasi perselisihan mahasiswa, kecurangan akademik, dan penegakan
              kode etik mahasiswa JTI Polinema.</p>
          </div>
          <div>
            <select id="statusFilterUnit" class="filter-select filter-select--accent">
              <option value="all">Semua Status</option>
              <option value="Menunggu Validasi">Menunggu Validasi</option>
              <option value="Dalam Penanganan">Dalam Penanganan</option>
              <option value="Selesai">Selesai</option>
              <option value="Ditolak">Ditolak</option>
            </select>
          </div>
        </div>

        <!-- Metric Cards -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-indicator orange"></div>
            <div>
              <div class="stat-number" id="unitStatValidasi">0</div>
              <div class="stat-label">Menunggu</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-indicator blue"></div>
            <div>
              <div class="stat-number" id="unitStatProses">0</div>
              <div class="stat-label">Diproses</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-indicator green"></div>
            <div>
              <div class="stat-number" id="unitStatSelesai">0</div>
              <div class="stat-label">Selesai</div>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-indicator red"></div>
            <div>
              <div class="stat-number" id="unitStatDitolak">0</div>
              <div class="stat-label">Ditolak</div>
            </div>
          </div>
        </div>

        <!-- Search Toolbar -->
        <div class="toolbar-row">
          <div class="search-box">
            <svg class="icon search-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor"
              stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8" />
              <path d="m21 21-4.3-4.3" />
            </svg>
            <input type="text" id="searchUnitReports" class="search-input"
              placeholder="Cari laporan ketertiban berdasarkan nomor tiket, judul, atau nama mahasiswa...">
          </div>
        </div>

        <!-- Reports Table -->
        <div class="data-card">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Data Pelapor</th>
                  <th>Pengaduan Tata Tertib</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                  <th>Aksi Tindakan</th>
                </tr>
              </thead>
              <tbody id="unitReportsBody">
                <!-- Dynamically populated by app.js for unit: kps -->
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
          <h3>Verifikasi Laporan Tata Tertib</h3>
          <p class="modal-subtitle">Tiket #<span id="valTiket"></span></p>
        </div>
        <button class="modal-close-btn" onclick="closeModal('validationModal')">✕</button>
      </div>

      <table class="summary-table summary-table--tight">
        <tr>
          <td class="label">Pelapor</td>
          <td id="valPelapor"></td>
        </tr>
        <tr>
          <td class="label">Pelanggaran</td>
          <td id="valJudul"></td>
        </tr>
        <tr>
          <td class="label">Lokasi</td>
          <td id="valLokasi"></td>
        </tr>
        <tr>
          <td class="label">Kronologi Lengkap</td>
          <td id="valKronologi"></td>
        </tr>
      </table>

      <div class="info-box">
        <p class="info-box__title">Keputusan Validasi KPS:</p>
        <p class="info-box__text">Laporan valid akan masuk status <strong>Dalam Penanganan</strong> untuk mediasi atau
          pembinaan tata tertib mahasiswa bersangkutan.</p>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-primary btn-danger btn-inline" id="btnReject">✕ Tolak (Tidak Valid)</button>
        <button type="button" class="btn-primary btn-success btn-inline" id="btnApprove">✓ Setujui (Mulai
          Penanganan)</button>
      </div>
    </div>
  </div>

  <!-- Resolution Modal (Record action & mark Selesai) -->
  <div class="modal" id="resolutionModal">
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
      <div class="modal-header">
        <div>
          <h3>Pencatatan Mediasi &amp; Penyelesaian KPS</h3>
          <p class="modal-subtitle">Tiket #<span id="resTiket"></span></p>
        </div>
        <button class="modal-close-btn" onclick="closeModal('resolutionModal')">✕</button>
      </div>

      <table class="summary-table summary-table--tight">
        <tr>
          <td class="label">Kasus</td>
          <td id="resJudul"></td>
        </tr>
        <tr>
          <td class="label">Lokasi</td>
          <td id="resLokasi"></td>
        </tr>
      </table>

      <form id="resolutionForm">
        <div class="form-group">
          <label for="resTindakan">Keterangan Tindakan Pembinaan / Mediasi <span class="required-mark">*</span></label>
          <textarea id="resTindakan" class="form-input"
            placeholder="Tuliskan hasil mediasi antar mahasiswa, sanksi akademik, atau surat pernyataan..."
            required></textarea>
        </div>

        <div class="form-group">
          <label>Unggah Surat Pernyataan / Berita Acara Mediasi</label>
          <input type="file" class="form-input" accept="image/*,.pdf">
        </div>

        <div class="modal-actions mt-lg">
          <button type="button" class="btn-detail" onclick="closeModal('resolutionModal')">Batal</button>
          <button type="submit" class="btn-primary btn-success btn-inline">✓ Simpan & Tandai SELESAI</button>
        </div>
      </form>
    </div>
  </div>

  <?php include __DIR__ . '/../../../includes/scripts.php'; ?>
</body>

</html>