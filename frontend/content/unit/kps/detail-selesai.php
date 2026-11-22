<?php
$unitKey    = 'kps';
$pageTitle  = 'Detail Laporan Selesai - SIPEKA';
$breadcrumb = 'Portal KPS &rsaquo; <strong>Detail Laporan</strong>';
$activeMenu = 'dashboard';
include __DIR__ . '/../../../includes/unit/layout-start.php';
?>
            <div class="detail-selesai">
                <div class="page-title">
                    <h2>Detail Laporan Selesai</h2>
                    <p>Informasi pelapor dan hasil penanganan laporan.</p>
                </div>

                <div id="loadingDetail">
                    Memuat data laporan...
                </div>

                <div id="detailSelesaiGrid" class="detail-selesai-grid" hidden>
                    <!-- KARTU KIRI -->
                    <div class="detail-selesai-card">
                        <h3>Detail Pelapor</h3>
                        <table class="detail-selesai-table">
                            <tr>
                                <td>Nomor Tiket</td>
                                <td id="detailTiket">-</td>
                            </tr>
                            <tr>
                                <td>Nama Pelapor</td>
                                <td id="detailPelapor">-</td>
                            </tr>
                            <tr>
                                <td>NIM</td>
                                <td id="detailNim">-</td>
                            </tr>
                            <tr>
                                <td>Kategori</td>
                                <td id="detailKategori">-</td>
                            </tr>
                            <tr>
                                <td>Judul</td>
                                <td id="detailJudul">-</td>
                            </tr>
                            <tr>
                                <td>Lokasi</td>
                                <td id="detailLokasi">-</td>
                            </tr>
                            <tr>
                                <td>Kronologi</td>
                                <td id="detailKronologi">-</td>
                            </tr>
                        </table>
                        <div class="bukti-preview">
                            <h3>Bukti Pelapor</h3>
                            <div id="buktiPelapor"></div>
                        </div>
                    </div>


                    <!-- KARTU KANAN: hasil akhir (status hanya SELESAI atau DITOLAK, diisi dari database) -->
                    <div class="detail-selesai-card">
                        <h3 id="judulKartuKanan">Detail Penanganan</h3>
                        <table class="detail-selesai-table">
                            <tr>
                                <td>Status</td>
                                <td><strong id="detailStatus">-</strong></td>
                            </tr>
                            <tr>
                                <td id="labelPetugas">Petugas</td>
                                <td id="detailPetugas">-</td>
                            </tr>
                            <tr>
                                <td id="labelTanggal">Tanggal</td>
                                <td id="detailTanggal">-</td>
                            </tr>
                        </table>

                        <div class="bukti-preview">
                            <h3 id="judulKeterangan">Keterangan Penanganan</h3>
                            <p id="detailKeterangan">-</p>
                        </div>

                        <div class="bukti-preview" id="blokBuktiPenanganan">
                            <h3 id="judulBuktiKanan">Bukti Penanganan</h3>
                            <div id="buktiPenanganan"></div>
                        </div>
                    </div>
                </div>
                <br>

                <a href="dashboard.php" class="btn-detail">
                    ← Kembali ke Dashboard
                </a>

            </div>

<?php include __DIR__ . '/../../../includes/unit/layout-end.php'; ?>
