<?php
include 'koneksi.php';

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

// Ambil semua tanggal yang memiliki tugas
$sql_tanggal = "SELECT DISTINCT tanggal FROM tugas";
$result_tanggal = mysqli_query($koneksi, $sql_tanggal);
$tanggal_tugas = [];
while ($row = mysqli_fetch_assoc($result_tanggal)) {
    $tanggal_tugas[] = $row['tanggal'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Tugas Berdasarkan Tanggal</title>
    <style>
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin: 20px;
        }
        .day {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            cursor: pointer;
        }
        .day.has-task {
            background-color: #f0f8ff;
            font-weight: bold;
        }
        .task-list {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Daftar Tugas Berdasarkan Tanggal</h1>

    <!-- Kalender -->
    <div class="calendar">
        <?php
        $days_in_month = date('t'); // Jumlah hari dalam bulan ini
        $current_month = date('Y-m'); // Bulan saat ini (format: YYYY-MM)

        for ($i = 1; $i <= $days_in_month; $i++) {
            $current_date = $current_month . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);
            $has_task = in_array($current_date, $tanggal_tugas) ? 'has-task' : '';
            echo "<div class='day $has_task' onclick=\"showTasks('$current_date')\">$i</div>";
        }
        ?>
    </div>

    <!-- Daftar Tugas -->
    <div class="task-list">
        <h2>Tugas pada Tanggal: <?php echo $tanggal ? $tanggal : 'Pilih tanggal'; ?></h2>
        <ul>
            <?php if ($tugas): ?>
                <?php foreach ($tugas as $t): ?>
                    <li><?php echo $t['deskripsi_tugas']; ?></li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>Tidak ada tugas pada tanggal ini.</li>
            <?php endif; ?>
        </ul>
    </div>

    <script>
        function showTasks(tanggal) {
            window.location.href = '?tanggal=' + tanggal;
        }
    </script>
</body>
</html>