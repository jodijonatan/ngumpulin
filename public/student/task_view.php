<?php
// public/student/task_view.php

// Catatan: Karena file ini ada di public/student/, path require_once harus disesuaikan.
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/header_student.php';
// Asumsi header_student.php sudah memuat $pageTitle, jika belum, tambahkan: $pageTitle = "Lihat Tugas";

if (!isset($_GET['id'])) {
  die("Invalid Task ID.");
}

$task_id = intval($_GET['id']);
$userId = $_SESSION['user']['id']; // Ambil ID pengguna dari session yang benar!

// 1. Ambil data tugas (Menggunakan Prepared Statement untuk keamanan)
$queryTask = "SELECT * FROM tasks WHERE id = ?";
$stmtTask = db_prepare_and_execute($queryTask, "i", [$task_id]);
$task = $stmtTask->get_result()->fetch_assoc();
$stmtTask->close();

if (!$task) {
  die("Task not found.");
}

// 2. Cek apakah user sudah mengumpulkan tugas ini
$queryCheck = "SELECT file_path FROM submissions WHERE task_id = ? AND user_id = ?";
$stmtCheck = db_prepare_and_execute($queryCheck, "ii", [$task_id, $userId]);
$existingSubmission = $stmtCheck->get_result()->fetch_assoc();
$stmtCheck->close();

$pageTitle = htmlspecialchars($task['title']);
?>

<div class="container mt-4">
  <h2><?= $pageTitle ?></h2>

  <p class="text-muted">Deadline: <?= date('d M Y H:i', strtotime($task['due_date'])) ?></p>

  <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>

  <hr>

  <?php if ($existingSubmission): ?>
    <div class="alert alert-warning">
      Anda telah mengumpulkan tugas ini. File Anda saat ini:
      <strong><?= basename($existingSubmission['file_path']) ?></strong>.
      Mengumpulkan lagi akan <span class="fw-bold">menimpa</span> file yang lama.
    </div>
  <?php endif; ?>

  <h4>Kumpulkan Tugas</h4>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $file = $_FILES['submission'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
      echo '<div class="alert alert-danger">Error saat upload file: ' . $file['error'] . '</div>';
    } else {
      // Ambil ekstensi file
      $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
      // Buat nama file yang unik dan aman: taskID_userID_timestamp.ext
      $filename = $task_id . '_' . $userId . '_' . time() . '.' . $fileExtension;

      // Menggunakan konstanta global UPLOAD_DIR (Pastikan UPLOAD_DIR benar di config.php)
      $targetPath = UPLOAD_DIR . $filename;

      // Pindahkan file
      if (move_uploaded_file($file['tmp_name'], $targetPath)) {

        // Query untuk INSERT/UPDATE (UPSERT)
        $comment = $_POST['comment'] ?? '';

        if ($existingSubmission) {
          // Update (Ganti file lama)
          $query = "UPDATE submissions SET file_path = ?, submitted_at = NOW() WHERE task_id = ? AND user_id = ?";
          $stmt = db_prepare_and_execute($query, "sii", [$targetPath, $task_id, $userId]);
          // Opsional: Hapus file lama dari disk sebelum update jika Anda ingin menghemat ruang
          // unlink($existingSubmission['file_path']); 
          $message = 'Tugas berhasil diperbarui!';
        } else {
          // Insert (Pengumpulan Baru)
          $query = "INSERT INTO submissions (task_id, user_id, file_path, comment) VALUES (?, ?, ?, ?)";
          $stmt = db_prepare_and_execute($query, "iiss", [$task_id, $userId, $targetPath, $comment]);
          $message = 'Tugas berhasil dikumpulkan!';
        }

        if ($stmt->affected_rows >= 0) {
          echo '<div class="alert alert-success">' . $message . '</div>';
        } else {
          echo '<div class="alert alert-danger">Gagal menyimpan data ke database.</div>';
        }
        $stmt->close();
      } else {
        echo '<div class="alert alert-danger">Gagal memindahkan file ke direktori upload.</div>';
      }
    }
  }
  ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Upload File</label>
      <input type="file" name="submission" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Catatan (Opsional)</label>
      <textarea name="comment" class="form-control"></textarea>
    </div>
    <button class="btn btn-primary">Kumpulkan</button>
  </form>
</div>

<?php
// Perbaikan path footer
require_once __DIR__ . '/../../includes/footer_student.php';
?>