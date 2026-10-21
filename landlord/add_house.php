<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('landlord');

$lid = $_SESSION['user_id'];

if (($_SESSION['status'] ?? '') !== 'approved') {
    flash('warning', 'Your account must be approved by an admin before you can list houses.');
    redirect('landlord/dashboard.php');
}
$stmt = $pdo->prepare('SELECT posting_fee_status FROM landlord_profiles WHERE user_id=?');
$stmt->execute([$lid]);
if (($stmt->fetch()['posting_fee_status'] ?? 'unpaid') !== 'paid') {
    flash('warning', 'Please pay the listing fee before posting a house.');
    redirect('landlord/pay_fee.php');
}

$errors = [];
$old = ['title'=>'','room_type'=>'bedsitter','price'=>'','location'=>'','nearest_campus'=>'','distance_notes'=>'','description'=>'','amenities'=>'','map_link'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $v) { $old[$k] = trim($_POST[$k] ?? ''); }

    if ($old['title'] === '') $errors[] = 'Title is required.';
    if (!is_numeric($old['price']) || $old['price'] <= 0) $errors[] = 'Enter a valid price.';
    if ($old['location'] === '') $errors[] = 'Location is required.';

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO houses (landlord_id, title, room_type, price, location, nearest_campus, distance_notes, description, amenities, map_link)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->execute([
            $lid, $old['title'], $old['room_type'], $old['price'], $old['location'],
            $old['nearest_campus'], $old['distance_notes'], $old['description'], $old['amenities'], $old['map_link']
        ]);
        $houseId = $pdo->lastInsertId();

        if (!empty($_FILES['photos']['name'][0])) {
            foreach ($_FILES['photos']['name'] as $i => $name) {
                if ($name === '') continue;
                $singleFile = [
                    'name' => $_FILES['photos']['name'][$i], 'type' => $_FILES['photos']['type'][$i],
                    'tmp_name' => $_FILES['photos']['tmp_name'][$i], 'error' => $_FILES['photos']['error'][$i],
                    'size' => $_FILES['photos']['size'][$i],
                ];
                $_FILES['__single'] = $singleFile;
                $path = handle_image_upload('__single', 'houses');
                if ($path) $pdo->prepare('INSERT INTO house_images (house_id, image_path) VALUES (?,?)')->execute([$houseId, $path]);
            }
        }

        flash('success', 'House listed! It will be visible to students once an admin approves it.');
        redirect('landlord/my_houses.php');
    }
}

$page_title = 'Add a House';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="section-title">List a New House</h3>
  <div class="sidebar-card">
    <?php foreach ($errors as $e): ?><div class="alert alert-danger py-2"><?= sanitize($e) ?></div><?php endforeach; ?>
    <form method="POST" enctype="multipart/form-data">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Title</label>
          <input type="text" name="title" class="form-control" value="<?= sanitize($old['title']) ?>" placeholder="e.g. Cozy bedsitter near main gate" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Room Type</label>
          <select name="room_type" class="form-select">
            <?php foreach (['bedsitter','single','one_bedroom','two_bedroom','hostel'] as $rt): ?>
              <option value="<?= $rt ?>" <?= $old['room_type']===$rt?'selected':'' ?>><?= room_type_label($rt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Price (KES / month)</label>
          <input type="number" step="0.01" name="price" class="form-control" value="<?= sanitize($old['price']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Location / Area</label>
          <input type="text" name="location" class="form-control" value="<?= sanitize($old['location']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Nearest Campus</label>
          <input type="text" name="nearest_campus" class="form-control" value="<?= sanitize($old['nearest_campus']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Distance Notes</label>
          <input type="text" name="distance_notes" class="form-control" placeholder="e.g. 5 min walk to gate" value="<?= sanitize($old['distance_notes']) ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Google Maps Link <span class="text-muted">(optional — pin the location)</span></label>
          <input type="url" name="map_link" class="form-control" placeholder="https://maps.google.com/..." value="<?= sanitize($old['map_link']) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" rows="4" class="form-control"><?= sanitize($old['description']) ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label">Amenities <span class="text-muted">(comma separated)</span></label>
          <input type="text" name="amenities" class="form-control" placeholder="e.g. Wi-Fi, Water included, Security, Parking" value="<?= sanitize($old['amenities']) ?>">
        </div>
        <div class="col-12">
          <label class="form-label">Photos <span class="text-muted">(you can select multiple)</span></label>
          <input type="file" name="photos[]" class="form-control" multiple accept="image/*">
        </div>
      </div>
      <button class="btn btn-brand mt-4 px-4 py-2">Submit for Approval</button>
    </form>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
