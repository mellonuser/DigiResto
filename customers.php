<?php
$title = 'Pelanggan'; $active = 'customers';
require 'includes/header.php';

$customer = new Customer();

// Tangani tambah, ubah, dan hapus pelanggan melalui satu endpoint POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    $id = (int)($_POST['id'] ?? 0);
    if ($_POST['action'] === 'save') {
        $d = [trim($_POST['name']), trim($_POST['phone']), trim($_POST['email']) ?: null, trim($_POST['notes']) ?: null];
        if ($d[0] === '' || $d[1] === '') Flash::set('Nama dan telepon wajib diisi.', 'danger');
        elseif ($id) { $customer->update($id, ...$d); Flash::set('Data pelanggan diperbarui.'); }
        else { $customer->create(...$d); Flash::set('Pelanggan ditambahkan.'); }
    } elseif ($_POST['action'] === 'delete') {
        try { $customer->delete($id); Flash::set('Pelanggan dihapus.'); }
        catch (PDOException $ex) { Flash::set('Pelanggan tidak bisa dihapus karena memiliki riwayat pemesanan.', 'danger'); }
    }
    Helper::redirect('customers.php');
}
// Pencarian mencakup nama dan nomor telepon, sedangkan riwayat dihitung lewat LEFT JOIN.
$q = trim($_GET['q'] ?? '');
$rows = $customer->search($q);
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h3 fw-bold mb-0">Pelanggan</h1>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#m" onclick="fill({})"><i class="bi bi-plus-lg me-1"></i>Pelanggan baru</button>
</div>
<form class="mb-3" style="max-width:360px"><input name="q" value="<?= Helper::e($q) ?>" class="form-control" placeholder="Cari nama atau telepon"></form>
<div class="card"><div class="table-responsive"><table class="table align-middle mb-0">
  <thead><tr><th>PELANGGAN</th><th>KONTAK</th><th>KUNJUNGAN</th><th>TERAKHIR</th><th></th></tr></thead><tbody>
  <?php foreach ($rows as $c): ?>
    <tr><td><div class="d-flex align-items-center gap-2"><span class="avatar"><?= Helper::e(Helper::initials($c['name'])) ?></span><span><b class="small"><?= Helper::e($c['name']) ?></b><br><small class="text-muted"><?= Helper::e($c['notes']) ?></small></span></div></td>
      <td><?= Helper::e($c['phone']) ?><br><small class="text-muted"><?= Helper::e($c['email']) ?></small></td>
      <td><?= $c['total'] ?>x</td><td><?= $c['last_visit'] ? Helper::tgl($c['last_visit']) : '-' ?></td>
      <td class="text-end text-nowrap">
        <button class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#m" onclick='fill(<?= json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i class="bi bi-pencil"></i></button>
        <form method="post" class="d-inline" onsubmit="return confirm('Hapus pelanggan ini?')"><?= Auth::csrfField() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $c['id'] ?>">
          <button class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button></form></td></tr>
  <?php endforeach; if (!$rows): ?><tr><td colspan="5" class="text-center text-muted py-4">Belum ada pelanggan.</td></tr><?php endif; ?>
</tbody></table></div></div>

<div class="modal fade" id="m" tabindex="-1"><div class="modal-dialog"><form method="post" class="modal-content"><?= Auth::csrfField() ?>
  <input type="hidden" name="action" value="save"><input type="hidden" name="id" id="f_id">
  <div class="modal-header"><h5 class="modal-title">Data pelanggan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body vstack gap-3">
    <div><label class="form-label">Nama</label><input name="name" id="f_name" class="form-control" required></div>
    <div><label class="form-label">Telepon</label><input name="phone" id="f_phone" class="form-control" required></div>
    <div><label class="form-label">Email</label><input type="email" name="email" id="f_email" class="form-control"></div>
    <div><label class="form-label">Catatan</label><input name="notes" id="f_notes" class="form-control" maxlength="255"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan pelanggan</button></div>
</form></div></div>
<script>
function fill(c){['id','name','phone','email','notes'].forEach(k=>document.getElementById('f_'+k).value=c[k]||'');}
</script>
<?php require 'includes/footer.php'; ?>
