<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Inipialisasi variabel
$mode = 'tambah';
$id_user = '';
$name = '';
$nip = '';
$username = '';
$email = '';
$role = '';
$password_input = '';

// Proses Simpan (Tambah atau Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_POST['id_user'];
    $name = $_POST['name'];
    $nip = $_POST['nip'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password_input = trim($_POST['password']);

    if ($id_user == '') {
        // Tambah data
        $password = $password_input !== '' ? password_hash($password_input, PASSWORD_DEFAULT) : password_hash('123456', PASSWORD_DEFAULT); // default password jika kosong
        $query = "INSERT INTO users (name, nip, username, email, role, password) 
                  VALUES ('$name', '$nip', '$username', '$email', '$role', '$password')";
        mysqli_query($koneksi, $query);
        header("Location: manajemen_user.php?message=Pengguna berhasil ditambahkan");
        exit();
    } else {
        // Edit data
        if ($password_input !== '') {
            $password = password_hash($password_input, PASSWORD_DEFAULT);
            $query = "UPDATE users SET name='$name', nip='$nip', username='$username', email='$email', role='$role', password='$password' 
                      WHERE id_user='$id_user'";
        } else {
            $query = "UPDATE users SET name='$name', nip='$nip', username='$username', email='$email', role='$role' 
                      WHERE id_user='$id_user'";
        }
        mysqli_query($koneksi, $query);
        header("Location: manajemen_user.php?message=Pengguna berhasil diperbarui");
        exit();
    }
}

// Proses Edit
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$edit_id'");
    $data = mysqli_fetch_assoc($result);
    if ($data) {
        $mode = 'edit';
        $id_user = $data['id_user'];
        $name = $data['name'];
        $nip = $data['nip'];
        $username = $data['username'];
        $email = $data['email'];
        $role = $data['role'];
    }
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $hapus_id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$hapus_id'");
    header("Location: manajemen_user.php?message=Pengguna berhasil dihapus");
    exit();
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Management User | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">

    <style>
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            font-family: 'Poppins', sans-serif;
        }

        /* Styling Container Utama */
        .main-content {
            padding: 20px;
        }

        .card {
            border-radius: 0.75rem;
        }

        .table {
            --bs-table-striped-bg: rgba(0, 0, 0, 0.02);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
        }

        .input-group-text {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <!-- Overlay hanya untuk mobile -->
        <div class="mobile-sidebar-overlay d-md-none" id="mobile-sidebar-overlay"></div>
        <div class="main">
            <?php include 'akun-info.php'; ?> <!-- Ini selalu muncul (desktop) -->
            <?php include 'navbar.php'; ?>
            <main class="content px-4 py-4">
                <div class="main-content">
                    <div class="container-fluid">
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-sm-flex justify-content-between align-items-center">
                                    <div class="mb-3 mb-sm-0">
                                        <h1 class="fw-bold mb-1">
                                            👥 Manajemen Pengguna
                                        </h1>
                                        <p class="text-muted mb-0">Kelola data pengguna sistem</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Alert -->
                        <?php if (isset($_GET['message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($_GET['message']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Form Tambah/Edit -->
                        <div class="card shadow-sm mb-4 border-0">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="bi <?php echo $mode === 'edit' ? 'bi-pencil-square text-warning' : 'bi-plus-circle text-success'; ?> me-2"></i>
                                    <?php echo $mode === 'edit' ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="row g-3 needs-validation" novalidate>
                                    <input type="hidden" name="id_user" value="<?php echo $id_user; ?>">

                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-medium">Nama Lengkap</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-person"></i></span>
                                            <input type="text" id="name" name="name" class="form-control" required value="<?php echo $name; ?>">
                                            <div class="invalid-feedback">
                                                Harap isi nama lengkap
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="username" class="form-label fw-medium">Username</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                                            <input type="text" id="username" name="username" class="form-control" required value="<?php echo $username; ?>">
                                            <div class="invalid-feedback">
                                                Harap isi username
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-medium">Email</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="email" id="email" name="email" class="form-control" required value="<?php echo $email; ?>">
                                            <div class="invalid-feedback">
                                                Harap isi email yang valid
                                            </div>
                                        </div>
                                    </div>

                                     <div class="col-md-6">
                                        <label for="nip" class="form-label fw-medium">NIS / NIP</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                            <input type="nip" id="nip" name="nip" class="form-control" required value="<?php echo $nip; ?>">
                                            <div class="invalid-feedback">
                                                Harap isi NIS / NIP
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="password" class="form-label fw-medium">
                                            Password
                                            <?php if ($mode === 'edit'): ?>
                                                <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small>
                                            <?php endif; ?>
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                            <input type="password" id="password" name="password" class="form-control" <?php echo $mode !== 'edit' ? 'required' : ''; ?>>
                                            <?php if ($mode !== 'edit'): ?>
                                                <div class="invalid-feedback">
                                                    Harap isi password
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="role" class="form-label fw-medium">Role</label>
                                        <select id="role" name="role" class="form-select" required>
                                            <option value="">-- Pilih Role --</option>
                                            <option value="admin" <?php if ($role === 'admin') echo 'selected'; ?>>Admin</option>
                                            <option value="guru" <?php if ($role === 'guru') echo 'selected'; ?>>Guru</option>
                                            <option value="siswa" <?php if ($role === 'siswa') echo 'selected'; ?>>Siswa</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Harap pilih role
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-success px-4">
                                            <i class="bi <?php echo $mode === 'edit' ? 'bi-check-circle' : 'bi-save'; ?> me-1"></i>
                                            <?php echo $mode === 'edit' ? 'Perbarui Pengguna' : 'Simpan Pengguna'; ?>
                                        </button>
                                        <?php if ($mode === 'edit'): ?>
                                            <a href="manajemen_user.php" class="btn btn-outline-secondary px-4 ms-2">
                                                <i class="bi bi-x-circle me-1"></i> Batal
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tabel Data -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="bi bi-list-check me-2 text-primary"></i> Daftar Pengguna
                                </h5>
                                <?php
                                $total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users");
                                $total_data = mysqli_fetch_assoc($total_query);
                                ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    Total: <?php echo $total_data['total']; ?> Pengguna
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50" class="text-center">No</th>
                                                <th>Nama</th>
                                                <th>Username</th>
                                                <th>Email</th>
                                                <th>NIS / NIP</th>
                                                <th width="120">Role</th>
                                                <th width="120" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $result = mysqli_query($koneksi, "SELECT * FROM users ORDER BY role, name");
                                            $no = 1;

                                            if ($result && mysqli_num_rows($result) > 0):
                                                while ($row = mysqli_fetch_assoc($result)):
                                            ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td>
                                                            <div class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></div>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['nip']); ?></td>
                                                        <td>
                                                            <?php
                                                            $badge_class = [
                                                                'admin' => 'bg-danger',
                                                                'guru' => 'bg-info',
                                                                'siswa' => 'bg-success'
                                                            ];
                                                            ?>
                                                            <span class="badge <?php echo $badge_class[$row['role']] ?? 'bg-secondary'; ?> bg-opacity-10 text-<?php echo $row['role'] === 'admin' ? 'danger' : ($row['role'] === 'guru' ? 'info' : 'success'); ?>">
                                                                <?php echo ucfirst($row['role']); ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="?edit=<?php echo $row['id_user']; ?>" class="btn btn-outline-warning" title="Edit">
                                                                    <i class="bi bi-pencil-fill"></i>
                                                                </a>
                                                                <a href="?hapus=<?php echo $row['id_user']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?')" class="btn btn-outline-danger" title="Hapus">
                                                                    <i class="bi bi-trash-fill"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="bi bi-person-x fs-4"></i>
                                                            <p class="mt-2 mb-0">Belum ada data pengguna</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script>
        // Validasi form
        (function() {
            'use strict'

            const forms = document.querySelectorAll('.needs-validation')

            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>

</body>

</html>