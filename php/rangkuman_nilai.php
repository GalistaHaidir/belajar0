<?php
session_start(); // Mulai session
require_once 'koneksi.php'; // Include koneksi database

$id_user = $_SESSION['user_id']; // Ambil ID user dari session

// --------------------
// Ambil nilai tugas individu
// --------------------
$query_tugas = "
SELECT 
    pt.*, 
    mc.title AS judul_tugas,
    cm.meeting_number,
    cm.title AS judul_pertemuan,
    cm.id_meeting, -- ✅ tambahkan kolom ini agar bisa digunakan nanti
    c.course_name
FROM pengumpulan_tugas pt
LEFT JOIN meeting_contents mc ON pt.id_content = mc.id_content
LEFT JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
LEFT JOIN courses c ON cm.id_courses = c.id_courses
WHERE mc.type = 'tugas' AND pt.id_user = '$id_user'
ORDER BY cm.meeting_number ASC
";
$hasil_tugas = mysqli_query($koneksi, $query_tugas);

// --------------------
// Ambil id_kelompok yang diikuti siswa
// --------------------
$query_kelompok = "
SELECT id_kelompok 
FROM anggota_kelompok 
WHERE id_user = '$id_user'
";
$kelompok_result = mysqli_query($koneksi, $query_kelompok);
$kelompok_ids = [];

while ($row = mysqli_fetch_assoc($kelompok_result)) {
    $kelompok_ids[] = $row['id_kelompok'];
}

$pjbl_rows = [];
if (!empty($kelompok_ids)) {
    $kelompok_list = implode(",", $kelompok_ids);

    // --------------------
    // Ambil nilai PjBL berdasarkan kelompok siswa
    // --------------------
    $query_pjbl = "
    SELECT 
        pt.*, 
        k.nama_kelompok,
        u.name AS nama_pengirim,
        mc.title AS judul_pjbl,
        cm.meeting_number,
        cm.title AS judul_pertemuan,
        cm.id_meeting, -- ✅ tambahkan ini juga agar bisa digunakan
        c.course_name
    FROM pengumpulan_tugas pt
    LEFT JOIN meeting_contents mc ON pt.id_content = mc.id_content
    LEFT JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
    LEFT JOIN courses c ON cm.id_courses = c.id_courses
    LEFT JOIN kelompok k ON pt.id_kelompok = k.id_kelompok
    LEFT JOIN users u ON pt.id_user = u.id_user
    WHERE mc.type = 'pjbl' AND pt.id_kelompok IN ($kelompok_list)
    ORDER BY cm.meeting_number ASC
    ";

    $hasil_pjbl = mysqli_query($koneksi, $query_pjbl);

    while ($row = mysqli_fetch_assoc($hasil_pjbl)) {
        $pjbl_rows[] = $row;
    }
}

// --------------------
// Ambil salah satu id_meeting untuk breadcrumb
// --------------------
$id_meeting = null;

if (mysqli_num_rows($hasil_tugas) > 0) {
    $row = mysqli_fetch_assoc($hasil_tugas);
    if (isset($row['id_meeting'])) {
        $id_meeting = $row['id_meeting'];
    }
    mysqli_data_seek($hasil_tugas, 0); // Reset pointer
} elseif (!empty($pjbl_rows)) {
    if (isset($pjbl_rows[0]['id_meeting'])) {
        $id_meeting = $pjbl_rows[0]['id_meeting'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rangkuman Nilai Saya | Belajaro</title>
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
                                    📄 Rangkuman Nilai Saya
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

                        <!-- SECTION: Nilai Tugas Individu -->
                        <section class="mt-5">
                            <h3 class="fw-semibold mb-3">📝 Nilai Tugas Individu</h3>
                            <div class="table-responsive shadow-sm rounded-3">
                                <table class="table table-hover table-bordered align-middle text-center">
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col">No</th>
                                            <th scope="col">Judul Tugas</th>
                                            <th scope="col">Pertemuan</th>
                                            <th scope="col">Nama Kursus</th>
                                            <th scope="col">Nilai</th>
                                            <th scope="col">Waktu Kumpul</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php while ($row = mysqli_fetch_assoc($hasil_tugas)): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td class="text-start"><?= htmlspecialchars($row['judul_tugas']) ?></td>
                                                <td class="text-start">
                                                    Pertemuan <?= $row['meeting_number'] ?> - <?= htmlspecialchars($row['judul_pertemuan']) ?>
                                                </td>
                                                <td class="text-start"><?= htmlspecialchars($row['course_name']) ?></td>
                                                <td><span class="badge bg-success"><?= is_null($row['nilai']) ? '-' : $row['nilai'] ?></span></td>
                                                <td><?= date('d M Y H:i', strtotime($row['waktu_kumpul'])) ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- SECTION: Nilai Tugas PjBL -->
                        <section class="mt-5">
                            <h3 class="fw-semibold mb-3">📦 Nilai Tugas PjBL (Kelompok)</h3>
                            <div class="table-responsive shadow-sm rounded-3">
                                <table class="table table-hover table-bordered align-middle text-center">
                                    <thead class="table-warning">
                                        <tr>
                                            <th scope="col">No</th>
                                            <th scope="col">Judul PjBL</th>
                                            <th scope="col">Pertemuan</th>
                                            <th scope="col">Nama Kursus</th>
                                            <th scope="col">Kelompok</th>
                                            <th scope="col">Pengirim</th>
                                            <th scope="col">Nilai</th>
                                            <th scope="col">Waktu Kumpul</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $no = 1; ?>
                                        <?php foreach ($pjbl_rows as $row): ?>
                                            <tr>
                                                <td><?= $no++ ?></td>
                                                <td class="text-start"><?= htmlspecialchars($row['judul_pjbl']) ?></td>
                                                <td class="text-start">
                                                    Pertemuan <?= $row['meeting_number'] ?> - <?= htmlspecialchars($row['judul_pertemuan']) ?>
                                                </td>
                                                <td class="text-start"><?= htmlspecialchars($row['course_name']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_kelompok']) ?></td>
                                                <td><?= htmlspecialchars($row['nama_pengirim']) ?></td>
                                                <td><span class="badge bg-primary"><?= is_null($row['nilai']) ? '-' : $row['nilai'] ?></span></td>
                                                <td><?= date('d M Y H:i', strtotime($row['waktu_kumpul'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>



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