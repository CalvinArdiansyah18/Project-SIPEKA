/**
 * SIPEKA User Cache
 * Menyimpan salinan data user yang sedang login di localStorage supaya nama/NIM/unit
 * bisa langsung tampil di topbar tanpa menunggu respons server.
 * Sumber kebenaran tetap DATABASE: isi cache ini selalu ditimpa dari backend
 * (login.php, validasi.php, profil.php). Data laporan TIDAK disimpan di sini.
 */

const SIPEKA_USER_KEY = 'sipeka_active_user';
const SIPEKA_LEGACY_REPORTS_KEY = 'sipeka_reports_data';
class SipekaStore {
  constructor() {
    try {
      localStorage.removeItem(SIPEKA_LEGACY_REPORTS_KEY);
    } catch (e) {
      // localStorage tidak tersedia (mode privat ketat): cache user saja yang tidak berfungsi
    }
  }

  getUser() {
    try {
      return JSON.parse(localStorage.getItem(SIPEKA_USER_KEY)) || null;
    } catch (e) {
      return null;
    }
  }

  setUser(user) {
    try {
      localStorage.setItem(SIPEKA_USER_KEY, JSON.stringify(user));
    } catch (e) {
      // diabaikan: cache gagal disimpan, data tetap diambil ulang dari server
    }
  }

  logout() {
    try {
      localStorage.removeItem(SIPEKA_USER_KEY);
    } catch (e) {
      // diabaikan
    }
  }
}

// Global Store Instance
window.sipekaStore = new SipekaStore();