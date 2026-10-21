<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    if ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='student'")->execute([$id]);
        flash('success', 'Student account deleted.');
    } elseif ($action === 'suspend') {
        $pdo->prepare("UPDATE users SET status='rejected' WHERE id=? AND role='student'")->execute([$id]);
        flash('success', 'Student account suspended.');
    } elseif ($action === 'reinstate') {
        $pdo->prepare("UPDATE users SET status='approved' WHERE id=? AND role='student'")->execute([$id]);
        flash('success', 'Student account reinstated.');
    }
    redirect('admin/students.php');
}

$students = $pdo->query(
    "SELECT u.*, (SELECT COUNT(*) FROM bookings WHERE student_id=u.id) AS booking_count
     FROM users u WHERE u.role='student' ORDER BY u.created_at DESC"
)->fetchAll();

$page_title = 'Manage Students';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">Manage Students / Tenants</h3>
  <div class="sidebar-card">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Name</th><th>Contact</th><th>Bookings</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($students as $s): ?>
            <tr>
              <td><?= sanitize($s['full_name']) ?></td>
              <td class="small"><?= sanitize($s['phone']) ?><br><?= sanitize($s['email']) ?></td>
              <td><?= $s['booking_count'] ?></td>
              <td><span class="badge badge-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <?php if ($s['status'] === 'approved'): ?>
                    <form method="POST"><input type="hidden" name="user_id" value="<?= $s['id'] ?>"><input type="hidden" name="action" value="suspend">
                      <button class="btn btn-sm btn-outline-danger">Suspend</button></form>
                  <?php else: ?>
                    <form method="POST"><input type="hidden" name="user_id" value="<?= $s['id'] ?>"><input type="hidden" name="action" value="reinstate">
                      <button class="btn btn-sm btn-brand">Reinstate</button></form>
                  <?php endif; ?>
                  <form method="POST"><input type="hidden" name="user_id" value="<?= $s['id'] ?>"><input type="hidden" name="action" value="delete">
                    <button class="btn btn-sm btn-outline-secondary confirm-action" data-confirm="Delete this student account?">Delete</button></form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!$students): ?><p class="text-muted">No students registered yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
