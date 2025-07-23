<?php
include 'koneksi.php';
session_start();


// Cek login (opsional, tergantung sistemmu)
if (!isset($_SESSION['user_id'])) {
    die('Anda harus login terlebih dahulu.');
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$id_content = $_GET['id_content'] ?? null;
if (!$id_content) {
    die('ID konten quiz tidak ditemukan.');
}

// Ambil info quiz
$query_quiz = mysqli_query($koneksi, "SELECT id_content, title, id_meeting FROM meeting_contents WHERE id_content = '$id_content' AND type = 'quiz'");
$quiz = mysqli_fetch_assoc($query_quiz);
if (!$quiz) {
    die('Konten quiz tidak valid.');
}

$id_meeting = $quiz['id_meeting'];
$id_quiz = $quiz['id_content']; // untuk tombol export dan detail

// Ambil hasil quiz
$query_hasil = mysqli_query($koneksi, "
    SELECT u.id_user, u.name, u.email, q.score, q.submitted_at 
    FROM quiz_result q
    JOIN users u ON q.id_user = u.id_user
    WHERE q.id_content = '$id_content'
    ORDER BY q.submitted_at DESC
");
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lihat Hasil Quiz | Belajaro</title>
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

        .symbol {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .symbol-circle {
            border-radius: 50%;
        }

        .symbol-40 {
            width: 40px;
            height: 40px;
        }

        .symbol-label {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            width: 100%;
            height: 100%;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
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
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h1 class="mb-2 text-capitalize">
                                    💯 Hasil: <?= htmlspecialchars($quiz['title']) ?>
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="kelola_konten.php?id_meeting=<?= $id_meeting ?>" class="text-decoration-none">Kelola Konten</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">Hasil Quiz</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Results Section -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white py-3 border-bottom-0">
                                <h5 class="card-title mb-0 fw-semibold">
                                    <i class="bi bi-table me-2"></i>Daftar Hasil Quiz
                                </h5>
                            </div>

                            <div class="card-body p-0">
                                <?php if (mysqli_num_rows($query_hasil) > 0): ?>
                                    <div class="table-responsive">
                                        <table class="table table-borderless align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-center" style="width: 60px;">No</th>
                                                    <th>Nama Siswa</th>
                                                    <th class="text-center" style="width: 100px;">Skor</th>
                                                    <th class="text-center" style="width: 180px;">Waktu Submit</th>
                                                    <th class="text-center" style="width: 140px;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $no = 1;
                                                while ($row = mysqli_fetch_assoc($query_hasil)): ?>
                                                    <tr>
                                                        <td class="text-center"><?= $no++ ?></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="symbol symbol-40 symbol-circle me-3">
                                                                    <span class="symbol-label bg-primary bg-opacity-10 text-primary fw-bold">
                                                                        <?= substr(htmlspecialchars($row['name']), 0, 1) ?>
                                                                    </span>
                                                                </div>
                                                                <div>
                                                                    <div class="fw-semibold"><?= htmlspecialchars($row['name']) ?></div>
                                                                    <div class="text-muted small"><?= htmlspecialchars($row['email'] ?? '-') ?></div>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <span class="badge bg-<?= ($row['score'] >= 80) ? 'success' : (($row['score'] >= 60) ? 'warning' : 'danger') ?> bg-opacity-10 text-<?= ($row['score'] >= 80) ? 'success' : (($row['score'] >= 60) ? 'warning' : 'danger') ?> p-2 w-100">
                                                                <?= htmlspecialchars($row['score']) ?>
                                                            </span>
                                                        </td>
                                                        <td class="text-center text-muted">
                                                            <i class="bi bi-clock-history me-1"></i>
                                                            <?= date('d M Y H:i', strtotime(htmlspecialchars($row['submitted_at']))) ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="detail_jawaban.php?id_user=<?= $row['id_user'] ?>&id_quiz=<?= $id_quiz ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                                <i class="bi bi-eye"></i> Detail
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <div class="mb-3">
                                            <i class="bi bi-emoji-frown display-4 text-muted"></i>
                                        </div>
                                        <h5 class="text-muted">Belum ada hasil quiz</h5>
                                        <p class="text-muted mb-4">Siswa belum mengumpulkan jawaban untuk quiz ini.</p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (mysqli_num_rows($query_hasil) > 0): ?>
                                <div class="card-footer bg-white py-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-muted small">
                                            Menampilkan total <?= mysqli_num_rows($query_hasil) ?> hasil
                                        </div>
                                        <div>
                                            <a href="export_excel.php?id_quiz=<?= $id_quiz ?>" class="btn btn-outline-success rounded-pill shadow-sm">
                                                <i class="bi bi-download me-1"></i>Export Excel
                                            </a>

                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>