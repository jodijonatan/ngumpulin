<?php
require_once '../../config/config.php';
require_once '../../config/database.php';
require_once '../../includes/auth_check.php';
require_once '../../includes/header.php';
require_once '../../includes/navbar_admin.php';

$action = $_GET['action'] ?? 'list';

// CREATE TASK
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = $_POST['title'];
  $desc = $_POST['description'];
  $deadline = $_POST['deadline'];

  $conn->query("INSERT INTO tasks (title, description, deadline) VALUES ('$title', '$desc', '$deadline')");
  header('Location: tasks_crud.php');
  exit;
}

// DELETE TASK
if ($action === 'delete' && isset($_GET['id'])) {
  $id = intval($_GET['id']);
  $conn->query("DELETE FROM tasks WHERE id=$id");
  header('Location: tasks_crud.php');
  exit;
}
?>

<div class="container mt-4">

  <?php if ($action === 'list'): ?>

    <h2>Manajemen Tugas</h2>
    <a href="tasks_crud.php?action=add" class="btn btn-success mb-3">Tambah Tugas</a>

    <?php $tasks = $conn->query("SELECT * FROM tasks ORDER BY created_at DESC"); ?>

    <table class="table table-striped">
      <thead>
        <tr>
          <th>Judul</th>
          <th>Deadline</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($t = $tasks->fetch_assoc()): ?>
          <tr>
            <td><?= htmlspecialchars($t['title']) ?></td>
            <td><?= $t['deadline'] ?></td>
            <td>
              <a href="tasks_crud.php?action=delete&id=<?= $t['id'] ?>"
                class="btn btn-danger btn-sm"
                onclick="return confirm('Hapus tugas ini?');">Hapus</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>

  <?php elseif ($action === 'add'): ?>

    <h2>Tambah Tugas</h2>

    <form method="POST" action="tasks_crud.php?action=create">
      <div class="mb-3">
        <label>Judul</label>
        <input type="text" name="title" class="form-control" required>
      </div>

      <div class="mb-3">
        <label>Deskripsi</label>
        <textarea name="description" class="form-control" rows="5"></textarea>
      </div>

      <div class="mb-3">
        <label>Deadline</label>
        <input type="datetime-local" name="deadline" class="form-control" required>
      </div>

      <button class="btn btn-primary">Simpan</button>
    </form>

  <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>