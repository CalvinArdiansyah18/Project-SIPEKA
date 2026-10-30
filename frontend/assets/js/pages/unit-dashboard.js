/**
 * Dashboard Unit
 * Halaman : content/unit/{unit}/dashboard.php
 * Fungsi  : initUnitDashboard(), window.openValidationModal(), window.openResolutionModal()
 * Data    : initUnitDashboard() memakai data asli (backend/api/unit/dashboard.php).
 *           openValidationModal/openResolutionModal MASIH data dummy (dikerjakan di tahap form).
 * Catatan : openValidationModal/openResolutionModal juga bisa dipakai halaman unit lain.
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

// =======================================
// UNIT DASHBOARD & DISPOSITION CONTROLLER
// Data: GET backend/api/unit/dashboard.php (unit ditentukan server dari akun yang login)
// =======================================
async function initUnitDashboard() {
    const searchInput = document.getElementById('searchUnitReports');
    const statusFilter = document.getElementById('statusFilterUnit');
    const tbody = document.getElementById('unitReportsBody');
    if (!tbody) return;

    // Nilai dropdown di HTML -> parameter filter di backend
    const STATUS_PARAM = {
        'all': 'all',
        'Menunggu Validasi': 'menunggu',
        'Dalam Penanganan': 'proses',        // mencakup: diproses + dalam_penanganan
        'Selesai': 'selesai',
        'Tidak Valid': 'ditolak'
    };

    function setText(id, nilai) {
        const el = document.getElementById(id);
        if (el) el.textContent = nilai ?? 0;
    }

    // Tombol aksi menuju halaman kerja unit (halaman tersebut dikerjakan di tahap berikutnya)
    function tombolAksi(r) {
        const t = encodeURIComponent(r.no_tiket);
        if (r.status === 'menunggu') {
            return `<a class="btn-action" style="padding: 0.35rem 0.8rem; font-size: 0.75rem; display: inline-block; text-decoration: none;" href="verifikasi.php?tiket=${t}">Validasi</a>`;
        }
        if (r.status === 'diproses' || r.status === 'dalam_penanganan') {
            return `<a class="btn-detail" href="penanganan.php?tiket=${t}">Kelola Penanganan</a>`;
        }
        return `<a class="btn-detail" href="penanganan.php?tiket=${t}">Lihat</a>`;
    }

    let permintaanTerakhir = 0;   // mencegah respons lama menimpa respons yang lebih baru

    async function loadUnitReports() {
        const idPermintaan = ++permintaanTerakhir;
        const statusDipilih = statusFilter ? statusFilter.value : 'all';

        let data;
        try {
            data = await window.sipekaAPI.getDashboardUnit({
                status: STATUS_PARAM[statusDipilih] ?? statusDipilih,
                q: searchInput ? searchInput.value.trim() : ''
            });
        } catch (error) {
            if (idPermintaan !== permintaanTerakhir) return;
            console.error('[SIPEKA] Gagal memuat dashboard unit:', err);
            showToast(err.message || 'Gagal memuat data dashboard.', 'error');
            tbody.innerHTML = getEmptyStateHtml('Gagal Memuat Data', escapeHtml(err.message || 'Terjadi kesalahan pada server.'));
            return;
        }

        if (idPermintaan !== permintaanTerakhir) return;

        const stats = data.stats || {};
        const reports = data.laporan || [];

        setText('unitStatValidasi', stats.menunggu);
        setText('unitStatProses', stats.proses);
        setText('unitStatSelesai', stats.selesai);

        if (reports.length === 0) {
            tbody.innerHTML = getEmptyStateHtml('Tidak Ada Laporan', 'Tidak ada laporan yang sesuai dengan filter atau pencarian pada unit ini.');
            return;
        }

        // const reports = await window.sipekaAPI.getReports({
        //     unit: selectedUnit,
        //     status: statusVal,
        //     q: query
        // });

        // const stats = await window.sipekaAPI.getStats(selectedUnit);

        const mValidasi = document.getElementById('unitStatValidasi');
        const mProses = document.getElementById('unitStatProses');
        const mSelesai = document.getElementById('unitStatSelesai');

        if (mValidasi) mValidasi.textContent = stats.menunggu ?? 0;
        if (mProses) mProses.textContent = stats.proses ?? 0;
        if (mSelesai) mSelesai.textContent = stats.selesai ?? 0;

        if (!tbody) return;
        if (!reports || reports.length === 0) {
            tbody.innerHTML = getEmptyStateHtml('Tidak Ada Laporan Masuk', 'Tidak ada laporan pengaduan pada unit ini saat ini. Semua laporan telah selesai diproses atau belum ada keluhan baru.');
            return;
        }

        tbody.innerHTML = reports.map(r => {
            const pelaporNama = r.nama_pelapor || r.nama_mahasiswa || 'Mahasiswa JTI';
            const pelaporNim = r.nim_pelapor || r.nim || '254107020095';

            return `
        <tr style="${r.status === 'Menunggu Validasi' ? 'background-color: #fffbeb;' : ''}">
          <td><strong>${r.no_tiket}</strong></td>
          <td>
            <div style="font-weight: 600;">${pelaporNama}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">${pelaporNim}</div>
          </td>
          <td>${r.judul_laporan}</td>
          <td>${formatDate(r.tanggal_kejadian)}</td>
          <td>${getStatusBadge(r.status)}</td>
          <td>
            ${r.status === 'Menunggu Validasi' ?
                    `<button class="btn-action" style="padding: 0.35rem 0.8rem; font-size: 0.75rem;" onclick="openValidationModal('${r.no_tiket}')">Validasi</button>` :
                    `<button class="btn-detail" onclick="openResolutionModal('${r.no_tiket}')">Kelola Penanganan</button>`
                }
          </td>
        </tr>
      `;
        }).join('');
    }

    if (searchInput) searchInput.addEventListener('input', () => loadUnitReports());
    if (statusFilter) statusFilter.addEventListener('change', () => loadUnitReports());

    let timerCari;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(timerCari);
            timerCari = setTimeout(loadUnitReports, 350);
        });
    }
    if (statusFilter) statusFilter.addEventListener('change', () => loadUnitReports());
    await loadUnitReports();
}

// Modal Validasi (Unit Staff)
window.openValidationModal = async function (noTiket) {
    const report = await window.sipekaAPI.getReportDetail(noTiket);
    if (!report) return;

    const tEl = document.getElementById('valTiket');
    const pelaporEl = document.getElementById('valPelapor');
    const judulEl = document.getElementById('valJudul');
    const lokasiEl = document.getElementById('valLokasi');
    const kronEl = document.getElementById('valKronologi');

    if (tEl) tEl.textContent = report.no_tiket;
    if (pelaporEl) pelaporEl.textContent = `${report.nama_pelapor || report.nama_mahasiswa} (${report.nim_pelapor || report.nim})`;
    if (judulEl) judulEl.textContent = report.judul_laporan;
    if (lokasiEl) lokasiEl.textContent = report.lokasi;
    if (kronEl) kronEl.textContent = report.kronologi;

    const btnApprove = document.getElementById('btnApprove');
    if (btnApprove) {
        btnApprove.onclick = async () => {
            await window.sipekaAPI.validateReport(noTiket, 'approve', 'Staff Unit memvalidasi kebenaran laporan. Penugasan teknisi atau tindak lanjut telah dijadwalkan.');
            showToast(`Tiket #${noTiket} telah divalidasi dan masuk status Penanganan!`, 'success');
            closeModal('validationModal');
            initUnitDashboard();
        };
    }

    const btnReject = document.getElementById('btnReject');
    if (btnReject) {
        btnReject.onclick = async () => {
            const reason = prompt('Masukkan alasan penolakan laporan:');
            if (reason) {
                await window.sipekaAPI.validateReport(noTiket, 'reject', `Laporan ditolak oleh unit: ${reason}`);
                showToast(`Tiket #${noTiket} ditandai Tidak Valid.`, 'error');
                closeModal('validationModal');
                initUnitDashboard();
            }
        };
    }

    openModal('validationModal');
};

// Modal Penanganan (Unit Staff)
window.openResolutionModal = async function (noTiket) {
    const report = await window.sipekaAPI.getReportDetail(noTiket);
    if (!report) return;

    const resTiket = document.getElementById('resTiket');
    const resJudul = document.getElementById('resJudul');
    const resLokasi = document.getElementById('resLokasi');

    if (resTiket) resTiket.textContent = report.no_tiket;
    if (resJudul) resJudul.textContent = report.judul_laporan;
    if (resLokasi) resLokasi.textContent = report.lokasi;

    const form = document.getElementById('resolutionForm');
    if (form) {
        form.onsubmit = async (e) => {
            e.preventDefault();
            const tindakan = document.getElementById('resTindakan').value.trim();
            const fileProof = form.querySelector('input[type="file"]');
            const rawProof = fileProof && fileProof.files.length > 0 ? fileProof.files[0] : null;

            await window.sipekaAPI.resolveReport(noTiket, tindakan, rawProof);
            showToast(`Tiket #${noTiket} berhasil ditandai SELESAI!`, 'success');
            closeModal('resolutionModal');
            initUnitDashboard();
        };
    }

    openModal('resolutionModal');
};