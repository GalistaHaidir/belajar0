<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$id_meeting = $_GET['id_meeting'] ?? null;
if (!$id_meeting) {
    die("ID pertemuan tidak ditemukan.");
}

// Dapatkan id_courses dari id_meeting
$qCourse = mysqli_query($koneksi, "SELECT id_courses FROM course_meetings WHERE id_meeting = $id_meeting");
$dataCourse = mysqli_fetch_assoc($qCourse);
$id_courses = $dataCourse['id_courses'] ?? null;

if (!$id_courses) {
    die("ID kursus tidak ditemukan untuk pertemuan ini.");
}


// Ambil semua konten dalam pertemuan
$queryKonten = mysqli_query($koneksi, "SELECT id_content, title FROM meeting_contents WHERE id_meeting = $id_meeting");
$konten = [];
while ($k = mysqli_fetch_assoc($queryKonten)) {
    $konten[] = $k;
}
$totalKonten = count($konten);

// Ambil semua siswa dalam kursus
$querySiswa = mysqli_query($koneksi, "
    SELECT u.id_user, u.name 
    FROM course_participants cp 
    JOIN users u ON cp.id_user = u.id_user 
    WHERE cp.id_courses IN (
        SELECT id_courses FROM course_meetings WHERE id_meeting = $id_meeting
    ) AND u.role = 'siswa'
");
$siswa = [];
while ($s = mysqli_fetch_assoc($querySiswa)) {
    $siswa[] = $s;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Progres Pertemuan | Belajaro</title>
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
                                    📊 Progres Siswa - Pertemuan
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="daftar_kursus.php">Daftar Kursus</a></li>
                                        <li class="breadcrumb-item">
                                            <a href="kelola_pertemuan.php?id_courses=<?= $id_courses ?>">Pertemuan</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">Progress Pertemuan</li>
                                    </ol>
                                </nav>
                            </div>

                        </div>

                        <!-- Konten -->
                        <?php if ($totalKonten === 0): ?>
                            <div class="text-center py-5 my-5 bg-light rounded-2">
                                <i class="bi bi-folder-x display-5 text-muted mb-4"></i>
                                <h4 class="fw-light">Belum ada konten di pertemuan ini</h4>
                                <p class="text-muted">Tambahkan konten terlebih dahulu agar siswa dapat mengerjakan</p>
                            </div>
                        <?php elseif (count($siswa) === 0): ?>
                            <div class="text-center py-5 my-5 bg-light rounded-2">
                                <i class="bi bi-person-x display-5 text-muted mb-4"></i>
                                <h4 class="fw-light">Tidak ada siswa terdaftar</h4>
                                <p class="text-muted">Pastikan siswa telah ditambahkan ke kursus ini</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 220px;">Siswa</th>
                                            <th style="width: 30%;">Progress</th>
                                            <th>Status Konten</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($siswa as $s): ?>
                                            <?php
                                            $selesai = 0;
                                            $statusList = [];

                                            foreach ($konten as $k) {
                                                $idc = $k['id_content'];
                                                $track = mysqli_query($koneksi, "SELECT status FROM content_activity WHERE id_user = {$s['id_user']} AND id_content = $idc");
                                                $data = mysqli_fetch_assoc($track);
                                                $status = $data['status'] ?? '—';
                                                $statusList[] = $status;

                                                if ($status === 'opened' || $status === 'submitted') {
                                                    $selesai++;
                                                }
                                            }

                                            $persen = $totalKonten > 0 ? round(($selesai / $totalKonten) * 100) : 0;
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <?= strtoupper($s['name'][0]) ?>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold"><?= htmlspecialchars($s['name']) ?></div>
                                                            <small class="text-muted">ID: <?= $s['id_user'] ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="progress rounded-pill" style="height: 16px;">
                                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $persen ?>%;" aria-valuenow="<?= $persen ?>" aria-valuemin="0" aria-valuemax="100">
                                                            <?= $persen ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php foreach ($statusList as $i => $stat): ?>
                                                        <div class="d-flex justify-content-between align-items-center border-bottom py-1">
                                                            <span class="text-muted small"><?= htmlspecialchars($konten[$i]['title']) ?></span>
                                                            <span class="badge 
                <?= $stat === 'submitted' ? 'bg-success' : ($stat === 'opened' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                                                <?= ucfirst($stat) ?>
                                                            </span>
                                                        </div>
                                                    <?php endforeach; ?>
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