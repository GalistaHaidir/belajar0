<?php
session_start();
include 'koneksi.php';

// Inisialisasi
$mode = 'tambah';
$id_courses = '';
$course_name = '';
$description = '';

// Simpan atau Edit Kelas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_courses = $_POST['id_courses'];
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];

    if ($id_courses == '') {
        // Tambah
        mysqli_query($koneksi, "INSERT INTO courses (course_name, description) VALUES ('$course_name', '$description')");
        header("Location: manajemen_kelas.php?message=Kelas berhasil ditambahkan");
        exit();
    } else {
        // Edit
        mysqli_query($koneksi, "UPDATE courses SET course_name='$course_name', description='$description' WHERE id_courses='$id_courses'");
        header("Location: manajemen_kelas.php?message=Kelas berhasil diperbarui");
        exit();
    }
}

// Ambil data untuk edit
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $result = mysqli_query($koneksi, "SELECT * FROM courses WHERE id_courses='$edit_id'");
    $data = mysqli_fetch_assoc($result);
    if ($data) {
        $mode = 'edit';
        $id_courses = $data['id_courses'];
        $course_name = $data['course_name'];
        $description = $data['description'];
    }
}

// Hapus kelas
if (isset($_GET['hapus'])) {
    $hapus_id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM courses WHERE id_courses='$hapus_id'");
    mysqli_query($koneksi, "DELETE FROM course_participants WHERE id_courses='$hapus_id'"); // hapus juga partisipan terkait
    header("Location: manajemen_kelas.php?message=Kelas berhasil dihapus");
    exit();
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Management Kelas | Belajaro</title>
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
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-sm-flex justify-content-between align-items-center">
                                    <div class="mb-3 mb-sm-0">
                                        <h1 class="fw-bold mb-1">👩‍🏫 Manajemen Kelas</h1>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Notifikasi -->
                        <?php if (isset($_GET['message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $_GET['message']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Form Kelas -->
                        <div class="card shadow-sm mb-4 border-0">
                            <div class="card-header bg-white border-0 py-3">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="bi <?php echo $mode === 'edit' ? 'bi-pencil-square text-warning' : 'bi-plus-circle text-success'; ?> me-2"></i>
                                    <?php echo $mode === 'edit' ? 'Edit Kelas' : 'Tambah Kelas Baru'; ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="row g-3 needs-validation" novalidate>
                                    <input type="hidden" name="id_courses" value="<?php echo $id_courses; ?>">

                                    <div class="col-md-6">
                                        <label for="course_name" class="form-label fw-medium">Nama Kelas</label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-book"></i></span>
                                            <input type="text" id="course_name" name="course_name" class="form-control" required value="<?php echo $course_name; ?>">
                                            <div class="invalid-feedback">
                                                Harap isi nama kelas
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="description" class="form-label fw-medium">Deskripsi</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-text-paragraph"></i></span>
                                            <input type="text" id="description" name="description" class="form-control" value="<?php echo $description; ?>">
                                        </div>
                                    </div>

                                    <div class="col-12 mt-4">
                                        <button type="submit" class="btn btn-success px-4">
                                            <i class="bi <?php echo $mode === 'edit' ? 'bi-check-circle' : 'bi-save'; ?> me-1"></i>
                                            <?php echo $mode === 'edit' ? 'Perbarui Kelas' : 'Simpan Kelas'; ?>
                                        </button>
                                        <?php if ($mode === 'edit'): ?>
                                            <a href="manajemen_kelas.php" class="btn btn-outline-secondary px-4 ms-2">
                                                <i class="bi bi-x-circle me-1"></i> Batal
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Tabel Kelas -->
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="bi bi-list-check me-2 text-primary"></i> Daftar Kelas
                                </h5>
                                <?php
                                // Query untuk mendapatkan total kelas
                                $total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM courses");
                                $total_data = mysqli_fetch_assoc($total_query);
                                ?>
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    Total: <?php echo $total_data['total']; ?> Kelas
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50" class="text-center">No</th>
                                                <th>Nama Kelas</th>
                                                <th>Deskripsi</th>
                                                <th width="250">Peserta</th>
                                                <th width="120" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Query untuk data kelas
                                            $result = mysqli_query($koneksi, "SELECT * FROM courses ORDER BY course_name");
                                            $no = 1;

                                            if ($result && mysqli_num_rows($result) > 0):
                                                while ($row = mysqli_fetch_assoc($result)):
                                                    $id = $row['id_courses'];
                                                    $q_partisipan = mysqli_query($koneksi, "
            SELECT COUNT(*) AS jumlah 
            FROM course_participants 
            WHERE id_courses = '$id'
        ");
                                                    $data_partisipan = mysqli_fetch_assoc($q_partisipan);
                                                    $jumlah_partisipan = $data_partisipan['jumlah'];
                                            ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $no++; ?></td>
                                                        <td>
                                                            <div class="fw-medium"><?php echo htmlspecialchars($row['course_name']); ?></div>
                                                        </td>
                                                        <td>
                                                            <?php if (!empty($row['description'])): ?>
                                                                <span class="text-muted"><?php echo htmlspecialchars($row['description']); ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted fst-italic">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if ($jumlah_partisipan > 0): ?>
                                                                <span class="badge bg-primary rounded-pill"><?php echo $jumlah_partisipan; ?> Partisipan</span>
                                                            <?php else: ?>
                                                                <span class="text-muted fst-italic">Belum ada peserta</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <a href="atur_partisipan.php?id_courses=<?php echo $id; ?>" class="btn btn-outline-info">
                                                                    <i class="bi bi-people-fill"></i> Atur Partisipan
                                                                </a>
                                                                <a href="?edit=<?php echo $id; ?>" class="btn btn-outline-warning" title="Edit">
                                                                    <i class="bi bi-pencil-fill"></i> Edit
                                                                </a>
                                                                <a href="?hapus=<?php echo $id; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')" class="btn btn-outline-danger" title="Hapus">
                                                                    <i class="bi bi-trash-fill"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="5" class="text-center py-4">
                                                        <div class="text-muted">
                                                            <i class="bi bi-folder-x fs-4"></i>
                                                            <p class="mt-2 mb-0">Belum ada data kelas</p>
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