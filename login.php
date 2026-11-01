<?php
require __DIR__ . '/config/bootstrap.php';
if (!empty($_SESSION['user_id'])) redirect('dashboard.php');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 verify_csrf();
 $stmt = db()->prepare('SELECT * FROM users WHERE email=?'); $stmt->execute([trim($_POST['email'] ?? '')]); $u=$stmt->fetch();
 if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
   session_regenerate_id(true); $_SESSION['user_id']=$u['id']; redirect('dashboard.php');
 } else $error='Invalid email or password.';
}
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Sign in</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/app.css" rel="stylesheet"></head><body class="auth-bg"><main class="container py-5"><div class="auth-card mx-auto"><div class="brand-mark mb-3">RE</div><h3 class="fw-bold">Real Estate Inventory</h3><p class="text-secondary">Sign in to manage your properties.</p><?php if($error): ?><div class="alert alert-danger"><?=h($error)?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Password</label><input class="form-control mb-3" type="password" name="password" required><button class="btn btn-primary w-100">Sign in</button></form><p class="small text-secondary mt-3 mb-0">First time? Run <a href="install.php">installation</a>.</p></div></main></body></html>
