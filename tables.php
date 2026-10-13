<?php
$title = 'Meja'; $active = 'tables';
require 'includes/header.php';

$tableModel = new DiningTable();

// Form modal memakai endpoint yang sama untuk tambah dan ubah meja.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    $id = (int)($_POST['id'] ?? 0);
    if ($_POST['action'] === 'save') {
        $code = strtoupper(trim($_POST['code'])); $cap = (int)$_POST['capacity']; $act = isset($_POST['is_active']) ? 1 : 0;
        if ($code === '' || $cap < 1) Flash::set('Kode meja dan kapasitas wajib diisi.', 'danger');
        else try {
            if ($id) $tableModel->update($id, $code, $cap, $act);
            else $tableModel->create($code, $cap, $act);
            Flash::set('Meja disimpan.');
        } catch (PDOException $ex) { Flash::set("Kode meja $code sudah digunakan.", 'danger'); }
    } elseif ($_POST['action'] === 'delete') {
        try { $tableModel->delete($id); Flash::set('Meja dihapus.'); }
        catch (PDOException $ex) { Flash::set('Meja memiliki riwayat pemesanan. Nonaktifkan saja.', 'danger'); }
    }
    Helper::redirect('tables.php');
}
$today = date('Y-m-d');
// Hitung pemesanan aktif per meja untuk menampilkan status operasional hari ini.
$rows = $tableModel->allWithBookings($today);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 fw-bold mb-0">Meja</h1>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#m" onclick="fill({is_active:1})"><i class="bi bi-plus-lg me-1"></i>Meja baru</button>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th>KODE</th><th>KAPASITAS</th><th>PEMESANAN HARI INI</th><th>STATUS</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $t): ?>
    <tr><td><b><?= Helper::e($t['code']) ?></b></td><td><?= $t['capacity'] ?> kursi</td><td><?= $t['booked'] ?></td>
      <td><?= $t['is_active'] ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>' ?></td>
      <td class="text-end text-nowrap">
        <a href="reservation_form.php?table=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Pesan</a>
        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#m" onclick='fill(<?= json_encode($t) ?>)'><i class="bi bi-pencil"></i></button>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus meja ini?')"><?= Auth::csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $t['id'] ?>">
          <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form></td></tr>
  <?php endforeach; ?>
</tbody></table></div></div>

<div class="modal fade" id="m" tabindex="-1"><div class="modal-dialog"><form method="post" class="modal-content"><?= Auth::csrfField() ?>
  <input type="hidden" name="action" value="save"><input type="hidden" name="id" id="f_id">
  <div class="modal-header"><h5 class="modal-title">Data meja</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body vstack gap-3">
    <div><label class="form-label">Kode meja</label><input name="code" id="f_code" class="form-control" required></div>
    <div><label class="form-label">Kapasitas (kursi)</label><input type="number" min="1" name="capacity" id="f_capacity" class="form-control" required></div>
    <div class="form-check"><input type="checkbox" class="form-check-input" name="is_active" id="f_is_active"><label class="form-check-label" for="f_is_active">Meja aktif</label></div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan meja</button></div>
</form></div></div>
<script>
// Isi modal dengan data meja yang dipilih, atau kosongkan untuk meja baru.
function fill(t){f_id.value=t.id||'';f_code.value=t.code||'';f_capacity.value=t.capacity||'';f_is_active.checked=t.is_active==1;}
</script>
<?php require 'includes/footer.php'; ?>
