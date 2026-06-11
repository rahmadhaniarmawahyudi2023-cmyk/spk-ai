<?php
session_start();
include "db.php";
if (!isset($_SESSION['username'])) {
    header("Location:index.php");
    exit;
}

$namaKriteria = [
    'C1' => 'Akurasi Jawaban',
    'C2' => 'Kemudahan Penggunaan',
    'C3' => 'Kecepatan Respon',
    'C4' => 'Kelengkapan Fitur Gratis',
    'C5' => 'Kemampuan Membantu Akademik',
    'C6' => 'Keamanan Data dan Privasi'
];

// Ambil data dari tbl_geomean
$q = mysqli_query($koneksi, "SELECT * FROM tbl_geomean ORDER BY kode_kriteria");
$existingData = [];
while ($row = mysqli_fetch_assoc($q)) {
    $existingData[$row['kode_kriteria']] = $row;
}

if (isset($_POST['simpan'])) {
    $gmValues = [];
    foreach (array_keys($namaKriteria) as $kode) {
        $gmValues[$kode] = (float) $_POST['GM_' . $kode];
    }

    $gmTotal = array_sum($gmValues);

    if ($gmTotal == 0) {
        echo "<script>alert('Total Geometric Mean tidak boleh 0!'); history.back();</script>";
        exit;
    }

    foreach ($gmValues as $kode => $gmVal) {
        $bobotFinal = $gmVal / $gmTotal;

        // Update tbl_geomean
        mysqli_query($koneksi,
            "UPDATE tbl_geomean 
             SET nilai_geomean = '$gmVal',
                 bobot_final   = '$bobotFinal'
             WHERE kode_kriteria = '$kode'"
        );

        // Tambahkan ini — update tbl_kriteria juga
        mysqli_query($koneksi,
            "UPDATE tbl_kriteria 
             SET bobot = '$bobotFinal'
             WHERE kode_kriteria = '$kode'"
        );
    }

    echo "<script>alert('Bobot berhasil diperbarui'); location='kriteria.php';</script>";
    exit;
}if (isset($_POST['simpan'])) {
    $gmValues = [];
    foreach (array_keys($namaKriteria) as $kode) {
        $gmValues[$kode] = (float) $_POST['GM_' . $kode];
    }

    $gmTotal = array_sum($gmValues);

    if ($gmTotal == 0) {
        echo "<script>alert('Total Geometric Mean tidak boleh 0!'); history.back();</script>";
        exit;
    }

    foreach ($gmValues as $kode => $gmVal) {
        $bobotFinal = $gmVal / $gmTotal;

        // Update tbl_geomean
        mysqli_query($koneksi,
            "UPDATE tbl_geomean 
             SET nilai_geomean = '$gmVal',
                 bobot_final   = '$bobotFinal'
             WHERE kode_kriteria = '$kode'"
        );

        // Tambahkan ini — update tbl_kriteria juga
        mysqli_query($koneksi,
            "UPDATE tbl_kriteria 
             SET bobot = '$bobotFinal'
             WHERE kode_kriteria = '$kode'"
        );
    }

    echo "<script>alert('Bobot berhasil diperbarui'); location='kriteria.php';</script>";
    exit;
}

include 'layout/header.php';
?>

<div class="card card-modern">
    <div class="card-header bg-warning">
        <h4>Edit Bobot AHP (Geometric Mean)</h4>
    </div>
    <div class="card-body">

        <div class="alert alert-info">
            Masukkan nilai <strong>Geometric Mean</strong> untuk setiap kriteria.
            Bobot prioritas (normalisasi) akan dihitung otomatis.
        </div>

        <form method="POST">
            <?php foreach ($namaKriteria as $kode => $nama): ?>
                <div class="mb-3">
                    <label class="form-label">
                        <strong><?= $kode ?></strong> – <?= $nama ?>
                    </label>
                    <input
                        type="number"
                        step="0.0001"
                        min="0"
                        name="GM_<?= $kode ?>"
                        class="form-control"
                        value="<?= isset($existingData[$kode]) ? $existingData[$kode]['nilai_geomean'] : '' ?>"
                        required>
                </div>
            
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Mengubah bobot AHP secara manual akan mempengaruhi 
                    hasil perhitungan TOPSIS. Data pairwise per responden tidak akan berubah.
                </div>
                <?php endforeach; ?>

            <button type="submit" name="simpan" class="btn btn-success">
                <i class="fas fa-save"></i> Simpan
            </button>
            <a href="kriteria.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</div>

<?php include 'layout/footer.php'; ?>