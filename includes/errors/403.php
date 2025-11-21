<?php
// Tentukan Judul Halaman
$page_title = "Akses Ditolak";

// Asumsikan BASE_URL sudah didefinisikan di config.php
$base_url = defined('BASE_URL') ? BASE_URL : '/ngumpulin/';

// Tentukan pesan spesifik
$error_code = "403";
$error_heading = "AKSES TERLARANG";
$error_message = "Maaf, Anda tidak memiliki izin untuk melihat halaman ini. Silakan kembali ke area yang sesuai dengan hak akses Anda.";
$redirect_link = $base_url . 'student/dashboard'; // Atau 'admin/dashboard' jika role tidak cocok

// Opsional: Cek role untuk penentuan link
if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin') {
  $redirect_link = $base_url . 'admin/dashboard';
}

// Jangan tampilkan header/footer penuh jika Anda ingin halaman ini berdiri sendiri
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title . ' - ' . $error_code; ?></title>
  <link href="<?php echo $base_url; ?>public/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    body {
      background-color: #f8f9fa;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      color: #343a40;
    }

    .error-container {
      text-align: center;
      padding: 40px;
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .error-code {
      font-size: 8rem;
      font-weight: 700;
      color: #dc3545;
      /* Merah untuk Forbidden */
      margin-bottom: 20px;
    }

    .error-icon {
      font-size: 3rem;
      color: #dc3545;
      margin-bottom: 15px;
    }

    .btn-custom {
      background-color: #dc3545;
      border-color: #dc3545;
      transition: all 0.3s;
    }

    .btn-custom:hover {
      background-color: #c82333;
      border-color: #c82333;
    }
  </style>
</head>

<body>
  <div class="error-container">
    <div class="error-icon">
      <i class="fas fa-hand-paper"></i>
    </div>
    <div class="error-code">
      <?php echo $error_code; ?>
    </div>
    <h1 class="mb-4 text-danger"><?php echo $error_heading; ?></h1>
    <p class="lead mb-4"><?php echo $error_message; ?></p>
    <a href="<?php echo $redirect_link; ?>" class="btn btn-custom text-white btn-lg">
      <i class="fas fa-tachometer-alt"></i> Kembali ke Dashboard
    </a>
  </div>
</body>

</html>