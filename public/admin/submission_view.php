<?php
// public/admin/submission_view.php

$requireAdmin = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth_check.php';

$pageTitle = "Detail Pengumpulan";
$submissionId = $_GET['id'] ?? null;
$error = '';
$success = '';

// Pastikan ID pengumpulan ada
if (empty($submissionId)) {
  die("ID Pengumpulan tidak ditemukan.");
}

// ====================================
// LOGIKA SUBMIT NILAI DAN FEEDBACK (UPDATE)
// ====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $grade = $_POST['grade'] ?? null;
  $feedback = trim($_POST['feedback'] ?? '');

  // Validasi sederhana
  if (empty($grade)) {
    $error = "Nilai tidak boleh kosong.";
  } else {
    // Gunakan Prepared Statement untuk update (Wajib!)
    $queryUpdate = "UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?";
    $stmtUpdate = db_prepare_and_execute($queryUpdate, "ssi", [$grade, $feedback, $submissionId]);

    if ($stmtUpdate->affected_rows >= 0) {
      $success = "Nilai dan umpan balik berhasil disimpan!";
    } else {
      $error = "Gagal menyimpan data ke database.";
    }
    $stmtUpdate->close();
  }
}

// ====================================
// AMBIL DETAIL PENGUMPULAN
// ====================================
$queryDetail = "
    SELECT 
        s.id AS submission_id,
        s.file_path,
        s.comment,
        s.submitted_at,
        s.grade,
        s.feedback,
        t.title AS task_title,
        t.due_date,
        u.name AS student_name,
        u.username AS student_username
    FROM 
        submissions s
    JOIN 
        tasks t ON t.id = s.task_id
    JOIN
        users u ON u.id = s.user_id
    WHERE
        s.id = ?
";

$stmtDetail = db_prepare_and_execute($queryDetail, "i", [$submissionId]);
$submission = $stmtDetail->get_result()->fetch_assoc();
$stmtDetail->close();

if (!$submission) {
  die("Data pengumpulan tidak ditemukan.");
}

// Tentukan status keterlambatan
$isLate = (strtotime($submission['submitted_at']) > strtotime($submission['due_date']));
$pageTitle = "Detail Pengumpulan: " . htmlspecialchars($submission['task_title']);

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
  <h2><?= $pageTitle ?></h2>
  <a href="submissions.php" class="btn btn-sm btn-secondary mb-3">← Kembali ke Daftar Pengumpulan</a>

  <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="row">
    <div class="col-md-7">
      <div class="card mb-4">
        <div class="card-header bg-primary text-white">
          Rincian Tugas & Peserta
        </div>
        <div class="card-body">
          <p><strong>Peserta:</strong> <?= htmlspecialchars($submission['student_name']) ?> (<?= htmlspecialchars($submission['student_username']) ?>)</p>
          <p><strong>Tugas:</strong> <?= htmlspecialchars($submission['task_title']) ?></p>
          <p><strong>Batas Waktu:</strong> <?= date('d M Y H:i', strtotime($submission['due_date'])) ?></p>
          <p><strong>Waktu Submit:</strong>
            <?= date('d M Y H:i', strtotime($submission['submitted_at'])) ?>
            <span class="badge <?= $isLate ? 'bg-danger' : 'bg-success' ?>">
              <?= $isLate ? 'TERLAMBAT' : 'Tepat Waktu' ?>
            </span>
          </p>
          <p><strong>Catatan Peserta:</strong> <?= nl2br(htmlspecialchars($submission['comment'] ?? '')) ?></p>

          <hr>

          <p>
            <strong>File Dikumpulkan:</strong>
            <a href="<?= BASE_URL . $submission['file_path'] ?>"
              download
              class="btn btn-sm btn-info text-white">
              <i class="fas fa-download"></i> Unduh File
            </a>
            <small class="text-muted">(<?= basename($submission['file_path']) ?>)</small>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-5">
      <div class="card">
        <div class="card-header bg-warning text-dark">
          Berikan Nilai & Umpan Balik
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label for="grade" class="form-label">Nilai</label>
              <input type="text" class="form-control" id="grade" name="grade"
                value="<?= htmlspecialchars($submission['grade'] ?? '') ?>" required>
            </div>
            <div class="mb-3">
              <label for="feedback" class="form-label">Umpan Balik (Feedback)</label>
              <textarea class="form-control" id="feedback" name="feedback" rows="4"><?= htmlspecialchars($submission['feedback'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-success">Simpan Nilai</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once '../../includes/footer.php'; ?>