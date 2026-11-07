<?php
// POST /backend/api/unit/selesai.php   (multipart/form-data)
//
// Field teks   : tiket (SPK-2026-00012), keterangan (tindakan penanganan, wajib)
// Field berkas : bukti (opsional)  -> JPG / PNG / WEBP / PDF, maksimal 5 MB
//
// Menyelesaikan laporan milik unit yang sedang login:
//   diproses | dalam_penanganan  ->  selesai
//
// Semua langkah berjalan dalam SATU transaksi (gagal satu = batal semua):
//   1. kunci baris laporan, pastikan milik unit ini dan statusnya masih diproses / dalam_penanganan
//   2. jika masih 'diproses'  -> sp_ubah_status ke 'dalam_penanganan' (alur database mewajibkan tahap ini)
//   3. INSERT ke tabel penanganan (trigger memvalidasi petugas = unit penanggung jawab)
//   4. simpan berkas ke backend/uploads/penanganan/<id_penanganan>/ + INSERT ke bukti_penanganan
//   5. sp_ubah_status ke 'selesai' (prosedur mensyaratkan sudah ada catatan penanganan)
//
// Jawaban: { "message": "...", "tiket": "...", "status": "selesai", "id_penanganan": 7 }

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

// Jika ukuran total kiriman melebihi post_max_size, PHP mengosongkan $_POST dan $_FILES
if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    json_out(['message' => 'Ukuran data yang dikirim melebihi batas server (post_max_size = '
        . ini_get('post_max_size') . ').'], 413);
}

// ---------- 1. ambil & validasi input ----------
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

// Validasi berkas SEBELUM menyentuh database (jika tidak valid, langsung dijawab error JSON)
$berkas = periksa_berkas_upload('bukti');

// ---------- 2. simpan dalam satu transaksi ----------
$path_tersimpan = null;
$gagal = function (string $pesan, int $kode) use ($pdo, &$path_tersimpan): void {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($path_tersimpan !== null) {
        @unlink(folder_uploads() . '/' . $path_tersimpan);   // jangan tinggalkan berkas yatim
        $path_tersimpan = null;
    }
    json_out(['message' => $pesan], $kode);
};

$ubahStatus = function (string $statusBaru, string $ket) use ($pdo, $id_laporan, $user): void {
    $st = $pdo->prepare('CALL sp_ubah_status(:id, :user, CAST(:status AS status_laporan), :ket)');
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

    // Kunci baris laporan supaya dua petugas tidak menyelesaikan laporan yang sama bersamaan
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
        $gagal('Laporan belum divalidasi. Validasi laporan terlebih dahulu sebelum menyelesaikannya.', 409);
    }
    if (!in_array($row['status'], ['diproses', 'dalam_penanganan'], true)) {
        $gagal('Laporan sudah berstatus "' . $row['status'] . '" dan tidak bisa diselesaikan lagi.', 409);
    }

    // Tahap wajib: diproses -> dalam_penanganan
    if ($row['status'] === 'diproses') {
        $ubahStatus('dalam_penanganan', 'Penanganan dimulai oleh petugas unit.');
    }

    // Catatan penanganan (trigger trg_penanganan_validasi_unit memeriksa petugasnya)
    $st = $pdo->prepare(
        'INSERT INTO penanganan (id_laporan, id_user, keterangan_penanganan)
        VALUES (:laporan, :user, :ket)
        RETURNING id_penanganan'
    );
    $st->execute([
        ':laporan' => $id_laporan,
        ':user' => $user['id_user'],
        ':ket' => $keterangan,
    ]);
    $id_penanganan = (int)$st->fetchColumn();

    // Bukti penanganan (opsional)
    if ($berkas !== null) {
        $path_tersimpan = simpan_berkas_upload($berkas, 'penanganan/' . $id_penanganan);

        $st = $pdo->prepare(
            'INSERT INTO bukti_penanganan (id_penanganan, nama_file, path_file, jenis_file)
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
    $ubahStatus('selesai', 'Penanganan selesai: ' . $keterangan);

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($path_tersimpan !== null) {
        @unlink(folder_uploads() . '/' . $path_tersimpan);
    }
    // SQLSTATE 42501 = bukan petugas unit penanggung jawab, P0001 = alur status / syarat penanganan ditolak
    $pesan = ($e->getCode() === '42501')
        ? 'Anda bukan petugas unit penanggung jawab laporan ini.'
        : 'Laporan gagal diselesaikan: ' . $e->getMessage();
    json_out(['message' => $pesan], 409);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($path_tersimpan !== null) {
        @unlink(folder_uploads() . '/' . $path_tersimpan);
    }
    throw $e; // ditangani handler di bootstrap.php (jawaban JSON 500)
}

json_out([
    'message' => 'Laporan berhasil diselesaikan.',
    'tiket' => $tiket,
    'status' => 'selesai',
    'id_penanganan' => $id_penanganan,
]);