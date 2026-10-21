<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');

$sid = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$sid]);
$user = $stmt->fetch();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $idNumber = trim($_POST['id_number'] ?? '');
    $newPass  = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $phone === '') $errors[] = 'Name and phone are required.';
    if ($newPass !== '' && ($newPass !== $confirm || strlen($newPass) < 6)) {
        $errors[] = 'New password must be at least 6 characters and match confirmation.';
    }

    if (!$errors) {
        $photo = handle_image_upload('profile_photo', 'profiles');
        if ($photo) {
            $pdo->prepare('UPDATE users SET profile_photo=? WHERE id=?')->execute([$photo, $sid]);
        }
        $pdo->prepare('UPDATE users SET full_name=?, phone=?, id_number=? WHERE id=?')
            ->execute([$fullName, $phone, $idNumber, $sid]);
        if ($newPass !== '') {
            $pdo->prepare('UPDATE users SET password=? WHERE id=?')
                ->execute([password_hash($newPass, PASSWORD_DEFAULT), $sid]);
        }
        $_SESSION['full_name'] = $fullName;
        flash('success', 'Profile updated successfully.');
        redirect('student/profile.php');
    }
    $user['full_name'] = $fullName; $user['phone'] = $phone; $user['id_number'] = $idNumber;
}

$page_title = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">My Profile</h3>
  <div class="row">
    <div class="col-lg-6">
      <div class="sidebar-card">
        <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?= sanitize($e) ?></div><?php endforeach; ?>
        <form method="POST" enctype="multipart/form-data">
          <div class="text-center mb-3">
            <img src="<?= $user['profile_photo'] ? base_url($user['profile_photo']) : base_url('assets/img/placeholder.svg') ?>"
                 class="rounded-circle" style="width:90px;height:90px;object-fit:cover;">
            <input type="file" name="profile_photo" class="form-control mt-2">
          </div>
          <div class="mb-3"><label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" value="<?= sanitize($user['full_name']) ?>" required></div>
          <div class="mb-3"><label class="form-label">Email</label>
            <input type="email" class="form-control" value="<?= sanitize($user['email']) ?>" disabled></div>
          <div class="mb-3"><label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" value="<?= sanitize($user['phone']) ?>" required></div>
          <div class="mb-3"><label class="form-label">Student ID</label>
            <input type="text" name="id_number" class="form-control" value="<?= sanitize($user['id_number']) ?>"></div>
          <hr>
          <p class="small text-muted">Leave blank to keep your current password.</p>
          <div class="mb-3"><label class="form-label">New Password</label>
            <input type="password" name="new_password" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control"></div>
          <button class="btn btn-brand w-100 py-2">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
