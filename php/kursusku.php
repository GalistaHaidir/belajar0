<?php
session_start();
require_once 'koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    die('Anda harus login terlebih dahulu.');
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['user_id'];


// Ambil daftar kursus yang diikuti siswa
$sql = "SELECT co.id_courses, co.course_name, co.description
        FROM courses co
        JOIN course_participants cp ON cp.id_courses = co.id_courses
        WHERE cp.id_user = ?";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

$kursus = [];
while ($row = $result->fetch_assoc()) {
    $kursus[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Kursus | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">

    <style>
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

        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            font-family: 'Poppins', sans-serif;
        }

        /* Styling Container Utama */
        .main-content {
            padding: 20px;
        }

        .course-card {
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .badge.bg-success-subtle {
            background-color: #d1e7dd;
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
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="fw-bold mb-2">📤 Kursus yang Saya Ikuti</h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <!-- <li class="breadcrumb-item"><a href="kelola_konten.php?id_meeting=<?= $data_konten['id_meeting'] ?>">Konten</a></li> -->
                                        <li class="breadcrumb-item active">Kursusku</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <?php if (empty($kursus)): ?>
                            <div class="alert alert-light border text-center p-4">
                                <i class="bi bi-emoji-frown fs-2 text-muted"></i>
                                <p class="mt-2 mb-0">Anda belum terdaftar dalam kursus apa pun.</p>
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($kursus as $k): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 shadow-sm border-0 course-card hover-shadow">
                                            <!-- Header Gambar Ilustratif -->
                                            <div class="ratio ratio-16x9 bg-secondary bg-opacity-10 rounded-top">
                                                <div class="d-flex justify-content-center align-items-center h-100">
                                                    <i class="bi bi-mortarboard fs-1 text-primary"></i>
                                                </div>
                                            </div>

                                            <!-- Konten -->
                                            <div class="card-body">
                                                <h5 class="card-title fw-semibold text-dark">
                                                    <?= htmlspecialchars($k['course_name']); ?>
                                                </h5>
                                                <span class="badge bg-success-subtle text-success mb-2">Kursus</span>
                                                <p class="card-text small text-muted">
                                                    <?= nl2br(htmlspecialchars($k['description'])); ?>
                                                </p>
                                            </div>

                                            <!-- Footer Aksi -->
                                            <div class="card-footer bg-white border-0">
                                                <a href="pertemuan.php?id_courses=<?= $k['id_courses']; ?>" class="btn btn-outline-primary w-100">
                                                    <i class="bi bi-calendar-week me-1"></i> Lihat Pertemuan
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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