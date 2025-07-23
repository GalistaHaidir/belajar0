<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}


$id_user = $_SESSION['user_id'];

if (!isset($_GET['id_content'])) {
    echo "ID konten tidak valid.";
    exit();
}

$id_content = intval($_GET['id_content']);
$notifikasi = '';

// Ambil detail konten
$sql = "SELECT * FROM meeting_contents WHERE id_content = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_content);
$stmt->execute();
$result = $stmt->get_result();
$content = $result->fetch_assoc();

if (!$content) {
    echo "Konten tidak ditemukan.";
    exit();
}

$id_project = $content['id_project'];
$is_pjbl = !empty($id_project);
$id_kelompok = null;

// Cek kelompok jika PjBL
if ($is_pjbl) {
    $sqlKelompok = "SELECT ak.id_kelompok
                    FROM anggota_kelompok ak
                    JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
                    WHERE ak.id_user = ? AND k.id_project = ?";
    $stmtKelompok = $koneksi->prepare($sqlKelompok);
    $stmtKelompok->bind_param("ii", $id_user, $id_project);
    $stmtKelompok->execute();
    $resultKelompok = $stmtKelompok->get_result();
    $dataKelompok = $resultKelompok->fetch_assoc();

    if ($dataKelompok) {
        $id_kelompok = $dataKelompok['id_kelompok'];
    } else {
        echo "Anda belum tergabung dalam kelompok untuk proyek ini.";
        exit();
    }
}

// Cek apakah sudah pernah mengumpulkan
$sqlCek = "SELECT * FROM pengumpulan_tugas WHERE id_content = ? AND " .
    ($is_pjbl ? "id_kelompok = ?" : "id_user = ?");
$stmtCek = $koneksi->prepare($sqlCek);
if ($is_pjbl) {
    $stmtCek->bind_param("ii", $id_content, $id_kelompok);
} else {
    $stmtCek->bind_param("ii", $id_content, $id_user);
}
$stmtCek->execute();
$resultCek = $stmtCek->get_result();
$existing = $resultCek->fetch_assoc();

$fileSebelumnya = $existing['file_path'] ?? '';

// Proses pengumpulan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $catatan = $_POST['catatan'] ?? '';
    $jawaban = $_POST['jawaban'] ?? null;

    $waktu_kumpul = date("Y-m-d H:i:s");

    $uploadBaru = false;
    if (isset($_FILES['file']) && $_FILES['file']['error'] === 0) {
        $folder = "uploads/";
        if (!is_dir($folder)) mkdir($folder);

        $nama_file = time() . "_" . basename($_FILES['file']['name']);
        $tujuan = $folder . $nama_file;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $tujuan)) {
            $uploadBaru = true;
        } else {
            $notifikasi = "<div class='alert alert-danger'>Gagal mengunggah file.</div>";
        }
    } else {
        $tujuan = $fileSebelumnya; // pakai file lama jika tidak upload baru
    }

    if ($existing) {
        // UPDATE
        $sqlUpdate = "UPDATE pengumpulan_tugas 
              SET file_path = ?, catatan = ?, jawaban = ?, waktu_kumpul = ? 
              WHERE id_content = ? AND " . ($is_pjbl ? "id_kelompok = ?" : "id_user = ?");
        $stmtUpdate = $koneksi->prepare($sqlUpdate);

        if ($is_pjbl) {
            $stmtUpdate->bind_param("ssssii", $tujuan, $catatan, $jawaban, $waktu_kumpul, $id_content, $id_kelompok);
        } else {
            $stmtUpdate->bind_param("ssssii", $tujuan, $catatan, $jawaban, $waktu_kumpul, $id_content, $id_user);
        }


        $stmtUpdate->execute();
        $notifikasi = "<div class='alert alert-success'>Tugas berhasil diperbarui.</div>";

        // Update atau Insert ke content_activity
        $cek_activity = $koneksi->prepare("SELECT * FROM content_activity WHERE id_user = ? AND id_content = ?");
        $cek_activity->bind_param("ii", $id_user, $id_content);
        $cek_activity->execute();
        $res = $cek_activity->get_result();

        if ($res->num_rows > 0) {
            $update = $koneksi->prepare("UPDATE content_activity SET status = 'submitted' WHERE id_user = ? AND id_content = ?");
            $update->bind_param("ii", $id_user, $id_content);
            $update->execute();
        } else {
            $insert = $koneksi->prepare("INSERT INTO content_activity (id_user, id_content, status) VALUES (?, ?, 'submitted')");
            $insert->bind_param("ii", $id_user, $id_content);
            $insert->execute();
        }
    } else {
        // INSERT baru
        $sqlInsert = "INSERT INTO pengumpulan_tugas 
              (id_content, id_user, id_kelompok, file_path, catatan, jawaban, waktu_kumpul)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtInsert = $koneksi->prepare($sqlInsert);
        $stmtInsert->bind_param("iiissss", $id_content, $id_user, $id_kelompok, $tujuan, $catatan, $jawaban, $waktu_kumpul);

        $stmtInsert->execute();
        $notifikasi = "<div class='alert alert-success'>Tugas berhasil dikumpulkan.</div>";

        // Update atau Insert ke content_activity
        $cek_activity = $koneksi->prepare("SELECT * FROM content_activity WHERE id_user = ? AND id_content = ?");
        $cek_activity->bind_param("ii", $id_user, $id_content);
        $cek_activity->execute();
        $res = $cek_activity->get_result();

        if ($res->num_rows > 0) {
            $update = $koneksi->prepare("UPDATE content_activity SET status = 'submitted' WHERE id_user = ? AND id_content = ?");
            $update->bind_param("ii", $id_user, $id_content);
            $update->execute();
        } else {
            $insert = $koneksi->prepare("INSERT INTO content_activity (id_user, id_content, status) VALUES (?, ?, 'submitted')");
            $insert->bind_param("ii", $id_user, $id_content);
            $insert->execute();
        }
    }
}
$bolehUpload = true;
$notifikasi_pjbl = '';

if ($is_pjbl && !empty($id_kelompok)) {
    // Cek semua konten sebelumnya dalam proyek ini
    $sqlTahapSebelumnya = "SELECT id_content FROM meeting_contents 
                           WHERE id_project = ? AND id_content < ?
                           ORDER BY id_content DESC LIMIT 1";
    $stmtTahap = $koneksi->prepare($sqlTahapSebelumnya);
    $stmtTahap->bind_param("ii", $id_project, $id_content);
    $stmtTahap->execute();
    $resTahap = $stmtTahap->get_result();
    $tahapSebelumnya = $resTahap->fetch_assoc();

    if ($tahapSebelumnya) {
        $id_tahap_sebelumnya = $tahapSebelumnya['id_content'];

        // Cek apakah tahap sebelumnya sudah dikumpulkan dan di-ACC oleh guru
        $sqlACC = "SELECT status_acc FROM pengumpulan_tugas 
                   WHERE id_content = ? AND id_kelompok = ?";
        $stmtACC = $koneksi->prepare($sqlACC);
        $stmtACC->bind_param("ii", $id_tahap_sebelumnya, $id_kelompok);
        $stmtACC->execute();
        $resACC = $stmtACC->get_result();
        $cek = $resACC->fetch_assoc();

        if (!$cek || $cek['status_acc'] !== 'approved') {
            $bolehUpload = false;
            $notifikasi_pjbl = "<div class='alert alert-warning'>
                Anda belum bisa mengumpulkan tugas ini karena <strong>tahap sebelumnya belum disetujui guru</strong>.
            </div>";
        }
    }
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
                                    📝 Upload Tugas
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="kursusku.php">Kursusku</a></li>
                                        <li class="breadcrumb-item"><a href="pertemuan.php">Daftar Pertemuan</a></li>
                                        <li class="breadcrumb-item">
                                            <a href="detail_pertemuan.php?id_meeting=<?= $content['id_meeting'] ?>">Detail Pertemuan</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">Upload Tugas</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Card Form Section -->
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h4 class="fw-semibold mb-2"><?= htmlspecialchars($content['title']); ?></h4>
                                <p class="text-muted mb-3"><?= nl2br(htmlspecialchars($content['description'])); ?></p>

                                <?php
                                // Notifikasi jika belum boleh upload PjBL
                                if (!$bolehUpload && $is_pjbl) {
                                    echo $notifikasi_pjbl;
                                } else {
                                ?>


                                    <?php if (!empty($content['deadline'])): ?>
                                        <div class="alert alert-danger d-inline-flex align-items-center py-2 px-3 rounded small mb-3">
                                            <i class="bi bi-clock me-2"></i>
                                            <strong>Deadline:</strong>&nbsp;<?= htmlspecialchars($content['deadline']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($notifikasi)) echo $notifikasi; ?>
                                    <form action="" method="POST" enctype="multipart/form-data" class="mt-4">

                                        <div class="mb-3">
                                            <label for="file" class="form-label fw-semibold">Pilih File (opsional)</label>
                                            <?php if ($fileSebelumnya): ?>
                                                <div class="mb-2">
                                                    <span class="badge bg-primary-subtle text-primary fw-normal small">
                                                        <i class="bi bi-file-earmark-text me-1"></i>
                                                        <a href="<?= htmlspecialchars($fileSebelumnya); ?>" class="text-decoration-none" target="_blank">Lihat File Sebelumnya</a>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" name="file" id="file" class="form-control">
                                        </div>

                                        <div class="mb-3">
                                            <label for="jawaban" class="form-label fw-semibold">Ketik Jawaban (opsional)</label>
                                            <textarea name="jawaban" id="jawaban" class="form-control" rows="5"><?= htmlspecialchars($existing['jawaban'] ?? '') ?></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill mt-2">
                                            <i class="bi bi-send-fill me-1"></i> <?= $existing ? 'Perbarui Tugas' : 'Kumpulkan Tugas' ?>
                                        </button>
                                    </form>

                                <?php } ?>

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