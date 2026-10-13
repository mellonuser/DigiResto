<?php
$title = 'Pemesanan'; $active = 'reservations';
require 'includes/header.php';

$reservation = new Reservation();

// Tangani perubahan status dan penghapusan sebelum mengambil data daftar.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    $id = (int)$_POST['id'];
    if ($_POST['action'] === 'status' && Reservation::isValidStatus($_POST['status'])) {
        $reservation->updateStatus($id, $_POST['status']);
        Flash::set('Status pemesanan diperbarui.');
    } elseif ($_POST['action'] === 'delete') {
        $reservation->delete($id);
        Flash::set('Pemesanan dihapus.');
    }
    Helper::redirect($_SERVER['HTTP_REFERER'] ?? 'reservations.php');
}

// Filter disusun di dalam class Reservation dengan parameter binding.
$q = trim($_GET['q'] ?? ''); $date = $_GET['date'] ?? ''; $status = $_GET['status'] ?? '';
$rows = $reservation->search($q, $date, $status);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 fw-bold mb-0">Pemesanan</h1>
  <a href="reservation_form.php" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Pemesanan baru</a>
</div>
<form class="card card-body mb-3"><div class="row g-2">
  <div class="col-md-4"><input name="q" value="<?= Helper::e($q) ?>" class="form-control" placeholder="Nama, kode, atau telepon"></div>
  <div class="col-md-3"><input type="date" name="date" value="<?= Helper::e($date) ?>" class="form-control"></div>
  <div class="col-md-3"><select name="status" class="form-select"><option value="">Semua status</option>
    <?php foreach (Reservation::STATUS as $k => [$l]): ?><option value="<?= $k ?>" <?= $status === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2 d-flex gap-2"><button class="btn btn-primary flex-fill">Cari</button><a href="reservations.php" class="btn btn-light">Reset</a></div>
</div></form>

<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th>KODE</th><th>PELANGGAN</th><th>TANGGAL & WAKTU</th><th>TAMU</th><th>MEJA</th><th>STATUS</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><a href="reservation_view.php?id=<?= $r['id'] ?>" class="fw-semibold"><?= Helper::e($r['code']) ?></a></td>
      <td><b class="small"><?= Helper::e($r['cname']) ?></b><br><small class="text-muted"><?= Helper::e($r['phone']) ?></small></td>
      <td><?= Helper::tgl($r['reservation_date']) ?><br><small class="text-muted"><?= substr($r['reservation_time'], 0, 5) ?></small></td>
      <td><?= $r['guests'] ?> orang</td>
      <td><span class="badge text-bg-light border"><?= Helper::e($r['tcode']) ?></span></td>
      <td>
        <form method="post" class="d-inline"><?= Auth::csrfField() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <select name="status" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <?php foreach (Reservation::STATUS as $k => [$l]): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
          </select></form>
      </td>
      <td class="text-end text-nowrap">
        <a href="reservation_view.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-light" title="Detail"><i class="bi bi-eye"></i></a>
        <a href="reservation_form.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-light" title="Ubah"><i class="bi bi-pencil"></i></a>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus pemesanan ini?')"><?= Auth::csrfField() ?>
          <input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
          <button class="btn btn-sm btn-light text-danger" title="Hapus"><i class="bi bi-trash"></i></button></form>
      </td>
    </tr>
  <?php endforeach; if (!$rows): ?>
    <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada pemesanan yang cocok dengan filter.</td></tr>
  <?php endif; ?>
</tbody></table></div></div>
<?php require 'includes/footer.php'; ?>
