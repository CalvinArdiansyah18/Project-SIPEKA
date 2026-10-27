/**
 * Dashboard Mahasiswa
 * Halaman : content/mahasiswa/dashboard.php
 * Fungsi  : initMahasiswaDashboard()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

// ===================
// MAHASISWA DASHBOARD
// ===================
async function initMahasiswaDashboard() {
    const stats = await window.sipekaAPI.getStats();
    const reports = await window.sipekaAPI.getReports();

    // Render Metric Counters
    const totalEl = document.getElementById('statTotal');
    const prosesEl = document.getElementById('statProses');
    const selesaiEl = document.getElementById('statSelesai');
    const ditolakEl = document.getElementById('statDitolak');

    if (totalEl) totalEl.textContent = stats.total ?? 0;
    if (prosesEl) prosesEl.textContent = (stats.proses ?? 0) + (stats.menunggu ?? 0);
    if (selesaiEl) selesaiEl.textContent = stats.selesai ?? 0;
    if (ditolakEl) ditolakEl.textContent = stats.ditolak ?? 0;

    // Render Recent Table (Max 5 items)
    const tbody = document.getElementById('recentReportsBody');
    if (!tbody) return;

    if (!reports || reports.length === 0) {
        tbody.innerHTML = getEmptyStateHtml('Belum Ada Pengaduan', 'Anda belum pernah mengajukan pengaduan. Klik tombol "Buat Pengaduan" di atas untuk memulai laporan baru.');
        return;
    }

    tbody.innerHTML = reports.slice(0, 5).map(r => `
    <tr>
      <td><strong>${r.no_tiket}</strong></td>
      <td>${r.kategori || r.nama_kategori}</td>
      <td>${r.judul_laporan}</td>
      <td>${formatDate(r.tanggal_kejadian)}</td>
      <td>${getStatusBadge(r.status)}</td>
      <td>
        <a href="riwayat.php?tiket=${r.no_tiket}" class="btn-detail">Detail</a>
      </td>
    </tr>
  `).join('');
}