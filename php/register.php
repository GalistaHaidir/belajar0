<?php
session_start();
include("koneksi.php");

// Variabel
$namaLengkap    = "";
$nomorTlpn      = "";
$email          = "";
$username       = "";
$password       = "";
$ulangPassword  = "";
$fotoProfil     = "";

$sukses         = "";
$error          = "";

if (isset($_POST['submit'])) {
    $namaLengkap    = $_POST['namaLengkap'];
    $nomorTlpn      = $_POST['nomorTlpn'];
    $email          = $_POST['email'];
    $username       = $_POST['username'];
    $password       = $_POST['password'];
    $ulangPassword  = $_POST['ulangPassword'];
    $fotoProfil     = ""; // Default kosong

    if ($namaLengkap && $nomorTlpn && $email && $username && $password && $ulangPassword) {
        // Validasi apakah password dan ulangi password sama
        if ($password === $ulangPassword) {
            // Hash password menggunakan MD5 (disarankan gunakan bcrypt di masa depan)
            $passwordHash = md5($password);

            // Proses upload foto profil
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
                            $fotoProfil = $fileName; // Simpan nama file ke database
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

            // Jika tidak ada error, simpan ke database
            if (empty($error)) {
                $sql1 = "INSERT INTO pengguna (namaLengkap, nomorTlpn, email, username, password, fotoProfil) 
                         VALUES ('$namaLengkap', '$nomorTlpn', '$email', '$username', '$passwordHash', '$fotoProfil')";
                $q1 = mysqli_query($koneksi, $sql1);

                if ($q1) {
                    $sukses = "Anda berhasil mendaftar";
                } else {
                    $error = "Anda gagal mendaftar: " . mysqli_error($koneksi);
                }
            }
        } else {
            $error = "Password dan Ulangi Password tidak cocok.";
        }
    } else {
        $error = "Silahkan masukkan semua data";
    }
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Daftar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body>
    <nav class="navbar navbar-expand-md" style="background-color: rgba(0, 0, 0, 0.5);">
        <div class="container-fluid">
            <div class="navbar-brand" href="#">
                <a class="navbar-brand" href="#">
                    <img src="logo.png" alt="Logo" style="height: 40px;"> <!-- Ganti dengan path gambar Anda -->
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon text-white"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto"> <!-- Tambahkan ms-auto di sini -->
                    <li class="nav-item">
                        <a class="btn btn-outline-warning me-2 mb-2" href="login.php" style="width: 90px;">Masuk</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex justify-content-center align-items-center" style="height: 90vh; margin: 20px;">
        <div class="mx-auto  text-white"
            style="background-color: rgba(0, 0, 0, 0.5); height: auto; width: 390px; border-radius: 20px;">
            <h3 class="px-3 pt-3">Daftar Akun</h3>
            <hr>

            <form class="px-3 py-1" method="POST" enctype="multipart/form-data"> <!-- Tambahkan enctype="multipart/form-data" -->
                <?php if ($error) { ?>
                    <div id="login-alert" class="alert alert-danger col-sm-12">
                        <ul><?php echo $error ?></ul>
                    </div>
                    <?php header("refresh:5;url=register.php"); ?>
                <?php } ?>
                <?php if ($sukses) { ?>
                    <div id="login-alert" class="alert alert-success col-sm-12">
                        <ul><?php echo $sukses ?></ul>
                    </div>
                    <?php header("refresh:5;url=login.php"); ?>
                <?php } ?>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-warning border border-warning" style="width: auto;">
                        <i class="bi bi-person-fill"></i> <!--icons-->
                    </span>
                    <input type="text" class="form-control" placeholder="Nama Lengkap" name="namaLengkap" value="<?php echo $namaLengkap ?>" id="namaLengkap">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-warning border border-warning" style="width: auto;">
                        <i class="bi bi-phone-fill"></i> <!--icons-->
                    </span>
                    <input type="text" class="form-control" placeholder="Nomor Handphone" name="nomorTlpn" value="<?php echo $nomorTlpn ?>" id="nomorTlpn">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-warning border border-warning" style="width: auto;">
                        <i class="bi bi-envelope-fill"></i> <!--icons-->
                    </span>
                    <input type="email" class="form-control" placeholder="Alamat Email" name="email" value="<?php echo $email ?>" id="email">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-warning border border-warning" style="width: auto;">
                        <i class="bi bi-person-fill"></i> <!--icons-->
                    </span>
                    <input type="text" class="form-control" placeholder="Username" name="username" value="<?php echo $username ?>" id="username">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-warning border border-warning">
                        <i class="bi bi-shield-lock-fill"></i><!-- Icon -->
                    </span>
                    <input type="password" class="form-control" id="password" placeholder="Password" name="password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword1">
                        <i class="bi bi-eye-slash text-white"></i>
                    </button>
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-warning border border-warning">
                        <i class="bi bi-shield-lock-fill"></i><!-- Icon -->
                    </span>
                    <input type="password" class="form-control" id="ulangPassword" placeholder="Ulangi Password" name="ulangPassword">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword2">
                        <i class="bi bi-eye-slash text-white"></i>
                    </button>
                </div>


                <!-- Input untuk Foto Profil -->
                <div class="mb-3">
                    <label for="fotoProfil" class="form-label fw-bold">Unggah Foto Profil</label>
                    <div class="input-group">
                        <span class="input-group-text bg-warning border border-warning">
                            <i class="bi bi-camera-fill"></i> <!-- Icon Kamera -->
                        </span>
                        <input type="file" class="form-control" name="fotoProfil" id="fotoProfil" accept="image/*">
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" name="submit" class="btn btn-warning" style="width: 100px;">Daftar</button>
                </div>
            </form>

            <hr>

            <div class="text-center mb-4">
                Sudah Memiliki Akun? <a href="login.php" class="text-decoration-none">Masuk</a><br>
            </div>
        </div>
    </div>


    <script>
        document.getElementById("togglePassword1").addEventListener("click", function() {
            let passwordInput = document.getElementById("password");
            let icon = this.querySelector("i");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        });

        document.getElementById("togglePassword2").addEventListener("click", function() {
            let passwordInput = document.getElementById("ulangPassword");
            let icon = this.querySelector("i");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("bi-eye-slash");
                icon.classList.add("bi-eye");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("bi-eye");
                icon.classList.add("bi-eye-slash");
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>