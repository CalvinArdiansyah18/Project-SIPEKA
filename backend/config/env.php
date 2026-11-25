<?php
// Pemuat file .env sederhana (tanpa dependency/composer).
// Dipanggil otomatis oleh koneksi.php.
//
// Aturan format .env yang didukung:
//   KEY=nilai
//   KEY="nilai dengan spasi"
//   KEY='nilai literal'
//   # komentar
//
// Variabel environment yang SUDAH ada (mis. dari server/hosting) tidak ditimpa,
// sehingga konfigurasi produksi tetap bisa mengalahkan isi .env.

function sipeka_muat_env(string $path): void
{
    if (!is_readable($path)) {
        return; // .env opsional: tanpa file ini, nilai fallback di koneksi.php dipakai
    }

    $baris = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($baris === false) {
        return;
    }

    foreach ($baris as $isi) {
        $isi = trim($isi);
        if ($isi === '' || $isi[0] === '#') {
            continue;
        }

        // dukung awalan "export KEY=..."
        if (stripos($isi, 'export ') === 0) {
            $isi = trim(substr($isi, 7));
        }

        $pos = strpos($isi, '=');
        if ($pos === false) {
            continue;
        }

        $kunci = trim(substr($isi, 0, $pos));
        if ($kunci === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $kunci)) {
            continue;
        }

        $nilai = trim(substr($isi, $pos + 1));

        // buang komentar di akhir nilai yang tidak dikutip
        if ($nilai !== '' && $nilai[0] !== '"' && $nilai[0] !== "'") {
            $hash = strpos($nilai, ' #');
            if ($hash !== false) {
                $nilai = rtrim(substr($nilai, 0, $hash));
            }
        }

        // buang tanda kutip pembungkus
        $panjang = strlen($nilai);
        if ($panjang >= 2) {
            $awal  = $nilai[0];
            $akhir = $nilai[$panjang - 1];
            if (($awal === '"' && $akhir === '"') || ($awal === "'" && $akhir === "'")) {
                $nilai = substr($nilai, 1, -1);
            }
        }

        // jangan timpa env yang sudah di-set oleh server
        if (getenv($kunci) !== false) {
            continue;
        }

        putenv($kunci . '=' . $nilai);
        $_ENV[$kunci]    = $nilai;
        $_SERVER[$kunci] = $nilai;
    }
}

// Ambil nilai env dengan fallback (dipakai koneksi.php).
function env_atau(string $kunci, ?string $bawaan = null): ?string
{
    $nilai = getenv($kunci);
    return ($nilai === false || $nilai === '') ? $bawaan : $nilai;
}

sipeka_muat_env(__DIR__ . '/../../.env');
