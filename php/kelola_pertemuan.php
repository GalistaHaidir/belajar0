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

$id_user = $_SESSION['user_id'];

// Ambil id_courses dari parameter
if (!isset($_GET['id_courses'])) {
    echo "Kursus tidak ditemukan.";
    exit();
}

$id_courses = $_GET['id_courses'];

// Ambil data kursus
$qCourse = mysqli_query($koneksi, "SELECT * FROM courses WHERE id_courses = '$id_courses'");
$course = mysqli_fetch_assoc($qCourse);

// Ambil data pertemuan
$qPertemuan = mysqli_query($koneksi, "SELECT * FROM course_meetings WHERE id_courses = '$id_courses' ORDER BY meeting_number ASC");

if (isset($_GET['hapus'])) {
    $id_pert = intval($_GET['hapus']);
    $id_courses = intval($_GET['id_courses']);

    $stmt = $koneksi->prepare("DELETE FROM course_meetings WHERE id_meeting = ?");
    $stmt->bind_param("i", $id_pert);
    $hapus = $stmt->execute();

    if ($hapus) {
        header("Location: kelola_pertemuan.php?id_courses=$id_courses&hapus_success=1");
        exit();
    } else {
        header("Location: kelola_pertemuan.php?id_courses=$id_courses&hapus_error=1");
        exit();
    }
}

?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pertemuan | Belajaro</title>
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
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
            border-left-color: #0d6efd;
        }

        .card-header {
            padding: 1rem 1.5rem;
            background-color: #f8f9fa;
        }

        .card-body {
            padding: 1.5rem;
        }

        .dropdown-menu {
            z-index: 1060 !important;
            /* Higher than card z-index */
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            border: none;
        }

        .btn-primary {
            padding: 0.5rem 1.25rem;
        }

        @media (max-width: 768px) {
            .card-body .row>div {
                width: 100%;
            }

            .col-md-4 {
                margin-top: 1rem;
                justify-content: flex-start !important;
            }
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
                                <h1 class="fw-bold mb-2">
                                    📅 Pertemuan dalam Kursus:
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($course['course_name']) ?></span>
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="daftar_kursus.php">Daftar Kursus</a></li>
                                        <li class="breadcrumb-item active">Pertemuan</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2 px-3">
                            <div>
                                <a href="kelola_pjbl.php?id_courses=<?= $id_courses ?>" class="btn btn-warning me-2">
                                    <i class="bi bi-diagram-3 me-1"></i> Kelola PjBL
                                </a>
                                <a href="tambah_pertemuan.php?id_courses=<?= $id_courses ?>" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Pertemuan
                                </a>
                            </div>
                        </div>

                        <!-- Full Width Meeting Cards -->
                        <?php if (mysqli_num_rows($qPertemuan) > 0): ?>
                            <div class="row g-0">
                                <?php while ($p = mysqli_fetch_assoc($qPertemuan)): ?>
                                    <div class="col-12 mb-3">
                                        <div class="card border-0 shadow-sm rounded-0">
                                            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center pb-2"> <!-- Added pb-2 for bottom padding -->
                                                <div>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 me-2">
                                                        <i class="bi bi-hash me-1"></i> Pertemuan : <?= $p['meeting_number'] ?>
                                                    </span>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-outline-secondary rounded-circle" type="button"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li>
                                                            <a class="dropdown-item" href="lihat_progres.php?id_meeting=<?= $p['id_meeting'] ?>">
                                                                <i class="bi bi-graph-up me-2"></i> Lihat Progres
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <hr class="dropdown-divider">
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="tambah_pertemuan.php?id_courses=<?= $id_courses ?>&edit=<?= $p['id_meeting'] ?>">
                                                                <i class="bi bi-pencil me-2"></i> Edit
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item text-danger" href="kelola_pertemuan.php?hapus=<?= $p['id_meeting'] ?>&id_courses=<?= $id_courses ?>"
                                                                onclick="return confirm('Yakin ingin menghapus pertemuan ini?')">
                                                                <i class="bi bi-trash me-2"></i> Hapus
                                                            </a>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <div class="card-body pt-3"> <!-- Added pt-3 for top padding -->
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h5 class="card-title fw-semibold mb-2"><?= htmlspecialchars($p['title']) ?></h5> <!-- Increased mb-1 to mb-2 -->
                                                        <p class="card-text text-muted mb-0">
                                                            <?= !empty($p['description']) ? htmlspecialchars($p['description']) : '<span class="fst-italic">Tidak ada deskripsi</span>' ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-4 d-flex align-items-center justify-content-end">
                                                        <a href="kelola_konten.php?id_meeting=<?= $p['id_meeting'] ?>"
                                                            class="btn btn-primary">
                                                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5 my-5 bg-light">
                                <i class="bi bi-calendar-x display-5 text-muted mb-4"></i>
                                <h4 class="fw-light">Belum ada pertemuan</h4>
                                <p class="text-muted mb-4">Mulai dengan membuat pertemuan pertama Anda</p>
                                <a href="tambah_pertemuan.php?id_courses=<?= $id_courses ?>" class="btn btn-primary px-4">
                                    <i class="bi bi-plus-circle me-2"></i> Buat Pertemuan
                                </a>
                            </div>
                        <?php endif; ?>



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