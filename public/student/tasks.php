<?php
// public/tasks.php

require_once __DIR__ . '../../../app/helpers/utils.php';
require_once __DIR__ . '/../../config/koneksi.php'; // Ini mendefinisikan $conn
require_login(); // Pastikan fungsi ini tersedia dan memuat config/session

// 1. Set Judul Halaman DINAMIS
$pageTitle = "Daftar Tugas";

// --- FUNGSI BANTU FRONTEND ---
/**
 * Menentukan status tugas (badge color) berdasarkan due_date.
 * @param string $dueDate Tanggal tenggat waktu
 * @return array {class: string, text: string}
 */
function getTaskStatus($dueDate)
{
  if (empty($dueDate)) {
    return ['class' => 'bg-secondary', 'text' => 'Tidak Ditentukan'];
  }
  $dueTimestamp = strtotime($dueDate);
  $now = time();

  if ($dueTimestamp < $now) {
    return ['class' => 'bg-danger text-white', 'text' => 'Terlambat'];
  } elseif ($dueTimestamp < $now + (2 * 24 * 3600)) { // Kurang dari 48 jam
    return ['class' => 'bg-warning text-dark', 'text' => 'Mendekati'];
  } else {
    return ['class' => 'bg-success text-white', 'text' => 'Tersedia'];
  }
}
// -----------------------------

// 2. Ambil semua tugas
$res = $conn->query('SELECT t.*, u.name as creator_name FROM tasks t JOIN users u ON u.id = t.created_by ORDER BY t.due_date ASC');

if ($res === false) {
  die("Query gagal: " . $conn->error);
}

$tasks = [];
while ($r = $res->fetch_assoc()) {
  $tasks[] = $r;
}

// 3. Load Header (memuat sidebar)
require_once __DIR__ . '/../../includes/header_student.php';
?>

<style>
  /* Gaya Kustom untuk tampilan yang lebih menarik */
  .task-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-radius: 0.75rem;
    /* Sudut lebih bulat */
    border: none;
    overflow: hidden;
  }

  .task-card:hover {
    transform: translateY(-5px);
    /* Efek mengangkat saat di-hover */
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  }

  .task-card-header {
    padding: 1.5rem 1.5rem 0.5rem 1.5rem;
  }

  .task-card-body {
    padding: 0.5rem 1.5rem 1.5rem 1.5rem;
  }

  .badge-status {
    font-size: 0.8em;
    padding: 0.4em 0.8em;
    border-radius: 0.5rem;
    font-weight: 600;
  }
</style>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-list-check me-2 text-primary"></i> Daftar Tugas</h2>
  </div>

  <div class="row">
    <?php if (empty($tasks)): ?>
      <div class="col-12">
        <div class="alert alert-info text-center py-4">
          <i class="fas fa-inbox fa-3x mb-3"></i>
          <h4>Belum ada tugas yang tersedia saat ini.</h4>
          <p>Silakan tunggu pengumuman tugas berikutnya dari dosen/guru Anda.</p>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($tasks as $task):
        $status = getTaskStatus($task['due_date']);
        $formattedDueDate = $task['due_date'] ? date('d M Y, H:i', strtotime($task['due_date'])) : '-';
      ?>
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="card task-card shadow-sm">

            <div class="task-card-header">
              <div class="d-flex justify-content-between align-items-start">
                <h5 class="card-title text-dark fw-bold me-3">
                  <?= htmlspecialchars($task['title']) ?>
                </h5>
                <span class="badge badge-status <?= $status['class'] ?>">
                  <?= $status['text'] ?>
                </span>
              </div>
            </div>

            <div class="task-card-body">
              <p class="text-muted small mb-3">
                <?= nl2br(htmlspecialchars(substr($task['description'], 0, 100))) ?><?php if (strlen($task['description']) > 100) echo '...' ?>
              </p>

              <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1 border-0">
                  <small class="text-secondary"><i class="far fa-calendar-alt me-1"></i> Tenggat Waktu:</small>
                  <small class="fw-semibold <?= ($status['class'] === 'bg-danger text-white' || $status['class'] === 'bg-warning text-dark') ? 'text-danger' : 'text-dark' ?>">
                    <?= $formattedDueDate ?>
                  </small>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-1 border-0">
                  <small class="text-secondary"><i class="fas fa-user-tie me-1"></i> Dibuat Oleh:</small>
                  <small class="text-dark"><?= htmlspecialchars($task['creator_name']) ?></small>
                </li>
              </ul>

              <a href="task_view.php?id=<?= $task['id'] ?>" class="btn btn-primary w-100 mt-2">
                <i class="fas fa-arrow-alt-circle-right me-1"></i> Lihat Detail & Submit
              </a>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<?php
// 4. Load Footer
require_once __DIR__ . '/../../includes/footer_student.php';
?>