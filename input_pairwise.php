<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data responden
$responden = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT * FROM tbl_responden WHERE id_responden='$id'"
));

if (!$responden) {
    echo "<script>alert('Responden tidak ditemukan!'); window.location='responden.php';</script>";
    exit;
}

$kriteria = ['C1','C2','C3','C4','C5','C6'];
$namaKriteria = [
    'C1' => 'Akurasi Jawaban',
    'C2' => 'Kemudahan Penggunaan',
    'C3' => 'Kecepatan Respon',
    'C4' => 'Kelengkapan Fitur Gratis',
    'C5' => 'Kemampuan Membantu Akademik',
    'C6' => 'Keamanan Data dan Privasi',
];

$skalaAHP = [
    '1'   => '1 — Sama Penting',
    '2'   => '2 — Antara 1 & 3',
    '3'   => '3 — Sedikit Lebih Penting',
    '4'   => '4 — Antara 3 & 5',
    '5'   => '5 — Lebih Penting',
    '6'   => '6 — Antara 5 & 7',
    '7'   => '7 — Sangat Lebih Penting',
    '8'   => '8 — Antara 7 & 9',
    '9'   => '9 — Mutlak Lebih Penting',
    '1/2' => '1/2',
    '1/3' => '1/3',
    '1/4' => '1/4',
    '1/5' => '1/5',
    '1/6' => '1/6',
    '1/7' => '1/7',
    '1/8' => '1/8',
    '1/9' => '1/9',
];

if (isset($_POST['simpan'])) {
    $n = count($kriteria);

    // Bangun matrix untuk hitung CR
    $idx = array_flip($kriteria);
    $mat = [];
    for ($i = 0; $i < $n; $i++)
        for ($j = 0; $j < $n; $j++)
            $mat[$i][$j] = ($i === $j) ? 1.0 : 0.0;

    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            $k1  = $kriteria[$i];
            $k2  = $kriteria[$j];
            $key = "pair_{$k1}_{$k2}";
            $raw = $_POST[$key] ?? '1';

            if (strpos($raw, '/') !== false) {
                $parts = explode('/', $raw);
                $nilai = (float)$parts[0] / (float)$parts[1];
            } else {
                $nilai = (float)$raw;
            }

            $mat[$i][$j] = $nilai;
            $mat[$j][$i] = ($nilai != 0) ? 1.0 / $nilai : 0.0;
        }
    }

    // Hitung CR
    $colSum = array_fill(0, $n, 0.0);
    for ($j = 0; $j < $n; $j++)
        for ($i = 0; $i < $n; $i++)
            $colSum[$j] += $mat[$i][$j];

    $ev = [];
    for ($i = 0; $i < $n; $i++) {
        $rowSum = 0;
        for ($j = 0; $j < $n; $j++)
            $rowSum += ($colSum[$j] != 0) ? $mat[$i][$j] / $colSum[$j] : 0;
        $ev[$i] = $rowSum / $n;
    }

    $lambdaMax = 0;
    for ($i = 0; $i < $n; $i++) {
        $ws = 0;
        for ($j = 0; $j < $n; $j++) $ws += $mat[$i][$j] * $ev[$j];
        $lambdaMax += ($ev[$i] != 0) ? $ws / $ev[$i] : 0;
    }
    $lambdaMax /= $n;
    $CI = ($lambdaMax - $n) / ($n - 1);
    $RI = 1.24;
    $CR = $CI / $RI;
    $konsisten = ($CR <= 0.10);

    $status = $konsisten
        ? 'Konsisten (CR=' . number_format($CR, 2) . ')'
        : 'Tidak Konsisten (CR=' . number_format($CR, 2) . ')';

    // Hapus pairwise lama jika ada
    mysqli_query($koneksi,
        "DELETE FROM tbl_pairwise_responden WHERE id_responden='$id'"
    );

    // Simpan pairwise baru
    for ($i = 0; $i < $n; $i++) {
        for ($j = $i + 1; $j < $n; $j++) {
            $k1  = $kriteria[$i];
            $k2  = $kriteria[$j];
            $key = "pair_{$k1}_{$k2}";
            $raw = $_POST[$key] ?? '1';

            if (strpos($raw, '/') !== false) {
                $parts = explode('/', $raw);
                $nilai = (float)$parts[0] / (float)$parts[1];
            } else {
                $nilai = (float)$raw;
            }

            $nilai = mysqli_real_escape_string($koneksi, $nilai);
            mysqli_query($koneksi,
                "INSERT INTO tbl_pairwise_responden (id_responden, kriteria1, kriteria2, nilai)
                 VALUES ('$id', '$k1', '$k2', '$nilai')"
            );
        }
    }

    // Update status kuesioner
    $status = mysqli_real_escape_string($koneksi, $status);
    mysqli_query($koneksi,
        "UPDATE tbl_responden SET status_kuesioner='$status' WHERE id_responden='$id'"
    );

    echo "<script>alert('Data pairwise berhasil disimpan! Status: $status'); window.location='responden.php';</script>";
    exit;
}

include "layout/header.php";
?>

<style>
.pairwise-table th, .pairwise-table td {
    vertical-align: middle;
    font-size: 12px;
}
.pairwise-table select {
    min-width: 180px;
}
.section-label {
    background: linear-gradient(135deg, #6d4c41, #4e342e);
    color: white;
    padding: 10px 18px;
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}
</style>

<div class="card card-modern mb-4">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-keyboard me-2"></i>
            Input Pairwise AHP — <?= htmlspecialchars($responden['nama_responden']) ?>
        </h5>
    </div>
    <div class="card-body">

        <!-- INFO RESPONDEN -->
        <div class="alert alert-secondary mb-4">
            <div class="row">
                <div class="col-md-4">
                    <strong>Nama:</strong> <?= htmlspecialchars($responden['nama_responden']) ?>
                </div>
                <div class="col-md-4">
                    <strong>Prodi:</strong> <?= htmlspecialchars($responden['program_studi']) ?>
                </div>
                <div class="col-md-4">
                    <strong>Angkatan:</strong> <?= htmlspecialchars($responden['angkatan']) ?>
                </div>
            </div>
        </div>

        <form method="POST">

            <div class="section-label">
                <i class="fas fa-scale-balanced"></i>
                Perbandingan Berpasangan AHP
            </div>

            <div class="alert alert-info mb-4">
                <strong>Petunjuk:</strong> Pilih nilai perbandingan untuk setiap pasang kriteria.
                Nilai <strong>1–9</strong> berarti baris lebih penting dari kolom.
                Nilai <strong>1/2–1/9</strong> berarti kolom lebih penting dari baris.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered pairwise-table">
                    <thead>
                        <tr style="background:linear-gradient(135deg,#6d4c41,#4e342e);color:white;">
                            <th>Kriteria A</th>
                            <th>vs</th>
                            <th>Kriteria B</th>
                            <th>Nilai Perbandingan (A terhadap B)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $n = count($kriteria);
                    for ($i = 0; $i < $n; $i++):
                        for ($j = $i + 1; $j < $n; $j++):
                            $k1  = $kriteria[$i];
                            $k2  = $kriteria[$j];
                            $key = "pair_{$k1}_{$k2}";
                    ?>
                    <tr>
                        <td>
                            <strong><?= $k1 ?></strong><br>
                            <small class="text-muted"><?= $namaKriteria[$k1] ?></small>
                        </td>
                        <td class="text-center text-muted">vs</td>
                        <td>
                            <strong><?= $k2 ?></strong><br>
                            <small class="text-muted"><?= $namaKriteria[$k2] ?></small>
                        </td>
                        <td>
                            <select name="<?= $key ?>" class="form-select form-select-sm" required>
                                <?php foreach ($skalaAHP as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $val == '1' ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <?php endfor; endfor; ?>
                    </tbody>
                </table>
            </div>

            <hr class="my-4">

            <div class="d-flex gap-3">
                <button type="submit" name="simpan" class="btn btn-success btn-lg px-4">
                    <i class="fas fa-save me-1"></i> Simpan & Hitung CR
                </button>
                <a href="responden.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

        </form>
    </div>
</div>

<?php include "layout/footer.php"; ?>