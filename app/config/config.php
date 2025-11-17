<?php
// config.php
// Sesuaikan sesuai environment
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'exkul_tasks');


// Path
define('APP_ROOT', dirname(__DIR__, 1) . '/');
define('UPLOAD_DIR', APP_ROOT . 'app/uploads/');
define('BASE_URL', '/exkul-task-manager/public/'); // sesuaikan


// Session
session_start();
