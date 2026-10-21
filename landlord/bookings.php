<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('landlord');

$lid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = (int)$_POST['booking_id'];
    $action    = $_POST['action'];

    $check = $pdo->prepare(
        "SELECT b.*, h.landlord_id, h.id AS house_id FROM bookings b JOIN houses h ON h.id=b.house_id WHERE b.id=?"
    );
    $check->execute([$bookingId]);
    $booking = $check->fetch();

    if ($booking && $booking['landlord_id'] == $lid) {
        if ($action === 'confirm') {
            $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?")->execute([$bookingId]);
            $pdo->prepare("UPDATE houses SET status='booked' WHERE id=?")->execute([$booking['house_id']]);
            $pdo->prepare("UPDATE bookings SET status='rejected' WHERE house_id=? AND id != ? AND status='pending'")
                ->execute([$booking['house_id'], $bookingId]);
            flash('success', 'Booking confirmed. The room has been marked as booked.');
        } elseif ($action === 'reject') {
            $pdo->prepare("UPDATE bookings SET status='rejected' WHERE id=?")->execute([$bookingId]);
            flash('success', 'Booking rejected.');
        }
    }
    redirect('landlord/bookings.php');
}

$bookings = $pdo->prepare(
    "SELECT b.*, h.title, h.location, h.price, u.full_name AS student_name, u.phone AS student_phone, u.email AS student_email
     FROM bookings b JOIN houses h ON h.id=b.house_id JOIN users u ON u.id=b.student_id
     WHERE h.landlord_id = ? ORDER BY b.status='pending' DESC, b.created_at DESC"
);
$bookings->execute([$lid]);
$bookings = $bookings->fetchAll();

$page_title = 'Booking Requests';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">Booking Requests</h3>
  <?php foreach ($bookings as $b): ?>
    <div class="sidebar-card mb-3">
      <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
        <div>
          <h6 class="mb-1"><?= sanitize($b['title']) ?> <span class="badge badge-<?= $b['status']==='confirmed'?'approved':($b['status']==='pending'?'pending':'rejected') ?>"><?= ucfirst($b['status']) ?></span></h6>
          <p class="mb-1 small text-muted"><?= sanitize($b['location']) ?> · KES <?= number_format($b['price']) ?></p>
          <p class="mb-1 small"><strong>Student:</strong> <?= sanitize($b['student_name']) ?> · <?= sanitize($b['student_phone']) ?> · <?= sanitize($b['student_email']) ?></p>
          <?php if ($b['message']): ?><p class="mb-0 small text-muted"><em>"<?= sanitize($b['message']) ?>"</em></p><?php endif; ?>
        </div>
        <?php if ($b['status'] === 'pending'): ?>
          <div class="d-flex gap-2">
            <form method="POST"><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><input type="hidden" name="action" value="confirm">
              <button class="btn btn-sm btn-brand">Confirm</button></form>
            <form method="POST"><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><input type="hidden" name="action" value="reject">
              <button class="btn btn-sm btn-outline-danger confirm-action" data-confirm="Reject this booking?">Reject</button></form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endforeach; ?>
  <?php if (!$bookings): ?><p class="text-muted">No booking requests yet.</p><?php endif; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
