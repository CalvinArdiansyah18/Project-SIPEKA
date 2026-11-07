/**
 * Halaman Detail Laporan Selesai / Ditolak (tombol "Lihat" di dashboard unit)
 * Halaman : content/unit/{unit}/detail-selesai.php?tiket=SPK-2026-00012
 * Fungsi  : initDetailSelesaiPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 *
 * Data dari database lewat GET backend/api/unit/detail.php. Status yang ditampilkan hanya 2:
 *   - selesai : detail pelapor + keterangan & bukti penanganan
 *   - ditolak : detail pelapor + alasan penolakan (dari riwayat status)
 * Laporan yang belum final dialihkan ke halaman verifikasi / penanganan.
 */

async function initDetailSelesaiPage() {
    const loading = document.getElementById('loadingDetail');
    const grid = document.getElementById('detailSelesaiGrid');
    const targetTiket = new URLSearchParams(window.location.search).get('tiket');

    const setText = (id, nilai) => {
        const el = document.getElementById(id);
        if (el) el.textContent = nilai;
    };
    const tampilkanPesan = (pesan) => {
        if (loading) {
            loading.hidden = false;
            loading.textContent = pesan;
        }
    };

    if (!targetTiket) {
        tampilkanPesan('Nomor tiket tidak ditemukan. Buka halaman ini lewat tombol "Lihat" di dashboard.');
        return;
    }

    let report;
    try {
        report = await window.sipekaAPI.getReportDetail(targetTiket);
    } catch (error) {
        console.error('[SIPEKA] Gagal memuat detail laporan:', error);
        tampilkanPesan(error.message || 'Gagal memuat data laporan.');
        showToast(error.message || 'Gagal memuat data laporan.', 'error');
        return;
    }
    if (!report) {
        tampilkanPesan('Laporan tidak ditemukan pada unit Anda.');
        return;
    }

    // Halaman ini khusus laporan final (selesai / ditolak).
    const tiketUrl = encodeURIComponent(report.no_tiket);
    if (report.status === 'menunggu') {
        window.location.replace(`verifikasi.php?tiket=${tiketUrl}`);
        return;
    }
    if (report.status !== 'selesai' && report.status !== 'ditolak') {
        window.location.replace(`penanganan.php?tiket=${tiketUrl}`);
        return;
    }
    const selesai = report.status === 'selesai';

    // ---------- kartu kiri: detail pelapor ----------
    setText('detailTiket', report.no_tiket);
    setText('detailPelapor', report.nama_pelapor || '-');
    setText('detailNim', report.nim || '-');
    setText('detailKategori', report.nama_kategori || '-');
    setText('detailJudul', report.judul_laporan || '-');
    setText('detailLokasi', report.lokasi || '-');
    setText('detailKronologi', report.kronologi || '-');
    renderDaftarBukti(
        document.getElementById('buktiPelapor'),
        report.bukti,
        'laporan',
        'Pelapor tidak melampirkan bukti.'
    );

    // ---------- kartu kanan: hasil akhir (selesai / ditolak) ----------
    const riwayat = report.riwayat || [];
    const riwayatAkhir = [...riwayat].reverse().find((r) => r.status === report.status) || null;
    const penanganan = report.penanganan || [];
    const penangananAkhir = penanganan.length > 0 ? penanganan[penanganan.length - 1] : null;

    const statusEl = document.getElementById('detailStatus');
    if (statusEl) {
        statusEl.textContent = selesai ? 'SELESAI' : 'DITOLAK';
        statusEl.style.color = selesai ? 'green' : '#dc2626';
    }

    if (selesai) {
        setText('judulKartuKanan', 'Detail Penanganan');
        setText('labelPetugas', 'Petugas');
        setText('labelTanggal', 'Tanggal Selesai');
        setText('judulKeterangan', 'Keterangan Penanganan');
        setText('judulBuktiKanan', 'Bukti Penanganan');

        setText('detailPetugas', (penangananAkhir && penangananAkhir.petugas) || (riwayatAkhir && riwayatAkhir.diubah_oleh) || '-');
        setText('detailTanggal', formatDateTime((riwayatAkhir && riwayatAkhir.tanggal_perubahan) || (penangananAkhir && penangananAkhir.tanggal) || report.updated_at));
        setText('detailKeterangan', (penangananAkhir && penangananAkhir.keterangan) || (riwayatAkhir && riwayatAkhir.keterangan) || '-');

        // Gabungkan bukti dari semua catatan penanganan laporan ini
        const semuaBukti = penanganan.flatMap((p) => p.bukti || []);
        renderDaftarBukti(
            document.getElementById('buktiPenanganan'),
            semuaBukti,
            'penanganan',
            'Petugas tidak melampirkan bukti penanganan.'
        );
    } else {
        setText('judulKartuKanan', 'Detail Penolakan');
        setText('labelPetugas', 'Ditolak oleh');
        setText('labelTanggal', 'Tanggal Ditolak');
        setText('judulKeterangan', 'Alasan Penolakan');

        setText('detailPetugas', (riwayatAkhir && riwayatAkhir.diubah_oleh) || '-');
        setText('detailTanggal', formatDateTime((riwayatAkhir && riwayatAkhir.tanggal_perubahan) || report.updated_at));
        setText('detailKeterangan', (riwayatAkhir && riwayatAkhir.keterangan) || '-');

        // Laporan ditolak tidak punya bukti penanganan: sembunyikan blok tersebut
        const blokBukti = document.getElementById('blokBuktiPenanganan');
        if (blokBukti) blokBukti.hidden = true;
    }

    if (loading) loading.hidden = true;
    if (grid) grid.hidden = false;
}