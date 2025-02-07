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

$id_pengguna = $_SESSION['id_pengguna']; // Ambil id_pengguna dari session


// Ambil ID peraturan dari parameter GET
$id_peraturan = isset($_GET['id_peraturan']) ? intval($_GET['id_peraturan']) : 0;

// Ambil data peraturan
$peraturan = $koneksi->query("SELECT waktu FROM tbl_pengaturan WHERE id_peraturan = '$id_peraturan' LIMIT 1")->fetch_assoc();
if (!$peraturan) {
    echo "Data peraturan tidak ditemukan. Silakan periksa ID peraturan.";
    exit;
}

// Ambil soal berdasarkan ID peraturan
$result = $koneksi->query("SELECT * FROM tbl_soal WHERE id_peraturan = '$id_peraturan'");
if ($result->num_rows == 0) {
    echo "Tidak ada soal untuk ujian ini.";
    exit;
}

// Proses jawaban
if (isset($_POST['submit'])) {
    // Simpan jawaban ke database atau proses sesuai kebutuhan
    foreach ($_POST['jawaban'] as $id_soal => $jawaban) {
        // Simpan jawaban ke database atau lakukan sesuatu dengan jawaban
        // Contoh: $koneksi->query("INSERT INTO tbl_jawaban (id_soal, jawaban) VALUES ('$id_soal', '$jawaban')");
    }
    echo "Jawaban berhasil dikirim!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
    <title>Mengerjakan Soal</title>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <h1>Mengerjakan Ujian</h1>
                <form method="POST" action="">
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <div>
                            <p><?= $row['pertanyaan']; ?></p>
                            <input type="radio" name="jawaban[<?= $row['id_soal']; ?>]" value="A"> <?= $row['a']; ?><br>
                            <input type="radio" name="jawaban[<?= $row['id_soal']; ?>]" value="B"> <?= $row['b']; ?><br>
                            <input type="radio" name="jawaban[<?= $row['id_soal']; ?>]" value="C"> <?= $row['c']; ?><br>
                            <input type="radio" name="jawaban[<?= $row['id_soal']; ?>]" value="D"> <?= $row['d']; ?><br>
                        </div>
                    <?php } ?>
                    <button type="submit" name="submit">Kirim Jawaban</button>
                </form>
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