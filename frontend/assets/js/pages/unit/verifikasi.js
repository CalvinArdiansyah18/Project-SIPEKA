/**
 * Halaman Verifikasi Unit
 * Halaman : content/unit/{unit}/verifikasi.php
 * Fungsi  : initVerifikasiPage()
 * Dipanggil dari main.js (router) sesuai URL halaman.
 *
 * Data diambil dari backend (bukan localStorage):
 *   GET  backend/api/unit/detail.php?tiket=...
 *   POST backend/api/unit/validasi.php  { tiket, action, keterangan }
 */

// =======================================
// DEDICATED VERIFIKASI & PENANGANAN PAGES
// =======================================
async function initVerifikasiPage() {
  const urlParams = new URLSearchParams(window.location.search);
  let targetTiket = urlParams.get("tiket");

  try {
    // Tanpa ?tiket= : ambil laporan pertama yang masih menunggu validasi di unit ini.
    if (!targetTiket) {
      const data = await window.sipekaAPI.getDashboardUnit({
        status: "menunggu",
      });
      const reports = data.laporan || [];
      if (reports.length > 0) targetTiket = reports[0].no_tiket;
    }

    if (!targetTiket) {
      showToast(
        "Tidak ada laporan yang menunggu validasi pada unit ini.",
        "info",
      );
      return;
    }

    const report = await window.sipekaAPI.getReportDetail(targetTiket);
    if (!report) {
      showToast("Laporan tidak ditemukan pada unit Anda.", "error");
      return;
    }

    const tEl = document.getElementById("verifTiket");
    const pelaporEl = document.getElementById("verifPelapor");
    const katEl = document.getElementById("verifKategori");
    const judulEl = document.getElementById("verifJudul");
    const lokasiEl = document.getElementById("verifLokasi");
    const kronEl = document.getElementById("verifKronologi");

    if (tEl) tEl.textContent = report.no_tiket;
    if (pelaporEl)
      pelaporEl.textContent = `${report.nama_pelapor || "Mahasiswa JTI"} (NIM: ${report.nim || "-"})`;
    if (katEl) katEl.textContent = report.nama_kategori || "-";
    if (judulEl) judulEl.textContent = report.judul_laporan;
    if (lokasiEl) lokasiEl.textContent = report.lokasi || "-";
    if (kronEl) kronEl.textContent = report.kronologi || "-";

    // Kartu tanggapan untuk pelapor (disimpan lewat backend/api/unit/tanggapan.php)
    initTanggapanUnit(report);

    // Menampilkan bukti yang diunggah mahasiswa
    const buktiContainer = document.getElementById("verifBukti");

    if (buktiContainer) {
      buktiContainer.innerHTML = "";

      const daftarBukti = report.bukti || [];

      if (daftarBukti.length === 0) {
        buktiContainer.innerHTML = `
            <p class="bukti-kosong">
                Pelapor tidak melampirkan bukti.
            </p>
        `;
      } else {
        daftarBukti.forEach((bukti) => {
          const url = window.sipekaAPI.getBuktiUrl(bukti.id_bukti);

          const item = document.createElement("div");
          item.className = "bukti-item";

          const nama = document.createElement("p");
          nama.className = "bukti-nama";
          nama.textContent = bukti.nama_file;

          item.appendChild(nama);

          // Preview gambar
          if (bukti.jenis_file.startsWith("image/")) {
            const gambar = document.createElement("img");

            gambar.src = url;
            gambar.alt = "Bukti pelapor";
            gambar.className = "bukti-image";
            gambar.loading = "lazy";

            item.appendChild(gambar);
          }

          // Preview PDF
          else if (bukti.jenis_file === "application/pdf") {
            const pdf = document.createElement("iframe");

            pdf.src = url;
            pdf.className = "bukti-pdf";
            pdf.title = "Dokumen bukti pelapor";

            item.appendChild(pdf);
          }

          // File selain gambar dan PDF
          else {
            const link = document.createElement("a");

            link.href = url;
            link.target = "_blank";
            link.rel = "noopener noreferrer";
            link.textContent = "Lihat / Unduh Bukti";

            item.appendChild(link);
          }

          buktiContainer.appendChild(item);
        });
      }
    }

    if (report.status !== "menunggu") {
      showToast(
        `Laporan ini sudah berstatus "${getStatusLabel(report.status)}".`,
        "info",
      );
    }

    const btnApprove = document.getElementById("btnApprovePage");
    const btnReject = document.getElementById("btnRejectPage");

    if (btnApprove) {
      btnApprove.onclick = async (e) => {
        e.preventDefault();
        btnApprove.disabled = true;
        try {
          await window.sipekaAPI.validateReport(
            report.no_tiket,
            "approve",
            "Laporan telah divalidasi oleh petugas unit.",
          );
          setTimeout(() => (window.location.href = "dashboard.php"), 700);
        } catch (error) {
          btnApprove.disabled = false;
          showToast(error.message || "Gagal memvalidasi laporan.", "error");
        }
      };
    }

    if (btnReject) {
      btnReject.onclick = async (e) => {
        e.preventDefault();
        const reason = await sipekaPrompt({
          title: "Tolak Laporan",
          message: `Tiket ${report.no_tiket} akan ditandai tidak valid. Alasan penolakan dicatat pada riwayat laporan.`,
          placeholder:
            "Contoh: Bukti foto tidak sesuai dengan lokasi kejadian.",
          confirmText: "Tolak Laporan",
          danger: true,
        });
        if (!reason) return;
        btnReject.disabled = true;
        try {
          await window.sipekaAPI.validateReport(
            report.no_tiket,
            "reject",
            `Ditolak unit: ${reason}`,
          );
          setTimeout(() => (window.location.href = "dashboard.php"), 700);
        } catch (error) {
          btnReject.disabled = false;
          showToast(error.message || "Gagal menolak laporan.", "error");
        }
      };
    }
  } catch (error) {
    console.error("[SIPEKA] Gagal memuat halaman verifikasi:", error);
    showToast(error.message || "Gagal memuat data laporan.", "error");
  }
}
