<?php
$title = 'Detail pemesanan'; $active = 'reservations';
require 'includes/header.php';
$id = (int)($_GET['id'] ?? 0);

$reservation = new Reservation();
$orderItem = new OrderItem();
$menuModel = new Menu();

// Tambah atau hapus item menu dari pemesanan yang sedang dilihat.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    if ($_POST['action'] === 'add') {
        $m = $menuModel->find((int)$_POST['menu_id']);
        $qty = max(1, (int)$_POST['qty']);
        if ($m) {
            $orderItem->add($id, $m, $qty);
            Flash::set('Menu ditambahkan ke pesanan.');
        }
    } elseif ($_POST['action'] === 'remove') {
        $orderItem->removeFromReservation((int)$_POST['item_id'], $id);
        Flash::set('Item dihapus dari pesanan.');
    }
    Helper::redirect("reservation_view.php?id=$id");
}

$r = $reservation->findDetail($id);
if (!$r) Helper::redirect('reservations.php');
$items = $orderItem->byReservation($id);
$menus = $menuModel->allOrdered();
// Total dihitung dari harga saat item dipesan, bukan harga menu terbaru.
$total = OrderItem::total($items);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 fw-bold mb-0"><?= Helper::e($r['code']) ?> <?= Helper::badge($r['status']) ?></h1>
  <div class="d-flex gap-2 no-print">
    <button onclick="print()" class="btn btn-light"><i class="bi bi-printer"></i> Cetak</button>
    <a href="reservation_form.php?id=<?= $id ?>" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>Ubah</a>
  </div>
</div>
<div class="row g-3">
  <div class="col-lg-4"><div class="card card-body">
    <div class="d-flex align-items-center gap-2 mb-3"><span class="avatar"><?= Helper::e(Helper::initials($r['cname'])) ?></span>
      <div><b><?= Helper::e($r['cname']) ?></b><br><small class="text-muted"><?= Helper::e($r['phone']) ?> <?= $r['email'] ? '· ' . Helper::e($r['email']) : '' ?></small></div></div>
    <dl class="row mb-0 small">
      <dt class="col-5 text-muted fw-normal">Tanggal</dt><dd class="col-7"><?= Helper::tgl($r['reservation_date']) ?></dd>
      <dt class="col-5 text-muted fw-normal">Jam</dt><dd class="col-7"><?= substr($r['reservation_time'], 0, 5) ?></dd>
      <dt class="col-5 text-muted fw-normal">Meja</dt><dd class="col-7"><?= Helper::e($r['tcode']) ?> (<?= $r['capacity'] ?> kursi)</dd>
      <dt class="col-5 text-muted fw-normal">Tamu</dt><dd class="col-7"><?= $r['guests'] ?> orang</dd>
      <dt class="col-5 text-muted fw-normal">Catatan</dt><dd class="col-7"><?= Helper::e($r['notes'] ?: '-') ?></dd>
    </dl>
  </div></div>
  <div class="col-lg-8"><div class="card">
    <div class="card-header fw-semibold">Pesanan menu</div>
    <div class="table-responsive"><table class="table align-middle mb-0">
      <thead><tr><th>MENU</th><th>HARGA</th><th>QTY</th><th class="text-end">SUBTOTAL</th><th class="no-print"></th></tr></thead><tbody>
      <?php foreach ($items as $i): ?>
        <tr><td><?= Helper::e($i['name']) ?></td><td><?= Helper::rupiah($i['price']) ?></td><td><?= $i['qty'] ?></td><td class="text-end"><?= Helper::rupiah($i['qty'] * $i['price']) ?></td>
          <td class="text-end no-print"><form method="post"><?= Auth::csrfField() ?><input type="hidden" name="action" value="remove"><input type="hidden" name="item_id" value="<?= $i['id'] ?>">
            <button class="btn btn-sm btn-light text-danger"><i class="bi bi-x-lg"></i></button></form></td></tr>
      <?php endforeach; if (!$items): ?><tr><td colspan="5" class="text-center text-muted py-3">Belum ada menu yang dipesan.</td></tr><?php endif; ?>
      </tbody>
      <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end"><?= Helper::rupiah($total) ?></th><th class="no-print"></th></tr></tfoot>
    </table></div>
    <form method="post" class="card-body border-top d-flex gap-2 no-print"><?= Auth::csrfField() ?><input type="hidden" name="action" value="add">
      <select name="menu_id" class="form-select" required><option value="">Pilih menu</option>
        <?php foreach ($menus as $m): ?><option value="<?= $m['id'] ?>"><?= Helper::e($m['name']) ?> – <?= Helper::rupiah($m['price']) ?></option><?php endforeach; ?></select>
      <input type="number" name="qty" value="1" min="1" class="form-control" style="width:90px">
      <button class="btn btn-primary text-nowrap">Tambah</button>
    </form>
  </div></div>
</div>
<?php require 'includes/footer.php'; ?>
