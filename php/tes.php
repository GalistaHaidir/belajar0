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

///////////////////////////////////

$id_soal = "";
$id_peraturan = "";
$pertanyaan = "";
$a = "";
$b = "";
$c = "";
$d = "";
$kunci_jawaban = "";

$sukses = "";
$error = "";

// Cek apakah ada operasi yang diminta (edit atau delete)
if (isset($_GET['op'])) {
    $op = $_GET['op'];
} else {
    $op = "";
}

// Handle Delete
if ($op == 'delete') {
    $id_soal = $_GET['id_soal'];
    $sql = "DELETE FROM tbl_soal WHERE id_soal = '$id_soal'";
    $q = mysqli_query($koneksi, $sql);
    if ($q) {
        $sukses = "Berhasil menghapus soal.";
    } else {
        $error = "Gagal menghapus soal.";
    }
}

// Handle Edit
if ($op == 'edit') {
    $id_soal = $_GET['id_soal'];
    $sql = "SELECT * FROM tbl_soal WHERE id_soal = '$id_soal'";
    $q = mysqli_query($koneksi, $sql);
    $r = mysqli_fetch_array($q);
    if ($r) {
        $id_peraturan = $r['id_peraturan'];
        $pertanyaan = $r['pertanyaan'];
        $a = $r['a'];
        $b = $r['b'];
        $c = $r['c'];
        $d = $r['d'];
        $kunci_jawaban = $r['kunci_jawaban'];
    } else {
        $error = "Data tidak ditemukan.";
    }
}

// Handle Create atau Update
if (isset($_POST['submit'])) {
    $id_peraturan = $_POST['id_peraturan'];
    $pertanyaan = $_POST['pertanyaan'];
    $a = $_POST['a'];
    $b = $_POST['b'];
    $c = $_POST['c'];
    $d = $_POST['d'];
    $kunci_jawaban = $_POST['kunci_jawaban'];

    if ($id_peraturan && $pertanyaan && $a && $b && $c && $d && $kunci_jawaban) {
        if ($op == 'edit') { // Update
            $sql = "UPDATE tbl_soal SET id_peraturan = '$id_peraturan', pertanyaan = '$pertanyaan', a = '$a', b = '$b', c = '$c', d = '$d', kunci_jawaban = '$kunci_jawaban' WHERE id_soal = '$id_soal'";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Soal berhasil diperbarui.";
            } else {
                $error = "Gagal memperbarui soal.";
            }
        } else { // Insert
            $sql = "INSERT INTO tbl_soal (id_peraturan, pertanyaan, a, b, c, d, kunci_jawaban) VALUES ('$id_peraturan', '$pertanyaan', '$a', '$b', '$c', '$d', '$kunci_jawaban')";
            $q = mysqli_query($koneksi, $sql);
            if ($q) {
                $sukses = "Berhasil menambahkan soal baru.";
            } else {
                $error = "Gagal menambahkan soal.";
            }
        }
    } else {
        $error = "Silakan isi semua data.";
    }
}

// Ambil data soal
$query = "SELECT tbl_soal.*, tbl_pengaturan.nama_ujian FROM tbl_soal JOIN tbl_pengaturan ON tbl_soal.id_peraturan = tbl_pengaturan.id_peraturan";
$result = $koneksi->query($query);


?>

<!DOCTYPE html>
<html lang="id">

<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="guru_home.css">
    <title>Kelola Soal</title>
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
                <h1>CRUD Soal Ujian</h1>

                <!-- Form Tambah Soal -->
                <h2>Tambah Soal</h2>
                <form method="POST" action="">
                    <label for="nama_ujian">Nama Ujian:</label>
                    <select name="id_peraturan" id="nama_ujian" required>
                        <?php
                        // Ambil data nama_ujian dari tbl_pengaturan
                        $result_peraturan = $koneksi->query("SELECT id_peraturan, nama_ujian FROM tbl_pengaturan");
                        while ($row_peraturan = $result_peraturan->fetch_assoc()) {
                            echo "<option value='" . $row_peraturan['id_peraturan'] . "'>" . $row_peraturan['nama_ujian'] . "</option>";
                        }
                        ?>
                    </select>
                    <label for="pertanyaan">Pertanyaan:</label>
                    <textarea name="pertanyaan" id="pertanyaan" required></textarea>
                    <label for="a">Pilihan A:</label>
                    <input type="text" name="a" id="a" required>
                    <label for="b">Pilihan B:</label>
                    <input type="text" name="b" id="b" required>
                    <label for="c">Pilihan C:</label>
                    <input type="text" name="c" id="c" required>
                    <label for="d">Pilihan D:</label>
                    <input type="text" name="d" id="d" required>
                    <label for="kunci_jawaban">Kunci Jawaban:</label>
                    <input type="text" name="kunci_jawaban" id="kunci_jawaban" required>
                    <button type="submit" name="submit">Tambah Soal</button>
                </form>

                <h2>Daftar Soal</h2>
                <table border="1">
                    <tr>
                        <th>Nama Ujian</th>
                        <th>Pertanyaan</th>
                        <th>A</th>
                        <th>B</th>
                        <th>C</th>
                        <th>D</th>
                        <th>Kunci Jawaban</th>
                        <th>Aksi</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['nama_ujian']; ?></td>
                            <td><?php echo $row['pertanyaan']; ?></td>
                            <td><?php echo $row['a']; ?></td>
                            <td><?php echo $row['b']; ?></td>
                            <td><?php echo $row['c']; ?></td>
                            <td><?php echo $row['d']; ?></td>
                            <td><?php echo $row['kunci_jawaban']; ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $row['id_soal']; ?>">Edit</a> |
                                <a href="delete.php?id=<?php echo $row['id_soal']; ?>">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>

</body>

</html>


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