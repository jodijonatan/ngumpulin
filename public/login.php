<?php

require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = db_prepare_and_execute(
    'SELECT id, username, password, name, role FROM users WHERE username = ?',
    's',
    [$username]
  );

  $res = $stmt->get_result();
  $user = $res->fetch_assoc();

  if ($user && password_verify($password, $user['password'])) {

    unset($user['password']);
    $_SESSION['user'] = $user;

    // redirect
    if ($user['role'] === 'admin') {
      header('Location: /ngumpulin/public/admin/dashboard.php');
    } else {
      header('Location: /ngumpulin/public/tasks.php');
    }

    exit;
  } else {
    $error = 'Username atau password salah';
  }
}
?>

<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Login</title>
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-5">
        <div class="card p-4">
          <h3 class="mb-3">Login</h3>
          <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          <form method="post">
            <div class="mb-2"><input name="username" class="form-control" placeholder="Username"></div>
            <div class="mb-2"><input type="password" name="password" class="form-control" placeholder="Password"></div>
            <button class="btn btn-primary">Login</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>

</html>