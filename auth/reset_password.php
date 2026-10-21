<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = false;
$prefill_email = $_SESSION['reset_email'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $code     = trim($_POST['code'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'No account found with that email.';
    } elseif (strlen($password) < 6 || $password !== $confirm) {
        $error = 'Passwords must match and be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare(
            'SELECT * FROM password_resets WHERE user_id = ? AND code = ? AND used = 0 AND expires_at >= NOW()
             ORDER BY id DESC LIMIT 1'
        );
        $stmt->execute([$user['id'], $code]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $error = 'That code is invalid or has expired. Please request a new one.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $user['id']]);
            $pdo->prepare('UPDATE password_resets SET used = 1 WHERE id = ?')->execute([$reset['id']]);
            unset($_SESSION['reset_email']);
            $success = true;
        }
    }
}

$page_title = 'Reset Password';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h3 class="text-center mb-1">Enter Reset Code</h3>
    <p class="text-center text-muted mb-4">Check the code you received and set a new password.</p>

    <?php if ($error): ?><div class="alert alert-danger py-2"><?= sanitize($error) ?></div><?php endif; ?>

    <?php if ($success): ?>
      <div class="alert alert-success">Your password has been reset successfully.</div>
      <a href="<?= base_url('auth/login.php') ?>" class="btn btn-brand w-100 py-2">Go to Login</a>
    <?php else: ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" value="<?= sanitize($prefill_email) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">6-Digit Code</label>
          <input type="text" name="code" maxlength="6" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">New Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm New Password</label>
          <input type="password" name="confirm_password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-brand w-100 py-2">Reset Password</button>
      </form>
    <?php endif; ?>
    <p class="text-center mt-3 mb-0 small"><a href="<?= base_url('auth/forgot_password.php') ?>">Request a new code</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
