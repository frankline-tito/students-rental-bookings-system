<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');

$sid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel') {
    $bookingId = (int)$_POST['booking_id'];
    $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=? AND student_id=? AND status='pending'")
        ->execute([$bookingId, $sid]);
    flash('success', 'Booking cancelled.');
    redirect('student/my_bookings.php');
}

$bookings = $pdo->prepare(
    "SELECT b.*, h.title, h.price, h.location, h.room_type, u.full_name AS landlord_name, u.phone AS landlord_phone
     FROM bookings b JOIN houses h ON h.id=b.house_id JOIN users u ON u.id=h.landlord_id
     WHERE b.student_id = ? ORDER BY b.created_at DESC"
);
$bookings->execute([$sid]);
$bookings = $bookings->fetchAll();

$page_title = 'My Bookings';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">My Bookings</h3>
  <?php if (!$bookings): ?>
    <p class="text-muted">No bookings yet. <a href="<?= base_url('houses.php') ?>">Browse houses</a> to send your first request.</p>
  <?php endif; ?>
  <?php foreach ($bookings as $b): ?>
    <div class="sidebar-card mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <h6 class="mb-1"><a href="<?= base_url('house_details.php?id=' . $b['house_id']) ?>"><?= sanitize($b['title']) ?></a></h6>
        <p class="mb-1 small text-muted"><i class="bi bi-geo-alt"></i> <?= sanitize($b['location']) ?> · <?= room_type_label($b['room_type']) ?> · KES <?= number_format($b['price']) ?></p>
        <p class="mb-0 small text-muted">Landlord: <?= sanitize($b['landlord_name']) ?> (<?= sanitize($b['landlord_phone']) ?>)</p>
      </div>
      <div class="text-end">
        <span class="badge badge-<?= $b['status']==='confirmed'?'approved':($b['status']==='pending'?'pending':'rejected') ?> mb-2 d-inline-block"><?= ucfirst($b['status']) ?></span><br>
        <?php if ($b['status'] === 'pending'): ?>
          <form method="POST" class="d-inline">
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
            <button class="btn btn-sm btn-outline-danger confirm-action" data-confirm="Cancel this booking request?">Cancel</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
