<?php
session_start(); // Mulai session
require_once 'koneksi.php'; // Include koneksi database

$id_user = $_SESSION['user_id']; // Ambil ID user dari session

// ========== CEK HAK AKSES USER ==========
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php"); // Jika bukan siswa atau admin, redirect ke login
    exit();
}

$cari = $_GET['cari'] ?? '';

$query = "
SELECT 
    pt.*, 
    u.name AS nama_siswa, 
    ak.peran,
    k.nama_kelompok, 
    mc.title AS judul_pjbl,
    cm.meeting_number,
    cm.title AS judul_pertemuan,
    c.course_name
FROM pengumpulan_tugas pt
LEFT JOIN users u ON pt.id_user = u.id_user
LEFT JOIN kelompok k ON pt.id_kelompok = k.id_kelompok
LEFT JOIN anggota_kelompok ak ON pt.id_user = ak.id_user AND pt.id_kelompok = ak.id_kelompok
LEFT JOIN meeting_contents mc ON pt.id_content = mc.id_content
LEFT JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
LEFT JOIN courses c ON cm.id_courses = c.id_courses
WHERE mc.type = 'pjbl'
";

if (!empty($cari)) {
    $cari_safe = mysqli_real_escape_string($koneksi, $cari);
    $query .= " AND (
        u.name LIKE '%$cari_safe%' OR 
        mc.title LIKE '%$cari_safe%' OR 
        k.nama_kelompok LIKE '%$cari_safe%' OR
        cm.title LIKE '%$cari_safe%' OR
        c.course_name LIKE '%$cari_safe%'
    )";
}

$query .= " ORDER BY cm.meeting_number ASC, k.nama_kelompok ASC, u.name ASC";

$result = mysqli_query($koneksi, $query);
if (!$result) {
    die("Query error: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Nilai PjBL | Belajaro</title>
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

        .table {
            --bs-table-striped-bg: rgba(0, 0, 0, 0.02);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.03);
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
                        <h2 class="mb-4 fw-bold">📦 Daftar Nilai Project-Based Learning (PjBL)</h2>

                        <!-- Form Pencarian -->
                        <div class="card-body">
                            <form method="get" action="nilai_pjbl.php" class="row g-2 align-items-center mb-4">
                                <!-- Input Cari -->
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                                        <input type="text" class="form-control" id="cari" name="cari" placeholder="Cari nama siswa / kelompok / tugas..." value="<?= htmlspecialchars($cari) ?>">
                                    </div>
                                </div>

                                <!-- Tombol Cari -->
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-search"></i> Cari
                                    </button>
                                </div>

                                <!-- Tombol Reset -->
                                <div class="col-auto">
                                    <a href="nilai_pjbl.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-clockwise"></i> Reset
                                    </a>
                                </div>
                            </form>
                        </div>

                        <!-- Tabel Nilai PjBL -->
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" width="50">No</th>
                                            <th>Nama Siswa (Peran)</th>
                                            <th>Kelompok</th>
                                            <th>Nama Kursus</th>
                                            <th>Judul PjBL</th>
                                            <th>Pertemuan</th>
                                            <th class="text-center">Nilai</th>
                                            <th>Waktu Kumpul</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        while ($row = mysqli_fetch_assoc($result)):
                                            $nama_siswa = htmlspecialchars($row['nama_siswa']);
                                            $peran = $row['peran'] ? " ({$row['peran']})" : "";
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $no++ ?></td>
                                                <td>
                                                    <div class="fw-medium"><?= $nama_siswa . $peran ?></div>
                                                </td>
                                                <td><span class="text-muted"><?= htmlspecialchars($row['nama_kelompok'] ?? '-') ?></span></td>
                                                <td><span class="text-muted"><?= htmlspecialchars($row['course_name'] ?? '-') ?></span></td>
                                                <td><span><?= htmlspecialchars($row['judul_pjbl']) ?></span></td>
                                                <td><span>Pertemuan <?= $row['meeting_number'] ?> - <?= htmlspecialchars($row['judul_pertemuan']) ?></span></td>
                                                <td class="text-center">
                                                    <?php if (is_null($row['nilai'])): ?>
                                                        <span class="text-muted fst-italic">-</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success"><?= $row['nilai'] ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d M Y H:i', strtotime($row['waktu_kumpul'])) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
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