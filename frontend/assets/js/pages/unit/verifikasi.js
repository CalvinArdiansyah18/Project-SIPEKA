/**
 * Halaman Verifikasi Unit
 * Halaman : content/unit/{unit}/verifikasi.php
 * Fungsi  : initVerifikasiPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 *
 * Data diambil dari backend (bukan localStorage):
 *   GET  backend/api/unit/detail.php?tiket=...
 *   POST backend/api/unit/validasi.php  { tiket, action, keterangan }
 */

// =======================================
// DEDICATED VERIFIKASI & PENANGANAN PAGES
// =======================================
async function initVerifikasiPage() {
    const urlParams = new URLSearchParams(window.location.search);
    let targetTiket = urlParams.get('tiket');

    try {
        // Tanpa ?tiket= : ambil laporan pertama yang masih menunggu validasi di unit ini.
        if (!targetTiket) {
            const data = await window.sipekaAPI.getDashboardUnit({ status: 'menunggu' });
            const reports = data.laporan || [];
            if (reports.length > 0) targetTiket = reports[0].no_tiket;
        }

        if (!targetTiket) {
            showToast('Tidak ada laporan yang menunggu validasi pada unit ini.', 'info');
            return;
        }

        const report = await window.sipekaAPI.getReportDetail(targetTiket);
        if (!report) {
            showToast('Laporan tidak ditemukan pada unit Anda.', 'error');
            return;
        }

        const tEl = document.getElementById('verifTiket');
        const pelaporEl = document.getElementById('verifPelapor');
        const katEl = document.getElementById('verifKategori');
        const judulEl = document.getElementById('verifJudul');
        const lokasiEl = document.getElementById('verifLokasi');
        const kronEl = document.getElementById('verifKronologi');

        if (tEl) tEl.textContent = report.no_tiket;
        if (pelaporEl) pelaporEl.textContent = `${report.nama_pelapor || 'Mahasiswa JTI'} (NIM: ${report.nim || '-'})`;
        if (katEl) katEl.textContent = report.nama_kategori || '-';
        if (judulEl) judulEl.textContent = report.judul_laporan;
        if (lokasiEl) lokasiEl.textContent = report.lokasi || '-';
        if (kronEl) kronEl.textContent = report.kronologi || '-';

        if (report.status !== 'menunggu') {
            showToast(`Laporan ini sudah berstatus "${getStatusLabel(report.status)}".`, 'info');
        }

        const btnApprove = document.getElementById('btnApprovePage');
        const btnReject = document.getElementById('btnRejectPage');

        if (btnApprove) {
            btnApprove.onclick = async (e) => {
                e.preventDefault();
                btnApprove.disabled = true;
                try {
                    await window.sipekaAPI.validateReport(
                        report.no_tiket,
                        'approve',
                        'Laporan telah divalidasi oleh petugas unit.'
                    );
                    showToast(`Laporan ${report.no_tiket} berhasil disetujui!`, 'success');
                    setTimeout(() => window.location.href = 'dashboard.php', 700);
                } catch (error) {
                    btnApprove.disabled = false;
                    showToast(error.message || 'Gagal memvalidasi laporan.', 'error');
                }
            };
        }

        if (btnReject) {
            btnReject.onclick = async (e) => {
                e.preventDefault();
                const reason = prompt('Masukkan alasan penolakan laporan:');
                if (!reason) return;
                btnReject.disabled = true;
                try {
                    await window.sipekaAPI.validateReport(
                        report.no_tiket,
                        'reject',
                        `Ditolak unit: ${reason}`
                    );
                    showToast(`Laporan ${report.no_tiket} ditandai tidak valid.`, 'error');
                    setTimeout(() => window.location.href = 'dashboard.php', 700);
                } catch (error) {
                    btnReject.disabled = false;
                    showToast(error.message || 'Gagal menolak laporan.', 'error');
                }
            };
        }
    } catch (error) {
        console.error('[SIPEKA] Gagal memuat halaman verifikasi:', error);
        showToast(error.message || 'Gagal memuat data laporan.', 'error');
    }
}