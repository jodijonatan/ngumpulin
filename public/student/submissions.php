<?php
// public/student/submissions.php

require_once __DIR__ . '/../../app/helpers/utils.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_login();

// Pastikan hanya student yang bisa akses (Meskipun sudah diurus oleh require_login/index.php)
if ($_SESSION['user']['role'] !== 'student') {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}

$pageTitle = "Riwayat Pengumpulan";
$userId = $_SESSION['user']['id'];

// Ambil semua pengumpulan (submissions) yang dibuat oleh user ini
$query = "
    SELECT 
        s.id AS submission_id,
        s.submitted_at,
        s.grade,
        s.feedback,
        t.id AS task_id, 
        t.title AS task_title,
        t.due_date
    FROM 
        submissions s
    JOIN 
        tasks t ON t.id = s.task_id
    WHERE 
        s.user_id = ?
    ORDER BY 
        s.submitted_at DESC
";

$stmt = db_prepare_and_execute($query, "i", [$userId]);
$result = $stmt->get_result();

if ($result === false) {
  die("Query riwayat pengumpulan gagal: " . $conn->error);
}

$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// --- FUNGSI BANTU FRONTEND ---
function getGradeBadgeClass($grade)
{
  if (!$grade) return 'bg-secondary';
  $g = (float)$grade;
  if ($g >= 80) return 'bg-success';
  if ($g >= 60) return 'bg-warning text-dark';
  return 'bg-danger';
}
// -----------------------------

require_once __DIR__ . '/../../includes/header_student.php';
?>

<style>
  /* Gaya Kustom untuk riwayat pengumpulan */
  .submission-item {
    transition: all 0.3s ease;
    border-radius: 0.5rem;
    border-left: 5px solid #343a40;
    /* Akses monokrom */
    cursor: pointer;
  }

  .submission-item:hover {
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
  }

  .submission-graded {
    border-left-color: #28a745 !important;
    /* Hijau untuk yang sudah dinilai */
  }

  .grade-badge {
    font-size: 1.1em;
    padding: 0.5em 1em;
    min-width: 60px;
    text-align: center;
  }
</style>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-history me-2 text-dark"></i> Riwayat Pengumpulan Tugas Anda</h2>
  </div>

  <?php if (empty($submissions)): ?>
    <div class="col-12">
      <div class="alert alert-info text-center py-4">
        <i class="fas fa-inbox fa-3x mb-3"></i>
        <h4>Anda belum mengumpulkan tugas apa pun.</h4>
        <p>Silakan segera cek halaman Daftar Tugas Anda.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="row">
      <?php foreach ($submissions as $sub):
        $taskId = $sub['task_id'] ?? null;
        if (!$taskId) continue;

        $isLate = (strtotime($sub['submitted_at']) > strtotime($sub['due_date']));
        $isGraded = !empty($sub['grade']);
        $gradeBadgeClass = getGradeBadgeClass($sub['grade']);
        $feedbackPreview = htmlspecialchars(substr($sub['feedback'] ?? 'Belum ada umpan balik.', 0, 80));
      ?>
        <div class="col-lg-12 mb-3">
          <div class="card shadow-sm submission-item <?= $isGraded ? 'submission-graded' : '' ?>">
            <div class="card-body p-3">
              <div class="row align-items-center">

                <div class="col-md-5">
                  <h5 class="fw-bold text-dark mb-1">
                    <i class="far fa-clipboard me-2"></i> <?= htmlspecialchars($sub['task_title']) ?>
                  </h5>
                  <small class="text-muted">
                    Submitted:
                    <span class="fw-semibold">
                      <?= date('d M Y H:i', strtotime($sub['submitted_at'])) ?>
                    </span>
                    <?php if ($isLate): ?>
                      <span class="badge bg-danger ms-2">Terlambat</span>
                    <?php endif; ?>
                  </small>
                </div>

                <div class="col-md-4 text-center">
                  <span class="badge grade-badge <?= $gradeBadgeClass ?>">
                    <?= $isGraded ? htmlspecialchars($sub['grade']) : 'Belum Dinilai' ?>
                  </span>
                  <small class="d-block text-secondary mt-1">
                    <i class="far fa-clock me-1"></i> Due: <?= date('d M Y H:i', strtotime($sub['due_date'])) ?>
                  </small>
                </div>

                <div class="col-md-3 text-end">
                  <small class="d-block text-truncate mb-2" title="<?= $sub['feedback'] ?? 'Belum ada umpan balik.' ?>">
                    <i class="fas fa-comment-dots me-1"></i> <?= $feedbackPreview ?>
                  </small>
                  <a href="task_view.php?id=<?= $taskId ?>" class="btn btn-sm btn-dark w-100">
                    <i class="fas fa-eye me-1"></i> Lihat Detail
                  </a>
                </div>

              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer_student.php'; ?>