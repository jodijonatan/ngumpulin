<?php
// public/admin/dashboard.php

// 1. Definisikan kebutuhan akses admin
$requireAdmin = true;

// 2. Load Konfigurasi & Koneksi Database
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';

// 3. Load Autentikasi
require_once __DIR__ . '/../../includes/auth_check.php';

// 4. Set Judul Halaman DINAMIS
$pageTitle = "Dashboard"; // <-- Tambahkan ini

// 5. Ambil Statistik
$total_users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role='member'")->fetch_assoc()['total'];
// ... (statistik lainnya) ...

// 6. Load Header (memuat sidebar dan header dinamis)
require_once __DIR__ . '/../../includes/header.php';
// HAPUS: require_once __DIR__ . '/../../includes/navbar_admin.php';
?>

<div class="container-fluid">
  <h2>Statistik Ngumpulin</h2>

</div>

<?php require_once '../../includes/footer.php'; ?>