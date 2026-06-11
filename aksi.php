<?php
include "db.php";

$query = mysqli_query($koneksi, "SELECT * FROM tbl_responden");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Data Responden</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h3>Data Responden</h3>

<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>No</th>
            <th>Nama Responden</th>
            <th>Program Studi</th>
            <th>Angkatan</th>
            <th>Status Kuesioner</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
        <?php $no = 1; while($row = mysqli_fetch_assoc($query)) { ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= $row['nama_responden']; ?></td>
            <td><?= $row['program_studi']; ?></td>
            <td><?= $row['angkatan']; ?></td>
            <td><?= $row['status_kuesioner']; ?></td>

            <td>
                <a class="btn btn-warning btn-sm"
                   href="edit.php?id=<?= $row['id']; ?>">
                   Edit
                </a>

                <a class="btn btn-danger btn-sm"
                   href="hapus.php?id=<?= $row['id']; ?>"
                   onclick="return confirm('Yakin ingin menghapus data ini?')">
                   Hapus
                </a>
            </td>
        </tr>
        <?php } ?>
    </tbody>
</table>

</body>
</html>