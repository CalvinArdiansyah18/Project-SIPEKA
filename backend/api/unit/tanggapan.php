<?php
// POST /backend/api/unit/tanggapan.php
// Body JSON : { "tiket": "SPK-2026-00012", "isi": "teks tanggapan untuk pelapor" }
//
// Petugas unit menulis tanggapan pada laporan yang masuk ke UNIT-nya. Tanggapan langsung terlihat
// oleh pelapor di halaman riwayat. Hanya laporan yang belum final (bukan selesai / ditolak) yang bisa
// ditanggapi. Unit penanggung jawab juga dijaga trigger database (trg_tanggapan_validasi_unit).
//
// Jawaban (201): { "message": "...", "tanggapan": { "jenis", "isi", "waktu", "petugas" } }

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
$isi = trim((string)($in['isi'] ?? ''));

$id_laporan = id_dari_tiket($tiket);
if ($id_laporan === null) {
    json_out(['message' => 'Format nomor tiket tidak valid'], 400);
}
if ($isi === '') {
    json_out(['message' => 'Isi tanggapan wajib diisi'], 400);
}
if (panjang_teks($isi) > 1000) {
    json_out(['message' => 'Tanggapan terlalu panjang (maksimal 1000 karakter)'], 400);
}

// Laporan harus milik unit ini dan belum final
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
if (in_array($row['status'], ['selesai', 'ditolak'], true)) {
    json_out(['message' => 'Laporan sudah berstatus "' . $row['status'] . '", tanggapan tidak dapat ditambahkan lagi.'], 409);
}

try {
    $st = $pdo->prepare(
        'INSERT INTO tanggapan (id_laporan, id_user, isi_tanggapan)
        VALUES (:laporan, :user, :isi)
        RETURNING tanggal_tanggapan'
    );
    $st->execute([
        ':laporan' => $id_laporan,
        ':user' => $user['id_user'],
        ':isi' => $isi,
    ]);
    $waktu = $st->fetchColumn();
} catch (PDOException $e) {
    // SQLSTATE 42501 = bukan petugas unit penanggung jawab (trigger)
    if ($e->getCode() === '42501') {
        json_out(['message' => 'Anda bukan petugas unit penanggung jawab laporan ini.'], 403);
    }
    throw $e;
}

json_out([
    'message' => 'Tanggapan berhasil dikirim ke pelapor.',
    'tanggapan' => [
        'jenis' => 'Tanggapan',
        'isi' => $isi,
        'waktu' => iso_waktu($waktu),
        'petugas' => $user['nama'],
    ],
], 201);
