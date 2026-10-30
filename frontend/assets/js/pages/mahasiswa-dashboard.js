/**
 * Dashboard Mahasiswa
 * Halaman : content/mahasiswa/dashboard.php
 * Fungsi  : initMahasiswaDashboard()
 * Data    : GET backend/api/mahasiswa/dashboard.php (data asli dari database)
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

// ===================
// MAHASISWA DASHBOARD
// ===================
async function initMahasiswaDashboard() {
  const totalEl = document.getElementById('statTotal');
  const prosesEl = document.getElementById('statProses');
  const selesaiEl = document.getElementById('statSelesai');
  const ditolakEl = document.getElementById('statDitolak');
  const tbody = document.getElementById('recentReportsBody');

  let data;
  try {
    data = await window.sipekaAPI.getDashboardMahasiswa(5);
  } catch (err) {
    console.error('[SIPEKA] Gagal memuat dashboard mahasiswa:', err);
    showToast(err.message || 'Gagal memuat data dashboard.', 'error');
    if (tbody) tbody.innerHTML = getEmptyStateHtml('Gagal Memuat Data', escapeHtml(err.message || 'Terjadi kesalahan pada server.'));
    return;
  }

  const stats = data.stats || {};
  const reports = data.laporan || [];

  // Kartu ringkasan. "Sedang Diproses" = menunggu validasi + diproses + dalam penanganan
  if (totalEl) totalEl.textContent = stats.total ?? 0;
  if (prosesEl) prosesEl.textContent = (stats.proses ?? 0) + (stats.menunggu ?? 0);
  if (selesaiEl) selesaiEl.textContent = stats.selesai ?? 0;
  if (ditolakEl) ditolakEl.textContent = stats.ditolak ?? 0;

  // Tabel pengaduan terbaru (maks. 5)
  if (!tbody) return;

  if (reports.length === 0) {
    tbody.innerHTML = getEmptyStateHtml('Belum Ada Pengaduan', 'Anda belum pernah mengajukan pengaduan. Klik tombol "Buat Pengaduan" di atas untuk memulai laporan baru.');
    return;
  }

  tbody.innerHTML = reports.map(r => `
    <tr>
      <td><strong>${escapeHtml(r.no_tiket)}</strong></td>
      <td>${escapeHtml(r.nama_kategori)}</td>
      <td>${escapeHtml(r.judul_laporan)}</td>
      <td>${formatDate(r.tanggal_kejadian)}</td>
      <td>${getStatusBadge(r.status)}</td>
      <td>
        <a href="riwayat.php?tiket=${encodeURIComponent(r.no_tiket)}" class="btn-detail">Detail</a>
      </td>
    </tr>
  `).join('');
}