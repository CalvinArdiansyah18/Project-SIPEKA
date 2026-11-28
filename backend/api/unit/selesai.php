<?php
// POST /backend/api/unit/selesai.php

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';
require_once __DIR__ . '/../../config/mail.php';

date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan POST'], 405);
}

$user = butuh_login('unit');
$id_unit = $user['id_unit'];

if ($id_unit === null) {
    json_out(['message' => 'Akun unit tidak terhubung ke unit manapun'], 403);
}

// Periksa batas ukuran kiriman
if (
    empty($_POST) &&
    empty($_FILES) &&
    (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0
) {
    json_out([
        'message' => 'Ukuran data melebihi batas server (post_max_size = '
            . ini_get('post_max_size') . ').'
    ], 413);
}

// ---------- 1. Validasi input ----------
$tiket = trim((string)($_POST['tiket'] ?? ''));
$keterangan = trim((string)($_POST['keterangan'] ?? ''));

$id_laporan = id_dari_tiket($tiket);

if ($id_laporan === null) {
    json_out(['message' => 'Format nomor tiket tidak valid'], 400);
}

if ($keterangan === '') {
    json_out(['message' => 'Keterangan tindakan penanganan wajib diisi'], 400);
}

if (panjang_teks($keterangan) > 2000) {
    json_out(['message' => 'Keterangan terlalu panjang (maksimal 2000 karakter)'], 400);
}

// Validasi bukti penanganan
$berkas = periksa_berkas_upload('bukti');

// ---------- 2. Simpan dalam satu transaksi ----------
$path_tersimpan = null;

$gagal = function (string $pesan, int $kode) use (
    $pdo,
    &$path_tersimpan
): void {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($path_tersimpan !== null) {
        @unlink(folder_uploads() . '/' . $path_tersimpan);
        $path_tersimpan = null;
    }

    json_out(['message' => $pesan], $kode);
};

$ubahStatus = function (
    string $statusBaru,
    string $ket
) use ($pdo, $id_laporan, $user): void {
    $st = $pdo->prepare(
        'CALL sp_ubah_status(
            :id,
            :user,
            CAST(:status AS status_laporan),
            :ket
        )'
    );

    $st->execute([
        ':id' => $id_laporan,
        ':user' => $user['id_user'],
        ':status' => $statusBaru,
        ':ket' => $ket,
    ]);

    $st->closeCursor();
};

try {
    $pdo->beginTransaction();

    // Kunci laporan
    $st = $pdo->prepare(
        'SELECT l.status, k.id_unit
         FROM laporan l
         JOIN kategori k ON k.id_kategori = l.id_kategori
         WHERE l.id_laporan = :id
         FOR UPDATE OF l'
    );

    $st->execute([':id' => $id_laporan]);
    $row = $st->fetch();

    if (!$row) {
        $gagal('Laporan tidak ditemukan', 404);
    }

    if ((int)$row['id_unit'] !== (int)$id_unit) {
        $gagal('Laporan bukan tanggung jawab unit Anda', 403);
    }

    if ($row['status'] === 'menunggu') {
        $gagal(
            'Laporan belum divalidasi. Validasi laporan terlebih dahulu.',
            409
        );
    }

    if (!in_array(
        $row['status'],
        ['diproses', 'dalam_penanganan'],
        true
    )) {
        $gagal(
            'Laporan sudah berstatus "' . $row['status']
                . '" dan tidak bisa diselesaikan lagi.',
            409
        );
    }

    // Tahap wajib: diproses -> dalam_penanganan
    if ($row['status'] === 'diproses') {
        $ubahStatus(
            'dalam_penanganan',
            'Penanganan dimulai oleh petugas unit.'
        );
    }

    // Simpan catatan penanganan
    $st = $pdo->prepare(
        'INSERT INTO penanganan
            (id_laporan, id_user, keterangan_penanganan)
         VALUES (:laporan, :user, :ket)
         RETURNING id_penanganan'
    );

    $st->execute([
        ':laporan' => $id_laporan,
        ':user' => $user['id_user'],
        ':ket' => $keterangan,
    ]);

    $id_penanganan = (int)$st->fetchColumn();

    // Simpan bukti penanganan jika tersedia
    if ($berkas !== null) {
        $path_tersimpan = simpan_berkas_upload(
            $berkas,
            'penanganan/' . $id_penanganan
        );

        $st = $pdo->prepare(
            'INSERT INTO bukti_penanganan
                (id_penanganan, nama_file, path_file, jenis_file)
             VALUES (:pen, :nama, :path, :jenis)'
        );

        $st->execute([
            ':pen' => $id_penanganan,
            ':nama' => $berkas['nama'],
            ':path' => $path_tersimpan,
            ':jenis' => $berkas['mime'],
        ]);
    }

    // Tahap akhir: dalam_penanganan -> selesai
    $ubahStatus(
        'selesai',
        'Penanganan selesai: ' . $keterangan
    );

    // Semua proses database harus berhasil sebelum email dikirim
    $pdo->commit();

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($path_tersimpan !== null) {
        @unlink(folder_uploads() . '/' . $path_tersimpan);
    }

    error_log('Gagal menyelesaikan laporan: ' . $e->getMessage());

    $pesan = ($e->getCode() === '42501')
        ? 'Anda bukan petugas unit penanggung jawab laporan ini.'
        : 'Laporan gagal diselesaikan. Silakan periksa data dan coba kembali.';

    json_out(['message' => $pesan], 409);

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($path_tersimpan !== null) {
        @unlink(folder_uploads() . '/' . $path_tersimpan);
    }

    throw $e;
}

// ---------- 3. Kirim email kepada mahasiswa ----------
try {
    $laporan = ambilDataEmailMahasiswa($pdo, $id_laporan);

    if ($laporan) {
        $nama = $laporan['nama'];
        $email = $laporan['email'];
        $noTiket = tiketEmailSipeka($laporan);
        $judul = $laporan['judul_laporan'];

        $pesan =
            "Kabar baik! Laporan pengaduan Anda telah selesai ditangani.\n\n" .
            "Nomor tiket: {$noTiket}\n" .
            "Judul laporan: {$judul}\n" .
            "Status: Selesai\n\n" .
            "Keterangan penanganan:\n{$keterangan}\n\n" .
            "Terima kasih telah menggunakan layanan SIPEKA. " .
            "Silakan masuk ke aplikasi untuk melihat detail laporan.";

        kirimEmailSipeka(
            $email,
            $nama,
            'Laporan Selesai Ditangani - ' . $noTiket,
            $pesan
        );
    }

} catch (Throwable $e) {
    error_log(
        'Notifikasi Gmail laporan selesai gagal: ' . $e->getMessage()
    );
}

// ---------- 4. Respons API ----------
json_out([
    'message' => 'Laporan berhasil diselesaikan.',
    'tiket' => $tiket,
    'status' => 'selesai',
    'id_penanganan' => $id_penanganan,
]);
