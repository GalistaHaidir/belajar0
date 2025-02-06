<?php
include("koneksi.php");

session_start();
if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
    exit();
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
                <!-- Card: Kelola Video -->
                <div class="card" style="border-radius: 20px;">
                    <div class="card-header text-light" style="background-color: #0b1915; font-weight: bold; border-top-left-radius: 20px; border-top-right-radius: 20px;">
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
                </div>
                <div class="card mt-4" style="border-radius: 20px;">
                    <div class="card-header text-white" style="background-color: #0b1915; font-weight: bold; border-top-left-radius: 20px; border-top-right-radius: 20px;">
                        Data Video
                    </div>
                    <div style="max-height: 350px; overflow-y: auto;">
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