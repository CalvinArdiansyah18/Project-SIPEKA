<?php
$unitKey    = 'kajur';
$pageTitle  = 'Verifikasi Laporan Etika/Pungli - Kajur SIPEKA';
$breadcrumb = 'Portal Kajur &rsaquo; <strong>Verifikasi Pengaduan Masuk</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-title">
          <h2>Verifikasi Bukti Pengaduan Kode Etik / Pungli</h2>
          <p>Tinjau kronologi dan berkas pengaduan secara rahasia dan objektif sebelum memulai pemanggilan.</p>
        </div>

        <div class="detail-grid">
          <!-- Left: Report Details -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Informasi Kasus</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor</td>
                <td><strong id="verifTiket">KP-0016</strong></td>
              </tr>
              <tr>
                <td class="label">Data Pelapor</td>
                <td id="verifPelapor">Rizka Nurhaliza (NIM: 254107020045)</td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="verifKategori">Pungutan Liar (Pungli)</td>
              </tr>
              <tr>
                <td class="label">Judul Kasus</td>
                <td id="verifJudul">Pungutan Biaya Modul Praktikum Tak Resmi</td>
              </tr>
              <tr>
                <td class="label">Lokasi Kejadian</td>
                <td id="verifLokasi">Ruang Kelas 4.05</td>
              </tr>
              <tr>
                <td class="label">Kronologi Lengkap</td>
                <td id="verifKronologi">Penarikan biaya cetak modul tanpa kwitansi resmi dari pihak jurusan di luar
                  ketentuan UKT.</td>
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

            <h3 class="card-title">Keputusan Verifikasi Kajur</h3>

            <p class="card-note">
              Tentukan keabsahan pengaduan. Jika valid, pimpinan akan menerbitkan surat panggilan klarifikasi resmi.
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
