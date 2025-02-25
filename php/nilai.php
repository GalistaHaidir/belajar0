<?php
session_start();
include 'koneksi.php';

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['id_pengguna'])) {
    header("Location: login.php"); // Redirect ke halaman login jika belum login
    exit;
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

// Inisialisasi variabel
$id_nilai = "";
$id_pengguna = "";
$id_peraturan = "";
$benar = "";
$salah = "";
$kosong = "";
$nilai = "";
$tanggal = "";
$status = "";

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
    $id_nilai = $_GET['id_nilai'];
    $sql = "DELETE FROM tbl_nilai WHERE id_nilai = '$id_nilai'";
    $q = mysqli_query($koneksi, $sql);
    if ($q) {
        $sukses = "Berhasil menghapus data.";
    } else {
        $error = "Gagal menghapus data.";
    }
}

// Handle Edit
if ($op == 'edit') {
    $id_nilai = $_GET['id_nilai'];
    $sql = "SELECT * FROM tbl_nilai WHERE id_nilai = '$id_nilai'";
    $q = mysqli_query($koneksi, $sql);
    $r = mysqli_fetch_array($q);
    $id_pengguna = $r['id_pengguna'];
    $id_peraturan = $r['id_peraturan'];
    $benar = $r['benar'];
    $salah = $r['salah'];
    $kosong = $r['kosong'];
    $nilai = $r['nilai'];
    $tanggal = $r['tanggal'];
    $status = $r['status'];

    if ($id_pengguna == '') {
        $error = "Data tidak ditemukan.";
    }
}

// Handle Create atau Update
if (isset($_POST['submit'])) {
    $id_pengguna = $_POST['id_pengguna'];
    $id_peraturan = $_POST['id_peraturan'];
    $benar = $_POST['benar'];
    $salah = $_POST['salah'];
    $kosong = $_POST['kosong'];
    $nilai = $_POST['nilai'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];

    // Validasi input
    if ($id_pengguna && $benar && $salah && $kosong && $nilai && $tanggal && $status && $id_peraturan) {
        if ($op == 'edit') { // Update
            $sql = "UPDATE tbl_nilai SET id_pengguna = '$id_pengguna', id_peraturan = '$id_peraturan', benar = '$benar', salah = '$salah', kosong = '$kosong', nilai = '$nilai', tanggal = '$tanggal', status = '$status' WHERE id_nilai = '$id_nilai'";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Data berhasil diperbarui.";
            } else {
                $error = "Data gagal diperbarui.";
            }
        } else { // Insert
            $sql = "INSERT INTO tbl_nilai (id_pengguna, id_peraturan, benar, salah, kosong, nilai, tanggal, status) VALUES ('$id_pengguna', '$id_peraturan', '$benar', '$salah', '$kosong', '$nilai', '$tanggal', '$status')";
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

// Fetch Data with JOIN to include namaLengkap
$query = "SELECT 
    tbl_nilai.*, 
    pengguna.namaLengkap, 
    tbl_pengaturan.nama_ujian
FROM tbl_nilai 
JOIN pengguna ON tbl_nilai.id_pengguna = pengguna.id_pengguna
JOIN tbl_pengaturan ON tbl_nilai.id_peraturan = tbl_pengaturan.id_peraturan;
";
$result = $koneksi->query($query);

// Check if the query was successful
if (!$result) {
    die("Query Error: " . $koneksi->error);
}

// Display Data
$urut = 1;
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">
    <title>Kelola Nilai Soal</title>
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
                        Kelola Nilai Soal
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
                                            window.location.href = "nilai.php";
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
                                            window.location.href = "nilai.php";
                                        }
                                    }
                                }, 1000);
                            </script>
                        <?php } ?>
                        <!-- Form untuk menambah atau mengedit data -->
                        <form action="" method="POST">
                            <div class="mb-3 row">
                                <label for="namaLengkap" class="col-sm-2 col-form-label">Nama Pengguna</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="id_pengguna" id="namaLengkap" required>
                                        <option value="" disabled selected>-- Pilih Nama Pengguna --</option>
                                        <?php
                                        // Ambil data namaLengkap dari tabel pengguna
                                        $result_pengguna = $koneksi->query("SELECT id_pengguna, namaLengkap FROM pengguna");
                                        while ($row_pengguna = $result_pengguna->fetch_assoc()) {
                                            echo "<option value='" . $row_pengguna['id_pengguna'] . "'>" . $row_pengguna['namaLengkap'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="nama_ujian" class="col-sm-2 col-form-label">Nama Soal</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="id_peraturan" id="nama_ujian" required>
                                        <option value="" disabled selected>-- Pilih Nama Soal --</option>
                                        <?php
                                        // Ambil data nama_ujian dari tabel pengguna
                                        $result_soal = $koneksi->query("SELECT id_peraturan, nama_ujian FROM tbl_pengaturan");
                                        while ($row_soal = $result_soal->fetch_assoc()) {
                                            echo "<option value='" . $row_soal['id_peraturan'] . "'>" . $row_soal['nama_ujian'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="benar" class="col-sm-2 col-form-label">Jumlah Jawaban Benar</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="Jumlah Jawaban Benar" name="benar" id="benar" value="<?php echo isset($benar) ? (int) $benar : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="salah" class="col-sm-2 col-form-label">Jumlah Jawaban Salah</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="Jumlah Jawaban Salah" name="salah" id="salah" value="<?php echo isset($salah) ? (int) $salah : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="kosong" class="col-sm-2 col-form-label">Jumlah Jawaban Kosong</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="Jumlah Jawaban Kosong" name="kosong" id="kosong" value="<?php echo isset($kosong) ? (int) $kosong : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="nilai" class="col-sm-2 col-form-label">Nilai</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="Nilai" name="nilai" id="nilai" value="<?php echo isset($nilai) ? (int) $nilai : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="tanggal" class="col-sm-2 col-form-label">Tanggal</label>
                                <div class="col-sm-10">
                                    <input type="date" class="form-control" placeholder="Tanggal" name="tanggal" id="tanggal" value="<?php echo isset($tanggal) ? htmlspecialchars($tanggal) : ''; ?>" required>
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
                        Data Soal Soal
                    </div>
                    <div class="card-body">
                        <!-- Add a wrapper div for the table -->
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nama Pengguna</th>
                                        <th scope="col">Nama Soal</th>
                                        <th scope="col">Jawaban Benar</th>
                                        <th scope="col">Jawaban Salah</th>
                                        <th scope="col">Jawaban Kosong</th>
                                        <th scope="col">Nilai</th>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++; ?></th>
                                            <td><?= htmlspecialchars($row['namaLengkap']); ?></td>
                                            <td><?= htmlspecialchars($row['nama_ujian']); ?></td>
                                            <td><?= (int)$row['benar']; ?> </td>
                                            <td><?= (int)$row['salah']; ?></td>
                                            <td><?= (int)$row['kosong']; ?></td>
                                            <td><?= (int)$row['nilai']; ?></td>
                                            <td><?= htmlspecialchars($row['tanggal']); ?></td>
                                            <td><?= htmlspecialchars($row['status']); ?></td>
                                            <td>
                                                <!-- Tombol Edit -->
                                                <a href="nilai.php?op=edit&id_nilai=<?= $row['id_nilai']; ?>">
                                                    <button type="button" class="btn btn-warning btn-sm btn-custom">
                                                        <i class="bi bi-pen-fill"></i>
                                                    </button>
                                                </a>
                                                <!-- Tombol Hapus -->
                                                <a href="nilai.php?op=delete&id_nilai=<?= $row['id_nilai']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
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