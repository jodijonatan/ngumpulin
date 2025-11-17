<?php
// includes/header_student.php (Hanya untuk Role 'student')

// Pastikan config dan session sudah dimuat sebelum file ini dipanggil.
$pageTitle = $pageTitle ?? "Dashboard"; // Default ke Dashboard
$userRole = $_SESSION['user']['role'] ?? 'student';
$userName = $_SESSION['user']['name'] ?? ($_SESSION['user']['username'] ?? 'Peserta'); // Prioritaskan nama, jika ada

// Function untuk generate link sidebar
function generateSidebarLinkStudent($title, $url, $currentPageTitle, $iconClass)
{
  // Menggunakan perbandingan path, bukan hanya title, untuk handling yang lebih robust
  $path = explode('/', $url);
  $currentPath = strtolower(basename($_SERVER['PHP_SELF']));
  $targetPath = strtolower(end($path));

  // Khusus untuk Dashboard
  if ($title === "Dashboard") {
    $isActive = ($currentPageTitle === $title) || ($currentPath === 'dashboard.php');
  } else {
    $isActive = ($currentPageTitle === $title) || ($currentPath === $targetPath);
  }

  $activeClass = $isActive ? 'active' : '';
  $baseUrl = BASE_URL;

  echo "<li class='nav-item'>
              <a class='nav-link {$activeClass}' href='{$baseUrl}{$url}'>
                  <i class='{$iconClass} me-3'></i> 
                  {$title}
              </a>
          </li>";
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ngumpulin - <?php echo htmlspecialchars($pageTitle); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* TEMA MONOKROMATIK & MODERN */
    :root {
      --sidebar-width: 280px;
      --primary-dark: #343a40;
      /* Hitam Gelap */
      --secondary-light: #f8f9fa;
      /* Abu-abu Sangat Terang */
      --accent-color: #6c757d;
      /* Abu-abu Sedang untuk aksen */
    }

    body {
      min-height: 100vh;
      background-color: var(--secondary-light);
      display: flex;
    }

    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: var(--primary-dark);
      color: white;
      padding: 20px 0;
      z-index: 1000;
      box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    }

    .main-content-wrapper {
      margin-left: var(--sidebar-width);
      flex-grow: 1;
      padding: 0;
    }

    /* Logo & Header Sidebar */
    .sidebar-brand {
      font-weight: 800;
      font-size: 1.5rem;
      letter-spacing: 1px;
      margin-bottom: 25px;
      padding: 0 20px;
      color: #fff;
      text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
    }

    /* Navigasi Link */
    .sidebar .nav-link {
      color: #adb5bd;
      /* Abu-abu muda */
      padding: 12px 25px;
      transition: all 0.2s;
      font-weight: 500;
    }

    .sidebar .nav-link:hover {
      color: white;
      background-color: #495057;
      /* Abu-abu sedikit lebih terang */
    }

    .sidebar .nav-link.active {
      color: white;
      background-color: var(--accent-color);
      /* Menggunakan abu-abu sedang sebagai aksen aktif */
      border-left: 5px solid #fff;
      /* Garis putih tegas pada elemen aktif */
      padding-left: 20px;
    }

    .sidebar-profile {
      padding: 0 25px 20px;
      border-bottom: 1px solid #495057;
      margin-bottom: 15px;
    }

    .main-header {
      background-color: white;
      padding: 18px 30px;
      border-bottom: 1px solid #dee2e6;
      box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
      /* Membuat header fixed agar sidebar dan header selaras */
      position: sticky;
      top: 0;
      z-index: 999;
    }

    .main-header h1 {
      font-size: 1.5rem;
      color: var(--primary-dark);
    }
  </style>
</head>

<body>

  <div class="sidebar">

    <div class="sidebar-brand">
      <i class="fas fa-graduation-cap me-2"></i> NGUMPULIN
    </div>

    <div class="sidebar-profile">
      <p class="text-white fw-bold mb-0"><?= htmlspecialchars($userName) ?></p>
      <p class="text-secondary small mb-0">Role: Siswa/Peserta</p>
    </div>

    <ul class="nav flex-column">
      <?php generateSidebarLinkStudent("Dashboard", "student/dashboard.php", $pageTitle, "fas fa-tachometer-alt"); ?>

      <?php generateSidebarLinkStudent("Daftar Tugas", "student/tasks.php", $pageTitle, "fas fa-list-check"); ?>

      <?php generateSidebarLinkStudent("Riwayat Pengumpulan", "student/submissions.php", $pageTitle, "fas fa-history"); ?>

      <li class="nav-item mt-4">
        <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>logout.php">
          <i class="fas fa-sign-out-alt me-3"></i> Logout
        </a>
      </li>
    </ul>
  </div>
  <div class="main-content-wrapper">

    <header class="main-header">
      <h1 class="h3 mb-0"><?php echo htmlspecialchars($pageTitle); ?></h1>
    </header>

    <main class="p-4">