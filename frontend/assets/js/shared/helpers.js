/**
 * Fungsi bantu yang dipakai banyak halaman.
 * Fungsi  : getUnitFromPath(), getUnitDashboardPath(), renderUserProfile(), escapeHtml(), getEmptyStateHtml(),
 * renderDaftarBukti(), initPasswordToggle()
 */

// Helper: Deteksi Unit dari URL path
function getUnitFromPath() {
    const p = window.location.pathname.toLowerCase();
    if (p.includes('/sekjur/')) return 'sekjur';
    if (p.includes('/admin-prodi/') || p.includes('/prodi/')) return 'prodi';
    if (p.includes('/kajur/')) return 'kajur';
    if (p.includes('/kps/')) return 'kps';
    return null;
}

// Helper: Ambil Path Dashboard Unit Spesifik
function getUnitDashboardPath(kodeAtauNamaUnit, relativePrefix = '..') {
    const teks = (kodeAtauNamaUnit || '').toLowerCase();
    if (teks.includes('sekjur') || teks.includes('sekretariat')) return `${relativePrefix}/unit/sekjur/dashboard.php`;
    if (teks.includes('prodi') || teks.includes('admin')) return `${relativePrefix}/unit/admin-prodi/dashboard.php`;
    if (teks.includes('kajur') || teks.includes('ketua jurusan')) return `${relativePrefix}/unit/kajur/dashboard.php`;
    if (teks.includes('kps') || teks.includes('ketua program')) return `${relativePrefix}/unit/kps/dashboard.php`;
    return `${relativePrefix}/unit/sekjur/dashboard.php`;
}

// Render User Info in Topbar
function renderUserProfile() {
    const user = window.sipekaStore ? window.sipekaStore.getUser() : null;
    const pathUnit = getUnitFromPath();
    const unitNames = {
        'sekjur': 'Sekretariat Jurusan (Sekjur)',
        'prodi': 'Admin Program Studi (Admin Prodi)',
        'kajur': 'Ketua Jurusan (Kajur)',
        'kps': 'Ketua Program Studi (KPS)'
    };
    const unitInitials = {
        'sekjur': 'SJ',
        'prodi': 'AP',
        'kajur': 'KJ',
        'kps': 'KP'
    };

    const nameEl = document.querySelector('.user-name');
    const roleEl = document.querySelector('.user-role');
    const avatarEl = document.querySelector('.avatar');

    if (user) {
        if (nameEl) nameEl.textContent = user.nama;
        if (roleEl) {
            roleEl.textContent = user.role === 'mahasiswa'
                ? `NIM: ${user.nim || '-'} (${user.program_studi || '-'})`
                : `Unit: ${user.nama_unit || unitNames[pathUnit] || 'Unit Penanganan'}`;
        }
        if (avatarEl && user.nama) {
            avatarEl.textContent = user.nama.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        }
    } else if (pathUnit) {
        if (nameEl) nameEl.textContent = `Staff ${unitNames[pathUnit]}`;
        if (roleEl) roleEl.textContent = unitNames[pathUnit];
        if (avatarEl) avatarEl.textContent = unitInitials[pathUnit] || 'UN';
    }
}

// Helper: amankan teks dari pengguna sebelum disisipkan ke innerHTML (mencegah XSS)
function escapeHtml(teks) {
    return String(teks ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));
}

// Helper: label status dari kode database (enum status_laporan)
function getStatusLabel(kode) {
    const label = {
        menunggu: 'Menunggu Validasi',
        dalam_penanganan: 'Dalam Penanganan',
        selesai: 'Selesai',
        ditolak: 'Ditolak'
    };
    return label[kode] || kode || '-';
}

// Helper: umur laporan dari jumlah jam (dari backend) -> "2 hari 3 jam" / "5 jam"
function formatUmur(jam) {
    if (jam === null || jam === undefined || jam === '') return '-';
    const j = Number(jam);
    if (!isFinite(j)) return '-';
    if (j < 1) return '< 1 jam';
    if (j < 24) return `${Math.floor(j)} jam`;
    const hari = Math.floor(j / 24);
    const sisa = Math.floor(j % 24);
    return sisa > 0 ? `${hari} hari ${sisa} jam` : `${hari} hari`;
}

const IKON_SLA_PERINGATAN = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
const IKON_SLA_WAKTU = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 14"/></svg>';

// Helper: badge peringatan SLA berdasarkan sla_status dari backend.
//   'terlambat' -> merah, 'mendekati' -> kuning, selain itu tidak tampil.
function getSlaBadge(slaStatus, terlambatJam) {
    if (slaStatus === 'terlambat') {
        const teks = terlambatJam ? `Terlambat ${formatUmur(terlambatJam)}` : 'Terlambat';
        return `<span class="badge badge-sla badge-sla--late">${IKON_SLA_PERINGATAN} ${teks}</span>`;
    }
    if (slaStatus === 'mendekati') {
        return `<span class="badge badge-sla badge-sla--warn">${IKON_SLA_WAKTU} Mendekati batas</span>`;
    }
    return '';
}

// Helper: kelas baris tabel untuk menyorot laporan yang perlu perhatian.
function getSlaRowClass(perluPerhatian) {
    return perluPerhatian ? 'row-perlu-perhatian' : '';
}

// Helper: tanggal + jam, contoh "5 Oktober 2026 09.30" (menerima format ISO 8601 dari backend)
function formatDateTime(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return String(iso);
    return d.toLocaleString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
}

// Helper: waktu relatif, contoh "5 menit lalu" / "kemarin" (dipakai notifikasi & ulasan)
function relativeTime(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return String(iso);
    const detik = Math.round((Date.now() - d.getTime()) / 1000);
    if (detik < 45) return 'baru saja';
    const menit = Math.round(detik / 60);
    if (menit < 60) return `${menit} menit lalu`;
    const jam = Math.round(menit / 60);
    if (jam < 24) return `${jam} jam lalu`;
    const hari = Math.round(jam / 24);
    if (hari === 1) return 'kemarin';
    if (hari < 7) return `${hari} hari lalu`;
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

// Helper: SVG Empty State Generator
function getEmptyStateHtml(title, description) {
    return `
    <tr>
      <td colspan="7" style="text-align: center; padding: 3rem 1.5rem;">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.75rem;">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/>
            <line x1="8" y1="12" x2="16" y2="12"/>
          </svg>
          <div style="font-weight: 600; color: #475569; font-size: 1rem;">${title}</div>
          <div style="font-size: 0.85rem; color: #94a3b8; max-width: 380px;">${description}</div>
        </div>
      </td>
    </tr>
  `;
}

// Helper: tampilkan daftar bukti (gambar, PDF, atau tautan) ke dalam sebuah elemen.
//   wadah       : elemen tujuan
//   daftar      : [{ id_bukti, nama_file, jenis_file }]
//   sumber      : 'laporan' | 'penanganan' (menentukan alamat berkas di backend)
//   pesanKosong : teks bila tidak ada bukti
function renderDaftarBukti(wadah, daftar, sumber, pesanKosong) {
    if (!wadah) return;
    wadah.innerHTML = '';

    if (!daftar || daftar.length === 0) {
        const kosong = document.createElement('p');
        kosong.className = 'bukti-kosong';
        kosong.textContent = pesanKosong || 'Tidak ada bukti.';
        wadah.appendChild(kosong);
        return;
    }

    daftar.forEach((bukti) => {
        const url = window.sipekaAPI.getBuktiUrl(bukti.id_bukti, sumber);
        const jenis = String(bukti.jenis_file || '');

        const item = document.createElement('div');
        item.className = 'bukti-item';

        const nama = document.createElement('p');
        nama.className = 'bukti-nama';
        nama.textContent = bukti.nama_file;
        item.appendChild(nama);

        if (jenis.startsWith('image/')) {
            const gambar = document.createElement('img');
            gambar.src = url;
            gambar.alt = bukti.nama_file;
            gambar.className = 'bukti-image';
            gambar.loading = 'lazy';
            // Berkas bisa hilang dari folder uploads (mis. data contoh): beri pesan, bukan gambar rusak
            gambar.onerror = () => {
                const info = document.createElement('p');
                info.className = 'bukti-kosong';
                info.textContent = 'Berkas tidak tersedia di server.';
                gambar.replaceWith(info);
            };
            item.appendChild(gambar);
        } else if (jenis === 'application/pdf') {
            const pdf = document.createElement('iframe');
            pdf.src = url;
            pdf.className = 'bukti-pdf';
            pdf.title = bukti.nama_file;
            item.appendChild(pdf);
        } else {
            const link = document.createElement('a');
            link.href = url;
            link.target = '_blank';
            link.rel = 'noopener noreferrer';
            link.textContent = 'Lihat / Unduh Bukti';
            item.appendChild(link);
        }

        wadah.appendChild(item);
    });
}

// Helper: tombol mata untuk melihat / menyembunyikan kata sandi.
// Cukup bungkus input dengan <div class="input-password">...</div>,
// tombol dibuat otomatis di sini (berlaku juga untuk elemen yang ditambahkan dinamis).
const IKON_MATA = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>';
const IKON_MATA_TUTUP = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a20.3 20.3 0 0 1 5.06-5.94"/><path d="M9.9 4.24A10.94 10.94 0 0 1 12 5c7 0 11 7 11 7a20.3 20.3 0 0 1-3.22 4.31"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

function initPasswordToggle(root = document) {
    root.querySelectorAll('.input-password').forEach((wadah) => {
        const input = wadah.querySelector('input[type="password"]');
        if (!input || wadah.querySelector('.input-password-toggle')) return;

        const tombol = document.createElement('button');
        tombol.type = 'button';
        tombol.className = 'input-password-toggle';
        tombol.setAttribute('aria-label', 'Tampilkan kata sandi');
        tombol.setAttribute('aria-pressed', 'false');
        tombol.innerHTML = IKON_MATA;

        tombol.addEventListener('click', () => {
            const tampil = input.type === 'password';
            input.type = tampil ? 'text' : 'password';
            tombol.innerHTML = tampil ? IKON_MATA_TUTUP : IKON_MATA;
            tombol.setAttribute('aria-label', tampil ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            tombol.setAttribute('aria-pressed', String(tampil));
        });

        wadah.appendChild(tombol);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => initPasswordToggle());
} else {
    initPasswordToggle();
}