<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('landlord');

$lid = $_SESSION['user_id'];
$id  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM houses WHERE id=? AND landlord_id=?');
$stmt->execute([$id, $lid]);
$house = $stmt->fetch();
if (!$house) { flash('danger', 'House not found.'); redirect('landlord/my_houses.php'); }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['form'] ?? '') === 'delete_image') {
        $pdo->prepare('DELETE FROM house_images WHERE id=? AND house_id=?')->execute([(int)$_POST['image_id'], $id]);
        flash('success', 'Photo removed.');
        redirect('landlord/edit_house.php?id=' . $id);
    }

    $title = trim($_POST['title'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if ($title === '') $errors[] = 'Title is required.';
    if (!is_numeric($price) || $price <= 0) $errors[] = 'Enter a valid price.';
    if ($location === '') $errors[] = 'Location is required.';

    if (!$errors) {
        $pdo->prepare(
            'UPDATE houses SET title=?, room_type=?, price=?, location=?, nearest_campus=?, distance_notes=?,
             description=?, amenities=?, map_link=? WHERE id=?'
        )->execute([
            $title, $_POST['room_type'], $price, $location, trim($_POST['nearest_campus'] ?? ''),
            trim($_POST['distance_notes'] ?? ''), trim($_POST['description'] ?? ''), trim($_POST['amenities'] ?? ''),
            trim($_POST['map_link'] ?? ''), $id
        ]);

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['name'] as $i => $name) {
                if ($name === '') continue;
                $_FILES['__single'] = [
                    'name' => $_FILES['photos']['name'][$i], 'type' => $_FILES['photos']['type'][$i],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$i], 'error' => $_FILES['photos']['error'][$i],
                    'size' => $_FILES['photos']['size'][$i],
                ];
                $path = handle_image_upload('__single', 'houses');
                if ($path) $pdo->prepare('INSERT INTO house_images (house_id, image_path) VALUES (?,?)')->execute([$id, $path]);
            }
        }

        flash('success', 'House updated.');
        redirect('landlord/edit_house.php?id=' . $id);
    }
}

$images = $pdo->prepare('SELECT * FROM house_images WHERE house_id=?');
$images->execute([$id]);
$images = $images->fetchAll();

$page_title = 'Edit House';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">Edit House</h3>
  <div class="sidebar-card">
    <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?= sanitize($e) ?></div><?php endforeach; ?>
    <form method="POST" enctype="multipart/form-data">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" value="<?= sanitize($house['title']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Room Type</label>
          <select name="room_type" class="form-select">
            <?php foreach (['bedsitter','single','one_bedroom','two_bedroom','hostel'] as $rt): ?>
              <option value="<?= $rt ?>" <?= $house['room_type']===$rt?'selected':'' ?>><?= room_type_label($rt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Price (KES / month)</label>
          <input type="number" step="0.01" name="price" class="form-control" value="<?= sanitize($house['price']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Location / Area</label>
          <input type="text" name="location" class="form-control" value="<?= sanitize($house['location']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Nearest Campus</label>
          <input type="text" name="nearest_campus" class="form-control" value="<?= sanitize($house['nearest_campus']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Distance Notes</label>
          <input type="text" name="distance_notes" class="form-control" value="<?= sanitize($house['distance_notes']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Google Maps Link</label>
          <input type="url" name="map_link" class="form-control" value="<?= sanitize($house['map_link']) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" rows="4" class="form-control"><?= sanitize($house['description']) ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Amenities</label>
          <input type="text" name="amenities" class="form-control" value="<?= sanitize($house['amenities']) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Add More Photos</label>
          <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
        </div>
      </div>
      <button class="btn btn-brand mt-4 px-4 py-2">Save Changes</button>
      <a href="<?= base_url('landlord/my_houses.php') ?>" class="btn btn-outline-brand mt-4 px-4 py-2">Back</a>
    </form>

    <?php if ($images): ?>
      <hr>
      <h6>Current Photos</h6>
      <div class="d-flex flex-wrap gap-2">
        <?php foreach ($images as $img): ?>
          <div>
            <img src="<?= base_url($img['image_path']) ?>" style="width:120px;height:90px;object-fit:cover;border-radius:8px;">
            <form method="POST" class="mt-1">
              <input type="hidden" name="form" value="delete_image">
              <input type="hidden" name="image_id" value="<?= $img['id'] ?>">
              <button class="btn btn-sm btn-outline-danger w-100 confirm-action" data-confirm="Remove this photo?">Remove</button>
            </form>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
