<?php
// public/admin/submissions.php

require_once __DIR__ . '/../../app/helpers/utils.php';

require_role('admin');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';

$pageTitle = "Kelola Pengumpulan";

// Ambil semua pengumpulan (submissions) dari semua user
$query = "
    SELECT
        s.id AS submission_id,
        s.submitted_at,
        s.grade,
        t.title AS task_title,
        u.name AS student_name,
        u.username AS student_username,
        t.due_date
    FROM
        submissions s
    JOIN
        tasks t ON t.id = s.task_id
    JOIN
        users u ON u.id = s.user_id
    ORDER BY
        s.submitted_at DESC
";

$result = $conn->query($query);

if ($result === false) {
  die("Query daftar pengumpulan gagal: " . $conn->error);
}

$submissions = $result->fetch_all(MYSQLI_ASSOC);


require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid py-4">
  <div class="card shadow-lg border-0">
    <div class="card-header bg-primary text-white p-3">
      <h2 class="mb-0"><i class="fas fa-inbox me-2"></i> Daftar Semua Pengumpulan Tugas</h2>
    </div>
    <div class="card-body p-4">
      <?php if (empty($submissions)): ?>
        <div class="alert alert-info d-flex align-items-center" role="alert">
          <i class="fas fa-info-circle me-2"></i>
          <div>
            Belum ada tugas yang dikumpulkan oleh peserta.
          </div>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle" style="min-width: 1000px;">
            <thead class="table-light">
              <tr>
                <th scope="col">#</th>
                <th scope="col"><i class="fas fa-book me-1"></i> Tugas</th>
                <th scope="col"><i class="fas fa-user-graduate me-1"></i> Peserta</th>
                <th scope="col"><i class="fas fa-clock me-1"></i> Waktu Submit</th>
                <th scope="col"><i class="fas fa-calendar-alt me-1"></i> Batas Waktu</th>
                <th scope="col"><i class="fas fa-shield-alt me-1"></i> Status</th>
                <th scope="col"><i class="fas fa-star me-1"></i> Nilai</th>
                <th scope="col"><i class="fas fa-cogs me-1"></i> Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php $no = 1;
              foreach ($submissions as $sub):
                $isLate = (strtotime($sub['submitted_at']) > strtotime($sub['due_date']));
                $statusClass = $isLate ? 'bg-danger' : 'bg-success';
                $statusText = $isLate ? 'Terlambat' : 'Tepat Waktu';
                $gradeBadgeClass = $sub['grade'] ? 'bg-primary' : 'bg-secondary';
                $gradeText = $sub['grade'] ? htmlspecialchars($sub['grade']) : 'Belum Dinilai';
              ?>
                <tr>
                  <td><?= $no++ ?></td>
                  <td>
                    <div class="fw-bold"><?= htmlspecialchars($sub['task_title']) ?></div>
                  </td>
                  <td>
                    <div class="text-dark fw-semibold"><?= htmlspecialchars($sub['student_name']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($sub['student_username']) ?></small>
                  </td>
                  <td>
                    <?= date('d M Y', strtotime($sub['submitted_at'])) ?><br>
                    <span class="text-muted small"><?= date('H:i', strtotime($sub['submitted_at'])) ?> WIB</span>
                  </td>
                  <td>
                    <?= date('d M Y', strtotime($sub['due_date'])) ?><br>
                    <span class="text-muted small"><?= date('H:i', strtotime($sub['due_date'])) ?> WIB</span>
                  </td>
                  <td>
                    <span class="badge rounded-pill <?= $statusClass ?> p-2"><?= $statusText ?></span>
                  </td>
                  <td>
                    <span class="badge rounded-pill <?= $gradeBadgeClass ?> p-2"><?= $gradeText ?></span>
                  </td>
                  <td>
                    <a href="submission_view.php?id=<?= $sub['submission_id'] ?>" class="btn btn-sm btn-info text-white shadow-sm">
                      <i class="fas fa-edit me-1"></i> Nilai / Detail
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>