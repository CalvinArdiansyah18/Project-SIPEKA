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

// ---------------------------------------------------------------
// SLA (Service Level Agreement) & peringatan laporan "tenggelam"
// Batas penanganan = 72 jam (3x24 jam) kalender sejak laporan masuk.
// Laporan 'selesai'/'ditolak' tidak dihitung (sudah final).
// ---------------------------------------------------------------
const SLA_JAM = 72;

// Waktu acuan SLA (potongan SQL):
//   - 'menunggu'          -> tanggal_lapor
//   - 'dalam_penanganan'  -> aktivitas terakhir unit (tanggapan/penanganan), fallback tanggal_lapor
// GREATEST dipakai agar laporan yang baru diubah status tidak langsung dianggap terlambat.
function sql_acuan_sla(string $tanggalLapor, string $idLaporan): string
{
    return "(GREATEST(
        {$tanggalLapor},
        COALESCE((SELECT MAX(t.tanggal_tanggapan) FROM tanggapan t WHERE t.id_laporan = {$idLaporan}), {$tanggalLapor}),
        COALESCE((SELECT MAX(p.tanggal_penanganan) FROM penanganan p WHERE p.id_laporan = {$idLaporan}), {$tanggalLapor})
    ))";
}

// Apakah acuan SLA sudah melewati batas (boolean SQL). Dipakai untuk COUNT/filter.
function sql_sla_terlambat(string $acuanExpr): string
{
    return "{$acuanExpr} < now() - INTERVAL '" . SLA_JAM . " hours'";
}

// Hitung status SLA dari satu baris laporan.
// $acuan            : timestamp acuan (hasil sql_acuan_sla)
// $jumlahTanggapan  : bila diisi, laporan 'menunggu' hanya dianggap perlu perhatian
//                     jika BELUM ada tanggapan (menghindari peringatan palsu).
function sla_meta(string $status, $acuan, ?int $jumlahTanggapan = null): array
{
    $final = in_array($status, ['selesai', 'ditolak'], true);

    $acuanTs = null;
    if ($acuan !== null && $acuan !== '') {
        try {
            $acuanTs = (new DateTime((string)$acuan))->getTimestamp();
        } catch (Exception $e) {
            $acuanTs = null;
        }
    }

    $umurJam = ($final || $acuanTs === null) ? null : max(0, (time() - $acuanTs) / 3600);

    if ($final) {
        $sla = 'selesai';
    } elseif ($umurJam === null) {
        $sla = 'aman';
    } elseif ($umurJam > SLA_JAM) {
        $sla = 'terlambat';
    } elseif ($umurJam > SLA_JAM * 0.75) {
        $sla = 'mendekati';
    } else {
        $sla = 'aman';
    }

    $perlu = ($sla === 'terlambat');
    if ($perlu && $status === 'menunggu' && $jumlahTanggapan !== null) {
        $perlu = $jumlahTanggapan === 0;
    }

    return [
        'sla_status' => $sla,
        'sla_jam' => SLA_JAM,
        'umur_jam' => $umurJam === null ? null : round($umurJam, 1),
        'sisa_jam' => $umurJam === null ? null : round(max(0, SLA_JAM - $umurJam), 1),
        'terlambat_jam' => $umurJam === null ? 0 : round(max(0, $umurJam - SLA_JAM), 1),
        'perlu_perhatian' => $perlu,
    ];
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

const UPLOAD_MAKS_BYTE = 5 * 1024 * 1024; // 5 MB per berkas
const UPLOAD_MAKS_TOTAL_BYTE = 20 * 1024 * 1024; // 20 MB total gabungan
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

// Memvalidasi satu entri berkas (bentuk tunggal dari $_FILES).
// Mengembalikan array info berkas jika valid, atau langsung menjawab error JSON.
function validasi_berkas(array $f): array
{
    if ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE) {
        json_out(['message' => 'Ukuran berkas melebihi batas server (upload_max_filesize = '
            . ini_get('upload_max_filesize') . '). Maksimal yang diizinkan aplikasi: 5 MB per berkas.'], 413);
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

// Memvalidasi satu berkas dari $_FILES[$field]. Mengembalikan null jika tidak ada berkas,
// atau array info berkas jika valid. Jika tidak valid, langsung menjawab error JSON.
function periksa_berkas_upload(string $field): ?array
{
    if (!isset($_FILES[$field])) {
        return null;
    }
    if (is_array($_FILES[$field]['error'])) {
        return null;
    }
    if ($_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    return validasi_berkas($_FILES[$field]);
}

// Memvalidasi banyak berkas dari $_FILES[$field] berbentuk array (input name="field[]").
// Mengembalikan daftar info berkas valid (kosong jika tidak ada berkas).
function periksa_berkas_upload_multi(string $field): array
{
    if (!isset($_FILES[$field])) {
        return [];
    }

    $f = $_FILES[$field];

    // Normalisasi: satu berkas berbentuk tunggal -> bentuk array
    if (!is_array($f['name'])) {
        $f = [
            'name' => [$f['name']],
            'type' => [$f['type']],
            'tmp_name' => [$f['tmp_name']],
            'error' => [$f['error']],
            'size' => [$f['size']],
        ];
    }

    $hasil = [];
    $total = 0;
    $jumlah = count($f['name']);

    for ($i = 0; $i < $jumlah; $i++) {
        if ($f['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        $total += (int)$f['size'][$i];
        if ($total > UPLOAD_MAKS_TOTAL_BYTE) {
            json_out(['message' => 'Total ukuran semua berkas melebihi 20 MB.'], 413);
        }

        $hasil[] = validasi_berkas([
            'name' => $f['name'][$i],
            'type' => $f['type'][$i],
            'tmp_name' => $f['tmp_name'][$i],
            'error' => $f['error'][$i],
            'size' => $f['size'][$i],
        ]);
    }

    return $hasil;
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