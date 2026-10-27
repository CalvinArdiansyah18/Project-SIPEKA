/**
 * Riwayat & Pelacakan Laporan
 * Halaman : content/mahasiswa/riwayat.php
 * Fungsi  : initRiwayatPage(), window.viewReportDetail()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

// ==========================
// RIWAYAT & TRACKING PAGE
// ==========================
async function initRiwayatPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const targetTiket = urlParams.get('tiket');
    if (urlParams.get('success') === 'true') {
        showToast('Laporan berhasil dikirim dan masuk dalam status antrean validasi!', 'success');
    }

    const searchInput = document.getElementById('searchReport');
    const statusFilter = document.getElementById('statusFilter');
    const tbody = document.getElementById('riwayatTableBody');

    async function loadAndRender() {
        const query = searchInput ? searchInput.value.trim() : '';
        const statusVal = statusFilter ? statusFilter.value : 'all';

        const reports = await window.sipekaAPI.getReports({
            q: query,
            status: statusVal
        });

        if (!tbody) return;
        if (!reports || reports.length === 0) {
            tbody.innerHTML = getEmptyStateHtml('Tidak Ada Laporan Ditemukan', 'Tidak ada data laporan yang cocok dengan kata kunci atau filter status yang dipilih.');
            return;
        }

        tbody.innerHTML = reports.map(r => {
            const unitName = (r.nama_unit || r.unit_tujuan || 'Sekjur').split('(')[0];
            return `
        <tr>
          <td><strong>${r.no_tiket}</strong></td>
          <td>${r.nama_kategori || r.kategori}</td>
          <td><span style="font-size: 0.8rem; font-weight: 600; color: #1e3a8a;">${unitName}</span></td>
          <td>${r.judul_laporan}</td>
          <td>${formatDate(r.tanggal_kejadian)}</td>
          <td>${getStatusBadge(r.status)}</td>
          <td>
            <button class="btn-detail" onclick="viewReportDetail('${r.no_tiket}')">Lacak Linimasa</button>
          </td>
        </tr>
      `;
        }).join('');
    }

    if (searchInput) searchInput.addEventListener('input', () => loadAndRender());
    if (statusFilter) statusFilter.addEventListener('change', () => loadAndRender());

    await loadAndRender();

    if (targetTiket) {
        setTimeout(() => viewReportDetail(targetTiket), 300);
    }
}

// View Timeline Tracking Modal
window.viewReportDetail = async function (noTiket) {
    const report = await window.sipekaAPI.getReportDetail(noTiket);
    if (!report) {
        showToast('Detail laporan tidak dapat dimuat.', 'error');
        return;
    }

    const modal = document.getElementById('trackingModal');
    if (!modal) return;

    document.getElementById('modalTiket').textContent = `Tiket #${report.no_tiket}`;
    document.getElementById('modalJudul').textContent = report.judul_laporan;
    document.getElementById('modalKategori').textContent = report.nama_kategori || report.kategori;
    document.getElementById('modalUnit').textContent = report.nama_unit || report.unit_tujuan;
    document.getElementById('modalLokasi').textContent = report.lokasi;
    document.getElementById('modalKronologi').textContent = report.kronologi;
    document.getElementById('modalStatusBadge').innerHTML = getStatusBadge(report.status);

    // Render Timeline
    const timelineContainer = document.getElementById('modalTimeline');
    if (timelineContainer && report.riwayat) {
        timelineContainer.innerHTML = report.riwayat.map((step, idx) => {
            const isDone = idx < report.riwayat.length - 1 || report.status === 'Selesai';
            return `
        <div class="timeline-item">
          <div class="timeline-dot ${isDone ? 'done' : ''}"></div>
          <div class="timeline-content">
            <h5>${step.status || step.status_text}</h5>
            <div class="time">${step.waktu || formatDate(step.tanggal_perubahan)}</div>
            <p>${step.keterangan}</p>
          </div>
        </div>
      `;
        }).join('');
    }

    openModal('trackingModal');
};
