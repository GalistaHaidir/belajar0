<?php
session_start(); // Pastikan ini ada di awal file

// Cek apakah session ada, jika tidak redirect ke login
if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
    exit();
}

// Pastikan username dari session ada sebelum digunakan
$sessionUsername = $_SESSION['session_username'];

include 'koneksi.php'; // Koneksi ke database


// Ambil data user dari database
$query = "SELECT * FROM pengguna WHERE username = '$sessionUsername'";
$result = mysqli_query($koneksi, $query);

// Periksa apakah ada hasil
if ($result && mysqli_num_rows($result) > 0) {
    $data = mysqli_fetch_assoc($result);
    $id_pengguna = $data['id_pengguna'];
    $namaLengkap = $data['namaLengkap'];
    $email = $data['email'];
    $fotoProfil = $data['fotoProfil'];
    $password = $data['password'];
    $nomorTlpn = $data['nomorTlpn'];
} else {
    // Jika data tidak ditemukan, set nilai default
    $namaLengkap = "Tidak ditemukan";
    $email = "Tidak ditemukan";
    $fotoProfil = "default.jpg";
    $nomorTlpn = "Tidak ditemukan";
}


if (isset($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = "";
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .grid-template {
            display: grid;
            grid-template-columns: 150px auto;
            gap: 10px;
        }
    </style>

    <link rel="stylesheet" href="guru_home.css">
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4 mt-4">
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <!-- Card Foto Profil -->
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body text-center">
                                    <img src="profile/<?php echo htmlspecialchars($fotoProfil); ?>" class="img-fluid mb-3" alt="Profile Picture"
                                        style="max-width: 500px; max-height: 300px;">
                                    <h5 class="card-title text-capitalize" style="font-weight:700;"><?php echo htmlspecialchars($sessionUsername); ?></h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card Informasi Profil -->
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h4>Informasi Profil</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-grid grid-template">
                                            <strong>Nama : </strong> <span> <?php echo htmlspecialchars($namaLengkap); ?></span>
                                        </li>
                                        <li class="list-group-item d-grid grid-template">
                                            <strong>Email : </strong> <span> <?php echo htmlspecialchars($email); ?></span>
                                        </li>
                                        <li class="list-group-item d-grid grid-template">
                                            <strong>Username : </strong> <span> <?php echo htmlspecialchars($sessionUsername); ?></span>
                                        </li>
                                        <li class="list-group-item d-grid grid-template">
                                            <strong>Password : </strong> <span> <?php echo str_repeat('*', strlen($password)); ?></span>
                                        </li>
                                        <li class="list-group-item d-grid grid-template">
                                            <strong>No. Telepon : </strong> <span> <?php echo htmlspecialchars($nomorTlpn); ?></span>
                                        </li>
                                    </ul>
                                </div>
                                <div class="card-footer text-center">
                                    <a href="edit_profil.php?op=edit&id_pengguna=<?php echo $id_pengguna ?>" class="btn btn-primary">Edit Profil</a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </main>
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-body-secondary">
                        <div class="col-6 text-start">
                            <a href="#" class="text-body-secondary">
                                <strong>Belajar.0</strong>
                            </a>
                        </div>
                        <div class="col-6 text-end text-body-secondary d-none d-md-block">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <a href="#" class="text-body-secondary">Contact</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="text-body-secondary">About</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="text-body-secondary">Terms & Conditions</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>