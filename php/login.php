<?php
session_start();
include("koneksi.php");

// Variabel
$error = "";
$username = "";
$password = "";
$ingat = "";

if (isset($_COOKIE['cookie_username'])) {
    $cookie_username = $_COOKIE['cookie_username'];
    $cookie_password = $_COOKIE['cookie_password'];
    
    $sql1 = "SELECT * FROM pengguna WHERE username = '$cookie_username'";
    $q1 = mysqli_query($koneksi, $sql1);
    $r1 = mysqli_fetch_array($q1);
    if ($r1['password'] == $cookie_password) {
        $_SESSION['session_username'] = $cookie_username;
        $_SESSION['session_password'] = $cookie_password;
    }
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    // $ingat = $_POST['ingat'];

    if ($username == '' || $password == '') {
        $error .= "<li>Silahkan masukkan username dan juga password.</li>";
    } else {
        $sql1 = "SELECT * FROM pengguna WHERE username = '$username'";
        $q1 = mysqli_query($koneksi, $sql1);
        $r1 = mysqli_fetch_array($q1);

        if ($r1['username'] == '') {
            $error .= "<li>Username <b>$username</b> tidak tersedia.</li>";
        } elseif ($r1['password'] != md5($password)) {
            $error .= "<li>Password yang dimasukkan tidak sesuai.</li>";
        }

        if (empty($error)) {
            $_SESSION['session_username'] = $username;
            $_SESSION['session_password'] = md5($password);
            $id_pengguna = $r1['id_pengguna'];

            // Memeriksa akses pengguna
            $sql2 = "SELECT * FROM akses WHERE id_pengguna = '$id_pengguna'";
            $q2 = mysqli_query($koneksi, $sql2);
            $akses = [];
            while ($r2 = mysqli_fetch_array($q2)) {
                $akses[] = $r2['id_master'];
            }

            if (empty($akses)) {
                $error .= "<li>Kamu tidak punya akses ke halaman ini</li>";
            } else {
                $_SESSION['admin_username'] = $username;
                $_SESSION['id_pengguna'] = $id_pengguna;
                $_SESSION['akses'] = $akses;

                // Menyimpan cookie jika diinginkan
                if ($ingat == 1) {
                    $cookie_name = "cookie_username";
                    $cookie_value = $username;
                    $cookie_time = time() + (60 * 60 * 24 * 7);
                    setcookie($cookie_name, $cookie_value, $cookie_time, "/");

                    $cookie_name = "cookie_password";
                    $cookie_value = md5($password);
                    setcookie($cookie_name, $cookie_value, $cookie_time, "/");
                }

                header("location:guru_home.php");
                exit();
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Masuk</title>
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
                        <a class="btn btn-outline-warning me-2 mb-2" href="register.php" style="width: 90px;">Daftar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex justify-content-center align-items-center" style="height: 90vh; margin: 20px;">
        <div class="mx-auto text-white"
            style="background-color: rgba(0, 0, 0, 0.5); height: auto; width: 390px; border-radius: 20px;">
            <h3 class="px-3 pt-3">Masuk</h3>
            <hr>

            <form class="px-3 py-1" method="POST">
                <?php if($error){ ?>
                    <div id="login-alert" class="alert alert-danger col-sm-12">
                        <ul><?php echo $error ?></ul>
                    </div>
                    <?php 
                    header("refresh:5;url=login.php");
                    ?>
                <?php } ?>
                <div class="input-group mb-3">
                    <span class="input-group-text bg-warning border border-warning" style="width: auto;">
                        <i class="bi bi-person-fill"></i> <!--icons-->
                    </span>
                    <input type="text" class="form-control" placeholder="Username" name="username" value="<?php echo $username ?>" id="username">
                </div>

                <div class="input-group mb-3">
                    <span class="input-group-text bg-warning border border-warning">
                        <i class="bi bi-shield-lock-fill"></i> <!-- Icon -->
                    </span>
                    <input type="password" class="form-control" id="password" placeholder="Password" name="password" id="password">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="bi bi-eye-slash text-white"></i>
                    </button>
                </div>
                <div class="input-group mb-3">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="login-remember" name="ingat" value="1"
                            <?php if($ingat == '1') echo "checked"?>> Ingat Aku
                        </label>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" name="login" class="btn btn-warning" style="width: 100px;">Masuk</button>
                </div>
            </form>
            <hr>

            <div class="text-center mb-4">
                Belum Mempunyai Akun? <a href="register.php" class="text-decoration-none">Daftar</a><br>
            </div>
        </div>
    </div>


    <script>
        const togglePassword = document.querySelector("#togglePassword");
        const passwordInput = document.querySelector("#password");

        togglePassword.addEventListener("click", function () {
            // Toggle the type attribute
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);

            // Toggle the icon
            this.innerHTML = type === "password"
                ? '<i class="bi bi-eye-slash"></i>'
                : '<i class="bi bi-eye"></i>';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>