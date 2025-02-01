<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "belajaro";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Fungsi CRUD
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'] ?? '';
    $tanggal = $_POST['tanggal'];
    $memo = $_POST['memo'] ?? '';

    if (isset($_POST['save'])) {
        if ($id) {
            $stmt = $conn->prepare("UPDATE tugas SET memo=? WHERE id=?");
            $stmt->bind_param("si", $memo, $id);
        } else {
            $stmt = $conn->prepare("INSERT INTO tugas (tanggal, memo) VALUES (?, ?)");
            $stmt->bind_param("ss", $tanggal, $memo);
        }
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete']) && $id) {
        $stmt = $conn->prepare("DELETE FROM tugas WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Ambil semua memo dari database
$memos = [];
$result = $conn->query("SELECT * FROM tugas");
while ($row = $result->fetch_assoc()) {
    $memos[$row['tanggal']] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Memo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .day {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: center;
            cursor: pointer;
        }

        .has-memo {
            background-color: lightgreen;
        }
    </style>
</head>

<body class="container mt-4">

    <div class="card">
        <div class="card-header text-center">
            <h2 class="text-center">Kalender Memo</h2>
            <!-- Dropdowns for Year and Month Selection -->
            <div class="d-flex justify-content-center my-3">
                <select id="yearSelect" class="form-select w-auto mx-2">
                    <?php
                    $currentYear = date("Y");
                    for ($y = $currentYear - 5; $y <= $currentYear + 5; $y++) {
                        echo "<option value='$y'" . ($y == $currentYear ? " selected" : "") . ">$y</option>";
                    }
                    ?>
                </select>
                <select id="monthSelect" class="form-select w-auto mx-2">
                    <?php
                    $months = [
                        "01" => "January",
                        "02" => "February",
                        "03" => "March",
                        "04" => "April",
                        "05" => "May",
                        "06" => "June",
                        "07" => "July",
                        "08" => "August",
                        "09" => "September",
                        "10" => "October",
                        "11" => "November",
                        "12" => "December"
                    ];
                    foreach ($months as $key => $month) {
                        echo "<option value='$key'" . ($key == date("m") ? " selected" : "") . ">$month</option>";
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="card-body">
            <div id="calendar" class="calendar d-flex flex-wrap">
                <?php
                $selectedYear = isset($_GET['year']) ? $_GET['year'] : date("Y");
                $selectedMonth = isset($_GET['month']) ? $_GET['month'] : date("m");
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);

                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $date = "$selectedYear-$selectedMonth-" . str_pad($i, 2, "0", STR_PAD_LEFT);
                    $hasMemo = isset($memos[$date]) ? "has-memo" : "";
                    echo "<div class='day $hasMemo border m-1 p-2 text-center' style='width: 50px; height: 50px;' data-date='$date'>$i</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Modal Input Memo -->
    <div class="modal fade" id="memoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah / Edit Memo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="memoId">
                        <input type="hidden" name="tanggal" id="tanggal">
                        <label for="memo" class="form-label">Memo:</label>
                        <textarea name="memo" id="memo" class="form-control" required></textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="save" class="btn btn-primary">Simpan</button>
                        <button type="submit" name="delete" class="btn btn-danger">Hapus</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const modal = new bootstrap.Modal(document.getElementById("memoModal"));
            const tanggalInput = document.getElementById("tanggal");
            const memoInput = document.getElementById("memo");
            const memoIdInput = document.getElementById("memoId");

            // Handle day click to open modal
            document.querySelectorAll(".day").forEach(day => {
                day.addEventListener("click", function() {
                    const date = this.dataset.date;
                    tanggalInput.value = date;
                    memoIdInput.value = ""; // Reset ID
                    memoInput.value = ""; // Reset memo

                    const memos = <?php echo json_encode($memos); ?>;
                    if (memos[date]) {
                        memoInput.value = memos[date].memo;
                        memoIdInput.value = memos[date].id;
                    }
                    modal.show();
                });
            });

            // Handle year and month selection
            const yearSelect = document.getElementById("yearSelect");
            const monthSelect = document.getElementById("monthSelect");

            yearSelect.addEventListener("change", updateCalendar);
            monthSelect.addEventListener("change", updateCalendar);

            function updateCalendar() {
                const year = yearSelect.value;
                const month = monthSelect.value;
                window.location.href = `?year=${year}&month=${month}`;
            }
        });
    </script>

</body>

</html>