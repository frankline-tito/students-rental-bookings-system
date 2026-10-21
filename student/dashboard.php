<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');

$sid = $_SESSION['user_id'];
$stats = [
    'total'     => $pdo->prepare('SELECT COUNT(*) c FROM bookings WHERE student_id=?'),
    'pending'   => $pdo->prepare("SELECT COUNT(*) c FROM bookings WHERE student_id=? AND status='pending'"),
    'confirmed' => $pdo->prepare("SELECT COUNT(*) c FROM bookings WHERE student_id=? AND status='confirmed'"),
    'wishlist'  => $pdo->prepare('SELECT COUNT(*) c FROM wishlist WHERE student_id=?'),
];
foreach ($stats as $k => $stmt) { $stmt->execute([$sid]); $stats[$k] = $stmt->fetch()['c']; }

$recent = $pdo->prepare(
    "SELECT b.*, h.title, h.price, h.location FROM bookings b
     JOIN houses h ON h.id = b.house_id WHERE b.student_id = ?
     ORDER BY b.created_at DESC LIMIT 5"
);
$recent->execute([$sid]);
$recent = $recent->fetchAll();

$page_title = 'Student Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="mb-1">Hi, <?= sanitize($_SESSION['full_name']) ?> 👋</h3>
  <p class="text-muted mb-4">Here's what's happening with your house search.</p>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
      <div class="dash-stat"><div class="num"><?= $stats['total'] ?></div>Total Bookings</div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="dash-stat accent"><div class="num"><?= $stats['pending'] ?></div>Pending</div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="dash-stat"><div class="num"><?= $stats['confirmed'] ?></div>Confirmed</div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="dash-stat accent"><div class="num"><?= $stats['wishlist'] ?></div>Wishlisted</div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-8">
      <div class="sidebar-card">
        <h5>Recent Bookings</h5>
        <?php if (!$recent): ?>
          <p class="text-muted small">You haven't booked anything yet. <a href="<?= base_url('houses.php') ?>">Browse houses</a> to get started.</p>
        <?php else: ?>
          <table class="table align-middle">
            <thead><tr><th>House</th><th>Location</th><th>Price</th><th>Status</th></tr></thead>
            <tbody>
              <?php foreach ($recent as $b): ?>
                <tr>
                  <td><a href="<?= base_url('house_details.php?id=' . $b['house_id']) ?>"><?= sanitize($b['title']) ?></a></td>
                  <td><?= sanitize($b['location']) ?></td>
                  <td>KES <?= number_format($b['price']) ?></td>
                  <td><span class="badge badge-<?= $b['status']==='confirmed'?'approved':($b['status']==='rejected'?'rejected':'pending') ?>"><?= ucfirst($b['status']) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-4">
      <div class="sidebar-card">
        <h5>Quick Links</h5>
        <div class="d-grid gap-2">
          <a class="btn btn-outline-brand" href="<?= base_url('houses.php') ?>"><i class="bi bi-search"></i> Browse Houses</a>
          <a class="btn btn-outline-brand" href="<?= base_url('student/my_bookings.php') ?>"><i class="bi bi-calendar-check"></i> My Bookings</a>
          <a class="btn btn-outline-brand" href="<?= base_url('student/wishlist.php') ?>"><i class="bi bi-heart"></i> Wishlist</a>
          <a class="btn btn-outline-brand" href="<?= base_url('student/profile.php') ?>"><i class="bi bi-person"></i> Edit Profile</a>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
