<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$landlords = $pdo->query("SELECT id, full_name FROM users WHERE role='landlord' AND status='approved' ORDER BY full_name")->fetchAll();
$errors = [];
$old = ['landlord_id'=>'','title'=>'','room_type'=>'bedsitter','price'=>'','location'=>'','nearest_campus'=>'','distance_notes'=>'','description'=>'','amenities'=>'','map_link'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) { $old[$k] = trim($_POST[$k] ?? ''); }

    if (!$old['landlord_id']) $errors[] = 'Select a landlord.';
    if ($old['title'] === '') $errors[] = 'Title is required.';
    if (!is_numeric($old['price']) || $old['price'] <= 0) $errors[] = 'Enter a valid price.';
    if ($old['location'] === '') $errors[] = 'Location is required.';

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO houses (landlord_id, title, room_type, price, location, nearest_campus, distance_notes, description, amenities, map_link, approval_status)
             VALUES (?,?,?,?,?,?,?,?,?,?,"approved")'
        );
        $stmt->execute([
            $old['landlord_id'], $old['title'], $old['room_type'], $old['price'], $old['location'],
            $old['nearest_campus'], $old['distance_notes'], $old['description'], $old['amenities'], $old['map_link']
        ]);
        $houseId = $pdo->lastInsertId();

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['name'] as $i => $name) {
                if ($name === '') continue;
                $_FILES['__single'] = [
                    'name' => $_FILES['photos']['name'][$i], 'type' => $_FILES['photos']['type'][$i],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$i], 'error' => $_FILES['photos']['error'][$i],
                    'size' => $_FILES['photos']['size'][$i],
                ];
                $path = handle_image_upload('__single', 'houses');
                if ($path) $pdo->prepare('INSERT INTO house_images (house_id, image_path) VALUES (?,?)')->execute([$houseId, $path]);
            }
        }

        flash('success', 'House added and published (admin-added listings are auto-approved).');
        redirect('admin/houses.php');
    }
}

$page_title = 'Add House (Admin)';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">Add a House on Behalf of a Landlord</h3>
  <div class="sidebar-card">
    <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?= sanitize($e) ?></div><?php endforeach; ?>
    <form method="POST" enctype="multipart/form-data">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Landlord</label>
          <select name="landlord_id" class="form-select" required>
            <option value="">Select landlord...</option>
            <?php foreach ($landlords as $l): ?>
              <option value="<?= $l['id'] ?>" <?= $old['landlord_id']==$l['id']?'selected':'' ?>><?= sanitize($l['full_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Room Type</label>
          <select name="room_type" class="form-select">
            <?php foreach (['bedsitter','single','one_bedroom','two_bedroom','hostel'] as $rt): ?>
              <option value="<?= $rt ?>" <?= $old['room_type']===$rt?'selected':'' ?>><?= room_type_label($rt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-8"><label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" value="<?= sanitize($old['title']) ?>" required></div>
        <div class="col-md-4"><label class="form-label">Price (KES/month)</label>
          <input type="number" step="0.01" name="price" class="form-control" value="<?= sanitize($old['price']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Location</label>
          <input type="text" name="location" class="form-control" value="<?= sanitize($old['location']) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Nearest Campus</label>
          <input type="text" name="nearest_campus" class="form-control" value="<?= sanitize($old['nearest_campus']) ?>"></div>
        <div class="col-12"><label class="form-label">Description</label>
          <textarea name="description" rows="3" class="form-control"><?= sanitize($old['description']) ?></textarea></div>
        <div class="col-12"><label class="form-label">Amenities</label>
          <input type="text" name="amenities" class="form-control" value="<?= sanitize($old['amenities']) ?>"></div>
        <div class="col-12"><label class="form-label">Photos</label>
          <input type="file" name="photos[]" class="form-control" multiple accept="image/*"></div>
      </div>
      <button class="btn btn-brand mt-4 px-4 py-2">Add House</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
