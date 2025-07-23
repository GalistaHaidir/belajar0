<?php
session_start(); // Memulai session PHP
include 'koneksi.php'; // Menghubungkan ke file koneksi database

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Jika belum login, redirect ke login.php
    exit();
}

// Cek apakah role user adalah guru atau admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin')) {
    header("Location: login.php"); // Jika bukan guru/admin, redirect ke login.php
    exit();
}

$id_courses = $_GET['id_courses']; // Ambil ID kelas dari URL
if (!$id_courses) {
    die("ID kelas tidak ditemukan."); // Jika tidak ada ID, hentikan eksekusi
}

// ========== TAMBAH PARTISIPAN ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_user'])) {
    $id_user = $_POST['id_user']; // Ambil ID user dari form POST

    // Cek apakah user tersebut sudah menjadi partisipan
    $cek = mysqli_query($koneksi, "SELECT * FROM course_participants WHERE id_courses='$id_courses' AND id_user='$id_user'");

    if (mysqli_num_rows($cek) === 0) {
        // Jika belum, tambahkan ke tabel partisipan
        mysqli_query($koneksi, "INSERT INTO course_participants (id_user, id_courses) VALUES ('$id_user', '$id_courses')");
    }

    // Redirect ke halaman yang sama agar form tidak dikirim ulang saat di-refresh
    header("Location: atur_partisipan.php?id_courses=$id_courses");
    exit();
}

// ========== HAPUS PARTISIPAN ==========
if (isset($_GET['hapus'])) {
    $id_user = $_GET['hapus']; // Ambil ID user dari parameter GET
    // Hapus user dari tabel partisipan
    mysqli_query($koneksi, "DELETE FROM course_participants WHERE id_courses='$id_courses' AND id_user='$id_user'");
    // Redirect kembali
    header("Location: atur_partisipan.php?id_courses=$id_courses");
    exit();
}

// ========== INFO KELAS ==========
$kelas = mysqli_fetch_assoc(
    mysqli_query($koneksi, "SELECT * FROM courses WHERE id_courses='$id_courses'")
);
// Ambil informasi detail tentang kelas berdasarkan ID
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Atur Partisipan | Belajaro</title>
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
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h1 class="fw-bold mb-1">
                                    🧑‍🤝‍🧑 Atur Partisipan Kelas: <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary"><?php echo htmlspecialchars($kelas['course_name']); ?></span>
                                </h1>
                                <p class="text-muted mb-0">Kelola peserta dan pengajar dalam kelas ini</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Tambah Partisipan -->
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-semibold">
                                <i class="bi bi-person-plus-fill text-success me-2"></i>
                                Tambah Partisipan ke Kelas
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="row g-3 needs-validation" novalidate>
                                <div class="col-md-5">
                                    <label for="id_user" class="form-label fw-medium">Pilih Pengguna</label>
                                    <select id="id_user" name="id_user" class="form-select" required>
                                        <option value="">-- Pilih Pengguna --</option>
                                        <?php
                                        $pengguna = mysqli_query($koneksi, "
                                SELECT * FROM users 
                                WHERE id_user NOT IN (
                                    SELECT id_user FROM course_participants WHERE id_courses = '$id_courses'
                                ) ORDER BY role, name
                            ");
                                        while ($u = mysqli_fetch_assoc($pengguna)) {
                                            echo "<option value='{$u['id_user']}'>{$u['name']} ({$u['role']})</option>";
                                        }
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Harap pilih pengguna
                                    </div>
                                </div>
                                <div class="col-md-2 align-self-end">
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="bi bi-plus-circle me-1"></i> Tambah
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabel Partisipan -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold">
                                <i class="bi bi-list-check text-primary me-2"></i>
                                Daftar Partisipan
                            </h5>
                            <?php
                            $total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM course_participants WHERE id_courses = '$id_courses'");
                            $total_data = mysqli_fetch_assoc($total_query);
                            ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-people me-1"></i>
                                Total: <?php echo $total_data['total']; ?> Partisipan
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
                                            <th width="120">Role Akun</th>
                                            <th width="100" class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $partisipan = mysqli_query($koneksi, "
                                    SELECT u.* FROM course_participants cp
                                    JOIN users u ON cp.id_user = u.id_user
                                    WHERE cp.id_courses = '$id_courses'
                                    ");
                                        $no = 1;

                                        if ($partisipan && mysqli_num_rows($partisipan) > 0):
                                            while ($row = mysqli_fetch_assoc($partisipan)):
                                                $role_badge = [
                                                    'admin' => ['bg-danger', 'text-danger'],
                                                    'guru' => ['bg-info', 'text-info'],
                                                    'siswa' => ['bg-success', 'text-success']
                                                ];
                                        ?>
                                                <tr>
                                                    <td class="text-center"><?php echo $no++; ?></td>
                                                    <td>
                                                        <div class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                    <td>
                                                        <span class="badge <?php echo $role_badge[$row['role']][0] ?? 'bg-secondary'; ?> bg-opacity-10 <?php echo $role_badge[$row['role']][1] ?? 'text-secondary'; ?>">
                                                            <?php echo ucfirst($row['role']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="?id_courses=<?php echo $id_courses; ?>&hapus=<?php echo $row['id_user']; ?>"
                                                            class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus partisipan ini dari kelas?')"
                                                            title="Hapus dari kelas">
                                                            <i class="bi bi-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="text-muted">
                                                        <i class="bi bi-person-x fs-4"></i>
                                                        <p class="mt-2 mb-0">Belum ada partisipan dalam kelas ini</p>
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