<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/db.php';

$featured = $pdo->query(
    "SELECT h.*, u.full_name AS landlord_name,
            (SELECT image_path FROM house_images WHERE house_id = h.id LIMIT 1) AS thumb
     FROM houses h JOIN users u ON u.id = h.landlord_id
     WHERE h.approval_status = 'approved' AND h.status = 'available'
     ORDER BY h.created_at DESC LIMIT 6"
)->fetchAll();

$totalHouses   = $pdo->query("SELECT COUNT(*) c FROM houses WHERE approval_status='approved'")->fetch()['c'];
$totalStudents = $pdo->query("SELECT COUNT(*) c FROM users WHERE role='student'")->fetch()['c'];
$totalLandlords= $pdo->query("SELECT COUNT(*) c FROM users WHERE role='landlord' AND status='approved'")->fetch()['c'];

$page_title = 'Find Your Perfect Campus Home';
require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <h1 class="fw-bold">Stop wandering plot to plot.<br>Find your house before you even set foot on campus.</h1>
        <p class="lead">CampusNest connects first-year and returning students with verified landlords near school — browse rooms, compare prices, and book online.</p>
        <div class="d-flex gap-3 flex-wrap mt-3">
          <div class="stat-pill"><strong><?= (int)$totalHouses ?></strong> Listings</div>
          <div class="stat-pill"><strong><?= (int)$totalStudents ?></strong> Students</div>
          <div class="stat-pill"><strong><?= (int)$totalLandlords ?></strong> Landlords</div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="container">
  <div class="search-card">
    <form method="GET" action="<?= base_url('houses.php') ?>" class="row g-3 align-items-end">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Location / Area</label>
        <input type="text" name="location" class="form-control" placeholder="e.g. Kikuyu, Chuka, Egerton...">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Room Type</label>
        <select name="room_type" class="form-select">
          <option value="">Any type</option>
          <option value="bedsitter">Bedsitter</option>
          <option value="single">Single Room</option>
          <option value="one_bedroom">One Bedroom</option>
          <option value="two_bedroom">Two Bedroom</option>
          <option value="hostel">Hostel Room</option>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Max Price (KES)</label>
        <input type="number" name="max_price" class="form-control" placeholder="e.g. 6000">
      </div>
      <div class="col-md-2">
        <button class="btn btn-warm w-100 py-2" type="submit"><i class="bi bi-search"></i> Search</button>
      </div>
    </form>
  </div>
</div>

<div class="container my-5">
  <h3 class="section-title">Featured Houses</h3>
  <div class="row g-4">
    <?php if (!$featured): ?>
      <p class="text-muted">No listings yet — check back soon, or <a href="<?= base_url('auth/register.php') ?>">register as a landlord</a> to be the first!</p>
    <?php endif; ?>
    <?php foreach ($featured as $h): ?>
      <div class="col-sm-6 col-lg-4">
        <div class="card house-card">
          <img src="<?= $h['thumb'] ? base_url($h['thumb']) : base_url('assets/img/placeholder.svg') ?>" class="card-img-top" alt="<?= sanitize($h['title']) ?>">
          <div class="card-body">
            <span class="badge badge-type mb-2"><?= room_type_label($h['room_type']) ?></span>
            <h5 class="card-title mb-1"><?= sanitize($h['title']) ?></h5>
            <p class="text-muted small mb-1"><i class="bi bi-geo-alt"></i> <?= sanitize($h['location']) ?></p>
            <p class="price-tag mb-2">KES <?= number_format($h['price']) ?> / month</p>
            <a href="<?= base_url('house_details.php?id=' . $h['id']) ?>" class="btn btn-outline-brand btn-sm w-100">View Details</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php if ($featured): ?>
    <div class="text-center mt-4">
      <a href="<?= base_url('houses.php') ?>" class="btn btn-brand px-4">Browse All Houses</a>
    </div>
  <?php endif; ?>
</div>

<div class="container my-5">
  <div class="row g-4">
    <div class="col-md-4">
      <div class="sidebar-card text-center h-100">
        <i class="bi bi-search fs-1" style="color:var(--brand)"></i>
        <h5 class="mt-2">1. Search</h5>
        <p class="text-muted small mb-0">Filter houses by location, price, and room type near your campus — from anywhere, before you even arrive.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sidebar-card text-center h-100">
        <i class="bi bi-calendar-check fs-1" style="color:var(--brand)"></i>
        <h5 class="mt-2">2. Book</h5>
        <p class="text-muted small mb-0">Send a booking request directly to the landlord and get confirmation on your dashboard.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="sidebar-card text-center h-100">
        <i class="bi bi-house-heart fs-1" style="color:var(--brand)"></i>
        <h5 class="mt-2">3. Move In</h5>
        <p class="text-muted small mb-0">Pay your landlord directly via their listed M-Pesa/Paybill/bank details and settle in stress-free.</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
