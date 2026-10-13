<?php
$title = 'Menu'; $active = 'menus';
require 'includes/header.php';

$menuModel = new Menu();

// Simpan perubahan menu atau hapus menu yang belum dipakai oleh pesanan.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    $id = (int)($_POST['id'] ?? 0);
    if ($_POST['action'] === 'save') {
        $d = [trim($_POST['name']), $_POST['category'], (int)$_POST['price']];
        if ($d[0] === '' || $d[2] < 0) Flash::set('Nama dan harga wajib diisi.', 'danger');
        elseif ($id) { $menuModel->update($id, ...$d); Flash::set('Menu diperbarui.'); }
        else { $menuModel->create(...$d); Flash::set('Menu ditambahkan.'); }
    } elseif ($_POST['action'] === 'delete') {
        try { $menuModel->delete($id); Flash::set('Menu dihapus.'); }
        catch (PDOException $ex) { Flash::set('Menu sudah dipakai di pesanan, tidak bisa dihapus.', 'danger'); }
    }
    Helper::redirect('menus.php');
}
$rows = $menuModel->allOrdered();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 fw-bold mb-0">Menu</h1>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#m" onclick="fill({category:'Makanan'})"><i class="bi bi-plus-lg me-1"></i>Menu baru</button>
</div>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th>NAMA</th><th>KATEGORI</th><th>HARGA</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $m): ?>
    <tr><td><?= Helper::e($m['name']) ?></td><td><?= Helper::e($m['category']) ?></td><td><?= Helper::rupiah($m['price']) ?></td>
      <td class="text-end text-nowrap">
        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#m" onclick='fill(<?= json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="bi bi-pencil"></i></button>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus menu ini?')"><?= Auth::csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $m['id'] ?>">
          <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form></td></tr>
  <?php endforeach; ?>
</tbody></table></div></div>

<div class="modal fade" id="m" tabindex="-1"><div class="modal-dialog"><form method="post" class="modal-content"><?= Auth::csrfField() ?>
  <input type="hidden" name="action" value="save"><input type="hidden" name="id" id="f_id">
  <div class="modal-header"><h5 class="modal-title">Data menu</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body vstack gap-3">
    <div><label class="form-label">Nama menu</label><input name="name" id="f_name" class="form-control" required></div>
    <div><label class="form-label">Kategori</label><select name="category" id="f_category" class="form-select"><option>Makanan</option><option>Minuman</option><option>Dessert</option></select></div>
    <div><label class="form-label">Harga (Rp)</label><input type="number" min="0" name="price" id="f_price" class="form-control" required></div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan menu</button></div>
</form></div></div>
<script>
function fill(m){f_id.value=m.id||'';f_name.value=m.name||'';f_category.value=m.category||'Makanan';f_price.value=m.price||'';}
</script>
<?php require 'includes/footer.php'; ?>
