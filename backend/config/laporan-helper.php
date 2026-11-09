<?php
// Fungsi bantu untuk data laporan. Di-require oleh endpoint dashboard (dan endpoint laporan berikutnya).
// Ekspresi SQL nomor tiket, contoh hasil: SPK-2026-00012
// Nomor tiket TIDAK disimpan di tabel; dihitung dari tahun lapor + id_laporan.
// $alias diisi dengan awalan tabel, misalnya 'l.' (kosongkan jika kolom dipilih dari view).
function sql_no_tiket(string $alias = ''): string
{
    return "('SPK-' || to_char({$alias}tanggal_lapor, 'YYYY') || '-' || lpad({$alias}id_laporan::text, 5, '0'))";
}

// Kebalikannya: "SPK-2026-00012" -> 12 (null jika format salah). Dipakai endpoint detail nanti.
function id_dari_tiket(string $tiket): ?int
{
    return preg_match('/^SPK-\d{4}-(\d+)$/i', trim($tiket), $m) ? (int)$m[1] : null;
}

// Hasil agregasi dari PostgreSQL bisa berupa string -> jadikan angka agar JSON rapi
function rapikan_stats(array $row): array
{
    return [
        'total' => (int)($row['total'] ?? 0),
        'menunggu' => (int)($row['menunggu'] ?? 0),
        'dalam_penanganan' => (int)($row['dalam_penanganan'] ?? 0),
        'selesai' => (int)($row['selesai'] ?? 0),
        'ditolak' => (int)($row['ditolak'] ?? 0),
    ];
}

// PDO pgsql dapat mengembalikan boolean sebagai true/false atau 't'/'f'
function ke_bool($v): bool
{
    return $v === true || $v === 't' || $v === 1 || $v === '1';
}

// "2026-10-05 09:30:00+07" -> "2026-10-05T09:30:00+07:00" (format ISO 8601, aman dibaca JavaScript)
function iso_waktu($v): ?string
{
    if ($v === null || $v === '') return null;
    try {
        return (new DateTime((string)$v))->format(DATE_ATOM);
    } catch (Exception $e) {
        return (string)$v;
    }
}

// Panjang teks (mendukung huruf non-ASCII bila mbstring aktif)
function panjang_teks(string $s): int
{
    return function_exists('mb_strlen') ? mb_strlen($s, 'UTF-8') : strlen($s);
}

// Daftar putih filter status -> potongan SQL.
// $kolom = nama kolom status (boleh berawalan alias, mis. 'l.status')
function kondisi_status(string $kolom = 'status'): array
{
    return [
        'menunggu' => "$kolom = 'menunggu'",
        'dalam_penanganan' => "$kolom = 'dalam_penanganan'",
        'selesai' => "$kolom = 'selesai'",
        'ditolak' => "$kolom = 'ditolak'",
    ];
}

// ---------------------------------------------------------------
// Upload berkas
// Berkas disimpan di backend/uploads/ (diblokir dari akses langsung oleh .htaccess)
// dan hanya disajikan lewat api/laporan/bukti.php setelah pengecekan hak akses.
// ---------------------------------------------------------------

const UPLOAD_MAKS_BYTE = 5 * 1024 * 1024; // 5 MB
const UPLOAD_JENIS = [ // MIME yang diizinkan => ekstensi penyimpanan
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
];

function folder_uploads(): string
{
    $dir = __DIR__ . '/../uploads';
    return realpath($dir) ?: $dir;
}

// Mengenali jenis berkas dari ISI berkas (bukan dari nama atau tipe kiriman browser)
function deteksi_mime(string $path): ?string
{
    $h = @file_get_contents($path, false, null, 0, 12);
    if ($h === false || $h === '') return null;
    if (strncmp($h, "\xFF\xD8\xFF", 3) === 0) return 'image/jpeg';
    if (strncmp($h, "\x89PNG\r\n\x1a\n", 8) === 0) return 'image/png';
    if (strncmp($h, 'RIFF', 4) === 0 && substr($h, 8, 4) === 'WEBP') return 'image/webp';
    if (strncmp($h, '%PDF-', 5) === 0) return 'application/pdf';
    return null;
}

// Memvalidasi satu berkas dari $_FILES[$field]. Mengembalikan null jika tidak ada berkas,
// atau array info berkas jika valid. Jika tidak valid, langsung menjawab error JSON.
function periksa_berkas_upload(string $field): ?array
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];

    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        json_out(['message' => 'Ukuran berkas melebihi batas server (upload_max_filesize = '
            . ini_get('upload_max_filesize') . '). Maksimal yang diizinkan aplikasi: 5 MB.'], 413);
    }
    if ($f['error'] !== UPLOAD_ERR_OK) {
        json_out(['message' => 'Berkas gagal diunggah (kode ' . $f['error'] . '). Silakan coba lagi.'], 400);
    }
    if (!is_uploaded_file($f['tmp_name'])) {
        json_out(['message' => 'Berkas unggahan tidak valid.'], 400);
    }
    if ($f['size'] <= 0) {
        json_out(['message' => 'Berkas yang diunggah kosong.'], 400);
    }
    if ($f['size'] > UPLOAD_MAKS_BYTE) {
        json_out(['message' => 'Ukuran berkas melebihi 5 MB.'], 413);
    }

    $mime = deteksi_mime($f['tmp_name']);
    if ($mime === null || !isset(UPLOAD_JENIS[$mime])) {
        json_out(['message' => 'Jenis berkas tidak didukung. Gunakan JPG, PNG, WEBP, atau PDF.'], 415);
    }
    if (strpos($mime, 'image/') === 0 && @getimagesize($f['tmp_name']) === false) {
        json_out(['message' => 'Berkas gambar rusak atau tidak dapat dibaca.'], 415);
    }

    // Nama asli hanya untuk ditampilkan; dibersihkan dari karakter berbahaya
    $nama = preg_replace('/[^\w.\-() ]/u', '_', basename((string)$f['name']));
    $nama = substr($nama === '' || $nama === null ? 'berkas' : $nama, 0, 150);

    return [
        'tmp' => $f['tmp_name'],
        'nama' => $nama,
        'mime' => $mime,
        'ext' => UPLOAD_JENIS[$mime],
        'size' => (int)$f['size'],
    ];
}

// Memindahkan berkas ke backend/uploads/<subfolder>/<nama-acak>.<ext>
// Mengembalikan path RELATIF terhadap folder uploads (itulah yang disimpan di database).
function simpan_berkas_upload(array $berkas, string $subfolder): string
{
    $dir = folder_uploads() . '/' . $subfolder;
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Folder uploads tidak dapat dibuat. Periksa izin tulis folder backend/uploads.');
    }
    $nama = bin2hex(random_bytes(16)) . '.' . $berkas['ext'];   // nama acak, bukan nama dari pengguna
    if (!move_uploaded_file($berkas['tmp'], $dir . '/' . $nama)) {
        throw new RuntimeException('Berkas tidak dapat disimpan ke folder uploads.');
    }
    return $subfolder . '/' . $nama;
}