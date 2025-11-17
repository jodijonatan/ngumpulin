<?php
// includes/header.php (Diperbarui untuk Layout Universal Sidebar)

// Pastikan $pageTitle didefinisikan sebelum include header.php di halaman utama
$pageTitle = $pageTitle ?? "Halaman Utama";
$userRole = $_SESSION['user']['role'] ?? 'guest'; // Ambil role user

// Function untuk generate link sidebar
function generateSidebarLink($title, $url, $currentPageTitle)
{
  $activeClass = ($currentPageTitle === $title) ? 'active' : '';
  $baseUrl = BASE_URL;
  echo "<li class='nav-item'>
              <a class='nav-link $activeClass' href='{$baseUrl}{$url}'>{$title}</a>
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
  <style>
    body {
      min-height: 100vh;
      background-color: #f8f9fa;
    }

    .sidebar {
      width: 250px;
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #343a40;
      /* Dark background for sidebar */
      color: white;
      padding-top: 20px;
    }

    .main-content-wrapper {
      margin-left: 250px;
      /* Offset untuk konten utama */
      padding: 0;
    }

    .main-header {
      background-color: white;
      padding: 15px 30px;
      border-bottom: 1px solid #dee2e6;
      box-shadow: 0 2px 4px rgba(0, 0, 0, .05);
    }

    .sidebar a {
      color: #adb5bd;
    }

    .sidebar a:hover {
      color: white;
      background-color: #495057;
    }

    .sidebar .active {
      color: white;
      background-color: #0d6efd;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <h3 class="text-center mb-4 text-warning">NGUMPULIN</h3>
    <p class="text-center text-secondary small">Login sebagai: <?= htmlspecialchars(ucfirst($userRole)) ?></p>

    <ul class="nav flex-column">

      <?php if ($userRole === 'admin'): ?>
        <?php generateSidebarLink("Dashboard", "admin/dashboard.php", $pageTitle); ?>
        <?php generateSidebarLink("Kelola Peserta", "admin/students.php", $pageTitle); ?>
        <?php generateSidebarLink("Kelola Tugas", "admin/tasks_crud.php", $pageTitle); ?>
        <?php generateSidebarLink("Kelola Pengumpulan", "admin/submissions.php", $pageTitle); ?>

      <?php elseif ($userRole === 'student'): ?>
        <?php generateSidebarLink("Daftar Tugas", "tasks.php", $pageTitle); ?>
        <?php generateSidebarLink("Riwayat Pengumpulan", "submissions.php", $pageTitle); ?>

      <?php endif; ?>

      <li class="nav-item mt-5">
        <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>logout.php">Logout</a>
      </li>
    </ul>
  </div>

  <div class="main-content-wrapper">
    <header class="main-header">
      <h1 class="h3 mb-0"><?php echo htmlspecialchars($pageTitle); ?></h1>
    </header>

    <main class="p-4">