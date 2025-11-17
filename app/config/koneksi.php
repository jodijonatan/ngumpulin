<?php
require_once __DIR__ . '/config.php';


$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) {
  die('Database connect error: ' . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');


// helper to prepare statements easily
function db_prepare_and_execute($query, $types = null, $params = [])
{
  global $mysqli;
  $stmt = $mysqli->prepare($query);
  if ($stmt === false) {
    die('Prepare failed: ' . $mysqli->error);
  }
  if ($types && $params) {
    $stmt->bind_param($types, ...$params);
  }
  $stmt->execute();
  return $stmt;
}
