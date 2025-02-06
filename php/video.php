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


// Fetch all videos from the database
$sql = "SELECT * FROM tbl_video";
$result = mysqli_query($koneksi, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Video Tutorial</title>
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
                <!-- Back Button -->
                <a class="btn btn-outline-danger"
                    style="border-top-left-radius: 50px; border-bottom-left-radius: 50px; margin-bottom:10px;"
                    onclick="navigateToPage()">
                    <i class="bi bi-backspace-fill"></i>
                    <span>Kembali</span>
                </a>
                <!-- Search Form -->
               

                <!-- Video Gallery -->
                <div class="container mt-4">
                <h2 class="mb-4"><i class="bi bi-play-btn-fill text-danger"></i>
                        Pilih Video</h2>

                    <!-- Search Input -->
                    <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" id="searchVideo" class="form-control" placeholder="Cari video..." onkeyup="filterVideos()">
                    </div>
                </div>
                    <h1 class="mb-4">Video Gallery</h1>
                    <div class="row row-cols-1 row-cols-md-3 g-4" id="videoGallery"> <!-- Tambahkan id videoGallery -->
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            $modalIndex = 1;

                            while ($row = mysqli_fetch_assoc($result)) {
                                $title = htmlspecialchars($row['title']);
                                $description = htmlspecialchars($row['description']);
                                $file_path = htmlspecialchars($row['file_path']);
                                $thumbnail_path = htmlspecialchars($row['thumbnail_path']);

                                echo '
                <div class="col video-card"> <!-- Tambahkan class "video-card" -->
                    <div class="card">
                        <img src="' . $thumbnail_path . '" class="card-img-top" alt="Thumbnail for ' . $title . '">
                        <div class="card-body">
                            <h5 class="card-title">' . $title . '</h5>
                            <p class="card-text">' . $description . '</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#videoModal' . $modalIndex . '">
                                Tonton Video
                            </button>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="videoModal' . $modalIndex . '" tabindex="-1" aria-labelledby="videoModal' . $modalIndex . 'Label" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="videoModal' . $modalIndex . 'Label">' . $title . '</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <video width="100%" height="400" controls>
                                    <source src="' . $file_path . '" type="video/mp4">
                                    Your browser does not support the video tag.
                                </video>
                            </div>
                        </div>
                    </div>
                </div>';

                                $modalIndex++;
                            }
                        } else {
                            echo "<p>No videos found.</p>";
                        }
                        ?>
                    </div>
                </div>



            </main>
            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="guru_home.js"></script>
    <script>
        function filterVideos() {
            let searchInput = document.getElementById('searchVideo').value.toLowerCase();
            let videoCards = document.querySelectorAll('#videoGallery .video-card'); // Gunakan class yang lebih spesifik

            videoCards.forEach(card => {
                let title = card.querySelector('.card-title').textContent.toLowerCase();
                let description = card.querySelector('.card-text').textContent.toLowerCase();

                if (title.includes(searchInput) || description.includes(searchInput)) {
                    card.style.display = 'block'; // Pastikan card ditampilkan
                } else {
                    card.style.display = 'none'; // Sembunyikan card yang tidak sesuai
                }
            });
        }



        function navigateToPage() {
            window.history.back();
        }
    </script>

</body>

</html>