<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';

if (is_logged_in()) redirect('index.php');

$errors = [];
$old = ['role' => 'student', 'full_name' => '', 'email' => '', 'phone' => '', 'id_number' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['role']      = $_POST['role'] === 'landlord' ? 'landlord' : 'student';
    $old['full_name'] = trim($_POST['full_name'] ?? '');
    $old['email']     = trim($_POST['email'] ?? '');
    $old['phone']     = trim($_POST['phone'] ?? '');
    $old['id_number'] = trim($_POST['id_number'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm          = $_POST['confirm_password'] ?? '';

    if ($old['full_name'] === '') $errors[] = 'Full name is required.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
    if ($old['phone'] === '') $errors[] = 'Phone number is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'An account with that email already exists.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (role, full_name, email, phone, password, status, id_number)
             VALUES (?,?,?,?,?,?,?)'
        );
        // Students are auto-approved (they just browse & book).
        // Landlords need admin approval before their listings go live.
        $status = $old['role'] === 'student' ? 'approved' : 'pending';
        $stmt->execute([$old['role'], $old['full_name'], $old['email'], $old['phone'], $hash, $status, $old['id_number']]);
        $userId = $pdo->lastInsertId();

        if ($old['role'] === 'landlord') {
            $pdo->prepare('INSERT INTO landlord_profiles (user_id) VALUES (?)')->execute([$userId]);
        }

        flash('success', $old['role'] === 'landlord'
            ? 'Account created! An admin will review and approve your landlord account shortly. You can log in now to complete your profile.'
            : 'Account created successfully! You can now log in.');
        redirect('auth/login.php');
    }
}

$page_title = 'Create Account';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="auth-wrap">
  <div class="auth-card">
    <h3 class="text-center mb-1">Join <?= SITE_NAME ?></h3>
    <p class="text-center text-muted mb-4">Find a house near campus, or list one as a landlord.</p>

    <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger py-2"><?= sanitize($e) ?></div>
    <?php endforeach; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label class="form-label fw-semibold">I am a</label>
        <div class="btn-group w-100" role="group">
          <input type="radio" class="btn-check" name="role" id="roleStudent" value="student" <?= $old['role']==='student'?'checked':'' ?>>
          <label class="btn btn-outline-brand" for="roleStudent"><i class="bi bi-mortarboard"></i> Student</label>
          <input type="radio" class="btn-check" name="role" id="roleLandlord" value="landlord" <?= $old['role']==='landlord'?'checked':'' ?>>
          <label class="btn btn-outline-brand" for="roleLandlord"><i class="bi bi-building"></i> Landlord</label>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Full Name</label>
        <input type="text" name="full_name" class="form-control" value="<?= sanitize($old['full_name']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" value="<?= sanitize($old['email']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="text" name="phone" class="form-control" placeholder="07XXXXXXXX" value="<?= sanitize($old['phone']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">National ID / Student ID <span class="text-muted">(optional)</span></label>
        <input type="text" name="id_number" class="form-control" value="<?= sanitize($old['id_number']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-brand w-100 py-2">Create Account</button>
    </form>
    <p class="text-center mt-3 mb-0 small">Already have an account? <a href="<?= base_url('auth/login.php') ?>">Log in</a></p>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
