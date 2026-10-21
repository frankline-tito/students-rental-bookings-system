<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$location  = trim($_GET['location'] ?? '');
$roomType  = trim($_GET['room_type'] ?? '');
$maxPrice  = trim($_GET['max_price'] ?? '');
$keyword   = trim($_GET['keyword'] ?? '');

$sql = "SELECT h.*, u.full_name AS landlord_name,
          (SELECT image_path FROM house_images WHERE house_id = h.id LIMIT 1) AS thumb,
          (SELECT ROUND(AVG(rating),1) FROM reviews WHERE house_id = h.id) AS avg_rating
        FROM houses h JOIN users u ON u.id = h.landlord_id
        WHERE h.approval_status = 'approved'";
$params = [];

if ($location !== '') { $sql .= " AND h.location LIKE ?"; $params[] = "%$location%"; }
if ($roomType !== '') { $sql .= " AND h.room_type = ?"; $params[] = $roomType; }
if ($maxPrice !== '' && is_numeric($maxPrice)) { $sql .= " AND h.price <= ?"; $params[] = $maxPrice; }
if ($keyword !== '') { $sql .= " AND (h.title LIKE ? OR h.description LIKE ?)"; $params[] = "%$keyword%"; $params[] = "%$keyword%"; }

$sql .= " ORDER BY h.status = 'available' DESC, h.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$houses = $stmt->fetchAll();

$page_title = 'Browse Houses';
require_once __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">Browse Houses</h3>

  <form method="GET" class="row g-2 mb-4 sidebar-card">
    <div class="col-md-3">
      <input type="text" name="location" class="form-control" placeholder="Location / Area" value="<?= sanitize($location) ?>">
    </div>
    <div class="col-md-3">
      <select name="room_type" class="form-select">
        <option value="">Any type</option>
        <?php foreach (['bedsitter','single','one_bedroom','two_bedroom','hostel'] as $rt): ?>
          <option value="<?= $rt ?>" <?= $roomType===$rt?'selected':'' ?>><?= room_type_label($rt) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <input type="number" name="max_price" class="form-control" placeholder="Max price" value="<?= sanitize($maxPrice) ?>">
    </div>
    <div class="col-md-2">
      <input type="text" name="keyword" class="form-control" placeholder="Keyword" value="<?= sanitize($keyword) ?>">
    </div>
    <div class="col-md-2">
      <button class="btn btn-brand w-100" type="submit">Filter</button>
    </div>
  </form>

  <p class="text-muted"><?= count($houses) ?> house(s) found</p>

  <div class="row g-4">
    <?php foreach ($houses as $h): ?>
      <div class="col-sm-6 col-lg-4">
        <div class="card house-card">
          <img src="<?= $h['thumb'] ? base_url($h['thumb']) : base_url('assets/img/placeholder.svg') ?>" class="card-img-top" alt="<?= sanitize($h['title']) ?>">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-1">
              <span class="badge badge-type"><?= room_type_label($h['room_type']) ?></span>
              <span class="badge badge-status-<?= $h['status'] ?>"><?= ucfirst($h['status']) ?></span>
            </div>
            <h5 class="card-title mb-1"><?= sanitize($h['title']) ?></h5>
            <p class="text-muted small mb-1"><i class="bi bi-geo-alt"></i> <?= sanitize($h['location']) ?></p>
            <?php if ($h['avg_rating']): ?>
              <p class="small mb-1 text-warning"><?= star_rating($h['avg_rating']) ?> <span class="text-muted">(<?= $h['avg_rating'] ?>)</span></p>
            <?php endif; ?>
            <p class="price-tag mb-2">KES <?= number_format($h['price']) ?> / month</p>
            <a href="<?= base_url('house_details.php?id=' . $h['id']) ?>" class="btn btn-outline-brand btn-sm w-100">View Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
    <?php if (!$houses): ?>
      <p class="text-muted">No houses matched your search. Try clearing some filters.</p>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
