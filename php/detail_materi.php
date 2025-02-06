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

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch the data for the specific ID
$sql = "SELECT * FROM materi WHERE id = $id";
$result = mysqli_query($koneksi, $sql);

// Check if data exists
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $title = htmlspecialchars($row['title']); // Escape special characters
    $description = htmlspecialchars($row['description']); // Escape special characters
    $file_path = htmlspecialchars($row['file_path']); // Assuming you have a 'file_path' column for the PDF
} else {
    // If no data is found, set default values
    $title = "Materi Tidak Ditemukan";
    $description = "Deskripsi tidak tersedia.";
    $file_path = ""; // Default to empty if no file found
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Materi</title>
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
                <div class="container mt-4">
                    <h2 id="judulMateri" style="text-transform: capitalize;"><?php echo $title; ?></h2>
                    <hr>

                    <h4>Description</h4>
                    <p id="capaianPembelajaran"><?php echo $description; ?></p>

                    <h6>Materi PDF</h6>
                    <?php if (!empty($file_path)) { ?>
                        <a href="<?php echo $file_path; ?>" class="btn btn-outline-primary mb-4" target="_blank">Download PDF</a>
                        <iframe src="<?php echo $file_path; ?>" width="100%" height="600px"></iframe>
                    <?php } else { ?>
                        <p>Tidak ada PDF yang tersedia.</p>
                    <?php } ?>
                </div>
            </main>

            <?php include 'footer.php'; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="guru_home.js"></script>
    <script>
        function navigateToPage() {
            window.history.back();
        }
    </script>

</body>

</html>