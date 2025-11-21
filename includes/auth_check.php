<?php
// includes/auth_check.php (Versi Sederhana yang Hanya Melakukan Cek Login)

// Panggil config untuk memastikan session sudah aktif (dan BASE_URL ada)
if (session_status() === PHP_SESSION_NONE) {
  require_once __DIR__ . '/../config/config.php';
}

// Panggil helper yang berisi require_login
require_once __DIR__ . '/../app/helpers/utils.php';

// Melakukan Cek Login
require_login();

// Catatan: Pengecekan ROLE spesifik (Admin/Student) sebaiknya dilakukan
// di file halaman tujuan (e.g., dashboard.php) menggunakan require_role() 
// untuk menghindari logika kondisional yang rumit di sini.
