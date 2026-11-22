<?php
$unitKey    = 'sekjur';
$pageTitle  = 'Verifikasi Laporan Sarpras - Sekjur SIPEKA';
$breadcrumb = 'Portal Sekjur &rsaquo; <strong>Verifikasi Laporan Masuk</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>

      <div class="content-container">

        <div class="page-title">
          <h2>Verifikasi Bukti &amp; Keabsahan Laporan Sarpras</h2>
          <p>Tinjau kelengkapan laporan mahasiswa sebelum menugaskan teknisi sarpras untuk perbaikan.</p>
        </div>

        <!-- Grid Verifikasi -->
        <div class="detail-grid">

          <!-- KIRI: Informasi Pengaduan dan Bukti Pelapor -->
          <div class="form-card form-card--full">

            <h3 class="card-title card-title--bordered">Informasi Pengaduan</h3>

            <table class="summary-table">
              <tr>
                <td class="label">Nomor</td>
                <td><strong id="verifTiket">KP-0013</strong></td>
              </tr>
              <tr>
                <td class="label">Data Pelapor</td>
                <td id="verifPelapor">Galih Maula Mumtaza (NIM: 254107020095)</td>
              </tr>
              <tr>
                <td class="label">Kategori</td>
                <td id="verifKategori">Fasilitas Kampus</td>
              </tr>
              <tr>
                <td class="label">Judul Kerusakan</td>
                <td id="verifJudul">Kerusakan Proyektor &amp; Stop Kontak Lab 3</td>
              </tr>
              <tr>
                <td class="label">Lokasi</td>
                <td id="verifLokasi">Gedung Sipil Lt. 7 - Lab Komputer 3</td>
              </tr>
              <tr>
                <td class="label">Kronologi Lengkap</td>
                <td id="verifKronologi">Proyektor mati mendadak saat sesi praktikum dan stop kontak di baris tengah
                  mengeluarkan percikan kecil.</td>
              </tr>
            </table>

            <!-- Bukti Pelapor -->
            <div class="bukti-section">
              <h3 class="card-title">Bukti Pelapor</h3>

              <div id="verifBukti" class="bukti-container">
                <p>Memuat bukti pelapor...</p>
              </div>
            </div>

          </div>
          <!-- PENUTUP KOLOM KIRI -->


          <!-- KANAN: Keputusan Verifikasi -->
          <div class="form-card form-card--full">

            <h3 class="card-title">Keputusan Verifikasi Sekjur</h3>

            <p class="card-note">
              Tentukan keabsahan laporan. Laporan yang valid akan langsung diteruskan ke teknisi sarpras.
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
          <!-- PENUTUP KOLOM KANAN -->

        </div>
        <!-- PENUTUP DETAIL GRID -->

      </div>

<?php include __DIR__ . '/../../../includes/unit/layout-end.php'; ?>
