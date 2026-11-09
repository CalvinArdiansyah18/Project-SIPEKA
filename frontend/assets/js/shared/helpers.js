/**
 * Fungsi bantu yang dipakai banyak halaman.
 * Fungsi  : getUnitFromPath(), getUnitDashboardPath(), renderUserProfile(), escapeHtml(), getEmptyStateHtml(),
 * renderDaftarBukti()
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

// Helper: tanggal + jam, contoh "5 Oktober 2026 09.30" (menerima format ISO 8601 dari backend)
function formatDateTime(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return String(iso);
    return d.toLocaleString('id-ID', {
        day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
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