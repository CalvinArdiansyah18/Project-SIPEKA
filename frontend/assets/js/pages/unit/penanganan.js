/**
 * Halaman Penanganan Unit
 * Halaman : content/unit/{unit}/penanganan.php
 * Fungsi  : initPenangananPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
  *
 * Data diambil & disimpan ke backend (bukan localStorage):
 *   GET  backend/api/unit/dashboard.php   (mencari laporan yang sedang ditangani)
 *   GET  backend/api/unit/detail.php?tiket=...
 *   POST backend/api/unit/selesai.php     (multipart: tiket, keterangan, bukti)
 *        -> mengubah status laporan menjadi 'selesai' di database
 */

const PENANGANAN_MAKS_BYTE = 5 * 1024 * 1024; // sama dengan batas di backend (5 MB)
const PENANGANAN_JENIS_BOLEH = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

async function initPenangananPage() {
    const urlParams = new URLSearchParams(window.location.search);
    let targetTiket = urlParams.get('tiket');

    try {
        // Tanpa ?tiket= : ambil laporan pertama yang masih aktif (diproses / dalam penanganan).
        if (!targetTiket) {
            const data = await window.sipekaAPI.getDashboardUnit({ status: 'proses' });
            const reports = data.laporan || [];
            if (reports.length > 0) targetTiket = reports[0].no_tiket;
        }

        if (!targetTiket) {
            showToast('Tidak ada laporan yang sedang dalam penanganan pada unit ini.', 'info');
            return;
        }

        const report = await window.sipekaAPI.getReportDetail(targetTiket);
        if (!report) {
            showToast('Laporan tidak ditemukan pada unit Anda.', 'error');
            return;
        }

        // Halaman ini hanya untuk laporan yang sedang aktif. Selain itu, arahkan ke halaman yang tepat.
        const tiketUrl = encodeURIComponent(report.no_tiket);
        if (report.status === 'selesai' || report.status === 'ditolak') {
            window.location.replace(`detail-selesai.php?tiket=${tiketUrl}`);
            return;
        }
        if (report.status === 'menunggu') {
            showToast('Laporan ini belum divalidasi. Mengalihkan ke halaman validasi...', 'info');
            setTimeout(() => window.location.replace(`verifikasi.php?tiket=${tiketUrl}`), 900);
            return;
        }

        const setText = (id, nilai) => {
            const el = document.getElementById(id);
            if (el) el.textContent = nilai;
        };
        setText('penangTiket', report.no_tiket);
        setText('penangJudul', report.judul_laporan);
        setText('penangLokasi', report.lokasi || '-');
        setText('penangKategori', report.nama_kategori || '-');
        
        // Kartu tanggapan untuk pelapor (disimpan lewat backend/api/unit/tanggapan.php)
        initTanggapanUnit(report);

        const form = document.getElementById('pageResolutionForm');
        if (!form) return;

        const tombol = form.querySelector('button[type="submit"]');
        const tekstombol = tombol ? tombol.textContent : '';

        form.onsubmit = async (e) => {
            e.preventDefault();

            const tindakanEl = document.getElementById('pageResTindakan');
            const tindakan = tindakanEl ? tindakanEl.value.trim() : '';
            if (tindakan === '') {
                showToast('Keterangan tindakan perbaikan wajib diisi.', 'error');
                if (tindakanEl) tindakanEl.focus();
                return;
            }

            const fileInput = form.querySelector('input[type="file"]');
            const berkas = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;
            if (berkas) {
                if (berkas.size > PENANGANAN_MAKS_BYTE) {
                    showToast('Ukuran berkas melebihi 5 MB.', 'error');
                    return;
                }
                if (!PENANGANAN_JENIS_BOLEH.includes(berkas.type)) {
                    showToast('Jenis berkas tidak didukung. Gunakan JPG, PNG, WEBP, atau PDF.', 'error');
                    return;
                }
            }

            if (tombol) {
                tombol.disabled = true;
                tombol.textContent = 'Menyimpan...';
            }
            try {
                // Status baru ditandai sukses HANYA setelah database benar-benar berubah
                await window.sipekaAPI.resolveReport(report.no_tiket, tindakan, berkas);
                showToast(`Tiket ${report.no_tiket} berhasil ditandai SELESAI!`, 'success');
                setTimeout(() => (window.location.href = 'dashboard.php'), 700);
            } catch (error) {
                if (tombol) {
                    tombol.disabled = false;
                    tombol.textContent = tekstombol;
                }
                showToast(error.message || 'Gagal menyelesaikan laporan.', 'error');
            }
        };
    } catch (error) {
        console.error('[SIPEKA] Gagal memuat halaman penanganan:', error);
        showToast(error.message || 'Gagal memuat data laporan.', 'error');
    }
}