<?php
/**
 * includes/unit/layout-start.php
 * Membuka halaman unit: <head>, topbar mobile, sidebar, <main>, dan header.
 * Pasangannya: includes/unit/layout-end.php (menutup layout + memuat script).
 *
 * Variabel yang harus diisi halaman SEBELUM include:
 *   $unitKey    string  nama folder unit ('sekjur' | 'admin-prodi' | 'kajur' | 'kps')
 *   $pageTitle  string  isi <title>
 *   $breadcrumb string  HTML isi breadcrumb (mis. 'Portal Sekjur &rsaquo; <strong>Ulasan</strong>')
 *   $activeMenu string  'dashboard' | 'ulasan' | 'profil' (default 'dashboard')
 */

$authRole = 'unit';
require __DIR__ . '/../auth-guard.php';

$unitConfig = require __DIR__ . '/config.php';
$cfg = $unitConfig[$unitKey] ?? null;
if ($cfg === null) {
    throw new RuntimeException('Unit tidak dikenal: ' . $unitKey);
}

$pageTitle  = $pageTitle  ?? 'SIPEKA';
$breadcrumb = $breadcrumb ?? '';
$activeMenu = $activeMenu ?? 'dashboard';

$menu = [
    'dashboard' => [
        'label' => 'Daftar Laporan Masuk',
        'href'  => 'dashboard.php',
        'icon'  => '<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                <rect width="8" height="4" x="8" y="2" rx="1" ry="1" />',
    ],
    'ulasan' => [
        'label' => 'Ulasan',
        'href'  => 'ulasan.php',
        'icon'  => '<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />',
    ],
    'profil' => [
        'label' => 'Profil Unit',
        'href'  => 'profil.php',
        'icon'  => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />',
    ],
];
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
  <?php include __DIR__ . '/../styles.php'; ?>
</head>

<body data-unit="<?= htmlspecialchars($cfg['data_unit'], ENT_QUOTES, 'UTF-8') ?>">
  <!-- Mobile Topbar -->
  <div class="mobile-topbar topbar--unit">
    <div class="topbar-brand">
      <div class="logo-badge logo-badge--sm"><img src="../../../assets/images/logo/JTI.webp" alt="Logo SIPEKA"
          class="logo-JTI"></div>
      <span class="topbar-title"><?= htmlspecialchars($cfg['topbar'], ENT_QUOTES, 'UTF-8') ?></span>
    </div>
    <button class="hamburger-btn" aria-label="Buka Menu">
      <svg class="icon" viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none"
        stroke-linecap="round" stroke-linejoin="round">
        <line x1="4" x2="20" y1="12" y2="12" />
        <line x1="4" x2="20" y1="6" y2="6" />
        <line x1="4" x2="20" y1="18" y2="18" />
      </svg>
    </button>
  </div>

  <div class="sidebar-backdrop"></div>

  <div class="app-layout">
    <aside class="app-sidebar sidebar--unit">
      <div>
        <div class="sidebar-logo">
          <div class="logo-badge"><img src="../../../assets/images/logo/JTI.webp" alt="Logo SIPEKA" class="logo-JTI">
          </div>
          <div class="logo-text">
            <h1><?= htmlspecialchars($cfg['brand'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p><?= htmlspecialchars($cfg['brand_sub'], ENT_QUOTES, 'UTF-8') ?></p>
          </div>
        </div>

        <ul class="sidebar-menu">
          <?php foreach ($menu as $key => $item): ?>
          <li<?= $key === $activeMenu ? ' class="active"' : '' ?>>
            <a href="<?= $item['href'] ?>">
              <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
                fill="none" stroke-linecap="round" stroke-linejoin="round">
                <?= $item['icon'] ?>
              </svg>
              <span><?= $item['label'] ?></span>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>

      <div class="sidebar-footer">
        <a href="../../auth/login.php" data-logout>
          <svg class="icon" viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2"
            fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" x2="9" y1="12" y2="12" />
          </svg>
          <span>Keluar</span>
        </a>
      </div>
    </aside>

    <main class="app-main">
      <header class="app-header">
        <div class="header-breadcrumb">
          <span class="breadcrumb-text"><?= $breadcrumb ?></span>
        </div>
        <div class="user-profile user-profile--clickable" onclick="window.location.href='profil.php'">
          <div class="avatar avatar--unit"> </div>
          <div class="user-info">
            <div class="user-name"> </div>
            <div class="user-role"> </div>
          </div>
        </div>
      </header>
