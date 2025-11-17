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

        // Asumsi db_prepare_and_execute tersedia dan berfungsi
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

<div class="container-fluid py-4">
  <?php if ($formSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
      <i class="fas fa-check-circle me-2"></i>
      <div><?= htmlspecialchars($formSuccess) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <?php if ($formError): ?>
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
      <i class="fas fa-exclamation-triangle me-2"></i>
      <div><?= htmlspecialchars($formError) ?></div>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php if ($action === 'create' || $action === 'edit'): ?>

    <div class="card shadow-lg mb-4">
      <div class="card-header bg-primary text-white p-3">
        <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i> <?= $formTitle ?></h4>
      </div>
      <div class="card-body">
        <form method="POST" action="students.php">
          <input type="hidden" name="form_action" value="<?= ($action === 'edit') ? 'update' : 'create' ?>">
          <input type="hidden" name="id" value="<?= htmlspecialchars($studentData['id']) ?>">

          <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Nama Lengkap</label>
            <input type="text" class="form-control form-control-lg" id="name" name="name" value="<?= htmlspecialchars($studentData['name']) ?>" placeholder="Masukkan nama lengkap peserta" required>
          </div>
          <div class="mb-3">
            <label for="username" class="form-label fw-semibold">Username</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($studentData['username']) ?>" placeholder="Username login unik" required>
          </div>
          <div class="mb-4">
            <label for="password" class="form-label fw-semibold">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="<?= ($action === 'edit') ? 'Kosongkan jika tidak diubah' : 'Wajib diisi untuk peserta baru' ?>">
            <?php if ($action === 'edit'): ?>
              <div class="form-text text-muted">Abaikan kolom ini jika Anda tidak ingin mengubah password saat ini.</div>
            <?php endif; ?>
          </div>

          <div class="d-flex justify-content-end border-top pt-3">
            <a href="students.php" class="btn btn-secondary me-2">
              <i class="fas fa-times me-1"></i> Batal
            </a>
            <button type="submit" class="btn btn-success">
              <i class="fas fa-save me-1"></i> <?= ($action === 'edit') ? 'Perbarui Data' : 'Tambah Peserta' ?>
            </button>
          </div>
        </form>
      </div>
    </div>

  <?php endif; ?>

  <?php if ($action === 'read' || $action === 'delete' || ($action === 'create' && $formError) || ($action === 'edit' && $formError)): // Tampilkan daftar jika bukan mode form atau setelah operasi CRUD 
  ?>
    <div class="d-flex justify-content-between align-items-center mb-4 mt-3">
      <h3 class="mb-0"><i class="fas fa-users me-2"></i> Daftar Peserta Ekskul</h3>
      <span class="badge bg-info text-dark p-2 shadow-sm">Total: <?= count($students) ?> peserta</span>
      <a href="students.php?action=create" class="btn btn-primary shadow-sm">
        <i class="fas fa-user-plus me-1"></i> Tambah Peserta Baru
      </a>
    </div>

    <div class="card shadow-lg border-0">
      <div class="card-header bg-light border-bottom">
        <h5 class="mb-0 text-dark">Data Akun Peserta</h5>
      </div>
      <div class="card-body p-0">
        <?php if (count($students) > 0): ?>
          <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
              <thead class="table-secondary">
                <tr>
                  <th style="width: 5%;">#</th>
                  <th style="width: 25%;"><i class="fas fa-id-badge me-1"></i> Nama Lengkap</th>
                  <th style="width: 20%;"><i class="fas fa-user me-1"></i> Username</th>
                  <th style="width: 20%;"><i class="fas fa-calendar-alt me-1"></i> Bergabung Sejak</th>
                  <th style="width: 30%;"><i class="fas fa-cogs me-1"></i> Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php $no = 1;
                foreach ($students as $student): ?>
                  <tr>
                    <td><?php echo $no++; ?></td>
                    <td>
                      <div class="fw-bold"><?php echo htmlspecialchars($student['name']); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                    <td><?php echo date('d M Y', strtotime($student['created_at'])); ?></td>
                    <td>
                      <a href="students.php?action=edit&id=<?php echo $student['id']; ?>" class="btn btn-sm btn-warning me-2 text-dark" title="Edit Data">
                        <i class="fas fa-edit"></i> Edit
                      </a>
                      <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?php echo $student['id']; ?>" title="Hapus Akun">
                        <i class="fas fa-trash-alt"></i> Hapus
                      </button>

                      <div class="modal fade" id="deleteModal<?php echo $student['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $student['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                          <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                              <h5 class="modal-title" id="deleteModalLabel<?php echo $student['id']; ?>">Konfirmasi Hapus Peserta</h5>
                              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              Anda yakin ingin menghapus peserta **<?php echo htmlspecialchars($student['name']); ?>** (Username: **<?php echo htmlspecialchars($student['username']); ?>**)?<br>Tindakan ini permanen dan akan menghapus semua data terkait peserta ini!
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <a href="students.php?action=delete&id=<?php echo $student['id']; ?>" class="btn btn-danger">
                                <i class="fas fa-trash-alt me-1"></i> Ya, Hapus Permanen
                              </a>
                            </div>
                          </div>
                        </div>
                      </div>

                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="alert alert-info m-3" role="alert">
            <i class="fas fa-info-circle me-2"></i> Belum ada peserta dengan role 'student' yang terdaftar. Silakan tambahkan peserta baru.
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once '../../includes/footer.php'; ?>