/**
 * Halaman Registrasi (khusus mahasiswa)
 * Halaman : content/auth/register.php
 * Fungsi  : initRegisterPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 */

// ===============================================
// REGISTRATION PAGE CONTROLLER (KHUSUS MAHASISWA)
// ===============================================
function initRegisterPage() {
    const form = document.getElementById('registerForm');
    if (!form) return;

    const namaInput = document.getElementById('regNama');
    const nimInput = document.getElementById('regNim');
    const prodiInput = document.getElementById('regProdi');
    const emailInput = document.getElementById('regEmail');
    const telpInput = document.getElementById('regTelepon');
    const passInput = document.getElementById('regPassword');
    const confirmInput = document.getElementById('regConfirmPassword');
    const submitBtn = document.getElementById('btnRegister');

    function clearErrors() {
        document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.form-input').forEach(el => el.classList.remove('input-error'));
    }

    function showError(inputEl, errorElId, msg) {
        if (inputEl) inputEl.classList.add('input-error');
        const errEl = document.getElementById(errorElId);
        if (errEl) {
            if (msg) errEl.textContent = msg;
            errEl.style.display = 'block';
        }
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();

        const nama = namaInput ? namaInput.value.trim() : '';
        const email = emailInput ? emailInput.value.trim() : '';
        const telp = telpInput ? telpInput.value.trim() : '';
        const pass = passInput ? passInput.value : '';
        const confirm = confirmInput ? confirmInput.value : '';

        let hasError = false;

        if (!nama || nama.length < 3) {
            showError(namaInput, 'errNama', 'Nama lengkap minimal 3 karakter.');
            hasError = true;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email || !emailRegex.test(email)) {
            showError(emailInput, 'errEmail', 'Format alamat email tidak valid.');
            hasError = true;
        }

        if (!pass || pass.length < 8) {
            showError(passInput, 'errPassword', 'Kata sandi minimal 8 karakter.');
            hasError = true;
        }

        if (pass !== confirm) {
            showError(confirmInput, 'errConfirm', 'Konfirmasi kata sandi tidak cocok.');
            hasError = true;
        }

        // Registrasi publik hanya untuk mahasiswa. Role ditentukan server, bukan dikirim dari sini.
        const nim = nimInput ? nimInput.value.trim() : '';
        const prodi = prodiInput ? prodiInput.value : '';
        if (!nim || !/^[0-9]{8,15}$/.test(nim)) {
            showError(nimInput, 'errNim', 'NIM harus berupa angka (8-15 digit).');
            hasError = true;
        }

        const payload = {
            nama: nama,
            email: email,
            password: pass,
            nim: nim,
            program_studi: prodi,
            nomor_telepon: telp
        };

        if (hasError) {
            showToast('Mohon perbaiki data pada kolom yang bertanda merah.', 'error');
            return;
        }

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Mendaftarkan Akun...';
        }

        try {
            const res = await window.sipekaAPI.register(payload);
            if (res && res.success) {
                showToast(res.message || 'Pendaftaran berhasil! Mengalihkan ke dashboard...', 'success');
                setTimeout(() => {
                    window.location.href = res.redirect || 'login.php';
                }, 1000);
            } else {
                showToast(res ? res.message : 'Registrasi gagal. Silakan periksa kembali formulir.', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'DAFTAR';
                }
            }
        } catch (err) {
            showToast('Terjadi kesalahan jaringan atau server.', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = 'DAFTAR';
            }
        }
    });
}