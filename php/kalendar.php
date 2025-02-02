<?php
include 'koneksi.php';

// Ambil bulan dan tahun dari parameter URL, atau gunakan bulan dan tahun saat ini
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Hitung jumlah hari dalam bulan yang dipilih
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

// Ambil semua tanggal yang memiliki tugas
$sql_tanggal = "SELECT DISTINCT tanggal FROM tugas";
$result_tanggal = mysqli_query($koneksi, $sql_tanggal);
$tanggal_tugas = [];
while ($row = mysqli_fetch_assoc($result_tanggal)) {
    $tanggal_tugas[] = $row['tanggal'];
}

// Ambil daftar tugas berdasarkan tanggal
if (isset($_GET['tanggal'])) {
    $tanggal = $_GET['tanggal'];
    $sql = "SELECT * FROM tugas WHERE tanggal = '$tanggal'";
    $result = mysqli_query($koneksi, $sql);
    $tugas = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $tanggal = null;
    $tugas = [];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas Berdasarkan Tanggal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            background-color: #0d6efd;
            color: white;
            font-weight: bold;
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

<body class="bg-light">
    <div class="container my-5">
        <h1 class="text-center mb-4">Daftar Tugas Berdasarkan Tanggal</h1>

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
            $today = date('Y-m-d'); // Mendapatkan tanggal hari ini dalam format YYYY-MM-DD
            for ($i = 1; $i <= $days_in_month; $i++) {
                $current_date = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
                $has_task = in_array($current_date, $tanggal_tugas) ? 'has-task' : '';
                $is_today = ($current_date == $today) ? 'today' : ''; // Tambahkan kelas 'today' jika tanggal adalah hari ini
                echo "<div class='day $has_task $is_today' onclick=\"showTasks('$current_date')\">$i</div>";
            }
            ?>
        </div>

        <!-- Daftar Tugas -->
        <div class="task-list mt-4">
            <h3 class="mb-3">Tugas pada Tanggal: <?php echo $tanggal ? $tanggal : 'Pilih tanggal'; ?></h3>
            <ul class="list-group">
                <?php if ($tugas): ?>
                    <?php foreach ($tugas as $t): ?>
                        <li class="list-group-item"><?php echo $t['tugas']; ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item">Tidak ada tugas pada tanggal ini.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showTasks(tanggal) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('tanggal', tanggal);
            window.location.search = urlParams.toString();
        }
    </script>
</body>

</html>