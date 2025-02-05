<?php
include("koneksi.php");

session_start();
if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
    exit();
}

if (!in_array("Guru", $_SESSION['akses'])) {
    echo "Kamu tidak punya akses";
    exit();
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
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Akun Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <button class="btn-backspace" onclick="navigateToPage()">
                    <i class="bi bi-backspace-fill"></i> <span>Kembali</span>
                </button>
                <div class="mb-3 mt-2">
                    <div class="row">
                        <div class="col-12 col-md-4">
                            <div class="card shadow card-nilai" style="cursor: pointer;" onclick="window.location.href='peraturan.php';">
                                <div class="card-body py-4 text-center">
                                    <div class="icon mb-3">
                                        <i class="fas fa-cogs fa-3x"></i> <!-- Ikon untuk kartu -->
                                    </div>
                                    <h5 class="card-title">Kelola Peraturan</h5>
                                    <p class="card-text">Atur peraturan pengerjaan soal sesuai kebutuhan.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card shadow card-nilai" style="cursor: pointer;" onclick="window.location.href='soal.php';">
                                <div class="card-body py-4 text-center">
                                    <div class="icon mb-3">
                                        <i class="fas fa-book fa-3x"></i> <!-- Ikon untuk kartu -->
                                    </div>
                                    <h5 class="card-title">Kelola Soal</h5>
                                    <p class="card-text">Tambahkan, edit, atau hapus soal untuk ujian.</p>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="card shadow card-nilai" style="cursor: pointer;" onclick="window.location.href='nilai.php';">
                                <div class="card-body py-4 text-center">
                                    <div class="icon mb-3">
                                        <i class="fas fa-chart-bar fa-3x"></i> <!-- Ikon untuk kartu -->
                                    </div>
                                    <h5 class="card-title">Lihat Nilai</h5>
                                    <p class="card-text">Analisis nilai peserta ujian.</p>
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
        function navigateToPage() {
            window.history.back();
        }
    </script>
</body>

</html>