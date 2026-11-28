<?php
// POST /backend/api/unit/validasi.php

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

$in = json_in();
$tiket = trim((string)($in['tiket'] ?? ''));
$action = strtolower(trim((string)($in['action'] ?? '')));
$keterangan = trim((string)($in['keterangan'] ?? ''));

$id_laporan = id_dari_tiket($tiket);

if ($id_laporan === null) {
    json_out(['message' => 'Format nomor tiket tidak valid'], 400);
}

$statusBaru = [
    'approve' => 'dalam_penanganan',
    'reject' => 'ditolak',
][$action] ?? null;

if ($statusBaru === null) {
    json_out(['message' => 'Aksi tidak dikenal, gunakan approve atau reject'], 400);
}

if ($keterangan === '') {
    $keterangan = $statusBaru === 'dalam_penanganan'
        ? 'Laporan divalidasi oleh petugas unit dan diteruskan ke penanganan.'
        : 'Laporan ditolak oleh petugas unit karena tidak valid.';
}

// ---------- 1. Periksa kepemilikan laporan ----------
$st = $pdo->prepare(
    'SELECT l.status, k.id_unit
     FROM laporan l
     JOIN kategori k ON k.id_kategori = l.id_kategori
     WHERE l.id_laporan = :id'
);

$st->execute([':id' => $id_laporan]);
$row = $st->fetch();

if (!$row) {
    json_out(['message' => 'Laporan tidak ditemukan'], 404);
}

if ((int)$row['id_unit'] !== (int)$id_unit) {
    json_out(['message' => 'Laporan bukan tanggung jawab unit Anda'], 403);
}

if ($row['status'] !== 'menunggu') {
    json_out([
        'message' => 'Laporan sudah diproses sebelumnya (status: '
            . $row['status'] . ')'
    ], 409);
}

// ---------- 2. Ubah status melalui stored procedure ----------
try {
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
        ':ket' => $keterangan,
    ]);

    $st->closeCursor();

} catch (PDOException $e) {
    error_log('Validasi laporan gagal: ' . $e->getMessage());

    $pesan = ($e->getCode() === '42501')
        ? 'Anda bukan petugas unit penanggung jawab laporan ini.'
        : 'Status laporan gagal diubah. Silakan coba kembali.';

    json_out(['message' => $pesan], 409);
}

// ---------- 3. Kirim email kepada mahasiswa ----------
try {
    $laporan = ambilDataEmailMahasiswa($pdo, $id_laporan);

    if ($laporan) {
        $nama = $laporan['nama'];
        $email = $laporan['email'];
        $noTiket = tiketEmailSipeka($laporan);
        $judul = $laporan['judul_laporan'];

        if ($statusBaru === 'dalam_penanganan') {
            $subjek = 'Laporan Berhasil Divalidasi - ' . $noTiket;

            $pesan =
                "Kabar baik! Laporan pengaduan Anda telah berhasil divalidasi.\n\n" .
                "Nomor tiket: {$noTiket}\n" .
                "Judul laporan: {$judul}\n" .
                "Status: Dalam Penanganan\n\n" .
                "Laporan Anda telah diteruskan kepada petugas untuk diproses.";

        } else {
            $subjek = 'Laporan Ditolak - ' . $noTiket;

            $pesan =
                "Mohon maaf, laporan pengaduan Anda telah ditolak oleh petugas unit.\n\n" .
                "Nomor tiket: {$noTiket}\n" .
                "Judul laporan: {$judul}\n" .
                "Status: Ditolak\n" .
                "Alasan: {$keterangan}\n\n" .
                "Silakan masuk ke aplikasi SIPEKA untuk melihat informasi lebih lanjut.";
        }

        kirimEmailSipeka(
            $email,
            $nama,
            $subjek,
            $pesan
        );
    }

} catch (Throwable $e) {
    error_log(
        'Notifikasi Gmail validasi gagal: ' . $e->getMessage()
    );
}

// ---------- 4. Respons API ----------
json_out([
    'message' => $statusBaru === 'dalam_penanganan'
        ? 'Laporan berhasil divalidasi dan diteruskan ke penanganan.'
        : 'Laporan berhasil ditolak.',
    'tiket' => $tiket,
    'status' => $statusBaru,
]);
