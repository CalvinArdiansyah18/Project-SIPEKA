<?php
// POST /backend/api/unit/validasi.php
// Body JSON : { "tiket": "SPK-2026-00012", "action": "approve|reject", "keterangan": "..." }
// Approve   : menunggu -> diproses  (laporan valid, masuk antrean penanganan)
// Reject    : menunggu -> ditolak   (laporan tidak valid)
//
// Perubahan status dilakukan lewat stored procedure sp_ubah_status supaya alur status,
// row lock, dan validasi unit penanggung jawab tetap dijaga oleh database.
//
// Jawaban: { "message": "...", "tiket": "...", "status": "diproses|ditolak" }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

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
    'approve' => 'diproses',
    'reject' => 'ditolak',
][$action] ?? null;
if ($statusBaru === null) {
    json_out(['message' => 'Aksi tidak dikenal, gunakan approve atau reject'], 400);
}

if ($keterangan === '') {
    $keterangan = $statusBaru === 'diproses'
        ? 'Laporan divalidasi oleh petugas unit dan diteruskan ke penanganan.'
        : 'Laporan ditolak oleh petugas unit karena tidak valid.';
}

// Pastikan laporan memang milik unit ini dan masih menunggu validasi.
$st = $pdo->prepare(
    'SELECT l.status, k.id_unit
    FROM laporan  l
    JOIN kategori k ON k.id_kategori = l.id_kategori
    WHERE l.id_laporan = :id'
);
$st->execute([':id' => $id_laporan]);
$row = $st->fetch();
if (!$row) {
    json_out(['message' => 'Laporan tidak ditemukan'], 404);
}
if ((int)$row['id_unit'] !== $id_unit) {
    json_out(['message' => 'Laporan bukan tanggung jawab unit Anda'], 403);
}
if ($row['status'] !== 'menunggu') {
    json_out(['message' => 'Laporan sudah diproses sebelumnya (status: ' . $row['status'] . ')'], 409);
}

try {
    $st = $pdo->prepare(
        'CALL sp_ubah_status(:id, :user, CAST(:status AS status_laporan), :ket)'
    );
    $st->execute([
        ':id' => $id_laporan,
        ':user' => $user['id_user'],
        ':status' => $statusBaru,
        ':ket' => $keterangan,
    ]);
    $st->closeCursor();
} catch (PDOException $e) {
    // SQLSTATE 42501 = bukan petugas unit penanggung jawab, P0001 = alur status ditolak
    $pesan = ($e->getCode() === '42501')
        ? 'Anda bukan petugas unit penanggung jawab laporan ini.'
        : 'Status laporan gagal diubah: ' . $e->getMessage();
    json_out(['message' => $pesan], 409);
}

json_out([
    'message' => $statusBaru === 'diproses'
        ? 'Laporan berhasil divalidasi dan diteruskan ke penanganan.'
        : 'Laporan berhasil ditolak.',
    'tiket' => $tiket,
    'status' => $statusBaru,
]);
