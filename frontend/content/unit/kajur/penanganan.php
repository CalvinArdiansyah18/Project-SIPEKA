<?php
$unitKey    = 'kajur';
$pageTitle  = 'Tindak Lanjut & Penyelesaian - Kajur SIPEKA';
$breadcrumb = 'Portal Kajur &rsaquo; <strong>Tindakan Penyelesaian Etika</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-title">
          <h2>Catat Progres Penanganan &amp; Keputusan Pimpinan</h2>
          <p>Dokumentasikan hasil pemanggilan, kesepakatan kuliah pengganti, atau berita acara pimpinan.</p>
        </div>

        <div class="detail-grid detail-grid--even">
          <!-- Left: Report Summary -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Ringkasan Kasus Etika</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor Tiket</td>
                <td><strong id="penangTiket">KP-0014</strong></td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="penangKategori">Perilaku Dosen atau Staf</td>
              </tr>
              <tr>
                <td class="label">Judul Kasus</td>
                <td id="penangJudul">Transparansi Nilai &amp; Kehadiran Perkuliahan</td>
              </tr>
              <tr>
                <td class="label">Lokasi Ruang</td>
                <td id="penangLokasi">Gedung Sipil Lt. 7 Ruang Teori 7.03</td>
              </tr>
            </table>
          </div>

          <!-- Right: Resolution Form -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Form Berita Acara Penyelesaian</h3>
            <form id="pageResolutionForm">
              <div class="form-group">
                <label for="pageResTindakan">Keterangan Keputusan / Penyelesaian Pimpinan <span
                    class="required-mark">*</span></label>
                <textarea id="pageResTindakan" class="form-input"
                  placeholder="Tuliskan keputusan rapat pimpinan jurusan, teguran, jadwal kuliah pengganti, dll..."
                  required></textarea>
              </div>

              <div class="form-group">
                <label>Surat Berita Acara / Nota Kesepakatan Resmi</label>
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
