<?php
require 'koneksi.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

$id_content = $_GET['id_content'] ?? null;
if (!$id_content) {
    echo "ID konten tidak ditemukan.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pengumpulan = intval($_POST['id_pengumpulan']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');
    $status_acc = $_POST['status_acc'] ?? null;

    // Cek nilai, jika kosong maka gunakan NULL
    $nilai = $_POST['nilai'];
    $nilai_sql = ($nilai === '' || !is_numeric($nilai)) ? 'NULL' : intval($nilai);

    // Bangun query manual karena bind_param tidak bisa handle NULL dengan baik
    $set_status = $status_acc !== null ? ", status_acc = '" . mysqli_real_escape_string($koneksi, $status_acc) . "'" : '';

    $sql = "
        UPDATE pengumpulan_tugas
        SET nilai = $nilai_sql, catatan = '$catatan' $set_status
        WHERE id_pengumpulan = $id_pengumpulan
    ";

    $update = mysqli_query($koneksi, $sql);

    if ($update) {
        $_SESSION['success_msg'] = "Penilaian dan status berhasil diperbarui.";
        header("Location: lihat_pengumpulan.php?id_content=$id_content");
        exit();
    } else {
        echo "Gagal menyimpan data.";
    }
}

// Ambil info konten
$konten = mysqli_query($koneksi, "SELECT * FROM meeting_contents WHERE id_content = $id_content");
$data_konten = mysqli_fetch_assoc($konten);
if (!$data_konten) {
    echo "Konten tidak ditemukan.";
    exit();
}

// Ambil data pengumpulan
$query = "SELECT pt.*, u.name AS nama_siswa, k.nama_kelompok, ak.peran, mc.deadline
FROM pengumpulan_tugas pt
LEFT JOIN users u ON pt.id_user = u.id_user
LEFT JOIN kelompok k ON pt.id_kelompok = k.id_kelompok
LEFT JOIN anggota_kelompok ak ON ak.id_kelompok = pt.id_kelompok AND ak.id_user = pt.id_user
LEFT JOIN meeting_contents mc ON pt.id_content = mc.id_content
WHERE pt.id_content = $id_content
ORDER BY pt.waktu_kumpul DESC";
$hasil = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pengumpulan | Belajaro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="guru_home.css">
    <style>
        .content {
            background: linear-gradient(135deg, rgb(255, 255, 255), rgb(244, 255, 246));
            font-family: 'Poppins', sans-serif;
        }

        .main-content {
            padding: 20px;
        }

        table tr:hover {
            transform: none !important;
            box-shadow: none !important;
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
                        <div class="row">
                            <div class="col-12">

                                <!-- Header -->
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <h1 class="fw-bold mb-2">📤 Pengumpulan Tugas</h1>
                                        <nav aria-label="breadcrumb">
                                            <ol class="breadcrumb">
                                                <li class="breadcrumb-item">
                                                    <a href="kelola_konten.php?id_meeting=<?= $data_konten['id_meeting'] ?>">Konten</a>
                                                </li>
                                                <li class="breadcrumb-item active">Pengumpulan</li>
                                            </ol>
                                        </nav>
                                    </div>
                                </div>

                                <!-- Notifikasi -->
                                <?php if (isset($_SESSION['success_msg'])): ?>
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        <?= $_SESSION['success_msg'];
                                        unset($_SESSION['success_msg']); ?>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    </div>
                                <?php endif; ?>

                                <!-- Tabel Pengumpulan -->
                                <div class="card bg-white mb-4">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-borderless align-middle">
                                                <thead class="table-light text-uppercase small">
                                                    <tr>
                                                        <th>Nama</th>
                                                        <th>File</th>
                                                        <th>Jawaban</th>
                                                        <th>Waktu Pengumpulan</th>
                                                        <th>Keterangan</th>
                                                        <th>Deadline</th>
                                                        <th>Penilaian</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (mysqli_num_rows($hasil) === 0): ?>
                                                        <tr>
                                                            <td colspan="5" class="text-center text-muted py-5">
                                                                <div class="py-3">
                                                                    <i class="bi bi-inbox-fill display-5 text-secondary mb-3"></i>
                                                                    <p class="fw-semibold mb-0">Belum ada pengumpulan</p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                                            <tr class="border-bottom">
                                                                <td>
                                                                    <span class="fw-semibold d-block"><?= htmlspecialchars($row['nama_siswa']) ?></span>
                                                                    <?php if (!empty($row['nama_kelompok'])): ?>
                                                                        <small class="text-muted d-block">Kelompok: <?= htmlspecialchars($row['nama_kelompok']) ?></small>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($row['peran'])): ?>
                                                                        <small class="text-muted d-block">Peran: <?= htmlspecialchars($row['peran']) ?></small>
                                                                    <?php endif; ?>
                                                                </td>

                                                                <td>
                                                                    <?php if (!empty($row['file_path'])): ?>
                                                                        <a href="../uploads/<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="badge text-bg-primary text-decoration-none px-3 py-2">
                                                                            <i class="bi bi-file-earmark-text me-1"></i> Lihat File
                                                                        </a>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary-subtle text-secondary">Tidak ada file</span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td class="small">
                                                                    <?= !empty($row['jawaban']) ? nl2br(htmlspecialchars($row['jawaban'])) : '<span class="text-muted fst-italic">Tidak ada jawaban</span>' ?>
                                                                </td>
                                                                <td class="text-muted small"><?= $row['waktu_kumpul'] ?></td>
                                                                <td class="small">
                                                                    <?php if (!empty($row['deadline'])): ?>
                                                                        <?php if (strtotime($row['waktu_kumpul']) > strtotime($row['deadline'])): ?>
                                                                            <span class="badge bg-danger-subtle text-danger"><i class="bi bi-exclamation-circle me-1"></i> Terlambat</span>
                                                                        <?php else: ?>
                                                                            <span class="badge bg-success-subtle text-success"><i class="bi bi-check-circle me-1"></i> Tepat waktu</span>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <span class="text-muted fst-italic">Tidak ada deadline</span>
                                                                    <?php endif; ?>
                                                                </td>

                                                                <td class="text-muted small">
                                                                    <?php if (!empty($row['deadline'])): ?>
                                                                        <div><i class="bi bi-hourglass-split me-1"></i> Deadline: <strong><?= $row['deadline'] ?></strong></div>
                                                                    <?php endif; ?>
                                                                </td>

                                                                <td>
                                                                    <form method="POST" action="lihat_pengumpulan.php?id_content=<?= $id_content ?>" class="d-flex flex-column gap-2">
                                                                        <input type="hidden" name="id_pengumpulan" value="<?= $row['id_pengumpulan'] ?>">
                                                                        <textarea name="catatan" class="form-control form-control-sm" placeholder="Catatan atau komentar..." rows="2"><?= htmlspecialchars($row['catatan']) ?></textarea>
                                                                        <input type="number" name="nilai" class="form-control form-control-sm" placeholder="Nilai (0-100)" max="100" min="0" value="<?= htmlspecialchars($row['nilai']) ?>">

                                                                        <?php if (!empty($data_konten['id_project']) && $row['id_kelompok']): ?>
                                                                            <div class="d-flex gap-2 mt-2">
                                                                                <button type="submit" name="status_acc" value="approved" class="btn btn-success btn-sm rounded-pill px-3">
                                                                                    <i class="bi bi-check2-circle me-1"></i> ACC
                                                                                </button>
                                                                                <button type="submit" name="status_acc" value="rejected" class="btn btn-danger btn-sm rounded-pill px-3">
                                                                                    <i class="bi bi-x-circle me-1"></i> Tolak
                                                                                </button>
                                                                            </div>
                                                                            <div class="text-muted small mt-1">
                                                                                Status:
                                                                                <?php
                                                                                if ($row['status_acc'] === 'approved') echo "<span class='text-success fw-semibold'>Disetujui</span>";
                                                                                elseif ($row['status_acc'] === 'rejected') echo "<span class='text-danger fw-semibold'>Ditolak</span>";
                                                                                else echo "<span class='text-muted fst-italic'>Belum diperiksa</span>";
                                                                                ?>
                                                                            </div>
                                                                        <?php else: ?>
                                                                            <button type="submit" name="simpan" class="btn btn-outline-success rounded-pill shadow-sm mt-2">
                                                                                <i class="bi bi-check-circle me-1"></i> Simpan
                                                                            </button>
                                                                        <?php endif; ?>
                                                                    </form>
                                                                </td>
                                                            </tr>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>