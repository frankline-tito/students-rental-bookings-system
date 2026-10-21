<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/../config/db.php';
$page_title = $page_title ?? SITE_NAME;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= sanitize($page_title) ?> · <?= SITE_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
<?php $flashes = get_flashes(); if ($flashes): ?>
  <div class="container mt-3">
    <?php foreach ($flashes as $f): ?>
      <div class="alert alert-<?= $f['type'] ?> alert-dismissible fade show" role="alert">
        <?= sanitize($f['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
