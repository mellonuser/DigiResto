<?php
require_once 'config.php'; Auth::requireLogin();
$from = $_GET['from'] ?? date('Y-m-01'); $to = $_GET['to'] ?? date('Y-m-d');

$report = new Report();
$byDay = $report->byDay($from, $to);

// Ekspor dilakukan sebelum template HTML dimuat agar output hanya berisi CSV.
if (($_GET['export'] ?? '') === 'csv') {
    $report->exportCsv($from, $to, $byDay);
}

$title = 'Laporan'; $active = 'reports';
require 'includes/header.php';

$byStatus = $report->byStatus($from, $to);
$topCust  = $report->topCustomers($from, $to);
$topMenu  = $report->topMenus($from, $to);

// Jumlahkan kolom rekap untuk nilai kartu di bagian atas laporan.
$cards = [['Total pemesanan', Report::sum($byDay, 'total')], ['Total tamu', Report::sum($byDay, 'guests')], ['Selesai', Report::sum($byDay, 'done')],
          ['Dibatalkan', Report::sum($byDay, 'cancelled')], ['Pendapatan pesanan', Helper::rupiah(Report::sum($byDay, 'revenue'))]];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <div><h1 class="h3 fw-bold mb-0">Laporan pemesanan</h1><small class="text-muted">Periode <?= Helper::tgl($from) ?> – <?= Helper::tgl($to) ?></small></div>
  <div class="d-flex gap-2 no-print">
    <a href="?from=<?= Helper::e($from) ?>&to=<?= Helper::e($to) ?>&export=csv" class="btn btn-light"><i class="bi bi-filetype-csv"></i> Ekspor CSV</a>
    <button onclick="print()" class="btn btn-primary"><i class="bi bi-printer me-1"></i>Cetak</button>
  </div>
</div>
<form class="card card-body mb-3 no-print"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label">Dari</label><input type="date" name="from" value="<?= Helper::e($from) ?>" class="form-control"></div>
  <div class="col-md-3"><label class="form-label">Sampai</label><input type="date" name="to" value="<?= Helper::e($to) ?>" class="form-control"></div>
  <div class="col-md-2"><button class="btn btn-primary w-100">Tampilkan</button></div>
</div></form>

<div class="row g-3 mb-3">
<?php foreach ($cards as [$l, $v]): ?>
  <div class="col-6 col-lg"><div class="card p-3"><div class="text-muted small"><?= $l ?></div><div class="fs-4 fw-bold"><?= $v ?></div></div></div>
<?php endforeach; ?>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-8"><div class="card"><div class="card-header fw-semibold">Rekap harian</div>
    <div class="table-responsive"><table class="table mb-0">
      <thead><tr><th>TANGGAL</th><th>PEMESANAN</th><th>TAMU</th><th>SELESAI</th><th>BATAL</th><th class="text-end">PENDAPATAN</th></tr></thead><tbody>
      <?php foreach ($byDay as $r): ?><tr><td><?= Helper::tgl($r['d']) ?></td><td><?= $r['total'] ?></td><td><?= $r['guests'] ?></td><td><?= $r['done'] ?></td><td><?= $r['cancelled'] ?></td><td class="text-end"><?= Helper::rupiah($r['revenue']) ?></td></tr>
      <?php endforeach; if (!$byDay): ?><tr><td colspan="6" class="text-center text-muted py-4">Tidak ada data pada periode ini.</td></tr><?php endif; ?>
    </tbody></table></div></div></div>
  <div class="col-lg-4"><div class="card h-100"><div class="card-header fw-semibold">Berdasarkan status</div>
    <ul class="list-group list-group-flush">
      <?php foreach (Reservation::STATUS as $k => [$l]): ?><li class="list-group-item d-flex justify-content-between"><?= Helper::badge($k) ?><b><?= $byStatus[$k] ?? 0 ?></b></li><?php endforeach; ?>
    </ul></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-6"><div class="card"><div class="card-header fw-semibold">Pelanggan teratas</div>
    <table class="table mb-0"><thead><tr><th>NAMA</th><th>KUNJUNGAN</th><th>TAMU</th></tr></thead><tbody>
    <?php foreach ($topCust as $c): ?><tr><td><?= Helper::e($c['name']) ?></td><td><?= $c['n'] ?></td><td><?= $c['g'] ?></td></tr>
    <?php endforeach; if (!$topCust): ?><tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr><?php endif; ?></tbody></table></div></div>
  <div class="col-lg-6"><div class="card"><div class="card-header fw-semibold">Menu terlaris</div>
    <table class="table mb-0"><thead><tr><th>MENU</th><th>TERJUAL</th><th class="text-end">NILAI</th></tr></thead><tbody>
    <?php foreach ($topMenu as $m): ?><tr><td><?= Helper::e($m['name']) ?></td><td><?= $m['qty'] ?></td><td class="text-end"><?= Helper::rupiah($m['rev']) ?></td></tr>
    <?php endforeach; if (!$topMenu): ?><tr><td colspan="3" class="text-center text-muted">Belum ada pesanan menu.</td></tr><?php endif; ?></tbody></table></div></div>
</div>
<?php require 'includes/footer.php'; ?>
