<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('landlord');

$lid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $houseId = (int)($_POST['house_id'] ?? 0);
    $check = $pdo->prepare('SELECT id FROM houses WHERE id=? AND landlord_id=?');
    $check->execute([$houseId, $lid]);
    if ($check->fetch()) {
        if ($_POST['action'] === 'delete') {
            $pdo->prepare('DELETE FROM houses WHERE id=?')->execute([$houseId]);
            flash('success', 'House deleted.');
        } elseif ($_POST['action'] === 'toggle_status') {
            $pdo->prepare("UPDATE houses SET status = IF(status='available','booked','available') WHERE id=?")->execute([$houseId]);
            flash('success', 'Availability updated.');
        }
    }
    redirect('landlord/my_houses.php');
}

$houses = $pdo->prepare(
    "SELECT h.*, (SELECT image_path FROM house_images WHERE house_id=h.id LIMIT 1) AS thumb
     FROM houses h WHERE h.landlord_id = ? ORDER BY h.created_at DESC"
);
$houses->execute([$lid]);
$houses = $houses->fetchAll();

$page_title = 'My Houses';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="section-title mb-0">My Houses</h3>
    <a href="<?= base_url('landlord/add_house.php') ?>" class="btn btn-brand"><i class="bi bi-plus-circle"></i> Add House</a>
  </div>

  <div class="row g-4">
    <?php foreach ($houses as $h): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card house-card">
          <img src="<?= $h['thumb'] ? base_url($h['thumb']) : base_url('assets/img/placeholder.svg') ?>" class="card-img-top">
          <div class="card-body">
            <div class="d-flex gap-2 mb-2">
              <span class="badge badge-<?= $h['approval_status'] ?>"><?= ucfirst($h['approval_status']) ?></span>
              <span class="badge badge-status-<?= $h['status'] ?>"><?= ucfirst($h['status']) ?></span>
            </div>
            <h5 class="card-title mb-1"><?= sanitize($h['title']) ?></h5>
            <p class="text-muted small mb-1"><?= sanitize($h['location']) ?></p>
            <p class="price-tag mb-2">KES <?= number_format($h['price']) ?></p>
            <div class="d-grid gap-2">
              <a href="<?= base_url('landlord/edit_house.php?id=' . $h['id']) ?>" class="btn btn-sm btn-outline-brand">Edit</a>
              <form method="POST"><input type="hidden" name="action" value="toggle_status"><input type="hidden" name="house_id" value="<?= $h['id'] ?>">
                <button class="btn btn-sm btn-outline-secondary w-100">Mark as <?= $h['status']==='available'?'Booked':'Available' ?></button>
              </form>
              <form method="POST"><input type="hidden" name="action" value="delete"><input type="hidden" name="house_id" value="<?= $h['id'] ?>">
                <button class="btn btn-sm btn-outline-danger w-100 confirm-action" data-confirm="Delete this listing permanently?">Delete</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$houses): ?><p class="text-muted">You haven't listed any houses yet.</p><?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
