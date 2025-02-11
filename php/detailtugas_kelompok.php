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





$id_proyek = $_GET['id_proyek'];
$id_pengguna = $_SESSION['id_pengguna'];

// Ambil ID kelompok pengguna dari tabel akses_kelompok
$query_kelompok = $koneksi->prepare("
    SELECT id_kelompok FROM akses_kelompok WHERE id_pengguna = ?
");
$query_kelompok->bind_param("i", $id_pengguna);
$query_kelompok->execute();
$result_kelompok = $query_kelompok->get_result();
$data_kelompok = $result_kelompok->fetch_assoc();

if (!$data_kelompok) {
    die("Anda belum tergabung dalam kelompok mana pun.");
}

$id_kelompok = $data_kelompok['id_kelompok'];

// Ambil detail tugas berdasarkan proyek
$query_tugas = $koneksi->prepare("
    SELECT * FROM tugas WHERE id_proyek = ?
");
$query_tugas->bind_param("i", $id_proyek);
$query_tugas->execute();
$result_tugas = $query_tugas->get_result();
$tugas = $result_tugas->fetch_assoc();

if (!$tugas) {
    die("Tugas tidak ditemukan untuk proyek ini.");
}

// Ambil informasi pengumpulan tugas berdasarkan id_kelompok
$query_pengumpulan = $koneksi->prepare("
    SELECT * FROM pengumpulan_tugas WHERE id_kelompok = ? AND id_tugas = ?
");
$query_pengumpulan->bind_param("ii", $id_kelompok, $tugas['id_tugas']);
$query_pengumpulan->execute();
$result_pengumpulan = $query_pengumpulan->get_result();
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

    <div class="container mt-4">
        <h1><?= htmlspecialchars($tugas['judul_tugas']); ?></h1>
        <p><strong>Deadline:</strong> <?= htmlspecialchars($tugas['dateline']); ?></p>
        <p><strong>Deskripsi:</strong> <?= nl2br(htmlspecialchars($tugas['deskripsi'])); ?></p>

        <?php if (!$pengumpulan): ?>
            <!-- Jika belum ada pengumpulan, tampilkan form upload -->
            <form action="upload_tugas_kelompok.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_tugas" value="<?= $tugas['id_tugas']; ?>">
                <input type="file" name="file_tugas" class="form-control mb-3" required>
                <button type="submit" class="btn btn-primary">Kumpulkan Tugas</button>
            </form>
        <?php else: ?>
            <!-- Jika tugas sudah dikumpulkan, tampilkan statusnya -->
            <div class="card mt-3">
                <div class="card-body">
                    <h5 class="card-title">Status Pengumpulan</h5>
                    <p><strong>Catatan Guru:</strong> <?= nl2br(htmlspecialchars($pengumpulan['catatan_guru'] ?? 'Belum ada catatan.')); ?></p>
                    <p><strong>Nilai:</strong> <?= htmlspecialchars($pengumpulan['nilai'] ?? 'Belum dinilai.'); ?></p>
                    <p><strong>File yang dikumpulkan:</strong> <a href="uploads/<?= htmlspecialchars($pengumpulan['file_tugas']); ?>" target="_blank"><?= htmlspecialchars($pengumpulan['file_tugas']); ?></a></p>
                </div>
            </div>
        <?php endif; ?>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</body>

</html>