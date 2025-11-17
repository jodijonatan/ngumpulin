<?php
// config/koneksi.php
require_once __DIR__ . '/config.php';

// Mengubah variabel koneksi dari $mysqli menjadi $conn
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_errno) {
  // Memberikan pesan error yang jelas jika koneksi gagal
  die('Database connect error: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');


// helper to prepare statements easily
function db_prepare_and_execute($query, $types = null, $params = [])
{
  // Mengubah global $mysqli menjadi global $conn
  global $conn;
  $stmt = $conn->prepare($query);
  if ($stmt === false) {
    die('Prepare failed: ' . $conn->error);
  }
  if ($types && $params) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  return $stmt;
}
