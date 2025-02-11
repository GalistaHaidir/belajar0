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



$id_tugas = $_GET['id_tugas'];
$id_pengguna = $_SESSION['id_pengguna']; // Ambil ID pengguna yang sedang login

// Ambil detail tugas
$query_tugas = "
    SELECT id_tugas, judul_tugas, dateline, deskripsi
    FROM tugas
    WHERE id_tugas = ?
";
$stmt = $koneksi->prepare($query_tugas);
$stmt->bind_param("i", $id_tugas);
$stmt->execute();
$result_tugas = $stmt->get_result();
$tugas = $result_tugas->fetch_assoc();

if (!$tugas) {
    echo "Tugas tidak ditemukan!";
    exit;
}

// Cek apakah pengguna sudah mengumpulkan tugas ini
$query_pengumpulan = "
    SELECT nilai, catatan_guru 
    FROM pengumpulan_tugas 
    WHERE id_tugas = ? AND id_pengguna = ?
";
$stmt = $koneksi->prepare($query_pengumpulan);
$stmt->bind_param("ii", $id_tugas, $id_pengguna);
$stmt->execute();
$result_pengumpulan = $stmt->get_result();
$pengumpulan = $result_pengumpulan->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>aa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>

<body>

    <h1>Detail Tugas</h1>

    <div>
        <h3><?= htmlspecialchars($tugas['judul_tugas']); ?></h3>
        <p><strong>Deadline:</strong> <?= htmlspecialchars($tugas['dateline']); ?></p>
        <p><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($tugas['deskripsi'])); ?></p>
    </div>

    <hr>

    <?php if (!$pengumpulan) { ?>
        <!-- Form Pengumpulan Tugas -->
        <h4>Unggah Tugas</h4>
        <form action="upload_tugas_individu.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_tugas" value="<?= $tugas['id_tugas']; ?>">
            <input type="file" name="file_tugas" required>
            <button type="submit">Kirim</button>
        </form>
    <?php } else { ?>
        <!-- Status Tugas -->
        <h4>Status Pengumpulan</h4>
        <p><strong>Nilai:</strong> <?= htmlspecialchars($pengumpulan['nilai'] ?? 'Belum dinilai'); ?></p>
        <p><strong>Catatan Guru:</strong> <?= nl2br(htmlspecialchars($pengumpulan['catatan_guru'] ?? 'Tidak ada catatan')); ?></p>
    <?php } ?>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>