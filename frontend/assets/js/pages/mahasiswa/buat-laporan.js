
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
    const MAKS_TOTAL_BYTE = 20 * 1024 * 1024;

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

    let berkasTerpilih = [];
    let objectUrls = [];

    function lepasObjectUrls() {
        objectUrls.forEach(url => URL.revokeObjectURL(url));
        objectUrls = [];
    }

    function totalUkuran(list) {
        return list.reduce((total, file) => total + file.size, 0);
    }

    function kunciBerkas(file) {
        return `${file.name}::${file.size}::${file.lastModified}`;
    }

    function renderPreview() {
        if (!previewContainer) return;

        lepasObjectUrls();

        previewContainer.innerHTML = berkasTerpilih.map((file, index) => {
            const ukuran = (file.size / 1024 / 1024).toFixed(2);
            const gambar = file.type.startsWith('image/');

            let thumb;

            if (gambar) {
                const url = URL.createObjectURL(file);
                objectUrls.push(url);

                thumb = `
                    <img
                        src="${url}"
                        alt="Preview Bukti"
                        class="upload-preview-thumb"
                    >
                `;
            } else {
                thumb = '<div class="upload-preview-icon">📄</div>';
            }

            return `
                <div class="upload-preview-card">
                    ${thumb}

                    <div class="upload-preview-info">
                        <h5>${escapeHtml(file.name)}</h5>
                        <p>${ukuran} MB • ${gambar
                            ? 'Berkas Gambar Siap Diunggah'
                            : 'Dokumen PDF Terlampir'}</p>
                    </div>

                    <button
                        type="button"
                        class="btn-detail btn-remove"
                        data-clear-upload="${index}"
                    >
                        Hapus
                    </button>
                </div>
            `;
        }).join('');
    }

    function tambahBerkas(daftar) {
        const pesan = [];
        let berubah = false;
        let total = totalUkuran(berkasTerpilih);

        Array.from(daftar).forEach(file => {
            if (!TIPE_BERKAS.includes(file.type)) {
                pesan.push(`${file.name}: jenis berkas tidak didukung.`);
                return;
            }

            if (file.size > MAKS_BYTE) {
                pesan.push(`${file.name}: ukuran melebihi 5 MB.`);
                return;
            }

            if (berkasTerpilih.some(f => kunciBerkas(f) === kunciBerkas(file))) {
                return;
            }

            if (total + file.size > MAKS_TOTAL_BYTE) {
                pesan.push(`${file.name}: total ukuran semua berkas melebihi 20 MB.`);
                return;
            }

            berkasTerpilih.push(file);
            total += file.size;
            berubah = true;
        });

        if (pesan.length > 0) {
            showToast(pesan.join(' '), 'error');
        }

        if (berubah) {
            renderPreview();
        }
    }

    function clearUpload() {
        berkasTerpilih = [];
        if (fileInput) fileInput.value = '';
        lepasObjectUrls();
        if (previewContainer) previewContainer.innerHTML = '';
    }

    window.clearUpload = clearUpload;

    if (fileInput && previewContainer &&
        fileInput.dataset.initialized !== 'true') {

        fileInput.dataset.initialized = 'true';

        fileInput.addEventListener('change', () => {
            if (!fileInput.files || fileInput.files.length === 0) return;

            tambahBerkas(fileInput.files);

            // Kosongkan input agar berkas yang sama bisa dipilih lagi
            fileInput.value = '';
        });

        // Tombol hapus preview
        previewContainer.addEventListener('click', (e) => {
            // Cegah klik preview ikut membuka dialog pemilihan berkas
            e.stopPropagation();

            const tombol = e.target.closest('[data-clear-upload]');

            if (!tombol) return;

            e.preventDefault();

            const index = Number(tombol.dataset.clearUpload);

            if (!Number.isInteger(index) || !berkasTerpilih[index]) return;

            berkasTerpilih.splice(index, 1);
            renderPreview();
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

                tambahBerkas(files);
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

        const berkasTidakValid = berkasTerpilih.find(
            file => !TIPE_BERKAS.includes(file.type) || file.size > MAKS_BYTE
        );

        if (berkasTidakValid) {
            showToast(
                `${berkasTidakValid.name}: jenis berkas tidak didukung atau melebihi 5 MB.`,
                'error'
            );
            return;
        }

        if (totalUkuran(berkasTerpilih) > MAKS_TOTAL_BYTE) {
            showToast('Total ukuran semua berkas melebihi 20 MB.', 'error');
            return;
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

        berkasTerpilih.forEach(file => {
            fd.append('bukti[]', file);
        });

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
