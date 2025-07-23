<?php
session_start(); // Memulai session untuk melacak login user
include 'koneksi.php'; // Menyertakan file koneksi ke database

// ========== CEK LOGIN USER ==========

// Cek apakah user sudah login (session user_id ada atau tidak)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Jika belum login, redirect ke halaman login
    exit(); // Hentikan eksekusi skrip
}

// Cek apakah role user bukan 'guru' atau 'admin'
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php"); // Jika bukan guru atau admin, redirect ke login
    exit(); // Hentikan eksekusi skrip
}
?>



<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kursus | Belajaro</title>
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

        .hover-effect:hover {
            box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-footer {
            background-color: rgba(0, 0, 0, 0.01);
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
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h1 class="fw-bold mb-2">
                                    📘 Daftar Kursus yang Anda Ampu
                                </h1>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2">
                                    <i class="bi bi-person-badge me-1"></i>
                                    ID Guru: <?= $_SESSION['user_id'] ?>
                                </span>
                            </div>
                        </div>

                        <!-- Daftar Kursus -->
                        <div class="row g-4 mt-4">
                            <?php
                            $id_user = $_SESSION['user_id'];

                            $query = mysqli_query($koneksi, "
            SELECT c.id_courses, c.course_name, c.description
            FROM courses c
            JOIN course_participants cp ON c.id_courses = cp.id_courses
            WHERE cp.id_user = '$id_user'
        ");

                            if (mysqli_num_rows($query) > 0):
                                while ($row = mysqli_fetch_assoc($query)):
                            ?>
                                    <div class="col-md-4">
                                        <div class="card border-0 shadow-sm h-100 hover-effect rounded-0">
                                            <div class="card-header bg-white border-0 pb-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h5 class="mb-0 fw-semibold"><?= htmlspecialchars($row['course_name']) ?></h5>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                                                        ID: <?= $row['id_courses'] ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="card-body pt-2">
                                                <p class="card-text text-muted mb-0">
                                                    <?= nl2br(htmlspecialchars($row['description'])) ?>
                                                </p>
                                            </div>
                                            <div class="card-footer bg-white border-0 pt-0">
                                                <div class="d-grid gap-2">
                                                    <a href="kelola_pertemuan.php?id_courses=<?= $row['id_courses'] ?>"
                                                        class="btn btn-outline-primary rounded-pill shadow-sm px-4">
                                                        <i class="bi bi-door-open-fill me-1"></i></i> Masuk Kursus
                                                    </a>
                                                    <a href="lihat_progres_kursus.php?id_courses=<?= $row['id_courses'] ?>"
                                                        class="btn btn-outline-success rounded-pill shadow-sm px-4">
                                                        <i class="bi bi-bar-chart-line-fill me-1"></i> Lihat Progres Kursus
                                                    </a>
                                                    <a href="lihat_progres_pjbl.php?id_courses=<?= $row['id_courses'] ?>" class="btn btn-outline-warning rounded-pill shadow-sm px-4">
                                                        <i class="bi bi-clipboard2-check-fill me-1"></i> Lihat Progres PjBL
                                                    </a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <div class="col-12">
                                    <div class="text-center py-5 my-5 bg-light rounded-2">
                                        <i class="bi bi-journal-x display-5 text-muted mb-4"></i>
                                        <h4 class="fw-light">Belum ada kursus yang Anda ampu</h4>
                                        <p class="text-muted mb-4">Silakan hubungi admin untuk ditambahkan ke dalam kursus</p>
                                    </div>
                                </div>
                            <?php endif; ?>
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

</body>

</html>