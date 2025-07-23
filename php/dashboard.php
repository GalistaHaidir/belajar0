<?php
session_start(); // Memulai sesi PHP
include 'koneksi.php'; // Menghubungkan ke file koneksi database

// ========== CEK LOGIN USER ==========

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect ke login jika belum login
    exit();
}

// ========== AMBIL DATA USER DARI SESSION ==========
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$name = $_SESSION['name'];

// ========== JIKA USER ADALAH ADMIN ==========
$totalAdmin = 0;
$totalGuru = 0;
$totalSiswa = 0;

if ($role == 'admin') {
    // Hitung jumlah user berdasarkan role
    $qAdmin = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'admin'");
    $qGuru = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'guru'");
    $qSiswa = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM users WHERE role = 'siswa'");

    $totalAdmin = mysqli_fetch_assoc($qAdmin)['total'];
    $totalGuru = mysqli_fetch_assoc($qGuru)['total'];
    $totalSiswa = mysqli_fetch_assoc($qSiswa)['total'];

    // Hitung jumlah konten berdasarkan tipe
    $qMateri = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM meeting_contents WHERE type = 'materi'");
    $qSoal = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM meeting_contents WHERE type = 'quiz'");
    $qTugas = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM meeting_contents WHERE type IN ('tugas', 'pjbl')");

    $totalMateri = mysqli_fetch_assoc($qMateri)['total'];
    $totalSoal = mysqli_fetch_assoc($qSoal)['total'];
    $totalTugas = mysqli_fetch_assoc($qTugas)['total'];
}

// ========== JIKA USER ADALAH GURU ==========
$kelasDiampu = [];
if ($role == 'guru') {
    // Ambil semua kelas yang diikuti guru
    $qKelas = mysqli_query($koneksi, "
        SELECT c.id_courses, c.course_name, COUNT(u.id_user) as jumlah_siswa
        FROM courses c
        LEFT JOIN course_participants cp ON c.id_courses = cp.id_courses
        LEFT JOIN users u ON cp.id_user = u.id_user AND u.role = 'siswa'
        WHERE c.id_courses IN (
            SELECT id_courses FROM course_participants WHERE id_user = '$user_id'
        )
        GROUP BY c.id_courses
    ");

    while ($row = mysqli_fetch_assoc($qKelas)) {
        $kelasDiampu[] = $row; // Tambahkan ke array kelasDiampu
    }
}

// ========== AMBIL 5 TUGAS TERBARU YANG DIKUMPULKAN ==========
$tugasTerbaru = [];
$quizTerbaru = [];

$qQuiz = mysqli_query($koneksi, "
    SELECT qr.id_content, u.name AS nama, mc.title AS judul, qr.score
    FROM quiz_result qr
    JOIN users u ON qr.id_user = u.id_user
    JOIN meeting_contents mc ON qr.id_content = mc.id_content
    JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
    WHERE cm.id_courses IN (
        SELECT id_courses FROM course_participants WHERE id_user = '$user_id'
    )
    ORDER BY qr.submitted_at DESC
    LIMIT 5
");

while ($row = mysqli_fetch_assoc($qQuiz)) {
    $quizTerbaru[] = $row;
}

$qTugas = mysqli_query($koneksi, "
    SELECT pt.id_content, u.name AS nama, mc.title AS judul
    FROM pengumpulan_tugas pt
    JOIN users u ON pt.id_user = u.id_user
    JOIN meeting_contents mc ON pt.id_content = mc.id_content
    JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
    WHERE cm.id_courses IN (
        SELECT id_courses FROM course_participants WHERE id_user = '$user_id'
    )
    ORDER BY pt.waktu_kumpul DESC
    LIMIT 5
");

while ($row = mysqli_fetch_assoc($qTugas)) {
    $tugasTerbaru[] = $row;
}

// ========== CARI TUGAS YANG BELUM DIKUMPULKAN SISWA ==========
$tugasBelum = [];

$qTugasBelum = mysqli_query($koneksi, "
    SELECT DISTINCT mc.id_content, mc.title
    FROM meeting_contents mc
    JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
    JOIN course_participants cp ON cm.id_courses = cp.id_courses
    WHERE mc.type IN ('tugas', 'pjbl')
      AND cp.id_user = '$user_id'
      AND (
        -- Belum dikumpulkan secara individu
        mc.id_content NOT IN (
            SELECT id_content FROM pengumpulan_tugas WHERE id_user = '$user_id'
        )
        OR
        -- Belum dikumpulkan oleh kelompok siswa
        mc.id_content NOT IN (
            SELECT pt.id_content
            FROM pengumpulan_tugas pt
            JOIN anggota_kelompok ak ON pt.id_kelompok = ak.id_kelompok
            WHERE ak.id_user = '$user_id'
        )
    )
    ORDER BY mc.deadline ASC
    LIMIT 5
");

while ($row = mysqli_fetch_assoc($qTugasBelum)) {
    $tugasBelum[] = $row;
}
?>



<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | Belajaro</title>
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

        .card-modern {
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: none;
        }

        .list-group-item {
            border-left: 0;
            border-right: 0;
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
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="d-sm-flex justify-content-between align-items-center">
                                    <div class="mb-3 mb-sm-0">
                                        <h1 class="fw-bold mb-1">📚 Dashboard</h1>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Konten Admin -->
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <!-- Kartu Statistik -->
                            <div class="row g-4 mb-4">
                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="text-uppercase text-muted mb-2">Administrator</h6>
                                                    <h2 class="mb-0 fw-bold"><?php echo $totalAdmin; ?></h2>
                                                </div>
                                                <div class="bg-primary bg-opacity-10 p-3 rounded">
                                                    <i class="bi bi-people-fill fs-3 text-primary"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="text-uppercase text-muted mb-2">Guru</h6>
                                                    <h2 class="mb-0 fw-bold"><?php echo $totalGuru; ?></h2>
                                                </div>
                                                <div class="bg-info bg-opacity-10 p-3 rounded">
                                                    <i class="bi bi-person-badge fs-3 text-info"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body p-4">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="text-uppercase text-muted mb-2">Siswa</h6>
                                                    <h2 class="mb-0 fw-bold"><?php echo $totalSiswa; ?></h2>
                                                </div>
                                                <div class="bg-warning bg-opacity-10 p-3 rounded">
                                                    <i class="bi bi-people fs-3 text-warning"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dua Kolom Konten -->
                            <div class="row g-4">
                                <!-- Statistik Sistem -->
                                <div class="col-lg-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h5 class="mb-0 fw-bold">📊 Statistik Sistem</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="list-group list-group-flush">
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-journal-bookmark-fill text-primary me-3 fs-4"></i>
                                                        <span>Total Materi</span>
                                                    </div>
                                                    <span class="badge bg-primary rounded-pill"><?php echo $totalMateri; ?></span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-question-circle-fill text-info me-3 fs-4"></i>
                                                        <span>Total Soal</span>
                                                    </div>
                                                    <span class="badge bg-info rounded-pill"><?php echo $totalSoal; ?></span>
                                                </div>
                                                <div class="list-group-item d-flex justify-content-between align-items-center border-0 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-clipboard-check-fill text-success me-3 fs-4"></i>
                                                        <span>Total Tugas</span>
                                                    </div>
                                                    <span class="badge bg-success rounded-pill"><?php echo $totalTugas; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Akses Cepat -->
                                <div class="col-lg-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h5 class="mb-0 fw-bold">⚡ Akses Cepat</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <a href="manajemen_user.php" class="card h-100 border-0 shadow-sm text-decoration-none hover-effect">
                                                        <div class="card-body text-center p-4">
                                                            <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                                                <i class="bi bi-people-fill fs-3 text-primary"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0">Kelola Pengguna</h6>
                                                        </div>
                                                    </a>
                                                </div>
                                                <div class="col-md-6">
                                                    <a href="manajemen_kelas.php" class="card h-100 border-0 shadow-sm text-decoration-none hover-effect">
                                                        <div class="card-body text-center p-4">
                                                            <div class="bg-info bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                                                                <i class="bi bi-book-half fs-3 text-info"></i>
                                                            </div>
                                                            <h6 class="fw-bold mb-0">Kelola Kelas</h6>
                                                        </div>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($_SESSION['role'] == 'guru'): ?>

                            <!-- Baris 1: Kelas yang Diampu -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0 fw-semibold">
                                                <i class="bi bi-journal-bookmark text-primary me-2"></i>
                                                Kelas yang Diampu
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (count($kelasDiampu) > 0): ?>
                                                <div class="row row-cols-1 row-cols-md-2 g-4">
                                                    <?php foreach ($kelasDiampu as $kelas): ?>
                                                        <div class="col">
                                                            <div class="card h-100 border-0 shadow-sm">
                                                                <div class="card-body">
                                                                    <h6 class="fw-semibold mb-1"><?= htmlspecialchars($kelas['course_name']) ?></h6>
                                                                    <p class="text-muted small mb-2">ID: <?= $kelas['id_courses'] ?></p>
                                                                    <span class="badge bg-primary rounded-pill mb-3"><?= $kelas['jumlah_siswa'] ?> siswa</span>
                                                                    <div class="d-grid gap-2">
                                                                        <a href="kelola_pertemuan.php?id_courses=<?= $kelas['id_courses'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">📚 Pertemuan</a>
                                                                        <a href="lihat_progres_pjbl.php?id_courses=<?= $kelas['id_courses'] ?>" class="btn btn-sm btn-outline-warning rounded-pill">📊 Progres PjBL</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted">Anda belum memiliki kelas yang diampu.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Baris 2: Aktivitas Siswa -->
                            <div class="row g-4">
                                <!-- Tugas Terbaru -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0 fw-semibold">
                                                <i class="bi bi-upload text-info me-2"></i>
                                                Pengumpulan Tugas Terbaru
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (count($tugasTerbaru) > 0): ?>
                                                <ul class="list-group list-group-flush">
                                                    <?php foreach ($tugasTerbaru as $tugas): ?>
                                                        <li class="list-group-item border-0 d-flex justify-content-between align-items-center py-2">
                                                            <div>
                                                                <strong><?= htmlspecialchars($tugas['nama']) ?></strong>
                                                                <br><small class="text-muted">"<?= htmlspecialchars($tugas['judul']) ?>"</small>
                                                            </div>
                                                            <a href="lihat_pengumpulan.php?id_content=<?= $tugas['id_content'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Lihat</a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="text-muted mb-0">Belum ada pengumpulan tugas.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hasil Quiz Terbaru -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0 h-100">
                                        <div class="card-header bg-white py-3">
                                            <h5 class="mb-0 fw-semibold">
                                                <i class="bi bi-clipboard-check text-success me-2"></i>
                                                Hasil Quiz Terbaru
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <?php if (count($quizTerbaru) > 0): ?>
                                                <ul class="list-group list-group-flush">
                                                    <?php foreach ($quizTerbaru as $quiz): ?>
                                                        <li class="list-group-item border-0 d-flex justify-content-between align-items-center py-2">
                                                            <div>
                                                                <strong><?= htmlspecialchars($quiz['nama']) ?></strong>
                                                                <br><small class="text-muted">"<?= htmlspecialchars($quiz['judul']) ?>" - Skor: <?= $quiz['score'] ?> / 100</small>

                                                            </div>
                                                            <a href="lihat_hasil_quiz.php?id_content=<?= $quiz['id_content'] ?>" class="btn btn-sm btn-outline-secondary rounded-pill">Lihat</a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php else: ?>
                                                <p class="text-muted mb-0">Belum ada hasil quiz terbaru.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>


                        <?php if ($role === 'siswa'): ?>

                            <!-- Kursus yang Diikuti -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h5 class="mb-0 fw-semibold">
                                                <i class="bi bi-journal-code text-primary me-2"></i>
                                                Kursus yang Saya Ikuti
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <?php
                                                $qKursus = mysqli_query($koneksi, "
                                                SELECT c.id_courses, c.course_name 
                                                FROM courses c 
                                                JOIN course_participants cp ON c.id_courses = cp.id_courses 
                                                WHERE cp.id_user = '$user_id'");
                                                while ($kursus = mysqli_fetch_assoc($qKursus)): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-2">
                                                        <span><?= htmlspecialchars($kursus['course_name']) ?></span>
                                                        <a href="petemuan.php?id_courses=<?= $kursus['id_courses'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">Masuk</a>
                                                    </li>
                                                <?php endwhile; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quiz & Tugas Belum Dikerjakan -->
                            <div class="row g-4 mb-4">
                                <!-- Quiz -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h6 class="mb-0 fw-semibold"><i class="bi bi-question-circle text-warning me-2"></i> Quiz yang Belum Dikerjakan</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <?php
                                                $qQuizBelum = mysqli_query($koneksi, "
                                                SELECT mc.id_content, mc.title 
                                                FROM meeting_contents mc
                                                JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
                                                JOIN course_participants cp ON cm.id_courses = cp.id_courses
                                                LEFT JOIN quiz_result qr ON qr.id_content = mc.id_content AND qr.id_user = '$user_id'
                                                WHERE mc.type = 'quiz' AND cp.id_user = '$user_id' AND qr.id_result IS NULL
                                                ORDER BY mc.deadline ASC LIMIT 5");
                                                if (mysqli_num_rows($qQuizBelum) > 0):
                                                    while ($quiz = mysqli_fetch_assoc($qQuizBelum)): ?>
                                                        <li class="list-group-item border-0 py-2 d-flex justify-content-between align-items-center">
                                                            <span><?= htmlspecialchars($quiz['title']) ?></span>
                                                            <a href="kerjakan_quiz.php?id_content=<?= $quiz['id_content'] ?>" class="btn btn-sm btn-outline-success rounded-pill">Kerjakan</a>
                                                        </li>
                                                    <?php endwhile;
                                                else: ?>
                                                    <li class="list-group-item border-0 py-2 text-muted">Tidak ada quiz tertunda</li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tugas -->
                                <div class="col-md-6">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h6 class="mb-0 fw-semibold">
                                                <i class="bi bi-clipboard-data text-info me-2"></i>
                                                Tugas yang Belum Dikumpulkan
                                            </h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <?php if (count($tugasBelum) > 0): ?>
                                                    <?php foreach ($tugasBelum as $tugas): ?>
                                                        <li class="list-group-item border-0 py-2 d-flex justify-content-between align-items-center">
                                                            <span><?= htmlspecialchars($tugas['title']) ?></span>
                                                            <a href="upload_tugas.php?id_content=<?= $tugas['id_content'] ?>" class="btn btn-sm btn-outline-primary rounded-pill">Upload</a>
                                                        </li>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <li class="list-group-item border-0 py-2 text-muted">
                                                        Tidak ada tugas tertunda
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>


                            </div>

                            <?php
                            // Ambil ID user
                            $id_user = $_SESSION['user_id'];

                            // Ambil semua id_kelompok yang diikuti siswa
                            $id_kelompok_siswa = [];
                            $qKelompok = mysqli_query($koneksi, "
    SELECT ak.id_kelompok 
    FROM anggota_kelompok ak
    WHERE ak.id_user = '$id_user'
");
                            while ($k = mysqli_fetch_assoc($qKelompok)) {
                                $id_kelompok_siswa[] = $k['id_kelompok'];
                            }

                            // Siapkan filter berdasarkan id_kelompok
                            $filter_kelompok = '';
                            if (!empty($id_kelompok_siswa)) {
                                $escaped_ids = array_map('intval', $id_kelompok_siswa);
                                $in_clause = implode(',', $escaped_ids);
                                $filter_kelompok = "OR id_kelompok IN ($in_clause)";
                            }
                            ?>
                            <!-- Forum Terbaru -->
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-header bg-white border-0 py-3">
                                            <h6 class="mb-0 fw-semibold"><i class="bi bi-chat-left-dots text-secondary me-2"></i> Forum Terbaru</h6>
                                        </div>
                                        <div class="card-body">
                                            <ul class="list-group list-group-flush">
                                                <?php
                                                $qForum = mysqli_query($koneksi, "
                        SELECT id_content, title FROM meeting_contents
                        WHERE type = 'forum'
                        AND (id_kelompok IS NULL $filter_kelompok)
                        ORDER BY created_at DESC
                        LIMIT 5
                    ");
                                                while ($forum = mysqli_fetch_assoc($qForum)): ?>
                                                    <li class="list-group-item border-0 py-2 d-flex justify-content-between align-items-center">
                                                        <span><?= htmlspecialchars($forum['title']) ?></span>
                                                        <a href="lihat_forum_siswa.php?id_content=<?= $forum['id_content'] ?>" class="btn btn-sm btn-outline-secondary">Buka</a>
                                                    </li>
                                                <?php endwhile; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
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