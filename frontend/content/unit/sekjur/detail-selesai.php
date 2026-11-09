<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Laporan Selesai - SIPEKA</title>
    <?php include __DIR__ . '/../../../includes/styles.php'; ?>
</head>

<body data-unit="sekjur">

    <div class="app-layout">

        <aside class="app-sidebar sidebar--unit">
            <div>
                <div class="sidebar-logo">
                    <div class="logo-badge"><img src="../../../assets/images/logo/Jti.webp" alt="Logo SIPEKA"
                            class="logo-JTI">
                    </div>
                    <div class="logo-text">
                        <h1>SIPEKA Sekjur</h1>
                        <p>Sekretariat Jurusan (Sarpras)</p>
                    </div>
                </div>

                <ul class="sidebar-menu">
                    <li class="active">
                        <a href="dashboard.php">
                            <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />
                            </svg>
                            <span>Daftar Laporan Masuk</span>
                        </a>
                    </li>
                    <li>
                        <a href="profil.php">
                            <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor"
                                stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <span>Profil Unit</span>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="sidebar-footer">
                <a href="../../auth/login.php" data-logout>
                    <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                        fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" x2="9" y1="12" y2="12" />
                    </svg>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>

        <main class="app-main">
            <header class="app-header">
                <div class="header-breadcrumb">
                    <span class="breadcrumb-text">Portal Sekjur &rsaquo; <strong>Detail laporan</strong></span>
                </div>
                <div class="user-profile user-profile--clickable" onclick="window.location.href='profil.php'">
                    <div class="avatar avatar--unit"> </div>
                    <div class="user-info">
                        <div class="user-name"> </div>
                        <div class="user-role"> </div>
                    </div>
                </div>
            </header>
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

        </main>

    </div>

    <?php include __DIR__ . '/../../../includes/scripts.php'; ?>

</body>

</html>