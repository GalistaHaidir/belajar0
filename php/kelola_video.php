<?php
include("koneksi.php");

$sukses = "";
$error = "";

$id = "";
$title = "";
$description = "";
$file_path = "";

if (isset($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = "";
}

// Proses Delete
if ($op == 'delete') {
    $id = $_GET['id'];
    $sql1 = "DELETE FROM tbl_video WHERE id = '$id'";
    $q1 = mysqli_query($koneksi, $sql1);
    if ($q1) {
        $sukses = "Berhasil menghapus video";
    } else {
        $error = "Gagal menghapus video";
    }
}

// Proses Edit (Read data untuk form)
if ($op == 'edit') {
    $id = $_GET['id'];
    $sql1 = "SELECT * FROM tbl_video WHERE id = '$id'";
    $q1 = mysqli_query($koneksi, $sql1);
    $r1 = mysqli_fetch_array($q1);
    $title = $r1['title'];
    $description = $r1['description'];
    $file_path = $r1['file_path'];
    if ($title == '') {
        $error = "Data tidak ditemukan";
    }
}

// Proses Create dan Update
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $file_path = '';

    // Cek apakah ada file yang diupload
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $file_path = 'uploads/' . basename($_FILES['video_file']['name']);
        move_uploaded_file($_FILES['video_file']['tmp_name'], $file_path);
    }

    if ($title && $description) {
        if ($op == 'edit') { // Update
            $sql1 = "UPDATE tbl_video SET title = '$title', description = '$description', file_path = '$file_path' WHERE id = '$id'";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Video berhasil diperbarui";
            } else {
                $error = "Video gagal diperbarui";
            }
        } else { // Insert
            $sql1 = "INSERT INTO tbl_video (title, description, file_path) VALUES ('$title', '$description', '$file_path')";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Video berhasil diupload";
            } else {
                $error = "Video gagal diupload";
            }
        }
    } else {
        $error = "Silahkan isi semua kolom";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
</head>

<body>
    <div class="wrapper">
        <aside id="sidebar">
            <div class="d-flex justify-content-between p-4">
                <div class="sidebar-logo">
                    <img src="logo.png" alt="Logo Belajar.0">
                </div>
                <button class="toggle-btn border-0" type="button">
                    <i id="icon" class="bi bi-arrow-right-short text-white"></i>
                </button>
            </div>
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="guru_home.php" class="sidebar-link">
                        <i class="bi bi-house-door-fill"></i>
                        <span>Halaman Utama</span>
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="guru_materi.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth1" aria-expanded="false" aria-controls="auth1">
                        <i class="bi bi-file-earmark-code-fill"></i>
                        <span>Materi</span>
                    </a>
                    <ul id="auth1" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="guru_materi.php" class="sidebar-link">
                                Kelola Materi
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                Unggah Materi
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                Kelompokkan Materi
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth2" aria-expanded="false" aria-controls="auth2">
                        <i class="bi bi-pencil-fill"></i>
                        <span>Soal</span>
                    </a>
                    <ul id="auth2" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                Bank Soal
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                eop
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="guru_tugas.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth3" aria-expanded="false" aria-controls="auth3">
                        <i class="bi bi-list-check"></i>
                        <span>Tugas</span>
                    </a>
                    <ul id="auth3" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                Kelola Tugas
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                eop
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="guru_evaluasi.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth4" aria-expanded="false" aria-controls="auth4">
                        <i class="bi bi-journal-text"></i>
                        <span>Evaluasi</span>
                    </a>
                    <ul id="auth4" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                poe
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                eop
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="sidebar-item">
                    <a href="guru_siswa.php" class="sidebar-link collapsed has-dropdown" data-bs-toggle="collapse"
                        data-bs-target="#auth5" aria-expanded="false" aria-controls="auth5">
                        <i class="bi bi-person-arms-up"></i>
                        <span>Siswa</span>
                    </a>
                    <ul id="auth5" class="sidebar-dropdown list-unstyled collapse" data-bs-parent="#sidebar">
                        <li class="sidebar-item">
                            <a href="guru_akunsiswa.php" class="sidebar-link">
                                Kelola Akun Siswa
                            </a>
                        </li>
                        <li class="sidebar-item">
                            <a href="#" class="sidebar-link">
                                eop
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
            <div class="sidebar-footer mb-1">
                <a href="logout.php" class="sidebar-link">
                    <i class="bi bi-door-open-fill"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </aside>
        <div class="main">
            <nav class="navbar navbar-expand px-4 py-3">
                <h3 class="fw-bold" style="text-transform: capitalize;">Hi, <?= $sessionUsername; ?></h3>
                <div class="navbar-collapse collapse">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a href="#" data-bs-toggle="dropdown" class="nav-icon pe-md-0">
                                <img src="cover.jpg" class="avatar omg-fluid" alt="">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end rounded-0 border-0 shadow mt-3">
                                <a href="#" class="dropdown-item">
                                    <i class="bi bi-person-fill"></i>
                                    <span>Akun</span>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item">
                                    <i class="bi bi-door-open-fill"></i>
                                    <span>Keluar</span>
                                </a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="content px-3 py-4">
                <div class="card">
                    <div class="card-header text-light" style="background-color: #0b1915;">
                        Kelola Video
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)) { ?>
                            <div id="alert-error" class="alert alert-danger col-sm-12">
                                <ul><?php echo $error; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "kelola_video.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <?php if (!empty($sukses)) { ?>
                            <div id="alert-success" class="alert alert-success col-sm-12">
                                <ul><?php echo $sukses; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "kelola_video.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="title" class="col-sm-2 col-form-label">Judul Video</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="Judul Video" name="title" value="<?php echo $title ?>" id="title" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="description" class="col-sm-2 col-form-label">Deskripsi</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" placeholder="Deskripsi Video" name="description" id="description" required><?php echo $description ?></textarea>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="video_file" class="col-sm-2 col-form-label">File Video</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" name="video_file" id="video_file" <?php echo ($op != 'edit') ? 'required' : ''; ?>>
                                </div>
                            </div>
                            <div class="col-12">
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="bi bi-upload"></i> Simpan
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="card">
                        <div class="card-header text-white" style="background-color: #0b1915;">
                            Data Video
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Judul</th>
                                        <th scope="col">Deskripsi</th>
                                        <th scope="col">Video</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql2 = "SELECT * FROM tbl_video ORDER BY id DESC";
                                    $q2 = mysqli_query($koneksi, $sql2);
                                    $urut = 1;
                                    while ($r2 = mysqli_fetch_array($q2)) {
                                        $id          = $r2['id'];
                                        $title       = $r2['title'];
                                        $description = $r2['description'];
                                        $file_path   = $r2['file_path'];
                                    ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++ ?></th>
                                            <td><?php echo $title ?></td>
                                            <td><?php echo $description ?></td>
                                            <td>
                                                <video width="150" controls>
                                                    <source src="<?php echo $file_path ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </td>
                                            <td>
                                                <a href="kelola_video.php?op=edit&id=<?php echo $id ?>">
                                                    <button type="button" class="btn btn-warning"><i class="bi bi-pen-fill"></i></button>
                                                </a>
                                                <a href="kelola_video.php?op=delete&id=<?php echo $id ?>" onclick="return confirm('Yakin ingin menghapus video ini?')">
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

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row text-body-secondary">
                        <div class="col-6 text-start">
                            <a href="#" class="text-body-secondary">
                                <strong>Belajar.0</strong>
                            </a>
                        </div>
                        <div class="col-6 text-end text-body-secondary d-none d-md-block">
                            <ul class="list-inline mb-0">
                                <li class="list-inline-item">
                                    <a href="#" class="text-body-secondary">Contact</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="text-body-secondary">About</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="text-body-secondary">Terms & Conditions</a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
</body>

</html>