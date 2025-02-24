<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php"); // Redirect ke halaman login jika belum login
    exit;
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
// Inisialisasi variabel
$id_peraturan = "";
$nama_ujian = "";
$waktu = "";
$nilai_minimal = "";
$peraturan = "";

$sukses = "";
$error = "";

// Cek apakah ada operasi yang diminta (edit atau delete)
if (isset($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = "";
}

// Handle Delete
if ($op == 'delete') {
    $id_peraturan = $_GET['id_peraturan'];
    $sql = "DELETE FROM tbl_pengaturan WHERE id_peraturan = '$id_peraturan'";
    $q = mysqli_query($koneksi, $sql);
    if ($q) {
        $sukses = "Berhasil menghapus data.";
    } else {
        $error = "Gagal menghapus data.";
    }
}

// Handle Edit
if ($op == 'edit') {
    $id_peraturan = $_GET['id_peraturan'];
    $sql = "SELECT * FROM tbl_pengaturan WHERE id_peraturan = '$id_peraturan'";
    $q = mysqli_query($koneksi, $sql);
    $r = mysqli_fetch_array($q);
    $nama_ujian = $r['nama_ujian'];
    $waktu = $r['waktu'];
    $nilai_minimal = $r['nilai_minimal'];
    $peraturan = $r['peraturan'];

    if ($nama_ujian == '') {
        $error = "Data tidak ditemukan.";
    }
}

// Handle Create atau Update
if (isset($_POST['submit'])) {
    $nama_ujian = $_POST['nama_ujian'];
    $waktu = $_POST['waktu'];
    $nilai_minimal = $_POST['nilai_minimal'];
    $peraturan = $_POST['peraturan'];

    if ($nama_ujian && $waktu && $nilai_minimal && $peraturan) {
        if ($op == 'edit') { // Update
            $sql = "UPDATE tbl_pengaturan SET nama_ujian = '$nama_ujian', waktu = '$waktu', nilai_minimal = '$nilai_minimal', peraturan = '$peraturan' WHERE id_peraturan = '$id_peraturan'";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Data berhasil diperbarui.";
            } else {
                $error = "Data gagal diperbarui.";
            }
        } else { // Insert
            $sql = "INSERT INTO tbl_pengaturan (nama_ujian, waktu, nilai_minimal, peraturan) VALUES ('$nama_ujian', '$waktu', '$nilai_minimal', '$peraturan')";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Berhasil menambahkan data baru.";
            } else {
                $error = "Gagal menambahkan data baru.";
            }
        }
    } else {
        $error = "Silakan isi semua data.";
    }
}

// Fetch Data
$urut = 1;
$result = $koneksi->query("SELECT * FROM tbl_pengaturan");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
    <title>Kelola Peraturan Soal</title>
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
                <a class="btn btn-outline-danger"
                    style="border-radius: 50px; margin-bottom: 15px;"
                    onclick="window.location.href='kelola_soal.php';">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i>
                    <span>Kembali</span>
                </a>
                <!-- Card: Kelola Materi -->
                <div class="card custom-card">
                    <div class="card-header custom-header">
                        Kelola Peraturan Soal
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
                                            window.location.href = "peraturan.php";
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
                                            window.location.href = "peraturan.php";
                                        }
                                    }
                                }, 1000);
                            </script>
                        <?php } ?>

                        <!-- Form untuk menambah atau mengedit data -->
                        <form action="" method="POST">
                            <div class="mb-3 row">
                                <label for="nama_ujian" class="col-sm-2 col-form-label">Nama Soal</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="nama_ujian" placeholder="Nama Soal" value="<?php echo isset($nama_ujian) ? htmlspecialchars($nama_ujian) : ''; ?>" id_peraturan="nama_ujian" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="waktu" class="col-sm-2 col-form-label">Waktu (menit)</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="Waktu" name="waktu" id="waktu" value="<?php echo isset($waktu) ? (int) $waktu : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="nilai_minimal" class="col-sm-2 col-form-label">Nilai Minimal</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="Nilai Minimal" name="nilai_minimal" id="nilai_minimal" value="<?php echo isset($nilai_minimal) ? (int) $nilai_minimal : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="peraturan" class="col-sm-2 col-form-label">Peraturan</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" placeholder="Peraturan" name="peraturan" id="peraturan" required><?php echo isset($peraturan) ? htmlspecialchars($peraturan) : ''; ?></textarea>
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
                        Data Peraturan
                    </div>
                    <div class="card-body">
                        <!-- Add a wrapper div for the table -->
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nama Ujian</th>
                                        <th scope="col">Waktu</th>
                                        <th scope="col">Nilai Minimal</th>
                                        <th scope="col">Peraturan</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++; ?></th>
                                            <td><?= htmlspecialchars($row['nama_ujian']); ?></td>
                                            <td><?= (int)$row['waktu']; ?> menit</td>
                                            <td><?= (int)$row['nilai_minimal']; ?></td>
                                            <td><?= htmlspecialchars($row['peraturan']); ?></td>
                                            <td>
                                                <!-- Tombol Edit -->
                                                <a href="peraturan.php?op=edit&id_peraturan=<?= $row['id_peraturan']; ?>">
                                                    <button type="button" class="btn btn-warning btn-sm btn-custom">
                                                        <i class="bi bi-pen-fill"></i>
                                                    </button>
                                                </a>
                                                <!-- Tombol Hapus -->
                                                <a href="peraturan.php?op=delete&id_peraturan=<?= $row['id_peraturan']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
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