<?php
/**
 * includes/ulasan-content.php
 * ISI halaman Ulasan, dipakai mahasiswa dan semua unit. Memakai kelas CSS yang sudah ada
 * (content-container, form-card, form-input, btn-primary, btn-detail) + sedikit tambahan di interaktif.css.
 * Kiri : kotak menulis ulasan (khusus mahasiswa; boleh berkali-kali, boleh dikaitkan ke penanganan yang sudah selesai).
 *        Untuk petugas unit kotak ini disembunyikan oleh ulasan.js dan diganti catatan informasi.
 * Kanan: filter (periode, kategori, kata kunci) + ulasan mahasiswa beserta balasan.
 * Logikanya ada di assets/js/pages/ulasan.js (ID elemen di bawah dipakai file itu).
 */
?>
<div class="content-container">
  <div class="page-header-row">
    <div class="page-title">
      <h2>Ulasan</h2>
      <p>Mahasiswa dapat menulis ulasan tentang penanganan laporan. Mahasiswa dan petugas unit dapat saling membalas.</p>
    </div>
  </div>

  <div class="ulasan-layout">
    <!-- Tulis ulasan -->
    <form id="ulasanForm" class="form-card form-card--full ulasan-form anim-fade-up" novalidate>
      <h3 class="card-title">Tulis Ulasan</h3>
      <div class="form-group">
        <label for="ulasanLaporan">Penanganan (opsional)</label>
        <select id="ulasanLaporan" class="form-input">
          <option value="">Ulasan umum (tidak terkait penanganan tertentu)</option>
        </select>
        <small id="ulasanLaporanInfo" class="form-hint form-hint--kiri">Memuat daftar penanganan selesai...</small>
      </div>
      <div class="form-group">
        <label for="ulasanIsi">Ulasan Anda</label>
        <textarea id="ulasanIsi" class="form-input" rows="5" maxlength="500"
          placeholder="Ceritakan pengalaman atau masukan Anda..."></textarea>
        <small class="form-hint"><span id="ulasanHitung">0/500</span></small>
      </div>
      <button type="submit" id="btnKirimUlasan" class="btn-primary btn-inline" disabled>Kirim Ulasan</button>
    </form>

    <!-- Catatan untuk petugas unit (menggantikan kotak tulis) -->
    <div id="ulasanCatatanUnit" class="form-card form-card--full ulasan-form hidden">
      <h3 class="card-title">Informasi untuk Unit Kerja</h3>
      <p class="form-hint form-hint--kiri">Mahasiswa dapat menulis ulasan. Petugas unit dapat membaca ulasan dan memberikan balasan.</p>
    </div>

    <!-- Filter + semua ulasan -->
    <section>
      <div id="ulasanFilter"></div>
      <div class="ulasan-head">
        <h3 class="card-title">Ulasan Mahasiswa</h3>
        <span id="ulasanRingkasan" class="card-meta"></span>
      </div>
      <div id="ulasanDaftar" class="review-list">
        <p class="review-empty">Memuat ulasan...</p>
      </div>
      <div class="review-more">
        <button type="button" id="ulasanMore" class="btn-detail" hidden>Muat lebih banyak</button>
      </div>
    </section>
  </div>
</div>
