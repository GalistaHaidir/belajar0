<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$id_meeting = $_GET['id_meeting'] ?? '';
$judul_pertemuan = ''; // default

// Ambil data pertemuan
$result = mysqli_query($koneksi, "SELECT title FROM course_meetings WHERE id_meeting = '$id_meeting'");
if ($data = mysqli_fetch_assoc($result)) {
    $judul_pertemuan = $data['title'];
}

// Fungsi ambil konten berdasarkan tipe
function getKonten($koneksi, $id_meeting, $type)
{
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM meeting_contents WHERE id_meeting = ? AND type = ?");
    mysqli_stmt_bind_param($stmt, "is", $id_meeting, $type);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}

// Ambil semua jenis konten
$materi = getKonten($koneksi, $id_meeting, 'materi');
$quiz = getKonten($koneksi, $id_meeting, 'quiz');
$tugas = getKonten($koneksi, $id_meeting, 'tugas');
$pjbl = getKonten($koneksi, $id_meeting, 'pjbl');
$forum = getKonten($koneksi, $id_meeting, 'forum');
$link = getKonten($koneksi, $id_meeting, 'link');
$deadline = getKonten($koneksi, $id_meeting, 'deadline');
$absen = getKonten($koneksi, $id_meeting, 'absen');


// Ambil id_courses berdasarkan id_meeting
$id_courses = '';
$query = "SELECT id_courses, title FROM course_meetings WHERE id_meeting = ?";
$stmt = mysqli_prepare($koneksi, $query);
mysqli_stmt_bind_param($stmt, "i", $id_meeting);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($data = mysqli_fetch_assoc($result)) {
    $id_courses = $data['id_courses']; // disiapkan untuk breadcrumb
    $judul_pertemuan = $data['title'];
}

// Ambil data PjBL beserta nama project
$pjbl = [];
$query_pjbl = mysqli_query($koneksi, "
    SELECT mc.*, pp.nama_project 
    FROM meeting_contents mc 
    LEFT JOIN pjbl_project pp ON mc.id_project = pp.id_project 
    WHERE mc.id_meeting = '$id_meeting' AND mc.type = 'pjbl'
");
while ($row = mysqli_fetch_assoc($query_pjbl)) {
    $pjbl[] = $row;
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konten Pertemuan | Belajaro</title>
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

        .list-group-item {
            border-left: 0;
            border-right: 0;
        }

        .list-group-item:first-child {
            border-top: 0;
        }

        .list-group-item:last-child {
            border-bottom: 0;
        }

        .badge {
            padding: 0.35em 0.65em;
            font-weight: 500;
        }

        .btn-group .btn {
            border-radius: 0.375rem !important;
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
                                    📝 Konten dari Kursus:
                                    <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary"><?= htmlspecialchars($judul_pertemuan) ?></span>
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="kelola_pertemuan.php?id_courses=<?= $id_courses ?>">Pertemuan</a></li>
                                        <li class="breadcrumb-item active">Konten</li>
                                    </ol>
                                </nav>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#pilihKontenModal">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Konten
                                </button>
                            </div>
                        </div>

                        <?php
                        function renderList($judul, $icon, $konten, $jenis = '')
                        {
                            $icon_class_map = [
                                '📄' => 'bi-file-earmark-text text-info',
                                '🧪' => 'bi-clipboard-check text-warning',
                                '✅' => 'bi-check-circle text-success',
                                '👥' => 'bi-people-fill text-primary',
                                '💬' => 'bi-chat-left-text text-secondary',
                            ];
                            $icon_class = $icon_class_map[trim($icon)] ?? 'bi-collection text-muted';

                            echo '<div class="card shadow-sm border-0 mb-4">';
                            echo '<div class="card-header bg-white border-0 py-3">';
                            echo '<h5 class="fw-semibold mb-0"><i class="bi ' . $icon_class . ' me-2"></i>' . $judul . '</h5>';
                            echo '</div>';
                            echo '<div class="card-body">';

                            foreach ($konten as $k) {
                                echo '<div class="card mb-3 shadow-sm border-0">';
                                echo '<div class="card-body">';

                                echo '<div class="d-flex justify-content-between align-items-center mb-2">';
                                echo '<h6 class="fw-bold mb-0">📌 ' . htmlspecialchars($k['title']) . '</h6>';
                                echo '<div>';
                                if (!empty($k['file_path'])) echo '<span class="badge bg-info-subtle text-info me-1"><i class="bi bi-file-earmark"></i></span>';
                                if (!empty($k['link_url'])) echo '<span class="badge bg-primary-subtle text-primary"><i class="bi bi-link-45deg"></i></span>';
                                echo '</div></div>';

                                if (!empty($k['description']))
                                    echo '<p class="text-muted small mb-1"><strong>📝 Deskripsi:</strong> ' . nl2br(htmlspecialchars($k['description'])) . '</p>';
                                if (!empty($k['link_url']))
                                    echo '<p class="small mb-1"><strong>🔗 Link:</strong> <a href="' . htmlspecialchars($k['link_url']) . '" target="_blank">' . htmlspecialchars($k['link_url']) . '</a></p>';
                                if (!empty($k['deadline']))
                                    echo '<p class="text-muted small mb-1"><strong>⏰ Deadline:</strong> ' . htmlspecialchars($k['deadline']) . '</p>';
                                if (!empty($k['nama_project']))
                                    echo '<p class="text-muted small mb-1"><strong>📁 Nama Project:</strong> ' . htmlspecialchars($k['nama_project']) . '</p>';
                                if ($k['type'] === 'quiz' && !empty($k['duration_minutes']))
                                    echo '<p class="text-muted small mb-1"><strong>⏱️ Durasi Quiz:</strong> ' . htmlspecialchars($k['duration_minutes']) . ' menit</p>';

                                echo '<div class="d-flex justify-content-between align-items-center mt-3">';
                                echo '<div class="d-flex flex-wrap gap-2">';

                                if (!empty($k['file_path'])) {
                                    echo '<a href="uploads/' . htmlspecialchars($k['file_path']) . '" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm px-3">';
                                    echo '<i class="bi bi-eye-fill"></i> Lihat File</a>';
                                }

                                if (!empty($k['link_url'])) {
                                    echo '<a href="' . htmlspecialchars($k['link_url']) . '" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm px-3">';
                                    echo '<i class="bi bi-box-arrow-up-right me-1"></i> Link</a>';
                                }

                                if ($k['type'] === 'tugas' || $k['type'] === 'pjbl') {
                                    echo '<a href="lihat_pengumpulan.php?id_content=' . $k['id_content'] . '" class="btn btn-sm btn-outline-success rounded-pill shadow-sm px-3">';
                                    echo '<i class="bi bi-folder-check"></i> Pengumpulan</a>';
                                }

                                if ($k['type'] === 'quiz') {
                                    echo '<a href="kelola_soal.php?id_content=' . $k['id_content'] . '" class="btn btn-sm btn-outline-warning rounded-pill shadow-sm px-3">';
                                    echo '<i class="bi bi-ui-checks-grid"></i> Kelola Soal</a>';
                                    echo '<a href="lihat_hasil_quiz.php?id_content=' . $k['id_content'] . '" class="btn btn-sm btn-outline-info rounded-pill shadow-sm px-3">';
                                    echo '<i class="bi bi-bar-chart-line"></i> Hasil</a>';
                                }

                                if ($k['type'] === 'forum') {
                                    echo '<a href="lihat_forum.php?id_content=' . $k['id_content'] . '" class="btn btn-sm btn-outline-secondary rounded-pill shadow-sm px-3">';
                                    echo '<i class="bi bi-chat-dots"></i> Forum</a>';
                                }

                                if ($k['type'] === 'absen') {
                                    echo '<a href="lihat_absensi.php?id_content=' . $k['id_content'] . '" class="btn btn-sm btn-outline-success rounded-pill shadow-sm px-3">';
                                    echo '<i class="bi bi-person-check"></i> Lihat Absensi</a>';
                                }

                                echo '</div>';

                                echo '<div class="d-flex gap-2">';
                                echo '<a href="form_konten.php?id=' . $k['id_content'] . '&mode=edit&type=' . $k['type'] . '&id_meeting=' . $k['id_meeting'] . '" class="btn btn-sm btn-outline-primary rounded-pill shadow-sm px-3">';
                                echo '<i class="bi bi-pencil me-1"></i>Edit</a>';

                                echo '<a href="hapus_konten.php?id=' . $k['id_content'] . '&id_meeting=' . $k['id_meeting'] . '" class="btn btn-sm btn-outline-danger rounded-pill shadow-sm px-3" onclick="return confirm(\'Yakin?\')">';
                                echo '<i class="bi bi-trash me-1"></i>Hapus</a>';
                                echo '</div>';

                                echo '</div>'; // tombol wrapper
                                echo '</div>'; // card-body
                                echo '</div>'; // card
                            }

                            echo '</div>'; // card-body
                            echo '</div>'; // card
                        }

                        // Hanya tampilkan jika ada isinya
                        if (!empty($materi)) {
                            renderList('Materi', '📄', $materi);
                        }
                        if (!empty($quiz)) {
                            renderList('Quiz', '🧪', $quiz);
                        }
                        if (!empty($tugas)) {
                            renderList('Tugas', '✅', $tugas);
                        }
                        if (!empty($pjbl)) {
                            renderList('Tugas PjBL', '👥', $pjbl);
                        }
                        if (!empty($forum)) {
                            renderList('Forum Diskusi', '💬', $forum);
                        }

                        if (!empty($absen)) {
                            renderList('Absensi', '📅', $absen);
                        }

                        ?>
                    </div>

                </div>
            </main>

            <?php include 'footer.php'; ?>
        </div>
    </div>
    <!-- Modal Pilih Jenis Konten -->
    <div class="modal fade" id="pilihKontenModal" tabindex="-1" aria-labelledby="pilihKontenModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="pilihKontenModalLabel">Pilih Jenis Konten</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="list-group">
                        <a href="form_konten.php?id_meeting=<?= $id_meeting ?>&type=materi&mode=add" class="list-group-item list-group-item-action">📄 Materi</a>
                        <a href="form_konten.php?id_meeting=<?= $id_meeting ?>&type=quiz&mode=add" class="list-group-item list-group-item-action">🧪 Quiz</a>
                        <a href="form_konten.php?id_meeting=<?= $id_meeting ?>&type=tugas&mode=add" class="list-group-item list-group-item-action">✅ Tugas Individu</a>
                        <a href="form_konten.php?id_meeting=<?= $id_meeting ?>&type=pjbl&mode=add" class="list-group-item list-group-item-action">👥 Tugas PjBL</a>
                        <a href="form_konten.php?id_meeting=<?= $id_meeting ?>&type=forum" class="list-group-item list-group-item-action">💬 Forum Diskusi</a>
                        <a href="form_konten.php?id_meeting=<?= $id_meeting ?>&type=absen&mode=add" class="list-group-item list-group-item-action">📅 Absensi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>