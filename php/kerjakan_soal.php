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


$result = $koneksi->query("SELECT * FROM tbl_pengaturan");
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
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <a class="btn btn-outline-danger"
                    style="border-top-left-radius: 50px; border-bottom-left-radius: 50px; margin-bottom:10px;"
                    onclick="navigateToPage()">
                    <i class="bi bi-backspace-fill"></i>
                    <span>Kembali</span>
                </a>
                <div class="container">
                    <h1>Pilih Ujian</h1>
                    <div class="row">
                        <?php
                        $id_pengguna = $_SESSION['id_pengguna']; // Ambil ID siswa yang sedang login

                        while ($row = $result->fetch_assoc()) {
                            $id_peraturan = $row['id_peraturan'];

                            // Ambil data ujian terbaru berdasarkan ID atau tanggal terbaru
                            $cek_ujian = $koneksi->query("
                SELECT status, nilai 
                FROM tbl_nilai 
                WHERE id_pengguna = '$id_pengguna' AND id_peraturan = '$id_peraturan' 
                ORDER BY id_nilai DESC LIMIT 1
            ");

                            $sudah_ujian = $cek_ujian->num_rows > 0;
                            $data_nilai = ($sudah_ujian) ? $cek_ujian->fetch_assoc() : null;

                            // Ambil status dan nilai terbaru
                            $status_ujian = $data_nilai['status'] ?? null;
                            $nilai = $data_nilai['nilai'] ?? null;

                            // Konversi status ke huruf kecil agar perbandingan lebih fleksibel
                            $status_lower = strtolower(trim($status_ujian));
                        ?>
                            <div class="col-md-4 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Nama Ujian: <?= $row['nama_ujian']; ?></h5>
                                        <p class="card-text">Waktu Ujian: <?= $row['waktu']; ?> menit</p>

                                        <?php if ($sudah_ujian) { ?>
                                            <p class="card-text">Nilai Anda: <strong><?= $nilai; ?></strong></p>
                                        <?php } ?>

                                        <?php if (!$sudah_ujian) { ?>
                                            <a href="ujian.php?id_peraturan=<?= $row['id_peraturan']; ?>" class="btn btn-primary">Mulai Ujian</a>
                                        <?php } elseif ($status_lower == 'tidak lulus') { ?>
                                            <a href="ujian.php?id_peraturan=<?= $row['id_peraturan']; ?>" class="btn btn-warning">Coba Lagi</a>
                                        <?php } else { ?>
                                            <button class="btn btn-secondary" disabled>Sudah Lulus</button>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
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