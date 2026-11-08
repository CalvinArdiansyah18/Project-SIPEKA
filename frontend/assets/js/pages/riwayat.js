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
                        <button type="button" class="btn-detail" data-tiket="${escapeHtml(r.no_tiket || '')}">Lacak Linimasa</button>
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
                            <h5>${escapeHtml(getStatusLabel(item.status))}</h5>
                            <div class="time">
                                ${escapeHtml(formatDateTime(item.tanggal_perubahan))}
                            </div>
                        </div>
                    </div>
                `).join('')
                : '<p>Belum ada riwayat perubahan status.</p>';
        }

        // Hasil penanganan: status akhir, catatan + bukti penanganan, atau alasan penolakan
        renderHasilPenanganan(
            document.getElementById('modalHasil'),
            report,
            history,
            response.penanganan || []
        );

        // Tanggapan dari unit (penanganan sudah ditampilkan di blok "Hasil Penanganan")
        const notes = document.getElementById('modalCatatan');
        const tanggapan = (response.catatan_unit || []).filter(item => item.jenis === 'Tanggapan');

        if (notes) {
            notes.innerHTML = tanggapan.length
                ? tanggapan.map(item => `
                    <div class="tanggapan-item">
                        <p>${escapeHtml(item.isi || '-')}</p>
                        <small>
                            ${escapeHtml(item.petugas || '-')}
                            · ${escapeHtml(formatDateTime(item.waktu))}
                        </small>
                    </div>
                `).join('')
                : '<p class="bukti-kosong">Belum ada tanggapan dari unit.</p>';
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

// Mengisi blok "Hasil Penanganan" pada modal pelacakan.
//   wadah      : elemen #modalHasil
//   report     : data laporan (status terkini)
//   riwayat    : linimasa status dari riwayat_status
//   penanganan : catatan penanganan unit beserta bukti-nya
function renderHasilPenanganan(wadah, report, riwayat, penanganan) {
    if (!wadah) return;
    wadah.innerHTML = '';

    const status = report.status;
    const riwayatAkhir = [...riwayat].reverse().find(r => r.status === status) || null;

    // Ringkasan status
    const ringkasan = document.createElement('div');
    ringkasan.className = 'hasil-ringkasan';
    const pesan = {
        menunggu: 'Laporan Anda menunggu validasi dari unit penanggung jawab.',
        diproses: 'Laporan sudah divalidasi dan masuk antrean penanganan.',
        dalam_penanganan: 'Laporan sedang ditangani oleh unit penanggung jawab.',
        selesai: 'Penanganan laporan Anda telah selesai.',
        ditolak: 'Laporan Anda ditolak oleh unit penanggung jawab.'
    }[status] || '';
    ringkasan.innerHTML = `<span>${escapeHtml(pesan)}</span>`;
    wadah.appendChild(ringkasan);

    // Laporan ditolak: tampilkan alasan dari riwayat status
    if (status === 'ditolak') {
        const alasan = document.createElement('div');
        alasan.className = 'hasil-item';
        alasan.innerHTML = `
            <strong>Alasan Penolakan</strong>
            <p>${escapeHtml((riwayatAkhir && riwayatAkhir.keterangan) || '-')}</p>
            <small>${escapeHtml((riwayatAkhir && riwayatAkhir.diubah_oleh) || '-')}
                · ${escapeHtml(formatDateTime(riwayatAkhir && riwayatAkhir.tanggal_perubahan))}</small>
        `;
        wadah.appendChild(alasan);
        return;
    }

    if (penanganan.length === 0) {
        const kosong = document.createElement('p');
        kosong.className = 'bukti-kosong';
        kosong.textContent = status === 'selesai'
            ? 'Unit tidak mencatat rincian penanganan.'
            : 'Hasil penanganan akan tampil di sini setelah unit mencatatnya.';
        wadah.appendChild(kosong);
        return;
    }

    // Satu kartu per catatan penanganan: keterangan, petugas, waktu, dan bukti
    penanganan.forEach((p) => {
        const item = document.createElement('div');
        item.className = 'hasil-item';

        const judul = document.createElement('strong');
        judul.textContent = status === 'selesai' ? 'Tindakan Penanganan' : 'Catatan Penanganan';
        const isi = document.createElement('p');
        isi.textContent = p.keterangan || '-';
        const meta = document.createElement('small');
        meta.textContent = `${p.petugas || '-'} · ${formatDateTime(p.tanggal)}`;
        item.append(judul, isi, meta);

        const bukti = document.createElement('div');
        bukti.className = 'bukti-preview';
        const labelBukti = document.createElement('strong');
        labelBukti.textContent = 'Bukti Penanganan';
        const daftarBukti = document.createElement('div');
        bukti.append(labelBukti, daftarBukti);
        item.appendChild(bukti);
        renderDaftarBukti(daftarBukti, p.bukti, 'penanganan', 'Petugas tidak melampirkan bukti penanganan.');

        wadah.appendChild(item);
    });
}