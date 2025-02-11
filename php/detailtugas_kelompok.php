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


$id_proyek = $_GET['id_proyek'] ?? 0;
$id_pengguna = $_SESSION['id_pengguna'] ?? 0;

// Pastikan user login
if (!$id_pengguna) {
    die("Silakan login terlebih dahulu.");
}

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

// Ambil semua tugas berdasarkan proyek
$query_tugas = $koneksi->prepare("
    SELECT * FROM tugas WHERE id_proyek = ? ORDER BY dateline ASC
");
$query_tugas->bind_param("i", $id_proyek);
$query_tugas->execute();
$result_tugas = $query_tugas->get_result();
$tugas_list = $result_tugas->fetch_all(MYSQLI_ASSOC);

// Jika tidak ada tugas, tampilkan pesan
if (!$tugas_list) {
    die("Tidak ada tugas untuk proyek ini.");
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
    <title>Detail Tugas Kelompok</title>
    <style>
        .card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <div class="main">
            <?php include 'navbar.php'; ?>
            <main class="content px-3 py-4">
                <a class="btn btn-outline-danger d-flex align-items-center rounded-pill mb-3" onclick="navigateToPage()">
                    <i class="bi bi-arrow-left-circle-fill me-2"></i>
                    <span>Kembali</span>
                </a>

                <div class="container">
                    <h1 class="mb-4 text-center text-primary">📚 Daftar Tugas dalam Proyek</h1>

                    <div class="row">
                        <?php foreach ($tugas_list as $tugas): ?>
                            <?php
                            // Cek apakah tugas sudah dikumpulkan
                            $query_pengumpulan = $koneksi->prepare("
                    SELECT * FROM pengumpulan_tugas WHERE id_kelompok = ? AND id_tugas = ?
                ");
                            $query_pengumpulan->bind_param("ii", $id_kelompok, $tugas['id_tugas']);
                            $query_pengumpulan->execute();
                            $result_pengumpulan = $query_pengumpulan->get_result();
                            $pengumpulan = $result_pengumpulan->fetch_assoc();

                            // Warna kartu berdasarkan status
                            $card_class = $pengumpulan ? 'border-success' : 'border-warning';
                            $status_badge = $pengumpulan
                                ? '<span class="badge bg-success">✅ Sudah Dikumpulkan</span>'
                                : '<span class="badge bg-warning text-dark">⚠️ Belum Dikumpulkan</span>';
                            ?>

                            <div class="col-md-12 mb-3">
                                <div class="card shadow-lg <?= $card_class; ?>">
                                    <div class="card-body">
                                        <h4 class="fw-bold"><?= htmlspecialchars($tugas['judul_tugas']); ?></h4>
                                        <p><strong>📅 Deadline:</strong>
                                            <span class="badge bg-danger"><?= htmlspecialchars($tugas['dateline']); ?></span>
                                        </p>
                                        <p><strong>📌 Deskripsi:</strong></p>
                                        <div class="alert alert-light"><?= nl2br(htmlspecialchars($tugas['deskripsi'])); ?></div>

                                        <?= $status_badge; ?>

                                        <hr>

                                        <?php if (!$pengumpulan): ?>
                                            <!-- Form Pengumpulan Tugas -->
                                            <h5 class="text-success mt-3">📤 Unggah Tugas</h5>
                                            <form action="upload_tugas_kelompok.php" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="id_proyek" value="<?= $id_proyek; ?>">

                                                <input type="hidden" name="id_tugas" value="<?= $tugas['id_tugas']; ?>">
                                                <div class="mb-3">
                                                    <input type="file" name="file_tugas" class="form-control" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="bi bi-upload"></i> Kumpulkan Tugas
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <!-- Status Pengumpulan -->
                                            <h5 class="text-warning mt-3">📑 Status Pengumpulan</h5>
                                            <table class="table table-bordered mt-3">
                                                <tr>
                                                    <th class="bg-light">📝 Catatan Guru</th>
                                                    <td><?= nl2br(htmlspecialchars($pengumpulan['catatan_guru'] ?? 'Tidak ada catatan')); ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light">📊 Nilai</th>
                                                    <td><?= htmlspecialchars($pengumpulan['nilai'] ?? 'Belum dinilai'); ?></td>
                                                </tr>
                                                <tr>
                                                    <th class="bg-light">📁 File yang Dikumpulkan</th>
                                                    <td>
                                                        <a href="<?= htmlspecialchars($pengumpulan['file_tugas']); ?>" target="_blank" class="btn btn-success btn-sm">
                                                            <i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars(basename($pengumpulan['file_tugas'])); ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
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