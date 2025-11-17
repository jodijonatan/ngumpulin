<?php
// public/download.php
// Securely serve uploaded files from app/uploads/
// Usage: download.php?file=filename.ext
require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/config/koneksi.php';

session_start();
if (!isset($_SESSION['user'])) {
  header('HTTP/1.1 403 Forbidden');
  echo 'Forbidden';
  exit;
}

$filename = basename($_GET['file'] ?? '');
if (empty($filename)) {
  header('HTTP/1.1 400 Bad Request');
  echo 'Bad request';
  exit;
}

$filepath = UPLOAD_DIR . $filename;
if (!file_exists($filepath) || !is_file($filepath)) {
  header('HTTP/1.1 404 Not Found');
  echo 'File not found';
  exit;
}

// Optional: additional access checks (e.g., only owner or admin)
$mime = mime_content_type($filepath);
$filesize = filesize($filepath);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $filesize);
header('Cache-Control: private');
readfile($filepath);
exit;
