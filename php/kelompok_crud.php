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
                                    window.location.href = "kelompok_crud.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <?php if (!empty($sukses)) { ?>
                            <div id="alert-success" class="alert alert-success col-sm-12">
                                <ul><?php echo $sukses; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "kelompok_crud.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="nama_kelompok" class="col-sm-2 col-form-label">Nama Kelompok</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="Nama Kelompok" name="nama_kelompok" value="<?php echo $nama_kelompok ?>" id="nama_kelompok">
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
                                        $nama_kelompok        = $r2['nama_kelompok'];
                                    ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++ ?></th>
                                            <td><?php echo $nama_kelompok ?></td>
                                            <td>
                                                <a href="kelompok_crud.php?op=edit&id_kelompok=<?php echo $id_kelompok ?>">
                                                    <button type="button" class="btn btn-warning"><i class="bi bi-pen-fill"></i></button>
                                                </a>
                                                <a href="kelompok_crud.php?op=delete&id_kelompok=<?php echo $id_kelompok ?>" onclick="return confirm('Yakin ingin menghapus kelompok ini?')">
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