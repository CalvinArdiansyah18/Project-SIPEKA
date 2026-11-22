<?php
$unitKey    = 'sekjur';
$pageTitle  = 'Pencatatan Penanganan Sarpras - Sekjur SIPEKA';
$breadcrumb = 'Portal Sekjur &rsaquo; <strong>Tindakan Penanganan Sarpras</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-title">
          <h2>Catat Progres Penanganan &amp; Penyelesaian Sarpras</h2>
          <p>Dokumentasikan tindakan perbaikan teknisi fasilitas dan selesaikan tiket pengaduan.</p>
        </div>

        <div class="detail-grid detail-grid--even">
          <!-- Left: Report Summary -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Ringkasan Tiket Sarpras</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor Tiket</td>
                <td><strong id="penangTiket">KP-0012</strong></td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="penangKategori">Fasilitas Kampus</td>
              </tr>
              <tr>
                <td class="label">Judul Masalah</td>
                <td id="penangJudul">Kerusakan AC Gedung Sipil Lt. 4</td>
              </tr>
              <tr>
                <td class="label">Lokasi Ruangan</td>
                <td id="penangLokasi">Gedung Sipil Lt. 4 Ruang 4.02</td>
              </tr>
            </table>
          </div>

          <!-- Right: Resolution Form -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Form Berita Acara Penyelesaian</h3>
            <form id="pageResolutionForm">
              <div class="form-group">
                <label for="pageResTindakan">Keterangan Tindakan Perbaikan <span class="required-mark">*</span></label>
                <textarea id="pageResTindakan" class="form-input"
                  placeholder="Tuliskan komponen yang diganti, hasil uji coba perbaikan, atau teknisi yang menangani..."
                  required></textarea>
              </div>

              <div class="form-group">
                <label>Foto Hasil Perbaikan / Berita Acara</label>
                <input type="file" class="form-input" accept="image/*,.pdf">
              </div>

              <div class="modal-actions mt-lg">
                <a href="dashboard.php" class="btn-detail">Batal</a>
                <button type="submit" class="btn-primary btn-success btn-inline">✓ Simpan &amp; Tandai SELESAI</button>
              </div>
            </form>
          </div>
        </div>

      </div>

<?php include __DIR__ . '/../../../includes/unit/layout-end.php'; ?>
