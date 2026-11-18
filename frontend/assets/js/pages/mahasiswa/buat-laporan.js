
/**
 * Form Buat Laporan
 * Halaman : content/mahasiswa/buat-laporan.php
 * Fungsi  : initBuatLaporanPage()
 */

async function initBuatLaporanPage() {
    const urlParams = new URLSearchParams(window.location.search);
    const kategoriKey = urlParams.get('kategori') || 'fasilitas';

    const categoryConfigs = {
        fasilitas: 'Fasilitas Kampus',
        akademik: 'Pelayanan Akademik',
        dosen: 'Perilaku Dosen/Staf',
        pungli: 'Pungutan Liar',
        mahasiswa: 'Pelanggaran Mahasiswa'
    };

    const TIPE_BERKAS = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf'
    ];

    const MAKS_BYTE = 5 * 1024 * 1024;

    // Ambil elemen HTML
    const form = document.getElementById('reportForm');
    const fileInput = document.getElementById('fileUpload');
    const previewContainer = document.getElementById('previewContainer');
    const uploadBox = document.getElementById('uploadBox');
    const kategoriText = document.getElementById('kategoriText');
    const tglInput = document.getElementById('tanggal');

    const submitBtn = form
        ? form.querySelector('button[type="submit"]')
        : null;

    if (!form) {
        console.error('[SIPEKA] Form laporan tidak ditemukan.');
        return;
    }

    // ==========================================
    // 1. PENGATURAN TANGGAL
    // ==========================================

    const d = new Date();

    const today = [
        d.getFullYear(),
        String(d.getMonth() + 1).padStart(2, '0'),
        String(d.getDate()).padStart(2, '0')
    ].join('-');

    if (tglInput) {
        tglInput.max = today;
        if (!tglInput.value) {
            tglInput.value = today;
        }
    }

    // ==========================================
    // 2. PREVIEW DAN UPLOAD GAMBAR
    // ==========================================

    function clearUpload() {
        if (fileInput) fileInput.value = '';
        if (previewContainer) previewContainer.innerHTML = '';
    }

    window.clearUpload = clearUpload;

    if (fileInput && previewContainer &&
        fileInput.dataset.initialized !== 'true') {

        fileInput.dataset.initialized = 'true';

        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];

            previewContainer.innerHTML = '';

            if (!file) return;

            // Validasi tipe file
            if (!TIPE_BERKAS.includes(file.type)) {
                showToast(
                    'Jenis berkas tidak didukung. Gunakan JPG, PNG, WEBP, atau PDF.',
                    'error'
                );
                clearUpload();
                return;
            }

            // Validasi ukuran file
            if (file.size > MAKS_BYTE) {
                showToast(
                    'Ukuran berkas melebihi 5 MB.',
                    'error'
                );
                clearUpload();
                return;
            }

            const ukuran = (file.size / 1024 / 1024).toFixed(2);

            // Preview gambar
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();

                reader.onload = (e) => {
                    previewContainer.innerHTML = `
                        <div class="upload-preview-card">
                            <img
                                src="${e.target.result}"
                                alt="Preview Bukti"
                                class="upload-preview-thumb"
                            >

                            <div class="upload-preview-info">
                                <h5>${escapeHtml(file.name)}</h5>
                                <p>${ukuran} MB • Berkas Gambar Siap Diunggah</p>
                            </div>

                            <button
                                type="button"
                                class="btn-detail btn-remove"
                                data-clear-upload
                            >
                                Hapus
                            </button>
                        </div>
                    `;
                };

                reader.onerror = () => {
                    showToast('Gagal membaca gambar.', 'error');
                    clearUpload();
                };

                reader.readAsDataURL(file);

            } else {
                // Preview PDF
                previewContainer.innerHTML = `
                    <div class="upload-preview-card">
                        <div style="font-size: 2rem;">📄</div>

                        <div class="upload-preview-info">
                            <h5>${escapeHtml(file.name)}</h5>
                            <p>${ukuran} MB • Dokumen PDF Terlampir</p>
                        </div>

                        <button
                            type="button"
                            class="btn-detail btn-remove"
                            data-clear-upload
                        >
                            Hapus
                        </button>
                    </div>
                `;
            }
        });

        // Tombol hapus preview
        previewContainer.addEventListener('click', (e) => {
            if (e.target.closest('[data-clear-upload]')) {
                clearUpload();
            }
        });

        // Drag and drop
        if (uploadBox) {
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadBox.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    uploadBox.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadBox.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    uploadBox.classList.remove('is-dragover');
                });
            });

            uploadBox.addEventListener('drop', (e) => {
                const files = e.dataTransfer?.files;

                if (!files || files.length === 0) return;

                const dt = new DataTransfer();
                dt.items.add(files[0]);

                fileInput.files = dt.files;
                fileInput.dispatchEvent(
                    new Event('change', { bubbles: true })
                );
            });
        }
    }

    // ==========================================
    // 3. AMBIL KATEGORI DARI DATABASE
    // ==========================================

    let kategoriTerpilih = null;

    try {
        const data = await window.sipekaAPI.getKategoriLaporan();

        const namaDicari = categoryConfigs[kategoriKey];

        kategoriTerpilih = (data.kategori || []).find(
            k => k.nama_kategori === namaDicari
        ) || null;

    } catch (err) {
        console.error('[SIPEKA] Gagal memuat kategori:', err);

        showToast(
            err.message || 'Gagal memuat kategori pengaduan.',
            'error'
        );
    }

    if (!kategoriTerpilih) {
        if (kategoriText) {
            kategoriText.value =
                'Kategori tidak ditemukan. Silakan pilih ulang kategori.';
        }

        if (submitBtn) submitBtn.disabled = true;

        showToast(
            'Kategori pengaduan tidak ditemukan.',
            'error'
        );

        return;
    }

    if (kategoriText) {
        kategoriText.value =
            `${kategoriTerpilih.nama_kategori} (Disposisi otomatis: ${kategoriTerpilih.nama_unit})`;
    }

    // ==========================================
    // 4. SUBMIT FORM LAPORAN
    // ==========================================

    // Mencegah event submit terpasang berulang
    if (form.dataset.submitInitialized === 'true') return;

    form.dataset.submitInitialized = 'true';

    const teksTombolAwal = submitBtn
        ? submitBtn.innerHTML
        : '';

    function pulihkanTombol() {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = teksTombolAwal;
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const judul = document.getElementById('judul').value.trim();
        const lokasi = document.getElementById('lokasi').value.trim();
        const tanggal = tglInput ? tglInput.value : '';
        const kronologi = document.getElementById('kronologi').value.trim();

        if (judul.length < 5) {
            showToast('Judul minimal 5 karakter.', 'error');
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

        if (tanggal > today) {
            showToast('Tanggal kejadian tidak boleh lebih dari hari ini.', 'error');
            return;
        }

        if (kronologi.length < 10) {
            showToast('Kronologi minimal 10 karakter.', 'error');
            document.getElementById('kronologi').focus();
            return;
        }

        const rawFile = fileInput?.files[0] || null;

        if (rawFile) {
            if (!TIPE_BERKAS.includes(rawFile.type)) {
                showToast('Jenis berkas tidak didukung.', 'error');
                return;
            }

            if (rawFile.size > MAKS_BYTE) {
                showToast('Ukuran berkas maksimal 5 MB.', 'error');
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

        if (rawFile) {
            fd.append('bukti', rawFile);
        }

        try {
            const res = await window.sipekaAPI.createLaporan(fd);

            showToast(
                `Pengaduan ${res.no_tiket} berhasil diajukan!`,
                'success'
            );

            setTimeout(() => {
                window.location.href =
                    `riwayat.php?tiket=${encodeURIComponent(res.no_tiket)}&success=true`;
            }, 800);

        } catch (err) {
            console.error('[SIPEKA] Gagal mengirim laporan:', err);

            showToast(
                err.message || 'Terjadi kesalahan saat memproses laporan.',
                'error'
            );

            pulihkanTombol();
        }
    });
}
