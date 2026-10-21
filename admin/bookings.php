<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $pdo->prepare('DELETE FROM bookings WHERE id=?')->execute([(int)$_POST['booking_id']]);
    flash('success', 'Booking record removed.');
    redirect('admin/bookings.php');
}

$bookings = $pdo->query(
    "SELECT b.*, h.title, h.location, u.full_name AS student_name, ul.full_name AS landlord_name
     FROM bookings b JOIN houses h ON h.id=b.house_id JOIN users u ON u.id=b.student_id
     JOIN users ul ON ul.id = h.landlord_id
     ORDER BY b.created_at DESC"
)->fetchAll();

$page_title = 'All Bookings';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">All Bookings</h3>
  <div class="sidebar-card">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>House</th><th>Student</th><th>Landlord</th><th>Status</th><th>Date</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
            <tr>
              <td><a href="<?= base_url('house_details.php?id=' . $b['house_id']) ?>"><?= sanitize($b['title']) ?></a><br><span class="small text-muted"><?= sanitize($b['location']) ?></span></td>
              <td><?= sanitize($b['student_name']) ?></td>
              <td><?= sanitize($b['landlord_name']) ?></td>
              <td><span class="badge badge-<?= $b['status']==='confirmed'?'approved':($b['status']==='pending'?'pending':'rejected') ?>"><?= ucfirst($b['status']) ?></span></td>
              <td class="small"><?= date('d M Y', strtotime($b['created_at'])) ?></td>
              <td>
                <form method="POST"><input type="hidden" name="booking_id" value="<?= $b['id'] ?>"><input type="hidden" name="action" value="delete">
                  <button class="btn btn-sm btn-outline-danger confirm-action" data-confirm="Remove this booking record?">Delete</button></form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!$bookings): ?><p class="text-muted">No bookings yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
