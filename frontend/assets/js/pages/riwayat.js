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
    const params = new URLSearchParams(window.location.search);
    const targetTiket = params.get('tiket');

    const searchInput = document.getElementById('searchReport');
    const statusFilter = document.getElementById('statusFilter');
    const tbody = document.getElementById('riwayatTableBody');

    if (params.get('success') === 'true') {
        showToast(
            'Laporan berhasil dikirim!',
            'success'
        );
    }

    async function loadAndRender() {
        if (!tbody) return;

        tbody.innerHTML = `
            <tr>
                <td colspan="7">Memuat data laporan...</td>
            </tr>
        `;

        try {
            const response =
                await window.sipekaAPI.getRiwayatLaporan({
                    q: searchInput?.value.trim() || '',
                    status: statusFilter?.value || 'all'
                });

            // API mengembalikan { laporan: [...] }
            const reports = response.laporan || [];

            if (reports.length === 0) {
                tbody.innerHTML = getEmptyStateHtml(
                    'Belum Ada Pengaduan',
                    'Tidak ada laporan yang sesuai.'
                );
                return;
            }

            tbody.innerHTML = reports.map(r => `
                <tr>
                    <td>
                        <strong>${escapeHtml(r.no_tiket || '-')}</strong>
                    </td>
                    <td>${escapeHtml(r.nama_kategori || '-')}</td>
                    <td>
                        <span style="font-size:0.8rem;font-weight:600">
                            ${escapeHtml(r.nama_unit || '-')}
                        </span>
                    </td>
                    <td>${escapeHtml(r.judul_laporan || '-')}</td>
                    <td>${formatDate(r.tanggal_kejadian)}</td>
                    <td>${getStatusBadge(r.status)}</td>
                    <td>
                        <button type="button" class="btn-detail" data-tiket="${escapeHtml(r.no_tiket || '')}">Lacak Linimasa<button>
                    </td>
                </tr>
            `).join('');

        } catch (err) {
            console.error(
                '[SIPEKA] Gagal memuat riwayat:',
                err
            );

            tbody.innerHTML = getEmptyStateHtml(
                'Gagal Memuat Riwayat',
                escapeHtml(err.message || 'Terjadi kesalahan server.')
            );
        }
    }

    // Event delegation untuk tombol detail
    if (tbody) {
        tbody.addEventListener('click', event => {
            const button = event.target.closest('[data-tiket]');
            if (!button) return;

            viewReportDetail(button.dataset.tiket);
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', loadAndRender);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', loadAndRender);
    }

    await loadAndRender();

    // Jika halaman dibuka dari tombol Detail dashboard
    if (targetTiket) {
        await viewReportDetail(targetTiket);
    }
}

// Ambil detail laporan langsung dari database
window.viewReportDetail = async function (noTiket) {
    try {
        const response =
            await window.sipekaAPI.getDetailLaporan(noTiket);

        // API detail mengembalikan objek dengan properti laporan
        const report = response.laporan;

        if (!report) {
            throw new Error('Data laporan tidak ditemukan.');
        }

        const modal = document.getElementById('trackingModal');
        if (!modal) {
            throw new Error('Elemen modal detail tidak ditemukan.');
        }

        document.getElementById('modalTiket').textContent =
            `Tiket ${report.no_tiket}`;

        document.getElementById('modalJudul').textContent =
            report.judul_laporan || '-';

        document.getElementById('modalKategori').textContent =
            report.nama_kategori || '-';

        document.getElementById('modalUnit').textContent =
            report.nama_unit || '-';

        document.getElementById('modalLokasi').textContent =
            report.lokasi || '-';

        document.getElementById('modalKronologi').textContent =
            report.kronologi || '-';

        document.getElementById('modalTanggal').textContent =
            report.tanggal_kejadian || '-';

        // Linimasa status dari tabel riwayat_status
        const timeline = document.getElementById('modalTimeline');
        const history = response.riwayat || [];

        if (timeline) {
            timeline.innerHTML = history.length
                ? history.map((item, index) => `
                    <div class="timeline-item">
                        <div class="timeline-dot ${index < history.length - 1 ? 'done' : ''
                    }"></div>
                        <div class="timeline-content">
                            <h5>${escapeHtml(item.status || '-')}</h5>
                            <div class="time">
                                ${formatDate(item.tanggal_perubahan)}
                            </div>
                            <p>${escapeHtml(item.keterangan || '-')}</p>
                        </div>
                    </div>
                `).join('')
                : '<p>Belum ada riwayat perubahan status.</p>';
        }

        // Tanggapan dan penanganan unit
        const notes = document.getElementById('modalCatatan');
        const catatan = response.catatan_unit || [];

        if (notes) {
            notes.innerHTML = catatan.length
                ? catatan.map(item => `
                    <div class="timeline-content" style="margin-bottom:12px">
                        <strong>${escapeHtml(item.jenis || '-')}</strong>
                        <p>${escapeHtml(item.isi || '-')}</p>
                        <small>
                            ${escapeHtml(item.petugas || '-')}
                            · ${formatDate(item.waktu)}
                        </small>
                    </div>
                `).join('')
                : '<p>Belum ada tanggapan atau penanganan dari unit.</p>';
        }

        openModal('trackingModal');

    } catch (err) {
        console.error(
            '[SIPEKA] Gagal memuat detail laporan:',
            err
        );

        showToast(
            err.message || 'Detail laporan tidak dapat dimuat.',
            'error'
        );
    }
};