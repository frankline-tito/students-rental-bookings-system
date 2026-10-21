<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

if (is_logged_in()) redirect('index.php');

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $error = 'Incorrect email or password.';
    } elseif ($user['status'] === 'rejected') {
        $error = 'Your account was not approved. Please contact the admin.';
    } else {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['status']    = $user['status'];

        flash('success', 'Welcome back, ' . $user['full_name'] . '!');
        if ($user['role'] === 'admin')    redirect('admin/dashboard.php');
        if ($user['role'] === 'landlord') redirect('landlord/dashboard.php');
        redirect('student/dashboard.php');
    }
}

$page_title = 'Login';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h3 class="text-center mb-1">Welcome Back</h3>
    <p class="text-center text-muted mb-4">Log in to <?= SITE_NAME ?></p>

    <?php if ($error): ?><div class="alert alert-danger py-2"><?= sanitize($error) ?></div><?php endif; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" value="<?= sanitize($email) ?>" required autofocus>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-brand w-100 py-2">Log In</button>
    </form>
    <p class="text-center mt-3 mb-1 small"><a href="<?= base_url('auth/forgot_password.php') ?>">Forgot password?</a></p>
    <p class="text-center small mb-0">No account yet? <a href="<?= base_url('auth/register.php') ?>">Register here</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
