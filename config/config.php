<?php
// config/config.php
// Sesuaikan sesuai environment
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ngumpulin');


// Path
define('APP_ROOT', dirname(__DIR__, 1) . '/');
define('UPLOAD_DIR', APP_ROOT . 'app/uploads/');
define('BASE_URL', '/ngumpulin/public/'); // sesuaikan


// Session - Pastikan hanya dipanggil sekali
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
// Tidak ada output HTML/spasi di sini