<?php

require_once __DIR__ . '/../config/koneksi.php';

// ... (kode PHP Anda untuk POST dan logika login tetap sama)
$base_url = '/ngumpulin/';

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
      header('Location: ' . $base_url . 'admin/dashboard');
    } else {
      header('Location: ' . $base_url . 'student/tasks');
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Login - Ngumpulin</title>
  <style>
    /* Gaya Kustom untuk Tampilan Monokromatik (Hitam, Putih, Abu-abu) */
    body {
      background-color: #f0f0f0;
      /* Abu-abu terang sebagai latar belakang */
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .login-card {
      border-radius: 1rem;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
      /* Bayangan abu-abu halus */
      border: 1px solid #ddd;
      /* Border abu-abu tipis */
      padding: 3rem;
      background-color: white;
    }

    .login-card-header {
      color: #343a40;
      /* Hitam gelap untuk judul */
      font-weight: 700;
      margin-bottom: 2rem;
      text-align: center;
    }

    .form-control {
      border-radius: 0.5rem;
      padding: 0.75rem 1rem;
      border: 1px solid #ced4da;
      /* Border abu-abu standar */
    }

    .input-group-text {
      border-radius: 0.5rem 0 0 0.5rem;
      background-color: #e9ecef;
      /* Abu-abu muda untuk background ikon */
      color: #6c757d;
      /* Abu-abu gelap untuk ikon */
      border-right: none;
      border: 1px solid #ced4da;
    }

    .input-group:focus-within .input-group-text {
      border-color: #6c757d;
      /* Warna border ikon saat fokus */
    }

    .btn-primary {
      background-color: #343a40;
      /* Warna tombol utama: Hitam gelap */
      border-color: #343a40;
      border-radius: 0.5rem;
      font-weight: 600;
      padding: 0.75rem 1.5rem;
      width: 100%;
      transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
      background-color: #6c757d;
      /* Abu-abu gelap saat hover */
      border-color: #6c757d;
    }

    .alert-danger {
      background-color: #f8d7da;
      /* Merah muda lembut (diperlukan untuk error) */
      color: #721c24;
      /* Teks merah gelap */
      border-color: #f5c6cb;
    }

    .alert-danger .fa-exclamation-circle {
      color: #dc3545;
      /* Ikon merah */
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card login-card">
          <h2 class="login-card-header">
            <i class="fas fa-graduation-cap me-2"></i> Ngumpulin
          </h2>
          <p class="text-center text-muted mb-4">Masuk untuk melanjutkan ke sistem.</p>

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-circle me-2"></i> <?= htmlspecialchars($error) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <form method="post">
            <div class="input-group mb-3">
              <span class="input-group-text"><i class="fas fa-user"></i></span>
              <input name="username" class="form-control" placeholder="Username atau Email" required
                value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
            </div>

            <div class="input-group mb-4">
              <span class="input-group-text"><i class="fas fa-lock"></i></span>
              <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
            <p class="mt-4 text-center text-muted">Ngumpulin. By Jodi Jonatan</p>
          </form>

        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>