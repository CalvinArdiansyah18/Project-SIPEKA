/**
 * Halaman Verifikasi Unit
 * Halaman : content/unit/{unit}/verifikasi.php
 * Fungsi  : initVerifikasiPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

// =======================================
// DEDICATED VERIFIKASI & PENANGANAN PAGES
// =======================================
async function initVerifikasiPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const pathUnit = getUnitFromPath();
    const reports = await window.sipekaAPI.getReports({ unit: pathUnit, status: 'Menunggu Validasi' });
    let targetTiket = urlParams.get('tiket');

    if (!targetTiket && reports && reports.length > 0) {
        targetTiket = reports[0].no_tiket;
    }

    if (!targetTiket) return;
    const report = await window.sipekaAPI.getReportDetail(targetTiket);
    if (!report) return;

    const tEl = document.getElementById('verifTiket');
    const pelaporEl = document.getElementById('verifPelapor');
    const katEl = document.getElementById('verifKategori');
    const judulEl = document.getElementById('verifJudul');
    const lokasiEl = document.getElementById('verifLokasi');
    const kronEl = document.getElementById('verifKronologi');

    if (tEl) tEl.textContent = report.no_tiket;
    if (pelaporEl) pelaporEl.textContent = `${report.nama_pelapor || report.nama_mahasiswa || 'Mahasiswa JTI'} (NIM: ${report.nim_pelapor || report.nim || '-'})`;
    if (katEl) katEl.textContent = report.nama_kategori || report.kategori || '-';
    if (judulEl) judulEl.textContent = report.judul_laporan;
    if (lokasiEl) lokasiEl.textContent = report.lokasi;
    if (kronEl) kronEl.textContent = report.kronologi;

    const btnApprove = document.getElementById('btnApprovePage');
    const btnReject = document.getElementById('btnRejectPage');

    if (btnApprove) {
        btnApprove.onclick = async (e) => {
            e.preventDefault();
            await window.sipekaAPI.validateReport(report.no_tiket, 'approve', 'Laporan telah divalidasi oleh petugas unit.');
            showToast(`Laporan ${report.no_tiket} berhasil disetujui!`, 'success');
            setTimeout(() => window.location.href = 'penanganan.php?tiket=' + report.no_tiket, 700);
        };
    }

    if (btnReject) {
        btnReject.onclick = async (e) => {
            e.preventDefault();
            const reason = prompt('Masukkan alasan penolakan laporan:');
            if (reason) {
                await window.sipekaAPI.validateReport(report.no_tiket, 'reject', `Ditolak unit: ${reason}`);
                showToast(`Laporan ${report.no_tiket} ditandai tidak valid.`, 'error');
                setTimeout(() => window.location.href = 'dashboard.php', 700);
            }
        };
    }
}