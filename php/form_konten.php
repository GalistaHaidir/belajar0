<?php
session_start();
include 'koneksi.php'; // Menghubungkan ke database

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Cek apakah role user adalah guru atau admin
if ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Ambil parameter dari URL
$mode = $_GET['mode'] ?? 'add'; // 'add' atau 'edit'
$id_meeting = $_GET['id_meeting'] ?? ''; // ID pertemuan
$type = $_GET['type'] ?? ''; // Jenis konten: quiz, materi, link, dsb
$id_content = $_GET['id'] ?? ''; // ID konten (jika edit)

// Inisialisasi variabel default untuk form
$title = '';
$description = '';
$link_url = '';
$file_path = '';
$deadline = '';
$id_project = '';
$id_kelompok = '';
$duration_minutes = ''; // Hanya untuk quiz

// Ambil data konten dari database jika dalam mode edit
if ($mode === 'edit' && $id_content) {
    $result = mysqli_query($koneksi, "SELECT * FROM meeting_contents WHERE id_content = '$id_content'");
    if ($data = mysqli_fetch_assoc($result)) {
        // Isi nilai default form dengan data dari DB
        $title = $data['title'];
        $description = $data['description'];
        $link_url = $data['link_url'];
        $file_path = $data['file_path'];
        $deadline = $data['deadline'];
        $id_project = $data['id_project'] ?? '';
        $id_kelompok = $data['id_kelompok'] ?? '';
        $duration_minutes = $data['duration_minutes'] ?? '';
    }
}

// Ambil id_courses berdasarkan id_meeting
$id_course = null;
$query_course = mysqli_query($koneksi, "SELECT id_courses FROM course_meetings WHERE id_meeting = '$id_meeting'");
if ($row = mysqli_fetch_assoc($query_course)) {
    $id_course = $row['id_courses'];
}

// Ambil daftar proyek (PjBL) yang terkait dengan course
$project_options = [];
if ($id_course) {
    $query_projects = mysqli_query($koneksi, "SELECT id_project, nama_project FROM pjbl_project WHERE id_courses = '$id_course'");
    while ($proj = mysqli_fetch_assoc($query_projects)) {
        $project_options[] = $proj;
    }
}

// Ambil daftar kelompok yang ikut dalam project (untuk akses forum kelompok)
$kelompok_options = [];
if ($id_course) {
    $query_kelompok = mysqli_query($koneksi, "
        SELECT k.id_kelompok, k.nama_kelompok 
        FROM kelompok k 
        JOIN pjbl_project p ON k.id_project = p.id_project 
        WHERE p.id_courses = '$id_course'
    ");
    while ($kel = mysqli_fetch_assoc($query_kelompok)) {
        $kelompok_options[] = $kel;
    }
}

// Proses form submit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil semua data dari form
    $title = $_POST['title'];
    $description = $_POST['description'];
    $id_meeting = $_POST['id_meeting'];
    $type = $_POST['type'];
    $deadline = $_POST['deadline'] ?? null;
    $id_project = $_POST['id_project'] ?? null;
    $duration_minutes = $_POST['duration_minutes'] ?? null;
    $link_url = $_POST['link_url'] ?? '';
    $file_name = '';
    $id_kelompok = $_POST['id_kelompok'] ?? null;
    if ($id_kelompok === '') $id_kelompok = null;

    // Upload file jika ada dan bukan tipe 'link'
    if (!empty($_FILES['file']['name']) && $type !== 'link') {
        $original_name = basename($_FILES['file']['name']);
        $safe_name = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $original_name);

        $upload_dir = 'uploads/' . $type;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true); // Buat folder jika belum ada
        }

        $target_path = $upload_dir . '/' . $safe_name;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
            $file_name = $type . '/' . $safe_name; // Simpan path relatif
        }
    }

    // Jika mode tambah
    if ($mode === 'add') {
        if ($type === 'quiz') {
            // Tambah konten quiz
            $stmt = mysqli_prepare($koneksi, "INSERT INTO meeting_contents 
                (id_meeting, type, title, description, file_path, link_url, deadline, duration_minutes) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issssssi", $id_meeting, $type, $title, $description, $file_name, $link_url, $deadline, $duration_minutes);
        } else {
            // Tambah konten biasa
            $stmt = mysqli_prepare($koneksi, "INSERT INTO meeting_contents 
                (id_meeting, type, title, description, file_path, link_url, deadline, id_project, id_kelompok) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issssssii", $id_meeting, $type, $title, $description, $file_name, $link_url, $deadline, $id_project, $id_kelompok);
        }
    } else { // Mode edit
        // Ambil file lama jika tidak upload baru
        if (empty($file_name)) {
            $query = mysqli_query($koneksi, "SELECT file_path FROM meeting_contents WHERE id_content = '$id_content'");
            $row = mysqli_fetch_assoc($query);
            $file_name = $row['file_path'] ?? '';
        }

        if ($type === 'quiz') {
            // Update konten quiz
            $stmt = mysqli_prepare($koneksi, "UPDATE meeting_contents 
                SET title=?, description=?, file_path=?, link_url=?, deadline=?, duration_minutes=? 
                WHERE id_content=?");
            mysqli_stmt_bind_param($stmt, "ssssssi", $title, $description, $file_name, $link_url, $deadline, $duration_minutes, $id_content);
        } else {
            // Update konten biasa
            $stmt = mysqli_prepare($koneksi, "UPDATE meeting_contents 
                SET title=?, description=?, file_path=?, link_url=?, deadline=?, id_project=?, id_kelompok=? 
                WHERE id_content=?");
            mysqli_stmt_bind_param($stmt, "ssssssii", $title, $description, $file_name, $link_url, $deadline, $id_project, $id_kelompok, $id_content);
        }
    }

    // Eksekusi statement
    if (mysqli_stmt_execute($stmt)) {
        // Redirect kembali ke halaman pengelolaan konten pertemuan
        header("Location: kelola_konten.php?id_meeting=$id_meeting");
        exit;
    } else {
        echo "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Konten | Belajaro</title>
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
                        <!-- Header Halaman -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h1 class="fw-bold mb-2" style="text-transform: capitalize;">📘 Kelola <?= htmlspecialchars($type); ?></h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="kelola_konten.php?id_meeting=<?= $id_meeting ?>">Konten</a></li>
                                        <li class="breadcrumb-item active">Kelola Konten</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Form Tambah/Edit Konten -->
                        <div class="card border-0 shadow-sm rounded-0 mb-4">
                            <div class="card-header bg-white border-0 fw-semibold d-flex align-items-center">
                                <i class="bi bi-pencil-square me-2 text-primary"></i>
                                <?= $mode === 'edit' ? "Edit Konten" : "Tambah Konten Baru" ?>
                            </div>

                            <div class="card-body pt-3">
                                <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                    <input type="hidden" name="id_meeting" value="<?= $id_meeting ?>">
                                    <input type="hidden" name="type" value="<?= $type ?>">

                                    <?php if ($type === 'forum'): ?>
                                        <!-- Form Forum -->
                                        <div class="mb-3">
                                            <label for="title" class="form-label fw-semibold">Judul Forum</label>
                                            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($title ?? '') ?>" required>
                                            <div class="invalid-feedback">Judul forum harus diisi.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-semibold">Deskripsi Forum</label>
                                            <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>
                                        </div>

                                        <?php
                                        // Ambil daftar kelompok (hanya untuk konten forum, dan hanya jika kursus punya proyek aktif)
                                        $kelompok_options = [];
                                        if ($id_course) {
                                            $q_kelompok = mysqli_query($koneksi, "
        SELECT k.id_kelompok, k.nama_kelompok 
        FROM kelompok k 
        JOIN pjbl_project p ON k.id_project = p.id_project 
        WHERE p.id_courses = '$id_course'
    ");
                                            while ($kel = mysqli_fetch_assoc($q_kelompok)) {
                                                $kelompok_options[] = $kel;
                                            }
                                        }
                                        ?>

                                        <div class="mb-3">
                                            <label for="id_kelompok" class="form-label fw-semibold">👥 Akses Forum Kelompok</label>
                                            <select name="id_kelompok" id="id_kelompok" class="form-select">
                                                <option value="">-- Umum (Seluruh Kelas) --</option>
                                                <?php foreach ($kelompok_options as $kel): ?>
                                                    <option value="<?= $kel['id_kelompok'] ?>" <?= isset($data['id_kelompok']) && $data['id_kelompok'] == $kel['id_kelompok'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($kel['nama_kelompok']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small class="text-muted">Forum hanya dapat diakses oleh kelompok yang dipilih. Biarkan kosong untuk forum umum seluruh siswa.</small>
                                        </div>


                                    <?php elseif ($type === 'quiz'): ?>
                                        <!-- Form Quiz -->
                                        <div class="mb-3">
                                            <label for="title" class="form-label fw-semibold">Judul Quiz</label>
                                            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($title ?? '') ?>" required>
                                            <div class="invalid-feedback">Judul quiz harus diisi.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-semibold">Deskripsi Quiz</label>
                                            <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label for="duration_minutes" class="form-label fw-semibold">⏱️ Durasi (menit)</label>
                                            <input type="number" name="duration_minutes" id="duration_minutes" class="form-control"
                                                value="<?= htmlspecialchars($duration_minutes) ?>" min="1" placeholder="Contoh: 30" required>
                                            <div class="invalid-feedback">Durasi quiz harus diisi.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="deadline" class="form-label fw-semibold">⏰ Deadline Quiz</label>
                                            <input type="datetime-local" name="deadline" id="deadline" class="form-control"
                                                value="<?= isset($deadline) ? date('Y-m-d\TH:i', strtotime($deadline)) : '' ?>" required>
                                        </div>

                                    <?php elseif ($type === 'absen'): ?>
                                        <!-- Form Absensi -->
                                        <div class="mb-3">
                                            <label for="title" class="form-label fw-semibold">Judul Absensi</label>
                                            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($title ?? '') ?>" required>
                                            <div class="invalid-feedback">Judul absensi harus diisi.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-semibold">Deskripsi atau Instruksi</label>
                                            <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>
                                            <small class="text-muted">Misal: "Silakan isi absensi sebelum pukul 10.00 WIB."</small>
                                        </div>

                                        <div class="mb-3">
                                            <label for="deadline" class="form-label fw-semibold">⏰ Batas Waktu Absensi</label>
                                            <input type="datetime-local" name="deadline" id="deadline" class="form-control"
                                                value="<?= isset($deadline) ? date('Y-m-d\TH:i', strtotime($deadline)) : '' ?>" required>
                                        </div>

                                    <?php else: ?>
                                        <!-- Form Konten Umum -->
                                        <div class="mb-3">
                                            <label for="title" class="form-label fw-semibold">Judul Konten</label>
                                            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($title ?? '') ?>" required>
                                            <div class="invalid-feedback">Judul harus diisi.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label fw-semibold">Deskripsi</label>
                                            <textarea name="description" id="description" class="form-control" rows="3"><?= htmlspecialchars($description ?? '') ?></textarea>
                                        </div>

                                        <?php if ($type === 'link'): ?>
                                            <div class="mb-3">
                                                <label for="link_url" class="form-label fw-semibold">🌐 Link Eksternal</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="bi bi-link-45deg"></i></span>
                                                    <input type="url" name="link_url" id="link_url" class="form-control"
                                                        value="<?= htmlspecialchars($link_url ?? '') ?>" placeholder="https://contoh.com" required>
                                                </div>
                                            </div>

                                        <?php elseif ($type === 'tugas' || $type === 'pjbl'): ?>
                                            <div class="mb-3">
                                                <label for="file" class="form-label fw-semibold">📎 Upload File <?= $type === 'pjbl' ? 'Panduan' : '(Opsional)' ?></label>
                                                <input type="file" name="file" id="file" class="form-control">
                                                <?php if (!empty($file_path)) : ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">File sebelumnya:</small>
                                                        <div class="d-flex align-items-center mt-1">
                                                            <i class="bi bi-file-earmark me-2"></i>
                                                            <span><?= htmlspecialchars($file_path) ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label for="deadline" class="form-label fw-semibold">⏰ Deadline Pengumpulan</label>
                                                <input type="datetime-local" name="deadline" id="deadline" class="form-control"
                                                    value="<?= isset($deadline) ? date('Y-m-d\TH:i', strtotime($deadline)) : '' ?>" required>
                                            </div>

                                            <div class="mb-3">
                                                <label for="link_url" class="form-label fw-semibold">🔗 Tambahan Link (Opsional)</label>
                                                <input type="url" name="link_url" id="link_url" class="form-control"
                                                    value="<?= htmlspecialchars($link_url ?? '') ?>" placeholder="https://contoh.com">
                                            </div>

                                            <?php if ($type === 'pjbl'): ?>
                                                <div class="mb-3">
                                                    <label for="id_project" class="form-label fw-semibold">📁 Pilih Proyek PjBL</label>
                                                    <select name="id_project" id="id_project" class="form-select" required>
                                                        <option value="">-- Pilih Proyek --</option>
                                                        <?php foreach ($project_options as $proj): ?>
                                                            <option value="<?= $proj['id_project'] ?>" <?= isset($id_project) && $id_project == $proj['id_project'] ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($proj['nama_project']) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="invalid-feedback">Silakan pilih proyek PjBL.</div>
                                                </div>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <div class="mb-3">
                                                <label for="file" class="form-label fw-semibold">📎 Upload File (Opsional)</label>
                                                <input type="file" name="file" id="file" class="form-control">
                                                <?php if (!empty($file_path)) : ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">File sebelumnya:</small>
                                                        <div class="d-flex align-items-center mt-1">
                                                            <i class="bi bi-file-earmark me-2"></i>
                                                            <span><?= htmlspecialchars($file_path) ?></span>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="mb-3">
                                                <label for="link_url" class="form-label fw-semibold">🔗 Tambahan Link (Opsional)</label>
                                                <input type="url" name="link_url" id="link_url" class="form-control"
                                                    value="<?= htmlspecialchars($link_url ?? '') ?>" placeholder="https://contoh.com">
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <!-- Tombol Aksi -->
                                    <div class="d-flex gap-2 mt-4">
                                        <button type="submit" class="btn btn-outline-success rounded-pill shadow-sm px-4">
                                            <i class="bi bi-save2-fill me-1"></i><?= $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Konten' ?>
                                        </button>
                                        <a href="kelola_konten.php?id_meeting=<?= $id_meeting ?>" class="btn btn-outline-secondary rounded-pill shadow-sm px-4">
                                            <i class="bi bi-arrow-left-circle me-1"></i>Kembali
                                        </a>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>

</html>