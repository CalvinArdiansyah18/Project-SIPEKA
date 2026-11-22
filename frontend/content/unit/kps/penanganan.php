<?php
$unitKey    = 'kps';
$pageTitle  = 'Penyelesaian Tata Tertib - KPS SIPEKA';
$breadcrumb = 'Portal KPS &rsaquo; <strong>Tindakan Mediasi &amp; Tata Tertib</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-title">
          <h2>Catat Progres Mediasi &amp; Penyelesaian Kasus</h2>
          <p>Dokumentasikan hasil mediasi, sanksi peringatan, atau surat pernyataan komitmen tata tertib mahasiswa.</p>
        </div>

        <div class="detail-grid detail-grid--even">
          <!-- Left: Report Summary -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Ringkasan Pelanggaran</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor Tiket</td>
                <td><strong id="penangTiket">KP-0015</strong></td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="penangKategori">Pelanggaran Mahasiswa</td>
              </tr>
              <tr>
                <td class="label">Judul Kasus</td>
                <td id="penangJudul">Indikasi Vandalisme di Fasilitas Lab Komputer</td>
              </tr>
              <tr>
                <td class="label">Lokasi Kejadian</td>
                <td id="penangLokasi">Gedung Sipil Lt. 6 - Lab Jaringan</td>
              </tr>
            </table>
          </div>

          <!-- Right: Resolution Form -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Form Berita Acara Penyelesaian</h3>
            <form id="pageResolutionForm">
              <div class="form-group">
                <label for="pageResTindakan">Keterangan Tindakan Pembinaan / Mediasi <span
                    class="required-mark">*</span></label>
                <textarea id="pageResTindakan" class="form-input"
                  placeholder="Tuliskan hasil mediasi antar mahasiswa, surat peringatan, komitmen ganti rugi, dll..."
                  required></textarea>
              </div>

              <div class="form-group">
                <label>Surat Berita Acara / Pernyataan Bermaterai</label>
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
