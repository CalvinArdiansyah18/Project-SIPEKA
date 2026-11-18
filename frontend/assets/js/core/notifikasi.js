/**
 * SIPEKA - Notifikasi (bel di header, mahasiswa & semua unit)
 * Fungsi  : initNotifikasi()  -> dipanggil main.js pada halaman yang butuh login.
 * Data    : GET  backend/api/notifikasi/list.php   (daftar + jumlah belum dibaca)
 *           POST backend/api/notifikasi/baca.php   (tandai dibaca)
 * Cara kerja:
 *   - bel disisipkan lewat JS ke .app-header (desktop) dan .mobile-topbar (HP),
 *     jadi HTML setiap halaman TIDAK perlu diubah.
 *   - dicek ulang tiap 30 detik (berhenti saat tab tidak aktif).
 *   - notifikasi baru memunculkan toast dan event 'sipeka:notifikasi-baru'
 *     (dipakai dashboard unit untuk memuat ulang tabel otomatis).
 */

// Metode API notifikasi & ulasan ditambahkan ke objek yang sudah ada (core/api.js)
Object.assign(window.sipekaAPI, {
    getNotifikasi: (limit = 20) => apiGet('notifikasi/list.php', { limit }),
    bacaNotifikasi: (payload) => apiPostJson('notifikasi/baca.php', payload),
    getUlasanPublik: (limit = 10, offset = 0, filter = {}) => apiGet('ulasan/list.php', { limit, offset, ...filter }),   // + filter waktu; juga dipakai halaman Ulasan (login -> milik_saya)
    tulisUlasan: (isi, id_laporan = null) => apiPostJson('ulasan/saya.php', { isi, id_laporan }),   // hanya mahasiswa
    getLaporanUlasan: () => apiGet('ulasan/laporan-saya.php'),                                       // penanganan selesai milik sendiri
    hapusUlasan: (id_ulasan) => apiPostJson('ulasan/saya.php', { hapus: true, id_ulasan }),
    balasUlasan: (id_ulasan, isi) => apiPostJson('ulasan/balas.php', { id_ulasan, isi }),
    hapusBalasan: (id_balasan) => apiPostJson('ulasan/balas.php', { hapus: true, id_balasan }),
});

const NOTIF_INTERVAL_MS = 30000;
const NOTIF_IKON_BEL = `<svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>`;

// Tujuan saat notifikasi diklik. Semua halaman tujuan berada di folder yang sama dengan halaman sekarang.
function tujuanNotifikasi(n) {
    if (n.jenis === 'balasan_ulasan' || n.jenis === 'ulasan_baru') return 'ulasan.php';   // halaman Ulasan ada di folder yang sama
    if (!n.no_tiket) return null;
    const t = encodeURIComponent(n.no_tiket);
    if (window.location.pathname.includes('/content/mahasiswa/')) return `riwayat.php?tiket=${t}`;
    switch (n.status_laporan) {
        case 'menunggu': return `verifikasi.php?tiket=${t}`;
        case 'diproses':
        case 'dalam_penanganan': return `penanganan.php?tiket=${t}`;
        default: return `detail-selesai.php?tiket=${t}`;
    }
}

async function initNotifikasi() {
    if (!window.sipekaAPI || document.querySelector('.notif-wrap')) return;

    const bel = [];   // satu bel per lokasi (desktop header, topbar HP)

    function buatBel(idPanel) {
        const wrap = document.createElement('div');
        wrap.className = 'notif-wrap';
        wrap.innerHTML = `
            <button type="button" class="notif-btn" aria-label="Notifikasi" aria-haspopup="true" aria-expanded="false" aria-controls="${idPanel}">
                ${NOTIF_IKON_BEL}
                <span class="notif-badge" hidden>0</span>
            </button>
            <div class="notif-panel" id="${idPanel}" role="region" aria-label="Daftar notifikasi" hidden>
                <div class="notif-panel__head">
                    <h3>Notifikasi</h3>
                    <button type="button" class="notif-readall" disabled>Tandai semua dibaca</button>
                </div>
                <ul class="notif-list"><li class="notif-empty">Memuat...</li></ul>
            </div>`;
        const o = {
            wrap,
            tombol: wrap.querySelector('.notif-btn'),
            badge: wrap.querySelector('.notif-badge'),
            panel: wrap.querySelector('.notif-panel'),
            daftar: wrap.querySelector('.notif-list'),
            semua: wrap.querySelector('.notif-readall'),
        };
        bel.push(o);
        return o;
    }

    // ---- sisipkan ke header desktop ----
    const header = document.querySelector('.app-header');
    const profil = header ? header.querySelector('.user-profile') : null;
    if (header && profil) {
        const aksi = document.createElement('div');
        aksi.className = 'header-actions';
        profil.parentNode.insertBefore(aksi, profil);
        aksi.appendChild(buatBel('notifPanelDesktop').wrap);
        aksi.appendChild(profil);
    }
    // ---- sisipkan ke topbar mobile (di samping tombol hamburger) ----
    const topbar = document.querySelector('.mobile-topbar');
    const hamburger = topbar ? topbar.querySelector('.hamburger-btn') : null;
    if (topbar && hamburger) {
        const aksi = document.createElement('div');
        aksi.className = 'topbar-actions';
        hamburger.parentNode.insertBefore(aksi, hamburger);
        aksi.appendChild(buatBel('notifPanelMobile').wrap);
        aksi.appendChild(hamburger);
    }
    if (bel.length === 0) return;

    let data = { belum_dibaca: 0, notifikasi: [] };
    let idTerakhir = null;       // id notifikasi terbesar yang sudah pernah dilihat (null = belum muat pertama kali)

    const esc = (v) => (typeof escapeHtml === 'function' ? escapeHtml(v) : String(v ?? ''));

    function gambar() {
        bel.forEach((b) => {
            const n = data.belum_dibaca;
            b.badge.hidden = n <= 0;
            b.badge.textContent = n > 99 ? '99+' : String(n);
            b.tombol.setAttribute('aria-label', n > 0 ? `Notifikasi, ${n} belum dibaca` : 'Notifikasi');
            b.semua.disabled = n <= 0;
            b.daftar.innerHTML = data.notifikasi.length === 0
                ? '<li class="notif-empty">Belum ada notifikasi.</li>'
                : data.notifikasi.map((x) => `
                    <li>
                        <button type="button" class="notif-item ${x.sudah_dibaca ? '' : 'notif-item--unread'}" data-id="${x.id_notifikasi}">
                            <span class="notif-dot" aria-hidden="true"></span>
                            <span>
                                <span class="notif-item__title" style="display:block">${esc(x.judul)}</span>
                                <span class="notif-item__text" style="display:block">${esc(x.pesan)}</span>
                                <span class="notif-item__time" style="display:block">${esc(relativeTime(x.waktu))}</span>
                            </span>
                        </button>
                    </li>`).join('');
        });
    }

    async function muat() {
        let hasil;
        try {
            hasil = await window.sipekaAPI.getNotifikasi(20);
        } catch (err) {
            // Gagal jaringan / sesi habis: diam saja, coba lagi pada siklus berikutnya.
            if (typeof sipekaLog === 'function') sipekaLog('notifikasi gagal dimuat:', err.message);
            return;
        }
        const baru = idTerakhir === null
            ? []
            : hasil.notifikasi.filter((n) => n.id_notifikasi > idTerakhir && !n.sudah_dibaca);
        if (hasil.notifikasi.length) {
            idTerakhir = Math.max(idTerakhir ?? 0, ...hasil.notifikasi.map((n) => n.id_notifikasi));
        } else if (idTerakhir === null) {
            idTerakhir = 0;
        }
        data = hasil;
        gambar();

        if (baru.length) {
            baru.slice(0, 2).forEach((n) => showToast(`${esc(n.judul)}: ${esc(n.pesan)}`, 'info'));
            bel.forEach((b) => {
                b.tombol.classList.remove('is-ringing');
                void b.tombol.offsetWidth;            // mulai ulang animasi
                b.tombol.classList.add('is-ringing');
            });
            window.dispatchEvent(new CustomEvent('sipeka:notifikasi-baru', { detail: { items: baru } }));
        }
    }

    function tutupSemua(kecuali = null) {
        bel.forEach((b) => {
            if (b === kecuali) return;
            b.panel.hidden = true;
            b.tombol.setAttribute('aria-expanded', 'false');
        });
    }

    async function tandaiSatu(id) {
        const item = data.notifikasi.find((n) => n.id_notifikasi === id);
        if (!item || item.sudah_dibaca) return;
        item.sudah_dibaca = true;                      // perbarui tampilan dulu
        data.belum_dibaca = Math.max(0, data.belum_dibaca - 1);
        gambar();
        try { await window.sipekaAPI.bacaNotifikasi({ id_notifikasi: id }); } catch (e) { /* dicoba lagi saat muat berikutnya */ }
    }

    bel.forEach((b) => {
        b.tombol.addEventListener('click', (e) => {
            e.stopPropagation();
            const buka = b.panel.hidden;
            tutupSemua(b);
            b.panel.hidden = !buka;
            b.tombol.setAttribute('aria-expanded', String(buka));
            if (buka) muat();                           // segarkan saat dibuka
        });
        b.panel.addEventListener('click', (e) => e.stopPropagation());
        b.semua.addEventListener('click', async () => {
            data.notifikasi.forEach((n) => { n.sudah_dibaca = true; });
            data.belum_dibaca = 0;
            gambar();
            try { await window.sipekaAPI.bacaNotifikasi({ semua: true }); } catch (err) { showToast('Gagal menandai notifikasi.', 'error'); }
        });
        b.daftar.addEventListener('click', async (e) => {
            const tombol = e.target.closest('.notif-item');
            if (!tombol) return;
            const id = Number(tombol.dataset.id);
            const item = data.notifikasi.find((n) => n.id_notifikasi === id);
            await tandaiSatu(id);
            const tujuan = item ? tujuanNotifikasi(item) : null;
            if (tujuan) window.location.href = tujuan;
        });
    });

    document.addEventListener('click', () => tutupSemua());
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        const terbuka = bel.find((b) => !b.panel.hidden);
        if (terbuka) { tutupSemua(); terbuka.tombol.focus(); }
    });

    await muat();
    setInterval(() => { if (!document.hidden) muat(); }, NOTIF_INTERVAL_MS);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) muat(); });
}
