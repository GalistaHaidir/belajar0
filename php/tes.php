<?php
include 'koneksi.php';

// Handle Create
if (isset($_POST['create'])) {
    $id_pengguna = $_POST['id_pengguna'];
    $benar = $_POST['benar'];
    $salah = $_POST['salah'];
    $kosong = $_POST['kosong'];
    $nilai = $_POST['nilai'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];

    $sql = "INSERT INTO tbl_nilai (id_pengguna, benar, salah, kosong, nilai, tanggal, status) VALUES ('$id_pengguna', '$benar', '$salah', '$kosong', '$nilai', '$tanggal', '$status')";
    $koneksi->query($sql);
    header("Location: nilai.php");
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $id_pengguna = $_POST['id_pengguna'];
    $benar = $_POST['benar'];
    $salah = $_POST['salah'];
    $kosong = $_POST['kosong'];
    $nilai = $_POST['nilai'];
    $tanggal = $_POST['tanggal'];
    $status = $_POST['status'];

    $sql = "UPDATE tbl_nilai SET id_pengguna='$id_pengguna', benar='$benar', salah='$salah', kosong='$kosong', nilai='$nilai', tanggal='$tanggal', status='$status' WHERE id='$id'";
    $koneksi->query($sql);
    header("Location: nilai.php");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM tbl_nilai WHERE id='$id'";
    $koneksi->query($sql);
    header("Location: nilai.php");
}

// Fetch Data
$result = $koneksi->query("SELECT * FROM tbl_nilai");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Nilai</title>
</head>

<body>
    <h1>Daftar Nilai</h1>

    <!-- Form Create -->
    <h2>Tambah Nilai</h2>
    <form method="POST" action="">
        <label>ID Pengguna:</label><br>
        <input type="number" name="id_pengguna" required><br>
        <label>Benar:</label><br>
        <input type="number" name="benar" required><br>
        <label>Salah:</label><br>
        <input type="number" name="salah" required><br>
        <label>Kosong:</label><br>
        <input type="number" name="kosong" required><br>
        <label>Nilai:</label><br>
        <input type="number" name="nilai" required><br>
        <label>Tanggal:</label><br>
        <input type="date" name="tanggal" required><br>
        <label>Status:</label><br>
        <input type="text" name="status" required><br>
        <button type="submit" name="create">Tambah</button>
    </form>

    <!-- Table Read -->
    <h2>Daftar Nilai</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>ID Pengguna</th>
            <th>Benar</th>
            <th>Salah</th>
            <th>Kosong</th>
            <th>Nilai</th>
            <th>Tanggal</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['id_pengguna'] ?></td>
                <td><?= $row['benar'] ?></td>
                <td><?= $row['salah'] ?></td>
                <td><?= $row['kosong'] ?></td>
                <td><?= $row['nilai'] ?></td>
                <td><?= $row['tanggal'] ?></td>
                <td><?= $row['status'] ?></td>
                <td>
                    <a href="nilai.php?edit=<?= $row['id'] ?>">Edit</a>
                    <a href="nilai.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Form Update -->
    <?php if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $editResult = $koneksi->query("SELECT * FROM tbl_nilai WHERE id='$id'");
        $editData = $editResult->fetch_assoc();
    ?>
        <h2>Edit Nilai</h2>
        <form method="POST" action="">
            <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <label>ID Pengguna:</label><br>
            <input type="number" name="id_pengguna" value="<?= $editData['id_pengguna'] ?>" required><br>
            <label>Benar:</label><br>
            <input type="number" name="benar" value="<?= $editData['benar'] ?>" required><br>
            <label>Salah:</label><br>
            <input type="number" name="salah" value="<?= $editData['salah'] ?>" required><br>
            <label>Kosong:</label><br>
            <input type="number" name="kosong" value="<?= $editData['kosong'] ?>" required><br>
            <label>Nilai:</label><br>
            <input type="number" name="nilai" value="<?= $editData['nilai'] ?>" required><br>
            <label>Tanggal:</label><br>
            <input type="date" name="tanggal" value="<?= $editData['tanggal'] ?>" required><br>
            <label>Status:</label><br>
            <input type="text" name="status" value="<?= $editData['status'] ?>" required><br>
            <button type="submit" name="update">Update</button>
        </form>
    <?php } ?>
</body>

</html>