/**
 * SIPEKA Application Controller
 * Mengintegrasikan API Layer, UI Utilities, dan Interaktivitas Dinamis di Seluruh Halaman.
 */

document.addEventListener('DOMContentLoaded', async () => {
  // Ambil profil sesi aktif
  if (window.sipekaAPI) {
    await window.sipekaAPI.getSession();
  }
  renderUserProfile();

  const path = window.location.pathname;

  if ((path.includes('dashboard.php') || path.includes('dashboard.html')) && path.includes('mahasiswa')) {
    initMahasiswaDashboard();
  } else if (path.includes('riwayat.php') || path.includes('riwayat.html')) {
    initRiwayatPage();
  } else if (path.includes('buat-laporan.php') || path.includes('buat-laporan.html')) {
    initBuatLaporanPage();
  } else if ((path.includes('dashboard.php') || path.includes('dashboard.html')) && path.includes('unit')) {
    initUnitDashboard();
  } else if (path.includes('login.php') || path.includes('login.html')) {
    initLoginPage();
  } else if (path.includes('register.php')) {
    initRegisterPage();
  } else if (path.includes('profil.php')) {
    initProfilePage();
  } else if (path.includes('verifikasi.php')) {
    initVerifikasiPage();
  } else if (path.includes('penanganan.php')) {
    initPenangananPage();
  }
});

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
function getUnitDashboardPath(kodeUnit, relativePrefix = '..') {
  const map = {
    'sekjur': `${relativePrefix}/unit/sekjur/dashboard.php`,
    'prodi': `${relativePrefix}/unit/admin-prodi/dashboard.php`,
    'kajur': `${relativePrefix}/unit/kajur/dashboard.php`,
    'kps': `${relativePrefix}/unit/kps/dashboard.php`
  };
  return map[kodeUnit] || `${relativePrefix}/unit/sekjur/dashboard.php`;
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
        ? `NIM: ${user.nim || '254107020095'} (${user.prodi || 'TI-2F'})` 
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

// ==============================================================================
// 1. LOGIN PAGE CONTROLLER
// ==============================================================================
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
      submitBtn.textContent = 'Memverifikasi...';
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
          submitBtn.textContent = 'LOGIN KE SISTEM';
        }
      }
    } catch (err) {
      showToast('Terjadi kesalahan pada sistem otentikasi.', 'error');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'LOGIN KE SISTEM';
      }
    }
  });
}

// ==============================================================================
// 2. REGISTRATION PAGE CONTROLLER (DUAL ROLE: MAHASISWA & ADMIN UNIT)
// ==============================================================================
function initRegisterPage() {
  const form = document.getElementById('registerForm');
  if (!form) return;

  const roleInput = document.getElementById('selectedRole');
  const namaInput = document.getElementById('regNama');
  const nimInput = document.getElementById('regNim');
  const prodiInput = document.getElementById('regProdi');
  const unitSelect = document.getElementById('regKodeUnit');
  const nipInput = document.getElementById('regNip');
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

    const role = roleInput ? roleInput.value : 'mahasiswa';
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

    if (!pass || pass.length < 6) {
      showError(passInput, 'errPassword', 'Kata sandi minimal 6 karakter.');
      hasError = true;
    }

    if (pass !== confirm) {
      showError(confirmInput, 'errConfirm', 'Konfirmasi kata sandi tidak cocok.');
      hasError = true;
    }

    const payload = {
      role: role,
      nama: nama,
      email: email,
      nomor_telepon: telp,
      password: pass,
      confirm_password: confirm
    };

    if (role === 'unit') {
      const kodeUnit = unitSelect ? unitSelect.value : 'sekjur';
      const nip = nipInput ? nipInput.value.trim() : '';
      if (!nip) {
        showError(nipInput, 'errNip', 'NIP atau nomor identitas dinas wajib diisi.');
        hasError = true;
      }
      payload.kode_unit = kodeUnit;
      payload.nip = nip;
    } else {
      const nim = nimInput ? nimInput.value.trim() : '';
      const prodi = prodiInput ? prodiInput.value : 'D4 Teknik Informatika';
      if (!nim || !/^[0-9]{8,15}$/.test(nim)) {
        showError(nimInput, 'errNim', 'NIM harus berupa angka (8-15 digit).');
        hasError = true;
      }
      payload.nim = nim;
      payload.program_studi = prodi;
      payload.jurusan = 'Teknologi Informasi';
    }

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
          const defaultUnitPath = (role === 'unit') ? getUnitDashboardPath(payload.kode_unit) : '../mahasiswa/dashboard.php';
          window.location.href = res.redirect || defaultUnitPath;
        }, 1000);
      } else {
        showToast(res ? res.message : 'Registrasi gagal. Silakan periksa kembali formulir.', 'error');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = (role === 'unit') ? 'DAFTAR SEBAGAI PETUGAS UNIT' : 'DAFTAR SEBAGAI MAHASISWA';
        }
      }
    } catch (err) {
      showToast('Terjadi kesalahan jaringan atau server.', 'error');
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = (role === 'unit') ? 'DAFTAR SEBAGAI PETUGAS UNIT' : 'DAFTAR SEBAGAI MAHASISWA';
      }
    }
  });
}

// ==============================================================================
// 3. PROFILE PAGE CONTROLLER (MAHASISWA & UNIT)
// ==============================================================================
async function initProfilePage() {
  const profile = await window.sipekaAPI.getProfile();
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
    if (emailEl) emailEl.textContent = profile.email || 'unit@polinema.ac.id';

    const editNama = document.getElementById('editNama');
    const editTelp = document.getElementById('editTelepon');
    if (editNama) editNama.value = profile.nama || '';
    if (editTelp) editTelp.value = profile.nomor_telepon || '';

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
      const telp = document.getElementById('editTelepon').value.trim();

      if (!nama || nama.length < 3) {
        showToast('Nama lengkap minimal 3 karakter.', 'error');
        return;
      }

      const res = await window.sipekaAPI.updateProfile({ nama: nama, nomor_telepon: telp });
      if (res && res.success) {
        showToast(res.message || 'Profil berhasil diperbarui!', 'success');
        renderUserProfile();
        if (cardNamaEl) cardNamaEl.textContent = nama;
        if (avatarEl) avatarEl.textContent = nama.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
      } else {
        showToast(res ? res.message : 'Gagal memperbarui profil.', 'error');
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

      if (newPass.length < 6) {
        showToast('Kata sandi baru minimal 6 karakter.', 'error');
        return;
      }

      if (newPass !== confirmPass) {
        showToast('Konfirmasi kata sandi baru tidak cocok.', 'error');
        return;
      }

      const res = await window.sipekaAPI.changePassword(oldPass, newPass, confirmPass);
      if (res && res.success) {
        showToast('Kata sandi berhasil diperbarui!', 'success');
        changePassForm.reset();
      } else {
        showToast(res ? res.message : 'Gagal memperbarui kata sandi. Periksa kata sandi lama Anda.', 'error');
      }
    });
  }
}

// ==============================================================================
// 4. MAHASISWA DASHBOARD
// ==============================================================================
async function initMahasiswaDashboard() {
  const stats = await window.sipekaAPI.getStats();
  const reports = await window.sipekaAPI.getReports();

  // Render Metric Counters
  const totalEl = document.getElementById('statTotal');
  const prosesEl = document.getElementById('statProses');
  const selesaiEl = document.getElementById('statSelesai');
  const ditolakEl = document.getElementById('statDitolak');

  if (totalEl) totalEl.textContent = stats.total ?? 0;
  if (prosesEl) prosesEl.textContent = (stats.proses ?? 0) + (stats.menunggu ?? 0);
  if (selesaiEl) selesaiEl.textContent = stats.selesai ?? 0;
  if (ditolakEl) ditolakEl.textContent = stats.ditolak ?? 0;

  // Render Recent Table (Max 5 items)
  const tbody = document.getElementById('recentReportsBody');
  if (!tbody) return;

  if (!reports || reports.length === 0) {
    tbody.innerHTML = getEmptyStateHtml('Belum Ada Pengaduan', 'Anda belum pernah mengajukan pengaduan. Klik tombol "Buat Pengaduan" di atas untuk memulai laporan baru.');
    return;
  }

  tbody.innerHTML = reports.slice(0, 5).map(r => `
    <tr>
      <td><strong>${r.no_tiket}</strong></td>
      <td>${r.kategori || r.nama_kategori}</td>
      <td>${r.judul_laporan}</td>
      <td>${formatDate(r.tanggal_kejadian)}</td>
      <td>${getStatusBadge(r.status)}</td>
      <td>
        <a href="riwayat.php?tiket=${r.no_tiket}" class="btn-detail">Detail</a>
      </td>
    </tr>
  `).join('');
}

// ==============================================================================
// 5. BUAT LAPORAN PAGE (DIRECT ROUTING INTEGRATION)
// ==============================================================================
function initBuatLaporanPage() {
  const urlParams = new URLSearchParams(window.location.search);
  const kategoriKey = urlParams.get('kategori') || 'fasilitas';

  const categoryConfigs = {
    'fasilitas': { name: 'Fasilitas Kampus', unit: 'Sekretariat Jurusan (Sekjur)' },
    'akademik': { name: 'Pelayanan Akademik', unit: 'Admin Program Studi (Admin Prodi)' },
    'dosen': { name: 'Perilaku Dosen atau Staf', unit: 'Ketua Jurusan (Kajur)' },
    'pungli': { name: 'Pungutan Liar (Pungli)', unit: 'Ketua Jurusan (Kajur)' },
    'mahasiswa': { name: 'Pelanggaran Mahasiswa', unit: 'Ketua Program Studi (KPS)' }
  };

  const config = categoryConfigs[kategoriKey] || categoryConfigs['fasilitas'];
  const kategoriText = document.getElementById('kategoriText');
  if (kategoriText) {
    kategoriText.value = `${config.name} (Disposisi otomatis: ${config.unit})`;
  }

  // Handle Form Submission
  const form = document.getElementById('reportForm');
  const fileInput = document.getElementById('fileUpload');

  if (form) {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const judul = document.getElementById('judul').value.trim();
      const lokasi = document.getElementById('lokasi').value.trim();
      const tanggal = document.getElementById('tanggal').value;
      const kronologi = document.getElementById('kronologi').value.trim();

      // Client-side validations
      if (judul.length < 10) {
        showToast('Judul pengaduan terlalu pendek (minimal 10 karakter).', 'error');
        document.getElementById('judul').focus();
        return;
      }

      if (!lokasi) {
        showToast('Lokasi kejadian wajib diisi.', 'error');
        document.getElementById('lokasi').focus();
        return;
      }

      if (!tanggal) {
        showToast('Tanggal kejadian wajib diisi.', 'error');
        return;
      }

      const today = new Date().toISOString().split('T')[0];
      if (tanggal > today) {
        showToast('Tanggal kejadian tidak boleh lebih dari hari ini.', 'error');
        return;
      }

      if (kronologi.length < 30) {
        showToast('Kronologi terlalu singkat. Jelaskan masalah minimal 30 karakter.', 'error');
        document.getElementById('kronologi').focus();
        return;
      }
      
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Mengirim Laporan...';
      }

      const rawFile = fileInput && fileInput.files.length > 0 ? fileInput.files[0] : null;

      const payload = {
        kategoriKey: kategoriKey,
        kategoriName: config.name,
        judul: judul,
        lokasi: lokasi,
        tanggal: tanggal,
        kronologi: kronologi,
        bukti_file: rawFile ? rawFile.name : 'foto-bukti.jpg'
      };

      try {
        const res = await window.sipekaAPI.createReport(payload, rawFile);
        if (res && res.success) {
          const tiket = res.data ? res.data.no_tiket : 'KP-Baru';
          showToast(`Pengaduan ${tiket} berhasil diajukan!`, 'success');
          setTimeout(() => {
            window.location.href = `riwayat.php?tiket=${tiket}&success=true`;
          }, 800);
        } else {
          showToast(res ? res.message : 'Gagal mengirim pengaduan.', 'error');
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Kirim Pengaduan Sekarang ✓';
          }
        }
      } catch (err) {
        showToast('Terjadi kesalahan saat memproses laporan.', 'error');
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Kirim Pengaduan Sekarang ✓';
        }
      }
    });
  }
}

// ==============================================================================
// 6. RIWAYAT & TRACKING PAGE
// ==============================================================================
async function initRiwayatPage() {
  const urlParams = new URLSearchParams(window.location.search);
  const targetTiket = urlParams.get('tiket');
  if (urlParams.get('success') === 'true') {
    showToast('Laporan berhasil dikirim dan masuk dalam status antrean validasi!', 'success');
  }

  const searchInput = document.getElementById('searchReport');
  const statusFilter = document.getElementById('statusFilter');
  const tbody = document.getElementById('riwayatTableBody');

  async function loadAndRender() {
    const query = searchInput ? searchInput.value.trim() : '';
    const statusVal = statusFilter ? statusFilter.value : 'all';

    const reports = await window.sipekaAPI.getReports({
      q: query,
      status: statusVal
    });

    if (!tbody) return;
    if (!reports || reports.length === 0) {
      tbody.innerHTML = getEmptyStateHtml('Tidak Ada Laporan Ditemukan', 'Tidak ada data laporan yang cocok dengan kata kunci atau filter status yang dipilih.');
      return;
    }

    tbody.innerHTML = reports.map(r => {
      const unitName = (r.nama_unit || r.unit_tujuan || 'Sekjur').split('(')[0];
      return `
        <tr>
          <td><strong>${r.no_tiket}</strong></td>
          <td>${r.nama_kategori || r.kategori}</td>
          <td><span style="font-size: 0.8rem; font-weight: 600; color: #1e3a8a;">${unitName}</span></td>
          <td>${r.judul_laporan}</td>
          <td>${formatDate(r.tanggal_kejadian)}</td>
          <td>${getStatusBadge(r.status)}</td>
          <td>
            <button class="btn-detail" onclick="viewReportDetail('${r.no_tiket}')">Lacak Linimasa</button>
          </td>
        </tr>
      `;
    }).join('');
  }

  if (searchInput) searchInput.addEventListener('input', () => loadAndRender());
  if (statusFilter) statusFilter.addEventListener('change', () => loadAndRender());

  await loadAndRender();

  if (targetTiket) {
    setTimeout(() => viewReportDetail(targetTiket), 300);
  }
}

// View Timeline Tracking Modal
window.viewReportDetail = async function(noTiket) {
  const report = await window.sipekaAPI.getReportDetail(noTiket);
  if (!report) {
    showToast('Detail laporan tidak dapat dimuat.', 'error');
    return;
  }

  const modal = document.getElementById('trackingModal');
  if (!modal) return;

  document.getElementById('modalTiket').textContent = `Tiket #${report.no_tiket}`;
  document.getElementById('modalJudul').textContent = report.judul_laporan;
  document.getElementById('modalKategori').textContent = report.nama_kategori || report.kategori;
  document.getElementById('modalUnit').textContent = report.nama_unit || report.unit_tujuan;
  document.getElementById('modalLokasi').textContent = report.lokasi;
  document.getElementById('modalKronologi').textContent = report.kronologi;
  document.getElementById('modalStatusBadge').innerHTML = getStatusBadge(report.status);

  // Render Timeline
  const timelineContainer = document.getElementById('modalTimeline');
  if (timelineContainer && report.riwayat) {
    timelineContainer.innerHTML = report.riwayat.map((step, idx) => {
      const isDone = idx < report.riwayat.length - 1 || report.status === 'Selesai';
      return `
        <div class="timeline-item">
          <div class="timeline-dot ${isDone ? 'done' : ''}"></div>
          <div class="timeline-content">
            <h5>${step.status || step.status_text}</h5>
            <div class="time">${step.waktu || formatDate(step.tanggal_perubahan)}</div>
            <p>${step.keterangan}</p>
          </div>
        </div>
      `;
    }).join('');
  }

  openModal('trackingModal');
};

// ==============================================================================
// 7. UNIT DASHBOARD & DISPOSITION CONTROLLER
// ==============================================================================
async function initUnitDashboard() {
  const explicitUnit = document.body.dataset.unit || getUnitFromPath();
  const unitFilter = document.getElementById('unitFilterSelect');
  const searchInput = document.getElementById('searchUnitReports');
  const statusFilter = document.getElementById('statusFilterUnit');
  const tbody = document.getElementById('unitReportsBody');

  async function loadUnitReports() {
    const selectedUnit = explicitUnit || (unitFilter ? unitFilter.value : 'all');
    const query = searchInput ? searchInput.value.trim() : '';
    const statusVal = statusFilter ? statusFilter.value : 'all';

    const reports = await window.sipekaAPI.getReports({
      unit: selectedUnit,
      status: statusVal,
      q: query
    });

    const stats = await window.sipekaAPI.getStats(selectedUnit);

    const mValidasi = document.getElementById('unitStatValidasi');
    const mProses = document.getElementById('unitStatProses');
    const mSelesai = document.getElementById('unitStatSelesai');

    if (mValidasi) mValidasi.textContent = stats.menunggu ?? 0;
    if (mProses) mProses.textContent = stats.proses ?? 0;
    if (mSelesai) mSelesai.textContent = stats.selesai ?? 0;

    if (!tbody) return;
    if (!reports || reports.length === 0) {
      tbody.innerHTML = getEmptyStateHtml('Tidak Ada Laporan Masuk', 'Tidak ada laporan pengaduan pada unit ini saat ini. Semua laporan telah selesai diproses atau belum ada keluhan baru.');
      return;
    }

    tbody.innerHTML = reports.map(r => {
      const pelaporNama = r.nama_pelapor || r.nama_mahasiswa || 'Mahasiswa JTI';
      const pelaporNim = r.nim_pelapor || r.nim || '254107020095';

      return `
        <tr style="${r.status === 'Menunggu Validasi' ? 'background-color: #fffbeb;' : ''}">
          <td><strong>${r.no_tiket}</strong></td>
          <td>
            <div style="font-weight: 600;">${pelaporNama}</div>
            <div style="font-size: 0.75rem; color: var(--text-muted);">${pelaporNim}</div>
          </td>
          <td>${r.judul_laporan}</td>
          <td>${formatDate(r.tanggal_kejadian)}</td>
          <td>${getStatusBadge(r.status)}</td>
          <td>
            ${r.status === 'Menunggu Validasi' ? 
              `<button class="btn-action" style="padding: 0.35rem 0.8rem; font-size: 0.75rem;" onclick="openValidationModal('${r.no_tiket}')">Validasi</button>` :
              `<button class="btn-detail" onclick="openResolutionModal('${r.no_tiket}')">Kelola Penanganan</button>`
            }
          </td>
        </tr>
      `;
    }).join('');
  }

  if (unitFilter) unitFilter.addEventListener('change', () => loadUnitReports());
  if (searchInput) searchInput.addEventListener('input', () => loadUnitReports());
  if (statusFilter) statusFilter.addEventListener('change', () => loadUnitReports());

  await loadUnitReports();
}

// Modal Validasi (Unit Staff)
window.openValidationModal = async function(noTiket) {
  const report = await window.sipekaAPI.getReportDetail(noTiket);
  if (!report) return;

  const tEl = document.getElementById('valTiket');
  const pelaporEl = document.getElementById('valPelapor');
  const judulEl = document.getElementById('valJudul');
  const lokasiEl = document.getElementById('valLokasi');
  const kronEl = document.getElementById('valKronologi');

  if (tEl) tEl.textContent = report.no_tiket;
  if (pelaporEl) pelaporEl.textContent = `${report.nama_pelapor || report.nama_mahasiswa} (${report.nim_pelapor || report.nim})`;
  if (judulEl) judulEl.textContent = report.judul_laporan;
  if (lokasiEl) lokasiEl.textContent = report.lokasi;
  if (kronEl) kronEl.textContent = report.kronologi;

  const btnApprove = document.getElementById('btnApprove');
  if (btnApprove) {
    btnApprove.onclick = async () => {
      await window.sipekaAPI.validateReport(noTiket, 'approve', 'Staff Unit memvalidasi kebenaran laporan. Penugasan teknisi atau tindak lanjut telah dijadwalkan.');
      showToast(`Tiket #${noTiket} telah divalidasi dan masuk status Penanganan!`, 'success');
      closeModal('validationModal');
      initUnitDashboard();
    };
  }

  const btnReject = document.getElementById('btnReject');
  if (btnReject) {
    btnReject.onclick = async () => {
      const reason = prompt('Masukkan alasan penolakan laporan:');
      if (reason) {
        await window.sipekaAPI.validateReport(noTiket, 'reject', `Laporan ditolak oleh unit: ${reason}`);
        showToast(`Tiket #${noTiket} ditandai Tidak Valid.`, 'error');
        closeModal('validationModal');
        initUnitDashboard();
      }
    };
  }

  openModal('validationModal');
};

// Modal Penanganan (Unit Staff)
window.openResolutionModal = async function(noTiket) {
  const report = await window.sipekaAPI.getReportDetail(noTiket);
  if (!report) return;

  const resTiket = document.getElementById('resTiket');
  const resJudul = document.getElementById('resJudul');
  const resLokasi = document.getElementById('resLokasi');

  if (resTiket) resTiket.textContent = report.no_tiket;
  if (resJudul) resJudul.textContent = report.judul_laporan;
  if (resLokasi) resLokasi.textContent = report.lokasi;

  const form = document.getElementById('resolutionForm');
  if (form) {
    form.onsubmit = async (e) => {
      e.preventDefault();
      const tindakan = document.getElementById('resTindakan').value.trim();
      const fileProof = form.querySelector('input[type="file"]');
      const rawProof = fileProof && fileProof.files.length > 0 ? fileProof.files[0] : null;

      await window.sipekaAPI.resolveReport(noTiket, tindakan, rawProof);
      showToast(`Tiket #${noTiket} berhasil ditandai SELESAI!`, 'success');
      closeModal('resolutionModal');
      initUnitDashboard();
    };
  }

  openModal('resolutionModal');
};

// ==============================================================================
// 8. DEDICATED VERIFIKASI & PENANGANAN PAGES
// ==============================================================================
async function initVerifikasiPage() {
  const urlParams = new URLSearchParams(window.location.search);
  const pathUnit = getUnitFromPath();
  const reports = await window.sipekaAPI.getReports({ unit: pathUnit, status: 'Menunggu Validasi' });
  let targetTiket = urlParams.get('tiket');

  if (!targetTiket && reports && reports.length > 0) {
    targetTiket = reports[0].no_tiket;
  }

  if (!targetTiket) return;
  const report = await window.sipekaAPI.getReportDetail(targetTiket);
  if (!report) return;

  const tEl = document.getElementById('verifTiket');
  const pelaporEl = document.getElementById('verifPelapor');
  const katEl = document.getElementById('verifKategori');
  const judulEl = document.getElementById('verifJudul');
  const lokasiEl = document.getElementById('verifLokasi');
  const kronEl = document.getElementById('verifKronologi');

  if (tEl) tEl.textContent = report.no_tiket;
  if (pelaporEl) pelaporEl.textContent = `${report.nama_pelapor || report.nama_mahasiswa || 'Mahasiswa JTI'} (NIM: ${report.nim_pelapor || report.nim || '-'})`;
  if (katEl) katEl.textContent = report.nama_kategori || report.kategori || '-';
  if (judulEl) judulEl.textContent = report.judul_laporan;
  if (lokasiEl) lokasiEl.textContent = report.lokasi;
  if (kronEl) kronEl.textContent = report.kronologi;

  const btnApprove = document.getElementById('btnApprovePage');
  const btnReject = document.getElementById('btnRejectPage');

  if (btnApprove) {
    btnApprove.onclick = async (e) => {
      e.preventDefault();
      await window.sipekaAPI.validateReport(report.no_tiket, 'approve', 'Laporan telah divalidasi oleh petugas unit.');
      showToast(`Laporan ${report.no_tiket} berhasil disetujui!`, 'success');
      setTimeout(() => window.location.href = 'penanganan.php?tiket=' + report.no_tiket, 700);
    };
  }

  if (btnReject) {
    btnReject.onclick = async (e) => {
      e.preventDefault();
      const reason = prompt('Masukkan alasan penolakan laporan:');
      if (reason) {
        await window.sipekaAPI.validateReport(report.no_tiket, 'reject', `Ditolak unit: ${reason}`);
        showToast(`Laporan ${report.no_tiket} ditandai tidak valid.`, 'error');
        setTimeout(() => window.location.href = 'dashboard.php', 700);
      }
    };
  }
}

async function initPenangananPage() {
  const urlParams = new URLSearchParams(window.location.search);
  const pathUnit = getUnitFromPath();
  const reports = await window.sipekaAPI.getReports({ unit: pathUnit });
  let targetTiket = urlParams.get('tiket');

  if (!targetTiket && reports && reports.length > 0) {
    const activeReport = reports.find(r => r.status === 'Dalam Penanganan') || reports[0];
    targetTiket = activeReport.no_tiket;
  }

  if (!targetTiket) return;
  const report = await window.sipekaAPI.getReportDetail(targetTiket);
  if (!report) return;

  const tEl = document.getElementById('penangTiket');
  const judulEl = document.getElementById('penangJudul');
  const lokasiEl = document.getElementById('penangLokasi');
  const katEl = document.getElementById('penangKategori');

  if (tEl) tEl.textContent = report.no_tiket;
  if (judulEl) judulEl.textContent = report.judul_laporan;
  if (lokasiEl) lokasiEl.textContent = report.lokasi;
  if (katEl) katEl.textContent = report.nama_kategori || report.kategori || '-';

  const form = document.getElementById('pageResolutionForm');
  if (form) {
    form.onsubmit = async (e) => {
      e.preventDefault();
      const tindakanEl = document.getElementById('pageResTindakan');
      const tindakan = tindakanEl ? tindakanEl.value.trim() : 'Tindakan penanganan telah diselesaikan.';
      const fileProof = form.querySelector('input[type="file"]');
      const rawProof = fileProof && fileProof.files.length > 0 ? fileProof.files[0] : null;

      await window.sipekaAPI.resolveReport(report.no_tiket, tindakan, rawProof);
      showToast(`Tiket #${report.no_tiket} berhasil ditandai SELESAI!`, 'success');
      setTimeout(() => window.location.href = 'dashboard.php', 700);
    };
  }
}
