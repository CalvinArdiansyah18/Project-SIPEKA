/**
 * Halaman Login
 * Halaman : content/auth/login.php
 * Fungsi  : initLoginPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */
// ======================
// LOGIN PAGE CONTROLLER
// ======================
function initLoginPage() {
    const form = document.getElementById('loginForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value.trim();
        const roleSelect = document.getElementById('roleSelect');
        const selectedRole = roleSelect ? roleSelect.value : 'mahasiswa';

        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
        }

        try {
            const res = await window.sipekaAPI.login(username, password, selectedRole);
            if (res && res.success) {
                showToast(`Selamat datang, ${res.user.nama}!`, 'success');
                setTimeout(() => {
                    if (res.redirect) {
                        window.location.href = res.redirect;
                    } else if (res.user.role === 'unit') {
                        window.location.href = getUnitDashboardPath(res.user.kode_unit || res.user.unit_id);
                    } else {
                        window.location.href = '../mahasiswa/dashboard.php';
                    }
                }, 600);
            } else {
                showToast(res ? res.message : 'Login gagal. Periksa kembali email dan password.', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'LOGIN';
                }
            }
        } catch (err) {
            showToast('Terjadi kesalahan pada sistem otentikasi.', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'LOGIN';
            }
        }
    });
}