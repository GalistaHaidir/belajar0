<?php
include 'koneksi.php'; // koneksi ke database
session_start();

// Cek login
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

// Ambil data konten quiz
$query_content = mysqli_query($koneksi, "SELECT title, id_meeting FROM meeting_contents WHERE id_content = '$id_content' AND type = 'quiz'");
$content = mysqli_fetch_assoc($query_content);
if (!$content) {
    die('Konten quiz tidak valid.');
}

$id_meeting = $content['id_meeting'];

// Ambil semua soal untuk quiz ini
$query_questions = mysqli_query($koneksi, "SELECT * FROM quiz_questions WHERE id_content = '$id_content' ORDER BY id_question DESC");


// Proses hapus soal jika ada parameter hapus_id
if (isset($_GET['hapus_id']) && is_numeric($_GET['hapus_id'])) {
    $hapus_id = $_GET['hapus_id'];

    // Pastikan soal tersebut milik konten ini
    $cek = mysqli_query($koneksi, "SELECT * FROM quiz_questions WHERE id_question = '$hapus_id' AND id_content = '$id_content'");
    if (mysqli_num_rows($cek) > 0) {
        mysqli_query($koneksi, "DELETE FROM quiz_questions WHERE id_question = '$hapus_id'");
        header("Location: kelola_soal.php?id_content=$id_content&msg=deleted");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Soal tidak ditemukan atau tidak valid.</div>";
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Soal | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">
    <style>
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            font-family: 'Poppins', sans-serif;
        }

        .main-content {
            padding: 20px;
        }

        .card {
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }

        .hover-scale {
            transition: transform 0.2s;
        }

        .hover-scale:hover {
            transform: scale(1.05);
        }

        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1) !important;
        }

        .bg-gradient-primary-to-secondary {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        }

        .breadcrumb-light .breadcrumb-item.active {
            color: #fff;
        }

        .material-snackbar {
            animation: materialSlideUp 0.3s ease-out, materialFadeOut 0.5s forwards 2.5s;
            transform-origin: center;
        }

        @keyframes materialSlideUp {
            from {
                transform: translateY(100%) scale(0.9);
                opacity: 0;
            }

            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        @keyframes materialFadeOut {
            to {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
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
                        <!-- header -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <h1 class="mb-2">📋 Kelola : <strong><?= htmlspecialchars($content['title']) ?></strong></h1>

                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="kelola_konten.php?id_meeting=<?= $id_meeting ?>">Konten</a></li>
                                        <li class="breadcrumb-item active">Kelola Soal</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Material Design Snackbar -->
                        <?php if (isset($_GET['msg'])): ?>
                            <div class="position-fixed bottom-0 start-50 translate-middle-x p-3" style="z-index: 11">
                                <div class="bg-<?= ($_GET['msg'] === 'deleted') ? 'danger' : 'success' ?> text-white px-4 py-3 rounded shadow-lg material-snackbar">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="bi bi-check2-circle me-2"></i>
                                            <?= match ($_GET['msg']) {
                                                'added' => 'Soal berhasil ditambahkan',
                                                'updated' => 'Perubahan telah disimpan',
                                                'deleted' => 'Soal telah dihapus'
                                            } ?>
                                        </span>
                                        <button type="button" class="btn-close btn-close-white ms-3" onclick="this.parentElement.parentElement.remove()"></button>
                                    </div>
                                </div>
                            </div>

                        <?php endif; ?>

                        <!-- Action Button (Sticky) -->
                        <div class="d-flex justify-content-end sticky-top mb-3" style="top: 70px; z-index: 10">
                            <a href="form_soal.php?id_content=<?= $id_content ?>"
                                class="btn btn-primary rounded-pill shadow-sm px-4 py-2 hover-scale">
                                <i class="bi bi-plus-lg me-2"></i>Tambah Soal
                            </a>
                        </div>

                        <!-- Questions Grid (Masonry Layout) -->
                        <div class="row g-4" data-masonry='{"percentPosition": true}'>
                            <?php if (mysqli_num_rows($query_questions) > 0): ?>
                                <?php while ($q = mysqli_fetch_assoc($query_questions)): ?>
                                    <div class="col-lg-4 col-md-6">
                                        <div class="card border-0 shadow-sm hover-lift transition-all">
                                            <!-- Question Header -->
                                            <div class="card-header bg-white border-bottom-0 pb-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill">
                                                        ID: <?= $q['id_question'] ?>
                                                    </span>
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-outline-secondary rounded-circle"
                                                            data-bs-toggle="dropdown">
                                                            <i class="bi bi-three-dots-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a href="form_soal.php?id=<?= $q['id_question'] ?>&id_content=<?= $id_content ?>&mode=edit"
                                                                    class="dropdown-item">
                                                                    <i class="bi bi-pencil me-2"></i>Edit
                                                                </a>
                                                            </li>
                                                            <li>
                                                                <a href="kelola_soal.php?id_content=<?= $id_content ?>&hapus_id=<?= $q['id_question'] ?>"
                                                                    onclick="return confirm('Hapus soal ini?')"
                                                                    class="dropdown-item text-danger">
                                                                    <i class="bi bi-trash me-2"></i>Hapus
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Question Body -->
                                            <div class="card-body">
                                                <p class="card-text fw-semibold text-dark mb-3">
                                                    <?= nl2br(htmlspecialchars($q['question_text'])) ?>
                                                </p>

                                                <!-- Options (Interactive) -->
                                                <div class="options">
                                                    <?php foreach (['A', 'B', 'C', 'D'] as $option): ?>
                                                        <div class="option-item p-2 mb-2 rounded <?=
                                                                                                    ($q['correct_option'] === $option) ? 'bg-success bg-opacity-10 border border-success border-opacity-25' : 'bg-light'
                                                                                                    ?>">
                                                            <span class="badge bg-<?=
                                                                                    ($q['correct_option'] === $option) ? 'success' : 'secondary'
                                                                                    ?> me-2"><?= $option ?></span>
                                                            <?= htmlspecialchars($q['option_' . strtolower($option)]) ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>

                                            <!-- Card Footer (Modified - Show answer directly) -->
                                            <div class="card-footer bg-white border-top-0 pt-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="small text-muted">
                                                        <i class="bi bi-check2-circle text-success me-1"></i>
                                                        Jawaban: <strong><?= $q['correct_option'] ?></strong>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <!-- Empty State -->
                                <div class="col-12">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body text-center py-5">
                                            <i class="bi bi-question-circle display-4 text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum ada soal</h5>
                                            <p class="text-muted mb-4">Mulai dengan menambahkan soal pertama Anda</p>
                                            <a href="form_soal.php?id_content=<?= $id_content ?>"
                                                class="btn btn-primary rounded-pill px-4">
                                                <i class="bi bi-plus-lg me-2"></i>Tambah Soal
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                </div>
            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        setTimeout(() => {
            document.querySelector('.material-snackbar').remove();
        }, 3000);
    </script>

</body>

</html>