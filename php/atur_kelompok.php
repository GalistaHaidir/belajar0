<?php
session_start(); // Memulai session PHP
require 'koneksi.php'; // Menghubungkan ke file koneksi database

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Jika belum login, redirect ke login.php
    exit();
}

// Cek apakah role user adalah guru atau admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php"); // Jika bukan guru/admin, redirect ke login.php
    exit();
}

// Validasi ID project dari parameter GET
if (!isset($_GET['id_project']) || !is_numeric($_GET['id_project'])) {
    die("ID content tidak valid."); // Jika id_project tidak valid, hentikan eksekusi
}
$id_project = (int) $_GET['id_project']; // Konversi ke integer

// Ambil id_meeting dan id_courses dari tabel relasi meeting
$q = mysqli_query($koneksi, "
    SELECT cm.id_meeting, cm.id_courses
    FROM meeting_contents mc
    JOIN course_meetings cm ON mc.id_meeting = cm.id_meeting
    WHERE mc.id_project = $id_project
");
$data = mysqli_fetch_assoc($q);
$id_meeting = $data['id_meeting'] ?? 0; // Jika tidak ada, default 0

// Ambil id_courses dari tabel pjbl_project jika belum ditemukan
$q = mysqli_query($koneksi, "
    SELECT id_courses 
    FROM pjbl_project 
    WHERE id_project = $id_project
");
$data = mysqli_fetch_assoc($q);
$id_courses = $data['id_courses'] ?? 0; // Jika tidak ada, default 0

// Proses tambah kelompok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['aksi'] === 'tambah_kelompok') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kelompok']); // Sanitasi input
    $query = "INSERT INTO kelompok (id_project, nama_kelompok) VALUES ('$id_project', '$nama')";
    mysqli_query($koneksi, $query); // Jalankan query
    header("Location: atur_kelompok.php?id_project=$id_project"); // Redirect kembali
    exit;
}

// Proses edit kelompok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['aksi'] === 'edit_kelompok') {
    $id_kelompok = intval($_POST['id_kelompok']); // Ambil ID kelompok
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_kelompok']); // Sanitasi input
    $query = "UPDATE kelompok SET nama_kelompok = '$nama' WHERE id_kelompok = $id_kelompok";
    mysqli_query($koneksi, $query); // Jalankan query update
    header("Location: atur_kelompok.php?id_project=$id_project"); // Redirect kembali
    exit;
}

// Proses hapus kelompok berdasarkan parameter GET
if (isset($_GET['hapus_kelompok'])) {
    $id_kelompok = intval($_GET['hapus_kelompok']); // Ambil ID kelompok
    mysqli_query($koneksi, "DELETE FROM kelompok WHERE id_kelompok = $id_kelompok"); // Hapus dari DB
    header("Location: atur_kelompok.php?id_project=$id_project"); // Redirect kembali
    exit;
}

// Proses tambah anggota ke dalam kelompok
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['aksi'] === 'tambah_anggota') {
    $id_kelompok = intval($_POST['id_kelompok']);
    $id_user = intval($_POST['id_user']);
    $peran = mysqli_real_escape_string($koneksi, $_POST['peran']);

    // Cek apakah user sudah menjadi anggota kelompok tersebut
    $cek = mysqli_query($koneksi, "SELECT COUNT(*) AS jml FROM anggota_kelompok WHERE id_kelompok = $id_kelompok AND id_user = $id_user");
    $row = mysqli_fetch_assoc($cek);

    if ($row['jml'] == 0) {
        // Jika belum, tambahkan ke dalam anggota_kelompok
        mysqli_query($koneksi, "INSERT INTO anggota_kelompok (id_kelompok, id_user, peran) VALUES ($id_kelompok, $id_user, '$peran')");
    }
    header("Location: atur_kelompok.php?id_project=$id_project");
    exit;
}

// Proses hapus anggota dari kelompok berdasarkan parameter GET
if (isset($_GET['hapus_anggota'])) {
    $id = intval($_GET['hapus_anggota']); // Ambil ID anggota
    mysqli_query($koneksi, "DELETE FROM anggota_kelompok WHERE id_anggota = $id"); // Hapus dari DB
    header("Location: atur_kelompok.php?id_project=$id_project"); // Redirect kembali
    exit;
}

// Proses ubah peran anggota
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['aksi'] === 'ubah_peran') {
    $id_anggota = intval($_POST['id_anggota']);
    $peran_baru = mysqli_real_escape_string($koneksi, $_POST['peran']);

    $query = "UPDATE anggota_kelompok SET peran = '$peran_baru' WHERE id_anggota = $id_anggota";
    mysqli_query($koneksi, $query);
    header("Location: atur_kelompok.php?id_project=$id_project");
    exit;
}

// Ambil semua data kelompok berdasarkan id_project
$daftar_kelompok = [];
$q_kelompok = mysqli_query($koneksi, "SELECT * FROM kelompok WHERE id_project = '$id_project'");
while ($row = mysqli_fetch_assoc($q_kelompok)) {
    $daftar_kelompok[] = $row; // Simpan data ke array
}

// Ambil semua data user dengan role 'siswa'
$siswa = [];
$q_siswa = mysqli_query($koneksi, "SELECT id_user, name FROM users WHERE role = 'siswa'");
while ($row = mysqli_fetch_assoc($q_siswa)) {
    $siswa[] = $row; // Simpan data ke array
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Konten Pertemuan | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">

    <style>
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            font-family: 'Poppins', sans-serif;
        }

        /* Styling Container Utama */
        .main-content {
            padding: 20px;
        }

        .card {
            border-radius: 0.75rem;
            transition: all 0.3s ease;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <?php include 'sidebar.php'; ?>
        <!-- Overlay hanya untuk mobile -->
        <div class="mobile-sidebar-overlay d-md-none" id="mobile-sidebar-overlay"></div>
        <div class="main">
            <?php include 'akun-info.php'; ?> <!-- Ini selalu muncul (desktop) -->
            <?php include 'navbar.php'; ?>
            <main class="content px-4 py-4">
                <div class="main-content">
                    <div class="container-fluid">
                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h1 class="fw-bold mb-0">👨‍👩‍👧‍👦 Manajemen Kelompok</h1>
                            <button class="btn btn-outline-primary rounded-pill shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#modalTambah">
                                <i class="bi bi-plus-circle me-1"></i> Tambah Kelompok
                            </button>
                        </div>

                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="kelola_pjbl.php?id_courses=<?= $id_courses ?>">Kembali ke PjBL</a></li>
                                <li class="breadcrumb-item active">Manajemen Kelompok</li>
                            </ol>
                        </nav>

                        <!-- Accordion -->
                        <div class="accordion" id="accordionKelompok">
                            <?php foreach ($daftar_kelompok as $index => $k): ?>
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="heading<?= $index ?>">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>" aria-expanded="false" aria-controls="collapse<?= $index ?>">
                                            <?= htmlspecialchars($k['nama_kelompok']) ?>
                                        </button>
                                    </h2>
                                    <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#accordionKelompok">
                                        <div class="accordion-body">

                                            <!-- TOMBOL EDIT & HAPUS -->
                                            <div class="mb-3 d-flex gap-2">
                                                <button class="btn btn-warning text-white rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $k['id_kelompok'] ?>">
                                                    <i class="bi bi-pencil-square me-1"></i> Edit
                                                </button>
                                                <a href="?hapus_kelompok=<?= $k['id_kelompok'] ?>&id_project=<?= $id_project ?>"
                                                    class="btn btn-outline-danger rounded-pill px-3 shadow-sm"
                                                    onclick="return confirm('Hapus kelompok ini?')">
                                                    <i class="bi bi-trash3 me-1"></i> Hapus
                                                </a>
                                            </div>

                                            <!-- TABEL ANGGOTA -->
                                            <table class="table table-bordered">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Nama Siswa</th>
                                                        <th>Peran</th>
                                                        <th>Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $anggotaList = [];
                                                    $id_k = intval($k['id_kelompok']);
                                                    $q_anggota = mysqli_query($koneksi, "
            SELECT a.id_anggota, u.name, a.peran 
            FROM anggota_kelompok a 
            JOIN users u ON a.id_user = u.id_user 
            WHERE a.id_kelompok = $id_k
        ");
                                                    while ($a = mysqli_fetch_assoc($q_anggota)) {
                                                        $anggotaList[] = $a;
                                                    }

                                                    if (!$anggotaList): ?>
                                                        <tr>
                                                            <td colspan="3"><em>Belum ada anggota</em></td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($anggotaList as $a): ?>
                                                            <tr>
                                                                <td><?= htmlspecialchars($a['name']) ?></td>
                                                                <td>
                                                                    <form method="POST" class="d-flex gap-2 align-items-center">
                                                                        <input type="hidden" name="aksi" value="ubah_peran">
                                                                        <input type="hidden" name="id_anggota" value="<?= $a['id_anggota'] ?>">
                                                                        <input type="text" name="peran" class="form-control form-control-sm" value="<?= htmlspecialchars($a['peran']) ?>" required style="width: 130px;">
                                                                        <button type="submit" class="btn btn-sm btn-primary rounded-pill">
                                                                            <i class="bi bi-check-circle"></i>
                                                                        </button>
                                                                    </form>
                                                                </td>
                                                                <td>
                                                                    <a href="?hapus_anggota=<?= $a['id_anggota'] ?>&id_project=<?= $id_project ?>"
                                                                        class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                                                        onclick="return confirm('Hapus anggota ini?')">
                                                                        <i class="bi bi-person-dash me-1"></i> Hapus
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>

                                            <!-- Tombol Tambah Anggota -->
                                            <button class="btn btn-info text-white rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAnggota<?= $k['id_kelompok'] ?>">
                                                <i class="bi bi-person-plus me-1"></i> Tambah Anggota
                                            </button>

                                        </div>
                                    </div>
                                </div>

                                <!-- MODAL EDIT KELOMPOK -->
                                <div class="modal fade" id="modalEdit<?= $k['id_kelompok'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" class="modal-content">
                                            <input type="hidden" name="aksi" value="edit_kelompok">
                                            <input type="hidden" name="id_kelompok" value="<?= $k['id_kelompok'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Kelompok</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label>Nama Kelompok:</label>
                                                <input type="text" class="form-control" name="nama_kelompok" value="<?= htmlspecialchars($k['nama_kelompok']) ?>" required>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                                    <i class="bi bi-save me-1"></i> Simpan
                                                </button>
                                                <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">
                                                    <i class="bi bi-x-circle me-1"></i> Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- MODAL TAMBAH ANGGOTA -->
                                <div class="modal fade" id="modalAnggota<?= $k['id_kelompok'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" class="modal-content">
                                            <input type="hidden" name="aksi" value="tambah_anggota">
                                            <input type="hidden" name="id_kelompok" value="<?= $k['id_kelompok'] ?>">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Tambah Anggota</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <label>Pilih Siswa:</label>
                                                <select class="form-select mb-3" name="id_user" required>
                                                    <option value="">-- Pilih Siswa --</option>
                                                    <?php foreach ($siswa as $s): ?>
                                                        <option value="<?= $s['id_user'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <label>Peran dalam Kelompok:</label>
                                                <input type="text" class="form-control" name="peran" placeholder="Contoh: Ketua, Anggota, Frontend" required>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-info text-white rounded-pill px-4 shadow-sm">
                                                    <i class="bi bi-person-plus me-1"></i> Tambah
                                                </button>
                                                <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">
                                                    <i class="bi bi-x-circle me-1"></i> Batal
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        </div>

                        <!-- MODAL TAMBAH KELOMPOK -->
                        <div class="modal fade" id="modalTambah" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST" class="modal-content">
                                    <input type="hidden" name="aksi" value="tambah_kelompok">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Tambah Kelompok</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label>Nama Kelompok:</label>
                                        <input type="text" name="nama_kelompok" class="form-control" required>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                                            <i class="bi bi-plus-circle me-1"></i> Tambah
                                        </button>
                                        <button type="button" class="btn btn-light border rounded-pill px-3" data-bs-dismiss="modal">
                                            <i class="bi bi-x-circle me-1"></i> Batal
                                        </button>
                                    </div>
                                </form>
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
</body>

</html>