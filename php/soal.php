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

///////////////////////////////////

$id_soal = "";
$id_peraturan = "";
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
    $id_soal = $_GET['id_soal'];
    $sql = "DELETE FROM tbl_soal WHERE id_soal = '$id_soal'";
    $q = mysqli_query($koneksi, $sql);
    if ($q) {
        $sukses = "Berhasil menghapus soal.";
    } else {
        $error = "Gagal menghapus soal.";
    }
}

// Handle Edit
$id_peraturan = $pertanyaan = $a = $b = $c = $d = $kunci_jawaban = $gambar = "";
if ($op == 'edit') {
    $id_soal = $_GET['id_soal'];
    $sql = "SELECT * FROM tbl_soal WHERE id_soal = '$id_soal'";
    $q = mysqli_query($koneksi, $sql);
    $r = mysqli_fetch_array($q);
    if ($r) {
        $id_peraturan = $r['id_peraturan'];
        $pertanyaan = $r['pertanyaan'];
        $a = $r['a'];
        $b = $r['b'];
        $c = $r['c'];
        $d = $r['d'];
        $kunci_jawaban = $r['kunci_jawaban'];
        $gambar = $r['gambar'];
    } else {
        $error = "Data tidak ditemukan.";
    }
}

// Handle Create atau Update
if (isset($_POST['submit'])) {
    $id_peraturan = $_POST['id_peraturan'];
    $pertanyaan = $_POST['pertanyaan'];
    $a = $_POST['a'];
    $b = $_POST['b'];
    $c = $_POST['c'];
    $d = $_POST['d'];
    $kunci_jawaban = $_POST['kunci_jawaban'];

    // Upload Gambar
    $gambar = null;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);

        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            $error = "Format file tidak didukung!";
        } else {
            $upload_dir = 'gambar_soal/';
            $file_name = uniqid() . '.' . $file_extension;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $file_name)) {
                $gambar = $file_name;
            } else {
                $error = "Gagal mengunggah gambar.";
            }
        }
    }

    if ($id_peraturan && $pertanyaan && $a && $b && $c && $d && $kunci_jawaban) {
        if ($op == 'edit') { // Update
            if ($gambar) {
                $sql = "UPDATE tbl_soal SET id_peraturan = '$id_peraturan', pertanyaan = '$pertanyaan', a = '$a', 
                        b = '$b', c = '$c', d = '$d', kunci_jawaban = '$kunci_jawaban', gambar = '$gambar' 
                        WHERE id_soal = '$id_soal'";
            } else {
                $sql = "UPDATE tbl_soal SET id_peraturan = '$id_peraturan', pertanyaan = '$pertanyaan', a = '$a', 
                        b = '$b', c = '$c', d = '$d', kunci_jawaban = '$kunci_jawaban' 
                        WHERE id_soal = '$id_soal'";
            }
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Soal berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui soal.";
            }
        } else { // Insert
            $sql = "INSERT INTO tbl_soal (id_peraturan, pertanyaan, a, b, c, d, kunci_jawaban, gambar) 
                    VALUES ('$id_peraturan', '$pertanyaan', '$a', '$b', '$c', '$d', '$kunci_jawaban', '$gambar')";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Berhasil menambahkan soal baru.";
            } else {
                $error = "Gagal menambahkan soal.";
            }
        }
    } else {
        $error = "Silakan isi semua data.";
    }
}

// Ambil data soal
$query = "SELECT tbl_soal.*, tbl_pengaturan.nama_ujian FROM tbl_soal JOIN tbl_pengaturan ON tbl_soal.id_peraturan = tbl_pengaturan.id_peraturan";
$result = $koneksi->query($query);

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

    <link rel="stylesheet" href="guru_home.css">
    <title>Kelola Soal</title>
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
                <div class="card" style="border-radius: 20px;">
                    <div class="card-header text-light" style="background-color: #0b1915; font-weight: bold; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                        Kelola Soal
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
                                <label for="nama_ujian" class="col-sm-2 col-form-label">Nama Soal</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="id_peraturan" id="nama_ujian" required>
                                        <?php
                                        // Ambil data nama_ujian dari tbl_pengaturan
                                        $result_peraturan = $koneksi->query("SELECT id_peraturan, nama_ujian FROM tbl_pengaturan");
                                        while ($row_peraturan = $result_peraturan->fetch_assoc()) {
                                            echo "<option value='" . $row_peraturan['id_peraturan'] . "'>" . $row_peraturan['nama_ujian'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="pertanyaan" class="col-sm-2 col-form-label">Pertanyaan</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" placeholder="Masukkan pertanyaan" name="pertanyaan" id="pertanyaan" required><?php echo isset($pertanyaan) ? htmlspecialchars($pertanyaan) : ''; ?></textarea>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="gambar" class="col-sm-2 col-form-label">Gambar</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" name="gambar" id="gambar">
                                    <?php if (!empty($gambar)) : ?>
                                        <img src="gambar_soal/<?php echo htmlspecialchars($gambar); ?>" alt="Gambar Soal" style="max-width: 100px; margin-top: 10px;">
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="a" class="col-sm-2 col-form-label">Pilihan A</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="A" name="a" id="a" value="<?php echo isset($a) ? htmlspecialchars($a) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="b" class="col-sm-2 col-form-label">Pilihan B</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="B" name="b" id="b" value="<?php echo isset($b) ? htmlspecialchars($b) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="c" class="col-sm-2 col-form-label">Pilihan C</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="C" name="c" id="c" value="<?php echo isset($c) ? htmlspecialchars($c) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="d" class="col-sm-2 col-form-label">Pilihan D</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="D" name="d" id="d" value="<?php echo isset($d) ? htmlspecialchars($d) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="kunci_jawaban" class="col-sm-2 col-form-label">Kunci Jawaban</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="kunci_jawaban" id="kunci_jawaban" required>
                                        <option value="" disabled selected>Pilih Kunci Jawaban</option>
                                        <option value="a" <?php echo (isset($kunci_jawaban) && $kunci_jawaban == 'a') ? 'selected' : ''; ?>>A</option>
                                        <option value="b" <?php echo (isset($kunci_jawaban) && $kunci_jawaban == 'b') ? 'selected' : ''; ?>>B</option>
                                        <option value="c" <?php echo (isset($kunci_jawaban) && $kunci_jawaban == 'c') ? 'selected' : ''; ?>>C</option>
                                        <option value="d" <?php echo (isset($kunci_jawaban) && $kunci_jawaban == 'd') ? 'selected' : ''; ?>>D</option>
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
                                        <th scope="col">Nama Ujian</th>
                                        <th scope="col">Pertanyaan</th>
                                        <th scope="col">Gambar Soal</th>
                                        <th scope="col">A</th>
                                        <th scope="col">B</th>
                                        <th scope="col">C</th>
                                        <th scope="col">D</th>
                                        <th scope="col">Kunci_jawaban</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++; ?></th>
                                            <td><?= htmlspecialchars($row['nama_ujian']); ?></td>
                                            <td><?= htmlspecialchars($row['pertanyaan']); ?></td>
                                            <td>
                                                <?php if (!empty($row['gambar'])) : ?>
                                                    <img src="gambar_soal/<?php echo htmlspecialchars($row['gambar']); ?>" alt="Gambar Soal" style="max-width: 100px; height: auto;">
                                                <?php else : ?>
                                                    Tidak ada gambar
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($row['a']); ?></td>
                                            <td><?= htmlspecialchars($row['b']); ?></td>
                                            <td><?= htmlspecialchars($row['c']); ?></td>
                                            <td><?= htmlspecialchars($row['d']); ?></td>
                                            <td><?= htmlspecialchars($row['kunci_jawaban']); ?></td>
                                            <td>
                                                <!-- Tombol Edit -->
                                                <a href="soal.php?op=edit&id_soal=<?= $row['id_soal']; ?>">
                                                    <button type="button" class="btn btn-warning">
                                                        <i class="bi bi-pen-fill"></i> Edit
                                                    </button>
                                                </a>
                                                <!-- Tombol Hapus -->
                                                <a href="soal.php?op=delete&id_soal=<?= $row['id_soal']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
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
</body>

</html>


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