<?php

session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

include "layout/header.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$query = mysqli_query($koneksi, "SELECT * FROM tbl_responden WHERE id_responden = $id");

if(mysqli_num_rows($query) == 0){
    echo "<div class='alert alert-danger'>Data responden tidak ditemukan.</div>";
    include "layout/footer.php";
    exit;
}

$data = mysqli_fetch_assoc($query);

if(isset($_POST['update'])){

    $nama_responden = mysqli_real_escape_string($koneksi, $_POST['nama_responden']);
    $program_studi = mysqli_real_escape_string($koneksi, $_POST['program_studi']);
    $angkatan = mysqli_real_escape_string($koneksi, $_POST['angkatan']);
    $status_kuesioner = mysqli_real_escape_string($koneksi, $_POST['status_kuesioner']);

    $update = mysqli_query($koneksi,"
        UPDATE tbl_responden SET
        nama_responden = '$nama_responden',
        program_studi = '$program_studi',
        angkatan = '$angkatan',
        status_kuesioner = '$status_kuesioner'
        WHERE id = $id
    ");

    if($update){
        echo "<script>
                alert('Data berhasil diperbarui');
                window.location='responden.php';
              </script>";
        exit;
    }else{
        echo "<div class='alert alert-danger'>
                Gagal memperbarui data.
              </div>";
    }
}
?>

<div class="card card-modern">

```
<div class="card-header">
    <h4>
        <i class="fas fa-user-edit"></i>
        Edit Responden
    </h4>
</div>

<div class="card-body">

    <form method="POST">

        <div class="mb-3">
            <label class="form-label">
                Nama Responden
            </label>

            <input
                type="text"
                name="nama_responden"
                class="form-control"
                value="<?= htmlspecialchars($data['nama_responden']) ?>"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Program Studi
            </label>

            <input
                type="text"
                name="program_studi"
                class="form-control"
                value="<?= htmlspecialchars($data['program_studi']) ?>"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Angkatan
            </label>

            <input
                type="text"
                name="angkatan"
                class="form-control"
                value="<?= htmlspecialchars($data['angkatan']) ?>"
                required>
        </div>

        <div class="mb-3">
            <label class="form-label">
                Status Kuesioner
            </label>

            <input
                type="text"
                name="status_kuesioner"
                class="form-control"
                value="<?= htmlspecialchars($data['status_kuesioner']) ?>"
                required>
        </div>

        <button
            type="submit"
            name="update"
            class="btn btn-primary">

            <i class="fas fa-save"></i>
            Simpan Perubahan

        </button>

        <a href="responden.php"
           class="btn btn-secondary">

            <i class="fas fa-arrow-left"></i>
            Kembali

        </a>

    </form>

</div>
```

</div>

<?php include "layout/footer.php"; ?>
