<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php"); // Redirect ke halaman login jika belum login
    exit;
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

// Ambil daftar proyek dan kelompok yang tersedia
$query = "
    SELECT DISTINCT d.id_proyek, d.id_kelompok, k.nama_kelompok
    FROM diskusi d
    JOIN kelompok k ON d.id_kelompok = k.id_kelompok
    ORDER BY d.id_proyek, d.id_kelompok
";
$result = $koneksi->query($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="guru_home.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <title>Pantau Diskusi Siswa</title>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <a class="btn btn-outline-danger"
                    style="border-radius: 50px; margin-bottom: 15px;"
                    onclick="window.location.href='kelola_tugas.php';">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i>
                    <span>Kembali</span>
                </a>
                <div class="container mt-1">
                    <h2 class="text-center">📊 Pantau Diskusi Siswa</h2>
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <h5>📌 Pilih Kelompok</h5>
                            <ul class="list-group">
                                <?php while ($row = $result->fetch_assoc()) { ?>
                                    <li class="list-group-item">
                                        <a href="#" class="chat-link" data-proyek="<?= $row['id_proyek']; ?>" data-kelompok="<?= $row['id_kelompok']; ?>">
                                            Proyek <?= htmlspecialchars($row['id_proyek']); ?> - Kelompok <?= htmlspecialchars($row['nama_kelompok']); ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </div>
                        <div class="col-md-8">
                            <h5>💬 Chat Diskusi</h5>
                            <div id="chat-container" style="height: 400px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; background: #f9f9f9;">
                                <p>Pilih kelompok untuk melihat chat.</p>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function navigateToPage() {
            window.history.back();
        }
        $(document).ready(function() {
            $(".chat-link").click(function(e) {
                e.preventDefault();
                let id_proyek = $(this).data("proyek");
                let id_kelompok = $(this).data("kelompok");

                $("#chat-container").html("<p>Memuat chat...</p>");
                $("#chat-container").load("tampil_chat.php?id_proyek=" + id_proyek + "&id_kelompok=" + id_kelompok);
            });
        });
    </script>
</body>

</html>