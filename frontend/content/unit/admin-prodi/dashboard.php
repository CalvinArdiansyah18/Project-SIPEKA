<?php
$unitKey    = 'admin-prodi';
$pageTitle  = 'Dashboard Admin Prodi - SIPEKA Polinema';
$breadcrumb = 'Portal Penanganan Unit Resmi &rsaquo; <strong>Admin Program Studi</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-header-row page-header-row--top">
          <div class="page-title">
            <div class="page-title-row">
              <h2>Dashboard Admin Program Studi (Admin Prodi)</h2>
              <span class="badge badge-soft">Pelayanan Akademik</span>
            </div>
            <p>Kelola kendala akademik mahasiswa: pengurusan KRS, sinkronisasi nilai, verifikasi presensi, dan surat
              izin perkuliahan.</p>
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
              placeholder="Cari keluhan akademik berdasarkan nomor tiket, judul, atau nama mahasiswa...">
          </div>

          <a href="#" id="btnUnduhRekap" class="btn-primary btn-inline"
            style="display:inline-flex; align-items:center; gap:0.4rem; text-decoration:none; white-space:nowrap;">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            Unduh Rekap (30 Hari)
          </a>
        </div>

        <!-- Reports Table -->
        <div class="data-card">
          <div class="table-responsive">
            <table class="custom-table">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Data Pelapor</th>
                  <th>Keluhan Layanan Akademik</th>
                  <th>Tanggal</th>
                  <th>Umur</th>
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

        <!-- Pagination -->
        <div class="pagination-row" id="unitPagination">
          <span class="pagination-info" id="unitPaginationInfo"></span>
          <div class="pagination-actions">
            <button type="button" class="btn-detail btn-pagination" id="unitPrevPage" aria-label="Laporan sebelumnya" title="Laporan sebelumnya" disabled>&lsaquo;</button>
            <button type="button" class="btn-detail btn-pagination" id="unitNextPage" aria-label="Laporan berikutnya" title="Laporan berikutnya" disabled>&rsaquo;</button>
          </div>
        </div>

      </div>

  <!-- Validation Modal (Approve / Reject) -->
  <div class="modal" id="validationModal">
    <div class="modal-backdrop"></div>
    <div class="modal-dialog">
      <div class="modal-header">
        <div>
          <h3>Verifikasi Laporan Akademik</h3>
          <p class="modal-subtitle">Tiket #<span id="valTiket"></span></p>
        </div>
        <button class="modal-close-btn" onclick="closeModal('validationModal')">✕</button>
      </div>

      <table class="summary-table summary-table--tight">
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

      <div class="info-box">
        <p class="info-box__title">Keputusan Validasi Admin Prodi:</p>
        <p class="info-box__text">Jika disetujui, laporan masuk status <strong>Dalam Penanganan</strong> untuk
          diverifikasi bersama dosen pengampu atau bagian akademik.</p>
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
          <h3>Penyelesaian Layanan Akademik</h3>
          <p class="modal-subtitle">Tiket #<span id="resTiket"></span></p>
        </div>
        <button class="modal-close-btn" onclick="closeModal('resolutionModal')">✕</button>
      </div>

      <table class="summary-table summary-table--tight">
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
          <label for="resTindakan">Keterangan Tindakan Akademik / Solusi <span class="required-mark">*</span></label>
          <textarea id="resTindakan" class="form-input"
            placeholder="Tuliskan tindakan koreksi nilai, perbaikan KRS, atau konfirmasi dosen..." required></textarea>
        </div>

        <div class="form-group">
          <label>Unggah Berita Acara / Surat Bukti Selesai</label>
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
