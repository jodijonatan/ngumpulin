<?php
// includes/auth_check.php

// Panggil config untuk memastikan session sudah aktif
if (session_status() === PHP_SESSION_NONE) {
  // Sesuaikan path ke config.php
  require_once __DIR__ . '/../config/config.php';
}

// ===============================
// CEK LOGIN
// ===============================
// Jika $_SESSION['user'] belum ada, alihkan ke halaman login
if (!isset($_SESSION['user'])) {
  header("Location: " . BASE_URL . "login.php");
  exit;
}

// ===============================
// CEK ADMIN (opsional)
// ===============================
// menggunakan: $requireAdmin = true; sebelum include file ini
if (isset($requireAdmin) && $requireAdmin === true) {
  if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'admin') {
    // Alihkan ke halaman non-admin jika bukan admin
    header("Location: " . BASE_URL . "index.php");
    exit;
  }
}
