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

$op = isset($_GET['op']) ? $_GET['op'] : '';

if ($op == 'edit') {
    $id_pengguna = $_GET['id_pengguna'];
    $sql1 = "SELECT * FROM pengguna WHERE id_pengguna = '$id_pengguna'";
    $q1 = mysqli_query($koneksi, $sql1);
    $r1 = mysqli_fetch_array($q1);
    $namaLengkap = $r1["namaLengkap"];
    $nomorTlpn = $r1["nomorTlpn"];
    $email = $r1["email"];
    $username = $r1["username"];
    $password = $r1["password"];

    if ($namaLengkap == '') {
        $error = "Data tidak ditemukan";
    }
}

if (isset($_POST['submit'])) { // create or update
    $namaLengkap = $_POST['namaLengkap'];
    $nomorTlpn = $_POST['nomorTlpn'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $fotoProfilBaru = $fotoProfil; // Default tetap foto lama

    if ($namaLengkap && $nomorTlpn && $email && $username) {
        // Jika password diisi, hash password baru
        $passwordHash = !empty($password) ? md5($password) : $data['password'];

        // Proses upload foto profil baru jika ada
        if (!empty($_FILES["fotoProfil"]["name"])) {
            $targetDir = "profile/"; // Folder penyimpanan
            $fileName = basename($_FILES["fotoProfil"]["name"]);
            $targetFilePath = $targetDir . $fileName;
            $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

            // Validasi format file
            $allowedTypes = array("jpg", "jpeg", "png", "gif");
            if (in_array($fileType, $allowedTypes)) {
                // Cek ukuran file (maksimal 2MB)
                if ($_FILES["fotoProfil"]["size"] <= 2097152) {
                    // Upload file ke folder uploads
                    if (move_uploaded_file($_FILES["fotoProfil"]["tmp_name"], $targetFilePath)) {
                        $fotoProfilBaru = $fileName; // Simpan nama file baru
                    } else {
                        $error = "Gagal mengunggah foto profil.";
                    }
                } else {
                    $error = "Ukuran file terlalu besar (maks 2MB).";
                }
            } else {
                $error = "Format file tidak valid (gunakan JPG, JPEG, PNG, atau GIF).";
            }
        }

        // Jika tidak ada error, update data ke database
        if (empty($error)) {
            $sqlUpdate = "UPDATE pengguna SET 
                            namaLengkap = '$namaLengkap', 
                            nomorTlpn = '$nomorTlpn', 
                            email = '$email', 
                            username = '$username', 
                            password = '$passwordHash', 
                            fotoProfil = '$fotoProfilBaru' 
                          WHERE id_pengguna = '$id_pengguna'";
            $qUpdate = mysqli_query($koneksi, $sqlUpdate);

            if ($qUpdate) {
                $sukses = "Data berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui data: " . mysqli_error($koneksi);
            }
        }
    } else {
        $error = "Silahkan masukkan semua data.";
    }
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

    <link rel="stylesheet" href="guru_home.css">
    <style>
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }

        .form-group label {
            width: 150px;
            /* Lebar label */
            margin-right: 10px;
            text-align: left;
            /* Rata kanan */
        }

        .form-group input {
            flex: 1;
            /* Input akan menyesuaikan sisa ruang */
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .input-wrapper {
            flex: 1;
            /* Membuat input file menyesuaikan sisa ruang */
        }

        .form-control {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4 mt-1">
                <a class="btn btn-outline-danger"
                    style="border-top-left-radius: 50px; border-bottom-left-radius: 50px; margin-bottom:10px;"
                    onclick="navigateToPage()">
                    <i class="bi bi-backspace-fill"></i>
                    <span>Kembali</span>
                </a>
                <div class="container-fluid">
                    <div class="row justify-content-center">
                        <!-- Card Foto Profil -->
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body text-center">
                                    <img src="profile/<?php echo htmlspecialchars($fotoProfil); ?>" class="img-fluid mb-3" alt="Profile Picture"
                                        style="max-width: 400px; max-height: 300px;">
                                    <h5 class="card-title text-capitalize" style="font-weight:700;"><?php echo htmlspecialchars($sessionUsername); ?></h5>
                                </div>
                            </div>
                        </div>

                        <!-- Card Informasi Profil -->
                        <div class="col-md-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-primary text-white">
                                    <h4>Update Profil</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($error)) { ?>
                                        <div id="login-alert" class="alert alert-danger col-sm-12">
                                            <ul><?php echo $error; ?></ul>
                                        </div>
                                        <script>
                                            setTimeout(function() {
                                                window.location.href = "edit_profil.php";
                                            }, 5000); // Redirect setelah 5 detik
                                        </script>
                                    <?php } ?>

                                    <?php if (!empty($sukses)) { ?>
                                        <div id="login-alert" class="alert alert-success col-sm-12">
                                            <ul><?php echo $sukses; ?></ul>
                                        </div>
                                        <script>
                                            setTimeout(function() {
                                                window.location.href = "profil.php";
                                            }, 5000); // Redirect setelah 5 detik
                                        </script>
                                    <?php } ?>
                                    <form method="post" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <label for="namaLengkap"><strong>Nama :</strong></label>
                                            <input type="text" id="namaLengkap" name="namaLengkap" value="<?php echo htmlspecialchars($namaLengkap); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="email"><strong>Email :</strong></label>
                                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="username"><strong>Username :</strong></label>
                                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($sessionUsername); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="password"><strong>Password :</strong></label>
                                            <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="nomorTlpn"><strong>No. Telepon :</strong></label>
                                            <input type="text" id="nomorTlpn" name="nomorTlpn" value="<?php echo htmlspecialchars($nomorTlpn); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fotoProfil"><strong>Foto Profil :</strong></label>
                                            <div class="input-wrapper">
                                                <input type="file" class="form-control" name="fotoProfil" id="fotoProfil" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" name="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            <a href="profil.php" class="btn btn-danger">Batal</a>
                                        </div>
                                    </form>
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