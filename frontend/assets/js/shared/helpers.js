/**
 * Fungsi bantu yang dipakai banyak halaman.
 * Fungsi  : getUnitFromPath(), getUnitDashboardPath(), renderUserProfile(), escapeHtml(), getEmptyStateHtml()
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