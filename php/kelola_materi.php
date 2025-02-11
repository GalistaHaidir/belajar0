<?php
include("koneksi.php");

session_start();
if (!isset($_SESSION['session_username'])) {
    header("location:login.php");
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

$id_materi = "";
$title = "";
$category = "";
$description = "";
$file_path = "";
$video_path = "";

if (isset($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = "";
} // Proses Delete
if ($op == 'delete') {
    $id_materi = $_GET['id_materi'];
    $sql1 = "SELECT file_path, video_path FROM materi WHERE id_materi = '$id_materi'";
    $q1 = mysqli_query($koneksi, $sql1);
    $r1 = mysqli_fetch_array($q1);

    if (!empty($r1['file_path']) && file_exists($r1['file_path'])) {
        unlink($r1['file_path']); // Menghapus file PDF dari server
    }

    if (!empty($r1['video_path']) && file_exists($r1['video_path'])) {
        unlink($r1['video_path']); // Menghapus video dari server
    }

    $sql2 = "DELETE FROM materi WHERE id_materi = '$id_materi'";
    $q2 = mysqli_query($koneksi, $sql2);
    if ($q2) {
        $sukses = "Berhasil menghapus materi";
    } else {
        $error = "Gagal menghapus materi";
    }
}

// Proses Edit (Read data untuk form)
if ($op == 'edit') {
    $id_materi = $_GET['id_materi'];
    $sql1 = "SELECT * FROM materi WHERE id_materi = '$id_materi'";
    $q1 = mysqli_query($koneksi, $sql1);
    $r1 = mysqli_fetch_array($q1);
    $title = $r1['title'];
    $category = $r1['category'];
    $description = $r1['description'];
    $file_path = $r1['file_path'];
    $video_path = $r1['video_path'];
    if ($title == '') {
        $error = "Data tidak ditemukan";
    }
}

// Proses Create dan Update
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $file_path = '';
    $video_path = '';

    // Cek apakah ada file PDF yang diupload
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0) {
        $file_path = 'pdf/' . basename($_FILES['pdf_file']['name']);
        move_uploaded_file($_FILES['pdf_file']['tmp_name'], $file_path);
    }

    // Cek apakah ada video yang diupload
    if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0) {
        $video_path = 'videos/' . basename($_FILES['video_file']['name']);
        move_uploaded_file($_FILES['video_file']['tmp_name'], $video_path);
    }

    if ($title && $description) {
        if ($op == 'edit') { // Update
            if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] == 0 && !empty($r1['file_path'])) {
                unlink($r1['file_path']); // Menghapus file PDF lama
            }
            if (isset($_FILES['video_file']) && $_FILES['video_file']['error'] == 0 && !empty($r1['video_path'])) {
                unlink($r1['video_path']); // Menghapus video lama
            }

            $sql1 = "UPDATE materi SET title = '$title', category = '$category', description = '$description', file_path = '$file_path', video_path = '$video_path' WHERE id_materi = '$id_materi'";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Materi berhasil diperbarui";
            } else {
                $error = "Materi gagal diperbarui";
            }
        } else { // Insert
            $sql1 = "INSERT INTO materi (title, category, description, file_path, video_path) VALUES ('$title', '$category', '$description', '$file_path', '$video_path')";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Materi berhasil diupload";
            } else {
                $error = "Materi gagal diupload";
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
    <title>Kelola Materi</title>
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
                        Kelola Materi
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)) { ?>
                            <div id="alert-error" class="alert alert-danger col-sm-12">
                                <ul><?php echo $error; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "kelola_materi.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <?php if (!empty($sukses)) { ?>
                            <div id="alert-success" class="alert alert-success col-sm-12">
                                <ul><?php echo $sukses; ?></ul>
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = "kelola_materi.php";
                                }, 5000);
                            </script>
                        <?php } ?>

                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="title" class="col-sm-2 col-form-label">Deskripsi Materi</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="Deskripsi Materi" name="title" value="<?php echo $title ?>" id="title" required>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="category" class="col-sm-2 col-form-label">Kategori Materi</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="category" id="category" required>
                                        <option value="" disabled <?php echo empty($category) ? 'selected' : ''; ?>>Pilih Kategori Materi</option>
                                        <option value="html" <?php echo $category === 'html' ? 'selected' : ''; ?>>HTML</option>
                                        <option value="css" <?php echo $category === 'css' ? 'selected' : ''; ?>>CSS</option>
                                        <option value="javascript" <?php echo $category === 'javascript' ? 'selected' : ''; ?>>JavaScript</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="description" class="col-sm-2 col-form-label">Capaian Pembelajaran</label>
                                <div class="col-sm-10">
                                    <textarea class="form-control" placeholder="Capaian Pembelajaran" name="description" id="description" required><?php echo $description ?></textarea>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="pdf_file" class="col-sm-2 col-form-label">File Materi</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" name="pdf_file" id="pdf_file" <?php echo ($op != 'edit') ? 'required' : ''; ?>>

                                    <?php if (!empty($file_path)) : ?>
                                        <p class="mt-2">File saat ini: <a href="<?php echo $file_path; ?>" target="_blank">Lihat PDF</a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="video_file" class="col-sm-2 col-form-label">File Video</label>
                                <div class="col-sm-10">
                                    <input type="file" class="form-control" name="video_file" id="video_file" <?php echo ($op != 'edit') ? 'required' : ''; ?>>
                                    <?php if ($op == 'edit' && !empty($video_path)) : ?>
                                        <p class="mt-2">Video saat ini:</p>
                                        <video width="320" height="180" controls style="margin-top: 10px;">
                                            <source src="<?php echo htmlspecialchars($video_path); ?>" type="video/mp4">
                                            Browser Anda tidak mendukung tag video.
                                        </video>
                                    <?php endif; ?>
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
                        Data Materi
                    </div>
                    <div class="card-body">
                        <!-- Add a wrapper div for the table -->
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Deskripsi Materi</th>
                                        <th scope="col">Kategori Materi</th>
                                        <th scope="col">Capaian Pembelajaran</th>
                                        <th scope="col">File Materi</th>
                                        <th scope="col">File Video</th>
                                        <th scope="col">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql2 = "SELECT * FROM materi ORDER BY id_materi DESC";
                                    $q2 = mysqli_query($koneksi, $sql2);
                                    $urut = 1;
                                    while ($r2 = mysqli_fetch_array($q2)) {
                                        $id_materi   = $r2['id_materi'];
                                        $title       = $r2['title'];
                                        $category    = $r2['category'];
                                        $description = $r2['description'];
                                        $file_path   = $r2['file_path'];
                                        $video_path  = $r2['video_path'];
                                    ?>
                                        <tr>
                                            <th scope="row"><?php echo $urut++ ?></th>
                                            <td><?php echo $title ?></td>
                                            <td><?php echo $category ?></td>
                                            <td><?php echo $description ?></td>
                                            <td>
                                                <a href="<?php echo $file_path ?>" target="_blank">
                                                    <button type="button" class="btn btn-primary">View PDF</button>
                                                </a>
                                            </td>
                                            <td>
                                                <video width="150" height="100" controls>
                                                    <source src="<?php echo $video_path ?>" type="video/mp4">
                                                    Your browser does not support the video tag.
                                                </video>
                                            </td>
                                            <td>
                                                <a href="kelola_materi.php?op=edit&id_materi=<?php echo $id_materi ?>">
                                                    <button type="button" class="btn btn-warning"><i class="bi bi-pen-fill"></i></button>
                                                </a>
                                                <a href="kelola_materi.php?op=delete&id_materi=<?php echo $id_materi ?>" onclick="return confirm('Yakin ingin menghapus PDF ini?')">
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