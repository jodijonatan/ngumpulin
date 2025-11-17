<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/navbar_member.php';

if (!isset($_GET['id'])) {
  die("Invalid Task");
}

$task_id = intval($_GET['id']);
$task = $conn->query("SELECT * FROM tasks WHERE id=$task_id")->fetch_assoc();

if (!$task) {
  die("Task not found");
}
?>

<div class="container mt-4">
  <h2><?= htmlspecialchars($task['title']) ?></h2>
  <p class="text-muted">Deadline: <?= $task['deadline'] ?></p>

  <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>

  <hr>
  <h4>Kumpulkan Tugas</h4>

  <?php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $file = $_FILES['submission'];
    $filename = time() . '_' . basename($file['name']);
    $path = '../uploads/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $path)) {
      $conn->query("INSERT INTO submissions (task_id, user_id, filename) VALUES ($task_id, $user_id, '$filename')");
      echo '<div class="alert alert-success">Tugas berhasil dikumpulkan!</div>';
    } else {
      echo '<div class="alert alert-danger">Gagal upload file.</div>';
    }
  }
  ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
      <label class="form-label">Upload File</label>
      <input type="file" name="submission" class="form-control" required>
    </div>
    <button class="btn btn-primary">Kumpulkan</button>
  </form>
</div>

<?php require_once '../includes/footer.php'; ?>