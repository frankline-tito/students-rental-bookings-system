<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('landlord');

$lid = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?'); $stmt->execute([$lid]); $user = $stmt->fetch();
$stmt = $pdo->prepare('SELECT * FROM landlord_profiles WHERE user_id=?'); $stmt->execute([$lid]); $profile = $stmt->fetch();

$houseStats = $pdo->prepare(
    "SELECT COUNT(*) total,
      SUM(approval_status='pending') pending_approval,
      SUM(status='available') available,
      SUM(status='booked') booked
     FROM houses WHERE landlord_id=?"
);
$houseStats->execute([$lid]);
$houseStats = $houseStats->fetch();

$pendingBookings = $pdo->prepare(
    "SELECT COUNT(*) c FROM bookings b JOIN houses h ON h.id=b.house_id
     WHERE h.landlord_id=? AND b.status='pending'"
);
$pendingBookings->execute([$lid]);
$pendingBookings = $pendingBookings->fetch()['c'];

$page_title = 'Landlord Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="mb-1">Welcome, <?= sanitize($user['full_name']) ?> 👋</h3>

  <?php if ($user['status'] !== 'approved'): ?>
    <div class="alert alert-warning">
      <i class="bi bi-hourglass-split"></i> Your landlord account is awaiting admin approval. You can complete your
      payment profile below in the meantime, but you won't be able to list houses until an admin approves your account.
    </div>
  <?php elseif (($profile['posting_fee_status'] ?? 'unpaid') !== 'paid'): ?>
    <div class="alert alert-warning">
      <i class="bi bi-cash"></i> You need to pay the one-time listing fee (KES <?= POSTING_FEE_AMOUNT ?>) before you can post houses.
      <a href="<?= base_url('landlord/pay_fee.php') ?>" class="fw-semibold">Pay now &rarr;</a>
    </div>
  <?php endif; ?>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3"><div class="dash-stat"><div class="num"><?= (int)$houseStats['total'] ?></div>My Houses</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat accent"><div class="num"><?= (int)$houseStats['pending_approval'] ?></div>Awaiting Approval</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat"><div class="num"><?= (int)$houseStats['available'] ?></div>Available</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat accent"><div class="num"><?= (int)$pendingBookings ?></div>Pending Bookings</div></div>
  </div>

  <div class="sidebar-card">
    <h5>Quick Links</h5>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-brand" href="<?= base_url('landlord/add_house.php') ?>"><i class="bi bi-plus-circle"></i> Add a House</a>
      <a class="btn btn-outline-brand" href="<?= base_url('landlord/my_houses.php') ?>"><i class="bi bi-houses"></i> My Houses</a>
      <a class="btn btn-outline-brand" href="<?= base_url('landlord/bookings.php') ?>"><i class="bi bi-calendar-check"></i> Booking Requests</a>
      <a class="btn btn-outline-brand" href="<?= base_url('landlord/profile.php') ?>"><i class="bi bi-credit-card"></i> Payment & Profile</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
