<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "db.php";

if(!isset($_SESSION['username'])){
    header("Location: index.php");
    exit;
}

// AMBIL DATA
$alternatif = [];
$qAlt = mysqli_query($koneksi, "SELECT * FROM tbl_alternatif ORDER BY id_alternatif");
while($a = mysqli_fetch_assoc($qAlt)) $alternatif[] = $a;

$kriteria = [];
$qKrit = mysqli_query($koneksi, "SELECT * FROM tbl_kriteria ORDER BY id_kriteria");
while($k = mysqli_fetch_assoc($qKrit)) $kriteria[] = $k;

// AMBIL NILAI YANG SUDAH ADA
$nilaiExisting = [];
$qNilai = mysqli_query($koneksi, "SELECT * FROM tbl_penilaian");
while($n = mysqli_fetch_assoc($qNilai)){
    $nilaiExisting[$n['id_alternatif']][$n['id_kriteria']] = $n['nilai'];
}

// SIMPAN
if(isset($_POST['simpan'])){
    $success = true;
    foreach($alternatif as $a){
        $idAlt = $a['id_alternatif'];
        foreach($kriteria as $k){
            $idKrit = $k['id_kriteria'];
            $nilai  = (float)($_POST['nilai'][$idAlt][$idKrit] ?? 0);

            // Cek sudah ada atau belum
            $cek = mysqli_query($koneksi,
                "SELECT id_penilaian FROM tbl_penilaian
                 WHERE id_alternatif=$idAlt AND id_kriteria=$idKrit"
            );

            if(mysqli_num_rows($cek) > 0){
                $row = mysqli_fetch_assoc($cek);
                $q = mysqli_query($koneksi,
                    "UPDATE tbl_penilaian SET nilai=$nilai
                     WHERE id_penilaian={$row['id_penilaian']}"
                );
            } else {
                $q = mysqli_query($koneksi,
                    "INSERT INTO tbl_penilaian (id_alternatif, id_kriteria, nilai)
                     VALUES ($idAlt, $idKrit, $nilai)"
                );
            }
            if(!$q) $success = false;
        }
    }
    $_SESSION['notif'] = $success
        ? ['type'=>'success', 'msg'=>'✅ Nilai penilaian berhasil disimpan!']
        : ['type'=>'danger',  'msg'=>'❌ Gagal menyimpan: '.mysqli_error($koneksi)];
    header("Location: input_penilaian.php");
    exit;
}

include "layout/header.php";
?>

<div class="container-fluid">

    <h2 class="mb-2">📝 Input Penilaian Alternatif</h2>

    <p class="text-muted mb-4">Input nilai rating tiap platform AI terhadap setiap kriteria (skala 1–5)</p>

    <?php if(isset($_SESSION['notif'])): ?>
    <div class="alert alert-<?= $_SESSION['notif']['type'] ?> alert-dismissible fade show">
        <?= $_SESSION['notif']['msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['notif']); endif; ?>

    <div class="alert alert-info mb-4">
        <strong>Skala Penilaian:</strong>
        <span class="ms-2">1 = Sangat Buruk</span>
        <span class="ms-2">2 = Buruk</span>
        <span class="ms-2">3 = Cukup</span>
        <span class="ms-2">4 = Baik</span>
        <span class="ms-2">5 = Sangat Baik</span>
    </div>

    <div class="card card-modern mb-4">
        <div class="card-header">
            Matriks Penilaian Alternatif
        </div>
        <div class="card-body">

            <form method="post">
                <div class="table-responsive">
                <table class="table table-bordered text-center align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Platform AI</th>
                            <?php foreach($kriteria as $k): ?>
                            <th>
                                <?= htmlspecialchars($k['kode_kriteria']) ?><br>
                                <small class="fw-normal" style="font-size:10px">
                                    <?= htmlspecialchars($k['nama_kriteria']) ?>
                                </small>
                            </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($alternatif as $a): ?>
                        <?php $idAlt = $a['id_alternatif']; ?>
                        <tr>
                            <td class="fw-bold text-start">
                                <?= htmlspecialchars($a['nama_alternatif']) ?>
                            </td>
                            <?php foreach($kriteria as $k): ?>
                            <?php
                            $idKrit      = $k['id_kriteria'];
                            $nilaiSaat   = $nilaiExisting[$idAlt][$idKrit] ?? 0;
                            $isEmpty     = !isset($nilaiExisting[$idAlt][$idKrit]);
                            ?>
                            <td>
                                <select
                                    name="nilai[<?= $idAlt ?>][<?= $idKrit ?>]"
                                    class="form-select form-select-sm text-center <?= $isEmpty ? 'border-danger' : '' ?>"
                                    style="min-width:60px">
                                    <?php for($v = 1; $v <= 5; $v++): ?>
                                    <option value="<?= $v ?>"
                                        <?= ($nilaiSaat == $v) ? 'selected' : '' ?>>
                                        <?= $v ?>
                                    </option>
                                    <?php endfor; ?>
                                </select>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>

                <div class="d-flex align-items-center gap-3 mt-3">
                    <button
                        type="submit"
                        name="simpan"
                        class="btn btn-lg px-4"
                        style="background:linear-gradient(135deg,#5d4037,#3e2723);color:white;"
                        onclick="return confirm('Simpan semua nilai penilaian?')">
                        💾 Simpan Penilaian
                    </button>
                    <a href="perhitungan.php" class="btn btn-outline-secondary btn-lg">
                        📊 Lihat Perhitungan TOPSIS
                    </a>
                    <small class="text-muted">
                        * Kolom merah = belum ada nilai sebelumnya
                    </small>
                </div>

            </form>
        </div>
    </div>

    <!-- PREVIEW NILAI SAAT INI -->
    <div class="card card-modern">
        <div class="card-header">
            Preview Nilai Tersimpan
        </div>
        <div class="card-body">
            <div class="table-responsive">
            <table class="table table-bordered text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Platform AI</th>
                        <?php foreach($kriteria as $k): ?>
                        <th><?= $k['kode_kriteria'] ?></th>
                        <?php endforeach; ?>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($alternatif as $a): ?>
                    <?php
                    $idAlt    = $a['id_alternatif'];
                    $complete = true;
                    foreach($kriteria as $k){
                        if(!isset($nilaiExisting[$idAlt][$k['id_kriteria']])) $complete = false;
                    }
                    ?>
                    <tr>
                        <td class="fw-bold text-start"><?= htmlspecialchars($a['nama_alternatif']) ?></td>
                        <?php foreach($kriteria as $k): ?>
                        <?php $idKrit = $k['id_kriteria']; ?>
                        <td class="<?= isset($nilaiExisting[$idAlt][$idKrit]) ? '' : 'table-danger' ?>">
                            <?= $nilaiExisting[$idAlt][$idKrit] ?? '<span class="text-danger">-</span>' ?>
                        </td>
                        <?php endforeach; ?>
                        <td>
                            <?php if($complete): ?>
                                <span class="badge bg-success">✔ Lengkap</span>
                            <?php else: ?>
                                <span class="badge bg-danger">✘ Belum Lengkap</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div>

<?php include "layout/footer.php"; ?>