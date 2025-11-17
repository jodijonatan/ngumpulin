<?php

require_once __DIR__ . '/../../config/koneksi.php';

function is_logged_in()
{
  return isset($_SESSION['user']);
}


function current_user()
{
  return $_SESSION['user'] ?? null;
}


function require_login()
{
  if (!is_logged_in()) {
    header('Location: /ngumpulin/public/login.php');
    exit;
  }
}


function require_admin()
{
  if (!is_logged_in() || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /ngumpulin/public/login.php');
    exit;
  }
}


function upload_file($fileInputName)
{
  if (empty($_FILES[$fileInputName]) || $_FILES[$fileInputName]['error'] !== UPLOAD_ERR_OK) {
    return ['error' => 'No file uploaded or upload error'];
  }
  $f = $_FILES[$fileInputName];
  $allowed = ['pdf', 'doc', 'docx', 'zip', 'rar', 'png', 'jpg', 'jpeg'];
  $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, $allowed)) return ['error' => 'File type not allowed'];
  $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
  $dest = UPLOAD_DIR . $filename;
  if (!move_uploaded_file($f['tmp_name'], $dest)) return ['error' => 'Failed to move uploaded file'];
  return ['path' => $filename];
}
