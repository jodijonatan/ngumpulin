<?php
require_once __DIR__ . '/../app/config/koneksi.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = $_POST['password'];
  $name = trim($_POST['name']);
  if (empty($username) || empty($password) || empty($name)) {
    $error = 'Lengkapi semua field';
  } else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = db_prepare_and_execute('INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)', 'ssss', [$username, $hash, $name, 'student']);
    if ($stmt->affected_rows) {
      header('Location: login.php');
      exit;
    } else {
      $error = 'Gagal registrasi (username mungkin sudah ada)';
    }
  }
}
?>
<!-- HTML form -->
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Register</title>
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card p-4 shadow-sm">
          <h3 class="mb-3">Register</h3>
          <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          <form method="post">
            <div class="mb-2"><input name="username" class="form-control" placeholder="Username"></div>
            <div class="mb-2"><input name="name" class="form-control" placeholder="Full name"></div>
            <div class="mb-2"><input type="password" name="password" class="form-control" placeholder="Password"></div>
            <button class="btn btn-primary">Register</button>
            <a href="login.php" class="btn btn-link">Login</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>