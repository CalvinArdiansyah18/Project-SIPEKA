/**
 * Halaman Profil (mahasiswa & unit)
 * Halaman : content/mahasiswa/profil.php, content/unit/{unit}/profil.php
 * Fungsi  : initProfilePage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

// ==========================================
// PROFILE PAGE CONTROLLER (MAHASISWA & UNIT)
// ==========================================
async function initProfilePage() {
    let profile;
    try {
        profile = await window.sipekaAPI.getProfile();   // data asli dari database
    } catch (error) {
        console.error('[SIPEKA] Gagal memuat profil:', error);
        showToast(error.message || 'Gagal memuat data profil.', 'error');
        return;
    }
    if (!profile) return;

    const isUnit = (profile.role === 'unit');

    // Render Left Card Data
    const avatarEl = document.getElementById('profAvatar');
    const cardNamaEl = document.getElementById('profCardNama');
    if (cardNamaEl) cardNamaEl.textContent = profile.nama;
    if (avatarEl && profile.nama) {
        avatarEl.textContent = profile.nama.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
    }

    // Populate specific fields
    if (!isUnit) {
        const nimEl = document.getElementById('profDetailNim');
        const prodiEl = document.getElementById('profDetailProdi');
        const jurusanEl = document.getElementById('profDetailJurusan');
        const emailEl = document.getElementById('profDetailEmail');
        if (nimEl) nimEl.textContent = profile.nim || '-';
        if (prodiEl) prodiEl.textContent = profile.program_studi || '-';
        if (jurusanEl) jurusanEl.textContent = profile.jurusan || 'Teknologi Informasi';
        if (emailEl) emailEl.textContent = profile.email || '-';

        // Populate edit form
        const editNama = document.getElementById('editNama');
        const editNim = document.getElementById('editNim');
        const editProdi = document.getElementById('editProdi');
        const editTelp = document.getElementById('editTelepon');
        if (editNama) editNama.value = profile.nama || '';
        if (editNim) editNim.value = profile.nim || '';
        if (editProdi) editProdi.value = profile.program_studi || '';
        if (editTelp) editTelp.value = profile.nomor_telepon || '';

        // Populate mini stats
        const stats = profile.stats || {};
        const sTotal = document.getElementById('statProfTotal');
        const sProses = document.getElementById('statProfProses');
        const sSelesai = document.getElementById('statProfSelesai');
        const sDitolak = document.getElementById('statProfDitolak');
        if (sTotal) sTotal.textContent = stats.total ?? 0;
        if (sProses) sProses.textContent = (stats.proses ?? 0) + (stats.menunggu ?? 0);
        if (sSelesai) sSelesai.textContent = stats.selesai ?? 0;
        if (sDitolak) sDitolak.textContent = stats.ditolak ?? 0;

    } else {
        // Unit Profile
        const unitEl = document.getElementById('profDetailUnit');
        const kodeEl = document.getElementById('profDetailKode');
        const wewenangEl = document.getElementById('profDetailWewenang');
        const emailEl = document.getElementById('profDetailEmail');
        if (unitEl) unitEl.textContent = profile.nama_unit || 'Sekretariat Jurusan (Sekjur)';
        if (kodeEl) kodeEl.textContent = profile.kode_unit || 'sekjur';
        if (wewenangEl && profile.deskripsi_unit) wewenangEl.textContent = profile.deskripsi_unit;
        if (emailEl) emailEl.textContent = profile.email || '-';

        // Akun unit tidak punya nomor telepon di database: hanya nama yang bisa diubah
        const editNama = document.getElementById('editNama');
        if (editNama) editNama.value = profile.nama || '';
        // const editTelp = document.getElementById('editTelepon');
        // if (editTelp) editTelp.value = profile.nomor_telepon || '';

        const stats = profile.stats || {};
        const sTotal = document.getElementById('statProfTotal');
        const sMenunggu = document.getElementById('statProfMenunggu');
        const sProses = document.getElementById('statProfProses');
        const sSelesai = document.getElementById('statProfSelesai');
        if (sTotal) sTotal.textContent = stats.total ?? 0;
        if (sMenunggu) sMenunggu.textContent = stats.menunggu ?? 0;
        if (sProses) sProses.textContent = stats.proses ?? 0;
        if (sSelesai) sSelesai.textContent = stats.selesai ?? 0;
    }

    // Handle Edit Profile Form
    const editForm = document.getElementById('editProfileForm');
    if (editForm) {
        editForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const nama = document.getElementById('editNama').value.trim();
            const telpEl = document.getElementById('editTelepon');
            const telp = telpEl ? telpEl.value.trim() : null;
            const tombol = editForm.querySelector('button[type="submit"]');

            if (!nama || nama.length < 3) {
                showToast('Nama lengkap minimal 3 karakter.', 'error');
                return;
            }

            const payload = { nama: nama };
            if (telp !== null) payload.nomor_telepon = telp;

            if (tombol) tombol.disabled = true;
            try {
                const res = await window.sipekaAPI.updateProfile(payload);
                const baru = res.profil || { nama: nama };
                showToast(res.message || 'Profil berhasil diperbarui!', 'success');
                renderUserProfile();
                if (cardNamaEl) cardNamaEl.textContent = baru.nama;
                if (avatarEl) avatarEl.textContent = baru.nama.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
            } catch (error) {
                showToast(error.message || 'Gagal memperbarui profil.', 'error');
            } finally {
                if (tombol) tombol.disabled = false;
            }
        });
    }

    // Handle Change Password Form
    const changePassForm = document.getElementById('changePasswordForm');
    if (changePassForm) {
        changePassForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const oldPass = document.getElementById('passLama').value;
            const newPass = document.getElementById('passBaru').value;
            const confirmPass = document.getElementById('passKonfirm').value;

            if (!oldPass || !newPass) {
                showToast('Kata sandi lama dan baru wajib diisi.', 'error');
                return;
            }

            if (newPass.length < 8) {
                showToast('Kata sandi baru minimal 8 karakter.', 'error');
                return;
            }

            if (newPass !== confirmPass) {
                showToast('Konfirmasi kata sandi baru tidak cocok.', 'error');
                return;
            }

            const tombol = changePassForm.querySelector('button[type="submit"]');
            if (tombol) tombol.disabled = true;
            try {
                const res = await window.sipekaAPI.changePassword(oldPass, newPass, confirmPass);
                showToast(res.message || 'Kata sandi berhasil diperbarui!', 'success');
                changePassForm.reset();
            } catch (error) {
                showToast(error.message || 'Gagal memperbarui kata sandi.', 'error');
            } finally {
                if (tombol) tombol.disabled = false;
            }
        });
    }
}