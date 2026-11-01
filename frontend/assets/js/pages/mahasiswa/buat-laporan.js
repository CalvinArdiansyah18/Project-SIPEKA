/**
 * Form Buat Laporan
 * Halaman : content/mahasiswa/buat-laporan.php
 * Fungsi  : initBuatLaporanPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */
// ===============================================
// BUAT LAPORAN PAGE (DIRECT ROUTING INTEGRATION)
// ===============================================
async function initBuatLaporanPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const kategoriKey = urlParams.get('kategori') || 'fasilitas';

    const categoryConfigs = {
        'fasilitas': 'Fasilitas Kampus',
        'akademik': 'Pelayanan Akademik',
        'dosen': 'Perilaku Dosen/Staf',
        'pungli': 'Pungutan Liar',
        'mahasiswa': 'Pelanggaran Mahasiswa'
    };
    const TIPE_BERKAS = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
    const MAKS_BYTE = 5 * 1024 * 1024;

    const form = document.getElementById('reportForm');
    const fileInput = document.getElementById('fileUpload');
    const kategoriText = document.getElementById('kategoriText');
    const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
    const teksTombolAwal = submitBtn ? submitBtn.innerHTML : '';

    function pulihkanTombol() {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = teksTombolAwal;
        }
    }

    // 1. Ambil kategori dari database (nama kategori + unit penanggung jawab)
    let kategoriTerpilih = null;
    try {
        const data = await window.sipekaAPI.getKategoriLaporan();
        const namaDicari = categoryConfigs[kategoriKey];
        kategoriTerpilih = (data.kategori || []).find(k => k.nama_kategori === namaDicari) || null;
    } catch (err) {
        console.error('[SIPEKA] Gagal memuat kategori:', err);
        showToast(err.message || 'Gagal memuat kategori pengaduan.', 'error');
    }

    if (!kategoriTerpilih) {
        if (kategoriText) kategoriText.value = 'Kategori tidak ditemukan. Silakan pilih ulang kategori.';
        if (submitBtn) submitBtn.disabled = true;
        showToast('Kategori pengaduan tidak ditemukan. Silakan pilih kategori dari halaman sebelumnya.', 'error');
        return;
    }
    if (kategoriText) {
        kategoriText.value = `${kategoriTerpilih.nama_kategori} (Disposisi otomatis: ${kategoriTerpilih.nama_unit})`;
    }

    // 2. Kirim formulir
    if (!form) return;
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

        const rawFile = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;
        if (rawFile) {
            if (!TIPE_BERKAS.includes(rawFile.type)) {
                showToast('Jenis berkas tidak didukung. Gunakan JPG, PNG, WEBP, atau PDF.', 'error');
                return;
            }
            if (rawFile.size > MAKS_BYTE) {
                showToast('Ukuran berkas melebihi 5 MB.', 'error');
                return;
            }
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = 'Mengirim Laporan...';
        }

        const fd = new FormData();
        fd.append('id_kategori', kategoriTerpilih.id_kategori);
        fd.append('judul_laporan', judul);
        fd.append('lokasi', lokasi);
        fd.append('tanggal_kejadian', tanggal);
        fd.append('kronologi', kronologi);
        if (rawFile) fd.append('bukti', rawFile);

        try {
            const res = await window.sipekaAPI.createLaporan(fd);
            showToast(`Pengaduan ${res.no_tiket} berhasil diajukan!`, 'success');
            setTimeout(() => {
                window.location.href = `riwayat.php?tiket=${encodeURIComponent(res.no_tiket)}&success=true`;
            }, 800);
        } catch (err) {
            console.error('[SIPEKA] Gagal mengirim laporan:', err);
            showToast(err.message || 'Terjadi kesalahan saat memproses laporan.', 'error');
            pulihkanTombol();
        }
    });
}

// Set max date to today so future dates cannot be picked
const today = new Date().toISOString().split('T')[0];
const tglInput = document.getElementById('tanggal');
if (tglInput) {
    tglInput.max = today;
    tglInput.value = today;
}

// Instant Image Thumbnail Preview using FileReader API
const fileInput = document.getElementById('fileUpload');
const previewContainer = document.getElementById('previewContainer');

if (fileInput && previewContainer) {
    fileInput.addEventListener('change', () => {
        const file = fileInput.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) {
                showToast('Ukuran berkas melebihi 5 MB. Silakan pilih berkas yang lebih kecil.', 'error');
                fileInput.value = '';
                previewContainer.innerHTML = '';
                return;
            }

            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewContainer.innerHTML = `
                <div class="upload-preview-card">
                  <img src="${e.target.result}" alt="Preview Bukti" class="upload-preview-thumb">
                  <div class="upload-preview-info">
                    <h5>${file.name}</h5>
                    <p>${(file.size / 1024 / 1024).toFixed(2)} MB • Berkas Gambar Siap Diunggah</p>
                  </div>
                  <button type="button" class="btn-detail" onclick="clearUpload()" style="padding: 4px 8px; color: #ef4444; border-color: #fca5a5;">Hapus</button>
                </div>
              `;
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.innerHTML = `
              <div class="upload-preview-card">
                <div style="font-size: 2rem;">📄</div>
                <div class="upload-preview-info">
                  <h5>${file.name}</h5>
                  <p>${(file.size / 1024 / 1024).toFixed(2)} MB • Dokumen PDF Terlampir</p>
                </div>
                <button type="button" class="btn-detail" onclick="clearUpload()" style="padding: 4px 8px; color: #ef4444; border-color: #fca5a5;">Hapus</button>
              </div>
            `;
            }
        }
    });
}

window.clearUpload = function () {
    if (fileInput) fileInput.value = '';
    if (previewContainer) previewContainer.innerHTML = '';
};