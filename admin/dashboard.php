<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../config/db.php';
require_role('admin');

$q = function ($sql) use ($pdo) { return $pdo->query($sql)->fetch()['c']; };

$stats = [
    'students'         => $q("SELECT COUNT(*) c FROM users WHERE role='student'"),
    'landlords'        => $q("SELECT COUNT(*) c FROM users WHERE role='landlord'"),
    'pending_landlords'=> $q("SELECT COUNT(*) c FROM users WHERE role='landlord' AND status='pending'"),
    'houses'           => $q("SELECT COUNT(*) c FROM houses"),
    'pending_houses'   => $q("SELECT COUNT(*) c FROM houses WHERE approval_status='pending'"),
    'bookings'         => $q("SELECT COUNT(*) c FROM bookings"),
    'pending_fees'     => $q("SELECT COUNT(*) c FROM landlord_profiles WHERE posting_fee_status='pending'"),
];

$page_title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="container py-4">
  <h3 class="mb-1">Admin Control Panel</h3>
  <p class="text-muted mb-4">Manage landlords, students, houses, bookings and payments.</p>

  <div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3"><div class="dash-stat"><div class="num"><?= $stats['students'] ?></div>Students</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat"><div class="num"><?= $stats['landlords'] ?></div>Landlords</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat accent"><div class="num"><?= $stats['pending_landlords'] ?></div>Landlords Awaiting Approval</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat"><div class="num"><?= $stats['houses'] ?></div>Total Houses</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat accent"><div class="num"><?= $stats['pending_houses'] ?></div>Houses Awaiting Approval</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat"><div class="num"><?= $stats['bookings'] ?></div>Total Bookings</div></div>
    <div class="col-sm-6 col-lg-3"><div class="dash-stat accent"><div class="num"><?= $stats['pending_fees'] ?></div>Fee Payments to Confirm</div></div>
  </div>

  <div class="sidebar-card">
    <h5>Quick Links</h5>
    <div class="d-flex flex-wrap gap-2">
      <a class="btn btn-outline-brand" href="<?= base_url('admin/landlords.php') ?>"><i class="bi bi-building"></i> Manage Landlords</a>
      <a class="btn btn-outline-brand" href="<?= base_url('admin/students.php') ?>"><i class="bi bi-mortarboard"></i> Manage Students</a>
      <a class="btn btn-outline-brand" href="<?= base_url('admin/houses.php') ?>"><i class="bi bi-houses"></i> Manage Houses</a>
      <a class="btn btn-outline-brand" href="<?= base_url('admin/bookings.php') ?>"><i class="bi bi-calendar-check"></i> All Bookings</a>
      <a class="btn btn-outline-brand" href="<?= base_url('admin/fees.php') ?>"><i class="bi bi-cash-coin"></i> Fee Confirmations</a>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
