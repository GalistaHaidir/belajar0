<?php
include 'koneksi.php';

// Handle Create
if (isset($_POST['create'])) {
    $pertanyaan = $_POST['pertanyaan'];
    $gambar = $_FILES['gambar']['name'];
    $a = $_POST['a'];
    $b = $_POST['b'];
    $c = $_POST['c'];
    $d = $_POST['d'];
    $kunci_jawaban = $_POST['kunci_jawaban'];

    // Upload gambar jika ada
    if ($gambar) {
        $target_dir = "gambar_soal/";
        $target_file = $target_dir . basename($gambar);
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
    }

    $sql = "INSERT INTO tbl_soal (pertanyaan, gambar, a, b, c, d, kunci_jawaban) VALUES ('$pertanyaan', '$gambar', '$a', '$b', '$c', '$d', '$kunci_jawaban')";
    $koneksi->query($sql);
    header("Location: soal.php");
}

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $pertanyaan = $_POST['pertanyaan'];
    $gambar = $_FILES['gambar']['name'];
    $a = $_POST['a'];
    $b = $_POST['b'];
    $c = $_POST['c'];
    $d = $_POST['d'];
    $kunci_jawaban = $_POST['kunci_jawaban'];

    // Upload gambar jika ada
    if ($gambar) {
        $target_dir = "gambar_soal/";
        $target_file = $target_dir . basename($gambar);
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_file);
        $sql = "UPDATE tbl_soal SET pertanyaan='$pertanyaan', gambar='$gambar', a='$a', b='$b', c='$c', d='$d', kunci_jawaban='$kunci_jawaban' WHERE id='$id'";
    } else {
        $sql = "UPDATE tbl_soal SET pertanyaan='$pertanyaan', a='$a', b='$b', c='$c', d='$d', kunci_jawaban='$kunci_jawaban' WHERE id='$id'";
    }

    $koneksi->query($sql);
    header("Location: soal.php");
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM tbl_soal WHERE id='$id'";
    $koneksi->query($sql);
    header("Location: soal.php");
}

// Fetch Data
$result = $koneksi->query("SELECT * FROM tbl_soal");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Soal</title>
</head>

<body>
    <h1>Daftar Soal</h1>

    <!-- Form Create -->
    <h2>Tambah Soal</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <label>Pertanyaan:</label><br>
        <textarea name="pertanyaan" required></textarea><br>
        <label>Gambar (Opsional):</label><br>
        <input type="file" name="gambar"><br>
        <label>Opsi A:</label><br>
        <input type="text" name="a" required><br>
        <label>Opsi B:</label><br>
        <input type="text" name="b" required><br>
        <label>Opsi C:</label><br>
        <input type="text" name="c" required><br>
        <label>Opsi D:</label><br>
        <input type="text" name="d" required><br>
        <label>Kunci Jawaban:</label><br>
        <select name="kunci_jawaban" required>
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="C">C</option>
            <option value="D">D</option>
        </select><br>
        <button type="submit" name="create">Tambah</button>
    </form>

    <!-- Table Read -->
    <h2>Daftar Soal</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Pertanyaan</th>
            <th>Gambar</th>
            <th>Opsi A</th>
            <th>Opsi B</th>
            <th>Opsi C</th>
            <th>Opsi D</th>
            <th>Kunci Jawaban</th>
            <th>Aksi</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['pertanyaan'] ?></td>
                <td><?= $row['gambar'] ? "<img src='gambar_soal/{$row['gambar']}' width='100'>" : "Tidak ada" ?></td>
                <td><?= $row['a'] ?></td>
                <td><?= $row['b'] ?></td>
                <td><?= $row['c'] ?></td>
                <td><?= $row['d'] ?></td>
                <td><?= $row['kunci_jawaban'] ?></td>
                <td>
                    <a href="soal.php?edit=<?= $row['id'] ?>">Edit</a>
                    <a href="soal.php?delete=<?= $row['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>
        <?php } ?>
    </table>

    <!-- Form Update -->
    <?php if (isset($_GET['edit'])) {
        $id = $_GET['edit'];
        $editResult = $koneksi->query("SELECT * FROM tbl_soal WHERE id='$id'");
        $editData = $editResult->fetch_assoc();
    ?>
        <h2>Edit Soal</h2>
        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <label>Pertanyaan:</label><br>
            <textarea name="pertanyaan" required><?= $editData['pertanyaan'] ?></textarea><br>
            <label>Gambar (Opsional):</label><br>
            <input type="file" name="gambar"><br>
            <label>Opsi A:</label><br>
            <input type="text" name="a" value="<?= $editData['a'] ?>" required><br>
            <label>Opsi B:</label><br>
            <input type="text" name="b" value="<?= $editData['b'] ?>" required><br>
            <label>Opsi C:</label><br>
            <input type="text" name="c" value="<?= $editData['c'] ?>" required><br>
            <label>Opsi D:</label><br>
            <input type="text" name="d" value="<?= $editData['d'] ?>" required><br>
            <label>Kunci Jawaban:</label><br>
            <select name="kunci_jawaban" required>
                <option value="A" <?= $editData['kunci_jawaban'] == 'A' ? 'selected' : '' ?>>A</option>
                <option value="B" <?= $editData['kunci_jawaban'] == 'B' ? 'selected' : '' ?>>B</option>
                <option value="C" <?= $editData['kunci_jawaban'] == 'C' ? 'selected' : '' ?>>C</option>
                <option value="D" <?= $editData['kunci_jawaban'] == 'D' ? 'selected' : '' ?>>D</option>
            </select><br>
            <button type="submit" name="update">Update</button>
        </form>
    <?php } ?>
</body>

</html>