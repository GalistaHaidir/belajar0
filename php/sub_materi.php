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
    <title>Pilih Materi</title>
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
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <a class="btn btn-outline-danger"
                    style="border-radius: 50px; margin-bottom: 15px;"
                    onclick="window.location.href='materi.php';">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i>
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

                    <div class="row" id="subMateriList">
                        <?php
                        // Database connection

                        // Get category from URL
                        $category = isset($_GET['category']) ? $_GET['category'] : '';

                        // Fetch data from the database
                        $sql = "SELECT * FROM materi WHERE category = '$category'";
                        $result = $koneksi->query($sql);

                        // Display data
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<div class='col-md-4 mb-4 materi-item' data-category='" . htmlspecialchars($row['category']) . "'>";
                                echo "    <div class='card shadow-sm'>";
                                echo "        <div class='card-body'>";
                                echo "            <h5 class='card-title mb-2' style='text-transform: capitalize;'>" . htmlspecialchars($row['title']) . "</h5>";
                                echo "            <a href='detail_materi.php?category=" . urlencode($row['category']) . "&id_materi=" . $row['id_materi'] . "' class='btn btn-outline-primary'>Buka Materi</a>";
                                echo "        </div>";
                                echo "    </div>";
                                echo "</div>";
                            }
                        } else {
                            echo "<p>No materials found for this category.</p>";
                        }

                        // Close connection
                        $koneksi->close();
                        ?>
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
            // Get the search input value and convert it to lowercase
            const searchInput = document.getElementById('searchSubMateri').value.toLowerCase();

            // Get all the materi items
            const materiItems = document.querySelectorAll('.materi-item');

            // Loop through each item and check if it matches the search input
            materiItems.forEach(item => {
                const title = item.querySelector('.card-title').textContent.toLowerCase();
                const description = item.querySelector('.card-text').textContent.toLowerCase();

                // Check if the title or description includes the search input
                if (title.includes(searchInput) || description.includes(searchInput)) {
                    item.style.display = ''; // Show the item
                } else {
                    item.style.display = 'none'; // Hide the item
                }
            });
        }
    </script>

</body>

</html>