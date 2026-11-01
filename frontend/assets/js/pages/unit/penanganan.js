/**
 * Halaman Penanganan Unit
 * Halaman : content/unit/{unit}/penanganan.php
 * Fungsi  : initPenangananPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

async function initPenangananPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const pathUnit = getUnitFromPath();
    const reports = await window.sipekaAPI.getReports({ unit: pathUnit });
    let targetTiket = urlParams.get('tiket');

    if (!targetTiket && reports && reports.length > 0) {
        const activeReport = reports.find(r => r.status === 'Dalam Penanganan') || reports[0];
        targetTiket = activeReport.no_tiket;
    }

    if (!targetTiket) return;
    const report = await window.sipekaAPI.getReportDetail(targetTiket);
    if (!report) return;

    const tEl = document.getElementById('penangTiket');
    const judulEl = document.getElementById('penangJudul');
    const lokasiEl = document.getElementById('penangLokasi');
    const katEl = document.getElementById('penangKategori');

    if (tEl) tEl.textContent = report.no_tiket;
    if (judulEl) judulEl.textContent = report.judul_laporan;
    if (lokasiEl) lokasiEl.textContent = report.lokasi;
    if (katEl) katEl.textContent = report.nama_kategori || report.kategori || '-';

    const form = document.getElementById('pageResolutionForm');
    if (form) {
        form.onsubmit = async (e) => {
            e.preventDefault();
            const tindakanEl = document.getElementById('pageResTindakan');
            const tindakan = tindakanEl ? tindakanEl.value.trim() : 'Tindakan penanganan telah diselesaikan.';
            const fileProof = form.querySelector('input[type="file"]');
            const rawProof = fileProof && fileProof.files.length > 0 ? fileProof.files[0] : null;

            await window.sipekaAPI.resolveReport(report.no_tiket, tindakan, rawProof);
            showToast(`Tiket #${report.no_tiket} berhasil ditandai SELESAI!`, 'success');
            setTimeout(() => window.location.href = 'dashboard.php', 700);
        };
    }
}
