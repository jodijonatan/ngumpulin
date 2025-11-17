<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../includes/navbar_admin.php';

// Statistik
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='member'")->fetch_assoc()['total'];
$total_tasks = $conn->query("SELECT COUNT(*) AS total FROM tasks")->fetch_assoc()['total'];
$total_submissions = $conn->query("SELECT COUNT(*) AS total FROM submissions")->fetch_assoc()['total'];
?>

<div class="container mt-4">
  <h2>Dashboard Admin</h2>

  <div class="row mt-4">
    <div class="col-md-4">
      <div class="card text-bg-primary mb-3 p-3">
        <h4>Total Peserta</h4>
        <p class="fs-3 fw-bold"><?= $total_users ?></p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card text-bg-success mb-3 p-3">
        <h4>Total Tugas</h4>
        <p class="fs-3 fw-bold"><?= $total_tasks ?></p>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card text-bg-warning mb-3 p-3">
        <h4>Total Pengumpulan</h4>
        <p class="fs-3 fw-bold"><?= $total_submissions ?></p>
      </div>
    </div>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>