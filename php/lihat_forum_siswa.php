<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    die('Anda harus login terlebih dahulu.');
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$id_user = $_SESSION['user_id'];
$id_content = $_GET['id_content'] ?? null;
// Ambil id_meeting berdasarkan id_content
$q_meeting = mysqli_query($koneksi, "SELECT id_meeting FROM meeting_contents WHERE id_content = '$id_content'");
$data_meeting = mysqli_fetch_assoc($q_meeting);
$id_meeting = $data_meeting['id_meeting'] ?? null;


// Cek konten forum
$query_forum = mysqli_query($koneksi, "SELECT * FROM meeting_contents WHERE id_content = '$id_content' AND type = 'forum'");
$forum = mysqli_fetch_assoc($query_forum);
if (!$forum) {
    echo "Forum tidak ditemukan.";
    exit;
}

// Proses kirim balasan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_text = mysqli_real_escape_string($koneksi, $_POST['reply_text']);
    mysqli_query($koneksi, "INSERT INTO forum_reply (id_content, id_user, reply_text) VALUES ('$id_content', '$id_user', '$reply_text')");
    header("Location: lihat_forum_siswa.php?id_content=$id_content");
    exit;
}

// Ambil semua komentar
$replies = mysqli_query($koneksi, "
    SELECT r.*, u.name 
    FROM forum_reply r 
    JOIN users u ON r.id_user = u.id_user 
    WHERE r.id_content = '$id_content' 
    ORDER BY r.created_at ASC
");

// Tambahkan tracking otomatis saat forum dibuka
if (isset($_GET['id_content'])) {
    $id_user = $_SESSION['user_id'];
    $id_content = intval($_GET['id_content']);

    $stmtTrack = $koneksi->prepare("INSERT INTO content_activity (id_user, id_content, status)
        VALUES (?, ?, 'opened')
        ON DUPLICATE KEY UPDATE updated_at = NOW()");
    $stmtTrack->bind_param("ii", $id_user, $id_content);
    $stmtTrack->execute();
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lihat Forum | Belajaro</title>
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
                        <!-- Header (tidak diubah sesuai permintaan) -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h1 class="fw-bold mb-2">🗨️ Forum Diskusi: <?= htmlspecialchars($forum['title']) ?></h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="detail_pertemuan.php?id_meeting=<?= $id_meeting ?>">Pertemuan</a></li>
                                        <li class="breadcrumb-item active">Lihat Forum</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>
                        <!-- Header Forum -->
                        <div class="card shadow-sm mb-4 border-primary">
                            <div class="card-body">
                                <div class="p-3 bg-light rounded mb-3">
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($forum['description'])) ?></p>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Komentar -->
                        <div class="card shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h4 class="mb-0">💬 Komentar Diskusi</h4>
                            </div>
                            <div class="card-body">
                                <?php if (mysqli_num_rows($replies) > 0) : ?>
                                    <?php while ($row = mysqli_fetch_assoc($replies)) : ?>
                                        <div class="mb-4 pb-3 border-bottom">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <strong class="text-primary"><?= htmlspecialchars($row['name']) ?></strong>
                                                <small class="text-muted"><?= date("d M Y, H:i", strtotime($row['created_at'])) ?></small>
                                            </div>
                                            <div class="p-3 bg-light rounded">
                                                <p class="mb-0"><?= nl2br(htmlspecialchars($row['reply_text'])) ?></p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-chat-left-text fs-1 text-muted"></i>
                                        <p class="text-muted mt-2">Belum ada komentar</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Form Komentar -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h4 class="mb-0">✍️ Tambah Komentar</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="reply_text" class="form-label fw-semibold">Isi Komentar Anda:</label>
                                        <textarea name="reply_text" id="reply_text" class="form-control" rows="4" required
                                            placeholder="Tulis komentar Anda di sini..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-send-fill me-2"></i> Kirim Komentar
                                        </button>
                                    </div>
                                </form>
                            </div>
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