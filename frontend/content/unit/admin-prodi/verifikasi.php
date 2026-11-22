<?php
$unitKey    = 'admin-prodi';
$pageTitle  = 'Verifikasi Kendala Akademik - Admin Prodi SIPEKA';
$breadcrumb = 'Portal Admin Prodi &rsaquo; <strong>Verifikasi Kendala Akademik</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">
        <div class="page-title">
          <h2>Verifikasi Bukti &amp; Permasalahan Akademik</h2>
          <p>Tinjau data pengaduan akademik mahasiswa sebelum melakukan klarifikasi dengan dosen atau staf prodi.</p>
        </div>

        <div class="detail-grid">
          <!-- Left: Report Details -->
          <div class="form-card form-card--full">
            <h3 class="card-title card-title--bordered">Informasi Kendala Akademik</h3>
            <table class="summary-table">
              <tr>
                <td class="label">Nomor</td>
                <td><strong id="verifTiket">KP-0011</strong></td>
              </tr>
              <tr>
                <td class="label">Data Mahasiswa</td>
                <td id="verifPelapor">Calvin Ardiansyah (NIM: 254107020214)</td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="verifKategori">Pelayanan Akademik</td>
              </tr>
              <tr>
                <td class="label">Judul Kendala</td>
                <td id="verifJudul">Koreksi Nilai Praktikum Basis Data</td>
              </tr>
              <tr>
                <td class="label">Lokasi / Ruang</td>
                <td id="verifLokasi">Ruang Dosen JTI</td>
              </tr>
              <tr>
                <td class="label">Kronologi Lengkap</td>
                <td id="verifKronologi">Nilai tugas praktikum 3 belum terinput di portal akademik padahal sudah dinilai
                  oleh asisten lab.</td>
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

            <h3 class="card-title">Keputusan Verifikasi Prodi</h3>

            <p class="card-note">
              Tentukan keabsahan permohonan. Laporan yang valid akan langsung diteruskan ke proses tindak lanjut
              akademik prodi.
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
