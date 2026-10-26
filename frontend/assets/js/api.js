const API_BASE_PATH = '../../../backend/api/auth';

window.sipekaAPI = {
  login: async function (usernameOrEmail, password, role) {
    try {
      const response = await fetch(`${API_BASE_PATH}/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include', // Wajib agar session PHP cookies tersimpan
        body: JSON.stringify({
          email: usernameOrEmail,
          password: password
        })
      });

      const data = await response.json();

      if (response.ok) {
        // Simpan data user ke store/local storage
        if (window.sipekaStore) {
          window.sipekaStore.setUser(data);
        }
        return {
          success: true,
          user: data,
          // Sesuaikan arah redirect berdasarkan role dari database
          redirect: data.role === 'unit' ? '../unit/sekjur/dashboard.php' : '../mahasiswa/dashboard.php'
        };
      } else {
        return {
          success: false,
          message: data.message || 'Login gagal. Periksa kembali email/NIM dan password.'
        };
      }
    } catch (err) {
      return { success: false, message: 'Terjadi kesalahan jaringan ke server.' };
    }
  },

  // 2. REGISTER (Menuju regstrasi.php sesuai nama file aslimu)
  register: async function (payload) {
    try {
      const response = await fetch(`${API_BASE_PATH}/regstrasi.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({
          nama: payload.nama,
          email: payload.email,
          password: payload.password,
          nim: payload.nim,
          program_studi: payload.program_studi,
          nomor_telepon: payload.nomor_telepon
        })
      });

      const data = await response.json();

      if (response.ok) {
        return {
          success: true,
          message: data.message || 'Registrasi berhasil!',
          redirect: '../auth/login.php'
        };
      } else {
        return {
          success: false,
          message: data.message || 'Registrasi gagal.'
        };
      }
    } catch (err) {
      return { success: false, message: 'Terjadi kesalahan jaringan atau server.' };
    }
  },

  // 3. GET SESSION / CEK LOGIN (Menuju validasi.php)
  getSession: async function () {
    try {
      const response = await fetch(`${API_BASE_PATH}/validasi.php`, {
        method: 'GET',
        credentials: 'include'
      });

      if (response.ok) {
        const user = await response.json();
        if (window.sipekaStore) {
          window.sipekaStore.setUser(user);
        }
        return user;
      }
      return null;
    } catch (err) {
      return null;
    }
  },

  // 4. LOGOUT (Menuju logout.php)
  logout: async function () {
    try {
      const response = await fetch(`${API_BASE_PATH}/logout.php`, {
        method: 'POST',
        credentials: 'include'
      });
      const data = await response.json();
      if (window.sipekaStore) {
        window.sipekaStore.logout();
      }
      return data;
    } catch (err) {
      return { message: 'Gagal melakukan logout.' };
    }
  },

  // --- STUB / LOCAL STORAGE HELPER UNTUK FITUR DASHBOARD & LAPORAN ---
  // (Bagian ini menggunakan store.js lokal agar halaman mahasiswa & unit tetap berjalan mulus)
  getProfile: async function () {
    return window.sipekaStore ? window.sipekaStore.getUser() : null;
  },

  updateProfile: async function (data) {
    const user = window.sipekaStore ? window.sipekaStore.getUser() : {};
    const updated = { ...user, ...data };
    if (window.sipekaStore) window.sipekaStore.setUser(updated);
    return { success: true, message: 'Profil berhasil diperbarui!' };
  },

  changePassword: async function () {
    return { success: true, message: 'Kata sandi berhasil diperbarui!' };
  },

  getStats: async function (unitId) {
    return window.sipekaStore ? window.sipekaStore.getStats(unitId) : { total: 0, menunggu: 0, proses: 0, selesai: 0, ditolak: 0 };
  },

  getReports: async function (filter) {
    if (!window.sipekaStore) return [];
    let reports = window.sipekaStore.getAllReports();
    if (filter && filter.status && filter.status !== 'all') {
      reports = reports.filter(r => r.status === filter.status);
    }
    if (filter && filter.unit && filter.unit !== 'all') {
      reports = reports.filter(r => r.unit_id === filter.unit);
    }
    if (filter && filter.q) {
      const q = filter.q.toLowerCase();
      reports = reports.filter(r => r.no_tiket.toLowerCase().includes(q) || r.judul_laporan.toLowerCase().includes(q));
    }
    return reports;
  },

  getReportDetail: async function (noTiket) {
    return window.sipekaStore ? window.sipekaStore.getReportById(noTiket) : null;
  },

  createReport: async function (payload) {
    if (window.sipekaStore) {
      const newReport = window.sipekaStore.createReport(payload);
      return { success: true, data: newReport };
    }
    return { success: false, message: 'Gagal membuat laporan' };
  },

  validateReport: async function (noTiket, action, ket) {
    if (window.sipekaStore) {
      const status = action === 'approve' ? 'Dalam Penanganan' : 'Tidak Valid';
      window.sipekaStore.updateStatus(noTiket, status, ket);
      return { success: true };
    }
    return { success: false };
  },

  resolveReport: async function (noTiket, tindakan) {
    if (window.sipekaStore) {
      window.sipekaStore.updateStatus(noTiket, 'Selesai', tindakan, {
        penanganan: { tindakan: tindakan, teknisi: 'Petugas Unit' }
      });
      return { success: true };
    }
    return { success: false };
  }
};