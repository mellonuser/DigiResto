<?php
require_once __DIR__ . '/../config.php';
Auth::requireLogin();

// Badge navigasi menunjukkan jumlah pemesanan yang masih menunggu konfirmasi.
$pendingCount = (new Reservation())->countPending();
$nav = [
  'index'        => ['Ringkasan', 'bi-grid', 'index.php'],
  'reservations' => ['Pemesanan', 'bi-calendar3', 'reservations.php'],
  'customers'    => ['Pelanggan', 'bi-people', 'customers.php'],
  'tables'       => ['Meja', 'bi-table', 'tables.php'],
  'menus'        => ['Menu', 'bi-journal-text', 'menus.php'],
  'reports'      => ['Laporan', 'bi-bar-chart', 'reports.php'],
];
$u = Auth::user();
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= Helper::e($title ?? 'DigiResto') ?> · DigiResto</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head><body>
<aside class="sidebar">
  <div class="d-flex align-items-center gap-2 mb-4 px-2">
    <span class="brand-icon"><i class="bi bi-cup-hot"></i></span>
    <div><div class="fw-bold fs-5 lh-1">DigiResto</div><small class="text-muted">Digital Restaurant</small></div>
  </div>
  <nav class="nav flex-column gap-1">
    <?php foreach ($nav as $k => [$label, $icon, $href]): ?>
      <a class="nav-link <?= ($active ?? '') === $k ? 'active' : '' ?>" href="<?= $href ?>">
        <i class="bi <?= $icon ?>"></i><?= $label ?>
        <?php if ($k === 'reservations' && $pendingCount): ?><span class="badge rounded-pill bg-primary ms-auto"><?= $pendingCount ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="mt-auto border-top pt-3 d-flex align-items-center gap-2">
    <div class="avatar"><?= Helper::e(Helper::initials($u['name'])) ?></div>
    <div class="lh-sm"><div class="fw-semibold small"><?= Helper::e($u['name']) ?></div><small class="text-muted"><?= Helper::e($u['role']) ?></small></div>
    <a href="logout.php" class="ms-auto text-muted" title="Keluar"><i class="bi bi-box-arrow-right"></i></a>
  </div>
</aside>
<div class="main">
  <div class="topbar d-flex justify-content-between align-items-center no-print">
    <form action="reservations.php" class="w-50" style="max-width:420px">
      <div class="input-group"><span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
      <input name="q" class="form-control bg-light border-0" placeholder="Cari pelanggan atau pemesanan..."></div>
    </form>
  </div>
  <div class="content">
  <?php if ($f = Flash::get()): ?>
    <div class="alert alert-<?= $f[1] ?> alert-dismissible fade show"><?= Helper::e($f[0]) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
  <?php endif; ?>
