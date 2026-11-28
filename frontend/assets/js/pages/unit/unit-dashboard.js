/**
 * Dashboard Unit
 * Halaman : content/unit/{unit}/dashboard.php
 * Fungsi  : initUnitDashboard(), window.openValidationModal(), window.openResolutionModal()
 * Data    : initUnitDashboard() memakai data asli (backend/api/unit/dashboard.php).
 *           openValidationModal() memakai API unit/detail.php + unit/validasi.php.
 *           openResolutionModal() memakai API unit/selesai.php.
 * Catatan : openValidationModal/openResolutionModal juga bisa dipakai halaman unit lain.
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

// =======================================
// UNIT DASHBOARD & DISPOSITION CONTROLLER
// Data: GET backend/api/unit/dashboard.php (unit ditentukan server dari akun yang login)
// =======================================

// Dipakai modal validasi/penanganan untuk memuat ulang daftar tanpa memasang listener ganda.
let muatUlangDashboardUnit = null;

async function initUnitDashboard() {
    const searchInput = document.getElementById('searchUnitReports');
    const statusFilter = document.getElementById('statusFilterUnit');
    const tbody = document.getElementById('unitReportsBody');
    if (!tbody) return;

    // Tombol unduh rekap CSV (laporan unit 30 hari terakhir), di samping kotak pencarian.
    const btnUnduhRekap = document.getElementById('btnUnduhRekap');
    if (btnUnduhRekap && window.sipekaAPI) {
        btnUnduhRekap.href = window.sipekaAPI.getRekapUnitCsvUrl();
    }

    // Nilai dropdown di HTML -> parameter filter di backend
    const STATUS_PARAM = {
        'all': 'all',
        'Menunggu Validasi': 'menunggu',
        'Dalam Penanganan': 'dalam_penanganan', // mencakup: diproses + dalam_penanganan
        'Selesai': 'selesai',
        'Ditolak': 'ditolak'
    };

    function setText(id, nilai) {
        const el = document.getElementById(id);
        if (el) el.textContent = nilai ?? 0;
    }

    // Paginasi kronologis: halaman 1 = laporan terbaru.
    // Tombol "‹" (sebelumnya) -> laporan lebih lama (halaman + 1).
    // Tombol "›" (berikutnya) -> laporan lebih baru (halaman - 1).
    const PER_PAGE = 5;
    let halamanSekarang = 1;
    let totalHalaman = 1;
    const btnPrev = document.getElementById('unitPrevPage');
    const btnNext = document.getElementById('unitNextPage');
    const infoPaginasi = document.getElementById('unitPaginationInfo');

    function renderPaginasi(pagination) {
        totalHalaman = Math.max(1, Number(pagination && pagination.total_pages) || 1);
        halamanSekarang = Math.max(1, Math.min(totalHalaman, Number(pagination && pagination.page) || 1));
        const total = Number(pagination && pagination.total) || 0;

        if (infoPaginasi) {
            if (total === 0) {
                infoPaginasi.textContent = 'Tidak ada data';
            } else {
                const dari = (halamanSekarang - 1) * PER_PAGE + 1;
                const sampai = Math.min(total, halamanSekarang * PER_PAGE);
                infoPaginasi.textContent = `Menampilkan ${dari}–${sampai} dari ${total} laporan`;
            }
        }
        // "sebelumnya" aktif bila masih ada laporan yang lebih lama.
        if (btnPrev) btnPrev.disabled = halamanSekarang >= totalHalaman;
        // "berikutnya" aktif bila sedang tidak di halaman terbaru.
        if (btnNext) btnNext.disabled = halamanSekarang <= 1;
    }

    let permintaanTerakhir = 0; // mencegah respons lama menimpa respons yang lebih baru

    async function loadUnitReports(halamanBaru = null) {
        const idPermintaan = ++permintaanTerakhir;
        const statusDipilih = statusFilter ? statusFilter.value : 'all';

        if (halamanBaru !== null) halamanSekarang = Math.max(1, Number(halamanBaru) || 1);

        const params = {
            q: searchInput ? searchInput.value.trim() : '',
            page: halamanSekarang,
            per_page: PER_PAGE,
        };
        if (statusDipilih === 'Perlu Perhatian') {
            params.perhatian = 1;
        } else {
            params.status = STATUS_PARAM[statusDipilih] ?? statusDipilih;
        }

        let data;
        try {
            data = await window.sipekaAPI.getDashboardUnit(params);
        } catch (error) {
            if (idPermintaan !== permintaanTerakhir) return;
            console.error('[SIPEKA] Gagal memuat dashboard unit:', error);
            showToast(error.message || 'Gagal memuat data dashboard.', 'error');
            tbody.innerHTML = getEmptyStateHtml(
                'Gagal Memuat Data',
                escapeHtml(error.message || 'Terjadi kesalahan pada server.')
            );

            return;
        }

        if (idPermintaan !== permintaanTerakhir) return;

        const stats = data.stats || {};
        const reports = data.laporan || [];

        setText('unitStatValidasi', stats.menunggu);
        setText('unitStatProses', stats.dalam_penanganan);
        setText('unitStatSelesai', stats.selesai);
        setText('unitStatDitolak', stats.ditolak);
        setText('unitStatPerhatian', stats.perlu_perhatian);

        renderPaginasi(data.pagination);

        if (reports.length === 0) {
            tbody.innerHTML = getEmptyStateHtml(
                'Tidak Ada Laporan Masuk',
                'Tidak ada laporan yang sesuai dengan filter atau pencarian pada unit ini.'
            );
            return;
        }

        tbody.innerHTML = reports.map(r => {
            const pelaporNama = escapeHtml(r.nama_pelapor || '-');
            const pelaporNim = escapeHtml(r.nim || '-');
            const tiket = encodeURIComponent(r.no_tiket);
            const status = String(r.status || '').toLowerCase();

            let aksi = '';

            if (status === 'menunggu') {
                aksi = `<a class="btn-action" href="verifikasi.php?tiket=${tiket}">Validasi</a>`;
            } else if (status === 'dalam_penanganan') {
                aksi = `<a class="btn-action" href="penanganan.php?tiket=${tiket}">Penanganan</a>`;
            } else {
                aksi = `<a class="btn-action" href="detail-selesai.php?tiket=${tiket}">Lihat</a>`;
            }
            const slaBadge = getSlaBadge(r.sla_status, r.terlambat_jam);
            return `
        <tr class="${getSlaRowClass(r.perlu_perhatian)}">
            <td><strong>${escapeHtml(r.no_tiket)}</strong></td>
            <td>
                <div style="font-weight:600;">${pelaporNama}</div>
                <div style="font-size:.75rem;color:var(--text-muted);">
                    ${pelaporNim}
                </div>
            </td>
            <td>${escapeHtml(r.judul_laporan)}</td>
            <td>${escapeHtml(formatDate(r.tanggal_kejadian))}</td>
            <td>
                <div>${formatUmur(r.umur_jam)}</div>
                ${slaBadge}
            </td>
            <td>${getStatusBadge(r.status)}</td>
            <td>${aksi}</td>
        </tr>
    `;
        }).join('');
    }

    let timerCari;
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            clearTimeout(timerCari);
            timerCari = setTimeout(() => loadUnitReports(1), 350); // pencarian baru -> halaman 1
        });
    }
    if (statusFilter) statusFilter.addEventListener('change', () => loadUnitReports(1)); // filter baru -> halaman 1
    if (btnPrev) btnPrev.addEventListener('click', () => {
        // laporan sebelumnya = lebih lama -> halaman berikutnya
        if (halamanSekarang < totalHalaman) loadUnitReports(halamanSekarang + 1);
    });
    if (btnNext) btnNext.addEventListener('click', () => {
        // laporan terbaru = lebih baru -> halaman sebelumnya (menuju halaman 1)
        if (halamanSekarang > 1) loadUnitReports(halamanSekarang - 1);
    });
    muatUlangDashboardUnit = () => loadUnitReports(halamanSekarang);
    await loadUnitReports();
}

// Modal Validasi (Unit Staff)
window.openValidationModal = async function (noTiket) {
    let report;
    try {
        report = await window.sipekaAPI.getReportDetail(noTiket);
    } catch (error) {
        showToast(error.message || 'Gagal memuat detail laporan.', 'error');
        return;
    }
    if (!report) {
        showToast('Laporan tidak ditemukan pada unit Anda.', 'error');
        return;
    }

    const tEl = document.getElementById('valTiket');
    const pelaporEl = document.getElementById('valPelapor');
    const judulEl = document.getElementById('valJudul');
    const lokasiEl = document.getElementById('valLokasi');
    const kronEl = document.getElementById('valKronologi');

    if (tEl) tEl.textContent = report.no_tiket;
    if (pelaporEl) pelaporEl.textContent = `${report.nama_pelapor || '-'} (${report.nim || '-'})`;
    if (judulEl) judulEl.textContent = report.judul_laporan;
    if (lokasiEl) lokasiEl.textContent = report.lokasi || '-';
    if (kronEl) kronEl.textContent = report.kronologi || '-';

    const btnApprove = document.getElementById('btnApprove');
    if (btnApprove) {
        btnApprove.onclick = async () => {
            btnApprove.disabled = true;
            try {
                await window.sipekaAPI.validateReport(noTiket, 'approve', 'Staff Unit memvalidasi kebenaran laporan. Penugasan teknisi atau tindak lanjut telah dijadwalkan.');
                showToast(`Tiket #${noTiket} telah divalidasi dan masuk status Penanganan!`, 'success');
                closeModal('validationModal');
                if (muatUlangDashboardUnit) await muatUlangDashboardUnit();
            } catch (error) {
                showToast(error.message || 'Gagal memvalidasi laporan.', 'error');
            } finally {
                btnApprove.disabled = false;
            }
        };
    }

    const btnReject = document.getElementById('btnReject');
    if (btnReject) {
        btnReject.onclick = async () => {
            const reason = await sipekaPrompt({
                title: 'Tolak Laporan',
                message: `Tiket ${report.no_tiket} akan ditandai tidak valid. Alasan penolakan dicatat pada riwayat laporan.`,
                placeholder: 'Contoh: Bukti foto tidak sesuai dengan lokasi kejadian.',
                confirmText: 'Tolak Laporan',
                danger: true,
            });
            if (!reason) return;
            btnReject.disabled = true;
            try {
                await window.sipekaAPI.validateReport(noTiket, 'reject', `Laporan ditolak oleh unit: ${reason}`);
                showToast(`Tiket #${noTiket} ditandai Tidak Valid.`, 'error');
                closeModal('validationModal');
                if (muatUlangDashboardUnit) await muatUlangDashboardUnit();
            } catch (error) {
                showToast(error.message || 'Gagal menolak laporan.', 'error');
            } finally {
                btnReject.disabled = false;
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

            try {
                await window.sipekaAPI.resolveReport(noTiket, tindakan, rawProof);
                showToast(`Tiket ${noTiket} berhasil ditandai SELESAI!`, 'success');
                closeModal('resolutionModal');
                if (muatUlangDashboardUnit) await muatUlangDashboardUnit();
            } catch (error) {
                showToast(error.message || 'Gagal menyelesaikan laporan.', 'error');
            }
        };
    }

    openModal('resolutionModal');
};