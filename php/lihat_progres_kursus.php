<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id_courses'])) {
    echo "ID kursus tidak ditemukan.";
    exit();
}

$id_courses = intval($_GET['id_courses']);

// Ambil semua siswa di kursus ini (tanpa guru)
$qSiswa = $koneksi->prepare("SELECT u.id_user, u.name 
    FROM users u 
    JOIN course_participants cp ON u.id_user = cp.id_user 
    WHERE cp.id_courses = ? AND u.role = 'siswa'");
$qSiswa->bind_param("i", $id_courses);
$qSiswa->execute();
$resultSiswa = $qSiswa->get_result();
$siswaList = $resultSiswa->fetch_all(MYSQLI_ASSOC);

// Ambil semua id_content dari semua pertemuan di kursus ini
$queryKonten = $koneksi->prepare("SELECT mc.id_content 
    FROM meeting_contents mc
    JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
    WHERE cm.id_courses = ?");
$queryKonten->bind_param("i", $id_courses);
$queryKonten->execute();
$resKonten = $queryKonten->get_result();
$idKonten = [];
while ($row = $resKonten->fetch_assoc()) {
    $idKonten[] = $row['id_content'];
}

$totalKonten = count($idKonten);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Progres Kursus | Belajaro</title>
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
                                    📊 Progres Kursus
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="daftar_kursus.php">Daftar Kursus</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Progress Kursus</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Konten -->
                        <?php if ($totalKonten === 0): ?>
                            <div class="text-center py-5 my-5 bg-light rounded-2">
                                <i class="bi bi-folder-x display-5 text-muted mb-4"></i>
                                <h4 class="fw-light">Belum ada konten dalam kursus ini</h4>
                                <p class="text-muted">Tambahkan konten ke pertemuan agar siswa dapat mulai belajar</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Siswa</th>
                                            <th>Konten Diselesaikan</th>
                                            <th style="width: 30%;">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($siswaList as $siswa): ?>
                                            <?php
                                            $id_user = $siswa['id_user'];
                                            $stmt = $koneksi->prepare("SELECT COUNT(*) as selesai 
                            FROM content_activity 
                            WHERE id_user = ? 
                            AND id_content IN (" . implode(",", $idKonten) . ") 
                            AND status IN ('opened', 'submitted')");
                                            $stmt->bind_param("i", $id_user);
                                            $stmt->execute();
                                            $res = $stmt->get_result()->fetch_assoc();
                                            $selesai = $res['selesai'] ?? 0;

                                            $persen = $totalKonten > 0 ? round(($selesai / $totalKonten) * 100) : 0;
                                            ?>
                                            <tr>
                                                <td class="fw-semibold"><?= htmlspecialchars($siswa['name']) ?></td>
                                                <td><?= $selesai ?> / <?= $totalKonten ?></td>
                                                <td>
                                                    <div class="progress rounded-pill" style="height: 16px;">
                                                        <div class="progress-bar <?= $persen == 100 ? 'bg-success' : 'bg-info' ?>"
                                                            role="progressbar"
                                                            style="width: <?= $persen ?>%;"
                                                            aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100">
                                                            <?= $persen ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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