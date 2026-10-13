<?php
$title = 'Form pemesanan'; $active = 'reservations';
require 'includes/header.php';

$reservation = new Reservation();
$customerModel = new Customer();
$tableModel = new DiningTable();

$id = (int)($_GET['id'] ?? 0);
// Nilai default dipakai saat membuat pemesanan baru dan ditimpa data database saat mengedit.
$r = ['customer_id' => '', 'table_id' => $_GET['table'] ?? '', 'reservation_date' => date('Y-m-d'), 'reservation_time' => '19:00',
      'guests' => 2, 'status' => 'pending', 'notes' => ''];
if ($id) {
    $found = $reservation->find($id);
    if (!$found) Helper::redirect('reservations.php');
    $r = $found;
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    $r = array_merge($r, [
        'customer_id' => (int)$_POST['customer_id'], 'table_id' => (int)$_POST['table_id'],
        'reservation_date' => $_POST['reservation_date'], 'reservation_time' => $_POST['reservation_time'],
        'guests' => (int)$_POST['guests'], 'status' => $_POST['status'], 'notes' => trim($_POST['notes']),
    ]);
    // Jika pelanggan belum dipilih, validasi lalu buat data pelanggan baru di bawah.
    if (!$r['customer_id']) {
        $nm = trim($_POST['new_name']); $ph = trim($_POST['new_phone']);
        if ($nm === '' || $ph === '') $errors[] = 'Pilih pelanggan atau isi nama dan telepon pelanggan baru.';
    }
    $tb = $tableModel->findActive($r['table_id']);
    if (!$tb) $errors[] = 'Pilih meja.';
    elseif ($r['guests'] < 1 || $r['guests'] > $tb['capacity']) $errors[] = "Jumlah tamu harus 1–{$tb['capacity']} untuk meja {$tb['code']}.";
    if (!Reservation::isValidStatus($r['status'])) $errors[] = 'Status tidak valid.';
    // Pemesanan yang sudah batal/selesai tidak lagi memblokir meja.
    if (!$errors && !in_array($r['status'], ['cancelled', 'completed']) &&
        $reservation->hasConflict($r['table_id'], $r['reservation_date'], $r['reservation_time'], $id))
        $errors[] = 'Meja sudah dipesan pada waktu tersebut (jeda minimal 2 jam). Pilih meja atau jam lain.';

    if (!$errors) {
        if (!$r['customer_id']) {
            $r['customer_id'] = $customerModel->create($nm, $ph, trim($_POST['new_email']) ?: null);
        }
        // Gunakan method berbeda untuk mode edit dan mode tambah.
        if ($id) {
            $reservation->update($id, $r);
        } else {
            $id = $reservation->create($r);
        }
        Flash::set('Pemesanan disimpan.');
        Helper::redirect("reservation_view.php?id=$id");
    }
}
$customers = $customerModel->options();
$tables = $tableModel->allActive();
?>
<h1 class="h3 fw-bold mb-3"><?= $id ? 'Ubah pemesanan' : 'Pemesanan baru' ?></h1>
<?php foreach ($errors as $er): ?><div class="alert alert-danger py-2"><?= Helper::e($er) ?></div><?php endforeach; ?>
<form method="post" class="card card-body" style="max-width:820px"><?= Auth::csrfField() ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Pelanggan</label>
      <select name="customer_id" id="cust" class="form-select">
        <option value="">+ Pelanggan baru</option>
        <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>" <?= $r['customer_id'] == $c['id'] ? 'selected' : '' ?>><?= Helper::e($c['name']) ?> (<?= Helper::e($c['phone']) ?>)</option><?php endforeach; ?>
      </select></div>
    <div class="col-md-6" id="newCust"><div class="row g-2">
      <div class="col-12"><label class="form-label">Nama pelanggan baru</label><input name="new_name" class="form-control" value="<?= Helper::e($_POST['new_name'] ?? '') ?>"></div>
      <div class="col-6"><input name="new_phone" class="form-control" placeholder="Telepon" value="<?= Helper::e($_POST['new_phone'] ?? '') ?>"></div>
      <div class="col-6"><input name="new_email" type="email" class="form-control" placeholder="Email (opsional)" value="<?= Helper::e($_POST['new_email'] ?? '') ?>"></div>
    </div></div>
    <div class="col-md-4"><label class="form-label">Tanggal</label><input type="date" name="reservation_date" class="form-control" required value="<?= Helper::e($r['reservation_date']) ?>"></div>
    <div class="col-md-4"><label class="form-label">Jam</label><input type="time" name="reservation_time" class="form-control" required value="<?= Helper::e(substr($r['reservation_time'], 0, 5)) ?>"></div>
    <div class="col-md-4"><label class="form-label">Jumlah tamu</label><input type="number" min="1" name="guests" class="form-control" required value="<?= Helper::e($r['guests']) ?>"></div>
    <div class="col-md-6"><label class="form-label">Meja</label>
      <select name="table_id" class="form-select" required><option value="">Pilih meja</option>
        <?php foreach ($tables as $t): ?><option value="<?= $t['id'] ?>" <?= $r['table_id'] == $t['id'] ? 'selected' : '' ?>><?= Helper::e($t['code']) ?> · <?= $t['capacity'] ?> kursi</option><?php endforeach; ?>
      </select></div>
    <div class="col-md-6"><label class="form-label">Status</label>
      <select name="status" class="form-select"><?php foreach (Reservation::STATUS as $k => [$l]): ?><option value="<?= $k ?>" <?= $r['status'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><label class="form-label">Catatan (alergi, diet, acara khusus)</label><input name="notes" class="form-control" maxlength="255" value="<?= Helper::e($r['notes']) ?>"></div>
  </div>
  <div class="mt-4 d-flex gap-2"><button class="btn btn-primary">Simpan pemesanan</button><a href="reservations.php" class="btn btn-light">Batal</a></div>
</form>
<script>
const cust = document.getElementById('cust'), nc = document.getElementById('newCust');
// Sembunyikan field pelanggan baru ketika pelanggan yang sudah ada dipilih.
const toggle = () => nc.style.display = cust.value ? 'none' : '';
cust.addEventListener('change', toggle); toggle();
</script>
<?php require 'includes/footer.php'; ?>
