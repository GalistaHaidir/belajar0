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


$nama_kelompok = "";

$sukses = "";
$error = "";

// Tambah atau Update Kelompok
// CREATE dan UPDATE
if (isset($_POST['submit'])) {
    $nama_kelompok = $_POST['nama_kelompok'];

    if ($nama_kelompok == "") {
        $error = "Nama kelompok tidak boleh kosong!";
    } else {
        if (isset($_GET['op']) && $_GET['op'] == 'edit') {
            $id_kelompok = $_GET['id_kelompok'];
            $sql = "UPDATE kelompok SET nama_kelompok = '$nama_kelompok' WHERE id_kelompok = '$id_kelompok'";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Data berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui data!";
            }
        } else {
            $sql = "INSERT INTO kelompok (nama_kelompok) VALUES ('$nama_kelompok')";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Data berhasil ditambahkan!";
            } else {
                $error = "Gagal menambahkan data!";
            }
        }
    }
}

// DELETE
if (isset($_GET['op']) && $_GET['op'] == 'delete') {
    $id_kelompok = $_GET['id_kelompok'];
    $sql = "DELETE FROM kelompok WHERE id_kelompok = '$id_kelompok'";
    $q = mysqli_query($koneksi, $sql);
    if ($q) {
        $sukses = "Data berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data!";
    }
}

// READ (Untuk Edit)
if (isset($_GET['op']) && $_GET['op'] == 'edit') {
    $id_kelompok = $_GET['id_kelompok'];
    $sql = "SELECT * FROM kelompok WHERE id_kelompok = '$id_kelompok'";
    $q = mysqli_query($koneksi, $sql);
    $r = mysqli_fetch_array($q);
    $nama_kelompok = $r['nama_kelompok'];
} else {
    $nama_kelompok = "";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Nama Kelompok</title>
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
                <!-- Tombol Kembali -->
                <a class="btn btn-outline-danger"
                    style="border-radius: 50px; margin-bottom: 15px;"
                    onclick="window.location.href='kelola_tugas.php';">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i>
                    <span>Kembali</span>
                </a>

                <!-- Card: Kelola Nama Kelompok -->
                <div class="card custom-card">
                    <div class="card-header custom-header">
                        Kelola Nama Kelompok
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
                                            window.location.href = "kelompok_crud.php";
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
                                            window.location.href = "kelompok_crud.php";
                                        }
                                    }
                                }, 1000);
                            </script>
                        <?php } ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="nama_kelompok" class="col-sm-2 col-form-label">Nama Kelompok</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="Nama Kelompok" name="nama_kelompok" value="<?php echo $nama_kelompok ?>" id="nama_kelompok">
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

                <!-- Card: Data Kelompok -->
                <div class="card custom-card mt-4">
                    <div class="card-header custom-header">
                        Data Kelompok
                    </div>
                    <div class="card-body">
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered table-custom">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nama Kelompok</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql2 = "SELECT * FROM kelompok ORDER BY id_kelompok DESC";
                                    $q2 = mysqli_query($koneksi, $sql2);
                                    $urut = 1;
                                    while ($r2 = mysqli_fetch_array($q2)) {
                                        $id_kelompok    = $r2['id_kelompok'];
                                        $nama_kelompok  = $r2['nama_kelompok'];
                                    ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++ ?></th>
                                            <td><?php echo $nama_kelompok ?></td>
                                            <td>
                                                <a href="kelompok_crud.php?op=edit&id_kelompok=<?php echo $id_kelompok ?>">
                                                    <button type="button" class="btn btn-warning btn-sm btn-custom">
                                                        <i class="bi bi-pen-fill"></i>
                                                    </button>
                                                </a>
                                                <a href="kelompok_crud.php?op=delete&id_kelompok=<?php echo $id_kelompok ?>" onclick="return confirm('Yakin ingin menghapus kelompok ini?')">
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>

</body>

</html>