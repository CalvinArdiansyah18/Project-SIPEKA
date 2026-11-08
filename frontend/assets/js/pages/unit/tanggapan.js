/**
 * Tanggapan Unit untuk Pelapor
 * Dipakai oleh : pages/unit/verifikasi.js dan pages/unit/penanganan.js
 * Fungsi       : initTanggapanUnit(report)
 *
 * Menambahkan kartu "Tanggapan untuk Pelapor" di bagian bawah .content-container:
 *   - daftar tanggapan yang sudah dikirim unit (dari report.catatan_unit, jenis 'Tanggapan')
 *   - form tulis tanggapan baru (disembunyikan jika laporan sudah selesai / ditolak)
 * Data disimpan lewat POST backend/api/unit/tanggapan.php. Tanggapan langsung terlihat oleh
 * pelapor di halaman riwayat.
 */

const TANGGAPAN_MAKS_KARAKTER = 1000;

function initTanggapanUnit(report) {
    const container = document.querySelector('.content-container');
    if (!container || !report || document.getElementById('tanggapanUnitCard')) return;

    const sudahFinal = report.status === 'selesai' || report.status === 'ditolak';
    const daftar = (report.catatan_unit || []).filter((c) => c.jenis === 'Tanggapan');

    const card = document.createElement('div');
    card.className = 'form-card form-card--full tanggapan-card';
    card.id = 'tanggapanUnitCard';
    card.innerHTML = `
        <h3 class="card-title card-title--bordered">Tanggapan untuk Pelapor</h3>
        <p class="tanggapan-bantuan">Tanggapan yang dikirim di sini langsung terlihat oleh pelapor pada halaman riwayat laporannya.</p>
        <div id="tanggapanDaftar" class="tanggapan-daftar"></div>
        ${sudahFinal ? '' : `
        <form id="tanggapanForm">
            <div class="form-group">
                <label for="tanggapanIsi">Tulis Tanggapan <span class="required-mark">*</span></label>
                <textarea id="tanggapanIsi" class="form-input" maxlength="${TANGGAPAN_MAKS_KARAKTER}" required
                    placeholder="Contoh: Laporan diterima, teknisi dijadwalkan memeriksa besok pagi."></textarea>
            </div>
            <div class="text-right">
                <button type="submit" class="btn-primary btn-inline">Kirim Tanggapan</button>
            </div>
        </form>`}
    `;
    container.appendChild(card);

    const wadah = card.querySelector('#tanggapanDaftar');
    const tampilkan = () => {
        wadah.innerHTML = '';
        if (daftar.length === 0) {
            const kosong = document.createElement('p');
            kosong.className = 'bukti-kosong';
            kosong.textContent = 'Belum ada tanggapan untuk laporan ini.';
            wadah.appendChild(kosong);
            return;
        }
        daftar.forEach((t) => {
            const item = document.createElement('div');
            item.className = 'tanggapan-item';
            const isi = document.createElement('p');
            isi.textContent = t.isi;
            const meta = document.createElement('small');
            meta.textContent = `${t.petugas || '-'} · ${formatDateTime(t.waktu)}`;
            item.append(isi, meta);
            wadah.appendChild(item);
        });
    };
    tampilkan();

    const form = card.querySelector('#tanggapanForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const input = form.querySelector('#tanggapanIsi');
        const tombol = form.querySelector('button[type="submit"]');
        const isi = input.value.trim();
        if (isi === '') {
            showToast('Isi tanggapan wajib diisi.', 'error');
            input.focus();
            return;
        }

        tombol.disabled = true;
        try {
            const res = await window.sipekaAPI.sendTanggapan(report.no_tiket, isi);
            daftar.push(res.tanggapan);
            tampilkan();
            input.value = '';
            showToast(res.message || 'Tanggapan berhasil dikirim.', 'success');
        } catch (error) {
            showToast(error.message || 'Gagal mengirim tanggapan.', 'error');
        } finally {
            tombol.disabled = false;
        }
    });
}