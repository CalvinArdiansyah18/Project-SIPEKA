/**
 * SIPEKA Central Data Store & Local Persistence Layer
 * Menangani CRUD data pengaduan, otentikasi sesi, dan sinkronisasi ke localStorage.
 * Struktur data diselaraskan 100% dengan skema PostgreSQL pada proposal.
 */

const SIPEKA_STORAGE_KEY = 'sipeka_reports_data';
const SIPEKA_USER_KEY = 'sipeka_active_user';

// Data Awal (Seed Data)
const INITIAL_REPORTS = [
  {
    id: 1,
    no_tiket: 'KP-0013',
    kategori: 'Fasilitas Kampus',
    unit_tujuan: 'Sekretariat Jurusan (Sekjur)',
    unit_id: 'sekjur',
    judul_laporan: 'Kerusakan Proyektor & Stop Kontak Lab 3',
    lokasi: 'Gedung Sipil Lt. 7 - Lab Komputer 3',
    tanggal_kejadian: '2026-10-06',
    kronologi: 'Proyektor mati mendadak saat sesi praktikum dan stop kontak di baris tengah mengeluarkan percikan kecil.',
    tanggal_lapor: '2026-10-06 11:20',
    nama_pelapor: 'Galih Maula Mumtaza',
    nim_pelapor: '254107020095',
    prodi: 'D4 Teknik Informatika (TI-2F)',
    status: 'Menunggu Validasi',
    bukti_file: 'foto-proyektor-lab3.jpg',
    tanggapan: null,
    penanganan: null,
    riwayat: [
      {
        status: 'Laporan Diajukan',
        waktu: '6 Okt 2026, 11:20 WIB',
        keterangan: 'Laporan berhasil dibuat oleh mahasiswa dan diteruskan ke Sekretariat Jurusan.'
      }
    ]
  },
  {
    id: 2,
    no_tiket: 'KP-0012',
    kategori: 'Fasilitas Kampus',
    unit_tujuan: 'Sekretariat Jurusan (Sekjur)',
    unit_id: 'sekjur',
    judul_laporan: 'Kerusakan AC Gedung Sipil Lt. 4',
    lokasi: 'Gedung Sipil Lt. 4 Ruang 4.02',
    tanggal_kejadian: '2026-05-10',
    kronologi: 'AC tidak dingin dan mengeluarkan tetesan air cukup deras di atas meja mahasiswa.',
    tanggal_lapor: '2026-05-10 09:15',
    nama_pelapor: 'Salsabila Widyadhana',
    nim_pelapor: '254107020200',
    prodi: 'D4 Teknik Informatika (TI-2F)',
    status: 'Dalam Penanganan',
    bukti_file: 'foto-ac-bocor.jpg',
    tanggapan: 'Laporan telah diverifikasi oleh tim sarpras. Surat perbaikan sudah diterbitkan.',
    penanganan: {
      teknisi: 'Pak Joko (Teknisi Sarpras)',
      tindakan: 'Pembersihan filter dan pengisian freon sedang dijadwalkan.',
      bukti_selesai: null
    },
    riwayat: [
      {
        status: 'Laporan Diajukan',
        waktu: '10 Mei 2025, 09:15 WIB',
        keterangan: 'Laporan dibuat oleh mahasiswa.'
      },
      {
        status: 'Validasi Disetujui',
        waktu: '10 Mei 2025, 11:30 WIB',
        keterangan: 'Staff Sekjur memvalidasi laporan dan menugaskan teknisi sarpras.'
      }
    ]
  },
  {
    id: 3,
    no_tiket: 'KP-0011',
    kategori: 'Pelayanan Akademik',
    unit_tujuan: 'Admin Program Studi (Admin Prodi)',
    unit_id: 'prodi',
    judul_laporan: 'Koreksi Nilai Praktikum Basis Data',
    lokasi: 'Ruang Dosen JTI',
    tanggal_kejadian: '2026-05-08',
    kronologi: 'Nilai tugas praktikum 3 belum terinput di portal akademik padahal sudah dinilai oleh asisten lab.',
    tanggal_lapor: '2026-05-08 14:00',
    nama_pelapor: 'Calvin Ardiansyah',
    nim_pelapor: '254107020214',
    prodi: 'D4 Teknik Informatika (TI-2F)',
    status: 'Selesai',
    bukti_file: 'screenshot-portal.png',
    tanggapan: 'Nilai telah diverifikasi dengan dosen pengampu dan telah diperbarui di sistem.',
    penanganan: {
      teknisi: 'Admin Prodi TI',
      tindakan: 'Sinkronisasi nilai dari sistem praktikum ke portal nilai semester.',
      bukti_selesai: 'berita-acara-nilai.pdf'
    },
    riwayat: [
      {
        status: 'Laporan Diajukan',
        waktu: '8 Mei 2025, 14:00 WIB',
        keterangan: 'Laporan dibuat oleh mahasiswa.'
      },
      {
        status: 'Dalam Penanganan',
        waktu: '9 Mei 2025, 10:00 WIB',
        keterangan: 'Admin Prodi mengonfirmasi ke dosen terkait.'
      },
      {
        status: 'Selesai',
        waktu: '9 Mei 2025, 15:30 WIB',
        keterangan: 'Nilai telah terbarui di portal KHS.'
      }
    ]
  },
  {
    id: 4,
    no_tiket: 'KP-0014',
    kategori: 'Perilaku Dosen atau Staf',
    unit_tujuan: 'Ketua Jurusan (Kajur)',
    unit_id: 'kajur',
    judul_laporan: 'Transparansi Nilai & Kehadiran Perkuliahan',
    lokasi: 'Gedung Sipil Lt. 7 Ruang Teori 7.03',
    tanggal_kejadian: '2026-10-08',
    kronologi: 'Perkuliahan kosong selama 3 kali pertemuan tanpa ada kelas pengganti atau penugasan mandiri terstruktur.',
    tanggal_lapor: '2026-10-09 10:15',
    nama_pelapor: 'Ahmad Fauzi',
    nim_pelapor: '254107020088',
    prodi: 'D4 Sistem Informasi Bisnis (SIB-2A)',
    status: 'Dalam Penanganan',
    bukti_file: 'presensi-kuliah.pdf',
    tanggapan: 'Ketua Jurusan telah memanggil dosen pengampu untuk klarifikasi jadwal kuliah pengganti.',
    penanganan: {
      teknisi: 'Ketua Jurusan TI',
      tindakan: 'Penjadwalan 3 sesi kuliah pengganti pada minggu ke-10 dan ke-11.',
      bukti_selesai: null
    },
    riwayat: [
      {
        status: 'Laporan Diajukan',
        waktu: '9 Okt 2026, 10:15 WIB',
        keterangan: 'Laporan diajukan dan langsung diteruskan ke Ketua Jurusan.'
      },
      {
        status: 'Dalam Penanganan',
        waktu: '10 Okt 2026, 09:00 WIB',
        keterangan: 'Ketua Jurusan menindaklanjuti laporan bersama sekretaris jurusan.'
      }
    ]
  },
  {
    id: 5,
    no_tiket: 'KP-0015',
    kategori: 'Pelanggaran Mahasiswa',
    unit_tujuan: 'Ketua Program Studi (KPS)',
    unit_id: 'kps',
    judul_laporan: 'Indikasi Vandalisme di Fasilitas Lab Komputer',
    lokasi: 'Gedung Sipil Lt. 6 - Lab Jaringan',
    tanggal_kejadian: '2026-10-10',
    kronologi: 'Terdapat coretan cat semprot dan kerusakan pada keyboard komputer nomor 12 di Lab Jaringan.',
    tanggal_lapor: '2026-10-10 13:45',
    nama_pelapor: 'Naufal Rizky',
    nim_pelapor: '254107020112',
    prodi: 'D4 Teknik Informatika (TI-2E)',
    status: 'Menunggu Validasi',
    bukti_file: 'foto-vandalisme-lab.jpg',
    tanggapan: null,
    penanganan: null,
    riwayat: [
      {
        status: 'Laporan Diajukan',
        waktu: '10 Okt 2026, 13:45 WIB',
        keterangan: 'Laporan diteruskan ke Ketua Program Studi untuk verifikasi tata tertib.'
      }
    ]
  },
  {
    id: 6,
    no_tiket: 'KP-0016',
    kategori: 'Pungutan Liar (Pungli)',
    unit_tujuan: 'Ketua Jurusan (Kajur)',
    unit_id: 'kajur',
    judul_laporan: 'Pungutan Biaya Modul Praktikum Tak Resmi',
    lokasi: 'Ruang Kelas 4.05',
    tanggal_kejadian: '2026-10-11',
    kronologi: 'Penarikan biaya cetak modul tanpa kwitansi resmi dari pihak jurusan di luar ketentuan UKT.',
    tanggal_lapor: '2026-10-11 08:30',
    nama_pelapor: 'Rizka Nurhaliza',
    nim_pelapor: '254107020045',
    prodi: 'D3 Manajemen Informatika',
    status: 'Menunggu Validasi',
    bukti_file: 'bukti-transfer-modul.jpg',
    tanggapan: null,
    penanganan: null,
    riwayat: [
      {
        status: 'Laporan Diajukan',
        waktu: '11 Okt 2026, 08:30 WIB',
        keterangan: 'Laporan masuk dalam antrean verifikasi Ketua Jurusan.'
      }
    ]
  }
];

class SipekaStore {
  constructor() {
    this.init();
  }

  init() {
    const raw = localStorage.getItem(SIPEKA_STORAGE_KEY);
    if (!raw) {
      localStorage.setItem(SIPEKA_STORAGE_KEY, JSON.stringify(INITIAL_REPORTS));
    } else {
      try {
        const current = JSON.parse(raw);
        let updated = false;
        INITIAL_REPORTS.forEach(initR => {
          if (!current.some(c => c.no_tiket === initR.no_tiket)) {
            current.push(initR);
            updated = true;
          }
        });
        if (updated) {
          localStorage.setItem(SIPEKA_STORAGE_KEY, JSON.stringify(current));
        }
      } catch (e) {
        localStorage.setItem(SIPEKA_STORAGE_KEY, JSON.stringify(INITIAL_REPORTS));
      }
    }
    if (!localStorage.getItem(SIPEKA_USER_KEY)) {
      this.setUser({
        nama: 'Galih Maula Mumtaza',
        nim: '254107020095',
        role: 'mahasiswa',
        prodi: 'D4 Teknik Informatika',
        email: '254107020095@student.polinema.ac.id'
      });
    }
  }

  // --- USER SESSION ---
  getUser() {
    return JSON.parse(localStorage.getItem(SIPEKA_USER_KEY)) || null;
  }

  setUser(user) {
    localStorage.setItem(SIPEKA_USER_KEY, JSON.stringify(user));
  }

  logout() {
    localStorage.removeItem(SIPEKA_USER_KEY);
  }

  // --- CRUD PENGADUAN ---
  getAllReports() {
    const data = localStorage.getItem(SIPEKA_STORAGE_KEY);
    return data ? JSON.parse(data) : [];
  }

  getReportById(noTiketOrId) {
    const reports = this.getAllReports();
    return reports.find(r => r.no_tiket === noTiketOrId || r.id === parseInt(noTiketOrId));
  }

  getReportsByUnit(unitId) {
    const reports = this.getAllReports();
    if (!unitId || unitId === 'all') return reports;
    return reports.filter(r => r.unit_id === unitId);
  }

  createReport(data) {
    const reports = this.getAllReports();
    const nextId = reports.length > 0 ? Math.max(...reports.map(r => r.id)) + 1 : 1;
    const ticketNumber = `KP-${String(nextId).padStart(4, '0')}`;

    const now = new Date();
    const dateFormatted = now.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
    const timeFormatted = `${now.getHours()}:${String(now.getMinutes()).padStart(2, '0')}`;

    // Direct routing unit map
    const unitMap = {
      'fasilitas': { name: 'Sekretariat Jurusan (Sekjur)', id: 'sekjur' },
      'akademik': { name: 'Admin Program Studi (Admin Prodi)', id: 'prodi' },
      'dosen': { name: 'Ketua Jurusan (Kajur)', id: 'kajur' },
      'pungli': { name: 'Ketua Jurusan (Kajur)', id: 'kajur' },
      'mahasiswa': { name: 'Ketua Program Studi (KPS)', id: 'kps' }
    };

    const targetUnit = unitMap[data.kategoriKey] || { name: 'Sekretariat Jurusan (Sekjur)', id: 'sekjur' };
    const user = this.getUser();

    const newReport = {
      id: nextId,
      no_tiket: ticketNumber,
      kategori: data.kategoriName || 'Fasilitas Kampus',
      unit_tujuan: targetUnit.name,
      unit_id: targetUnit.id,
      judul_laporan: data.judul,
      lokasi: data.lokasi,
      tanggal_kejadian: data.tanggal,
      kronologi: data.kronologi,
      tanggal_lapor: `${dateFormatted} ${timeFormatted}`,
      nama_pelapor: user.nama || 'Mahasiswa JTI',
      nim_pelapor: user.nim || '254107020095',
      prodi: user.prodi || 'D4 Teknik Informatika',
      status: 'Menunggu Validasi',
      bukti_file: data.bukti_file || 'lampiran-bukti.jpg',
      tanggapan: null,
      penanganan: null,
      riwayat: [
        {
          status: 'Laporan Diajukan',
          waktu: `${dateFormatted}, ${timeFormatted} WIB`,
          keterangan: `Laporan berhasil diajukan dan langsung didisposisikan ke ${targetUnit.name}.`
        }
      ]
    };

    reports.unshift(newReport);
    localStorage.setItem(SIPEKA_STORAGE_KEY, JSON.stringify(reports));
    return newReport;
  }

  updateStatus(noTiket, newStatus, keterangan, metadata = {}) {
    const reports = this.getAllReports();
    const index = reports.findIndex(r => r.no_tiket === noTiket);
    if (index === -1) return false;

    const report = reports[index];
    report.status = newStatus;
    report.updated_at = new Date().toISOString();

    const now = new Date();
    const waktu = `${now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })}, ${now.getHours()}:${String(now.getMinutes()).padStart(2, '0')} WIB`;

    if (!report.riwayat) report.riwayat = [];
    report.riwayat.push({
      status: newStatus,
      waktu: waktu,
      keterangan: keterangan || `Status diperbarui menjadi ${newStatus}.`
    });

    if (metadata.tanggapan) {
      report.tanggapan = metadata.tanggapan;
    }
    if (metadata.penanganan) {
      report.penanganan = metadata.penanganan;
    }

    reports[index] = report;
    localStorage.setItem(SIPEKA_STORAGE_KEY, JSON.stringify(reports));
    return report;
  }

  // --- STATS METRICS ---
  getStats(unitId = null) {
    const reports = unitId ? this.getReportsByUnit(unitId) : this.getAllReports();
    return {
      total: reports.length,
      menunggu: reports.filter(r => r.status === 'Menunggu Validasi').length,
      proses: reports.filter(r => r.status === 'Dalam Penanganan').length,
      selesai: reports.filter(r => r.status === 'Selesai').length,
      ditolak: reports.filter(r => r.status === 'Tidak Valid').length
    };
  }
}

// Global Store Instance
window.sipekaStore = new SipekaStore();
