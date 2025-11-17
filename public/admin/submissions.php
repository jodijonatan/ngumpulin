<?php
// public/admin/submissions.php

$requireAdmin = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth_check.php';

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

<div class="container-fluid">
  <h2>Daftar Semua Pengumpulan Tugas</h2>

  <?php if (empty($submissions)): ?>
    <div class="alert alert-info" role="alert">
      Belum ada tugas yang dikumpulkan oleh peserta.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Tugas</th>
            <th>Peserta</th>
            <th>Waktu Submit</th>
            <th>Status</th>
            <th>Nilai</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          foreach ($submissions as $sub):
            $isLate = (strtotime($sub['submitted_at']) > strtotime($sub['due_date']));
            $statusClass = $isLate ? 'bg-danger' : 'bg-success';
            $statusText = $isLate ? 'Terlambat' : 'Tepat Waktu';
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($sub['task_title']) ?></td>
              <td><?= htmlspecialchars($sub['student_name']) ?> (<?= htmlspecialchars($sub['student_username']) ?>)</td>
              <td>
                <?= date('d M Y H:i', strtotime($sub['submitted_at'])) ?>
                <span class="badge <?= $statusClass ?>"><?= $statusText ?></span>
              </td>
              <td><?= date('d M Y H:i', strtotime($sub['due_date'])) ?></td>
              <td>
                <?php if ($sub['grade']): ?>
                  <span class="badge bg-primary"><?= htmlspecialchars($sub['grade']) ?></span>
                <?php else: ?>
                  <span class="badge bg-secondary">Belum Dinilai</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="submission_view.php?id=<?= $sub['submission_id'] ?>" class="btn btn-sm btn-info text-white">Nilai / Detail</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>