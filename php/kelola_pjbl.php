<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id_courses']) || !is_numeric($_GET['id_courses'])) {
    die("ID course tidak valid.");
}
$id_courses = (int) $_GET['id_courses'];

// CREATE atau UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_project = mysqli_real_escape_string($koneksi, $_POST['nama_project']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    if (isset($_POST['id_project']) && is_numeric($_POST['id_project'])) {
        // UPDATE
        $id_project = (int) $_POST['id_project'];
        $query = "UPDATE pjbl_project SET nama_project='$nama_project', deskripsi='$deskripsi' WHERE id_project=$id_project AND id_courses=$id_courses";
    } else {
        // CREATE
        $query = "INSERT INTO pjbl_project (id_courses, nama_project, deskripsi) VALUES ($id_courses, '$nama_project', '$deskripsi')";
    }
    mysqli_query($koneksi, $query);
    header("Location: kelola_pjbl.php?id_courses=$id_courses");
    exit;
}

// DELETE
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id_project = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pjbl_project WHERE id_project = $id_project AND id_courses = $id_courses");
    header("Location: kelola_pjbl.php?id_courses=$id_courses");
    exit;
}

// EDIT: ambil data jika ada parameter edit
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $id_project = (int) $_GET['edit'];
    $result = mysqli_query($koneksi, "SELECT * FROM pjbl_project WHERE id_project = $id_project AND id_courses = $id_courses");
    $edit_data = mysqli_fetch_assoc($result);
}

// Ambil semua proyek
$pjbl = [];
$query = mysqli_query($koneksi, "SELECT * FROM pjbl_project WHERE id_courses = $id_courses ORDER BY id_project DESC");
while ($row = mysqli_fetch_assoc($query)) {
    $pjbl[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detail Pertemuan | Belajaro</title>
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

        .hover-effect:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
            box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.1) !important;
        }

        .card {
            transition: all 0.3s ease;
            border-radius: 0.75rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1)
        }

        .card-header {
            border-radius: 0.75rem 0.75rem 0 0 !important;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
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
                        <!-- Header Section -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h1 class="fw-bold mb-2">
                                    💡 Kelola Proyek PjBL
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="kelola_pertemuan.php?id_courses=<?= $id_courses ?>">Pertemuan</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">Proyek PjBL</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Form Tambah/Edit -->
                        <div class="card border-0 shadow-sm rounded-0 mb-4">
                            <div class="card-header bg-white border-0 fw-semibold d-flex align-items-center">
                                <i class="bi bi-pencil-square me-2 text-primary"></i>
                                <?= $edit_data ? "Edit Proyek" : "Tambah Proyek Baru" ?>
                            </div>
                            <div class="card-body pt-3">
                                <form method="post">
                                    <?php if ($edit_data): ?>
                                        <input type="hidden" name="id_project" value="<?= $edit_data['id_project'] ?>">
                                    <?php endif; ?>
                                    <div class="mb-3">
                                        <label for="nama_project" class="form-label fw-semibold">Nama Proyek</label>
                                        <input type="text" class="form-control" id="nama_project" name="nama_project" required value="<?= $edit_data['nama_project'] ?? '' ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="deskripsi" class="form-label fw-semibold">Deskripsi</label>
                                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= $edit_data['deskripsi'] ?? '' ?></textarea>
                                    </div>
                                    <!-- Tombol Submit dan Batal -->
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-outline-success rounded-pill shadow-sm px-4">
                                            <i class="bi bi-check-circle-fill me-1"></i><?= $edit_data ? "Simpan Perubahan" : "Tambah Proyek" ?>
                                        </button>
                                        <?php if ($edit_data): ?>
                                            <a href="kelola_pjbl.php?id_courses=<?= $id_courses ?>" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                                                <i class="bi bi-x-circle me-1"></i>Batal
                                            </a>
                                        <?php endif ?>
                                    </div>

                                </form>
                            </div>
                        </div>

                        <!-- Daftar Proyek -->
                        <?php if (count($pjbl) === 0): ?>
                            <div class="text-center py-5 my-5 bg-light rounded-2">
                                <i class="bi bi-folder-x display-5 text-muted mb-4"></i>
                                <h4 class="fw-light">Belum ada proyek PjBL</h4>
                                <p class="text-muted mb-4">Mulailah dengan menambahkan proyek pertama Anda</p>
                            </div>
                        <?php else: ?>
                            <div class="card border-0 shadow-sm rounded-0">
                                <div class="card-body pt-3">
                                    <h5 class="fw-semibold mb-3">
                                        <i class="bi bi-list-task me-2"></i>Daftar Proyek
                                    </h5>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 30%;">Nama Proyek</th>
                                                    <th>Deskripsi</th>
                                                    <th class="text-end" style="width: 30%;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($pjbl as $p): ?>
                                                    <tr>
                                                        <td class="fw-semibold"><?= htmlspecialchars($p['nama_project']) ?></td>
                                                        <td><?= nl2br(htmlspecialchars($p['deskripsi'])) ?></td>
                                                        <!-- Tombol Aksi di Tabel -->
                                                        <td class="text-end">
                                                            <a href="kelola_pjbl.php?id_courses=<?= $id_courses ?>&edit=<?= $p['id_project'] ?>"
                                                                class="btn btn-outline-primary rounded-pill shadow-sm px-4 me-1 mb-1">
                                                                <i class="bi bi-pencil"></i> Edit
                                                            </a>
                                                            <a href="kelola_pjbl.php?id_courses=<?= $id_courses ?>&hapus=<?= $p['id_project'] ?>"
                                                                class="btn btn-outline-danger rounded-pill shadow-sm px-4 me-1 mb-1"
                                                                onclick="return confirm('Hapus proyek ini?')">
                                                                <i class="bi bi-trash"></i> Hapus
                                                            </a>
                                                            <a href="atur_kelompok.php?id_project=<?= $p['id_project'] ?>"
                                                                class="btn btn-outline-secondary rounded-pill shadow-sm px-4 mb-1">
                                                                <i class="bi bi-people"></i> Kelompok
                                                            </a>
                                                        </td>

                                                    </tr>
                                                <?php endforeach ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endif ?>
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
</body>

</html>