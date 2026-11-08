<?php
// GET  /backend/api/auth/profil.php
//   Profil lengkap akun yang sedang login (mahasiswa atau unit) + ringkasan jumlah laporan.
// POST /backend/api/auth/profil.php
//   Body JSON : { "nama": "...", "nomor_telepon": "08..." (opsional, hanya berlaku untuk mahasiswa) }
//   Memperbarui nama (users) dan nomor telepon (mahasiswa). Email, NIM, prodi, dan unit tidak bisa diubah di sini.
//
// Jawaban GET : { nama, email, role, nama_unit, kode_unit, nim, program_studi, nomor_telepon, stats }
// Jawaban POST: { message, profil: { ...sama seperti GET } }

require __DIR__ . '/../../config/bootstrap.php';
require __DIR__ . '/../../config/laporan-helper.php';

$user = butuh_login();   // semua peran boleh; data diambil dari SESSION, bukan dari input

function ambil_profil(PDO $pdo, array $user): array
{
    $st = $pdo->prepare(
        'SELECT u.nama, u.email, u.role, un.nama_unit,
                m.nim, m.program_studi, m.nomor_telepon
        FROM users u
        LEFT JOIN unit un ON un.id_unit = u.id_unit
        LEFT JOIN mahasiswa m ON m.id_user = u.id_user
        WHERE u.id_user = :id'
    );
    $st->execute([':id' => $user['id_user']]);
    $p = $st->fetch();
    if (!$p) {
        json_out(['message' => 'Akun tidak ditemukan'], 404);
    }

    // "Admin Prodi" -> "admin-prodi", "Sekjur" -> "sekjur"
    $p['kode_unit'] = $p['nama_unit'] !== null
        ? strtolower(preg_replace('/\s+/', '-', trim($p['nama_unit'])))
        : null;

    if ($user['role'] === 'unit') {
        $st = $pdo->prepare(
            'SELECT COALESCE(SUM(total_laporan), 0) AS total,
                    COALESCE(SUM(menunggu), 0) AS menunggu,
                    COALESCE(SUM(diproses), 0) + COALESCE(SUM(dalam_penanganan), 0) AS proses,
                    COALESCE(SUM(selesai), 0) AS selesai,
                    COALESCE(SUM(ditolak), 0) AS ditolak
            FROM v_rekap_unit
            WHERE id_unit = :u'
        );
        $st->execute([':u' => $user['id_unit']]);
    } else {
        $st = $pdo->prepare(
            "SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE status = 'menunggu') AS menunggu,
                    COUNT(*) FILTER (WHERE status IN ('diproses','dalam_penanganan')) AS proses,
                    COUNT(*) FILTER (WHERE status = 'selesai') AS selesai,
                    COUNT(*) FILTER (WHERE status = 'ditolak') AS ditolak
            FROM laporan
            WHERE id_mahasiswa = :m"
        );
        $st->execute([':m' => $user['id_mahasiswa'] ?? 0]);
    }
    $p['stats'] = rapikan_stats($st->fetch());

    return $p;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    json_out(ambil_profil($pdo, $user));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['message' => 'Method tidak diizinkan, gunakan GET atau POST'], 405);
}

// ---------- ubah profil ----------
$in = json_in();
$nama = trim((string)($in['nama'] ?? ''));
if (panjang_teks($nama) < 3 || panjang_teks($nama) > 100) {
    json_out(['message' => 'Nama lengkap harus 3-100 karakter'], 400);
}

$ubahTelepon = false;
$telepon = null;
if ($user['role'] === 'mahasiswa' && array_key_exists('nomor_telepon', $in)) {
    $telepon = trim((string)$in['nomor_telepon']);
    $telepon = $telepon === '' ? null : $telepon;
    if ($telepon !== null && !preg_match('/^(\+62|0)[0-9]{8,13}$/', $telepon)) {
        json_out(['message' => 'Format nomor telepon tidak valid (contoh: 081234567890)'], 400);
    }
    $ubahTelepon = true;
}

try {
    $pdo->beginTransaction();

    $st = $pdo->prepare('UPDATE users SET nama = :nama WHERE id_user = :id');
    $st->execute([':nama' => $nama, ':id' => $user['id_user']]);

    if ($ubahTelepon) {
        $st = $pdo->prepare('UPDATE mahasiswa SET nomor_telepon = :telp WHERE id_user = :id');
        $st->execute([':telp' => $telepon, ':id' => $user['id_user']]);
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($e->getCode() === '23514') {   // pelanggaran CHECK
        json_out(['message' => 'Data tidak memenuhi aturan validasi'], 400);
    }
    throw $e;
}

$_SESSION['user']['nama'] = $nama;   // supaya nama di sidebar/topbar ikut berubah

json_out([
    'message' => 'Profil berhasil diperbarui.',
    'profil' => ambil_profil($pdo, $_SESSION['user']),
]);
