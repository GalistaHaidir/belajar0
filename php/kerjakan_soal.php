<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php"); // Redirect ke halaman login jika belum login
    exit;
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

$id_pengguna = $_SESSION['id_pengguna']; // Ambil id_pengguna dari session

// Fetch waktu minimal dari tabel peraturan
$peraturan = $koneksi->query("SELECT waktu FROM tbl_pengaturan LIMIT 1")->fetch_assoc();
$waktu_minimal = $peraturan['waktu']; // Waktu dalam menit

// Fetch all questions from the database
$result = $koneksi->query("SELECT * FROM tbl_soal");

// Handle form submission
if (isset($_POST['submit'])) {
    $jawaban_benar = 0;
    $jawaban_salah = 0;
    $jawaban_kosong = 0;

    while ($row = $result->fetch_assoc()) {
        $id_soal = $row['id'];
        $kunci_jawaban = $row['kunci_jawaban'];

        // Cek jawaban pengguna
        if (isset($_POST["jawaban_$id_soal"])) {
            $jawaban = $_POST["jawaban_$id_soal"];
            if ($jawaban == $kunci_jawaban) {
                $jawaban_benar++;
            } else {
                $jawaban_salah++;
            }
        } else {
            $jawaban_kosong++;
        }
    }

    // Hitung nilai
    $total_soal = $jawaban_benar + $jawaban_salah + $jawaban_kosong;
    $nilai = ($jawaban_benar / $total_soal) * 100;

    // Simpan hasil ke database
    $tanggal = date('Y-m-d');
    $status = $nilai >= 60 ? 'Lulus' : 'Tidak Lulus'; // Contoh kriteria kelulusan

    $sql = "INSERT INTO tbl_nilai (id_pengguna, benar, salah, kosong, nilai, tanggal, status) VALUES ('$id_pengguna', '$jawaban_benar', '$jawaban_salah', '$jawaban_kosong', '$nilai', '$tanggal', '$status')";
    $koneksi->query($sql);

    // Tampilkan hasil
    echo "<h2>Hasil Ujian</h2>";
    echo "Jawaban Benar: $jawaban_benar<br>";
    echo "Jawaban Salah: $jawaban_salah<br>";
    echo "Jawaban Kosong: $jawaban_kosong<br>";
    echo "Nilai: $nilai<br>";
    echo "Status: $status<br>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
    <title>Mengerjakan Soal</title>
    <style></style>
    <script>
        // Timer untuk waktu minimal
        let waktu = <?= $waktu_minimal ?> * 60; // Konversi menit ke detik

        function startTimer() {
            const timerElement = document.getElementById('timer');
            const interval = setInterval(() => {
                const minutes = Math.floor(waktu / 60);
                const seconds = waktu % 60;

                // Tampilkan waktu yang tersisa
                timerElement.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;

                // Jika waktu habis, kirim form secara otomatis
                if (waktu <= 0) {
                    clearInterval(interval);
                    alert('Waktu habis! Jawaban Anda akan dikirim.');
                    document.getElementById('soalForm').submit();
                }

                waktu--;
            }, 1000);
        }

        window.onload = startTimer;
    </script>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <div class="container">
                    <!-- Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h1 class="text-center">Mengerjakan Soal</h1>
                        </div>
                    </div>

                    <!-- Timer dan Soal -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <form method="POST" action="" id="soalForm">
                                <?php
                                // Reset result pointer to fetch questions again
                                $result = $koneksi->query("SELECT * FROM tbl_soal");
                                while ($row = $result->fetch_assoc()) { ?>
                                    <div class="card shadow-sm border-0 mb-4">
                                        <div class="card-body">
                                            <!-- Pertanyaan -->
                                            <p class="fw-bold"><?= $row['pertanyaan'] ?></p>

                                            <!-- Gambar (jika ada) -->
                                            <?php if ($row['gambar']) { ?>
                                                <div class="text-center mb-3">
                                                    <img src="gambar_soal/<?= $row['gambar'] ?>" class="img-fluid rounded" style="max-width: 300px;">
                                                </div>
                                            <?php } ?>

                                            <!-- Pilihan Jawaban -->
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input" name="jawaban_<?= $row['id'] ?>" value="A" id="jawaban_<?= $row['id'] ?>_A">
                                                <label class="form-check-label" for="jawaban_<?= $row['id'] ?>_A"><?= $row['a'] ?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input" name="jawaban_<?= $row['id'] ?>" value="B" id="jawaban_<?= $row['id'] ?>_B">
                                                <label class="form-check-label" for="jawaban_<?= $row['id'] ?>_B"><?= $row['b'] ?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input" name="jawaban_<?= $row['id'] ?>" value="C" id="jawaban_<?= $row['id'] ?>_C">
                                                <label class="form-check-label" for="jawaban_<?= $row['id'] ?>_C"><?= $row['c'] ?></label>
                                            </div>
                                            <div class="form-check">
                                                <input type="radio" class="form-check-input" name="jawaban_<?= $row['id'] ?>" value="D" id="jawaban_<?= $row['id'] ?>_D">
                                                <label class="form-check-label" for="jawaban_<?= $row['id'] ?>_D"><?= $row['d'] ?></label>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>

                                <!-- Tombol Kirim -->
                                <div class="row">
                                    <div class="col-12 text-center">
                                        <button type="submit" name="submit" class="btn btn-success btn-lg">Kirim Jawaban</button>
                                    </div>
                                </div>
                                
                            </form>
                        </div>

                        <div class="col-md-4">
                            <div class="card shadow-sm border-0">
                                <div class="card-body text-center">
                                    <h5>Waktu Tersisa</h5>
                                    <p class="display-6 text-danger" id="timer">00:00</p>
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