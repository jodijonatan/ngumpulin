<?php
// public/admin/dashboard.php
require_once __DIR__ . '/../../app/helpers/utils.php';

require_role('admin');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';

$pageTitle = "Dashboard Admin";

// ===================================
// 5. AMBIL SEMUA STATISTIK
// ===================================

// Total Pengguna (PERBAIKAN: Menggunakan role='student' untuk menghitung siswa)
$queryUsers = "SELECT COUNT(*) AS total FROM users WHERE role='student'";
$total_users = $conn->query($queryUsers)->fetch_assoc()['total'] ?? 0;

// Total Tugas Dibuat
$queryTasks = "SELECT COUNT(*) AS total FROM tasks";
$total_tasks = $conn->query($queryTasks)->fetch_assoc()['total'] ?? 0;

// Total Pengumpulan Tugas
$querySubmissions = "SELECT COUNT(*) AS total FROM submissions";
$total_submissions = $conn->query($querySubmissions)->fetch_assoc()['total'] ?? 0;

// Statistik Pengumpulan berdasarkan Status (Dinilai vs Belum Dinilai)
$queryGrading = "
    SELECT 
        COUNT(CASE WHEN grade IS NULL THEN 1 END) AS pending,
        COUNT(CASE WHEN grade IS NOT NULL THEN 1 END) AS graded
    FROM submissions
";
$grading_stats = $conn->query($queryGrading)->fetch_assoc();
$pending_grading = $grading_stats['pending'] ?? 0;
$graded_submissions = $grading_stats['graded'] ?? 0;

// ===================================
// STATISTIK DIAGRAM LINGKARAN (Tugas yang memiliki submission vs yang belum)
// ===================================

// 1. Hitung Tugas yang sudah memiliki setidaknya satu submission
$querySubmittedTasks = "
    SELECT COUNT(DISTINCT task_id) AS submitted_count 
    FROM submissions
";
$submitted_count = $conn->query($querySubmittedTasks)->fetch_assoc()['submitted_count'] ?? 0;

// 2. Hitung Tugas yang belum memiliki submission
$unsubmitted_count = $total_tasks - $submitted_count;

// Variabel data untuk Chart.js
$chartData = [
  'submitted' => $submitted_count,
  'unsubmitted' => $unsubmitted_count,
  'total' => $total_tasks
];

// ===================================
// 6. AMBIL DATA TUGAS TERDEKAT
// ===================================
$queryUpcomingTasks = "
    SELECT id, title, due_date
    FROM tasks
    WHERE due_date >= NOW()
    ORDER BY due_date ASC
    LIMIT 5
";
$upcomingTasks = $conn->query($queryUpcomingTasks)->fetch_all(MYSQLI_ASSOC);


// ===================================
// 7. AMBIL 5 PENGUMPULAN TERBARU
// ===================================
$queryLatestSubmissions = "
    SELECT 
        s.id AS submission_id,
        s.submitted_at,
        s.grade,
        t.title AS task_title,
        u.name AS student_name
    FROM submissions s
    JOIN tasks t ON t.id = s.task_id
    JOIN users u ON u.id = s.user_id
    ORDER BY s.submitted_at DESC
    LIMIT 5
";
$latestSubmissions = $conn->query($queryLatestSubmissions)->fetch_all(MYSQLI_ASSOC);


// 8. Load Header
require_once __DIR__ . '/../../includes/header.php';
?>

<!-- Import Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<div class="container-fluid">
  <h2>Ringkasan Dashboard</h2>

  <!-- Bagian 1: Kartu Statistik Utama -->
  <div class="row mb-4">

    <!-- Total Siswa -->
    <div class="col-md-3">
      <div class="card bg-primary text-white shadow">
        <div class="card-body">
          <h5 class="card-title">Total Siswa</h5>
          <p class="card-text fs-3"><?= number_format($total_users) ?></p>
          <a href="students.php" class="text-white small stretched-link">Lihat Detail Siswa →</a>
        </div>
      </div>
    </div>

    <!-- Total Tugas -->
    <div class="col-md-3">
      <div class="card bg-info text-white shadow">
        <div class="card-body">
          <h5 class="card-title">Total Tugas</h5>
          <p class="card-text fs-3"><?= number_format($total_tasks) ?></p>
          <a href="tasks_crud.php" class="text-white small stretched-link">Kelola Tugas →</a>
        </div>
      </div>
    </div>

    <!-- Total Pengumpulan -->
    <div class="col-md-3">
      <div class="card bg-success text-white shadow">
        <div class="card-body">
          <h5 class="card-title">Total Pengumpulan</h5>
          <p class="card-text fs-3"><?= number_format($total_submissions) ?></p>
          <a href="submissions.php" class="text-white small stretched-link">Lihat Semua Pengumpulan →</a>
        </div>
      </div>
    </div>

    <!-- Perlu Dinilai -->
    <div class="col-md-3">
      <div class="card bg-warning text-dark shadow">
        <div class="card-body">
          <h5 class="card-title">Perlu Dinilai</h5>
          <p class="card-text fs-3"><?= number_format($pending_grading) ?></p>
          <a href="submissions.php?status=pending" class="text-dark small stretched-link">Proses Penilaian →</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bagian 3: Diagram Lingkaran Tugas & Tugas Mendekati Deadline -->
  <div class="row mb-4">

    <!-- Kolom Diagram Lingkaran -->
    <div class="col-md-6">
      <div class="card shadow h-100">
        <div class="card-header bg-dark text-white">
          Distribusi Status Tugas
        </div>
        <div class="card-body d-flex flex-column justify-content-between">
          <?php if ($total_tasks > 0): ?>
            <div class="row flex-grow-1">
              <div class="col-12" style="max-height: 300px;">
                <canvas id="submissionPieChart"></canvas>
              </div>
            </div>
            <div class="row mt-3 text-center">
              <!-- Legenda Manual -->
              <div class="col-6">
                <span class="badge bg-success">Dikumpulkan: <?= $chartData['submitted'] ?> Tugas</span>
              </div>
              <div class="col-6">
                <span class="badge bg-danger">Belum Ada Pengumpulan: <?= $chartData['unsubmitted'] ?> Tugas</span>
              </div>
            </div>
          <?php else: ?>
            <div class="alert alert-info text-center mt-3">Belum ada tugas yang dibuat.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Kolom Tugas Mendekati Deadline -->
    <div class="col-md-6">
      <div class="card shadow h-100">
        <div class="card-header bg-secondary text-white">
          Tugas Mendekati Deadline (5 Terdekat)
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <?php if (empty($upcomingTasks)): ?>
              <li class="list-group-item text-center text-muted">Tidak ada tugas mendekati deadline.</li>
            <?php else: ?>
              <?php foreach ($upcomingTasks as $task): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <a href="tasks_crud.php?action=edit&id=<?= $task['id'] ?>">
                    <?= htmlspecialchars($task['title']) ?>
                  </a>
                  <span class="badge bg-danger">
                    <?= date('d M Y H:i', strtotime($task['due_date'])) ?>
                  </span>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>
  </div>

  <!-- Bagian 4: Pengumpulan Terbaru -->
  <div class="row">

    <!-- Kolom 5 Pengumpulan Terbaru (Tengah) -->
    <div class="col-md-6 offset-md-3">
      <div class="card shadow mb-4">
        <div class="card-header bg-secondary text-white">
          5 Pengumpulan Tugas Terbaru
        </div>
        <div class="card-body">
          <ul class="list-group list-group-flush">
            <?php if (empty($latestSubmissions)): ?>
              <li class="list-group-item text-center text-muted">Belum ada pengumpulan tugas.</li>
            <?php else: ?>
              <?php foreach ($latestSubmissions as $submission): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <a href="submission_view.php?id=<?= $submission['submission_id'] ?>">
                    <?= htmlspecialchars($submission['student_name']) ?>
                  </a>
                  <small class="text-muted me-auto ms-2">Mengumpulkan <span class="fw-bold"><?= htmlspecialchars($submission['task_title']) ?></span></small>

                  <span class="badge <?= $submission['grade'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= $submission['grade'] ? 'Dinilai' : 'Pending' ?>
                  </span>
                </li>
              <?php endforeach; ?>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </div>

  </div> <!-- End row -->

</div>

<script>
  <?php if ($total_tasks > 0): ?>
    // Data dari PHP
    const chartData = <?= json_encode($chartData) ?>;

    // Konteks Canvas
    const ctx = document.getElementById('submissionPieChart').getContext('2d');

    // Buat Diagram Lingkaran menggunakan Chart.js
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Sudah Mengumpulkan', 'Belum Mengumpulkan'],
        datasets: [{
          label: 'Status Tugas',
          data: [chartData.submitted, chartData.unsubmitted],
          backgroundColor: [
            '#198754', // Hijau (Success)
            '#dc3545' // Merah (Danger)
          ],
          hoverOffset: 4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                let label = context.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.parsed !== null) {
                  const value = context.parsed;
                  const total = context.dataset.data.reduce((a, b) => a + b, 0);
                  const percentage = ((value / total) * 100).toFixed(1) + '%';
                  label += value + ' Tugas (' + percentage + ')';
                }
                return label;
              }
            }
          }
        }
      }
    });
  <?php endif; ?>
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>