<?php
include 'koneksi.php';

$id_tugas = "";
$tanggal = "";
$deskripsi_tugas = "";

$sukses = "";
$error = "";

// Operasi Delete
if (isset($_GET['op']) && $_GET['op'] == 'delete') {
    $id_tugas = $_GET['id_tugas'];
    $sql1 = "DELETE FROM tugas WHERE id_tugas = '$id_tugas'";
    $q1 = mysqli_query($koneksi, $sql1);
    if ($q1) {
        $sukses = "Berhasil menghapus data";
    } else {
        $error = "Gagal menghapus data";
    }
}

// Operasi Edit
if (isset($_GET['op']) && $_GET['op'] == 'edit') {
    $id_tugas = $_GET['id_tugas'];
    $sql1 = "SELECT * FROM tugas WHERE id_tugas = '$id_tugas'";
    $q1 = mysqli_query($koneksi, $sql1);
    $r1 = mysqli_fetch_array($q1);
    $tanggal = $r1['tanggal'];
    $deskripsi_tugas = $r1['deskripsi_tugas'];

    if ($tanggal == '') {
        $error = "Data tidak ditemukan";
    }
}

// Operasi Create/Update
if (isset($_POST['submit'])) {
    $tanggal = $_POST['tanggal'];
    $deskripsi_tugas = $_POST['deskripsi_tugas'];

    if ($tanggal && $deskripsi_tugas) {
        if (isset($_GET['op']) && $_GET['op'] == 'edit') {
            $sql1 = "UPDATE tugas SET tanggal = '$tanggal', deskripsi_tugas = '$deskripsi_tugas' WHERE id_tugas = '$id_tugas'";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Data berhasil diperbarui";
            } else {
                $error = "Data gagal diperbarui";
            }
        } else {
            $sql1 = "INSERT INTO tugas (tanggal, deskripsi_tugas) VALUES ('$tanggal', '$deskripsi_tugas')";
            $q1 = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Berhasil menambahkan data baru";
            } else {
                $error = "Gagal menambahkan data baru";
            }
        }
    } else {
        $error = "Silakan isi semua data!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Memo Tugas</title>
</head>

<body>
    <h1>CRUD Memo Tugas</h1>

    <!-- Notifikasi -->
    <?php if ($sukses) {
        echo "<p style='color: green;'>$sukses</p>";
    } ?>
    <?php if ($error) {
        echo "<p style='color: red;'>$error</p>";
    } ?>

    <!-- Form Input -->
    <form method="POST">
        <label for="tanggal">Tanggal:</label><br>
        <input type="date" id="tanggal" name="tanggal" value="<?php echo $tanggal; ?>" required><br><br>
        <label for="deskripsi_tugas">Deskripsi Tugas:</label><br>
        <textarea id="deskripsi_tugas" name="deskripsi_tugas" required><?php echo $deskripsi_tugas; ?></textarea><br><br>
        <input type="submit" name="submit" value="Simpan">
    </form>

    <hr>

    <!-- Tabel Data -->
    <h2>Daftar Memo Tugas</h2>
    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Deskripsi Tugas</th>
            <th>Aksi</th>
        </tr>
        <?php
        $sql2 = "SELECT * FROM tugas ORDER BY tanggal DESC";
        $q2 = mysqli_query($koneksi, $sql2);
        $no = 1;
        while ($r2 = mysqli_fetch_array($q2)) {
            $id_tugas = $r2['id_tugas'];
            $tanggal = $r2['tanggal'];
            $deskripsi_tugas = $r2['deskripsi_tugas'];
        ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $tanggal; ?></td>
                <td><?php echo $deskripsi_tugas; ?></td>
                <td>
                    <a href="?op=edit&id_tugas=<?php echo $id_tugas; ?>">Edit</a> |
                    <a href="?op=delete&id_tugas=<?php echo $id_tugas; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</body>

</html>