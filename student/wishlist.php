<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('student');

$sid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove') {
    $pdo->prepare('DELETE FROM wishlist WHERE id=? AND student_id=?')->execute([(int)$_POST['wish_id'], $sid]);
    flash('success', 'Removed from wishlist.');
    redirect('student/wishlist.php');
}

$items = $pdo->prepare(
    "SELECT w.id AS wish_id, h.* , (SELECT image_path FROM house_images WHERE house_id=h.id LIMIT 1) AS thumb
     FROM wishlist w JOIN houses h ON h.id = w.house_id
     WHERE w.student_id = ? ORDER BY w.created_at DESC"
);
$items->execute([$sid]);
$items = $items->fetchAll();

$page_title = 'My Wishlist';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">My Wishlist</h3>
  <div class="row g-4">
    <?php foreach ($items as $h): ?>
      <div class="col-sm-6 col-lg-4">
        <div class="card house-card">
          <img src="<?= $h['thumb'] ? base_url($h['thumb']) : base_url('assets/img/placeholder.svg') ?>" class="card-img-top">
          <div class="card-body">
            <span class="badge badge-type mb-2"><?= room_type_label($h['room_type']) ?></span>
            <h5 class="card-title mb-1"><?= sanitize($h['title']) ?></h5>
            <p class="text-muted small mb-1"><i class="bi bi-geo-alt"></i> <?= sanitize($h['location']) ?></p>
            <p class="price-tag mb-2">KES <?= number_format($h['price']) ?> / month</p>
            <a href="<?= base_url('house_details.php?id=' . $h['id']) ?>" class="btn btn-outline-brand btn-sm w-100 mb-2">View</a>
            <form method="POST">
              <input type="hidden" name="action" value="remove">
              <input type="hidden" name="wish_id" value="<?= $h['wish_id'] ?>">
              <button class="btn btn-sm btn-outline-danger w-100">Remove</button>
            </form>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$items): ?><p class="text-muted">Your wishlist is empty. <a href="<?= base_url('houses.php') ?>">Browse houses</a> and tap the heart to save some.</p><?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
