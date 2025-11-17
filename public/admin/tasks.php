<?php
// public/admin/tasks.php

$requireAdmin = true;
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../includes/auth_check.php';
require_once __DIR__ . '/../../includes/header.php';

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
  $deadline = $_POST['deadline']; // Ini akan disimpan sebagai due_date

  // Menggunakan Prepared Statement untuk keamanan
  $query = "INSERT INTO tasks (title, description, due_date, created_by) VALUES (?, ?, ?, ?)";
  $stmt = db_prepare_and_execute($query, "sssi", [$title, $desc, $deadline, $adminId]);

  if ($stmt->affected_rows > 0) {
    header('Location: tasks.php');
    exit;
  } else {
    // Logging error jika gagal
    error_log("Gagal menambah tugas. Error: " . $conn->error);
    // Tampilkan pesan error sederhana ke pengguna
    $error = "Gagal menambah tugas. Silakan coba lagi.";
  }
}

// ===================================
// UPDATE TASK (FITUR BARU)
// ===================================
if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST' && $taskId) {
  $title = $_POST['title'];
  $desc = $_POST['description'];
  $deadline = $_POST['deadline'];

  // Menggunakan Prepared Statement untuk update (Wajib!)
  $query = "UPDATE tasks SET title = ?, description = ?, due_date = ? WHERE id = ?";

  // Tipe binding: string, string, string, integer
  $stmt = db_prepare_and_execute($query, "sssi", [$title, $desc, $deadline, $taskId]);

  if ($stmt->affected_rows >= 0) {
    // Jika berhasil, redirect
    header('Location: tasks.php');
    exit;
  } else {
    error_log("Gagal mengupdate tugas ID {$taskId}. Error: " . $conn->error);
    $error = "Gagal mengupdate tugas. Silakan coba lagi.";
  }
}

// ===================================
// DELETE TASK
// ===================================
if ($action === 'delete' && $taskId) {
  // Menggunakan Prepared Statement untuk keamanan!
  $query = "DELETE FROM tasks WHERE id = ?";
  db_prepare_and_execute($query, "i", [$taskId]);

  header('Location: tasks.php');
  exit;
}

// ===================================
// LOGIKA AMBIL DATA UNTUK EDIT
// ===================================
$taskData = null;
if ($action === 'edit' && $taskId) {
  $query = "SELECT id, title, description, due_date FROM tasks WHERE id = ?";
  $stmt = db_prepare_and_execute($query, "i", [$taskId]);
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $taskData = $result->fetch_assoc();
  } else {
    die("Tugas tidak ditemukan.");
  }
  $stmt->close();
}
?>

<div class="container mt-4">

  <?php if (isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($action === 'list'): ?>

    <h2><?= $pageTitle ?></h2>
    <a href="tasks.php?action=add" class="btn btn-success mb-3">Tambah Tugas</a>

    <?php
    $result = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC");
    if ($result === false) {
      die("Query gagal: " . $conn->error);
    }
    $tasks = $result->fetch_all(MYSQLI_ASSOC);
    ?>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Judul</th>
          <th>Deadline</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($tasks)): ?>
          <tr>
            <td colspan="3">Belum ada tugas yang dibuat.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($tasks as $t): ?>
            <tr>
              <td><?= htmlspecialchars($t['title']) ?></td>
              <td><?= $t['due_date'] ?></td>
              <td>
                <!-- Tombol Edit DITAMBAHKAN -->
                <a href="tasks.php?action=edit&id=<?= $t['id'] ?>" class="btn btn-warning btn-sm me-2">Edit</a>

                <a href="tasks.php?action=delete&id=<?= $t['id'] ?>"
                  class="btn btn-danger btn-sm"
                  onclick="return confirm('Hapus tugas ini?');">Hapus</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

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

    <h2><?= $formTitle ?></h2>

    <!-- FORMULIR TUGAS (Digunakan untuk ADD dan EDIT) -->
    <form method="POST" action="<?= $formAction ?>">
      <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($titleValue) ?>" required>
      </div>

      <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="description" class="form-control" rows="5"><?= htmlspecialchars($descValue) ?></textarea>
      </div>

      <div class="mb-3">
        <label>Deadline</label>
        <input type="datetime-local" name="deadline" class="form-control" value="<?= $deadlineValue ?>" required>
      </div>

      <button class="btn btn-primary">Simpan Perubahan</button>
      <a href="tasks.php" class="btn btn-secondary">Batal</a>
    </form>

  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>