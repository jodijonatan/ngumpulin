<?php
// public/student/dashboard.php

require_once __DIR__ . '/../../app/helpers/utils.php';

require_role('student');

require_once __DIR__ . '/../../config/koneksi.php';
require_login();

// Pastikan hanya student yang bisa akses
if ($_SESSION['user']['role'] !== 'student') {
  header('Location: ' . BASE_URL . 'index.php');
  exit;
}

$pageTitle = "Dashboard Siswa";
$userId = $_SESSION['user']['id'];

// --- 1. Ambil Data Statistik Cepat ---
// A. Total Tugas yang Tersedia
$queryTotalTasks = "SELECT COUNT(id) as total FROM tasks";
$resTotalTasks = $conn->query($queryTotalTasks);
$totalTasks = $resTotalTasks->fetch_assoc()['total'];

// B. Tugas Belum Dikumpulkan
$queryUnsubmitted = "
    SELECT COUNT(t.id) as unsubmitted_count
    FROM tasks t
    LEFT JOIN submissions s ON t.id = s.task_id AND s.user_id = ?
    WHERE s.id IS NULL
";
$stmtUnsubmitted = db_prepare_and_execute($queryUnsubmitted, "i", [$userId]);
$unsubmittedCount = $stmtUnsubmitted->get_result()->fetch_assoc()['unsubmitted_count'];
$stmtUnsubmitted->close();

// C. Nilai Rata-rata
$queryAvgGrade = "
    SELECT AVG(grade) as average_grade
    FROM submissions
    WHERE user_id = ? AND grade IS NOT NULL
";
$stmtAvgGrade = db_prepare_and_execute($queryAvgGrade, "i", [$userId]);
$averageGrade = $stmtAvgGrade->get_result()->fetch_assoc()['average_grade'];
$stmtAvgGrade->close();
$avgGradeFormatted = $averageGrade ? number_format($averageGrade, 1) : 'N/A';

// --- 2. Ambil Daftar 5 Tugas Terbaru yang Belum Dikumpulkan ---
$queryLatestTasks = "
    SELECT 
        t.id, t.title, t.due_date
    FROM 
        tasks t
    LEFT JOIN 
        submissions s ON t.id = s.task_id AND s.user_id = ?
    WHERE 
        s.id IS NULL
    ORDER BY 
        t.due_date ASC
    LIMIT 5
";
$stmtLatestTasks = db_prepare_and_execute($queryLatestTasks, "i", [$userId]);
$latestTasks = $stmtLatestTasks->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtLatestTasks->close();


require_once __DIR__ . '/../../includes/header_student.php';
?>

<style>
  /* Gaya Kustom Dashboard */
  .stat-card {
    border-radius: 1rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease;
    border: 1px solid #e9ecef;
  }

  .stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  }

  .stat-card h3 {
    font-size: 2.5rem;
    font-weight: 700;
  }

  .stat-card p {
    color: #6c757d;
    font-weight: 500;
  }

  .task-list-card {
    border-left: 4px solid #343a40;
    /* Akses Monokrom */
  }

  .list-group-item-task {
    border-left: none;
    transition: background-color 0.2s;
    padding: 1rem 1.5rem;
  }

  .list-group-item-task:hover {
    background-color: #f8f9fa;
  }
</style>

<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-5">
    <h1><i class="fas fa-tachometer-alt me-2 text-dark"></i> Selamat Datang, <?= htmlspecialchars($_SESSION['user']['name']) ?>!</h1>
  </div>

  <div class="row mb-5">

    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card stat-card bg-white p-4">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="text-dark"><?= $totalTasks ?></h3>
            <p class="mb-0">Tugas Tersedia</p>
          </div>
          <div class="fs-1 text-dark"><i class="fas fa-clipboard-list"></i></div>
        </div>
        <a href="tasks.php" class="small mt-3 text-dark fw-semibold">Lihat Semua Tugas <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>

    <div class="col-lg-4 col-md-6 mb-4">
      <div class="card stat-card p-4 <?= $unsubmittedCount > 0 ? 'border-danger' : 'border-success' ?>">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="<?= $unsubmittedCount > 0 ? 'text-danger' : 'text-success' ?>"><?= $unsubmittedCount ?></h3>
            <p class="mb-0">Tugas Belum Dikumpul</p>
          </div>
          <div class="fs-1 <?= $unsubmittedCount > 0 ? 'text-danger' : 'text-success' ?>"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
        <a href="tasks.php" class="small mt-3 text-dark fw-semibold">Segera Kumpulkan <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>

    <div class="col-lg-4 col-md-12 mb-4">
      <div class="card stat-card p-4">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h3 class="text-primary"><?= $avgGradeFormatted ?></h3>
            <p class="mb-0">Nilai Rata-rata</p>
          </div>
          <div class="fs-1 text-primary"><i class="fas fa-medal"></i></div>
        </div>
        <a href="submissions.php" class="small mt-3 text-dark fw-semibold">Lihat Riwayat Nilai <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-7 mb-4">
      <div class="card shadow-sm task-list-card h-100">
        <div class="card-header bg-dark text-white fw-bold py-3">
          <i class="fas fa-hourglass-half me-2"></i> Tugas Mendekati Tenggat (Urgent)
        </div>
        <ul class="list-group list-group-flush">
          <?php if (empty($latestTasks)): ?>
            <li class="list-group-item list-group-item-task text-center text-muted py-4">
              Semua tugas sudah dikumpulkan atau tidak ada tugas baru!
            </li>
          <?php else: ?>
            <?php foreach ($latestTasks as $task):
              $dueDate = strtotime($task['due_date']);
              $isUrgent = ($dueDate < time() + (24 * 3600)); // Kurang dari 24 jam
            ?>
              <li class="list-group-item list-group-item-task d-flex justify-content-between align-items-center">
                <div>
                  <a href="task_view.php?id=<?= $task['id'] ?>" class="text-decoration-none fw-semibold <?= $isUrgent ? 'text-danger' : 'text-dark' ?>">
                    <?= htmlspecialchars($task['title']) ?>
                  </a>
                  <small class="d-block text-muted">Batas: <?= date('d M Y H:i', $dueDate) ?></small>
                </div>
                <span class="badge <?= $isUrgent ? 'bg-danger' : 'bg-secondary' ?>">
                  <?= $isUrgent ? 'URGENT' : 'Tersisa' ?>
                </span>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>
        <div class="card-footer bg-light">
          <a href="tasks.php" class="text-dark small fw-semibold">Lihat Semua Tugas yang Belum Selesai</a>
        </div>
      </div>
    </div>

    <div class="col-lg-5 mb-4">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-light fw-bold py-3">
          <i class="fas fa-bullhorn me-2"></i> Pengumuman Terbaru
        </div>
        <div class="card-body">
          <div class="alert alert-info small">
            <h6 class="alert-heading fw-bold mb-1">Pemberitahuan: Ujian Akhir Semester</h6>
            <p class="mb-0">UAS akan dimulai pada tanggal 20 Desember. Harap persiapkan diri Anda!</p>
          </div>
          <div class="alert alert-light border small">
            <h6 class="alert-heading fw-bold mb-1">Akses Bantuan</h6>
            <p class="mb-0">Jika ada masalah, hubungi Admin melalui email: support@ngumpulin.id</p>
          </div>
          <p class="text-muted small mt-3">Tidak ada pengumuman resmi lainnya saat ini.</p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer_student.php'; ?>