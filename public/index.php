<?php
// public/index.php
// Simple entry: redirect user based on session/role
session_start();
if (isset($_SESSION['user'])) {
  if ($_SESSION['user']['role'] === 'admin') {
    header('Location: admin/dashboard.php');
    exit;
  } else {
    header('Location: tasks.php');
    exit;
  }
} else {
  header('Location: login.php');
  exit;
}
