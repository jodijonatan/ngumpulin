<?php
require_once __DIR__ . '/../app/helpers/utils.php';
require_login();
require_once __DIR__ . '/../app/config/koneksi.php';


// Ambil semua tugas
$res = $mysqli->query('SELECT t.*, u.name as creator_name FROM tasks t JOIN users u ON u.id = t.created_by ORDER BY t.due_date ASC');
$tasks = [];
while ($r = $res->fetch_assoc()) $tasks[] = $r;
?>


<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Daftar Tugas</title>
</head>

<body>
  <?php include 'partials/navbar.php'; ?>
  <div class="container py-4">
    <h2>Daftar Tugas</h2>
    <div class="row">
      <?php foreach ($tasks as $task): ?>
        <div class="col-md-6 mb-3">
          <div class="card p-3">
            <h5><?= htmlspecialchars($task['title']) ?></h5>
            <p><?= nl2br(htmlspecialchars(substr($task['description'], 0, 200))) ?>...</p>
            <p class="mb-1"><small>Due: <?= $task['due_date'] ? date('d M Y H:i', strtotime($task['due_date'])) : '-' ?></small></p>
            <a href="task_view.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-primary">Lihat & Submit</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</body>

</html>