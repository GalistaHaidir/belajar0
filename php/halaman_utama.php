<?php
session_start();

include 'koneksi.php'; // Koneksi ke database

if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
}

$sessionUsername = $_SESSION['admin_username'];

// Ambil data user dari database
$query = "SELECT fotoProfil FROM pengguna WHERE username = '$sessionUsername'";
$result = mysqli_query($koneksi, $query);

// Periksa apakah ada hasil
if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    $fotoProfil = $data['fotoProfil'];
} else {
    // Jika data tidak ditemukan, set nilai default
    $fotoProfil = "default.jpg";
}


$query = "SELECT COUNT(*) as total FROM materi";
$result = $koneksi->query($query);
$row = $result->fetch_assoc();
$totalMateri = $row['total'];

$query = "SELECT COUNT(*) as total FROM tugas";
$result = $koneksi->query($query);
$row = $result->fetch_assoc();
$totalTugas = $row['total'];

$query = "SELECT COUNT(*) as total FROM soal";
$result = $koneksi->query($query);
$row = $result->fetch_assoc();
$totalSoal = $row['total'];

// Query untuk menampilkan 3 materi terbaru berdasarkan ID terbesar
$queryMateri = "SELECT id_materi, title FROM materi ORDER BY id_materi DESC LIMIT 3";
$resultMateri = $koneksi->query($queryMateri);



// Ambil bulan dan tahun dari parameter URL, atau gunakan bulan dan tahun saat ini
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Hitung jumlah hari dalam bulan yang dipilih
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Ambil semua dateline yang memiliki tugas
$sql_dateline = "SELECT DISTINCT DATE(dateline) AS dateline FROM tugas";
$result_dateline = mysqli_query($koneksi, $sql_dateline);
$dateline_tugas = [];
while ($row = mysqli_fetch_assoc($result_dateline)) {
    $dateline_tugas[] = $row['dateline']; // Sekarang hanya YYYY-MM-DD
}


// Ambil daftar tugas berdasarkan dateline
if (isset($_GET['dateline'])) {
    $dateline = $_GET['dateline'];
    $sql = "SELECT * FROM tugas WHERE dateline = '$dateline'";
    $result = mysqli_query($koneksi, $sql);
    $tugas = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $dateline = null;
    $tugas = [];
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Utama</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">
    <style>
        /* Styling body */
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            color: #1B5E20;
            /* Warna hijau tua */
            font-family: 'Poppins', sans-serif;
        }

        /* Styling Container Utama */
        .main-content {
            padding: 20px;
        }

        /* Styling Card Modern */
        .card-modern {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
            color: #2E7D32;
            /* Hijau lebih kuat */
        }

        .card-modern:hover {
            transform: translateY(-5px);
        }

        /* Styling Header */
        .header {
            text-align: center;
            margin-bottom: 30px;
            color: #1B5E20;
        }

        .header p {
            color: #424242;
            /* Abu-abu gelap untuk deskripsi */
            font-weight: 550;
        }

        /* Styling List */
        .list-group-item {
            background: rgba(255, 255, 255, 0.2);
            color: rgb(0, 0, 0);
            border: none;
            font-weight: 500;
        }

        .list-group-item span {
            font-weight: bold;
            color: rgb(255, 7, 7);
            font-weight: 780;
            /* Warna kuning untuk highlight */
        }

        /* Teks di dalam Card */
        .card-modern h5 {
            font-weight: bold;
            color: #FFFFFF;
            /* Warna putih agar terlihat jelas */
            background: rgb(0, 67, 4);
            /* Warna hijau tua */
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }

        .card-modern p {
            font-size: 18px;
            color: rgb(0, 0, 0);
        }

        h2 {
            font-weight: 850;
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin: 20px 0;
        }

        .day {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            cursor: pointer;
            border-radius: 5px;
        }

        .day.today {
            background-color: #d4edda;
            /* Warna untuk hari ini */
            color: #155724;
            font-weight: bold;
            border: 2px solid #28a745;
        }

        .day.has-task {
            background-color: rgb(237, 212, 212);
            /* Warna untuk hari ini */
            color: rgb(87, 21, 21);
            font-weight: bold;
            border: 2px solid #a72828;
        }

        .day:hover {
            background-color: #0056b3;
            color: white;
        }

        .task-list {
            margin-top: 20px;
        }
    </style>

</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <div class="main-content">
                    <div class="container">

                        <!-- Header -->
                        <div class="header">
                            <h2>📚 Dashboard </h2>
                        </div>

                        <!-- Statistik Card -->
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="card-modern">
                                    <h5><i class="bi bi-book"></i> Materi</h5>
                                    <p>Total: <span><?php echo $totalMateri; ?></span></p>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card-modern">
                                    <h5><i class="bi bi-clipboard-check"></i> Soal</h5>
                                    <p>Total: <span><?php echo $totalSoal; ?></span></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card-modern">
                                    <h5><i class="bi bi-journal"></i> Tugas</h5>
                                    <p>Total: <span><?php echo $totalTugas; ?></span></p>
                                </div>
                            </div>
                        </div>

                        <!-- Tugas & Materi -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card-modern">
                                    <h5>📌 Tugas pada Kalender</h5>

                                    <!-- Navigasi Dropdown -->
                                    <form method="GET" class="d-flex justify-content-center align-items-center mb-4 gap-3">
                                        <label for="bulan" class="form-label">Bulan:</label>
                                        <select name="bulan" id="bulan" class="form-select w-auto" onchange="this.form.submit()">
                                            <?php
                                            for ($i = 1; $i <= 12; $i++) {
                                                $selected = ($i == $bulan) ? 'selected' : '';
                                                echo "<option value='" . str_pad($i, 2, '0', STR_PAD_LEFT) . "' $selected>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
                                            }
                                            ?>
                                        </select>

                                        <label for="tahun" class="form-label">Tahun:</label>
                                        <select name="tahun" id="tahun" class="form-select w-auto" onchange="this.form.submit()">
                                            <?php
                                            $current_year = date('Y');
                                            for ($i = $current_year - 5; $i <= $current_year + 5; $i++) {
                                                $selected = ($i == $tahun) ? 'selected' : '';
                                                echo "<option value='$i' $selected>$i</option>";
                                            }
                                            ?>
                                        </select>
                                    </form>

                                    <!-- Kalender -->
                                    <div class="calendar">
                                        <?php
                                        $today = date('Y-m-d'); // Format YYYY-MM-DD

                                        for ($i = 1; $i <= $days_in_month; $i++) {
                                            $current_date = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

                                            // Pastikan hanya mengecek tanggal (tanpa waktu)
                                            $has_task = in_array($current_date, $dateline_tugas) ? 'has-task' : '';
                                            $is_today = ($current_date == $today) ? 'today' : '';

                                            echo "<div class='day $has_task $is_today' onclick=\"showTasks('$current_date')\">$i</div>";
                                        }

                                        ?>
                                    </div>

                                    <!-- Daftar Tugas -->
                                    <div class="task-list mt-4">
                                        <h3 class="mb-3">Tugas pada dateline: <?php echo $dateline ? $dateline : 'Pilih dateline'; ?></h3>
                                        <ul class="list-group">
                                            <?php if ($tugas): ?>
                                                <?php foreach ($tugas as $t): ?>
                                                    <li class="list-group-item"><?php echo $t['judul_tugas']; ?></li>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <li class="list-group-item">Tidak ada tugas pada dateline ini.</li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>


                            <div class="col-md-6">
                                <div class="card-modern">
                                    <h5>📚 Materi Terbaru</h5>
                                    <ul class="list-group">
                                        <?php if ($resultMateri->num_rows > 0) { ?>
                                            <?php while ($row = $resultMateri->fetch_assoc()) { ?>
                                                <li class="list-group-item">
                                                    📖 <strong><?= $row['title']; ?></strong>
                                                </li>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <li class="list-group-item text-muted">Belum ada materi.</li>
                                        <?php } ?>
                                    </ul>
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
    <script>
        function showTasks(dateline) {
            window.location.href = "?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>&dateline=" + dateline;
        }
    </script>

</body>

</html>