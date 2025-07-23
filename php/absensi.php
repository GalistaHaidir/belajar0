<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['user_id'];
$id_meeting = isset($_GET['id_meeting']) ? intval($_GET['id_meeting']) : 0;

// Cek apakah ada konten absensi pada pertemuan ini
$query = $koneksi->prepare("SELECT id_content, title FROM meeting_contents WHERE id_meeting = ? AND type = 'absen' LIMIT 1");
$query->bind_param("i", $id_meeting);
$query->execute();
$result = $query->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    echo "Konten absensi tidak tersedia.";
    exit();
}

$id_content = $data['id_content'];
$title = $data['title'];

// Cek apakah user sudah absen
$cek = $koneksi->prepare("SELECT * FROM absensi WHERE id_user = ? AND id_content = ?");
$cek->bind_param("ii", $id_user, $id_content);
$cek->execute();
$res_cek = $cek->get_result();
$sudah_absen = $res_cek->num_rows > 0;

// Jika belum absen dan submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$sudah_absen) {
    $status = $_POST['status'];
    $waktu = date("Y-m-d H:i:s");
    $bukti_izin = null;

    if ($status === 'izin') {
        if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === 0) {
            $upload_dir = 'uploads_izin/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            $filename = date('YmdHis') . '_' . basename($_FILES['bukti']['name']);
            $target_file = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['bukti']['tmp_name'], $target_file)) {
                $bukti_izin = $target_file;
            } else {
                $error = "Gagal mengunggah file bukti izin.";
            }
        } else {
            $error = "Mohon unggah bukti izin.";
        }
    }

    if (!isset($error)) {
        $stmt = $koneksi->prepare("INSERT INTO absensi (id_content, id_user, waktu_absen, status, bukti_izin) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $id_content, $id_user, $waktu, $status, $bukti_izin);
        if ($stmt->execute()) {
            $sudah_absen = true;

            // =============== Tambahkan ke content_activity ===============
            $status_activity = 'submitted';
            $updated_at = date("Y-m-d H:i:s");

            $cek_activity = $koneksi->prepare("
        SELECT id_activity FROM content_activity WHERE id_user = ? AND id_content = ?
    ");
            $cek_activity->bind_param("ii", $id_user, $id_content);
            $cek_activity->execute();
            $res_activity = $cek_activity->get_result();

            if ($res_activity->num_rows > 0) {
                $stmt_update = $koneksi->prepare("
            UPDATE content_activity SET status = ?, updated_at = ? 
            WHERE id_user = ? AND id_content = ?
        ");
                $stmt_update->bind_param("ssii", $status_activity, $updated_at, $id_user, $id_content);
                $stmt_update->execute();
            } else {
                $stmt_insert = $koneksi->prepare("
            INSERT INTO content_activity (id_user, id_content, status, updated_at)
            VALUES (?, ?, ?, ?)
        ");
                $stmt_insert->bind_param("iiss", $id_user, $id_content, $status_activity, $updated_at);
                $stmt_insert->execute();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Absensi | Belajaro</title>
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
                                    📋 Absensi
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="kursusku.php">Kursusku</a></li>
                                        <li class="breadcrumb-item"><a href="pertemuan.php">Daftar Pertemuan</a></li>
                                        <li class="breadcrumb-item">
                                            <a href="detail_pertemuan.php?id_meeting=<?= $id_meeting; ?>">Detail Pertemuan</a>
                                        </li>
                                        <li class="breadcrumb-item active" aria-current="page">Absensi</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Card dengan Tabs -->
                        <div class="card border-0 shadow-lg">
                            <div class="card-header bg-white border-bottom">
                                <ul class="nav nav-tabs card-header-tabs">
                                    <li class="nav-item">
                                        <a class="nav-link active" href="#">Absensi</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body p-4">
                                <h3 class="mb-4">🔍 Detail Absensi</h3>
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <p><span class="badge bg-light text-dark">Judul:</span> <?= htmlspecialchars($title); ?></p>
                                    </div>
                                </div>

                                <?php if ($sudah_absen): ?>
                                    <div class="alert alert-success d-flex align-items-center">
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                        <span>Absensi Anda telah tercatat!</span>
                                    </div>
                                <?php else: ?>
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?= $error; ?></div>
                                    <?php endif; ?>

                                    <form method="POST" enctype="multipart/form-data" class="mt-3">
                                        <div class="form-group mb-4">
                                            <label class="fw-bold mb-2">Status Kehadiran:</label>
                                            <div class="d-flex gap-4">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="hadir" value="hadir" checked>
                                                    <label class="form-check-label" for="hadir">Hadir</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="status" id="izin" value="izin">
                                                    <label class="form-check-label" for="izin">Izin</label>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="bukti-izin-upload" class="mb-4" style="display:none;">
                                            <label class="form-label fw-bold">Upload Bukti Izin</label>
                                            <div class="custom-file">
                                                <input type="file" class="form-control" name="bukti" accept=".pdf,.jpg,.jpeg,.png">
                                                <small class="text-muted">Format: PDF, JPG, atau PNG (max 2MB)</small>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary px-4 py-2">
                                            <i class="bi bi-send-fill me-2"></i> Submit Absensi
                                        </button>
                                    </form>
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
    <script>
        document.querySelectorAll('input[name="status"]').forEach(function(elem) {
            elem.addEventListener('change', function() {
                document.getElementById('bukti-izin-upload').style.display =
                    this.value === 'izin' ? 'block' : 'none';
            });
        });
    </script>

</body>

</html>