<?php
// public/admin/students.php

// 1. Definisikan kebutuhan akses admin
$requireAdmin = true;

// 2. Load Konfigurasi & Koneksi Database
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/koneksi.php';

// 3. Load Autentikasi
require_once __DIR__ . '/../../includes/auth_check.php';

// 4. Set Judul Halaman DINAMIS
$pageTitle = "Kelola Peserta";

// 5. Inisialisasi variabel untuk Form (Edit/Tambah)
$action = $_GET['action'] ?? 'read'; // Default action adalah 'read'
$studentData = ['id' => '', 'username' => '', 'name' => '', 'password' => ''];
$formTitle = 'Tambah Peserta Baru';
$formError = '';
$formSuccess = '';

// ====================================
// LOGIKA CRUD
// ====================================

// --- HANDLE SUBMISSION (CREATE & UPDATE) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['form_action'] ?? 'read';
  $id = $_POST['id'] ?? null;
  $username = trim($_POST['username'] ?? '');
  $name = trim($_POST['name'] ?? '');
  $password = $_POST['password'] ?? '';
  $role = 'student'; // Role default adalah student

  if (empty($username) || empty($name)) {
    $formError = "Username dan Nama Lengkap tidak boleh kosong.";
  } else {
    if ($action === 'create') {
      if (empty($password)) {
        $formError = "Password wajib diisi untuk peserta baru.";
      } else {
        // Hashing Password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $query = "INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)";
        $stmt = db_prepare_and_execute($query, "ssss", [$username, $hashedPassword, $name, $role]);

        if ($stmt->affected_rows > 0) {
          $formSuccess = "Peserta berhasil ditambahkan.";
          $action = 'read'; // Kembali ke mode Read
        } else {
          $formError = "Gagal menambahkan peserta. Username mungkin sudah digunakan.";
        }
        $stmt->close();
      }
    } elseif ($action === 'update') {
      $updateFields = ['username=?, name=?'];
      $updateTypes = "ss";
      $updateParams = [$username, $name];

      if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $updateFields[] = 'password=?';
        $updateTypes .= "s";
        $updateParams[] = $hashedPassword;
      }

      $updateParams[] = $id;
      $updateTypes .= "i"; // id always int

      $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id=?";
      $stmt = db_prepare_and_execute($query, $updateTypes, $updateParams);

      if ($stmt->affected_rows >= 0) {
        $formSuccess = "Data peserta berhasil diperbarui.";
        $action = 'read'; // Kembali ke mode Read
      } else {
        $formError = "Gagal memperbarui data peserta.";
      }
      $stmt->close();
    }
  }

  // Jika ada error/success, muat ulang form_action
  if ($action !== 'read') {
    $studentData = ['id' => $id, 'username' => $username, 'name' => $name, 'password' => ''];
    $formTitle = ($action === 'edit') ? 'Edit Peserta' : 'Tambah Peserta Baru';
  }
}

// --- HANDLE DELETE ---
if ($action === 'delete' && isset($_GET['id'])) {
  $id = $_GET['id'];
  $query = "DELETE FROM users WHERE id=? AND role='student'";
  $stmt = db_prepare_and_execute($query, "i", [$id]);

  if ($stmt->affected_rows > 0) {
    $formSuccess = "Peserta berhasil dihapus.";
  } else {
    $formError = "Gagal menghapus peserta atau peserta tidak ditemukan.";
  }
  $stmt->close();
  $action = 'read'; // Kembali ke mode Read
}

// --- HANDLE EDIT (Menampilkan Form Edit) ---
if ($action === 'edit' && isset($_GET['id'])) {
  $id = $_GET['id'];
  $query = "SELECT id, username, name FROM users WHERE id=? AND role='student'";
  $stmt = db_prepare_and_execute($query, "i", [$id]);
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
    $studentData = $result->fetch_assoc();
    $formTitle = 'Edit Peserta: ' . htmlspecialchars($studentData['name']);
    // Setelah load data, kita kembali ke mode form (edit)
  } else {
    $formError = "Peserta tidak ditemukan.";
    $action = 'read';
  }
  $stmt->close();
}

// --- LOGIKA READ (Mengambil semua data peserta) ---
$query = "SELECT id, username, name, created_at FROM users WHERE role='student' ORDER BY name";
$result = $conn->query($query);

if ($result === false) {
  die("Query daftar peserta gagal: " . $conn->error);
}
$students = $result->fetch_all(MYSQLI_ASSOC);


// 6. Load Header
require_once __DIR__ . '/../../includes/header.php';
?>

<div class="container-fluid">
  <?php if ($formSuccess): ?>
    <div class="alert alert-success mt-3"><?= htmlspecialchars($formSuccess) ?></div>
  <?php endif; ?>
  <?php if ($formError): ?>
    <div class="alert alert-danger mt-3"><?= htmlspecialchars($formError) ?></div>
  <?php endif; ?>

  <?php if ($action === 'create' || $action === 'edit'): ?>

    <div class="card mb-4">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0"><?= $formTitle ?></h4>
      </div>
      <div class="card-body">
        <form method="POST" action="students.php">
          <input type="hidden" name="form_action" value="<?= ($action === 'edit') ? 'update' : 'create' ?>">
          <input type="hidden" name="id" value="<?= htmlspecialchars($studentData['id']) ?>">

          <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($studentData['name']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($studentData['username']) ?>" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">Password <?= ($action === 'edit') ? '(Kosongkan jika tidak diubah)' : '' ?></label>
            <input type="password" class="form-control" id="password" name="password">
          </div>

          <button type="submit" class="btn btn-success me-2"><?= ($action === 'edit') ? 'Perbarui Data' : 'Tambah Peserta' ?></button>
          <a href="students.php" class="btn btn-secondary">Batal</a>
        </form>
      </div>
    </div>

  <?php endif; ?>

  <?php if ($action === 'read' || $action === 'delete'): // Tampilkan daftar setelah delete/read 
  ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3>Daftar Peserta Ekskul (<?= count($students) ?>)</h3>
      <a href="students.php?action=create" class="btn btn-primary">Tambah Peserta Baru</a>
    </div>

    <?php if (count($students) > 0): ?>
      <div class="table-responsive">
        <table class="table table-striped table-hover">
          <thead class="table-dark">
            <tr>
              <th>#</th>
              <th>Username</th>
              <th>Nama Lengkap</th>
              <th>Bergabung</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1;
            foreach ($students as $student): ?>
              <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($student['username']); ?></td>
                <td><?php echo htmlspecialchars($student['name']); ?></td>
                <td><?php echo date('d M Y', strtotime($student['created_at'])); ?></td>
                <td>
                  <a href="students.php?action=edit&id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning me-1">Edit</a>
                  <a href="students.php?action=delete&id=<?php echo $student['id']; ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('Yakin ingin menghapus peserta \'<?= htmlspecialchars($student['name']) ?>\'? Tindakan ini permanen!');">Hapus</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-info" role="alert">
        Belum ada peserta dengan role 'student' yang terdaftar.
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>