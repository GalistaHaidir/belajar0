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
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <a class="btn btn-outline-danger"
                    style="border-top-left-radius: 50px; border-bottom-left-radius: 50px; margin-bottom:10px;"
                    onclick="navigateToPage()">
                    <i class="bi bi-backspace-fill"></i>
                    <span>Kembali</span>
                </a>
                <!-- Card: Kelola Materi -->
                <div class="card" style="border-radius: 20px;">
                    <div class="card-header text-light" style="background-color: #0b1915; font-weight: bold; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                        Kelola Peraturan Soal
                    </div>
                    <div class="card-body">
                        <!-- Tampilkan pesan error jika ada -->
                        <?php if (!empty($error)) { ?>
                            <div id="alert-error" class="alert alert-danger col-sm-12">
                                <ul><?php echo htmlspecialchars($error); ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "peraturan.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <!-- Tampilkan pesan sukses jika ada -->
                        <?php if (!empty($sukses)) { ?>
                            <div id="alert-success" class="alert alert-success col-sm-12">
                                <ul><?php echo htmlspecialchars($sukses); ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "peraturan.php";
                                }, 5000);
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
                            <div class="col-12">
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Card: Data Materi -->
                <div class="card mt-4" style="border-radius: 20px;">
                    <div class="card-header text-white" style="background-color: #0b1915; font-weight: bold; border-top-left-radius: 20px; border-top-right-radius: 20px;">
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
                                                    <button type="button" class="btn btn-warning">
                                                        <i class="bi bi-pen-fill"></i> Edit
                                                    </button>
                                                </a>
                                                <!-- Tombol Hapus -->
                                                <a href="peraturan.php?op=delete&id_peraturan=<?= $row['id_peraturan']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <button type="button" class="btn btn-danger">
                                                        <i class="bi bi-trash-fill"></i> Hapus
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
    <script>
        function navigateToPage() {
            window.history.back();
        }
    </script>
</body>

</html>