/**
 * Halaman depan - bagian Ulasan (HANYA MENAMPILKAN HASIL; tidak ada formulir)
 * Isi     : ulasan MAHASISWA + balasan UNIT saja (unit tidak menulis ulasan)
 * Halaman : landing-page.php (#reviewFilter, #reviewSummary, #reviewGrid, #reviewMore)
 * Fungsi  : initLandingUlasan()
 * Data    : GET backend/api/ulasan/list.php (publik, tanpa login) + filter waktu
 * Juga memuat fungsi bersama yang dipakai pages/ulasan.js:
 *   chipPeran(), buatFilterUlasan() (filter harian/bulanan/tahunan/rentang + peran + kata kunci)
 */

// Label kecil "Petugas Unit" / "Mahasiswa"
function chipPeran(peran) {
    const unit = peran === 'Petugas Unit';
    return `<span class="review-item__chip ${unit ? 'review-item__chip--unit' : 'review-item__chip--mhs'}">${escapeHtml(peran)}</span>`;
}

/**
 * Filter ulasan. Dipakai halaman depan dan halaman Ulasan (tampilan sama).
 *   wadah    : elemen kosong tempat filter digambar
 *   onChange : dipanggil setiap filter berubah
 * Mengembalikan { ambil(), aktif(), reset(picu = true), isiKategori(daftar) }.
 * ambil() menghasilkan parameter siap kirim ke ulasan/list.php: {mode, kategori, tanggal|bulan|tahun|dari+sampai, q}
 */
function buatFilterUlasan(wadah, onChange, { tengah = false } = {}) {
    const BULAN = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const pad = (n) => String(n).padStart(2, '0');
    const kini = new Date();
    const hariIni = `${kini.getFullYear()}-${pad(kini.getMonth() + 1)}-${pad(kini.getDate())}`;   // tanggal lokal, bukan UTC
    const bulanIni = pad(kini.getMonth() + 1);
    const tahunIni = kini.getFullYear();

    const opsiBulan = BULAN.map((b, i) => `<option value="${pad(i + 1)}"${pad(i + 1) === bulanIni ? ' selected' : ''}>${b}</option>`).join('');
    const opsiTahun = Array.from({ length: 6 }, (_, i) => tahunIni - i).map((t) => `<option value="${t}">${t}</option>`).join('');

    wadah.innerHTML = `
        <div class="review-filter ${tengah ? 'review-filter--tengah' : ''}">
            <div class="review-filter__field"><label>Periode</label>
                <select data-f="mode">
                    <option value="semua">Semua waktu</option>
                    <option value="harian">Harian</option>
                    <option value="bulanan">Bulanan</option>
                    <option value="tahunan">Tahunan</option>
                    <option value="rentang">Rentang tanggal</option>
                </select></div>
            <div class="review-filter__field" data-m="harian" hidden><label>Tanggal</label>
                <input type="date" data-f="tanggal" value="${hariIni}" max="${hariIni}"></div>
            <div class="review-filter__field" data-m="bulanan" hidden><label>Bulan</label>
                <select data-f="bulan">${opsiBulan}</select></div>
            <div class="review-filter__field" data-m="bulanan tahunan" hidden><label>Tahun</label>
                <select data-f="tahun">${opsiTahun}</select></div>
            <div class="review-filter__field" data-m="rentang" hidden><label>Dari</label>
                <input type="date" data-f="dari" value="${hariIni}" max="${hariIni}"></div>
            <div class="review-filter__field" data-m="rentang" hidden><label>Sampai</label>
                <input type="date" data-f="sampai" value="${hariIni}" max="${hariIni}"></div>
            <div class="review-filter__field"><label>Kategori</label>
                <select data-f="kategori"><option value="semua">Semua kategori</option></select></div>
            <div class="review-filter__field review-filter__cari"><label>Cari</label>
                <input type="search" data-f="q" placeholder="Kata kunci..." maxlength="60"></div>
            <button type="button" class="review-filter__reset">Atur ulang</button>
        </div>`;

    const f = (nama) => wadah.querySelector(`[data-f="${nama}"]`);

    function tampilkanKolom() {
        const mode = f('mode').value;
        wadah.querySelectorAll('[data-m]').forEach((k) => {
            k.hidden = !k.dataset.m.split(' ').includes(mode);
        });
    }

    function ambil() {
        const mode = f('mode').value;
        const hasil = { mode };
        if (f('kategori').value !== 'semua') hasil.kategori = f('kategori').value;
        if (mode === 'harian') hasil.tanggal = f('tanggal').value || hariIni;
        if (mode === 'bulanan') hasil.bulan = `${f('tahun').value}-${f('bulan').value}`;
        if (mode === 'tahunan') hasil.tahun = f('tahun').value;
        if (mode === 'rentang') { hasil.dari = f('dari').value || hariIni; hasil.sampai = f('sampai').value || hariIni; }
        const q = f('q').value.trim();
        if (q) hasil.q = q;
        return hasil;
    }

    function aktif() {
        return f('mode').value !== 'semua' || f('kategori').value !== 'semua' || f('q').value.trim() !== '';
    }

    let tunda = null;
    wadah.querySelectorAll('select, input[type="date"]').forEach((el) => {
        el.addEventListener('change', () => { tampilkanKolom(); onChange(); });
    });
    f('q').addEventListener('input', () => {                    // tunggu pengguna berhenti mengetik
        clearTimeout(tunda);
        tunda = setTimeout(onChange, 350);
    });
    function reset(picu = true) {
        f('mode').value = 'semua'; f('kategori').value = 'semua'; f('q').value = '';
        f('tanggal').value = hariIni; f('dari').value = hariIni; f('sampai').value = hariIni;
        f('bulan').value = bulanIni; f('tahun').value = String(tahunIni);
        tampilkanKolom();
        if (picu) onChange();
    }
    wadah.querySelector('.review-filter__reset').addEventListener('click', () => reset(true));

    // Pilihan kategori datang dari server (ulasan/list.php -> data.kategori); pilihan yang sedang aktif dipertahankan.
    function isiKategori(daftar) {
        const sel = f('kategori');
        const dipilih = sel.value;
        sel.innerHTML = '<option value="semua">Semua kategori</option>' +
            (daftar || []).map((k) => `<option value="${k.id_kategori}">${escapeHtml(k.nama_kategori)}</option>`).join('');
        sel.value = [...sel.options].some((o) => o.value === dipilih) ? dipilih : 'semua';
    }

    tampilkanKolom();
    return { ambil, aktif, reset, isiKategori };
}

// Konteks penanganan yang diulas: kategori + unit (tiket hanya ada untuk pemilik ulasan)
function infoPenangananHtml(lap) {
    if (!lap) return '';
    const bagian = [lap.no_tiket ? `<strong>${escapeHtml(lap.no_tiket)}</strong>` : '', escapeHtml(lap.kategori || ''), lap.unit ? `ditangani ${escapeHtml(lap.unit)}` : '']
        .filter(Boolean).join(' · ');
    return `<div class="review-item__laporan"><span aria-hidden="true">🛠</span> Penanganan: ${bagian}</div>`;
}

// Teks jumlah hasil: "12 ulasan" atau "3 dari 12 ulasan" saat filter aktif
function teksJumlahUlasan(ringkasan, filterAktif) {
    return filterAktif ? `${ringkasan.total} dari ${ringkasan.total_semua} ulasan` : `${ringkasan.total} ulasan`;
}

// Kartu ulasan mahasiswa untuk halaman depan (hanya baca). Balasan yang datang dari API hanya balasan unit;
// maks. 2 balasan pertama ditampilkan.
function kartuUlasanHtml(u, idx = 0) {
    const inisial = (u.nama || '?').trim().charAt(0).toUpperCase();
    const balasan = u.balasan || [];
    const tampil = balasan.slice(0, 2).map((b) => `
        <div class="review-reply">
            <div class="review-reply__head"><strong>${escapeHtml(b.nama)}</strong>${chipPeran(b.peran)}</div>
            <div>${escapeHtml(b.isi)}</div>
        </div>`).join('');
    const sisa = balasan.length - 2;
    return `
        <article class="review-item anim-fade-up" style="--i:${Math.min(idx, 8)}">
            <div class="review-item__head">
                <div class="review-item__avatar">${escapeHtml(inisial)}</div>
                <div>
                    <div class="review-item__name">${escapeHtml(u.nama)}</div>
                    <div class="review-item__role">${chipPeran(u.peran)}</div>
                </div>
            </div>
            ${infoPenangananHtml(u.laporan)}
            <p class="review-item__text">${escapeHtml(u.isi)}</p>
            ${balasan.length ? `<div class="review-replies">${tampil}${sisa > 0 ? `<div class="review-replies-more">+${sisa} balasan lainnya</div>` : ''}</div>` : ''}
            <div class="review-item__date">${escapeHtml(relativeTime(u.waktu))}</div>
        </article>`;
}

async function initLandingUlasan() {
    const grid = document.getElementById('reviewGrid');
    const ringkasan = document.getElementById('reviewSummary');
    const wadahFilter = document.getElementById('reviewFilter');
    const more = document.getElementById('reviewMore');
    if (!grid) return;

    const HALAMAN = 6;
    let dimuat = 0;
    let permintaan = 0;   // abaikan jawaban lama bila filter berubah saat memuat

    const filter = wadahFilter ? buatFilterUlasan(wadahFilter, () => muat(true), { tengah: true }) : null;

    async function muat(awal) {
        const nomor = ++permintaan;
        if (awal) dimuat = 0;
        let data;
        try {
            data = await window.sipekaAPI.getUlasanPublik(HALAMAN, dimuat, { ...(filter ? filter.ambil() : {}), hanya_unit: 1 });
        } catch (err) {
            if (nomor !== permintaan) return;
            if (awal) grid.innerHTML = '<p class="review-empty">Ulasan belum dapat dimuat. Silakan coba lagi nanti.</p>';
            else showToast(err.message || 'Gagal memuat ulasan.', 'error');
            return;
        }
        if (nomor !== permintaan) return;

        const r = data.ringkasan;
        if (filter) filter.isiKategori(data.kategori);
        if (awal) {
            if (r.total_semua === 0) {
                if (ringkasan) ringkasan.textContent = '';
                grid.innerHTML = '<p class="review-empty">Belum ada ulasan. Jadilah yang pertama setelah masuk ke akun Anda.</p>';
                if (more) more.hidden = true;
                return;
            }
            if (ringkasan) ringkasan.textContent = teksJumlahUlasan(r, filter ? filter.aktif() : false);
            grid.innerHTML = r.total === 0 ? '<p class="review-empty">Tidak ada ulasan untuk filter ini.</p>' : '';
        }
        grid.insertAdjacentHTML('beforeend', data.ulasan.map((u, i) => kartuUlasanHtml(u, i)).join(''));
        dimuat += data.ulasan.length;
        if (more) more.hidden = !data.ada_lagi;
    }

    if (more) more.addEventListener('click', async () => {
        more.disabled = true;
        await muat(false);
        more.disabled = false;
    });
    await muat(true);
}
