<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)$_POST['user_id'];
    $action = $_POST['action'];
    if ($action === 'confirm') {
        $pdo->prepare("UPDATE landlord_profiles SET posting_fee_status='paid' WHERE user_id=?")->execute([$userId]);
        flash('success', 'Fee payment confirmed — landlord can now post houses.');
    } elseif ($action === 'reject') {
        $pdo->prepare("UPDATE landlord_profiles SET posting_fee_status='unpaid', posting_fee_ref=NULL WHERE user_id=?")->execute([$userId]);
        flash('success', 'Fee payment rejected.');
    }
    redirect('admin/fees.php');
}

$fees = $pdo->query(
    "SELECT u.full_name, u.phone, u.email, lp.* FROM landlord_profiles lp JOIN users u ON u.id=lp.user_id
     WHERE lp.posting_fee_status != 'unpaid' ORDER BY lp.posting_fee_status='pending' DESC"
)->fetchAll();

$page_title = 'Fee Confirmations';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">Listing Fee Confirmations</h3>
  <div class="sidebar-card">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Landlord</th><th>Method</th><th>Reference</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($fees as $f): ?>
            <tr>
              <td><?= sanitize($f['full_name']) ?><br><span class="small text-muted"><?= sanitize($f['phone']) ?></span></td>
              <td><?= sanitize(ucfirst($f['posting_fee_method'])) ?></td>
              <td><?= sanitize($f['posting_fee_ref']) ?></td>
              <td><span class="badge badge-<?= $f['posting_fee_status']==='paid'?'approved':'pending' ?>"><?= ucfirst($f['posting_fee_status']) ?></span></td>
              <td>
                <?php if ($f['posting_fee_status'] === 'pending'): ?>
                  <div class="d-flex gap-1">
                    <form method="POST"><input type="hidden" name="user_id" value="<?= $f['user_id'] ?>"><input type="hidden" name="action" value="confirm">
                      <button class="btn btn-sm btn-brand">Confirm</button></form>
                    <form method="POST"><input type="hidden" name="user_id" value="<?= $f['user_id'] ?>"><input type="hidden" name="action" value="reject">
                      <button class="btn btn-sm btn-outline-danger">Reject</button></form>
                  </div>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!$fees): ?><p class="text-muted">No fee submissions yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
