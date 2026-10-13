<?php
$title = 'Ringkasan'; $active = 'index';
require 'includes/header.php';
$today = date('Y-m-d');

// Kartu ringkasan mengambil metrik utama untuk tanggal hari ini.
$dashboard = new Dashboard();
$total   = $dashboard->totalByDate($today);
$pending = $dashboard->pendingByDate($today);
$guests  = $dashboard->guestsByDate($today);
$allTables = (new DiningTable())->allActive();

// Tentukan meja yang memiliki pemesanan aktif dan apakah sedang dilayani.
$tstat = $dashboard->tableStatuses($today);
$booked = count($tstat);
$occ = $allTables ? round($booked / count($allTables) * 100) : 0;

$list = $dashboard->upcomingReservations($today);

// Data per jam digunakan untuk membuat visualisasi jam tersibuk.
$hours = $dashboard->guestsPerHour($today);
$maxH = max($hours ?: [1]);

$notes = $dashboard->notesCount($today);

$hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
$jam = (int)date('G'); $sapa = $jam < 11 ? 'pagi' : ($jam < 15 ? 'siang' : ($jam < 18 ? 'sore' : 'malam'));
$stats = [
  ['Pemesanan hari ini', $total, 'bi-calendar3', '#efeafd', '#6c4ee0'],
  ['Menunggu konfirmasi', $pending, 'bi-clock', '#fdeeee', '#d9534f'],
  ['Total tamu', $guests, 'bi-people', '#e9f8f1', '#1d9a6c'],
  ['Okupansi meja', $occ . '%', 'bi-table', '#fff4de', '#e8a33d'],
];
?>
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <div class="text-primary small fw-semibold"><?= $hari[date('w')] ?>, <?= Helper::tgl($today) ?></div>
    <h1 class="h2 fw-bold mb-1">Selamat <?= $sapa ?>, <?= Helper::e(explode(' ', $u['name'])[0]) ?>.</h1>
    <div class="text-muted">Berikut ringkasan aktivitas restoran hari ini.</div>
  </div>
  <a href="reservation_form.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Pemesanan baru</a>
</div>

<div class="row g-3 mb-4">
<?php foreach ($stats as [$l, $v, $ic, $bg, $fg]): ?>
  <div class="col-6 col-xl-3"><div class="card p-3 h-100"><div class="d-flex align-items-center gap-3">
    <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $fg ?>"><i class="bi <?= $ic ?>"></i></div>
    <div><div class="text-muted small"><?= $l ?></div><div class="fs-3 fw-bold lh-1"><?= $v ?></div></div>
  </div></div></div>
<?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
  <div class="col-xl-8"><div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div><div class="fw-semibold">Pemesanan hari ini</div><small class="text-muted"><?= count($list) ?> pemesanan berikutnya</small></div>
      <a href="reservations.php?date=<?= $today ?>" class="small fw-semibold">Lihat semua</a>
    </div>
    <div class="table-responsive"><table class="table align-middle mb-0">
      <thead><tr><th>PELANGGAN</th><th>WAKTU</th><th>TAMU</th><th>MEJA</th><th>STATUS</th></tr></thead><tbody>
      <?php foreach ($list as $r): ?>
        <tr>
          <td><a href="reservation_view.php?id=<?= $r['id'] ?>" class="text-decoration-none text-reset d-flex align-items-center gap-2">
            <span class="avatar"><?= Helper::e(Helper::initials($r['cname'])) ?></span>
            <span><b class="small"><?= Helper::e($r['cname']) ?></b><br><small class="text-muted"><?= Helper::e($r['code']) ?></small></span></a></td>
          <td><?= substr($r['reservation_time'], 0, 5) ?></td>
          <td><?= $r['guests'] ?> orang</td>
          <td><span class="badge text-bg-light border"><?= Helper::e($r['tcode']) ?></span></td>
          <td><?= Helper::badge($r['status']) ?></td>
        </tr>
      <?php endforeach; if (!$list): ?>
        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada pemesanan hari ini. <a href="reservation_form.php">Buat pemesanan</a></td></tr>
      <?php endif; ?>
      </tbody></table></div>
  </div></div>

  <div class="col-xl-4"><div class="card h-100">
    <div class="card-header"><div class="fw-semibold">Status meja</div><small class="text-muted">Lantai utama</small></div>
    <div class="card-body">
      <div class="d-flex gap-3 justify-content-center small text-muted mb-3">
        <span><i class="bi bi-square-fill" style="color:#bfe8d5"></i> Tersedia</span>
        <span><i class="bi bi-square-fill" style="color:#f3c6c6"></i> Terisi</span>
        <span><i class="bi bi-square-fill" style="color:#d3c8f7"></i> Dipesan</span>
      </div>
      <div class="row g-2">
      <?php foreach ($allTables as $t):
        $cls = !isset($tstat[$t['id']]) ? 'available' : ($tstat[$t['id']] ? 'occupied' : 'reserved'); ?>
        <div class="col-3"><div class="tbl-box tbl-<?= $cls ?>"><i class="bi bi-table"></i><b><?= Helper::e($t['code']) ?></b><?= $t['capacity'] ?> kursi</div></div>
      <?php endforeach; ?>
      </div>
      <a href="tables.php" class="btn btn-outline-primary w-100 mt-3 btn-sm">Kelola meja</a>
    </div>
  </div></div>
</div>

<div class="row g-3">
  <div class="col-xl-8"><div class="card h-100">
    <div class="card-header"><div class="fw-semibold">Jam tersibuk</div><small class="text-muted">Distribusi tamu hari ini</small></div>
    <div class="card-body"><div class="bars">
      <?php for ($h = 10; $h <= 21; $h++): $g = $hours[$h] ?? 0; ?>
        <div class="<?= $g && $g == $maxH ? 'peak' : '' ?>" title="<?= $g ?> tamu"><span style="height:<?= max(4, $g / $maxH * 100) ?>%"></span><?= sprintf('%02d:00', $h) ?></div>
      <?php endfor; ?>
    </div></div>
  </div></div>
  <div class="col-xl-4"><div class="card h-100"><div class="card-body d-flex gap-3">
    <div class="stat-icon" style="background:#efeafd;color:#6c4ee0"><i class="bi bi-cup-hot"></i></div>
    <div><div class="fw-semibold">Catatan dapur</div>
      <div class="text-muted small mb-2"><?= $notes ?> tamu memiliki permintaan khusus terkait alergi dan diet.</div>
      <a href="reservations.php?date=<?= $today ?>" class="small fw-semibold">Lihat detail</a></div>
  </div></div></div>
</div>
<?php require 'includes/footer.php'; ?>
