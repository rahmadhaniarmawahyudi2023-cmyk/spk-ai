<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

/* =========================
   TAMBAH DATA
========================= */
if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $kode = $_POST['kode_alternatif'];
    $nama = $_POST['nama_alternatif'];

    mysqli_query($koneksi, "
        INSERT INTO tbl_alternatif (kode_alternatif, nama_alternatif)
        VALUES ('$kode', '$nama')
    ");

    header("Location: alternatif.php");
    exit;
}

/* =========================
   UPDATE DATA
========================= */
if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $id   = $_POST['id_alternatif'];
    $kode = $_POST['kode_alternatif'];
    $nama = $_POST['nama_alternatif'];

    mysqli_query($koneksi, "
        UPDATE tbl_alternatif
        SET kode_alternatif='$kode', nama_alternatif='$nama'
        WHERE id_alternatif='$id'
    ");

    header("Location: alternatif.php");
    exit;
}

/* =========================
   HAPUS DATA
========================= */
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM tbl_alternatif WHERE id_alternatif='$id'");
    header("Location: alternatif.php");
    exit;
}

/* =========================
   AMBIL DATA
========================= */
$data = mysqli_query(
    $koneksi,
    "SELECT * FROM tbl_alternatif ORDER BY id_alternatif ASC"
);

include "layout/header.php";
?>

<div class="card card-modern mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-robot me-2"></i> Data Alternatif Platform AI
        </h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
            <i class="fas fa-plus me-1"></i> Tambah Alternatif
        </button>
    </div>
    <div class="card-body">

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Kode Alternatif</th>
                        <th>Nama Platform AI</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($data)):
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['kode_alternatif']) ?></td>
                    <td><?= htmlspecialchars($row['nama_alternatif']) ?></td>
                    <td>
                        <button class="btn btn-warning btn-action"
                            data-bs-toggle="modal"
                            data-bs-target="#edit<?= $row['id_alternatif'] ?>">
                            <i class="fas fa-pen"></i>
                        </button>
                        <a href="?hapus=<?= $row['id_alternatif'] ?>"
                            class="btn btn-danger btn-action"
                            onclick="return confirm('Hapus data ini?')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>

                <!-- MODAL EDIT -->
                <div class="modal fade" id="edit<?= $row['id_alternatif'] ?>">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id_alternatif" value="<?= $row['id_alternatif'] ?>">

                                <div class="modal-header">
                                    <h5>Edit Alternatif</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label>Kode</label>
                                        <input type="text" name="kode_alternatif"
                                            value="<?= htmlspecialchars($row['kode_alternatif']) ?>"
                                            class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label>Nama Platform</label>
                                        <input type="text" name="nama_alternatif"
                                            value="<?= htmlspecialchars($row['nama_alternatif']) ?>"
                                            class="form-control" required>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-success">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">

                <div class="modal-header">
                    <h5>Tambah Alternatif</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Kode</label>
                        <input type="text" name="kode_alternatif"
                            class="form-control" placeholder="Contoh: A6" required>
                    </div>
                    <div class="mb-3">
                        <label>Nama Platform</label>
                        <input type="text" name="nama_alternatif"
                            class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include "layout/footer.php"; ?>