<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('landlord');

$lid = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?'); $stmt->execute([$lid]); $user = $stmt->fetch();
$stmt = $pdo->prepare('SELECT * FROM landlord_profiles WHERE user_id=?'); $stmt->execute([$lid]); $profile = $stmt->fetch();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST['form'] ?? '';

    if ($form === 'personal') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone    = trim($_POST['phone'] ?? '');
        $newPass  = $_POST['new_password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if ($fullName === '' || $phone === '') $errors[] = 'Name and phone are required.';
        if ($newPass !== '' && ($newPass !== $confirm || strlen($newPass) < 6)) {
            $errors[] = 'New password must be at least 6 characters and match.';
        }
        if (!$errors) {
            $photo = handle_image_upload('profile_photo', 'profiles');
            if ($photo) $pdo->prepare('UPDATE users SET profile_photo=? WHERE id=?')->execute([$photo, $lid]);
            $pdo->prepare('UPDATE users SET full_name=?, phone=? WHERE id=?')->execute([$fullName, $phone, $lid]);
            if ($newPass !== '') $pdo->prepare('UPDATE users SET password=? WHERE id=?')->execute([password_hash($newPass, PASSWORD_DEFAULT), $lid]);
            $_SESSION['full_name'] = $fullName;
            flash('success', 'Profile updated.');
            redirect('landlord/profile.php');
        }
    }

    if ($form === 'payment') {
        $pdo->prepare(
            'UPDATE landlord_profiles SET mpesa_number=?, paybill_number=?, paybill_account=?, bank_name=?, bank_account_number=?, account_name=? WHERE user_id=?'
        )->execute([
            trim($_POST['mpesa_number'] ?? ''), trim($_POST['paybill_number'] ?? ''), trim($_POST['paybill_account'] ?? ''),
            trim($_POST['bank_name'] ?? ''), trim($_POST['bank_account_number'] ?? ''), trim($_POST['account_name'] ?? ''), $lid
        ]);
        flash('success', 'Payment details updated — students will see these on your listings.');
        redirect('landlord/profile.php');
    }
}

$page_title = 'My Profile';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">Profile & Payment Details</h3>
  <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?= sanitize($e) ?></div><?php endforeach; ?>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="sidebar-card">
        <h5>Personal Info</h5>
        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="form" value="personal">
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
          <p class="small mb-3">Account status:
            <span class="badge badge-<?= $user['status'] ?>"><?= ucfirst($user['status']) ?></span>
          </p>
          <hr>
          <p class="small text-muted">Leave blank to keep current password.</p>
          <div class="mb-3"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control"></div>
          <div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control"></div>
          <button class="btn btn-brand w-100 py-2">Save</button>
        </form>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="sidebar-card mb-3">
        <h5>Payment Details <span class="text-muted small">(shown to students on each listing)</span></h5>
        <form method="POST">
          <input type="hidden" name="form" value="payment">
          <div class="mb-3"><label class="form-label">M-Pesa Number (Send Money)</label>
            <input type="text" name="mpesa_number" class="form-control" value="<?= sanitize($profile['mpesa_number']) ?>" placeholder="07XXXXXXXX"></div>
          <div class="row">
            <div class="col-6 mb-3"><label class="form-label">Paybill Number</label>
              <input type="text" name="paybill_number" class="form-control" value="<?= sanitize($profile['paybill_number']) ?>"></div>
            <div class="col-6 mb-3"><label class="form-label">Paybill Account No.</label>
              <input type="text" name="paybill_account" class="form-control" value="<?= sanitize($profile['paybill_account']) ?>"></div>
          </div>
          <div class="mb-3"><label class="form-label">Bank Name</label>
            <input type="text" name="bank_name" class="form-control" value="<?= sanitize($profile['bank_name']) ?>"></div>
          <div class="mb-3"><label class="form-label">Bank Account Number</label>
            <input type="text" name="bank_account_number" class="form-control" value="<?= sanitize($profile['bank_account_number']) ?>"></div>
          <div class="mb-3"><label class="form-label">Account Name</label>
            <input type="text" name="account_name" class="form-control" value="<?= sanitize($profile['account_name']) ?>" placeholder="Name registered on the account"></div>
          <button class="btn btn-brand w-100 py-2">Save Payment Details</button>
        </form>
      </div>

      <div class="sidebar-card">
        <h6>Listing Fee</h6>
        <p class="small mb-1">Status: <span class="badge badge-<?= $profile['posting_fee_status']==='paid'?'approved':'pending' ?>"><?= ucfirst($profile['posting_fee_status']) ?></span></p>
        <?php if ($profile['posting_fee_status'] !== 'paid'): ?>
          <a href="<?= base_url('landlord/pay_fee.php') ?>" class="btn btn-outline-brand btn-sm">Pay Listing Fee</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
