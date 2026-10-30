<?php
// Fungsi bantu untuk data laporan. Di-require oleh endpoint dashboard (dan endpoint laporan berikutnya).
// Ekspresi SQL nomor tiket, contoh hasil: SPK-2026-00012
// Nomor tiket TIDAK disimpan di tabel; dihitung dari tahun lapor + id_laporan.
// $alias diisi dengan awalan tabel, misalnya 'l.' (kosongkan jika kolom dipilih dari view).
function sql_no_tiket(string $alias = ''): string {
    return "('SPK-' || to_char({$alias}tanggal_lapor, 'YYYY') || '-' || lpad({$alias}id_laporan::text, 5, '0'))";
}

// Kebalikannya: "SPK-2026-00012" -> 12 (null jika format salah). Dipakai endpoint detail nanti.
function id_dari_tiket(string $tiket): ?int {
    return preg_match('/^SPK-\d{4}-(\d+)$/i', trim($tiket), $m) ? (int)$m[1] : null;
}

// Hasil agregasi dari PostgreSQL bisa berupa string -> jadikan angka agar JSON rapi
function rapikan_stats(array $row): array {
    return [
        'total'    => (int)($row['total'] ?? 0),
        'menunggu' => (int)($row['menunggu'] ?? 0),
        'proses'   => (int)($row['proses'] ?? 0),     // diproses + dalam_penanganan
        'selesai'  => (int)($row['selesai'] ?? 0),
        'ditolak'  => (int)($row['ditolak'] ?? 0),
    ];
}

// PDO pgsql dapat mengembalikan boolean sebagai true/false atau 't'/'f'
function ke_bool($v): bool {
    return $v === true || $v === 't' || $v === 1 || $v === '1';
}
