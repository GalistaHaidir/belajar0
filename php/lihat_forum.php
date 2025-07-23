<?php
session_start();
require 'koneksi.php'; // file koneksi ke database

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
    die('ID konten tidak ditemukan.');
}

// Ambil data konten forum
$query_forum = mysqli_query($koneksi, "SELECT * FROM meeting_contents WHERE id_content = '$id_content' AND type = 'forum'");
$forum = mysqli_fetch_assoc($query_forum);

$id_meeting = $forum['id_meeting'];

if (!$forum) {
    die('Forum tidak ditemukan.');
}

// Handle submit reply
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reply_text = mysqli_real_escape_string($koneksi, $_POST['reply_text']);
    $id_user = $_SESSION['user_id'];

    if (!empty($reply_text)) {
        mysqli_query($koneksi, "INSERT INTO forum_reply (id_content, id_user, reply_text, created_at) 
            VALUES ('$id_content', '$id_user', '$reply_text', NOW())");
        header("Location: lihat_forum.php?id_content=$id_content");
        exit;
    }
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
                                <h1 class="fw-bold mb-2">📢 Forum : <?= htmlspecialchars($forum['title']) ?></h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="kelola_konten.php?id_meeting=<?= $id_meeting ?>">Konten</a></li>
                                        <li class="breadcrumb-item active">Lihat Forum</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Forum Diskusi -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="fw-semibold">Deskripsi Forum:</h6>
                                <div class="p-3 bg-light border rounded">
                                    <?= nl2br(htmlspecialchars($forum['description'])) ?>
                                </div>
                            </div>
                        </div>

                        <!-- Daftar Balasan -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-bottom">
                                <h4 class="mb-0 fw-semibold">🗨️ Daftar Balasan</h4>
                            </div>
                            <div class="card-body">
                                <?php
                                $query_reply = mysqli_query($koneksi, "
            SELECT r.*, u.name, u.role
            FROM forum_reply r 
            JOIN users u ON r.id_user = u.id_user 
            WHERE r.id_content = '$id_content' 
            ORDER BY r.created_at ASC
        ");

                                // Fungsi untuk mengatur warna badge berdasarkan role
                                function getBadgeClass($role)
                                {
                                    switch ($role) {
                                        case 'admin':
                                            return 'bg-danger';
                                        case 'guru':
                                            return 'bg-primary';
                                        case 'siswa':
                                            return 'bg-success';
                                        default:
                                            return 'bg-secondary';
                                    }
                                }

                                if (mysqli_num_rows($query_reply) > 0):
                                    while ($reply = mysqli_fetch_assoc($query_reply)):
                                        $badge_class = getBadgeClass($reply['role']);
                                ?>
                                        <div class="mb-4">
                                            <div class="border-start border-3 ps-3 border-primary">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="fw-semibold text-primary">
                                                        <?= htmlspecialchars($reply['name']) ?>
                                                        <span class="badge <?= $badge_class ?> text-capitalize ms-2">
                                                            <?= htmlspecialchars($reply['role']) ?>
                                                        </span>
                                                    </div>
                                                    <small class="text-muted">
                                                        <i class="bi bi-clock-history me-1"></i>
                                                        <?= date("d M Y, H:i", strtotime($reply['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <div class="p-3 bg-light rounded">
                                                    <?= nl2br(htmlspecialchars($reply['reply_text'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php
                                    endwhile;
                                else:
                                    ?>
                                    <div class="text-center py-5">
                                        <i class="bi bi-chat-square-text fs-1 text-muted"></i>
                                        <p class="text-muted mt-2 mb-0">Belum ada balasan pada forum ini.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Form Tambah Balasan -->
                        <div class="card border-0 shadow-sm mb-5">
                            <div class="card-header bg-white border-bottom">
                                <h4 class="mb-0 fw-semibold">✍️ Tambah Balasan Anda</h4>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <div class="mb-3">
                                        <label for="reply_text" class="form-label fw-semibold">Tulis Balasan:</label>
                                        <textarea name="reply_text" id="reply_text" class="form-control" rows="5" required placeholder="Tulis tanggapan Anda..."></textarea>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                                            <i class="bi bi-send-fill me-2"></i> Kirim
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