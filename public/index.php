<?php
// public/index.php - Front Controller & Router

// 1. Panggil config
// Asumsi config/ berada satu level di atas public/
require_once __DIR__ . '/../config/config.php';

// Pastikan session sudah dimulai (sebaiknya di config.php)
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

## A. AMBIL URI YANG DIMINTA
// 1. Ambil path URI (Uniform Resource Identifier) yang diminta pengguna
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

// 2. Tentukan prefix folder Anda (sesuaikan 'ngumpulin')
// Cari tahu apakah folder proyek ada di URI
$prefix = 'ngumpulin';

// Cek apakah URI dimulai dengan prefix folder dan hapus
if (strpos($uri, $prefix) === 0) {
  // Hapus 'ngumpulin' dari awal string
  $uri = substr($uri, strlen($prefix));
  // Hapus slash di awal jika masih ada (misal: /login menjadi login)
  $uri = trim($uri, '/');
}
// Setelah ini, jika akses: /ngumpulin/login, $uri akan menjadi 'login'

// Pisahkan URI menjadi segmen (tidak wajib, tapi berguna untuk debugging/parameter)
$segments = explode('/', $uri);


## B. DEFINISI ROUTE & PEMETAAN KE CONTROLLER
// Peta: URL Bersih => File Controller yang akan memproses permintaan
$routes = [
  // --- Route Publik (Tidak Perlu Login) ---
  '' => '',      // URL: /
  'login' => 'public/login.php',    // URL: /login
  'logout' => 'public/logout.php',   // URL: /logout
  'register' => 'public/register.php', // URL: /register

  // --- Route Student (Dibutuhkan Login - Role 'student') ---
  'student/dashboard' => 'public/student/dashboard.php',
  'student/tasks' => 'public/student/tasks.php',
  'student/submissions' => 'public/student/submissions.php',

  // --- Route Admin (Dibutuhkan Login - Role 'admin') ---
  'admin/dashboard' => 'public/admin/dashboard.php',
  'admin/students' => 'public/admin/students.php',
  'admin/tasks' => 'public/admin/tasks.php',
  'admin/submissions' => 'public/admin/submissions.php',

  // --- Route Aksi/Controller Khusus ---
  'download' => 'public/download.php',

  // Contoh rute yang membutuhkan parameter (halaman detail task)
  // Gunakan rute terpanjang dulu untuk menghindari konflik
  'student/task/view' => 'public/student/task_view.php',
  'admin/submission/review' => 'public/admin/submission_view.php',
];


## C. PENCARIAN & PEMUATAN ROUTE
$target_controller = null;

// 1. Cek rute yang persis sama
if (isset($routes[$uri])) {
  $target_controller = $routes[$uri];
} else {
  // 2. Cek rute dengan parameter (Dynamic Routing Sederhana)
  // Ini menangani kasus seperti /admin/submission/review/10
  foreach ($routes as $route_pattern => $file) {
    // Cek jika URI dimulai dengan pola rute & bukan home ('')
    if ($route_pattern !== '' && strpos($uri, $route_pattern) === 0) {

      // Ambil sisa URI sebagai string parameter
      $params_string = trim(substr($uri, strlen($route_pattern)), '/');

      if ($params_string !== '') {
        // Pisahkan string parameter menjadi array dan simpan di $_GET
        $params = explode('/', $params_string);
        $_GET['params'] = $params;
      }

      $target_controller = $file;
      break; // Hentikan pencarian jika sudah ketemu
    }
  }
}


## D. PENANGANAN OTORISASI & MIDDLEWARE SEDERHANA
// Daftar rute yang TIDAK memerlukan autentikasi
$public_routes = ['login', 'logout', 'register', ''];

// Cek apakah URI mengandung 'admin/'
$is_admin_route = (strpos($uri, 'admin/') === 0);

if ($target_controller) {
  // --- 1. Cek Autentikasi (WAJIB Login) ---
  if (!in_array($uri, $public_routes)) {
    if (!isset($_SESSION['user'])) {
      // Belum login, arahkan ke login
      header('Location: ' . BASE_URL . 'login');
      exit;
    }
  }

  // --- 2. Cek Otorisasi (WAJIB Role Admin) ---
  if ($is_admin_route) {
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
      // User mencoba akses admin tanpa hak
      http_response_code(403);
      // Muat tampilan error 403
      require_once __DIR__ . '/../includes/errors/403.php';
      exit;
    }
  }

  // 3. Muat file Controller target
  $controller_path = __DIR__ . '/../' . $target_controller;

  if (file_exists($controller_path)) {
    require_once $controller_path;
  } else {
    // Target Controller ada di $routes tapi file tidak ada di server
    http_response_code(500);
    echo "<h1>500 Internal Server Error</h1><p>Controller file not found: " . htmlspecialchars($target_controller) . "</p>";
  }
} else {
  // Rute tidak ditemukan (404 Not Found)
  http_response_code(404);
  // Muat tampilan error 404
  require_once __DIR__ . '/../includes/errors/404.php';
}
