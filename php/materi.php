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

$sql1 = "SELECT * FROM materi";
$q1 = mysqli_query($koneksi, $sql1);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Kategori Materi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
    <style>
        /* Styling body */
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            color: #1B5E20;
            /* Warna hijau tua */
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <div class="container mt-4">
                    <h2 class="mb-4"><i class="bi bi-book-half text-primary"></i></i>
                        Pilih Kategori Materi</h2>

                    <!-- Search Bar -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" id="searchInput" class="form-control" placeholder="Cari materi...">
                        </div>
                        <div class="col-md-4">
                            <select id="categoryFilter" class="form-select">
                                <option value="">Semua Kategori</option>
                                <option value="html">HTML</option>
                                <option value="css">CSS</option>
                                <option value="js">JavaScript</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="filterMateri()">Filter</button>
                        </div>
                    </div>

                    <!-- Materi List -->
                    <div class="row" id="materiList">
                        <!-- Contoh Materi -->
                        <div class="col-md-4 mb-4 materi-item" data-category="html">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Dasar-dasar HTML</h5>
                                    <p class="card-text">Memahami struktur dasar HTML.</p>
                                    <a href="sub_materi.php?category=html" class="btn btn-outline-primary">Lihat Materi</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-4 materi-item" data-category="css">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">CSS</h5>
                                    <p class="card-text">Belajar mengatur layout dengan Flexbox.</p>
                                    <a href="sub_materi.php?category=css" class="btn btn-outline-primary">Lihat Materi</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-4 materi-item" data-category="js">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">JavaScript</h5>
                                    <p class="card-text">Pengenalan dasar-dasar JavaScript.</p>
                                    <a href="sub_materi.php?category=javascript" class="btn btn-outline-primary">Lihat Materi</a>
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
        function filterMateri() {
            let searchValue = document.getElementById("searchInput").value.toLowerCase();
            let categoryValue = document.getElementById("categoryFilter").value;
            let items = document.querySelectorAll(".materi-item");

            items.forEach(item => {
                let title = item.querySelector(".card-title").textContent.toLowerCase();
                let category = item.getAttribute("data-category");

                if ((title.includes(searchValue) || searchValue === "") &&
                    (category === categoryValue || categoryValue === "")) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        }

    </script>
</body>

</html>