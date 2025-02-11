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


$id_tugas = "";
$judul_tugas = "";
$deskripsi = "";
$dateline = "";
$akses = "";
$tahap = "";
$id_proyek = "";


$sukses = "";
$error = "";

#create update
if (isset($_POST['submit'])) {
    $judul_tugas = $_POST['judul_tugas'];
    $deskripsi = $_POST['deskripsi'];
    $dateline = $_POST['dateline'];
    $akses = $_POST['akses'];
    $tahap = isset($_POST['tahap']) ? $_POST['tahap'] : NULL;
    $id_proyek = isset($_POST['id_proyek']) && $_POST['id_proyek'] !== "" ? $_POST['id_proyek'] : NULL; // Ambil nilai id_proyek dari form, bisa NULL

    if ($judul_tugas == "" || $deskripsi == "" || $dateline == "" || $akses == "") {
        $error = "Semua data harus diisi!";
    } else {
        if (isset($_GET['op']) && $_GET['op'] == 'edit') {
            $id_tugas = $_GET['id_tugas'];
            $sql = "UPDATE tugas SET judul_tugas = '$judul_tugas', deskripsi = '$deskripsi', dateline = '$dateline', akses = '$akses', tahap = " . ($tahap ? "'$tahap'" : "NULL") . ", id_proyek = " . ($id_proyek ? "'$id_proyek'" : "NULL") . " WHERE id_tugas = '$id_tugas'";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Data berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui data!";
            }
        } else {
            $sql = "INSERT INTO tugas (judul_tugas, deskripsi, dateline, akses, tahap, id_proyek) VALUES ('$judul_tugas', '$deskripsi', '$dateline', '$akses', " . ($tahap ? "'$tahap'" : "NULL") . ", " . ($id_proyek ? "'$id_proyek'" : "NULL") . ")";
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
    $id_tugas = $_GET['id_tugas'];
    $sql = "DELETE FROM tugas WHERE id_tugas = '$id_tugas'";
    $q = mysqli_query($koneksi, $sql);
    if ($q) {
        $sukses = "Data berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data!";
    }
}

#READ (Untuk Edit)
if (isset($_GET['op']) && $_GET['op'] == 'edit') {
    $id_tugas = $_GET['id_tugas'];
    $sql = "SELECT * FROM tugas WHERE id_tugas = '$id_tugas'";
    $q = mysqli_query($koneksi, $sql);
    $r = mysqli_fetch_array($q);
    $judul_tugas = $r['judul_tugas'];
    $deskripsi = $r['deskripsi'];
    $dateline = $r['dateline'];
    $akses = $r['akses'];
    $tahap = $r['tahap'];
    $id_proyek = $r['id_proyek'];
} else {
    $judul_tugas = "";
    $deskripsi = "";
    $dateline = "";
    $akses = "";
    $tahap = "";
    $id_proyek = "";
}

// Ambil data untuk tabel
$sql = "SELECT * FROM tugas ORDER BY id_tugas DESC";
$result = $koneksi->query($sql);
$urut = 1;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Video</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">

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
                        Kelola Nama Kelompok
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)) { ?>
                            <div id="alert-error" class="alert alert-danger col-sm-12">
                                <ul><?php echo $error; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "atur_tugas.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <?php if (!empty($sukses)) { ?>
                            <div id="alert-success" class="alert alert-success col-sm-12">
                                <ul><?php echo $sukses; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "atur_tugas.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="judul_tugas" class="col-sm-2 col-form-label">Judul Tugas</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="Judul Tugas" name="judul_tugas" value="<?php echo $judul_tugas ?>" id="judul_tugas">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="deskripsi" class="col-sm-2 col-form-label">Deskripsi</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="Deskripsi Tugas" name="deskripsi" value="<?php echo $deskripsi ?>" id="deskripsi">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="dateline" class="col-sm-2 col-form-label">Tenggat Waktu</label>
                                <div class="col-sm-10">
                                    <input type="date" class="form-control" placeholder="Tenggat Waktu" name="dateline" value="<?php echo $dateline ?>" id="dateline">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="namaLengkap" class="col-sm-2 col-form-label">Akses</label>
                                <div class="col-sm-10">
                                    <select class="form-select" name="akses" id="akses" required>
                                        <option value="" disabled selected>-- Pilih Akses --</option>
                                        <option value="individu" <?= ($akses == 'individu') ? 'selected' : ''; ?>>Individu</option>
                                        <option value="kelompok" <?= ($akses == 'kelompok') ? 'selected' : ''; ?>>Kelompok</option>
                                        <option value="umum" <?= ($akses == 'umum') ? 'selected' : ''; ?>>Umum</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="tahap" class="col-sm-2 col-form-label">Tahap</label>
                                <div class="col-sm-10">
                                    <select class="form-select" id="tahap" name="tahap">
                                        <option value="" disabled selected>-- Pilih Tahap --</option>
                                        <option value="studi_kasus" <?= ($tahap == 'studi_kasus') ? 'selected' : ''; ?>>Studi Kasus</option>
                                        <option value="perencanaan" <?= ($tahap == 'perencanaan') ? 'selected' : ''; ?>>Perencanaan</option>
                                        <option value="eksplorasi" <?= ($tahap == 'eksplorasi') ? 'selected' : ''; ?>>Eksplorasi</option>
                                        <option value="pengembangan" <?= ($tahap == 'pengembangan') ? 'selected' : ''; ?>>Pengembangan</option>
                                        <option value="presentasi" <?= ($tahap == 'presentasi') ? 'selected' : ''; ?>>Presentasi</option>
                                        <option value="evaluasi" <?= ($tahap == 'evaluasi') ? 'selected' : ''; ?>>Evaluasi</option>
                                        <option value="penilaian" <?= ($tahap == 'penilaian') ? 'selected' : ''; ?>>Penilaian</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="id_proyek" class="col-sm-2 col-form-label">ID Proyek</label>
                                <div class="col-sm-10">
                                    <input type="int" class="form-control" placeholder="Id Proyek" name="id_proyek" value="<?php echo $id_proyek ?>" id="id_proyek">
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
                        Data Tugas
                    </div>
                    <div class="card-body">
                        <!-- Add a wrapper div for the table -->
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Judul Tugas</th>
                                        <th scope="col">Deskripsi</th>
                                        <th scope="col">Tenggat Waktu</th>
                                        <th scope="col">Akses</th>
                                        <th scope="col">Tahap</th>
                                        <th scope="col">ID Proyek</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()) { ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++; ?></th>
                                            <td><?= htmlspecialchars($row['judul_tugas']); ?></td>
                                            <td><?= htmlspecialchars($row['deskripsi']); ?></td>
                                            <td><?= htmlspecialchars($row['dateline']); ?></td>
                                            <td><?= htmlspecialchars($row['akses']); ?></td>
                                            <td><?= htmlspecialchars($row['tahap']); ?></td>
                                            <td><?= (int)$row['id_proyek']; ?> </td>
                                            <td>
                                                <!-- Tombol Edit -->
                                                <a href="atur_tugas.php?op=edit&id_tugas=<?= $row['id_tugas']; ?>">
                                                    <button type="button" class="btn btn-warning">
                                                        <i class="bi bi-pen-fill"></i> Edit
                                                    </button>
                                                </a>
                                                <!-- Tombol Hapus -->
                                                <a href="atur_tugas.php?op=delete&id_tugas=<?= $row['id_tugas']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>

    <script>
        function navigateToPage() {
            window.history.back();
        }
    </script>

</body>

</html>