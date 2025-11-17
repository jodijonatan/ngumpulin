<?php
// public/tasks.php

require_once __DIR__ . '/../app/helpers/utils.php';
require_once __DIR__ . '/../config/koneksi.php'; // Ini mendefinisikan $conn
require_login(); // Pastikan fungsi ini tersedia dan memuat config/session

// 1. Set Judul Halaman DINAMIS
$pageTitle = "Daftar Tugas";

// 2. Ambil semua tugas
// Menggunakan $conn (sesuai perbaikan sebelumnya)
$res = $conn->query('SELECT t.*, u.name as creator_name FROM tasks t JOIN users u ON u.id = t.created_by ORDER BY t.due_date ASC');

// Periksa apakah query berhasil
if ($res === false) {
  die("Query gagal: " . $conn->error);
}

$tasks = [];
while ($r = $res->fetch_assoc()) {
  $tasks[] = $r;
}

// 3. Load Header (memuat sidebar)
require_once __DIR__ . '/../includes/header.php';

?>


<div class="container-fluid">
  <h2>Daftar Tugas</h2>
  <div class="row">
    <?php if (empty($tasks)): ?>
      <div class="alert alert-info">Belum ada tugas yang tersedia saat ini.</div>
    <?php else: ?>
      <?php foreach ($tasks as $task): ?>
        <div class="col-md-6 mb-3">
          <div class="card p-3">
            <h5><?= htmlspecialchars($task['title']) ?></h5>
            <p><?= nl2br(htmlspecialchars(substr($task['description'], 0, 200))) ?>...</p>
            <p class="mb-1"><small>Due: <?= $task['due_date'] ? date('d M Y H:i', strtotime($task['due_date'])) : '-' ?></small></p>
            <p class="mb-1"><small>Dibuat oleh: <?= htmlspecialchars($task['creator_name']) ?></small></p>
            <a href="task_view.php?id=<?= $task['id'] ?>" class="btn btn-sm btn-primary">Lihat & Submit</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php
// 4. Load Footer
require_once __DIR__ . '/../includes/footer.php';
?>