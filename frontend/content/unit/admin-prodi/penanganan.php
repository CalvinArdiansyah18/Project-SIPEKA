<?php
$unitKey    = 'admin-prodi';
$pageTitle  = 'Penyelesaian Kendala Akademik - Admin Prodi SIPEKA';
$breadcrumb = 'Portal Admin Prodi &rsaquo; <strong>Tindakan Penyelesaian Akademik</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-title">
          <h2>Catat Progres Penanganan Layanan Akademik</h2>
          <p>Dokumentasikan perbaikan nilai, pengesahan KRS, atau surat izin dan selesaikan tiket pengaduan.</p>
        </div>

        <div class="detail-grid detail-grid--even">
          <!-- Left: Report Summary -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Ringkasan Kendala Akademik</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor Tiket</td>
                <td><strong id="penangTiket">KP-0011</strong></td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="penangKategori">Pelayanan Akademik</td>
              </tr>
              <tr>
                <td class="label">Judul Kendala</td>
                <td id="penangJudul">Koreksi Nilai Praktikum Basis Data</td>
              </tr>
              <tr>
                <td class="label">Ruang / Bagian</td>
                <td id="penangLokasi">Ruang Dosen JTI</td>
              </tr>
            </table>
          </div>

          <!-- Right: Resolution Form -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Form Berita Acara Penyelesaian</h3>
            <form id="pageResolutionForm">
              <div class="form-group">
                <label for="pageResTindakan">Keterangan Tindakan Akademik / Solusi <span
                    class="required-mark">*</span></label>
                <textarea id="pageResTindakan" class="form-input"
                  placeholder="Tuliskan konfirmasi yang telah dilakukan bersama dosen pengampu, pembaruan di KHS, dsb..."
                  required></textarea>
              </div>

              <div class="form-group">
                <label>Surat Bukti / Screenshot Portal yang Sudah Diperbarui</label>
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
