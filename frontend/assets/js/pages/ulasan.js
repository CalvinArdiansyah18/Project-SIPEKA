/**
 * Halaman Ulasan (mahasiswa & semua unit memakai skrip yang sama)
 * Halaman : content/mahasiswa/ulasan.php, content/unit/{unit}/ulasan.php
 *           (isi halaman ada di includes/ulasan-content.php)
 * Fungsi  : initUlasanPage()
 * Data    : GET ulasan/list.php (SEMUA ulasan mahasiswa + balasan, dengan filter waktu & kategori),
 *           GET ulasan/laporan-saya.php (penanganan selesai milik sendiri, untuk dipilih),
 *           POST ulasan/saya.php (tulis / hapus ulasan sendiri; hanya mahasiswa),
 *           POST ulasan/balas.php (balas / hapus balasan sendiri)
 * Ulasan berupa komentar (tanpa bintang), ditulis HANYA oleh mahasiswa dan boleh lebih dari satu;
 * petugas unit hanya membalas. Balasan boleh banyak.
 * Memakai chipPeran() & buatFilterUlasan() dari pages/landing-ulasan.js.
 */

async function initUlasanPage() {
    const form = document.getElementById('ulasanForm');
    if (!form) return;

    const isi = document.getElementById('ulasanIsi');
    const hitung = document.getElementById('ulasanHitung');
    const btnKirim = document.getElementById('btnKirimUlasan');
    const daftar = document.getElementById('ulasanDaftar');
    const ringkasan = document.getElementById('ulasanRingkasan');
    const btnMore = document.getElementById('ulasanMore');
    const wadahFilter = document.getElementById('ulasanFilter');
    const pilihLaporan = document.getElementById('ulasanLaporan');
    const infoLaporan = document.getElementById('ulasanLaporanInfo');
    const catatanUnit = document.getElementById('ulasanCatatanUnit');

    // Halaman di bawah /content/unit/ = petugas unit: tidak bisa menulis ulasan, hanya membalas.
    // (Server tetap menolak: ulasan/saya.php memeriksa peran.)
    const adalahUnit = window.location.pathname.includes('/content/unit/');

    const HALAMAN = 10;
    let dimuat = 0;           // jumlah ulasan yang sedang tampil
    let permintaan = 0;       // abaikan jawaban lama bila filter berubah saat memuat
    const terbuka = new Set(); // id ulasan yang kotak balasnya sedang dibuka

    const filter = buatFilterUlasan(wadahFilter, () => muatDaftar());

    // ================= tulis ulasan (mahasiswa) =================
    async function muatPenanganan() {
        try {
            const { laporan } = await window.sipekaAPI.getLaporanUlasan();
            pilihLaporan.innerHTML = '<option value="">Ulasan umum (tidak terkait penanganan tertentu)</option>' +
                laporan.map((l) => {
                    const diulas = l.jumlah_ulasan ? ` — sudah diulas ${l.jumlah_ulasan}×` : '';
                    return `<option value="${l.id_laporan}">${escapeHtml(l.no_tiket)} · ${escapeHtml(l.judul)} (${escapeHtml(l.kategori)})${diulas}</option>`;
                }).join('');
            infoLaporan.textContent = laporan.length
                ? 'Pilih penanganan yang sudah selesai, atau biarkan sebagai ulasan umum.'
                : 'Belum ada penanganan yang selesai. Penanganan yang selesai akan muncul di sini.';
        } catch (err) {
            if (infoLaporan) {
                infoLaporan.textContent = 'Daftar penanganan belum dapat dimuat.';
            }
            console.error('Gagal memuat daftar penanganan untuk ulasan:', err);
        }
    }

    function periksa() {
        hitung.textContent = `${isi.value.length}/500`;
        btnKirim.disabled = isi.value.trim().length < 3;
    }
    isi.addEventListener('input', periksa);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        btnKirim.disabled = true;
        try {
            const hasil = await window.sipekaAPI.tulisUlasan(isi.value.trim(), pilihLaporan.value || null);
            showToast(hasil.message || 'Ulasan terkirim.', 'success');
            isi.value = '';
            pilihLaporan.value = '';
            muatPenanganan();                  // perbarui penanda "sudah diulas"
            filter.reset(false);               // pastikan ulasan baru terlihat (tidak tersaring)
            await muatDaftar();
        } catch (err) {
            showToast(err.message || 'Gagal mengirim ulasan.', 'error');
        }
        periksa();
    });

    // ================= daftar semua ulasan + balasan =================
    function balasanHtml(b) {
        return `
        <div class="review-reply">
            <div class="review-reply__head">
                <strong>${escapeHtml(b.nama)}</strong>${chipPeran(b.peran)}
                <span class="review-reply__time">· ${escapeHtml(relativeTime(b.waktu))}</span>
                ${b.milik_saya ? `<button type="button" class="review-reply__del" data-hapus-balasan="${b.id_balasan}">Hapus</button>` : ''}
            </div>
            <div>${escapeHtml(b.isi)}</div>
        </div>`;
    }

    function kartuHtml(u, idx) {
        const inisial = (u.nama || '?').trim().charAt(0).toUpperCase();
        const buka = terbuka.has(u.id_ulasan);
        return `
        <article class="review-item anim-fade-up" id="ulasan-${u.id_ulasan}" style="--i:${Math.min(idx, 8)}">
            <div class="review-item__head">
                <div class="review-item__avatar">${escapeHtml(inisial)}</div>
                <div>
                    <div class="review-item__name">${escapeHtml(u.nama)}${u.milik_saya ? '<span class="review-item__chip review-item__chip--saya">Ulasan Anda</span>' : ''}</div>
                    <div class="review-item__role">${chipPeran(u.peran)}</div>
                </div>
            </div>
            ${infoPenangananHtml(u.laporan)}
            <p class="review-item__text">${escapeHtml(u.isi)}</p>
            ${u.balasan.length ? `<div class="review-replies">${u.balasan.map(balasanHtml).join('')}</div>` : ''}
            <div class="review-item__foot">
                <span class="review-item__date">${escapeHtml(relativeTime(u.waktu))}</span>
                <span class="review-item__actions">
                    ${u.milik_saya ? `<button type="button" class="review-item__hapus" data-hapus-ulasan="${u.id_ulasan}">Hapus</button>` : ''}
                    <button type="button" class="review-item__balas" data-toggle-balas="${u.id_ulasan}">
                        Balas${u.jumlah_balasan ? ` (${u.jumlah_balasan})` : ''}
                    </button>
                </span>
            </div>
            <div class="review-reply-box ${buka ? '' : 'hidden'}" data-kotak-balas="${u.id_ulasan}">
                <textarea class="form-input" rows="2" maxlength="300" placeholder="Tulis balasan (2-300 karakter)..."></textarea>
                <div class="review-reply-box__aksi">
                    <button type="button" class="btn-primary btn-inline" data-kirim-balas="${u.id_ulasan}">Kirim Balasan</button>
                </div>
            </div>
        </article>`;
    }

    // tambah=false: muat ulang dari awal sebanyak yang sudah tampil (balasan baru langsung terlihat)
    async function muatDaftar({ tambah = false } = {}) {
        const nomor = ++permintaan;
        const awal = tambah ? dimuat : 0;
        const jumlah = tambah ? HALAMAN : Math.max(dimuat, HALAMAN);
        let data;
        try {
            data = await window.sipekaAPI.getUlasanPublik(jumlah, awal, filter.ambil());
        } catch (err) {
            if (nomor !== permintaan) return;
            if (!tambah) daftar.innerHTML = '<p class="review-empty">Daftar ulasan belum dapat dimuat.</p>';
            else showToast(err.message || 'Gagal memuat ulasan.', 'error');
            return;
        }
        if (nomor !== permintaan) return;

        filter.isiKategori(data.kategori);
        const r = data.ringkasan;
        ringkasan.textContent = r.total_semua ? teksJumlahUlasan(r, filter.aktif()) : '';
        if (r.total === 0) {
            daftar.innerHTML = r.total_semua === 0
                ? `<p class="review-empty">${adalahUnit ? 'Belum ada ulasan dari mahasiswa.' : 'Belum ada ulasan. Jadilah yang pertama!'}</p>`
                : '<p class="review-empty">Tidak ada ulasan untuk filter ini.</p>';
            btnMore.hidden = true;
            dimuat = 0;
            return;
        }
        const html = data.ulasan.map((u, i) => kartuHtml(u, i)).join('');
        if (tambah) { daftar.insertAdjacentHTML('beforeend', html); dimuat += data.ulasan.length; }
        else { daftar.innerHTML = html; dimuat = data.ulasan.length; }
        btnMore.hidden = !data.ada_lagi;
    }

    daftar.addEventListener('click', async (e) => {
        const toggle = e.target.closest('[data-toggle-balas]');
        if (toggle) {
            const id = Number(toggle.dataset.toggleBalas);
            const kotak = daftar.querySelector(`[data-kotak-balas="${id}"]`);
            const buka = kotak.classList.toggle('hidden') === false;
            if (buka) { terbuka.add(id); kotak.querySelector('textarea').focus(); } else { terbuka.delete(id); }
            return;
        }
        const kirim = e.target.closest('[data-kirim-balas]');
        if (kirim) {
            const id = Number(kirim.dataset.kirimBalas);
            const ta = daftar.querySelector(`[data-kotak-balas="${id}"] textarea`);
            const teks = ta.value.trim();
            if (teks.length < 2) { showToast('Balasan minimal 2 karakter.', 'error'); return; }
            kirim.disabled = true;
            try {
                await window.sipekaAPI.balasUlasan(id, teks);
                showToast('Balasan terkirim.', 'success');
                terbuka.delete(id);
                await muatDaftar();
            } catch (err) {
                showToast(err.message || 'Gagal mengirim balasan.', 'error');
                kirim.disabled = false;
            }
            return;
        }
        const hapusBalasan = e.target.closest('[data-hapus-balasan]');
        if (hapusBalasan) {
            if (!window.confirm('Hapus balasan Anda?')) return;
            try {
                await window.sipekaAPI.hapusBalasan(Number(hapusBalasan.dataset.hapusBalasan));
                await muatDaftar();
            } catch (err) {
                showToast(err.message || 'Gagal menghapus balasan.', 'error');
            }
            return;
        }
        const hapusUlasan = e.target.closest('[data-hapus-ulasan]');
        if (hapusUlasan) {
            if (!window.confirm('Hapus ulasan ini? Balasan di bawahnya ikut terhapus.')) return;
            try {
                await window.sipekaAPI.hapusUlasan(Number(hapusUlasan.dataset.hapusUlasan));
                showToast('Ulasan dihapus.', 'success');
                await muatDaftar();
            } catch (err) {
                showToast(err.message || 'Gagal menghapus ulasan.', 'error');
            }
        }
    });

    btnMore.addEventListener('click', async () => {
        btnMore.disabled = true;
        await muatDaftar({ tambah: true });
        btnMore.disabled = false;
    });

    // Ada balasan baru untuk saya (notifikasi) -> daftar diperbarui otomatis
    window.addEventListener('sipeka:notifikasi-baru', (e) => {
        if (e.detail.items.some((n) => n.jenis === 'balasan_ulasan')) muatDaftar();
    });

    if (adalahUnit) {
        form.classList.add('hidden');
        if (catatanUnit) catatanUnit.classList.remove('hidden');
    } else {
        periksa();
        muatPenanganan();
    }
    await muatDaftar();
}
