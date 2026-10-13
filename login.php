<?php
require 'config.php';
// Sediakan akun awal agar instalasi baru bisa langsung digunakan.
// Hapus mekanisme ini saat aplikasi dipindahkan ke production.
Auth::ensureDefaultUser();
$error = '';
// Proses login hanya ketika form dikirim dengan metode POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Auth::checkCsrf();
    if (Auth::attempt($_POST['username'] ?? '', $_POST['password'] ?? '')) {
        Helper::redirect('index.php');
    }
    $error = 'Username atau kata sandi salah.';
}
?>
<!DOCTYPE html>
<html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · Restoar</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="assets/style.css" rel="stylesheet">
</head><body class="d-flex align-items-center justify-content-center min-vh-100">
<div class="card p-4 shadow-sm" style="width:100%;max-width:380px">
  <div class="d-flex align-items-center gap-2 mb-3">
    <span class="brand-icon"><i class="bi bi-cup-hot"></i></span>
    <div><div class="fw-bold fs-5 lh-1">DigiResto</div><small class="text-muted">Digital Restaurant</small></div>
  </div>
  <h1 class="h5 mb-3">Masuk ke akun Anda</h1>
  <?php if ($error): ?><div class="alert alert-danger py-2"><?= Helper::e($error) ?></div><?php endif; ?>
  <form method="post"><?= Auth::csrfField() ?>
    <div class="mb-3"><label class="form-label">Username</label><input name="username" class="form-control" required autofocus></div>
    <div class="mb-3"><label class="form-label">Kata sandi</label><input type="password" name="password" class="form-control" required></div>
    <button class="btn btn-primary w-100">Masuk</button>
  </form>
</div>
</body></html>
