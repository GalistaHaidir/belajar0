<?php
require 'koneksi.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$id_courses = $_GET['id_courses'] ?? null;
if (!$id_courses) {
    echo "ID kursus tidak ditemukan.";
    exit();
}

// Ambil data project PjBL dalam kursus ini
$projects = mysqli_query($koneksi, "
    SELECT * FROM pjbl_project 
    WHERE id_courses = '$id_courses'
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Progres PjBL | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                                    🤝 Progres PjBL
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="daftar_kursus.php" class="text-decoration-none">Daftar Kursus</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Progress PJBL</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <?php while ($project = mysqli_fetch_assoc($projects)): ?>
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white border-bottom fw-semibold">
                                    <i class="bi bi-folder2-open me-2 text-primary"></i><?= htmlspecialchars($project['nama_project']) ?>
                                    <div class="small text-muted mt-1">Periode: <?= $project['tanggal_mulai'] ?> s.d. <?= $project['tanggal_selesai'] ?></div>
                                </div>

                                <div class="card-body">
                                    <?php
                                    // Ambil kelompok untuk project ini
                                    $kelompok = mysqli_query($koneksi, "SELECT * FROM kelompok WHERE id_project = '{$project['id_project']}'");

                                    // Hitung total tugas PjBL dalam kursus ini
                                    $q_total_tugas = mysqli_query($koneksi, "
                    SELECT COUNT(*) as total FROM meeting_contents 
                    WHERE id_meeting IN (
                        SELECT id_meeting FROM course_meetings WHERE id_courses = '$id_courses'
                    ) AND type = 'pjbl' AND id_project = '{$project['id_project']}'
                ");
                                    $total_tugas = mysqli_fetch_assoc($q_total_tugas)['total'] ?? 0;

                                    if ($total_tugas == 0): ?>
                                        <div class="text-muted">📂 Belum ada tugas PjBL untuk proyek ini.</div>
                                    <?php else: ?>
                                        <?php while ($k = mysqli_fetch_assoc($kelompok)):
                                            $q_terkumpul = mysqli_query($koneksi, "
                            SELECT COUNT(*) as terkumpul FROM pengumpulan_tugas 
                            WHERE id_kelompok = '{$k['id_kelompok']}' AND id_content IN (
                                SELECT id_content FROM meeting_contents 
                                WHERE id_meeting IN (
                                    SELECT id_meeting FROM course_meetings WHERE id_courses = '$id_courses'
                                ) AND type = 'pjbl' AND id_project = '{$project['id_project']}'
                            )
                        ");
                                            $terkumpul = mysqli_fetch_assoc($q_terkumpul)['terkumpul'] ?? 0;
                                            $persen = $total_tugas > 0 ? round(($terkumpul / $total_tugas) * 100) : 0;
                                        ?>
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between mb-1">
                                                    <span class="fw-semibold">
                                                        <i class="bi bi-people me-1 text-secondary"></i><?= htmlspecialchars($k['nama_kelompok']) ?>
                                                    </span>
                                                    <span class="text-muted small"><?= $terkumpul ?>/<?= $total_tugas ?> tugas</span>
                                                </div>
                                                <div class="progress rounded-pill" style="height: 18px;">
                                                    <div class="progress-bar bg-success fw-semibold" role="progressbar" style="width: <?= $persen ?>%;">
                                                        <?= $persen ?>%
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
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