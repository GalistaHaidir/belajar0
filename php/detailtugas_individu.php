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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
    <title>Detail Tugas Individu</title>
    <style>
        /* Styling body */
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            color: #1B5E20;
            /* Warna hijau tua */
        }

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
                <a class="btn btn-outline-danger"
                    style="border-top-left-radius: 50px; border-bottom-left-radius: 50px; margin-bottom:10px;"
                    onclick="navigateToPage()">
                    <i class="bi bi-backspace-fill"></i>
                    <span>Kembali</span>
                </a>

                <div class="container">
                    <h1 class="mb-4 text-center text-primary">📚 Detail Tugas Individu</h1>

                    <div class="row">
                        <div class="col-md-12">
                            <?php
                            // Cek apakah tugas sudah dikumpulkan
                            $query_pengumpulan = $koneksi->prepare("
                SELECT * FROM pengumpulan_tugas WHERE id_pengguna = ? AND id_tugas = ?
            ");
                            $query_pengumpulan->bind_param("ii", $id_pengguna, $tugas['id_tugas']);
                            $query_pengumpulan->execute();
                            $result_pengumpulan = $query_pengumpulan->get_result();
                            $pengumpulan = $result_pengumpulan->fetch_assoc();

                            // Warna kartu berdasarkan status
                            $card_class = $pengumpulan ? 'border-success' : 'border-warning';
                            $status_badge = $pengumpulan
                                ? '<span class="badge bg-success">✅ Sudah Dikumpulkan</span>'
                                : '<span class="badge bg-warning text-dark">⚠️ Belum Dikumpulkan</span>';
                            ?>

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
                                        <form action="upload_tugas_individu.php" method="POST" enctype="multipart/form-data">
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