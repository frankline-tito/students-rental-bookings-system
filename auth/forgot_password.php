<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

$error = '';
$sent  = false;
$devCode = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $error = 'No account found with that email.';
    } else {
        $code = generate_code();
        $expires = date('Y-m-d H:i:s', strtime_plus(15 * 60));
        $pdo->prepare('INSERT INTO password_resets (user_id, code, expires_at) VALUES (?,?,?)')
            ->execute([$user['id'], $code, $expires]);

        // In production, email/SMS $code to $user via a mail service.
        // No live SMTP is configured for this local project, so for
        // demonstration purposes the code is shown right on screen below.
        $sent = true;
        $devCode = $code;
        $_SESSION['reset_email'] = $email;
    }
}

function strtime_plus($seconds) {
    return time() + $seconds;
}

$page_title = 'Forgot Password';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h3 class="text-center mb-1">Reset Password</h3>
    <p class="text-center text-muted mb-4">Enter your account email to receive a reset code.</p>

    <?php if ($error): ?><div class="alert alert-danger py-2"><?= sanitize($error) ?></div><?php endif; ?>

    <?php if ($sent): ?>
      <div class="alert alert-success">
        A 6-digit reset code has been generated. In a live deployment this is emailed/SMS'd to the account's
        contact details — for this local demo, here it is:
        <div class="fs-3 fw-bold text-center my-2"><?= sanitize($devCode) ?></div>
        It expires in 15 minutes.
      </div>
      <a href="<?= base_url('auth/reset_password.php') ?>" class="btn btn-brand w-100 py-2">Enter Code &amp; Reset</a>
    <?php else: ?>
      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" required autofocus>
        </div>
        <button type="submit" class="btn btn-brand w-100 py-2">Send Reset Code</button>
      </form>
    <?php endif; ?>
    <p class="text-center mt-3 mb-0 small"><a href="<?= base_url('auth/login.php') ?>">Back to login</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
