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
        t.id AS task_id,       -- PERBAIKAN: ID Tugas ditambahkan
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
  // Menampilkan error hanya jika query gagal secara fatal
  die("Query riwayat pengumpulan gagal: " . $conn->error);
}

$submissions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();


require_once __DIR__ . '/../../includes/header_student.php';
?>

<div class="container-fluid">
  <h2>Riwayat Pengumpulan Tugas Anda</h2>

  <?php if (empty($submissions)): ?>
    <div class="alert alert-info" role="alert">
      Anda belum mengumpulkan tugas apa pun.
    </div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th>#</th>
            <th>Judul Tugas</th>
            <th>Waktu Submit</th>
            <th>Batas Waktu</th>
            <th>Nilai</th>
            <th>Umpan Balik</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          foreach ($submissions as $sub):
            // Pastikan task_id tersedia sebelum digunakan
            $taskId = $sub['task_id'] ?? null;
            if (!$taskId) continue; // Lewati baris jika ID tugas tidak ada

            $isLate = (strtotime($sub['submitted_at']) > strtotime($sub['due_date']));
          ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($sub['task_title']) ?></td>
              <td>
                <?= date('d M Y H:i', strtotime($sub['submitted_at'])) ?>
                <?php if ($isLate): ?>
                  <span class="badge bg-danger">Terlambat</span>
                <?php endif; ?>
              </td>
              <td><?= date('d M Y H:i', strtotime($sub['due_date'])) ?></td>
              <td>
                <?php if ($sub['grade']): ?>
                  <span class="badge bg-success"><?= htmlspecialchars($sub['grade']) ?></span>
                <?php else: ?>
                  <span class="badge bg-secondary">Belum Dinilai</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars(substr($sub['feedback'] ?? '', 0, 50)) ?>...</td>
              <td>
                <!-- PERBAIKAN: Menggunakan path relatif 'task_view.php' karena berada di folder yang sama -->
                <a href="task_view.php?id=<?= $taskId ?>" class="btn btn-sm btn-info text-white">Lihat Detail</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer_student.php'; ?>