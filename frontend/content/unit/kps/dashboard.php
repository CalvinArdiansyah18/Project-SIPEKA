<?php
$unitKey    = 'kps';
$pageTitle  = 'Dashboard KPS - SIPEKA Polinema';
$breadcrumb = 'Portal Penanganan Unit Resmi &rsaquo; <strong>Ketua Program Studi (KPS)</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

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
              <option value="Perlu Perhatian">Perlu Perhatian</option>
            </select>
          </div>
        </div>

        <!-- Metric Cards -->
        <div class="stats-grid stats-grid--unit">
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
              <div class="stat-label">Dalam Penanganan</div>
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

          <div class="stat-card">
            <div class="stat-indicator orange"></div>
            <div>
              <div class="stat-number" id="unitStatPerhatian">0</div>
              <div class="stat-label">Perlu Perhatian</div>
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
                  <th>Umur</th>
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

<?php include __DIR__ . '/../../../includes/unit/layout-end.php'; ?>
