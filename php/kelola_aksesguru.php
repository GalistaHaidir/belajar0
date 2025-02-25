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


$id_pengguna = "";
$id_master = "";

$sukses = "";
$error = "";


// CREATE dan UPDATE
if (isset($_POST['submit'])) {
    $id_master = $_POST['id_master'];
    $id_pengguna = $_POST['id_pengguna'];

    if ($id_master == "" || $id_pengguna == "") {
        $error = "Semua data harus diisi!";
    } else {
        if (isset($_GET['op']) && $_GET['op'] == 'edit') {
            $id_akses = $_GET['id_akses'];
            $sql = "UPDATE akses SET id_master = '$id_master', id_pengguna = '$id_pengguna' WHERE id_akses = '$id_akses'";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Data berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui data!";
            }
        } else {
            $sql = "INSERT INTO akses (id_master, id_pengguna) VALUES ('$id_master', '$id_pengguna')";
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
    $id_akses = $_GET['id_akses'];
    $sql = "DELETE FROM akses WHERE id_akses = '$id_akses'";
    $q = mysqli_query($koneksi, $sql);
    if ($q) {
        $sukses = "Data berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data!";
    }
}

// READ (Untuk Edit)
if (isset($_GET['op']) && $_GET['op'] == 'edit') {
    $id_akses = $_GET['id_akses'];
    $sql = "SELECT * FROM akses WHERE id_akses = '$id_akses'";
    $q = mysqli_query($koneksi, $sql);
    $r = mysqli_fetch_array($q);

    $id_master = $r['id_master'];
    $id_pengguna = $r['id_pengguna'];
} else {
    $id_master = "";
    $id_pengguna = "";
}

$sql = "SELECT ak.id_akses, p.namaLengkap, ak.id_master 
FROM akses ak
JOIN pengguna p ON ak.id_pengguna = p.id_pengguna
ORDER BY ak.id_akses DESC;
";
$result = $koneksi->query($sql);
$urut = 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Akses Kelompok</title>
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
                <!-- Card: Kelola Materi -->
                <div class="card custom-card mt-4">
                    <div class="card-header custom-header">
                        Kelola Nama Kelompok
                    </div>
                    <div class="card-body">
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
                                            window.location.href = "kelola_aksesguru.php";
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
                                            window.location.href = "kelola_aksesguru.php";
                                        }
                                    }
                                }, 1000);
                            </script>
                        <?php } ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="id_master" class="col-sm-2 col-form-label">Nama Peran</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="id_master" id="id_master" required>
                                        <option value="" disabled selected>-- Pilih Nama Kelompok --</option>
                                        <?php
                                        $result_akses = $koneksi->query("SELECT id_master FROM akses");
                                        while ($row_akses = $result_akses->fetch_assoc()) {
                                            $selected = ($row_akses['id_master'] == $id_master) ? "selected" : "";
                                            echo "<option value='" . $row_akses['id_master'] . "' $selected>" . $row_akses['id_master'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="namaLengkap" class="col-sm-2 col-form-label">Nama Siswa</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="id_pengguna" id="namaLengkap" required>
                                        <option value="" disabled selected>-- Pilih Nama Siswa --</option>
                                        <?php
                                        $result_siswa = $koneksi->query("SELECT id_pengguna, namaLengkap FROM pengguna");
                                        while ($row_siswa = $result_siswa->fetch_assoc()) {
                                            $selected = ($row_siswa['id_pengguna'] == $id_pengguna) ? "selected" : "";
                                            echo "<option value='" . $row_siswa['id_pengguna'] . "' $selected>" . $row_siswa['namaLengkap'] . "</option>";
                                        }
                                        ?>
                                    </select>
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

                <!-- Card: Data Materi -->
                <div class="card custom-card mt-4">
                    <div class="card-header custom-header">
                        Data Nama Kelompok
                    </div>
                    <div class="card-body">
                        <!-- Add a wrapper div for the table -->
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nama Peran</th>
                                        <th scope="col">Nama Akun</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++; ?></th>
                                            <td><?= htmlspecialchars($row['id_master']); ?></td>
                                            <td><?= htmlspecialchars($row['namaLengkap']); ?></td>
                                            <td>
                                                <!-- Tombol Edit -->
                                                <a href="kelola_aksesguru.php?op=edit&id_akses=<?= $row['id_akses']; ?>">
                                                    <button type="button" class="btn btn-warning btn-sm btn-custom">
                                                        <i class="bi bi-pen-fill"></i>
                                                    </button>
                                                </a>
                                                <!-- Tombol Hapus -->
                                                <a href="kelola_aksesguru.php?op=delete&id_akses=<?= $row['id_akses']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
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