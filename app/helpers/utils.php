<?php
// app/helpers/utils.php

// Pastikan BASE_URL dan UPLOAD_DIR sudah didefinisikan di tempat lain (misalnya config/config.php)
require_once __DIR__ . '/../../config/koneksi.php';

// --- FUNGSI AUTHENTIKASI DASAR ---

function is_logged_in()
{
  // Cek apakah sesi dimulai dan variabel user ada
  return isset($_SESSION['user']);
}

function current_user()
{
  return $_SESSION['user'] ?? null;
}

// --- FUNGSI MIDDLEWARE OTORISASI ---

/**
 * Middleware Otorisasi: Memastikan user memiliki peran yang diizinkan untuk mengakses halaman.
 * Jika tidak, user akan dialihkan ke halaman dashboard yang sesuai dengan rolenya.
 * * @param array|string $allowed_roles Peran yang diizinkan (e.g., ['admin'], 'student').
 */
function require_role($allowed_roles)
{
  // 1. Cek Login
  if (!is_logged_in()) {
    // Jika belum login, alihkan ke halaman login
    header('Location: ' . BASE_URL . 'login.php');
    exit;
  }

  $user_role = current_user()['role'];

  // 2. Ubah peran yang diizinkan menjadi array jika hanya string tunggal
  if (!is_array($allowed_roles)) {
    $allowed_roles = [$allowed_roles];
  }

  // 3. Cek Otorisasi
  if (!in_array($user_role, $allowed_roles)) {
    // Jika peran tidak diizinkan, alihkan ke dashboard yang sesuai

    if ($user_role === 'admin') {
      // Admin mencoba akses halaman student, redirect ke dashboard admin
      header('Location: ' . BASE_URL . 'admin/dashboard.php');
    } elseif ($user_role === 'student') {
      // Student mencoba akses halaman admin, redirect ke dashboard student
      header('Location: ' . BASE_URL . 'student/dashboard.php');
    } else {
      // Role tidak dikenal, atau fallback (jarang terjadi)
      header('Location: ' . BASE_URL . 'index.php');
    }
    exit;
  }
}

// --- FUNGSI WRAPPER AGAR KODE LAMA TETAP JALAN ---

function require_login()
{
  // Hanya memastikan user sudah login (tidak peduli rolenya)
  if (!is_logged_in()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
  }
}

function require_admin()
{
  // Memastikan user adalah admin. Menggunakan require_role untuk logika pengalihan yang cerdas.
  require_role('admin');
}

// --- FUNGSI UPLOAD ---

function upload_file($fileInputName)
{
  if (empty($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'No file uploaded or upload error'];
  }

  $f = $_FILES[$fileInputName];
  $allowed = ['pdf', 'doc', 'docx', 'zip', 'rar', 'png', 'jpg', 'jpeg'];
  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));

  if (!in_array($ext, $allowed)) return ['error' => 'File type not allowed'];

  // Buat nama file unik dan aman
  $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;

  // Asumsi UPLOAD_DIR adalah konstanta global yang benar
  $dest = UPLOAD_DIR . $filename;

  if (!move_uploaded_file($f['tmp_name'], $dest)) return ['error' => 'Failed to move uploaded file'];

  // Hanya kembalikan nama file agar path lengkap (UPLOAD_DIR) tidak disimpan
  // Jika Anda menyimpan path lengkap, kembalikan: ['path' => $dest]
  return ['path' => $filename];
}
