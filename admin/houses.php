<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['house_id'];
    $action = $_POST['action'];
    if ($action === 'approve') { $pdo->prepare("UPDATE houses SET approval_status='approved' WHERE id=?")->execute([$id]); flash('success', 'House approved.'); }
    elseif ($action === 'reject') { $pdo->prepare("UPDATE houses SET approval_status='rejected' WHERE id=?")->execute([$id]); flash('success', 'House rejected.'); }
    elseif ($action === 'delete') { $pdo->prepare("DELETE FROM houses WHERE id=?")->execute([$id]); flash('success', 'House deleted.'); }
    redirect('admin/houses.php');
}

$houses = $pdo->query(
    "SELECT h.*, u.full_name AS landlord_name FROM houses h JOIN users u ON u.id=h.landlord_id
     ORDER BY h.approval_status='pending' DESC, h.created_at DESC"
)->fetchAll();

$page_title = 'Manage Houses';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="section-title mb-0">Manage Houses</h3>
    <a href="<?= base_url('admin/add_house.php') ?>" class="btn btn-brand"><i class="bi bi-plus-circle"></i> Add House (on behalf of landlord)</a>
  </div>
  <div class="sidebar-card">
    <div class="table-responsive">
      <table class="table align-middle">
        <thead><tr><th>Title</th><th>Landlord</th><th>Price</th><th>Type</th><th>Approval</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php foreach ($houses as $h): ?>
            <tr>
              <td><a href="<?= base_url('house_details.php?id=' . $h['id']) ?>"><?= sanitize($h['title']) ?></a></td>
              <td><?= sanitize($h['landlord_name']) ?></td>
              <td>KES <?= number_format($h['price']) ?></td>
              <td><?= room_type_label($h['room_type']) ?></td>
              <td><span class="badge badge-<?= $h['approval_status'] ?>"><?= ucfirst($h['approval_status']) ?></span></td>
              <td><span class="badge badge-status-<?= $h['status'] ?>"><?= ucfirst($h['status']) ?></span></td>
              <td>
                <div class="d-flex gap-1 flex-wrap">
                  <?php if ($h['approval_status'] !== 'approved'): ?>
                    <form method="POST"><input type="hidden" name="house_id" value="<?= $h['id'] ?>"><input type="hidden" name="action" value="approve">
                      <button class="btn btn-sm btn-brand">Approve</button></form>
                  <?php endif; ?>
                  <?php if ($h['approval_status'] !== 'rejected'): ?>
                    <form method="POST"><input type="hidden" name="house_id" value="<?= $h['id'] ?>"><input type="hidden" name="action" value="reject">
                      <button class="btn btn-sm btn-outline-danger">Reject</button></form>
                  <?php endif; ?>
                  <form method="POST"><input type="hidden" name="house_id" value="<?= $h['id'] ?>"><input type="hidden" name="action" value="delete">
                    <button class="btn btn-sm btn-outline-secondary confirm-action" data-confirm="Delete this listing?">Delete</button></form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!$houses): ?><p class="text-muted">No houses listed yet.</p><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
