<?php
session_start();
require_once 'koneksi.php'; // koneksi ke database

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}


$id_user = $_SESSION['user_id'];

// Ambil semua pertemuan dari kelas yang diikuti siswa
$sql = "SELECT cm.id_meeting, cm.meeting_number, cm.title AS meeting_title, cm.description, 
               co.course_name
        FROM course_meetings cm
        JOIN courses co ON cm.id_courses = co.id_courses
        JOIN course_participants cp ON cp.id_courses = cm.id_courses
        WHERE cp.id_user = ?
        ORDER BY cm.meeting_number ASC";

$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();

$pertemuan = [];
while ($row = $result->fetch_assoc()) {
    $pertemuan[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pertemuan | Belajaro</title>
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

        .meeting-card-full {
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .meeting-card-full:hover {
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
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
                                <h1 class="fw-bold mb-2">📤 Daftar Pertemuan</h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item active"><a href="kursusku.php">Kursusku</a></li>
                                        <li class="breadcrumb-item active">Daftar Pertemuan</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <?php if (count($pertemuan) === 0): ?>
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                                <div>Belum ada pertemuan yang tersedia.</div>
                            </div>
                        <?php else: ?>
                            <?php
                            function hitungProgressPertemuan($koneksi, $id_meeting, $id_user)
                            {
                                $q_total = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM meeting_contents WHERE id_meeting = $id_meeting");
                                $t = mysqli_fetch_assoc($q_total)['total'];

                                $q_selesai = mysqli_query($koneksi, "
        SELECT COUNT(*) as selesai FROM content_activity 
        WHERE id_user = $id_user 
        AND id_content IN (
            SELECT id_content FROM meeting_contents WHERE id_meeting = $id_meeting
        )
        AND status IN ('opened', 'submitted', 'completed')
    ");
                                $s = mysqli_fetch_assoc($q_selesai)['selesai'];

                                return ($t > 0) ? round(($s / $t) * 100) : 0;
                            }

                            ?>

                            <div class="row g-4">
                                <?php foreach ($pertemuan as $p): ?>
                                    <div class="col-12">
                                        <div class="card shadow-sm border-0 p-3 d-flex flex-column flex-md-row align-items-start gap-3 meeting-card-full">
                                            <!-- Ikon / Nomor Pertemuan -->
                                            <div class="bg-primary bg-opacity-10 rounded-circle d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                                <span class="text-primary fw-bold fs-5"><?= $p['meeting_number']; ?></span>
                                            </div>

                                            <!-- Isi Konten -->
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1 fw-semibold text-primary">
                                                    <?= htmlspecialchars($p['meeting_title']); ?>
                                                </h5>
                                                <h6 class="text-muted mb-2">
                                                    <i class="bi bi-bookmark-check me-1"></i><?= htmlspecialchars($p['course_name']); ?>
                                                </h6>
                                                <p class="text-secondary small mb-2">
                                                    <?= nl2br(htmlspecialchars($p['description'])); ?>
                                                </p>

                                                <?php
                                                $progress = hitungProgressPertemuan($koneksi, $p['id_meeting'], $_SESSION['user_id']);
                                                ?>

                                                <div class="mb-2">
                                                    <label class="form-label small mb-1">Progress</label>
                                                    <div class="progress" style="height: 10px;">
                                                        <div class="progress-bar bg-success" role="progressbar"
                                                            style="width: <?= $progress ?>%;" aria-valuenow="<?= $progress ?>" aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                    <small class="text-muted"><?= $progress ?>%</small>
                                                </div>

                                            </div>

                                            <!-- Tombol Aksi -->
                                            <div class="ms-md-auto mt-3 mt-md-0">
                                                <a href="detail_pertemuan.php?id_meeting=<?= $p['id_meeting']; ?>" class="btn btn-primary">
                                                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk Pertemuan
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