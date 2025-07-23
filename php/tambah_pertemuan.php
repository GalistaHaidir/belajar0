<?php
session_start();
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'guru' && $_SESSION['role'] !== 'admin')) {
    header("Location: login.php");
    exit();
}

// Cek apakah id_course tersedia
if (!isset($_GET['id_courses'])) {
    echo "Kursus tidak ditemukan.";
    exit();
}


$id_courses = $_GET['id_courses'];
$isEdit = isset($_GET['edit']);
$editData = null;

// Ambil data jika mode edit
if ($isEdit) {
    $id_meeting = intval($_GET['edit']);
    $getData = mysqli_query($koneksi, "SELECT * FROM course_meetings WHERE id_meeting = $id_meeting");

    if ($getData && mysqli_num_rows($getData) > 0) {
        $editData = mysqli_fetch_assoc($getData);
    } else {
        echo "Data tidak ditemukan.";
        exit;
    }
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $meeting_number = mysqli_real_escape_string($koneksi, $_POST['meeting_number']);
    $title = mysqli_real_escape_string($koneksi, $_POST['title']);
    $description = mysqli_real_escape_string($koneksi, $_POST['description']);

    if (isset($_POST['edit_id'])) {
        // Mode edit
        $edit_id = intval($_POST['edit_id']);
        $query = "UPDATE course_meetings 
                  SET meeting_number='$meeting_number', title='$title', description='$description' 
                  WHERE id_meeting = $edit_id";
    } else {
        // Mode tambah
        $query = "INSERT INTO course_meetings (id_courses, meeting_number, title, description)
                  VALUES ('$id_courses', '$meeting_number', '$title', '$description')";
    }

    $result = mysqli_query($koneksi, $query);

    if ($result) {
        header("Location: kelola_pertemuan.php?id_courses=$id_courses");
        exit();
    } else {
        echo "Gagal menyimpan pertemuan.";
    }
}

?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Pertemuan | Belajaro</title>
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

        .input-group-text {
            background-color: #f8f9fa;
        }

        .form-control,
        .input-group-text {
            border-radius: 0.5rem !important;
        }

        textarea.form-control {
            min-height: 120px;
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
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h1 class="fw-bold mb-2">
                                    ➕📅 Kelola Pertemuan
                                </h1>
                                <nav aria-label="breadcrumb">
                                    <ol class="breadcrumb mb-0">
                                        <li class="breadcrumb-item"><a href="daftar_kursus.php">Daftar Kursus</a></li>
                                        <li class="breadcrumb-item"><a href="kelola_pertemuan.php?id_courses=<?= $id_courses ?>">Pertemuan</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Kelola Pertemuan</li>
                                    </ol>
                                </nav>
                            </div>
                        </div>

                        <!-- Form -->
                        <div class="card shadow-sm border-0 rounded-0">
                            <div class="card-body pt-4">
                                <form method="POST" class="needs-validation" novalidate>
                                    <input type="hidden" name="id_courses" value="<?= $id_courses ?>">
                                    <?php if ($isEdit): ?>
                                        <input type="hidden" name="edit_id" value="<?= $editData['id_meeting'] ?>">
                                    <?php endif; ?>

                                    <!-- Nomor Pertemuan -->
                                    <div class="mb-4">
                                        <label for="meeting_number" class="form-label fw-semibold">
                                            <i class="bi bi-hash text-primary me-1"></i>
                                            Pertemuan Ke-
                                        </label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-123"></i></span>
                                            <input type="number" class="form-control" id="meeting_number" name="meeting_number" required
                                                value="<?= $isEdit ? htmlspecialchars($editData['meeting_number']) : '' ?>">
                                            <div class="invalid-feedback">Harap isi nomor pertemuan</div>
                                        </div>
                                    </div>

                                    <!-- Judul Pertemuan -->
                                    <div class="mb-4">
                                        <label for="title" class="form-label fw-semibold">
                                            <i class="bi bi-card-heading text-primary me-1"></i>
                                            Judul Pertemuan
                                        </label>
                                        <div class="input-group has-validation">
                                            <span class="input-group-text"><i class="bi bi-text-left"></i></span>
                                            <input type="text" class="form-control" id="title" name="title" required
                                                value="<?= $isEdit ? htmlspecialchars($editData['title']) : '' ?>">
                                            <div class="invalid-feedback">Harap isi judul pertemuan</div>
                                        </div>
                                    </div>

                                    <!-- Deskripsi -->
                                    <div class="mb-4">
                                        <label for="description" class="form-label fw-semibold">
                                            <i class="bi bi-text-paragraph text-primary me-1"></i>
                                            Deskripsi Pertemuan
                                        </label>
                                        <textarea class="form-control" id="description" name="description" rows="5" required><?= $isEdit ? htmlspecialchars($editData['description']) : '' ?></textarea>
                                        <div class="invalid-feedback">Harap isi deskripsi pertemuan</div>
                                    </div>

                                    <!-- Tombol Aksi -->
                                    <div class="d-flex justify-content-end gap-2 mt-4">
                                        <button type="submit" class="btn btn-outline-primary rounded-pill shadow-sm px-4">
                                            <i class="bi bi-save me-1"></i>
                                            <?= $isEdit ? 'Update' : 'Simpan' ?> Pertemuan
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
    <script>
        // Validasi form
        (function() {
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