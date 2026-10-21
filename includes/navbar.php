<nav class="navbar navbar-expand-lg navbar-dark sticky-top brand-nav">
  <div class="container">
    <a class="navbar-brand" href="<?= base_url('index.php') ?>">
      <i class="bi bi-houses-fill"></i> <?= SITE_NAME ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
        <li class="nav-item"><a class="nav-link" href="<?= base_url('index.php') ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= base_url('houses.php') ?>">Browse Houses</a></li>

        <?php if (!is_logged_in()): ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('auth/login.php') ?>">Login</a></li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-warm btn-sm" href="<?= base_url('auth/register.php') ?>">Get Started</a>
          </li>

        <?php elseif (current_role() === 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/dashboard.php') ?>">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/landlords.php') ?>">Landlords</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students.php') ?>">Students</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/houses.php') ?>">Houses</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/bookings.php') ?>">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>

        <?php elseif (current_role() === 'landlord'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('landlord/dashboard.php') ?>">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('landlord/my_houses.php') ?>">My Houses</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('landlord/bookings.php') ?>">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('landlord/profile.php') ?>">Payment & Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>

        <?php elseif (current_role() === 'student'): ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('student/dashboard.php') ?>">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('student/my_bookings.php') ?>">My Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('student/wishlist.php') ?>">Wishlist</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('student/profile.php') ?>">Profile</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
