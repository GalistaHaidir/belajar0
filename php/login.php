<?php
session_start();
include("koneksi.php");

$error = "";
$username = "";
$password = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input kosong
    if ($username == '' || $password == '') {
        $error .= "<li>Silakan masukkan username dan password.</li>";
    } else {
        // Cek pengguna berdasarkan username
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = mysqli_prepare($koneksi, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (!$result) {
            die("Query gagal: " . mysqli_error($koneksi));
        }

        $user = mysqli_fetch_assoc($result);

        // Validasi username dan password
        if (!$user) {
            $error .= "<li>Username <b>$username</b> tidak ditemukan.</li>";
        } elseif (!password_verify($password, $user['password'])) {
            $error .= "<li>Password yang dimasukkan salah.</li>";
        }

        // Jika tidak ada error, simpan sesi
        if (empty($error)) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];

            header("Location: dashboard.php");
            exit();
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
    <link rel="icon" type="image/png" href="favicon.png">
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
        </div>
    </nav>

    <div class="d-flex justify-content-center align-items-center" style="height: 90vh; margin: 20px;">
        <div class="mx-auto text-white card-blur"
            style="background-color: rgba(0, 0, 0, 0.5); height: auto; width: 390px; border-radius: 20px;">
            <h3 class="px-3 pt-3">Masuk</h3>
            <hr>

            <form class="px-3 py-1" method="POST">
                <?php if ($error) { ?>
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

        togglePassword.addEventListener("click", function() {
            // Toggle the type attribute
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);

            // Toggle the icon
            this.innerHTML = type === "password" ?
                '<i class="bi bi-eye-slash"></i>' :
                '<i class="bi bi-eye"></i>';
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>