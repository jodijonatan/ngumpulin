<?php
// public/student/task_view.php

// Catatan: Karena file ini ada di public/student/, path require_once harus disesuaikan.
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// --- FUNGSI BANTU FRONTEND ---
/**
 * Menentukan status deadline dan badge color.
 * @param string $dueDate Tanggal tenggat waktu
 * @return array {class: string, text: string, is_overdue: bool}
 */
function getDeadlineStatus($dueDate)
{
  $dueTimestamp = strtotime($dueDate);
  $now = time();

  if ($dueTimestamp < $now) {
    return ['class' => 'bg-danger text-white', 'text' => 'Tenggat Sudah Lewat', 'is_overdue' => true];
  } elseif ($dueTimestamp < $now + (3 * 24 * 3600)) { // Kurang dari 72 jam
    return ['class' => 'bg-warning text-dark', 'text' => 'Mendekati Tenggat', 'is_overdue' => false];
  } else {
    // Menggunakan warna abu-abu/biru gelap yang netral untuk tema monokrom
    return ['class' => 'bg-secondary text-white', 'text' => 'Tersedia', 'is_overdue' => false];
  }
}
// -----------------------------

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
$queryCheck = "SELECT file_path, submitted_at FROM submissions WHERE task_id = ? AND user_id = ?";
$stmtCheck = db_prepare_and_execute($queryCheck, "ii", [$task_id, $userId]);
$existingSubmission = $stmtCheck->get_result()->fetch_assoc();
$stmtCheck->close();

$pageTitle = htmlspecialchars($task['title']);

// Dapatkan status deadline
$deadlineStatus = getDeadlineStatus($task['due_date']);

require_once __DIR__ . '/../../includes/header_student.php';
?>

<style>
  /* Gaya Kustom untuk halaman detail tugas */
  .task-header {
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 1rem;
    margin-bottom: 1.5rem;
  }

  .deadline-info {
    font-size: 1.1rem;
    font-weight: 600;
  }

  .submission-card {
    border-left: 5px solid #343a40;
    /* Akses Monokrom */
  }

  .submission-card-submitted {
    border-left: 5px solid #28a745;
    /* Hijau untuk status berhasil kumpul */
    box-shadow: 0 4px 8px rgba(40, 167, 69, 0.1);
  }

  .btn-submit {
    background-color: #343a40;
    border-color: #343a40;
    font-weight: 600;
    transition: background-color 0.3s ease;
  }

  .btn-submit:hover {
    background-color: #6c757d;
    border-color: #6c757d;
  }
</style>

<div class="container py-4">
  <div class="task-header d-flex justify-content-between align-items-center">
    <h1>
      <i class="fas fa-book-open me-2 text-dark"></i> <?= $pageTitle ?>
    </h1>
  </div>

  <div class="row">
    <div class="col-lg-7 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <h5 class="fw-bold mb-3"><i class="fas fa-info-circle me-1"></i> Deskripsi Tugas</h5>
          <div class="p-3 bg-light rounded mb-4">
            <p class="text-muted small mb-0">
              <?= nl2br(htmlspecialchars($task['description'])) ?>
            </p>
          </div>

          <div class="d-flex justify-content-between align-items-center deadline-info p-3 border rounded <?= $deadlineStatus['is_overdue'] ? 'bg-light text-danger border-danger' : 'bg-white text-dark border-secondary' ?>">
            <span><i class="far fa-clock me-2"></i> Tenggat Waktu:</span>
            <span class="badge <?= $deadlineStatus['class'] ?> py-2 px-3">
              <?= date('d M Y H:i', strtotime($task['due_date'])) ?> (<?= $deadlineStatus['text'] ?>)
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5 mb-4">
      <div class="card shadow-lg h-100 submission-card <?= $existingSubmission ? 'submission-card-submitted' : '' ?>">
        <div class="card-body">
          <h4 class="card-title fw-bold mb-4">
            <i class="fas fa-upload me-2"></i> Area Pengumpulan
          </h4>

          <?php
          // Menampilkan pesan POST (Success/Error)
          if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ... (kode PHP logic POST di sini)
            $file = $_FILES['submission'];

            if ($file['error'] !== UPLOAD_ERR_OK) {
              echo '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i> Error saat upload file: ' . $file['error'] . '</div>';
            } else {
              $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
              $filename = $task_id . '_' . $userId . '_' . time() . '.' . $fileExtension;
              $targetPath = UPLOAD_DIR . $filename;

              if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $comment = $_POST['comment'] ?? '';

                if ($existingSubmission) {
                  $query = "UPDATE submissions SET file_path = ?, submitted_at = NOW() WHERE task_id = ? AND user_id = ?";
                  $stmt = db_prepare_and_execute($query, "sii", [$targetPath, $task_id, $userId]);
                  $message = 'Tugas berhasil **diperbarui**!';
                } else {
                  $query = "INSERT INTO submissions (task_id, user_id, file_path, comment) VALUES (?, ?, ?, ?)";
                  $stmt = db_prepare_and_execute($query, "iiss", [$task_id, $userId, $targetPath, $comment]);
                  $message = 'Tugas berhasil **dikumpulkan**!';
                }

                if ($stmt->affected_rows >= 0) {
                  echo '<div class="alert alert-success"><i class="fas fa-check-circle me-1"></i> ' . $message . '</div>';
                  // Refresh data pengumpulan setelah sukses
                  $existingSubmission = ['file_path' => $targetPath, 'submitted_at' => date('Y-m-d H:i:s')];
                } else {
                  echo '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i> Gagal menyimpan data ke database.</div>';
                }
                $stmt->close();
              } else {
                echo '<div class="alert alert-danger"><i class="fas fa-times-circle me-1"></i> Gagal memindahkan file ke direktori upload.</div>';
              }
            }
          }
          ?>

          <?php if ($existingSubmission): ?>
            <div class="alert alert-success submission-card-submitted border-0 shadow-sm">
              <h6 class="alert-heading fw-bold"><i class="fas fa-check-double me-1"></i> Tugas Sudah Dikumpulkan!</h6>
              <p class="mb-1 small">
                File Anda saat ini: <strong><?= basename($existingSubmission['file_path']) ?></strong>
              </p>
              <p class="mb-1 small">
                Terakhir dikumpulkan: <strong><?= date('d M Y H:i', strtotime($existingSubmission['submitted_at'])) ?></strong>
              </p>
              <hr class="my-2">
              <p class="mb-0 small text-danger">
                Mengunggah file baru akan **menimpa** file yang lama.
              </p>
            </div>
          <?php endif; ?>

          <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
              <label for="submissionFile" class="form-label fw-semibold"><i class="fas fa-file-upload me-1"></i> Pilih File Tugas</label>
              <input type="file" name="submission" id="submissionFile" class="form-control" required>
              <div class="form-text">Maksimal ukuran file disesuaikan dengan pengaturan server.</div>
            </div>
            <div class="mb-3">
              <label for="commentArea" class="form-label fw-semibold"><i class="far fa-comment-dots me-1"></i> Catatan (Opsional)</label>
              <textarea name="comment" id="commentArea" class="form-control" rows="3" placeholder="Tuliskan catatan untuk guru/dosen Anda..."></textarea>
            </div>

            <button class="btn btn-submit w-100" type="submit"
              <?= $deadlineStatus['is_overdue'] ? 'disabled' : '' ?>>
              <i class="fas fa-paper-plane me-1"></i>
              <?= $existingSubmission ? 'Perbarui Pengumpulan' : 'Kumpulkan Tugas' ?>
            </button>

            <?php if ($deadlineStatus['is_overdue']): ?>
              <p class="text-danger mt-2 text-center small fw-bold">Pengumpulan sudah ditutup.</p>
            <?php endif; ?>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
// Perbaikan path footer
require_once __DIR__ . '/../../includes/footer_student.php';
?>