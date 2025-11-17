<?php
// public/index.php

// 1. Panggil config untuk memulai session dan mendapatkan BASE_URL
require_once __DIR__ . '/../config/config.php';

// 2. Simple entry: redirect user based on session/role
if (isset($_SESSION['user'])) {
  // Session user ada, cek role
  if ($_SESSION['user']['role'] === 'admin') {
    header('Location: ' . BASE_URL . 'admin/dashboard.php');
    exit;
  } else {
    header('Location: ' . BASE_URL . 'tasks.php');
    exit;
  }
} else {
  // Belum login, redirect ke halaman login
  header('Location: ' . BASE_URL . 'login.php');
  exit;
}
