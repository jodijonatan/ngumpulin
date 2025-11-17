<?php
// public/admin/tasks.php

$requireAdmin = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth_check.php';

// Fungsi db_prepare_and_execute() diasumsikan sudah ada di config/koneksi.php
// Atau file lain yang di-include sebelum ini.

// Ambil ID Admin yang sedang login
$adminId = $_SESSION['user']['id'];
$pageTitle = "Manajemen Tugas";

$action = $_GET['action'] ?? 'list';
$taskId = isset($_GET['id']) ? intval($_GET['id']) : null;

// ===================================
// CREATE TASK
// ===================================
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $desc = $_POST['description'];
  $deadline = $_POST['deadline']; // YYYY-MM-DDTHH:MM

  // Ubah format deadline ke format datetime MySQL (YYYY-MM-DD HH:MM:SS)
  // Walaupun PHP/MySQL cukup fleksibel, lebih baik konsisten.
  $deadlineFormatted = str_replace('T', ' ', $deadline) . ':00';

  // Menggunakan Prepared Statement untuk keamanan
  $query = "INSERT INTO tasks (title, description, due_date, created_by) VALUES (?, ?, ?, ?)";
  // Pastikan Anda memiliki fungsi db_prepare_and_execute yang menangani $conn
  // Contoh sederhana:
  if (function_exists('db_prepare_and_execute')) {
    $stmt = db_prepare_and_execute($query, "sssi", [$title, $desc, $deadlineFormatted, $adminId]);
  } else {
    // Fallback jika fungsi tidak ditemukan (HANYA untuk contoh)
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $title, $desc, $deadlineFormatted, $adminId);
    $stmt->execute();
  }

  if ($stmt->affected_rows > 0) {
    header('Location: tasks.php');
    exit;
  } else {
    error_log("Gagal menambah tugas. Error: " . $conn->error);
    $error = "Gagal menambah tugas. Silakan coba lagi.";
  }
  $stmt->close();
}

// ===================================
// UPDATE TASK
// ===================================
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST' && $taskId) {
  $title = $_POST['title'];
  $desc = $_POST['description'];
  $deadline = $_POST['deadline'];
  $deadlineFormatted = str_replace('T', ' ', $deadline) . ':00';

  // Menggunakan Prepared Statement untuk update
  $query = "UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ?";

  if (function_exists('db_prepare_and_execute')) {
    $stmt = db_prepare_and_execute($query, "sssi", [$title, $desc, $deadlineFormatted, $taskId]);
  } else {
    // Fallback
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $title, $desc, $deadlineFormatted, $taskId);
    $stmt->execute();
  }

  if ($stmt->affected_rows >= 0) {
    header('Location: tasks.php');
    exit;
  } else {
    error_log("Gagal mengupdate tugas ID {$taskId}. Error: " . $conn->error);
    $error = "Gagal mengupdate tugas. Silakan coba lagi.";
  }
  $stmt->close();
}

// ===================================
// DELETE TASK
// ===================================
if ($action === 'delete' && $taskId) {
  // Menggunakan Prepared Statement untuk keamanan!
  $query = "DELETE FROM tasks WHERE id = ?";
  if (function_exists('db_prepare_and_execute')) {
    db_prepare_and_execute($query, "i", [$taskId]);
  } else {
    // Fallback
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $stmt->close();
  }

  header('Location: tasks.php');
  exit;
}

// ===================================
// LOGIKA AMBIL DATA UNTUK EDIT
// ===================================
$taskData = null;
if ($action === 'edit' && $taskId) {
  $query = "SELECT id, title, description, due_date FROM tasks WHERE id = ?";

  // Perlu menggunakan koneksi manual jika db_prepare_and_execute tidak mengembalikan statement
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $taskId);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $taskData = $result->fetch_assoc();
  } else {
    die("Tugas tidak ditemukan.");
  }
  $stmt->close();
}

require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container py-4">

  <?php if (isset($error)): ?>
    <div class="alert alert-danger d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i> <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($action === 'list'): ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2 class="mb-0 text-primary"><i class="fas fa-clipboard-list me-2"></i> Manajemen Tugas</h2>
      <a href="tasks.php?action=add" class="btn btn-primary shadow-sm">
        <i class="fas fa-plus-circle me-1"></i> Tambah Tugas Baru
      </a>
    </div>

    <?php
    $result = $conn->query("SELECT id, title, description, due_date FROM tasks ORDER BY created_at DESC");
    if ($result === false) {
      die("Query gagal: " . $conn->error);
    }
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
    ?>

    <div class="card shadow-lg border-0">
      <div class="card-header bg-light border-bottom">
        <h5 class="mb-0 text-dark"><i class="fas fa-list-ul me-1"></i> Daftar Semua Tugas Aktif</h5>
      </div>
      <div class="card-body p-0">

        <?php if (empty($tasks)): ?>
          <div class="alert alert-info border-0 m-3" role="alert">
            <i class="fas fa-info-circle me-2"></i> Belum ada tugas yang dibuat. Klik "Tambah Tugas Baru" di atas.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-secondary">
                <tr>
                  <th scope="col" style="width: 5%;">#</th>
                  <th scope="col" style="width: 45%;">Judul Tugas</th>
                  <th scope="col" style="width: 25%;">Batas Waktu</th>
                  <th scope="col" style="width: 25%;">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1;
                foreach ($tasks as $t):
                  $isOverdue = (strtotime($t['due_date']) < time());
                  $deadlineClass = $isOverdue ? 'text-danger fw-bold' : 'text-success fw-bold';
                  $iconClass = $isOverdue ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
                  $deadlineText = date('d M Y H:i', strtotime($t['due_date']));
                ?>
                  <tr>
                    <td><?= $no++ ?></td>
                    <td>
                      <div class="fw-semibold text-primary"><?= htmlspecialchars($t['title']) ?></div>
                      <p class="text-muted small mb-0 mt-1" style="max-height: 40px; overflow: hidden;"><?= htmlspecialchars(substr($t['description'], 0, 100)) ?>...</p>
                    </td>
                    <td>
                      <span class="<?= $deadlineClass ?>">
                        <i class="<?= $iconClass ?> me-1"></i> <?= $deadlineText ?>
                      </span>
                      <?php if ($isOverdue): ?>
                        <span class="badge bg-danger ms-2">Terlewat</span>
                      <?php else: ?>
                        <span class="badge bg-secondary ms-2">Aktif</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <a href="tasks.php?action=edit&id=<?= $t['id'] ?>" class="btn btn-sm btn-warning me-2 text-dark" title="Edit Tugas">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                      <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $t['id'] ?>" title="Hapus Tugas">
                        <i class="fas fa-trash-alt"></i> Hapus
                      </button>

                      <div class="modal fade" id="deleteModal<?= $t['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $t['id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                              <h5 class="modal-title" id="deleteModalLabel<?= $t['id'] ?>">Konfirmasi Hapus</h5>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              Apakah Anda yakin ingin menghapus tugas **"<?= htmlspecialchars($t['title']) ?>"**? Aksi ini tidak dapat dibatalkan.
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <a href="tasks.php?action=delete&id=<?= $t['id'] ?>" class="btn btn-danger">Ya, Hapus Sekarang</a>
                            </div>
                          </div>
                        </div>
                      </div>

                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php elseif ($action === 'add' || $action === 'edit'):

    // Tentukan Judul dan Action URL berdasarkan mode (Add atau Edit)
    $formTitle = ($action === 'add') ? 'Tambah Tugas Baru' : 'Edit Tugas: ' . htmlspecialchars($taskData['title'] ?? '');
    $formAction = ($action === 'add') ? 'tasks.php?action=create' : 'tasks.php?action=edit&id=' . $taskId;

    // Ambil data untuk Edit, jika ada
    $titleValue = $taskData['title'] ?? '';
    $descValue = $taskData['description'] ?? '';

    // Format due_date dari DB (YYYY-MM-DD HH:MM:SS) ke format input HTML (YYYY-MM-DDTHH:MM)
    $deadlineValue = isset($taskData['due_date']) ? date('Y-m-d\TH:i', strtotime($taskData['due_date'])) : '';

  ?>

    <div class="card shadow-lg border-0">
      <div class="card-header bg-info text-white p-3">
        <h3 class="mb-0"><i class="fas fa-pencil-alt me-2"></i> <?= $formTitle ?></h3>
      </div>
      <div class="card-body p-4">
        <form method="POST" action="<?= $formAction ?>">
          <div class="mb-3">
            <label for="title" class="form-label fw-semibold">Judul Tugas</label>
            <input type="text" id="title" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($titleValue) ?>" placeholder="Contoh: Proyek Akhir Basis Data" required>
          </div>

          <div class="mb-3">
            <label for="description" class="form-label fw-semibold">Deskripsi Tugas</label>
            <textarea id="description" name="description" class="form-control" rows="6" placeholder="Jelaskan detail tugas, tujuan, dan kriteria penilaian di sini..."><?= htmlspecialchars($descValue) ?></textarea>
          </div>

          <div class="mb-4">
            <label for="deadline" class="form-label fw-semibold">Batas Waktu Pengumpulan (Deadline)</label>
            <input type="datetime-local" id="deadline" name="deadline" class="form-control form-control-lg" value="<?= $deadlineValue ?>" required>
            <div class="form-text">Masukkan tanggal dan waktu batas akhir tugas (YYYY-MM-DD HH:MM).</div>
          </div>

          <div class="d-flex justify-content-end pt-3 border-top">
            <a href="tasks.php" class="btn btn-secondary me-2">
              <i class="fas fa-times-circle me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-1"></i> Simpan Tugas
            </button>
          </div>
        </form>
      </div>
    </div>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>