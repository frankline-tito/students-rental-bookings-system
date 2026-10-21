<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    if ($action === 'approve') {
        $pdo->prepare("UPDATE users SET status='approved' WHERE id=? AND role='landlord'")->execute([$id]);
        flash('success', 'Landlord approved.');
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE users SET status='rejected' WHERE id=? AND role='landlord'")->execute([$id]);
        flash('success', 'Landlord rejected.');
    } elseif ($action === 'delete') {
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='landlord'")->execute([$id]);
        flash('success', 'Landlord account deleted.');
    }
    redirect('admin/landlords.php');
}

$landlords = $pdo->query(
    "SELECT u.*, lp.mpesa_number, lp.paybill_number, lp.bank_account_number, lp.posting_fee_status,
      (SELECT COUNT(*) FROM houses WHERE landlord_id=u.id) AS house_count
     FROM users u LEFT JOIN landlord_profiles lp ON lp.user_id=u.id
     WHERE u.role='landlord' ORDER BY u.status='pending' DESC, u.created_at DESC"
)->fetchAll();

$page_title = 'Manage Landlords';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">Manage Landlords</h3>
  <div class="sidebar-card">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Name</th><th>Contact</th><th>Houses</th><th>Fee</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($landlords as $l): ?>
            <tr>
              <td><?= sanitize($l['full_name']) ?></td>
              <td class="small"><?= sanitize($l['phone']) ?><br><?= sanitize($l['email']) ?></td>
              <td><?= $l['house_count'] ?></td>
              <td><span class="badge badge-<?= $l['posting_fee_status']==='paid'?'approved':'pending' ?>"><?= ucfirst($l['posting_fee_status'] ?? 'unpaid') ?></span></td>
              <td><span class="badge badge-<?= $l['status'] ?>"><?= ucfirst($l['status']) ?></span></td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <?php if ($l['status'] !== 'approved'): ?>
                    <form method="POST"><input type="hidden" name="user_id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="approve">
                      <button class="btn btn-sm btn-brand">Approve</button></form>
                  <?php endif; ?>
                  <?php if ($l['status'] !== 'rejected'): ?>
                    <form method="POST"><input type="hidden" name="user_id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="reject">
                      <button class="btn btn-sm btn-outline-danger">Reject</button></form>
                  <?php endif; ?>
                  <form method="POST"><input type="hidden" name="user_id" value="<?= $l['id'] ?>"><input type="hidden" name="action" value="delete">
                    <button class="btn btn-sm btn-outline-secondary confirm-action" data-confirm="Delete this landlord and all their listings?">Delete</button></form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!$landlords): ?><p class="text-muted">No landlords registered yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
