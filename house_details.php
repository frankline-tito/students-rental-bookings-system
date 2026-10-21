<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT h.*, u.full_name AS landlord_name, u.phone AS landlord_phone, u.email AS landlord_email,
            lp.mpesa_number, lp.paybill_number, lp.paybill_account, lp.bank_name, lp.bank_account_number, lp.account_name
     FROM houses h
     JOIN users u ON u.id = h.landlord_id
     LEFT JOIN landlord_profiles lp ON lp.user_id = u.id
     WHERE h.id = ?"
);
$stmt->execute([$id]);
$house = $stmt->fetch();

if (!$house || $house['approval_status'] !== 'approved') {
    flash('danger', 'That listing could not be found.');
    redirect('houses.php');
}

$images = $pdo->prepare('SELECT * FROM house_images WHERE house_id = ?');
$images->execute([$id]);
$images = $images->fetchAll();

// ---- Handle actions ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if (!is_logged_in() || current_role() !== 'student') {
        flash('warning', 'Please log in as a student to do that.');
        redirect('auth/login.php');
    }

    $studentId = $_SESSION['user_id'];

    if ($action === 'book') {
        if ($house['status'] !== 'available') {
            flash('danger', 'Sorry, this house is no longer available.');
        } else {
            $msg = trim($_POST['message'] ?? '');
            $pdo->prepare('INSERT INTO bookings (student_id, house_id, message) VALUES (?,?,?)')
                ->execute([$studentId, $id, $msg]);
            flash('success', 'Booking request sent! The landlord will confirm it shortly. Track it under "My Bookings".');
        }
        redirect('house_details.php?id=' . $id);
    }

    if ($action === 'wishlist') {
        $exists = $pdo->prepare('SELECT id FROM wishlist WHERE student_id=? AND house_id=?');
        $exists->execute([$studentId, $id]);
        if ($exists->fetch()) {
            $pdo->prepare('DELETE FROM wishlist WHERE student_id=? AND house_id=?')->execute([$studentId, $id]);
            flash('success', 'Removed from wishlist.');
        } else {
            $pdo->prepare('INSERT INTO wishlist (student_id, house_id) VALUES (?,?)')->execute([$studentId, $id]);
            flash('success', 'Added to wishlist.');
        }
        redirect('house_details.php?id=' . $id);
    }

    if ($action === 'review') {
        $rating  = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $comment = trim($_POST['comment'] ?? '');
        $pdo->prepare('INSERT INTO reviews (house_id, student_id, rating, comment) VALUES (?,?,?,?)')
            ->execute([$id, $studentId, $rating, $comment]);
        flash('success', 'Thanks for your review!');
        redirect('house_details.php?id=' . $id);
    }
}

$reviews = $pdo->prepare(
    "SELECT r.*, u.full_name FROM reviews r JOIN users u ON u.id = r.student_id
     WHERE r.house_id = ? ORDER BY r.created_at DESC"
);
$reviews->execute([$id]);
$reviews = $reviews->fetchAll();
$avgRating = $reviews ? round(array_sum(array_column($reviews, 'rating')) / count($reviews), 1) : null;

$isWishlisted = false;
if (is_logged_in() && current_role() === 'student') {
    $w = $pdo->prepare('SELECT id FROM wishlist WHERE student_id=? AND house_id=?');
    $w->execute([$_SESSION['user_id'], $id]);
    $isWishlisted = (bool)$w->fetch();
}

$page_title = $house['title'];
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-7">
      <img id="mainHouseImage" src="<?= $images ? base_url($images[0]['image_path']) : base_url('assets/img/placeholder.svg') ?>"
           class="img-fluid rounded-4 mb-2 w-100" style="max-height:420px;object-fit:cover;" alt="<?= sanitize($house['title']) ?>">
      <?php if (count($images) > 1): ?>
        <div class="thumb-scroll mb-3">
          <?php foreach ($images as $i => $img): ?>
            <img src="<?= base_url($img['image_path']) ?>" data-full="<?= base_url($img['image_path']) ?>" class="<?= $i===0?'active':'' ?>">
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="d-flex gap-2 align-items-center mb-2">
        <span class="badge badge-type"><?= room_type_label($house['room_type']) ?></span>
        <span class="badge badge-status-<?= $house['status'] ?>"><?= ucfirst($house['status']) ?></span>
        <?php if ($avgRating): ?><span class="text-warning small"><?= star_rating($avgRating) ?> (<?= $avgRating ?>)</span><?php endif; ?>
      </div>
      <h2><?= sanitize($house['title']) ?></h2>
      <p class="text-muted"><i class="bi bi-geo-alt"></i> <?= sanitize($house['location']) ?>
        <?php if ($house['nearest_campus']): ?> &middot; near <?= sanitize($house['nearest_campus']) ?><?php endif; ?>
        <?php if ($house['distance_notes']): ?> &middot; <?= sanitize($house['distance_notes']) ?><?php endif; ?>
      </p>
      <p class="price-tag fs-4">KES <?= number_format($house['price']) ?> / month</p>
      <p><?= nl2br(sanitize($house['description'])) ?></p>
      <?php if ($house['amenities']): ?>
        <p><strong>Amenities:</strong> <?= sanitize($house['amenities']) ?></p>
      <?php endif; ?>
      <?php if ($house['map_link']): ?>
        <p><a href="<?= sanitize($house['map_link']) ?>" target="_blank"><i class="bi bi-map"></i> View pinned location on map</a></p>
      <?php endif; ?>

      <hr>
      <h5>Reviews <?= $reviews ? '(' . count($reviews) . ')' : '' ?></h5>
      <?php foreach ($reviews as $r): ?>
        <div class="mb-3">
          <strong><?= sanitize($r['full_name']) ?></strong> <span class="text-warning"><?= star_rating($r['rating']) ?></span>
          <p class="mb-0 small text-muted"><?= sanitize($r['comment']) ?></p>
        </div>
      <?php endforeach; ?>
      <?php if (!$reviews): ?><p class="text-muted small">No reviews yet.</p><?php endif; ?>

      <?php if (is_logged_in() && current_role() === 'student'): ?>
        <form method="POST" class="sidebar-card mt-3">
          <input type="hidden" name="action" value="review">
          <label class="form-label fw-semibold">Leave a review</label>
          <select name="rating" class="form-select mb-2" style="max-width:160px;">
            <?php for ($s = 5; $s >= 1; $s--): ?><option value="<?= $s ?>"><?= $s ?> Star<?= $s>1?'s':'' ?></option><?php endfor; ?>
          </select>
          <textarea name="comment" class="form-control mb-2" rows="2" placeholder="Share your experience..."></textarea>
          <button class="btn btn-outline-brand btn-sm">Submit Review</button>
        </form>
      <?php endif; ?>
    </div>

    <div class="col-lg-5">
      <div class="sidebar-card mb-3">
        <h6 class="fw-bold"><i class="bi bi-person-circle"></i> Landlord</h6>
        <p class="mb-1"><?= sanitize($house['landlord_name']) ?></p>
        <p class="mb-1 small text-muted"><i class="bi bi-telephone"></i> <?= sanitize($house['landlord_phone']) ?></p>
        <p class="mb-0 small text-muted"><i class="bi bi-envelope"></i> <?= sanitize($house['landlord_email']) ?></p>
      </div>

      <div class="pay-info mb-3">
        <h6 class="fw-bold text-brand"><i class="bi bi-cash-coin"></i> Payment Details</h6>
        <p class="small text-muted mb-2">Once your booking is confirmed, pay rent/deposit directly to the landlord using:</p>
        <?php if ($house['mpesa_number']): ?><p class="mb-1"><strong>M-Pesa (Send Money):</strong> <?= sanitize($house['mpesa_number']) ?></p><?php endif; ?>
        <?php if ($house['paybill_number']): ?><p class="mb-1"><strong>Paybill:</strong> <?= sanitize($house['paybill_number']) ?> — Acc: <?= sanitize($house['paybill_account']) ?></p><?php endif; ?>
        <?php if ($house['bank_name']): ?><p class="mb-1"><strong>Bank:</strong> <?= sanitize($house['bank_name']) ?> — <?= sanitize($house['bank_account_number']) ?></p><?php endif; ?>
        <?php if ($house['account_name']): ?><p class="mb-0"><strong>Account Name:</strong> <?= sanitize($house['account_name']) ?></p><?php endif; ?>
        <?php if (!$house['mpesa_number'] && !$house['paybill_number'] && !$house['bank_name']): ?>
          <p class="mb-0 small text-muted">The landlord hasn't added payment details yet — contact them directly.</p>
        <?php endif; ?>
      </div>

      <div class="sidebar-card">
        <?php if ($house['status'] === 'available'): ?>
          <form method="POST">
            <input type="hidden" name="action" value="book">
            <label class="form-label small fw-semibold">Message to landlord (optional)</label>
            <textarea name="message" class="form-control mb-2" rows="2" placeholder="e.g. I'd like to move in from next month..."></textarea>
            <button type="submit" class="btn btn-brand w-100 py-2 mb-2"><i class="bi bi-calendar-check"></i> Request Booking</button>
          </form>
        <?php else: ?>
          <button class="btn btn-secondary w-100 py-2 mb-2" disabled>Already Booked</button>
        <?php endif; ?>
        <form method="POST">
          <input type="hidden" name="action" value="wishlist">
          <button type="submit" class="btn btn-outline-brand w-100">
            <i class="bi bi-heart<?= $isWishlisted ? '-fill' : '' ?>"></i> <?= $isWishlisted ? 'Remove from Wishlist' : 'Save to Wishlist' ?>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
