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

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Video</title>
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
                <a class="btn btn-outline-danger"
                    style="border-top-left-radius: 50px; border-bottom-left-radius: 50px; margin-bottom:10px;"
                    onclick="navigateToPage()">
                    <i class="bi bi-backspace-fill"></i>
                    <span>Kembali</span>
                </a>
                <div class="container">
                    <h2 class="mb-4"><i class="bi bi-folder-fill text-warning"></i>
                        Pilih Materi</h2>

                    <!-- Search Input -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="searchSubMateri" class="form-control" placeholder="Cari sub-materi..." onkeyup="filterSubMateri()">
                        </div>
                    </div>

                    <!-- Card Menu Materi -->
                    <div class="row" id="subMateriList">
                        <!-- HTML -->
                        <div class="col-md-4 mb-4 sub-materi-item">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Struktur Dasar HTML</h5>
                                    <p class="card-text">Belajar tentang elemen dasar HTML.</p>
                                    <a href="materi-html-struktur.html" class="btn btn-outline-primary">Lihat Materi</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4 sub-materi-item">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Form dan Input</h5>
                                    <p class="card-text">Membuat form interaktif di HTML.</p>
                                    <a href="materi-html-form.html" class="btn btn-outline-primary">Lihat Materi</a>
                                </div>
                            </div>
                        </div>

                        <!-- CSS -->
                        <div class="col-md-4 mb-4 sub-materi-item">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Dasar CSS</h5>
                                    <p class="card-text">Belajar menata tampilan dengan CSS.</p>
                                    <a href="materi-css-dasar.html" class="btn btn-outline-primary">Lihat Materi</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4 sub-materi-item">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Flexbox & Grid</h5>
                                    <p class="card-text">Teknik layout modern dengan CSS.</p>
                                    <a href="materi-css-layout.html" class="btn btn-outline-primary">Lihat Materi</a>
                                </div>
                            </div>
                        </div>

                        <!-- JavaScript -->
                        <div class="col-md-4 mb-4 sub-materi-item">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Variabel & Tipe Data</h5>
                                    <p class="card-text">Memahami dasar JavaScript.</p>
                                    <a href="materi-js-variabel.html" class="btn btn-outline-primary">Lihat Materi</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4 sub-materi-item">
                            <div class="card shadow-sm" onclick="goToDetail('js-dom')">
                                <div class="card-body">
                                    <h5 class="card-title">DOM Manipulation</h5>
                                    <p class="card-text">Mengubah elemen HTML dengan JavaScript.</p>
                                    <a href="lihat3.php" class="btn btn-outline-primary">Lihat Materi</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </main>

            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>

    <script>
        function filterSubMateri() {
            let searchValue = document.getElementById("searchSubMateri").value.toLowerCase();
            let items = document.querySelectorAll(".sub-materi-item");

            items.forEach(item => {
                let title = item.querySelector(".card-title").textContent.toLowerCase();

                if (title.includes(searchValue) || searchValue === "") {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        }

        function goToDetail(materiId) {
            window.location.href = `lihat3.php?materi=${materiId}`;
        }

        function navigateToPage() {
            window.history.back();
        }
    </script>

</body>

</html>