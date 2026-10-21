<footer class="site-footer mt-5">
  <div class="container py-4">
    <div class="row gy-3">
      <div class="col-md-4">
        <h5 class="text-white"><i class="bi bi-houses-fill"></i> <?= SITE_NAME ?></h5>
        <p class="text-white-50 small mb-0">Helping campus students find genuine, verified houses near school — no more walking from plot to plot.</p>
      </div>
      <div class="col-md-4">
        <h6 class="text-white">Quick Links</h6>
        <ul class="list-unstyled small">
          <li><a href="<?= base_url('houses.php') ?>">Browse Houses</a></li>
          <li><a href="<?= base_url('auth/register.php') ?>">Create an Account</a></li>
          <li><a href="<?= base_url('auth/login.php') ?>">Login</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h6 class="text-white">For Landlords</h6>
        <p class="text-white-50 small">List your rooms, reach hundreds of students, and manage bookings from one dashboard.</p>
      </div>
    </div>
    <hr class="border-secondary">
    <p class="text-white-50 small mb-0 text-center">&copy; <?= date('Y') ?> <?= SITE_NAME ?>. Final year project — built for campus students.</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('assets/js/script.js') ?>"></script>
</body>
</html>
