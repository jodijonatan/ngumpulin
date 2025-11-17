<?php
// includes/header.php (Diperbarui untuk Layout Universal Sidebar Modern)

// Pastikan $pageTitle didefinisikan sebelum include header.php di halaman utama
$pageTitle = $pageTitle ?? "Halaman Utama";
$userRole = $_SESSION['user']['role'] ?? 'guest'; // Ambil role user

// Function untuk generate link sidebar
// Tambahkan parameter $icon untuk ikon Font Awesome
function generateSidebarLink($title, $url, $icon, $currentPageTitle)
{
  // Cek apakah URL saat ini mengandung URL link untuk memastikan active state yang benar
  $activeClass = (strpos($currentPageTitle, $title) !== false) ? 'active' : '';
  $baseUrl = BASE_URL;

  // Perbaikan: gunakan BASE_URL di depan URL jika belum ada
  $fullUrl = (strpos($url, $baseUrl) === 0) ? $url : $baseUrl . $url;

  echo "<li class='nav-item'>
              <a class='nav-link $activeClass' href='{$fullUrl}'>
                <i class='{$icon} me-3 fa-fw'></i>
                <span>{$title}</span>
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

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    :root {
      --sidebar-width: 280px;
      --sidebar-bg: #212529;
      /* Darker than default */
      --sidebar-color: #e9ecef;
      --primary-color: #0d6efd;
    }

    body {
      min-height: 100vh;
      background-color: #f4f6f9;
      /* Light, modern background */
    }

    /* --- Sidebar Style --- */
    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: var(--sidebar-bg);
      color: var(--sidebar-color);
      padding: 0;
      box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
      transition: all 0.3s;
    }

    /* Sidebar Logo/Brand */
    .sidebar-header {
      padding: 20px 25px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* Sidebar Navigation Links */
    .sidebar .nav-link {
      padding: 15px 25px;
      color: var(--sidebar-color);
      border-left: 5px solid transparent;
      transition: all 0.2s;
    }

    .sidebar .nav-link:hover {
      color: white;
      background-color: #343a40;
      border-left-color: var(--primary-color);
    }

    /* Active Link */
    .sidebar .nav-link.active {
      color: white;
      background-color: #343a40;
      border-left-color: #0d6efd;
      /* Primary color highlight */
      font-weight: 600;
    }

    /* Logout Link Styling */
    .logout-section {
      padding: 20px 0;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      position: absolute;
      bottom: 0;
      width: 100%;
    }

    /* --- Main Content Style --- */
    .main-content-wrapper {
      margin-left: var(--sidebar-width);
      padding: 0;
      transition: margin-left 0.3s;
    }

    .main-header {
      background-color: white;
      padding: 15px 30px;
      border-bottom: 1px solid #dee2e6;
      box-shadow: 0 2px 8px rgba(0, 0, 0, .1);
      position: sticky;
      top: 0;
      z-index: 1000;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    /* Mobile adjustments (Optional, but recommended for modern UI) */
    @media (max-width: 992px) {
      .sidebar {
        margin-left: calc(var(--sidebar-width) * -1);
        /* Hidden by default */
      }

      .main-content-wrapper {
        margin-left: 0;
      }

      /* Class untuk menampilkan sidebar saat tombol toggle ditekan */
      /* Logika JS untuk toggle ini perlu di footer/halaman utama */
      .sidebar.show {
        margin-left: 0;
      }

      .main-header {
        padding-left: 15px;
        /* Kurangi padding karena ada tombol toggle */
      }
    }
  </style>
</head>

<body>
  <button class="btn btn-primary d-block d-lg-none position-fixed top-0 start-0 m-3 z-3" type="button"
    data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
    <i class="fas fa-bars"></i>
  </button>

  <div class="sidebar d-none d-lg-block" id="sidebarOffcanvas">

    <div class="sidebar-header">
      <h3 class="mb-0 text-white fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i> NGUMPULIN</h3>
      <span class="badge bg-secondary mt-2">Role: <?= htmlspecialchars(ucfirst($userRole)) ?></span>
    </div>

    <ul class="nav flex-column mt-3">

      <li class="nav-header text-uppercase text-muted small px-4 mb-2 mt-4">MENU NAVIGASI</li>

      <?php if ($userRole === 'admin'): ?>
        <?php generateSidebarLink("Dashboard", "admin/dashboard.php", "fas fa-chart-line", $pageTitle); ?>
        <li class="nav-item">
          <hr class="dropdown-divider mx-3 border-secondary opacity-25">
        </li>
        <?php generateSidebarLink("Kelola Peserta", "admin/students.php", "fas fa-user-graduate", $pageTitle); ?>
        <?php generateSidebarLink("Kelola Tugas", "admin/tasks.php", "fas fa-clipboard-list", $pageTitle); ?>
        <?php generateSidebarLink("Kelola Pengumpulan", "admin/submissions.php", "fas fa-inbox", $pageTitle); ?>

      <?php elseif ($userRole === 'student'): ?>
        <?php generateSidebarLink("Dashboard", "student/dashboard.php", "fas fa-home", $pageTitle); ?>
        <?php generateSidebarLink("Daftar Tugas", "student/tasks.php", "fas fa-tasks", $pageTitle); ?>
        <?php generateSidebarLink("Riwayat Pengumpulan", "student/submissions.php", "fas fa-history", $pageTitle); ?>

      <?php endif; ?>

    </ul>

    <div class="logout-section d-grid gap-2 px-3">
      <a class="btn btn-outline-danger" href="<?php echo BASE_URL; ?>logout.php">
        <i class="fas fa-sign-out-alt me-2"></i> Logout
      </a>
    </div>
  </div>

  <div class="offcanvas offcanvas-start bg-dark text-white d-lg-none" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header sidebar-header">
      <h5 class="offcanvas-title text-white fw-bold" id="sidebarOffcanvasLabel"><i class="fas fa-layer-group me-2 text-primary"></i> NGUMPULIN</h5>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
      <ul class="nav flex-column">

        <li class="nav-header text-uppercase text-muted small px-4 mb-2 mt-2">MENU NAVIGASI</li>

        <?php if ($userRole === 'admin'): ?>
          <?php generateSidebarLink("Dashboard", "admin/dashboard.php", "fas fa-chart-line", $pageTitle); ?>
          <li class="nav-item">
            <hr class="dropdown-divider mx-3 border-secondary opacity-25">
          </li>
          <?php generateSidebarLink("Kelola Peserta", "admin/students.php", "fas fa-user-graduate", $pageTitle); ?>
          <?php generateSidebarLink("Kelola Tugas", "admin/tasks.php", "fas fa-clipboard-list", $pageTitle); ?>
          <?php generateSidebarLink("Kelola Pengumpulan", "admin/submissions.php", "fas fa-inbox", $pageTitle); ?>

        <?php elseif ($userRole === 'student'): ?>
          <?php generateSidebarLink("Dashboard", "student/dashboard.php", "fas fa-home", $pageTitle); ?>
          <?php generateSidebarLink("Daftar Tugas", "student/tasks.php", "fas fa-tasks", $pageTitle); ?>
          <?php generateSidebarLink("Riwayat Pengumpulan", "student/submissions.php", "fas fa-history", $pageTitle); ?>

        <?php endif; ?>

        <li class="nav-item mt-5">
          <a class="nav-link text-danger" href="<?php echo BASE_URL; ?>logout.php">
            <i class="fas fa-sign-out-alt me-3 fa-fw"></i>
            <span>Logout</span>
          </a>
        </li>
      </ul>
    </div>
  </div>

  <div class="main-content-wrapper">
    <header class="main-header">
      <h1 class="h3 mb-0 text-dark fw-bold"><?php echo htmlspecialchars($pageTitle); ?></h1>
      <div class="d-flex align-items-center">
        <span class="text-muted me-3 d-none d-sm-inline">Halo, <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Admin/Peserta') ?>!</span>
        <i class="fas fa-user-circle fa-2x text-secondary"></i>
      </div>
    </header>

    <main class="p-4">