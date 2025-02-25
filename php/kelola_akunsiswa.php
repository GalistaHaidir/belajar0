<?php
include("koneksi.php");

session_start();
if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
    exit();
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

$id_pengguna = "";
$namaLengkap = "";
$nomorTlpn = "";
$email = "";
$username = "";
$password = "";

$sukses = "";
$error = "";

if (isset($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = "";
}
if ($op == 'delete') {
    $id_pengguna = $_GET['id_pengguna'];
    $sql1 = "DELETE FROM pengguna WHERE id_pengguna = '$id_pengguna'";
    $q1 = mysqli_query($koneksi, $sql1);
    if ($q1) {
        $sukses = "Berhasil hapus data";
    } else {
        $error = "Gagal hapus data";
    }
}

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

if (isset($_POST['submit'])) { // create
    $namaLengkap = $_POST['namaLengkap'];
    $nomorTlpn = $_POST['nomorTlpn'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($namaLengkap && $nomorTlpn && $email && $username && $password) {
        if ($op == 'edit') { //update
            $passwordHash = md5($password);
            $sql1 = "UPDATE pengguna SET namaLengkap = '$namaLengkap', nomorTlpn = '$nomorTlpn', email = '$email', username = '$username', password = '$passwordHash' WHERE id_pengguna = '$id_pengguna'";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Data berhasil diperbarui";
            } else {
                $error = "Data gagal diperbarui";
            }
        } else { //insert
            $passwordHash = md5($password);
            $sql1 = "INSERT INTO pengguna (namaLengkap, nomorTlpn, email, username, password) VALUES ('$namaLengkap','$nomorTlpn','$email','$username','$passwordHash')";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Berhasil menambahkan data baru";
            } else {
                $error = "Gagal menambahkan data baru: " . mysqli_error($koneksi);
            }
        }
    } else {
        $error = "Silahkan isi semua kolom data.";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Akun</title>
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
        }

        /* Efek shadow untuk card */
        .custom-card {
            border-radius: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
        }

        /* Header dengan gradient */
        .custom-header {
            background: linear-gradient(135deg, #0b1915, #1d4035);
            font-weight: bold;
            color: white;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
        }

        /* Efek hover untuk tombol */
        .btn-custom {
            border-radius: 50px;
            transition: 0.3s ease-in-out;
        }

        .btn-custom:hover {
            transform: scale(1.05);
        }

        /* Table styling */
        .table-custom thead {
            background: #0b1915;
            color: white;
        }

        .table-custom tbody tr:nth-child(even) {
            background: #f8f9fa;
        }

        .table-custom tbody tr:hover {
            background: #e9ecef;
            transition: 0.3s;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <!-- Card: Kelola Nama Kelompok -->
                <div class="card custom-card mt-4">
                    <div class="card-header custom-header">
                        Kelola Akun
                    </div>
                    <div class="card-body">
                        <!-- Tampilkan pesan error jika ada -->
                        <?php if (!empty($error)) { ?>
                            <div id="alert-error" class="alert alert-danger col-sm-12">
                                <p><?php echo htmlspecialchars($error); ?>, Halaman akan direfresh dalam <span id="countdown-success">5</span> detik...</p>
                            </div>
                            <script>
                                let timeLeftError = 5;
                                let countdownErrorElement = document.getElementById("countdown-error");

                                let timerError = setInterval(function() {
                                    if (countdownErrorElement) {
                                        timeLeftError--;
                                        countdownErrorElement.innerText = timeLeftError;
                                        if (timeLeftError <= 0) {
                                            clearInterval(timerError);
                                            window.location.href = "kelola_akunsiswa.php";
                                        }
                                    }
                                }, 1000);
                            </script>
                        <?php } ?>

                        <?php if (!empty($sukses)) { ?>
                            <div id="alert-success" class="alert alert-success col-sm-12">
                                <p><?php echo htmlspecialchars($sukses); ?>, Halaman akan direfresh dalam <span id="countdown-success">5</span> detik...</p>
                            </div>
                            <script>
                                let timeLeftSuccess = 5;
                                let countdownSuccessElement = document.getElementById("countdown-success");

                                let timerSuccess = setInterval(function() {
                                    if (countdownSuccessElement) {
                                        timeLeftSuccess--;
                                        countdownSuccessElement.innerText = timeLeftSuccess;
                                        if (timeLeftSuccess <= 0) {
                                            clearInterval(timerSuccess);
                                            window.location.href = "kelola_akunsiswa.php";
                                        }
                                    }
                                }, 1000);
                            </script>
                        <?php } ?>
                        <form action="" method="POST">
                            <div class="mb-3 row">
                                <label for="namaLengkap" class="col-sm-2 col-form-label">Nama Lengkap</label>
                                <div class="col-sm 10">
                                    <input type="text" class="form-control" placeholder="Nama Lengkap" name="namaLengkap" value="<?php echo $namaLengkap ?>" id="namaLengkap">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="nomorTlpn" class="col-sm-2 col-form-label">Nomor Handphone</label>
                                <div class="col-sm 10">
                                    <input type="text" class="form-control" placeholder="Nomor Handphone" name="nomorTlpn" value="<?php echo $nomorTlpn ?>" id="nomorTlpn">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="email" class="col-sm-2 col-form-label">Alamat Email</label>
                                <div class="col-sm 10">
                                    <input type="email" class="form-control" placeholder="Alamat Email" name="email" value="<?php echo $email ?>" id="email">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="username" class="col-sm-2 col-form-label">Username</label>
                                <div class="col-sm 10">
                                    <input type="text" class="form-control" placeholder="Username" name="username" value="<?php echo $username ?>" id="username">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="password" class="col-sm-2 col-form-label">Password</label>
                                <div class="col-sm 10">
                                    <input type="text" class="form-control" placeholder="Password" name="password" value="<?php echo $password ?>" id="password">
                                </div>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" name="submit" class="btn btn-primary btn-custom px-3">
                                    <i class="bi bi-cloud-arrow-up-fill"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card custom-card mt-4">
                    <div class="card-header custom-header">
                        Data Akun
                    </div>
                    <div class="card-body">
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Nomor Hp</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Username</th>
                                        <th scope="col">Password</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql2   = "SELECT * FROM pengguna ORDER BY id_pengguna DESC";
                                    $q2     = mysqli_query($koneksi, $sql2);
                                    $urut   = 1;
                                    while ($r2 = mysqli_fetch_array($q2)) {
                                        $id_pengguna    = $r2['id_pengguna'];
                                        $namaLengkap    = $r2['namaLengkap'];
                                        $nomorTlpn      = $r2['nomorTlpn'];
                                        $email          = $r2['email'];
                                        $username       = $r2['username'];
                                        $password       = $r2['password'];
                                    ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++ ?></th>
                                            <td scope="row"><?php echo $namaLengkap ?></td>
                                            <td scope="row"><?php echo $nomorTlpn ?></td>
                                            <td scope="row"><?php echo $email ?></td>
                                            <td scope="row"><?php echo $username ?></td>
                                            <td scope="row"><?php echo $password ?></td>
                                            <td>
                                                <a href="kelola_akunsiswa.php?op=edit&id_pengguna=<?php echo $id_pengguna ?>">
                                                    <button type="button" class="btn btn-warning btn-sm btn-custom">
                                                        <i class="bi bi-pen-fill"></i>
                                                    </button>
                                                </a>
                                                <a href="kelola_akunsiswa.php?op=delete&id_pengguna=<?php echo $id_pengguna ?>" onclick="return confirm('Yakin ingin menghapus kelompok ini?')">
                                                    <button type="button" class="btn btn-danger btn-sm btn-custom">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
</body>

</html>