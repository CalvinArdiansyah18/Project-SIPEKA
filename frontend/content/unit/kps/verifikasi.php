<?php
$unitKey    = 'kps';
$pageTitle  = 'Verifikasi Tata Tertib Mahasiswa - KPS SIPEKA';
$breadcrumb = 'Portal KPS &rsaquo; <strong>Verifikasi Laporan Tata Tertib</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-title">
          <h2>Verifikasi Bukti Pelanggaran Tata Tertib</h2>
          <p>Tinjau data pengaduan ketertiban mahasiswa sebelum melakukan mediasi atau pembinaan.</p>
        </div>

        <div class="detail-grid">
          <!-- Left: Report Details -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Informasi Pelanggaran</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor</td>
                <td><strong id="verifTiket">KP-0015</strong></td>
              </tr>
              <tr>
                <td class="label">Data Pelapor</td>
                <td id="verifPelapor">Naufal Rizky (NIM: 254107020112)</td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="verifKategori">Pelanggaran Mahasiswa</td>
              </tr>
              <tr>
                <td class="label">Judul Pelanggaran</td>
                <td id="verifJudul">Indikasi Vandalisme di Fasilitas Lab Komputer</td>
              </tr>
              <tr>
                <td class="label">Lokasi Kejadian</td>
                <td id="verifLokasi">Gedung Sipil Lt. 6 - Lab Jaringan</td>
              </tr>
              <tr>
                <td class="label">Kronologi Lengkap</td>
                <td id="verifKronologi">Terdapat coretan cat semprot dan kerusakan pada keyboard komputer nomor 12 di
                  Lab Jaringan.</td>
              </tr>
            </table>

            <div class="bukti-section">
              <h3 class="card-title">Bukti Pelapor</h3>

              <div id="verifBukti" class="bukti-container">
                <p>Memuat bukti pelapor...</p>
              </div>
            </div>

          </div>


          <!-- Right: Action Card -->
          <div class="form-card form-card--full">

            <h3 class="card-title">Keputusan Verifikasi KPS</h3>

            <p class="card-note">
              Tentukan keabsahan laporan. Laporan yang valid akan langsung diteruskan ke proses pemanggilan mediasi dan
              pembinaan mahasiswa.
            </p>

            <div class="action-stack">
              <button type="button" id="btnApprovePage" class="btn-primary btn-success">
                ✓ Setujui (Laporan Valid)
              </button>

              <button type="button" id="btnRejectPage" class="btn-primary btn-danger">
                ✕ Tolak (Tidak Valid)
              </button>

              <a href="dashboard.php" class="btn-detail mt-sm">
                Kembali ke Dashboard
              </a>
            </div>

          </div>
        </div>

      </div>

<?php include __DIR__ . '/../../../includes/unit/layout-end.php'; ?>
