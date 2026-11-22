<?php
/**
 * includes/unit/config.php
 * Identitas tiap unit penanganan. Dipakai oleh partial layout unit
 * (layout-start.php) agar sidebar/topbar/header tidak diduplikasi di 24 halaman.
 *
 * Kunci array = nama folder di frontend/content/unit/<kunci>/.
 */
return [
    'admin-prodi' => [
        'data_unit' => 'prodi',
        'topbar'    => 'SIPEKA Admin Prodi',
        'brand'     => 'SIPEKA Prodi',
        'brand_sub' => 'Admin Program Studi JTI',
        'portal'    => 'Portal Admin Prodi',
    ],
    'kajur' => [
        'data_unit' => 'kajur',
        'topbar'    => 'SIPEKA Kajur',
        'brand'     => 'SIPEKA Kajur',
        'brand_sub' => 'Ketua Jurusan TI Polinema',
        'portal'    => 'Portal Kajur',
    ],
    'kps' => [
        'data_unit' => 'kps',
        'topbar'    => 'SIPEKA KPS',
        'brand'     => 'SIPEKA KPS',
        'brand_sub' => 'Ketua Program Studi JTI',
        'portal'    => 'Portal KPS',
    ],
    'sekjur' => [
        'data_unit' => 'sekjur',
        'topbar'    => 'SIPEKA Sekjur',
        'brand'     => 'SIPEKA Sekjur',
        'brand_sub' => 'Sekretariat Jurusan (Sarpras)',
        'portal'    => 'Portal Sekjur',
    ],
];
