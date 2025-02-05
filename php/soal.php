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
$id = "";
$pertanyaan = "";
$gambar = "";
$a = "";
$b = "";
$c = "";
$d = "";
$kunci_jawaban = "";

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
    $id = $_GET['id'];
    $sql = "DELETE FROM tbl_soal WHERE id = '$id'";
    $q = mysqli_query($koneksi, $sql);
    if ($q) {
        $sukses = "Berhasil menghapus data.";
    } else {
        $error = "Gagal menghapus data.";
    }
}

// Handle Edit
if ($op == 'edit') {
    $id = $_GET['id'];
    $sql = "SELECT * FROM tbl_soal WHERE id = '$id'";
    $q = mysqli_query($koneksi, $sql);
    $r = mysqli_fetch_array($q);
    $pertanyaan = $r['pertanyaan'];
    $gambar = $r['gambar'];
    $a = $r['a'];
    $b = $r['b'];
    $c = $r['c'];
    $d = $r['d'];
    $kunci_jawaban = $r['kunci_jawaban'];

    if ($pertanyaan == '') {
        $error = "Data tidak ditemukan.";
    }
}

// Handle Create atau Update
if (isset($_POST['submit'])) {
    $pertanyaan = $_POST['pertanyaan'];
    $gambar = $_FILES['gambar']['name'];
    $a = $_POST['a'];
    $b = $_POST['b'];
    $c = $_POST['c'];
    $d = $_POST['d'];
    $kunci_jawaban = $_POST['kunci_jawaban'];

    // Validasi input
    if ($pertanyaan && $a && $b && $c && $d && $kunci_jawaban) {
        // Upload gambar jika ada
        if ($gambar) {
            $target_dir = "gambar_soal/";
            $target_file = $target_dir . basename($gambar);
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Validasi file gambar
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_type, $allowed_types)) {
                if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file)) {
                    $error = "Gagal mengupload gambar.";
                }
            } else {
                $error = "Format file tidak didukung. Hanya JPG, JPEG, PNG, dan GIF yang diperbolehkan.";
            }
        }

        if ($op == 'edit') { // Update
            if ($gambar) {
                $sql = "UPDATE tbl_soal SET pertanyaan = '$pertanyaan', gambar = '$gambar', a = '$a', b = '$b', c = '$c', d = '$d', kunci_jawaban = '$kunci_jawaban' WHERE id = '$id'";
            } else {
                $sql = "UPDATE tbl_soal SET pertanyaan = '$pertanyaan', a = '$a', b = '$b', c = '$c', d = '$d', kunci_jawaban = '$kunci_jawaban' WHERE id = '$id'";
            }
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Data berhasil diperbarui.";
            } else {
                $error = "Data gagal diperbarui.";
            }
        } else { // Insert
            $sql = "INSERT INTO tbl_soal (pertanyaan, gambar, a, b, c, d, kunci_jawaban) VALUES ('$pertanyaan', '$gambar', '$a', '$b', '$c', '$d', '$kunci_jawaban')";
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
$result = $koneksi->query("SELECT * FROM tbl_soal");
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
    <title>Kelola Soal</title>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">

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
                                    window.location.href = "soal.php";
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
                                    window.location.href = "soal.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <!-- Form untuk menambah atau mengedit data -->
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="pertanyaan" class="col-sm-2 col-form-label">Pertanyaan</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="pertanyaan" placeholder="Pertanyaan" id="pertanyaan" value="<?php echo isset($pertanyaan) ? htmlspecialchars($pertanyaan) : ''; ?>" id="nama_ujian" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="gambar" class="col-sm-2 col-form-label">Gambar (Opsional)</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" placeholder="Gambar (Opsional)" name="gambar" value="<?php echo isset($gambar) ? htmlspecialchars($gambar) : ''; ?>" id="gambar">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="a" class="col-sm-2 col-form-label">Opsi Jawaban A</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="A" name="a" id="a" value="<?php echo isset($a) ? htmlspecialchars($a) : ''; ?>" id="a" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="b" class="col-sm-2 col-form-label">Opsi Jawaban B</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="B" name="b" value="<?php echo isset($b) ? htmlspecialchars($b) : ''; ?>" id="b" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="c" class="col-sm-2 col-form-label">Opsi Jawaban C</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="C" name="c" value="<?php echo isset($c) ? htmlspecialchars($c) : ''; ?>" id="c" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="d" class="col-sm-2 col-form-label">Opsi Jawaban d</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="D" name="d" value="<?php echo isset($d) ? htmlspecialchars($d) : ''; ?>" id="d" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="kunci_jawaban" class="col-sm-2 col-form-label">Kunci Jawaban</label>
                                <div class="col-sm-10">
                                    <select name="kunci_jawaban" id="kunci_jawaban" class="form-select" required>
                                        <option value="" disabled selected>Pilih Kunci Jawaban</option>
                                        <option value="A">A</option>
                                        <option value="B">B</option>
                                        <option value="C">C</option>
                                        <option value="D">D</option>
                                    </select>
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
                        Data Soal
                    </div>
                    <div class="card-body">
                        <!-- Add a wrapper div for the table -->
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Pertanyaan</th>
                                        <th scope="col">Gambar</th>
                                        <th scope="col">Opsi A</th>
                                        <th scope="col">Opsi B</th>
                                        <th scope="col">Opsi C</th>
                                        <th scope="col">Opsi D</th>
                                        <th scope="col">Kunci Jawaban</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++; ?></th>
                                            <td scope="row"><?= htmlspecialchars($row['pertanyaan']); ?></td>
                                            <td scope="row">
                                                <?= $row['gambar']
                                                    ? "<img src='" . htmlspecialchars("gambar_soal/{$row['gambar']}") . "' alt='Gambar Soal' width='100'>"
                                                    : "Tidak ada"; ?>
                                            </td>
                                            <td scope="row"><?= htmlspecialchars($row['a']); ?></td>
                                            <td scope="row"><?= htmlspecialchars($row['b']); ?></td>
                                            <td scope="row"><?= htmlspecialchars($row['c']); ?></td>
                                            <td scope="row"><?= htmlspecialchars($row['d']); ?></td>
                                            <td scope="row"><?= htmlspecialchars($row['kunci_jawaban']); ?></td>
                                            <!-- Tombol Edit -->
                                            <td>
                                                <a href="soal.php?op=edit&id=<?= $row['id']; ?>">
                                                    <button type="button" class="btn btn-warning">
                                                        <i class="bi bi-pen-fill"></i> Edit
                                                    </button>
                                                </a>
                                                <!-- Tombol Hapus -->
                                                <a href="soal.php?op=delete&id=<?= $row['id']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
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
</body>

</html>