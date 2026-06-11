<?php

session_start();
include "db.php";
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$gm = [];
$norm = [];
$q = mysqli_query(
    $koneksi,
    "SELECT * FROM tbl_geomean ORDER BY kode_kriteria"
);

while($row = mysqli_fetch_assoc($q)){
    $kode = $row['kode_kriteria'];
    $gm[$kode] =
        (float)$row['nilai_geomean'];
    $norm[$kode] =
        (float)$row['bobot_final'];
}

$gmTotal = array_sum($gm);
$namaKriteria = [
    'C1' => 'Akurasi Jawaban',
    'C2' => 'Kemudahan Penggunaan',
    'C3' => 'Kecepatan Respon',
    'C4' => 'Kelengkapan Fitur Gratis',
    'C5' => 'Kemampuan Membantu Akademik',
    'C6' => 'Keamanan Data dan Privasi'
];
include "layout/header.php";
?>

<h2 class="mb-4">
    <i class="fas fa-weight-scale"></i>
    Bobot AHP Hasil Geometric Mean
</h2>

<a href="edit_bobot.php"
   class="btn btn-warning mb-4">

    <i class="fas fa-pen"></i>
    Edit Bobot AHP

</a>

<div class="alert alert-info">

    <strong>Informasi Perhitungan AHP</strong><br>

    Nilai Geometric Mean diperoleh dari agregasi penilaian
    5 responden terhadap perbandingan berpasangan kriteria.
    Nilai tersebut kemudian dinormalisasi untuk menghasilkan
    bobot prioritas AHP yang digunakan sebagai bobot kriteria
    pada proses perhitungan TOPSIS.

</div>

<!-- ==========================
     GEOMETRIC MEAN
=========================== -->

<div class="card card-modern mb-4">

    <div class="card-header">

        <h5 class="mb-0">
            <i class="fas fa-calculator"></i>
            Hasil Geometric Mean Kriteria
        </h5>

    </div>

    <div class="card-body">

        <table class="table table-bordered table-striped">

            <thead class="table-dark">

                <tr>
                    <th>Kode</th>
                    <th>Nama Kriteria</th>
                    <th>Nilai Geometric Mean</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach($namaKriteria as $kode => $nama): ?>

                <tr>

                    <td><?= $kode ?></td>

                    <td><?= $nama ?></td>

                    <td>
                        <?= number_format($gm[$kode],4) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            <tr class="table-warning">

                <th colspan="2">
                    Total Geometric Mean
                </th>

                <th>
                    <?= number_format($gmTotal,4) ?>
                </th>

            </tr>

            </tbody>

        </table>

    </div>

</div>

<!-- ==========================
     BOBOT PRIORITAS AHP
=========================== -->

<div class="card card-modern">

    <div class="card-header bg-success text-white">

        <h5 class="mb-0">

            <i class="fas fa-chart-bar"></i>

            Bobot Prioritas AHP
            (Normalisasi Geometric Mean)

        </h5>

    </div>

    <div class="card-body">

        <table class="table table-bordered mb-4">

            <thead class="table-success">

                <tr>
                    <th>Kode</th>
                    <th>Nama Kriteria</th>
                    <th>Bobot AHP</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach($norm as $kode => $nilai): ?>

                <tr>

                    <td><?= $kode ?></td>

                    <td><?= $namaKriteria[$kode] ?></td>

                    <td>
                        <?= number_format($nilai,4) ?>
                    </td>

                </tr>

            <?php endforeach; ?>

            <tr class="table-warning">

                <th colspan="2">
                    Total Bobot
                </th>

                <th>
                    <?= number_format(array_sum($norm),4) ?>
                </th>

            </tr>

            </tbody>

        </table>

        <hr>

        <h6 class="mb-4">
            Visualisasi Bobot Prioritas
        </h6>

        <?php foreach($norm as $kode => $nilai): ?>

            <?php $persen = $nilai * 100; ?>

            <div class="mb-4">

                <div class="d-flex justify-content-between">

                    <strong>

                        <?= $kode ?>
                        -
                        <?= $namaKriteria[$kode] ?>

                    </strong>

                    <span class="badge">

                        <?= number_format($nilai,4) ?>

                    </span>

                </div>

                <div
                    class="progress mt-2"
                    style="height:30px;">

                    <div
                        class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                        role="progressbar"
                        style="width: <?= $persen ?>%;">

                        <?= number_format($persen,1) ?>%

                    </div>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

<?php include "layout/footer.php"; ?>