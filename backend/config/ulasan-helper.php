<?php
// Fungsi bantu ulasan & balasan. Di-require oleh api/ulasan/*.php

// "Budi Santoso Putra" -> "Budi P."
function samarkan_nama(string $nama): string
{
    $bagian = preg_split('/\s+/u', trim($nama)) ?: [];
    if (count($bagian) <= 1) return $nama;
    $akhir = end($bagian);
    $inisial = function_exists('mb_substr') ? mb_substr($akhir, 0, 1, 'UTF-8') : substr($akhir, 0, 1);
    return $bagian[0] . ' ' . strtoupper($inisial) . '.';
}

// Petugas unit tampil sebagai nama unitnya; mahasiswa disamarkan.
function nama_tampil(array $r): string
{
    return $r['peran'] === 'unit'
        ? ($r['nama_unit'] ?: 'Petugas Unit')
        : samarkan_nama((string)$r['nama']);
}

function peran_tampil(string $peran): string
{
    return $peran === 'unit' ? 'Petugas Unit' : 'Mahasiswa';
}

// Balasan untuk sekumpulan ulasan -> [ id_ulasan => [ balasan, ... ] ]
// $hanyaUnit = true: hanya balasan petugas unit (dipakai halaman depan)
function muat_balasan(PDO $pdo, array $idUlasan, ?int $idUserSaya, bool $hanyaUnit = false): array
{
    $hasil = [];
    if (!$idUlasan) return $hasil;
    $tanda = implode(',', array_fill(0, count($idUlasan), '?'));
    $st = $pdo->prepare(
        "SELECT b.id_balasan, b.id_ulasan, b.id_user, b.isi_balasan, b.created_at,
                u.nama, u.role::text AS peran, un.nama_unit
           FROM ulasan_balasan b
           JOIN users u      ON u.id_user = b.id_user
           LEFT JOIN unit un ON un.id_unit = u.id_unit
          WHERE b.id_ulasan IN ($tanda)
            " . ($hanyaUnit ? "AND u.role::text = 'unit'" : '') . "
          ORDER BY b.id_balasan"
    );
    $st->execute(array_values($idUlasan));
    foreach ($st->fetchAll() as $r) {
        $hasil[(int)$r['id_ulasan']][] = [
            'id_balasan' => (int)$r['id_balasan'],
            'nama' => nama_tampil($r),
            'peran' => peran_tampil($r['peran']),
            'isi' => $r['isi_balasan'],
            'waktu' => iso_waktu($r['created_at']),
            'milik_saya' => $idUserSaya !== null && (int)$r['id_user'] === $idUserSaya,
        ];
    }
    return $hasil;
}
