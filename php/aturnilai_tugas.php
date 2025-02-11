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


$id_pengumpulan = "";
$id_tugas = "";
$id_kelompok = "";
$file_tugas = "";
$tanggal_upload = "";
$id_pengguna = "";
$nilai = "";
$catatan_guru = "";

$sukses = "";
$error = "";

// CREATE or UPDATE
if (isset($_POST['submit'])) {
    $id_tugas = mysqli_real_escape_string($koneksi, $_POST['id_tugas']);
    $id_kelompok = isset($_POST['id_kelompok']) && $_POST['id_kelompok'] !== '' ? (int) $_POST['id_kelompok'] : NULL;
    $tanggal_upload = mysqli_real_escape_string($koneksi, $_POST['tanggal_upload']);
    $nilai = isset($_POST['nilai']) ? mysqli_real_escape_string($koneksi, $_POST['nilai']) : NULL;
    $catatan_guru = isset($_POST['catatan_guru']) ? mysqli_real_escape_string($koneksi, $_POST['catatan_guru']) : NULL;
    $id_pengguna = $_SESSION['id_pengguna'];

    // Proses Upload File
    $file_tugas = "";
    if (isset($_FILES['file_tugas']) && $_FILES['file_tugas']['error'] == 0) {
        $target_dir = "uploads/";
        $file_tugas = $target_dir . basename($_FILES["file_tugas"]["name"]);
        move_uploaded_file($_FILES["file_tugas"]["tmp_name"], $file_tugas);
    } else {
        $file_tugas = isset($_POST['file_tugas_lama']) ? $_POST['file_tugas_lama'] : "";
    }

    // Proses Insert atau Update
    if (isset($_GET['op']) && $_GET['op'] == 'edit') {
        $id_pengumpulan = mysqli_real_escape_string($koneksi, $_GET['id_pengumpulan']);
        $sql = "UPDATE pengumpulan_tugas SET 
            nilai = " . ($nilai ? "'$nilai'" : "NULL") . ", 
            catatan_guru = " . ($catatan_guru ? "'$catatan_guru'" : "NULL") . " 
            WHERE id_pengumpulan = '$id_pengumpulan'";
    } else {
        $sql = "INSERT INTO pengumpulan_tugas (id_tugas, id_kelompok, file_tugas, tanggal_upload, id_pengguna, nilai, catatan_guru) 
                VALUES ('$id_tugas', '$id_kelompok', '$file_tugas', '$tanggal_upload', '$id_pengguna', " . ($nilai ? "'$nilai'" : "NULL") . ", " . ($catatan_guru ? "'$catatan_guru'" : "NULL") . ")";
    }

    if (mysqli_query($koneksi, $sql)) {
        $sukses = isset($_GET['op']) && $_GET['op'] == 'edit' ? "Data berhasil diperbarui!" : "Data berhasil ditambahkan!";
    } else {
        $error = "Gagal memproses data: " . mysqli_error($koneksi);
    }
}

// DELETE
if (isset($_GET['op']) && $_GET['op'] == 'delete') {
    $id_pengumpulan = mysqli_real_escape_string($koneksi, $_GET['id_pengumpulan']);

    // Ambil file sebelum dihapus
    $query_file = mysqli_query($koneksi, "SELECT file_tugas FROM pengumpulan_tugas WHERE id_pengumpulan = '$id_pengumpulan'");
    $data = mysqli_fetch_array($query_file);
    if ($data['file_tugas'] && file_exists($data['file_tugas'])) {
        unlink($data['file_tugas']);
    }

    $sql = "DELETE FROM pengumpulan_tugas WHERE id_pengumpulan = '$id_pengumpulan'";
    if (mysqli_query($koneksi, $sql)) {
        $sukses = "Data berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data!";
    }
}

// READ (Untuk Edit)
if (isset($_GET['op']) && $_GET['op'] == 'edit') {
    $id_pengumpulan = mysqli_real_escape_string($koneksi, $_GET['id_pengumpulan']);
    $sql = "SELECT p.*, u.namaLengkap, t.judul_tugas, k.nama_kelompok 
            FROM pengumpulan_tugas p
            LEFT JOIN pengguna u ON p.id_pengguna = u.id_pengguna
            LEFT JOIN tugas t ON p.id_tugas = t.id_tugas
            LEFT JOIN kelompok k ON p.id_kelompok = k.id_kelompok
            WHERE p.id_pengumpulan = '$id_pengumpulan'";

    $q = mysqli_query($koneksi, $sql);

    if ($q) {
        $r = mysqli_fetch_array($q);

        if ($r) { // Pastikan array tidak null sebelum mengakses elemen
            $id_tugas = $r['id_tugas'] ?? "";
            $id_kelompok = $r['id_kelompok'] ?? "";
            $file_tugas = $r['file_tugas'] ?? "";
            $tanggal_upload = $r['tanggal_upload'] ?? "";
            $id_pengguna = $r['id_pengguna'] ?? "";
            $nilai = $r['nilai'] ?? "";
            $catatan_guru = $r['catatan_guru'] ?? "";
        } else {
            $error = "Data tidak ditemukan.";
            $id_tugas = $id_kelompok = $file_tugas = $tanggal_upload = $id_pengguna = $nilai = $catatan_guru = "";
        }
    } else {
        $error = "Query gagal: " . mysqli_error($koneksi);
    }
} else {
    $id_tugas = $id_kelompok = $file_tugas = $tanggal_upload = $id_pengguna = $nilai = $catatan_guru = "";
}


// Fetch data untuk ditampilkan dalam tabel
$sql = "SELECT p.*, u.namaLengkap, t.judul_tugas, k.nama_kelompok 
        FROM pengumpulan_tugas p
        LEFT JOIN pengguna u ON p.id_pengguna = u.id_pengguna
        LEFT JOIN tugas t ON p.id_tugas = t.id_tugas
        LEFT JOIN kelompok k ON p.id_kelompok = k.id_kelompok
        ORDER BY p.id_pengumpulan DESC";
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
                        Kelola Nilai Tugas
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)) { ?>
                            <div id="alert-error" class="alert alert-danger col-sm-12">
                                <ul><?php echo $error; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "aturnilai_tugas.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <?php if (!empty($sukses)) { ?>
                            <div id="alert-success" class="alert alert-success col-sm-12">
                                <ul><?php echo $sukses; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "aturnilai_tugas.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="id_pengumpulan" class="col-sm-2 col-form-label">ID Pengumpulan</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="ID Pengumpulan" name="id_pengumpulan" value="<?php echo $id_pengumpulan ?>" id="id_pengumpulan">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="id_tugas" class="col-sm-2 col-form-label">ID Tugas</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="ID Tugas" name="id_tugas" value="<?php echo $id_tugas ?>" id="id_tugas">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="id_kelompok" class="col-sm-2 col-form-label">ID Kelompok</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="ID Kelompok" name="id_kelompok" value="<?php echo $id_kelompok ?>" id="id_kelompok">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="tanggal_upload" class="col-sm-2 col-form-label">Tanggal Upload</label>
                                <div class="col-sm-10">
                                    <input type="date" class="form-control" placeholder="tanggal_upload" name="tanggal_upload" value="<?php echo $tanggal_upload ?>" id="tanggal_upload">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="id_pengguna" class="col-sm-2 col-form-label">ID Pengguna</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="Id Proyek" name="id_pengguna" value="<?php echo $id_pengguna ?>" id="id_pengguna">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="nilai" class="col-sm-2 col-form-label">Nilai</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" placeholder="Nilai" name="nilai" value="<?php echo $nilai ?>" id="nilai">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="catatan_guru" class="col-sm-2 col-form-label">Catatan Guru</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="Catatan Guru" name="catatan_guru" value="<?php echo $catatan_guru ?>" id="catatan_guru">
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
                        Data Tugas Siswa
                    </div>
                    <div class="card-body">
                        <!-- Add a wrapper div for the table -->
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nama Tugas</th>
                                        <th scope="col">Nama Kelompok</th>
                                        <th scope="col">File Tugas</th>
                                        <th scope="col">Tanggal Pengumpulan</th>
                                        <th scope="col">Nama Pengguna</th>
                                        <th scope="col">Nilai</th>
                                        <th scope="col">Catatan Guru</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql2 = "SELECT p.id_pengumpulan, t.judul_tugas, k.nama_kelompok, p.file_tugas, 
                                    p.tanggal_upload, u.namaLengkap, p.nilai, p.catatan_guru
                             FROM pengumpulan_tugas p
                             LEFT JOIN tugas t ON p.id_tugas = t.id_tugas
                             LEFT JOIN kelompok k ON p.id_kelompok = k.id_kelompok
                             LEFT JOIN pengguna u ON p.id_pengguna = u.id_pengguna
                             ORDER BY p.id_pengumpulan DESC";

                                    $q2 = mysqli_query($koneksi, $sql2);
                                    $urut = 1;

                                    while ($r2 = mysqli_fetch_array($q2)) {
                                        $id_pengumpulan          = (int)$r2['id_pengumpulan'];

                                        $judul_tugas     = htmlspecialchars($r2['judul_tugas']);
                                        $nama_kelompok  = htmlspecialchars($r2['nama_kelompok']);
                                        $file_tugas     = $r2['file_tugas'];
                                        $tanggal_upload = htmlspecialchars($r2['tanggal_upload']);
                                        $nama_pengguna  = htmlspecialchars($r2['namaLengkap']);
                                        $nilai          = (int)$r2['nilai'];
                                        $catatan_guru   = htmlspecialchars($r2['catatan_guru']);
                                    ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++; ?></th>
                                            <td><?php echo $judul_tugas; ?></td>
                                            <td><?php echo $nama_kelompok; ?></td>
                                            <td>
                                                <?php if (!empty($file_tugas)): ?>
                                                    <a href="<?php echo $file_tugas ?>" target="_blank">
                                                        <button type="button" class="btn btn-primary">View PDF</button>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-danger">No File</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $tanggal_upload; ?></td>
                                            <td><?php echo $nama_pengguna; ?></td>
                                            <td><?php echo $nilai; ?></td>
                                            <td><?php echo $catatan_guru; ?></td>
                                            <td>
                                                <a href="aturnilai_tugas.php?op=edit&id_pengumpulan=<?php echo $id_pengumpulan; ?>">
                                                    <button type="button" class="btn btn-warning">
                                                        <i class="bi bi-pen-fill"></i>
                                                    </button>
                                                </a>
                                                <a href="aturnilai_tugas.php?op=delete&id_pengumpulan=<?php echo $id_pengumpulan; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
                                                    <button type="button" class="btn btn-danger">
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

    <script>
        function navigateToPage() {
            window.history.back();
        }
    </script>

</body>

</html>