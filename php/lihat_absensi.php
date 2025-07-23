<?php
session_start();
include 'koneksi.php';

// ========== CEK HAK AKSES USER ==========
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'siswa' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php"); // Jika bukan siswa atau admin, redirect ke login
    exit();
}

$id_content = $_GET['id_content'] ?? '';
if (empty($id_content)) {
    echo "ID konten absensi tidak ditemukan.";
    exit;
}

// Ambil ID kursus dari konten absensi
$query_course = mysqli_query($koneksi, "
    SELECT cm.id_courses, cm.id_meeting
    FROM meeting_contents mc
    JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
    WHERE mc.id_content = '$id_content'
") or die("Query Error: " . mysqli_error($koneksi));

$data_course = mysqli_fetch_assoc($query_course);
$id_courses = $data_course['id_courses'] ?? null;
$id_meeting = $data_course['id_meeting'] ?? null;

if (!$id_courses) {
    echo "Data kursus tidak ditemukan.";
    exit;
}

// Ambil semua siswa dari kursus
$query_siswa = mysqli_query($koneksi, "
    SELECT u.id_user, u.name
    FROM users u
    JOIN course_participants cp ON u.id_user = cp.id_user
    WHERE cp.id_courses = '$id_courses' AND u.role = 'siswa'
");

$siswa = [];
while ($row = mysqli_fetch_assoc($query_siswa)) {
    $siswa[$row['id_user']] = $row['name'];
}

// ✅ INI BAGIAN YANG KURANG DI KODE KAMU:
$query_absen = mysqli_query($koneksi, "
    SELECT id_user, status, bukti_izin, waktu_absen
    FROM absensi
    WHERE id_content = '$id_content'
") or die("Query Absensi Gagal: " . mysqli_error($koneksi));

// Ambil data absensi lengkap
$absensi_data = [];
while ($a = mysqli_fetch_assoc($query_absen)) {
    $absensi_data[$a['id_user']] = [
        'status' => $a['status'],
        'bukti_izin' => $a['bukti_izin'],
        'waktu' => $a['waktu_absen']
    ];
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Absensi | Belajaro</title>
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
                                <h1 class="fw-bold mb-2">📋 Daftar Absensi</h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="kelola_konten.php?id_meeting=<?= $id_meeting ?>">Konten</a></li>
                                        <li class="breadcrumb-item active">Lihat Absensi</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Hadir -->
                            <div class="col-md-4 mb-4">
                                <div class="card border-success shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-success">✅ Siswa Hadir</h5>
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            $jumlah_hadir = 0;
                                            foreach ($siswa as $id => $name) {
                                                if (isset($absensi_data[$id]) && $absensi_data[$id]['status'] === 'hadir') {
                                                    $waktu = date('H:i', strtotime($absensi_data[$id]['waktu']));
                                                    echo "<li class='list-group-item d-flex justify-content-between align-items-center'>
        <div>👤 " . htmlspecialchars($name) . "</div>
        <small class='text-muted'>$waktu</small>
      </li>";

                                                    $jumlah_hadir++;
                                                }
                                            }
                                            if ($jumlah_hadir === 0) {
                                                echo "<li class='list-group-item text-center text-muted py-4'>
                                <div style='font-size: 2rem;'>😶</div>
                                <div class='mt-2'>Tidak ada siswa hadir</div>
                              </li>";
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Izin -->
                            <div class="col-md-4 mb-4">
                                <div class="card border-warning shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-warning">📝 Siswa Izin</h5>
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            $jumlah_izin = 0;
                                            foreach ($siswa as $id => $name) {
                                                if (isset($absensi_data[$id]) && $absensi_data[$id]['status'] === 'izin') {
                                                    $jumlah_izin++;
                                                    $waktu = date('H:i', strtotime($absensi_data[$id]['waktu']));
                                                    echo "<li class='list-group-item'>
                                <div class='d-flex justify-content-between align-items-center mb-1'>
                                    <div>🧍 " . htmlspecialchars($name) . "</div>
                                    <small class='text-muted'>$waktu</small>
                                </div>";
                                                    if ($absensi_data[$id]['bukti_izin']) {
                                                        echo "<a href='" . htmlspecialchars($absensi_data[$id]['bukti_izin']) . "' target='_blank' class='btn btn-sm btn-outline-secondary'>
                                    📎 Lihat Bukti
                                  </a>";
                                                    } else {
                                                        echo "<span class='badge bg-light text-muted'>Tanpa bukti</span>";
                                                    }
                                                    echo "</li>";
                                                }
                                            }
                                            if ($jumlah_izin === 0) {
                                                echo "<li class='list-group-item text-center text-muted py-4'>
                            <div style='font-size: 2rem;'>😊</div>
                            <div class='mt-2'>Tidak ada yang izin</div>
                          </li>";
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>


                            <!-- Belum Absen -->
                            <div class="col-md-4 mb-4">
                                <div class="card border-danger shadow-sm h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-danger">❌ Belum Absen</h5>
                                        <ul class="list-group list-group-flush">
                                            <?php
                                            $jumlah_belum = 0;
                                            foreach ($siswa as $id => $name) {
                                                if (!isset($absensi_data[$id])) {
                                                    echo "<li class='list-group-item'>" . htmlspecialchars($name) . "</li>";
                                                    $jumlah_belum++;
                                                }
                                            }
                                            if ($jumlah_belum === 0) {
                                                echo "<li class='list-group-item text-center text-muted py-4'>
                                <div style='font-size: 2rem;'>✅</div>
                                <div class='mt-2'>Semua siswa sudah absen</div>
                              </li>";
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
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