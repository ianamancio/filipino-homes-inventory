<?php
require __DIR__ . '/config/bootstrap.php';
$error = '';
$done = false;
try {
    $sql = file_get_contents(__DIR__ . '/config/schema.sql');
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) db()->exec($statement);
} catch (Throwable $e) { $error = 'Database setup failed: ' . $e->getMessage(); }
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    verify_csrf();
    $name = trim($_POST['full_name'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $pass = $_POST['password'] ?? '';
    if (!$name || !$email || strlen($pass) < 10) $error = 'Enter a name, valid email, and password with at least 10 characters.';
    else {
        try {
            $stmt = db()->prepare('INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,?)');
            $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), 'admin']);
            $done = true;
        } catch (Throwable $e) { $error = 'Could not create admin. The email may already exist.'; }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Install - Real Estate Inventory</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/css/app.css" rel="stylesheet"></head><body class="auth-bg"><main class="container py-5"><div class="auth-card mx-auto"><h3 class="fw-bold">Set up your inventory system</h3><p class="text-secondary">Create the first administrator account.</p><?php if($error): ?><div class="alert alert-danger"><?=h($error)?></div><?php endif; ?><?php if($done): ?><div class="alert alert-success">Admin created. <a href="login.php">Sign in</a>. Delete install.php now.</div><?php else: ?><form method="post"><input type="hidden" name="csrf" value="<?=h(csrf_token())?>"><label class="form-label">Full name</label><input class="form-control mb-3" name="full_name" required><label class="form-label">Email</label><input class="form-control mb-3" type="email" name="email" required><label class="form-label">Password (10+ characters)</label><input class="form-control mb-3" type="password" name="password" minlength="10" required><button class="btn btn-primary w-100">Create administrator</button></form><?php endif; ?></div></main></body></html>
