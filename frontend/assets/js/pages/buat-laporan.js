/**
 * Form Buat Laporan
 * Halaman : content/mahasiswa/buat-laporan.php
 * Fungsi  : initBuatLaporanPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */
// ===============================================
// BUAT LAPORAN PAGE (DIRECT ROUTING INTEGRATION)
// ===============================================
function initBuatLaporanPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const kategoriKey = urlParams.get('kategori') || 'fasilitas';

    const categoryConfigs = {
        'fasilitas': { name: 'Fasilitas Kampus', unit: 'Sekretariat Jurusan (Sekjur)' },
        'akademik': { name: 'Pelayanan Akademik', unit: 'Admin Program Studi (Admin Prodi)' },
        'dosen': { name: 'Perilaku Dosen atau Staf', unit: 'Ketua Jurusan (Kajur)' },
        'pungli': { name: 'Pungutan Liar (Pungli)', unit: 'Ketua Jurusan (Kajur)' },
        'mahasiswa': { name: 'Pelanggaran Mahasiswa', unit: 'Ketua Program Studi (KPS)' }
    };

    const config = categoryConfigs[kategoriKey] || categoryConfigs['fasilitas'];
    const kategoriText = document.getElementById('kategoriText');
    if (kategoriText) {
        kategoriText.value = `${config.name} (Disposisi otomatis: ${config.unit})`;
    }

    // Handle Form Submission
    const form = document.getElementById('reportForm');
    const fileInput = document.getElementById('fileUpload');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const judul = document.getElementById('judul').value.trim();
            const lokasi = document.getElementById('lokasi').value.trim();
            const tanggal = document.getElementById('tanggal').value;
            const kronologi = document.getElementById('kronologi').value.trim();

            // Client-side validations
            if (judul.length < 10) {
                showToast('Judul pengaduan terlalu pendek (minimal 10 karakter).', 'error');
                document.getElementById('judul').focus();
                return;
            }

            if (!lokasi) {
                showToast('Lokasi kejadian wajib diisi.', 'error');
                document.getElementById('lokasi').focus();
                return;
            }

            if (!tanggal) {
                showToast('Tanggal kejadian wajib diisi.', 'error');
                return;
            }

            const today = new Date().toISOString().split('T')[0];
            if (tanggal > today) {
                showToast('Tanggal kejadian tidak boleh lebih dari hari ini.', 'error');
                return;
            }

            if (kronologi.length < 30) {
                showToast('Kronologi terlalu singkat. Jelaskan masalah minimal 30 karakter.', 'error');
                document.getElementById('kronologi').focus();
                return;
            }

            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Mengirim Laporan...';
            }

            const rawFile = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;

            const payload = {
                kategoriKey: kategoriKey,
                kategoriName: config.name,
                judul: judul,
                lokasi: lokasi,
                tanggal: tanggal,
                kronologi: kronologi,
                bukti_file: rawFile ? rawFile.name : 'foto-bukti.jpg'
            };

            try {
                const res = await window.sipekaAPI.createReport(payload, rawFile);
                if (res && res.success) {
                    const tiket = res.data ? res.data.no_tiket : 'KP-Baru';
                    showToast(`Pengaduan ${tiket} berhasil diajukan!`, 'success');
                    setTimeout(() => {
                        window.location.href = `riwayat.php?tiket=${tiket}&success=true`;
                    }, 800);
                } else {
                    showToast(res ? res.message : 'Gagal mengirim pengaduan.', 'error');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Kirim Pengaduan Sekarang ✓';
                    }
                }
            } catch (err) {
                showToast('Terjadi kesalahan saat memproses laporan.', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Kirim Pengaduan Sekarang ✓';
                }
            }
        });
    }
}