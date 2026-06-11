<?php
session_start();
include "db.php";
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}
include "layout/header.php";

$data = mysqli_query($koneksi, "SELECT * FROM tbl_responden ORDER BY id_responden");
?>

<div class="card card-modern">

    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>
            Data Responden
        </h5>
        <a href="tambah_responden.php" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i> Tambah Responden
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Nama Responden</th>
                    <th>Program Studi</th>
                    <th>Angkatan</th>
                    <th>Status Kuesioner</th>
                    <th class="text-center" width="160">Tindakan</th>
                </tr>
            </thead>
            <tbody>
            <?php $no = 1; while ($r = mysqli_fetch_assoc($data)): ?>
            <?php
                $id_resp = $r['id_responden'];
                $cekPairwise = mysqli_num_rows(mysqli_query($koneksi,
                    "SELECT * FROM tbl_pairwise_responden 
                     WHERE id_responden='$id_resp' LIMIT 1"
                ));
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($r['nama_responden']) ?></td>
                <td><?= htmlspecialchars($r['program_studi']) ?></td>
                <td><?= htmlspecialchars($r['angkatan']) ?></td>
                <td>
                    <?php
                    $status = $r['status_kuesioner'];
                    if (strpos($status, 'Konsisten (') === 0 && strpos($status, 'Tidak') === false):
                    ?>
                        <span class="badge bg-success"><?= htmlspecialchars($status) ?></span>
                    <?php elseif (strpos($status, 'Tidak Konsisten') !== false): ?>
                        <span class="badge bg-danger"><?= htmlspecialchars($status) ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary"><?= htmlspecialchars($status) ?></span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if ($cekPairwise == 0): ?>
                        <a href="input_pairwise.php?id=<?= $id_resp ?>"
                           class="btn btn-primary btn-sm"
                           title="Input Data Pairwise AHP">
                            <i class="fas fa-keyboard"></i>
                        </a>
                    <?php else: ?>
                        <a href="hitung_cr.php?id=<?= $id_resp ?>"
                           class="btn btn-info btn-sm"
                           title="Hitung Ulang CR">
                            <i class="fas fa-calculator"></i>
                        </a>
                    <?php endif; ?>
                    <a href="edit_responden.php?id=<?= $id_resp ?>"
                       class="btn btn-warning btn-sm"
                       title="Edit">
                        <i class="fas fa-pen"></i>
                    </a>
                    <a href="hapus_responden.php?id=<?= $id_resp ?>"
                       class="btn btn-danger btn-sm"
                       title="Hapus"
                       onclick="return confirm('Yakin ingin menghapus data ini?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<?php include "layout/footer.php"; ?>