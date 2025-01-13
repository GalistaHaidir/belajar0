<?php
include("koneksi.php");

session_start();
if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
    exit();
}

$sessionUsername = $_SESSION['admin_username'];

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
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
</head>

<body>
    <div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex justify-content-between p-4">
                <div class="sidebar-logo">
                    <img src="logo.png" alt="Logo Belajar.0">
                </div>
                <button class="toggle-btn border-0" type="button">
                    <i id="icon" class="bi bi-arrow-right-short text-white"></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="guru_home.php" class="sidebar-link">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Halaman Utama</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="guru_materi.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth1" aria-expanded="false" aria-controls="auth1">
                        <i class="bi bi-file-earmark-code-fill"></i>
                        <span>Materi</span>
                    </a>
                    <ul id="auth1" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="guru_materi.php" class="sidebar-link">
                                Kelola Materi
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                Unggah Materi
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                Kelompokkan Materi
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth2" aria-expanded="false" aria-controls="auth2">
                        <i class="bi bi-pencil-fill"></i>
                        <span>Soal</span>
                    </a>
                    <ul id="auth2" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                Bank Soal
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                eop
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="guru_tugas.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth3" aria-expanded="false" aria-controls="auth3">
                        <i class="bi bi-list-check"></i>
                        <span>Tugas</span>
                    </a>
                    <ul id="auth3" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                Kelola Tugas
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                eop
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="guru_evaluasi.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth4" aria-expanded="false" aria-controls="auth4">
                        <i class="bi bi-journal-text"></i>
                        <span>Evaluasi</span>
                    </a>
                    <ul id="auth4" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                poe
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                eop
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="guru_siswa.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth5" aria-expanded="false" aria-controls="auth5">
                        <i class="bi bi-person-arms-up"></i>
                        <span>Siswa</span>
                    </a>
                    <ul id="auth5" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="guru_akunsiswa.php" class="sidebar-link">
                                Kelola Akun Siswa
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                eop
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="sidebar-footer mb-1">
                <a href="logout.php" class="sidebar-link">
                    <i class="bi bi-door-open-fill"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>
        <div class="main">
            <nav class="navbar navbar-expand px-4 py-3">
                <h3 class="fw-bold" style="text-transform: capitalize;">Hi, <?= $sessionUsername; ?></h3>
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                                <img src="cover.jpg" class="avatar omg-fluid" alt="">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end rounded-0 border-0 shadow mt-3">
                                <a href="#" class="dropdown-item">
                                    <i class="bi bi-person-fill"></i>
                                    <span>Akun</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item">
                                    <i class="bi bi-door-open-fill"></i>
                                    <span>Keluar</span>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="content px-3 py-4">
                <div class="card">
                    <div class="card-header text-light" style="background-color: #0b1915;">
                        Kelola Akun
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)) { ?>
                            <div id="login-alert" class="alert alert-danger col-sm-12">
                                <ul><?php echo $error; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "guru_akunsiswa.php";
                                }, 5000); // Mengalihkan setelah 5 detik
                            </script>
                        <?php } ?>

                        <?php if (!empty($sukses)) { ?>
                            <div id="login-alert" class="alert alert-success col-sm-12">
                                <ul><?php echo $sukses; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "guru_akunsiswa.php";
                                }, 5000); // Mengalihkan setelah 5 detik
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
                                    <input type="text" class="form-control" placeholder="username" name="username" value="<?php echo $username ?>" id="username">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="password" class="col-sm-2 col-form-label">Password</label>
                                <div class="col-sm 10">
                                    <input type="text" class="form-control" placeholder="password" name="password" value="<?php echo $password ?>" id="password">
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="bi bi-floppy-fill"></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card">
                        <div class="card-header text-white" style="background-color: #0b1915;">
                            Data Akun
                        </div>
                        <div class="card-body">
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
                                            <td scope="row">
                                                <a href="guru_akunsiswa.php?op=edit&id_pengguna=<?php echo $id_pengguna ?>">
                                                    <button type="button" class="btn btn-warning"><i class="bi bi-pen-fill"></i></button>
                                                </a>
                                                <a href="guru_akunsiswa.php?op=delete&id_pengguna=<?php echo $id_pengguna ?>" onclick="return confirm('Yakin ingin hapus data?')">
                                                    <button type="button" class="btn btn-danger"><i class="bi bi-trash-fill"></i></button>
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