<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('landlord');

$lid = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM landlord_profiles WHERE user_id=?'); $stmt->execute([$lid]); $profile = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $profile['posting_fee_status'] !== 'paid') {
    $method = $_POST['method'] ?? '';
    $ref    = trim($_POST['ref'] ?? '');
    $pdo->prepare("UPDATE landlord_profiles SET posting_fee_status='pending', posting_fee_method=?, posting_fee_ref=? WHERE user_id=?")
        ->execute([$method, $ref, $lid]);
    flash('success', 'Payment submitted! An admin will confirm it shortly and unlock house posting for you.');
    redirect('landlord/dashboard.php');
}

$page_title = 'Pay Listing Fee';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="sidebar-card">
        <h4 class="section-title">Listing Fee</h4>
        <p>A one-time fee of <strong>KES <?= POSTING_FEE_AMOUNT ?></strong> is required before you can post houses on <?= SITE_NAME ?>. Pay using any of the methods below, then submit your payment reference — an admin will confirm it.</p>
        <div class="pay-info mb-4">
          <p class="mb-1"><strong>M-Pesa Paybill:</strong> <?= ADMIN_MPESA_TILL ?></p>
          <p class="mb-1"><strong>Account Number:</strong> your registered phone number</p>
          <p class="mb-0"><strong>Or Cash:</strong> visit the admin office to pay and receive a receipt number</p>
        </div>

        <?php if ($profile['posting_fee_status'] === 'paid'): ?>
          <div class="alert alert-success">Your listing fee is already confirmed. You're all set to post houses!</div>
        <?php elseif ($profile['posting_fee_status'] === 'pending'): ?>
          <div class="alert alert-warning">Your payment (ref: <?= sanitize($profile['posting_fee_ref']) ?>) is awaiting admin confirmation.</div>
        <?php else: ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Payment Method</label>
              <select name="method" class="form-select" required>
                <option value="mpesa">M-Pesa</option>
                <option value="cash">Cash</option>
                <option value="bank">Bank Transfer</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">M-Pesa Code / Receipt Number</label>
              <input type="text" name="ref" class="form-control" placeholder="e.g. QK7X8YABCD" required>
            </div>
            <button class="btn btn-brand w-100 py-2">Submit for Confirmation</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
