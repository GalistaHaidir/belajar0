<?php
session_start(); // Mulai session
require_once 'koneksi.php'; // Include koneksi database

$id_user = $_SESSION['user_id']; // Ambil ID user dari session

// ========== CEK HAK AKSES USER ==========
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php"); // Jika bukan siswa atau admin, redirect ke login
    exit();
}

// ========== TRACKING KONTEN YANG DIBUKA (opsional melalui GET) ==========
if (isset($_GET['track']) && $_GET['track'] == 1 && isset($_GET['id_content'])) {
    $id_user = $_SESSION['user_id'];
    $id_content = intval($_GET['id_content']);

    // Cek apakah sudah pernah dibuka
    $stmtCek = $koneksi->prepare("SELECT status FROM content_activity WHERE id_user = ? AND id_content = ?");
    $stmtCek->bind_param("ii", $id_user, $id_content);
    $stmtCek->execute();
    $resultCek = $stmtCek->get_result();
    $dataCek = $resultCek->fetch_assoc();

    if (!$dataCek) {
        // Belum ada record, insert
        $stmtTrack = $koneksi->prepare("INSERT INTO content_activity (id_user, id_content, status)
            VALUES (?, ?, 'opened')");
        $stmtTrack->bind_param("ii", $id_user, $id_content);
        $stmtTrack->execute();
    }
    // ✅ Tidak perlu update kalau sudah pernah dibuka
}

// ========== VALIDASI ID MEETING ==========
if (!isset($_GET['id_meeting'])) {
    echo "ID pertemuan tidak valid.";
    exit();
}

$id_meeting = intval($_GET['id_meeting']);

// ========== AMBIL DETAIL MEETING ==========
$sqlMeeting = "
    SELECT cm.meeting_number, cm.title, cm.description, co.course_name
    FROM course_meetings cm
    JOIN courses co ON cm.id_courses = co.id_courses
    WHERE cm.id_meeting = ?
";
$stmt = $koneksi->prepare($sqlMeeting);
$stmt->bind_param("i", $id_meeting);
$stmt->execute();
$resultMeeting = $stmt->get_result();
$meeting = $resultMeeting->fetch_assoc();

if (!$meeting) {
    echo "Pertemuan tidak ditemukan.";
    exit();
}

// ========== AMBIL ID KELOMPOK SISWA (JIKA ADA) ==========
$id_kelompok_siswa = [];
$sqlKelompok = "
    SELECT ak.id_kelompok FROM anggota_kelompok ak
    JOIN kelompok k ON ak.id_kelompok = k.id_kelompok
    JOIN pjbl_project p ON k.id_project = p.id_project
    JOIN course_meetings cm ON p.id_courses = cm.id_courses
    WHERE ak.id_user = ? AND cm.id_meeting = ?
";
$stmtKelompok = $koneksi->prepare($sqlKelompok);
$stmtKelompok->bind_param("ii", $id_user, $id_meeting);
$stmtKelompok->execute();
$resultKelompok = $stmtKelompok->get_result();
while ($row = $resultKelompok->fetch_assoc()) {
    $id_kelompok_siswa[] = $row['id_kelompok'];
}

// ========== BANGUN FILTER SQL UNTUK KONTEN YANG BOLEH DIAKSES SISWA ==========
$filter_kelompok = '';
if (!empty($id_kelompok_siswa)) {
    // Buat placeholder `?` sebanyak jumlah ID kelompok
    $placeholders = implode(',', array_fill(0, count($id_kelompok_siswa), '?'));
    $filter_kelompok = "OR id_kelompok IN ($placeholders)";
}

// ========== QUERY KONTEN MEETING YANG DITAMPILKAN ==========
$sqlKonten = "
    SELECT * FROM meeting_contents 
    WHERE id_meeting = ? AND (id_kelompok IS NULL $filter_kelompok)
";

$stmtKonten = $koneksi->prepare($sqlKonten);

// Gabungkan parameter id_meeting dan id_kelompok ke satu array
$params = array_merge([$id_meeting], $id_kelompok_siswa);
$types = str_repeat("i", count($params)); // semua parameter bertipe integer

$stmtKonten->bind_param($types, ...$params); // Binding parameter
$stmtKonten->execute();
$resultKonten = $stmtKonten->get_result();

$konten = [];
while ($row = $resultKonten->fetch_assoc()) {
    $konten[] = $row; // Simpan semua konten ke array
}

// ========== AMBIL INFO KELOMPOK (NAMA & ANGGOTA) JIKA SISWA PUNYA KELOMPOK ==========
$kelompokInfo = [];
if (!empty($id_kelompok_siswa)) {
    $id_kelompok_utama = $id_kelompok_siswa[0]; // Ambil salah satu ID kelompok saja

    // Ambil nama kelompok
    $stmtNamaKelompok = $koneksi->prepare("
        SELECT nama_kelompok FROM kelompok WHERE id_kelompok = ?
    ");
    $stmtNamaKelompok->bind_param("i", $id_kelompok_utama);
    $stmtNamaKelompok->execute();
    $resNamaKelompok = $stmtNamaKelompok->get_result();
    $nama_kelompok = $resNamaKelompok->fetch_assoc()['nama_kelompok'] ?? '';

    // Ambil daftar anggota kelompok
    $stmtAnggota = $koneksi->prepare("
    SELECT u.name, ak.peran FROM anggota_kelompok ak
    JOIN users u ON ak.id_user = u.id_user
    WHERE ak.id_kelompok = ?
");

    $stmtAnggota->bind_param("i", $id_kelompok_utama);
    $stmtAnggota->execute();
    $resAnggota = $stmtAnggota->get_result();

    $anggota_kelompok = [];
    while ($row = $resAnggota->fetch_assoc()) {
        $anggota_kelompok[] = $row['name'] . ' (' . $row['peran'] . ')';
    }

    // Simpan semua info kelompok
    $kelompokInfo = [
        'nama_kelompok' => $nama_kelompok,
        'anggota' => $anggota_kelompok
    ];
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
                        <div class="bg-primary-subtle p-4 rounded shadow-sm mb-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                                <div>
                                    <h2 class="fw-bold text-primary-emphasis mb-1">
                                        <i class="bi bi-journal-bookmark-fill me-2"></i><?= htmlspecialchars($meeting['course_name']); ?>
                                    </h2>
                                    <h4 class="text-dark-emphasis">
                                        Pertemuan <?= $meeting['meeting_number']; ?>:
                                        <span class="text-dark"><?= htmlspecialchars($meeting['title']); ?></span>
                                    </h4>
                                    <p class="text-muted mb-2"><?= nl2br(htmlspecialchars($meeting['description'])); ?></p>
                                </div>
                            </div>
                            <nav aria-label="breadcrumb" class="mt-3">
                                <ol class="breadcrumb small">
                                    <li class="breadcrumb-item"><a href="kursusku.php">Kursusku</a></li>
                                    <li class="breadcrumb-item"><a href="pertemuan.php">Daftar Pertemuan</a></li>
                                    <li class="breadcrumb-item active">Detail Pertemuan</li>
                                </ol>
                            </nav>
                        </div>

                        <!-- Konten Section -->
                        <h5 class="fw-bold mb-3 text-secondary-emphasis">📦 Konten dalam pertemuan ini</h5>
                        <?php if (!empty($kelompokInfo)): ?>
                            <div class="alert alert-info shadow-sm">
                                <h6 class="mb-1 text-primary-emphasis">
                                    <i class="bi bi-people-fill me-2"></i> Anda tergabung dalam <strong><?= htmlspecialchars($kelompokInfo['nama_kelompok']); ?></strong>
                                </h6>
                                <div class="mb-0 small text-dark-emphasis">
                                    <strong>Anggota Kelompok:</strong>
                                    <ul class="mb-0 ps-3">
                                        <?php foreach ($kelompokInfo['anggota'] as $anggota): ?>
                                            <li><?= htmlspecialchars($anggota); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (count($konten) === 0): ?>
                            <div class="alert alert-warning">
                                Tidak ada konten dalam pertemuan ini.
                            </div>
                        <?php else: ?>
                            <div class="row g-4">
                                <?php foreach ($konten as $k): ?>
                                    <?php
                                    $type = strtolower($k['type']);
                                    $typeMap = [
                                        'materi' => ['Dokumen', 'bi-file-earmark-text-fill', 'bg-primary-subtle', 'text-primary'],
                                        'tugas' => ['Tugas', 'bi-journal-check', 'bg-success-subtle', 'text-success'],
                                        'pjbl' => ['Proyek', 'bi-kanban-fill', 'bg-info-subtle', 'text-info'],
                                        'forum' => ['Forum Diskusi', 'bi-people-fill', 'bg-purple-subtle', 'text-purple'],
                                        'absen' => ['Absen Kehadiran', 'bi-clipboard-check-fill', 'bg-indigo-subtle', 'text-indigo']
                                    ];
                                    $typeLabel = $typeMap[$type][0] ?? strtoupper($type);
                                    $typeIcon = $typeMap[$type][1] ?? 'bi-file-earmark';
                                    $typeBg = $typeMap[$type][2] ?? 'bg-light';
                                    $typeColor = $typeMap[$type][3] ?? 'text-dark';

                                    // Cek apakah sudah dibaca oleh user
                                    $stmtTrack = $koneksi->prepare("SELECT status FROM content_activity WHERE id_user = ? AND id_content = ?");
                                    $stmtTrack->bind_param("ii", $id_user, $k['id_content']);
                                    $stmtTrack->execute();
                                    $resTrack = $stmtTrack->get_result();
                                    $dataTrack = $resTrack->fetch_assoc();
                                    $statusKonten = $dataTrack['status'] ?? null;

                                    ?>
                                    <div class="col-12">
                                        <div class="card shadow-sm border-0 p-0 overflow-hidden">
                                            <!-- Jenis Konten di Atas -->
                                            <div class="<?= $typeBg ?> px-4 py-2 d-flex align-items-center gap-2">
                                                <i class="bi <?= $typeIcon ?> <?= $typeColor ?>"></i>
                                                <strong class="<?= $typeColor ?>"><?= $typeLabel ?></strong>
                                            </div>

                                            <!-- Isi Konten -->
                                            <div class="card-body">
                                                <h5 class="card-title fw-semibold"><?= htmlspecialchars($k['title']); ?></h5>
                                                <p class="text-muted small mb-3"><?= nl2br(htmlspecialchars($k['description'])); ?></p>

                                                <div class="mb-2 d-flex flex-wrap gap-2">
                                                    <?php if (!empty($k['file_path'])): ?>
                                                        <a href="uploads/<?= htmlspecialchars($k['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Lihat File
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if ($type === 'materi'): ?>
                                                        <?php if ($statusKonten !== 'opened'): ?>
                                                            <a href="detail_pertemuan.php?id_meeting=<?= $id_meeting ?>&id_content=<?= $k['id_content']; ?>&track=1" class="btn btn-sm btn-outline-success">
                                                                <i class="bi bi-check-circle me-1"></i> Tandai Sudah Dibaca
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="badge bg-success-subtle text-success small">
                                                                <i class="bi bi-check-circle-fill me-1"></i> Sudah dibaca
                                                            </span>
                                                        <?php endif; ?>

                                                        <!-- Keterangan tambahan -->
                                                        <?php if ($statusKonten === 'opened'): ?>
                                                            <div class="mt-2 small">
                                                                <i class="bi bi-info-circle me-1"></i>
                                                                <span class="text-success">Materi telah dibuka oleh Anda.</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php endif; ?>

                                                    <?php if (!empty($k['link_url'])): ?>
                                                        <a href="<?= htmlspecialchars($k['link_url']); ?>" target="_blank" class="btn btn-sm btn-outline-info">
                                                            <i class="bi bi-link-45deg me-1"></i> Buka Link
                                                        </a>
                                                    <?php endif; ?>

                                                    <?php if (!empty($k['deadline'])): ?>
                                                        <?php
                                                        $isLate = strtotime('now') > strtotime($k['deadline']);
                                                        $deadlineClass = $isLate ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-danger';
                                                        ?>
                                                        <span class="badge <?= $deadlineClass ?> small">
                                                            <i class="bi bi-clock me-1"></i> Deadline: <?= htmlspecialchars($k['deadline']); ?>
                                                            <?php if ($isLate): ?>
                                                                <span class="ms-1 fw-semibold">(Waktu Habis, periksa apakah kamu sudah mengerjakan tugas 🤔)</span>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php endif; ?>

                                                </div>

                                                <?php
                                                $isPjbl = !empty($k['id_project']);
                                                $sudahMengumpulkan  = false;
                                                $nilai = null;
                                                $catatanGuru = '';
                                                $status_acc = null;

                                                if ($isPjbl) {
                                                    // Cek ID kelompok siswa untuk proyek ini
                                                    $stmtCekKelompok = $koneksi->prepare("SELECT ak.id_kelompok 
        FROM anggota_kelompok ak 
        JOIN kelompok k ON ak.id_kelompok = k.id_kelompok 
        WHERE ak.id_user = ? AND k.id_project = ?");
                                                    $stmtCekKelompok->bind_param("ii", $_SESSION['user_id'], $k['id_project']);
                                                    $stmtCekKelompok->execute();
                                                    $resultKelompok = $stmtCekKelompok->get_result();
                                                    $kelompok = $resultKelompok->fetch_assoc();
                                                    $id_kelompok = $kelompok['id_kelompok'] ?? null;

                                                    // Cek apakah ada pengumpulan oleh kelompok ini
                                                    if ($id_kelompok) {
                                                        $stmtPengumpulan = $koneksi->prepare("SELECT * FROM pengumpulan_tugas WHERE id_content = ? AND id_kelompok = ?");
                                                        $stmtPengumpulan->bind_param("ii", $k['id_content'], $id_kelompok);
                                                        $stmtPengumpulan->execute();
                                                        $resultPengumpulan = $stmtPengumpulan->get_result();
                                                        $dataPengumpulan = $resultPengumpulan->fetch_assoc();
                                                        $sudahMengumpulkan = $dataPengumpulan ? true : false;

                                                        // Ambil tambahan info
                                                        $nilai = $dataPengumpulan['nilai'] ?? null;
                                                        $catatanGuru = $dataPengumpulan['catatan'] ?? '';
                                                        $status_acc = $dataPengumpulan['status_acc'] ?? null;
                                                    }
                                                } else {
                                                    // Tugas individu
                                                    $stmtPengumpulan = $koneksi->prepare("SELECT * FROM pengumpulan_tugas WHERE id_content = ? AND id_user = ?");
                                                    $stmtPengumpulan->bind_param("ii", $k['id_content'], $_SESSION['user_id']);
                                                    $stmtPengumpulan->execute();
                                                    $resultPengumpulan = $stmtPengumpulan->get_result();
                                                    $sudahMengumpulkan  = $resultPengumpulan->num_rows > 0;
                                                }
                                                ?>

                                                <?php if (in_array($type, ['tugas', 'pjbl'])): ?>
                                                    <a href="upload_tugas.php?id_content=<?= $k['id_content']; ?>&track=1"
                                                        class="btn btn-sm <?= $sudahMengumpulkan ? 'btn-warning text-dark' : 'btn-success' ?> mt-2">
                                                        <i class="bi bi-upload me-1"></i>
                                                        <?= $sudahMengumpulkan ? 'Edit Tugas' : 'Kumpulkan Tugas' ?>
                                                    </a>
                                                    <?php if (!is_null($nilai)): ?>
                                                        <div class="mt-2 small text-primary">
                                                            <i class="bi bi-award-fill me-1"></i> Nilai: <strong><?= htmlspecialchars($nilai); ?></strong>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if (!empty($catatanGuru)): ?>
                                                        <div class="mt-1 small text-muted fst-italic">
                                                            <i class="bi bi-chat-left-dots me-1"></i> Catatan: <?= nl2br(htmlspecialchars($catatanGuru)); ?>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($isPjbl && isset($status_acc)): ?>
                                                        <div class="mt-2 small">
                                                            <i class="bi bi-info-circle me-1"></i>
                                                            <?php if ($status_acc === 'approved'): ?>
                                                                <span class="text-success fw-semibold">Proyek kamu telah <u>DISETUJUI</u> oleh guru.</span>
                                                            <?php elseif ($status_acc === 'rejected'): ?>
                                                                <span class="text-danger fw-semibold">Proyek kamu telah <u>DITOLAK</u>. Silakan perbaiki dan kumpulkan ulang.</span>
                                                            <?php else: ?>
                                                                <span class="text-muted fst-italic">Status proyek belum diperiksa oleh guru.</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>

                                                <?php endif; ?>


                                                <?php if ($type === 'forum'): ?>
                                                    <a href="lihat_forum_siswa.php?id_content=<?= $k['id_content']; ?>&track=1" class="btn btn-sm btn-warning text-dark mt-2">
                                                        <i class="bi bi-chat-dots me-1"></i> Masuk Forum Diskusi
                                                    </a>
                                                <?php endif; ?>

                                                <?php if ($type === 'quiz'): ?>
                                                    <a href="kerjakan_quiz.php?id_content=<?= $k['id_content']; ?>&track=1" class="btn btn-sm btn-primary mt-2">
                                                        <i class="bi bi-ui-checks-grid me-1"></i> Kerjakan Quiz
                                                    </a>
                                                    <?php
                                                    // Cek apakah siswa sudah pernah mengerjakan quiz ini
                                                    $stmtQuiz = $koneksi->prepare("SELECT score FROM quiz_result WHERE id_user = ? AND id_content = ?");
                                                    $stmtQuiz->bind_param("ii", $_SESSION['user_id'], $k['id_content']);
                                                    $stmtQuiz->execute();
                                                    $resultQuiz = $stmtQuiz->get_result();
                                                    $quizData = $resultQuiz->fetch_assoc();

                                                    if ($quizData):
                                                    ?>
                                                        <span class="badge bg-success-subtle text-success small">
                                                            <i class="bi bi-check-circle-fill me-1"></i> Skor Quiz: <strong><?= $quizData['score']; ?> / 100</strong>
                                                        </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <!-- TOMBOL ABSENSI -->
                                                <?php if ($type === 'absen'): ?>
                                                    <a href="absensi.php?id_content=<?= $k['id_content']; ?>&id_meeting=<?= $id_meeting; ?>" class="btn btn-sm btn-outline-success mt-2">
                                                        <i class="bi bi-fingerprint me-1"></i> Absen Sekarang
                                                    </a>
                                                <?php endif; ?>

                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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