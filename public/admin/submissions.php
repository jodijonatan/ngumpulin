<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../includes/navbar_admin.php';

$submissions = $conn->query("
    SELECT s.*, u.name, t.title
    FROM submissions s
    JOIN users u ON s.user_id = u.id
    JOIN tasks t ON s.task_id = t.id
    ORDER BY s.created_at DESC
");
?>

<div class="container mt-4">
  <h2>Semua Pengumpulan Tugas</h2>

  <table class="table table-bordered mt-4">
    <thead>
      <tr>
        <th>Nama</th>
        <th>Tugas</th>
        <th>File</th>
        <th>Tanggal</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = $submissions->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><?= htmlspecialchars($row['title']) ?></td>
          <td>
            <a href="../../uploads/<?= $row['filename'] ?>"
              class="btn btn-sm btn-primary" download>
              Download
            </a>
          </td>
          <td><?= $row['created_at'] ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php require_once '../../includes/footer.php'; ?>