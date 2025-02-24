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
$tahap = NULL;
$id_proyek = NULL;

$sukses = "";
$error = "";

// Cek jika form disubmit
if (isset($_POST['submit'])) {
    // Gunakan mysqli_real_escape_string untuk menghindari SQL Injection
    $judul_tugas = mysqli_real_escape_string($koneksi, $_POST['judul_tugas']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $akses = mysqli_real_escape_string($koneksi, $_POST['akses']);

    // Konversi dateline ke format DATETIME MySQL
    $dateline = DateTime::createFromFormat('Y-m-d\TH:i', $_POST['dateline']);
    if ($dateline) {
        $dateline = $dateline->format('Y-m-d H:i:s'); // Format untuk database
    } else {
        $error = "Format tanggal tidak valid!";
    }

    // Cek apakah `tahap` dan `id_proyek` ada, jika tidak maka NULL
    $tahap = isset($_POST['tahap']) && $_POST['tahap'] !== "" ? $_POST['tahap'] : NULL;
    $id_proyek = isset($_POST['id_proyek']) && $_POST['id_proyek'] !== "" ? $_POST['id_proyek'] : NULL;

    // Validasi input
    if ($judul_tugas == "" || $deskripsi == "" || $dateline == "" || $akses == "") {
        $error = "Semua data harus diisi!";
    } else {
        if (isset($_GET['op']) && $_GET['op'] == 'edit') {
            // Mode Edit
            $id_tugas = $_GET['id_tugas'];
            $sql = "UPDATE tugas SET judul_tugas = ?, deskripsi = ?, dateline = ?, akses = ?, tahap = ?, id_proyek = ? WHERE id_tugas = ?";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "ssssssi", $judul_tugas, $deskripsi, $dateline, $akses, $tahap, $id_proyek, $id_tugas);
        } else {
            // Mode Insert (Tambah Data)
            $sql = "INSERT INTO tugas (judul_tugas, deskripsi, dateline, akses, tahap, id_proyek) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($koneksi, $sql);
            mysqli_stmt_bind_param($stmt, "ssssss", $judul_tugas, $deskripsi, $dateline, $akses, $tahap, $id_proyek);
        }

        // Eksekusi query
        if (mysqli_stmt_execute($stmt)) {
            $sukses = (isset($_GET['op']) && $_GET['op'] == 'edit') ? "Data berhasil diperbarui!" : "Data berhasil ditambahkan!";
        } else {
            $error = "Gagal menyimpan data! Error: " . mysqli_error($koneksi);
        }

        // Tutup statement
        mysqli_stmt_close($stmt);
    }
}

// DELETE
if (isset($_GET['op']) && $_GET['op'] == 'delete') {
    $id_tugas = $_GET['id_tugas'];
    $sql = "DELETE FROM tugas WHERE id_tugas = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_tugas);
    if (mysqli_stmt_execute($stmt)) {
        $sukses = "Data berhasil dihapus!";
    } else {
        $error = "Gagal menghapus data!";
    }
    mysqli_stmt_close($stmt);
}

// READ (Untuk Edit)
if (isset($_GET['op']) && $_GET['op'] == 'edit') {
    $id_tugas = $_GET['id_tugas'];
    $sql = "SELECT * FROM tugas WHERE id_tugas = ?";
    $stmt = mysqli_prepare($koneksi, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_tugas);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($r = mysqli_fetch_array($result)) {
        $judul_tugas = $r['judul_tugas'];
        $deskripsi = $r['deskripsi'];
        $dateline = date('Y-m-d\TH:i', strtotime($r['dateline'])); // Konversi agar sesuai input HTML
        $akses = $r['akses'];
        $tahap = $r['tahap'];
        $id_proyek = $r['id_proyek'];
    }
    mysqli_stmt_close($stmt);
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
    <title>Kelola Tugas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
    <style>
        /* Styling body */
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            color: #1B5E20;
        }

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
                    onclick="window.location.href='kelola_tugas.php';">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i>
                    <span>Kembali</span>
                </a>
                <!-- Card: Kelola Materi -->
                <div class="card custom-card">
                    <div class="card-header custom-header">
                        Kelola Tugas
                    </div>
                    <div class="card-body">
                        <!-- Tampilkan pesan error jika ada -->
                        <?php if (!empty($error)) { ?>
                            <div id="alert-error" class="alert alert-danger col-sm-12">
                                <p><?php echo htmlspecialchars($error); ?>, Halaman akan direfresh dalam <span id="countdown-error">5</span> detik...</p>
                            </div>
                            <script>
                                let timeLeftError = 5;
                                let countdownErrorElement = document.getElementById("countdown-error");

                                if (countdownErrorElement) { // Pastikan elemen ada sebelum menjalankan interval
                                    let timerError = setInterval(function() {
                                        timeLeftError--;
                                        countdownErrorElement.innerText = timeLeftError;
                                        if (timeLeftError <= 0) {
                                            clearInterval(timerError);
                                            window.location.href = "atur_tugas.php";
                                        }
                                    }, 1000);
                                }
                            </script>
                        <?php } ?>

                        <?php if (!empty($sukses)) { ?>
                            <div id="alert-success" class="alert alert-success col-sm-12">
                                <p><?php echo htmlspecialchars($sukses); ?>, Halaman akan direfresh dalam <span id="countdown-success">5</span> detik...</p>
                            </div>
                            <script>
                                let timeLeftSuccess = 5;
                                let countdownSuccessElement = document.getElementById("countdown-success");

                                if (countdownSuccessElement) { // Pastikan elemen ada sebelum menjalankan interval
                                    let timerSuccess = setInterval(function() {
                                        timeLeftSuccess--;
                                        countdownSuccessElement.innerText = timeLeftSuccess;
                                        if (timeLeftSuccess <= 0) {
                                            clearInterval(timerSuccess);
                                            window.location.href = "atur_tugas.php";
                                        }
                                    }, 1000);
                                }
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
                                    <input type="datetime-local" class="form-control" placeholder="Tenggat Waktu" name="dateline" value="<?php echo $dateline ?>" id="dateline">
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
                                                    <button type="button" class="btn btn-warning btn-sm btn-custom">
                                                        <i class="bi bi-pen-fill"></i>
                                                    </button>
                                                </a>
                                                <!-- Tombol Hapus -->
                                                <a href="atur_tugas.php?op=delete&id_tugas=<?= $row['id_tugas']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="guru_home.js"></script>

</body>

</html>