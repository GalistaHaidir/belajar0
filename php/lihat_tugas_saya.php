<?php
session_start();
require_once 'koneksi.php'; // file koneksi DB

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['user_id'];
$id_content = $_GET['id_content'] ?? null;

if (!$id_content) {
    echo "Konten tidak ditemukan.";
    exit;
}

// Ambil detail konten termasuk apakah dia tugas/PjBL
$stmt = $koneksi->prepare("SELECT mc.*, pj.id_project 
    FROM meeting_contents mc 
    LEFT JOIN pjbl_project pj ON mc.id_project = pj.id_project 
    WHERE mc.id_content = ?");
$stmt->bind_param("i", $id_content);
$stmt->execute();
$contentResult = $stmt->get_result();
$content = $contentResult->fetch_assoc();

if (!$content) {
    echo "Konten tidak ditemukan.";
    exit;
}

$isPjbl = !empty($content['id_project']);
$tugas = null;

if ($isPjbl) {
    // Ambil ID kelompok siswa
    $stmt = $koneksi->prepare("SELECT ak.id_kelompok 
        FROM anggota_kelompok ak
        JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
        WHERE ak.id_user = ? AND k.id_project = ?");
    $stmt->bind_param("ii", $id_user, $content['id_project']);
    $stmt->execute();
    $kelompokResult = $stmt->get_result();
    $kelompok = $kelompokResult->fetch_assoc();

    if ($kelompok) {
        $id_kelompok = $kelompok['id_kelompok'];

        // Ambil tugas dari kelompok
        $stmt = $koneksi->prepare("SELECT * FROM pengumpulan_tugas 
            WHERE id_content = ? AND id_kelompok = ?");
        $stmt->bind_param("ii", $id_content, $id_kelompok);
        $stmt->execute();
        $result = $stmt->get_result();
        $tugas = $result->fetch_assoc();
    }
} else {
    // Tugas individu
    $stmt = $koneksi->prepare("SELECT * FROM pengumpulan_tugas 
        WHERE id_content = ? AND id_user = ?");
    $stmt->bind_param("ii", $id_content, $id_user);
    $stmt->execute();
    $result = $stmt->get_result();
    $tugas = $result->fetch_assoc();
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
                                    <i class="bi bi-file-earmark-check-fill me-2"></i>Lihat Tugas Saya
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="pertemuan.php">Pertemuan</a></li>
                                        <li class="breadcrumb-item"><a href="detail_pertemuan.php?id_meeting=<?= $content['id_meeting'] ?>">Konten</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Lihat Tugas</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Content Card -->
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <?php if ($tugas): ?>
                                    <h5 class="fw-semibold mb-3"><?= htmlspecialchars($content['title']) ?></h5>

                                    <ul class="list-group list-group-flush mb-3">
                                        <li class="list-group-item">
                                            <strong><i class="bi bi-clock me-1"></i>Waktu Pengumpulan:</strong><br>
                                            <?= htmlspecialchars($tugas['waktu_kumpul']) ?>
                                        </li>
                                        <li class="list-group-item">
                                            <strong><i class="bi bi-paperclip me-1"></i>File:</strong><br>
                                            <a href="<?= htmlspecialchars($tugas['file_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-1">
                                                <i class="bi bi-eye-fill me-1"></i>Lihat File
                                            </a>
                                        </li>
                                        <?php if (!empty($tugas['catatan'])): ?>
                                            <li class="list-group-item">
                                                <strong><i class="bi bi-chat-left-text me-1"></i>Catatan Guru:</strong><br>
                                                <?= nl2br(htmlspecialchars($tugas['catatan'])) ?>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        Belum ada tugas yang dikumpulkan.
                                    </div>
                                <?php endif; ?>

                            </div>
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