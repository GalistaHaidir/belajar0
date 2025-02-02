<?php
include 'koneksi.php';

// Handle Create
if (isset($_POST['create'])) {
    $nama_ujian = $_POST['nama_ujian'];
    $waktu = $_POST['waktu'];
    $nilai_minimal = $_POST['nilai_minimal'];
    $peraturan = $_POST['peraturan'];

    $sql = "INSERT INTO tbl_pengaturan (nama_ujian, waktu, nilai_minimal, peraturan) VALUES ('$nama_ujian', '$waktu', '$nilai_minimal', '$peraturan')";
    $koneksi->query($sql);
    header("Location: peraturan.php");
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $nama_ujian = $_POST['nama_ujian'];
    $waktu = $_POST['waktu'];
    $nilai_minimal = $_POST['nilai_minimal'];
    $peraturan = $_POST['peraturan'];

    $sql = "UPDATE tbl_pengaturan SET nama_ujian='$nama_ujian', waktu='$waktu', nilai_minimal='$nilai_minimal', peraturan='$peraturan' WHERE id='$id'";
    $koneksi->query($sql);
    header("Location: peraturan.php");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM tbl_pengaturan WHERE id='$id'";
    $koneksi->query($sql);
    header("Location: peraturan.php");
}

// Fetch Data
$result = $koneksi->query("SELECT * FROM tbl_pengaturan");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Peraturan Ujian</title>
</head>
<body>
    <h1>Peraturan Ujian</h1>

    <!-- Form Create -->
    <h2>Tambah Peraturan</h2>
    <form method="POST" action="">
        <label>Nama Ujian:</label><br>
        <input type="text" name="nama_ujian" required><br>
        <label>Waktu (menit):</label><br>
        <input type="number" name="waktu" required><br>
        <label>Nilai Minimal:</label><br>
        <input type="number" name="nilai_minimal" required><br>
        <label>peraturan:</label><br>
        <textarea name="peraturan" required></textarea><br>
        <button type="submit" name="create">Tambah</button>
    </form>

    <!-- Table Read -->
    <h2>Daftar Peraturan</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nama Ujian</th>
            <th>Waktu</th>
            <th>Nilai Minimal</th>
            <th>peraturan</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['nama_ujian'] ?></td>
            <td><?= $row['waktu'] ?> menit</td>
            <td><?= $row['nilai_minimal'] ?></td>
            <td><?= $row['peraturan'] ?></td>
            <td>
                <a href="peraturan.php?edit=<?= $row['id'] ?>">Edit</a>
                <a href="peraturan.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Form Update -->
    <?php if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $editResult = $koneksi->query("SELECT * FROM tbl_pengaturan WHERE id='$id'");
        $editData = $editResult->fetch_assoc();
    ?>
    <h2>Edit Peraturan</h2>
    <form method="POST" action="">
        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
        <label>Nama Ujian:</label><br>
        <input type="text" name="nama_ujian" value="<?= $editData['nama_ujian'] ?>" required><br>
        <label>Waktu (menit):</label><br>
        <input type="number" name="waktu" value="<?= $editData['waktu'] ?>" required><br>
        <label>Nilai Minimal:</label><br>
        <input type="number" name="nilai_minimal" value="<?= $editData['nilai_minimal'] ?>" required><br>
        <label>peraturan:</label><br>
        <textarea name="peraturan" required><?= $editData['peraturan'] ?></textarea><br>
        <button type="submit" name="update">Update</button>
    </form>
    <?php } ?>
</body>
</html>