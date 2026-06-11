<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "db.php";

// ============================================================
// KONFIGURASI
// ============================================================
$KRITERIA = ['C1','C2','C3','C4','C5','C6'];
$NAMA_KRITERIA = [
    'C1' => 'Akurasi Jawaban',
    'C2' => 'Kemudahan Penggunaan',
    'C3' => 'Kecepatan Respon',
    'C4' => 'Kelengkapan Fitur Gratis',
    'C5' => 'Kemampuan Membantu Akademik',
    'C6' => 'Keamanan Data dan Privasi',
];
$N  = count($KRITERIA);
$RI = 1.24;

// ============================================================
// FUNGSI
// ============================================================
function buildMatrix(array $rows, array $kriteria): array {
    $n   = count($kriteria);
    $idx = array_flip($kriteria);
    $mat = [];
    for ($i = 0; $i < $n; $i++)
        for ($j = 0; $j < $n; $j++)
            $mat[$i][$j] = ($i === $j) ? 1.0 : 0.0;
    foreach ($rows as $row) {
        $i = $idx[$row['kriteria1']] ?? null;
        $j = $idx[$row['kriteria2']] ?? null;
        if ($i === null || $j === null) continue;
        $val = (float)$row['nilai'];
        $mat[$i][$j] = $val;
        $mat[$j][$i] = ($val != 0) ? 1.0 / $val : 0.0;
    }
    return $mat;
}

function calcAHP(array $mat, int $n, float $RI): array {
    $colSum = array_fill(0, $n, 0.0);
    for ($j = 0; $j < $n; $j++)
        for ($i = 0; $i < $n; $i++)
            $colSum[$j] += $mat[$i][$j];
    $norm = [];
    for ($i = 0; $i < $n; $i++)
        for ($j = 0; $j < $n; $j++)
            $norm[$i][$j] = ($colSum[$j] != 0) ? $mat[$i][$j] / $colSum[$j] : 0;
    $ev = [];
    for ($i = 0; $i < $n; $i++)
        $ev[$i] = array_sum($norm[$i]) / $n;
    $lambda = [];
    for ($i = 0; $i < $n; $i++) {
        $ws = 0;
        for ($j = 0; $j < $n; $j++) $ws += $mat[$i][$j] * $ev[$j];
        $lambda[$i] = ($ev[$i] != 0) ? $ws / $ev[$i] : 0;
    }
    $lambdaMax = array_sum($lambda) / $n;
    $CI = ($n > 1) ? ($lambdaMax - $n) / ($n - 1) : 0;
    $CR = ($RI != 0) ? $CI / $RI : 0;
    return [
        'matrix'     => $mat,
        'col_sum'    => $colSum,
        'norm'       => $norm,
        'ev'         => $ev,
        'lambda'     => $lambda,
        'lambda_max' => $lambdaMax,
        'CI'         => $CI,
        'CR'         => $CR,
        'consistent' => ($CR <= 0.10),
    ];
}

function geoMean(array $vals): float {
    $n = count($vals);
    if ($n === 0) return 0.0;
    $logSum = 0;
    foreach ($vals as $v) $logSum += log(max((float)$v, 1e-10));
    return exp($logSum / $n);
}

function fmt(float $v, int $d = 4): string {
    return number_format($v, $d);
}

// ============================================================
// AMBIL DATA & HITUNG
// ============================================================
$qPairwise = mysqli_query($koneksi,
    "SELECT * FROM tbl_pairwise_responden ORDER BY id_responden, kriteria1, kriteria2"
);
$rawPairwise = [];
while ($row = mysqli_fetch_assoc($qPairwise)) {
    $rawPairwise[$row['id_responden']][] = $row;
}

$evTersimpan = [];
$qEV = mysqli_query($koneksi, "SELECT * FROM tbl_ev_responden ORDER BY id_responden");
while ($row = mysqli_fetch_assoc($qEV)) {
    $evTersimpan[$row['id_responden']] = $row;
}

$respResults = [];
foreach ($rawPairwise as $rId => $rows) {
    $mat = buildMatrix($rows, $KRITERIA);
    $ahp = calcAHP($mat, $N, $RI);
    $ahp['id_responden'] = $rId;
    $ahp['ev_db'] = $evTersimpan[$rId] ?? null;
    $respResults[$rId] = $ahp;
}

$geoMatrix = [];
for ($i = 0; $i < $N; $i++) {
    for ($j = 0; $j < $N; $j++) {
        $vals = [];
        foreach ($respResults as $res) $vals[] = $res['matrix'][$i][$j];
        $geoMatrix[$i][$j] = geoMean($vals);
    }
}

$finalAHP   = calcAHP($geoMatrix, $N, $RI);
$bobotFinal = $finalAHP['ev'];

// ============================================================
// SIMPAN BOBOT — HARUS SEBELUM include header.php
// ============================================================
if(isset($_POST['simpan_bobot'])){
    $success = true;
    foreach($KRITERIA as $i => $kode){
        $bobot = (float)$bobotFinal[$i];

        // Hitung nilai geomean per baris (geometric mean dari baris ke-i matrix)
        $geoRow = 1;
        for($j = 0; $j < $N; $j++) $geoRow *= $geoMatrix[$i][$j];
        $geoRow = pow($geoRow, 1.0 / $N);

        // Update tbl_kriteria → untuk perhitungan TOPSIS
        $q1 = mysqli_query($koneksi,
            "UPDATE tbl_kriteria 
             SET bobot = $bobot 
             WHERE kode_kriteria = '$kode'"
        );

        // Cek apakah baris sudah ada di tbl_geomean
        $cek = mysqli_query($koneksi,
            "SELECT id_geomean FROM tbl_geomean WHERE kode_kriteria = '$kode'"
        );

        if(mysqli_num_rows($cek) > 0){
            // Update jika sudah ada
            $q2 = mysqli_query($koneksi,
                "UPDATE tbl_geomean 
                 SET nilai_geomean = $geoRow, bobot_final = $bobot 
                 WHERE kode_kriteria = '$kode'"
            );
        } else {
            // Insert jika belum ada
            $q2 = mysqli_query($koneksi,
                "INSERT INTO tbl_geomean (kode_kriteria, nilai_geomean, bobot_final) 
                 VALUES ('$kode', $geoRow, $bobot)"
            );
        }

        if(!$q1 || !$q2) $success = false;
    }
    $_SESSION['notif'] = $success
        ? ['type'=>'success', 'msg'=>'✅ Bobot AHP berhasil disimpan! tbl_kriteria & tbl_geomean terupdate.']
        : ['type'=>'danger',  'msg'=>'❌ Gagal menyimpan: '.mysqli_error($koneksi)];
    header("Location: ahp_responden.php");
    exit;
}

// ============================================================
// HITUNG VARIABEL SISA
// ============================================================
$geoRowSum = [];
for ($i = 0; $i < $N; $i++) {
    $vals = [];
    for ($j = 0; $j < $N; $j++) $vals[] = $geoMatrix[$i][$j];
    $geoRowSum[$i] = array_sum($vals);
}
$totalGeoSum = array_sum($geoRowSum);

$bobotGeo = [];
for ($i = 0; $i < $N; $i++) {
    $bobotGeo[$i] = ($totalGeoSum != 0) ? $geoRowSum[$i] / $totalGeoSum : 0;
}

$totalResp      = count($respResults);
$konsistenCount = array_sum(array_map(fn($r) => $r['consistent'] ? 1 : 0, $respResults));

// ============================================================
// BARU INCLUDE HEADER
// ============================================================
include "layout/header.php";
?>

<div class="container-fluid px-4">

    <div class="d-flex align-items-center mb-3 mt-2">
        <h2 class="mb-0">🧮 Analisis AHP Per Responden</h2>
    </div>

    <?php if(isset($_SESSION['notif'])): ?>
    <div class="alert alert-<?= $_SESSION['notif']['type'] ?> alert-dismissible fade show">
        <?= $_SESSION['notif']['msg'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['notif']); endif; ?>

    <div class="alert alert-info mb-4">
        <strong>Alur Perhitungan:</strong>
        Data pairwise dari <code>tbl_pairwise_responden</code> →
        Matrix berpasangan tiap responden →
        Normalisasi & Priority Vector →
        Uji Konsistensi (CI/CR) →
        <strong>Geometric Mean Matrix</strong> →
        Bobot AHP Final.
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-primary"><?= $totalResp ?></div>
                <div class="text-muted small">Total Responden</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-success"><?= $konsistenCount ?>/<?= $totalResp ?></div>
                <div class="text-muted small">Responden Konsisten</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold <?= $finalAHP['consistent'] ? 'text-success' : 'text-danger' ?>">
                    <?= fmt($finalAHP['CR'], 4) ?>
                </div>
                <div class="text-muted small">CR GeoMean Final</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-1 fw-bold text-warning"><?= $RI ?></div>
                <div class="text-muted small">Random Index (n=<?= $N ?>)</div>
            </div>
        </div>
    </div>

    <h4 class="mb-3">📋 Detail Perhitungan Per Responden</h4>

    <<ul class="nav nav-tabs mb-0" id="tabsResponden">
        <?php $noResp = 1; foreach ($respResults as $rId => $res): ?>
        <li class="nav-item">
            <button class="nav-link <?= $rId === array_key_first($respResults) ? 'active' : '' ?>"
                    data-bs-toggle="tab"
                    data-bs-target="#resp-<?= $rId ?>">
                Responden <?= $noResp ?>
                <?php if ($res['consistent']): ?>
                    <span class="badge bg-success ms-1" style="font-size:10px">✔</span>
                <?php else: ?>
                    <span class="badge bg-danger ms-1" style="font-size:10px">✘</span>
                <?php endif; ?>
            </button>
        </li>
        <?php $noResp++; endforeach; ?>
    </ul>

    <div class="tab-content border border-top-0 rounded-bottom p-3 bg-white shadow-sm mb-5">
        <?php foreach ($respResults as $rId => $res): ?>
        <div class="tab-pane fade <?= $rId === array_key_first($respResults) ? 'show active' : '' ?>"
             id="resp-<?= $rId ?>">

            <div class="d-flex align-items-center gap-3 mb-3 mt-1">
                <?php if ($res['consistent']): ?>
                    <span class="badge bg-success fs-6 px-3 py-2">✔ Konsisten</span>
                <?php else: ?>
                    <span class="badge bg-danger fs-6 px-3 py-2">✘ Tidak Konsisten</span>
                <?php endif; ?>
                <span class="text-muted small">
                    λmax = <strong><?= fmt($res['lambda_max']) ?></strong> &nbsp;|&nbsp;
                    CI = <strong><?= fmt($res['CI']) ?></strong> &nbsp;|&nbsp;
                    RI = <strong><?= $RI ?></strong> &nbsp;|&nbsp;
                    CR = <strong><?= fmt($res['CR']) ?></strong>
                    <?= ($res['CR'] <= 0.10) ? '(≤ 0.10 ✔)' : '(> 0.10 ✘)' ?>
                </span>
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <h6 class="fw-bold mb-2">① Matrix Perbandingan Berpasangan</h6>
                    <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size:13px">
                        <thead class="table-dark">
                            <tr>
                                <th>Kriteria</th>
                                <?php foreach ($KRITERIA as $k): ?>
                                <th><?= $k ?><br><small class="fw-normal"><?= $NAMA_KRITERIA[$k] ?></small></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < $N; $i++): ?>
                            <tr>
                                <td class="fw-bold bg-light"><?= $KRITERIA[$i] ?></td>
                                <?php for ($j = 0; $j < $N; $j++): ?>
                                <td <?= ($i === $j) ? 'class="table-secondary fw-bold"' : '' ?>>
                                    <?= fmt($res['matrix'][$i][$j], 4) ?>
                                </td>
                                <?php endfor; ?>
                            </tr>
                            <?php endfor; ?>
                            <tr class="table-warning fw-bold">
                                <td>Jumlah Kolom</td>
                                <?php for ($j = 0; $j < $N; $j++): ?>
                                <td><?= fmt($res['col_sum'][$j], 4) ?></td>
                                <?php endfor; ?>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <div class="col-12">
                    <h6 class="fw-bold mb-2">② Normalisasi, Priority Vector (EV) & Lambda</h6>
                    <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size:13px">
                        <thead class="table-dark">
                            <tr>
                                <th>Kriteria</th>
                                <?php foreach ($KRITERIA as $k): ?><th><?= $k ?></th><?php endforeach; ?>
                                <th class="table-warning text-dark">EV (Bobot)</th>
                                <th class="table-info text-dark">λ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 0; $i < $N; $i++): ?>
                            <tr>
                                <td class="fw-bold bg-light"><?= $KRITERIA[$i] ?></td>
                                <?php for ($j = 0; $j < $N; $j++): ?>
                                <td><?= fmt($res['norm'][$i][$j], 4) ?></td>
                                <?php endfor; ?>
                                <td class="fw-bold text-primary"><?= fmt($res['ev'][$i], 4) ?></td>
                                <td><?= fmt($res['lambda'][$i], 4) ?></td>
                            </tr>
                            <?php endfor; ?>
                            <tr class="table-warning fw-bold">
                                <td>Total</td>
                                <?php for ($j = 0; $j < $N; $j++): ?><td>1.0000</td><?php endfor; ?>
                                <td><?= fmt(array_sum($res['ev']), 4) ?></td>
                                <td>λmax=<?= fmt($res['lambda_max'], 4) ?></td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>

                <?php if ($res['ev_db']): ?>
                <div class="col-12">
                    <h6 class="fw-bold mb-2">③ Perbandingan EV Hitung vs EV Tersimpan</h6>
                    <div class="table-responsive">
                    <table class="table table-bordered table-sm text-center align-middle mb-0" style="font-size:13px">
                        <thead class="table-dark">
                            <tr>
                                <th>Sumber</th>
                                <?php foreach ($KRITERIA as $k): ?><th><?= $k ?></th><?php endforeach; ?>
                                <th>λmax</th><th>CI</th><th>CR</th><th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="fw-bold">Hitung Ulang</td>
                                <?php for ($i = 0; $i < $N; $i++): ?>
                                <td><?= fmt($res['ev'][$i], 4) ?></td>
                                <?php endfor; ?>
                                <td><?= fmt($res['lambda_max'], 4) ?></td>
                                <td><?= fmt($res['CI'], 4) ?></td>
                                <td><?= fmt($res['CR'], 4) ?></td>
                                <td>
                                    <span class="badge <?= $res['consistent'] ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $res['consistent'] ? 'Konsisten' : 'Tidak Konsisten' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php $db = $res['ev_db']; ?>
                            <tr class="table-light">
                                <td class="fw-bold">DB Tersimpan</td>
                                <td><?= $db['ev_c1'] ?></td>
                                <td><?= $db['ev_c2'] ?></td>
                                <td><?= $db['ev_c3'] ?></td>
                                <td><?= $db['ev_c4'] ?></td>
                                <td><?= $db['ev_c5'] ?></td>
                                <td><?= $db['ev_c6'] ?></td>
                                <td><?= $db['lambda_max'] ?></td>
                                <td><?= $db['ci'] ?></td>
                                <td><?= $db['cr'] ?></td>
                                <td>
                                    <span class="badge <?= strtolower($db['status_konsistensi']) === 'konsisten' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= htmlspecialchars($db['status_konsistensi']) ?>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
                <?php endif; ?>

                <div class="col-12">
                    <div class="row g-2 mt-1">
                        <div class="col-6 col-md-3">
                            <div class="card text-center border-0 bg-light py-2">
                                <div class="fw-bold fs-5"><?= fmt($res['lambda_max'], 4) ?></div>
                                <small class="text-muted">λmax</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card text-center border-0 bg-light py-2">
                                <div class="fw-bold fs-5"><?= fmt($res['CI'], 4) ?></div>
                                <small class="text-muted">CI = (λmax−<?=$N?>)/(<?=$N?>−1)</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card text-center border-0 bg-light py-2">
                                <div class="fw-bold fs-5"><?= $RI ?></div>
                                <small class="text-muted">RI (n=<?= $N ?>)</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="card text-center border-0 <?= $res['consistent'] ? 'bg-success text-white' : 'bg-danger text-white' ?> py-2">
                                <div class="fw-bold fs-5"><?= fmt($res['CR'], 4) ?></div>
                                <small>CR = CI/RI <?= $res['consistent'] ? '✔ Konsisten' : '✘ Tidak Konsisten' ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <h4 class="mb-3">🔢 Geometric Mean Matrix (Agregasi <?= $totalResp ?> Responden)</h4>

    <div class="card shadow-sm mb-5">
        <div class="card-header text-white" style="background:#4a2c0a">
            GM(i,j) = (v₁ × v₂ × ... × v<?= $totalResp ?>)^(1/<?= $totalResp ?>)
        </div>
        <div class="card-body">
            <h6 class="fw-bold mb-2">Matrix Geometric Mean</h6>
            <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm text-center align-middle" style="font-size:13px">
                <thead class="table-dark">
                    <tr>
                        <th>Kriteria</th>
                        <?php foreach ($KRITERIA as $k): ?><th><?= $k ?></th><?php endforeach; ?>
                        <th class="table-warning text-dark">Geo Mean Baris</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $N; $i++): ?>
                    <tr>
                        <td class="fw-bold bg-light"><?= $KRITERIA[$i] ?></td>
                        <?php for ($j = 0; $j < $N; $j++): ?>
                        <td <?= ($i===$j) ? 'class="table-secondary"' : '' ?>>
                            <?= fmt($geoMatrix[$i][$j], 4) ?>
                        </td>
                        <?php endfor; ?>
                        <?php
                        $geoRowVal = 1;
                        for($j = 0; $j < $N; $j++) $geoRowVal *= $geoMatrix[$i][$j];
                        $geoRowVal = pow($geoRowVal, 1.0 / $N);
                        ?>
                        <td class="fw-bold text-primary"><?= fmt($geoRowVal, 4) ?></td>
                    </tr>
                    <?php endfor; ?>
                    <tr class="table-warning fw-bold">
                        <td>Jumlah Kolom</td>
                        <?php foreach ($finalAHP['col_sum'] as $cs): ?>
                        <td><?= fmt($cs, 4) ?></td>
                        <?php endforeach; ?>
                        <td><?= fmt($totalGeoSum, 4) ?></td>
                    </tr>
                </tbody>
            </table>
            </div>

            <h6 class="fw-bold mb-2">Normalisasi & Bobot Prioritas (Eigenvector)</h6>
            <div class="table-responsive mb-4">
            <table class="table table-bordered table-sm text-center align-middle" style="font-size:13px">
                <thead class="table-dark">
                    <tr>
                        <th>Kriteria</th>
                        <?php foreach ($KRITERIA as $k): ?><th><?= $k ?></th><?php endforeach; ?>
                        <th class="table-warning text-dark">EV / Bobot</th>
                        <th class="table-info text-dark">λ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($i = 0; $i < $N; $i++): ?>
                    <tr>
                        <td class="fw-bold bg-light"><?= $KRITERIA[$i] ?></td>
                        <?php for ($j = 0; $j < $N; $j++): ?>
                        <td><?= fmt($finalAHP['norm'][$i][$j], 4) ?></td>
                        <?php endfor; ?>
                        <td class="fw-bold text-primary"><?= fmt($bobotFinal[$i], 4) ?></td>
                        <td><?= fmt($finalAHP['lambda'][$i], 4) ?></td>
                    </tr>
                    <?php endfor; ?>
                    <tr class="table-warning fw-bold">
                        <td>Total</td>
                        <?php for ($j = 0; $j < $N; $j++): ?><td>1.0000</td><?php endfor; ?>
                        <td><?= fmt(array_sum($bobotFinal), 4) ?></td>
                        <td>λmax=<?= fmt($finalAHP['lambda_max'], 4) ?></td>
                    </tr>
                </tbody>
            </table>
            </div>

            <div class="row g-2">
                <div class="col-6 col-md-3">
                    <div class="card text-center border-0 bg-light py-2">
                        <div class="fw-bold fs-5"><?= fmt($finalAHP['lambda_max'], 4) ?></div>
                        <small class="text-muted">λmax</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center border-0 bg-light py-2">
                        <div class="fw-bold fs-5"><?= fmt($finalAHP['CI'], 4) ?></div>
                        <small class="text-muted">CI</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center border-0 bg-light py-2">
                        <div class="fw-bold fs-5"><?= $RI ?></div>
                        <small class="text-muted">RI</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card text-center border-0 <?= $finalAHP['consistent'] ? 'bg-success text-white' : 'bg-danger text-white' ?> py-2">
                        <div class="fw-bold fs-5"><?= fmt($finalAHP['CR'], 4) ?></div>
                        <small>CR <?= $finalAHP['consistent'] ? '✔ Konsisten' : '✘ Tidak Konsisten' ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3">⚖️ Bobot Prioritas AHP Final</h4>

    <div class="card shadow-sm mb-5">
        <div class="card-header text-white" style="background:#4a2c0a">
            Bobot digunakan sebagai input perhitungan TOPSIS
        </div>
        <div class="card-body">

            <div class="alert alert-info mb-3">
                ℹ️ Klik tombol di bawah untuk menyimpan bobot hasil AHP ke database.
                Setelah disimpan, <a href="perhitungan.php">perhitungan TOPSIS</a> akan otomatis menggunakan bobot terbaru.
            </div>

            <form method="post" class="mb-4">
                <button
                    type="submit"
                    name="simpan_bobot"
                    class="btn btn-lg px-4"
                    style="background:linear-gradient(135deg,#5d4037,#3e2723);color:white;"
                    onclick="return confirm('Simpan bobot AHP ke database? Bobot lama akan ditimpa.')">
                    💾 Simpan Bobot ke tbl_kriteria
                </button>
                <small class="text-muted ms-3">
                    Setelah disimpan, TOPSIS akan otomatis pakai bobot terbaru.
                </small>
            </form>

            <div class="row g-3">
                <?php foreach ($KRITERIA as $i => $k): ?>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="fw-bold"><?= $k ?> – <?= $NAMA_KRITERIA[$k] ?></span>
                        <span class="badge bg-primary fs-6"><?= fmt($bobotFinal[$i], 4) ?></span>
                    </div>
                    <div class="progress" style="height:22px;border-radius:6px">
                        <div class="progress-bar" role="progressbar"
                             style="width:<?= round($bobotFinal[$i]*100,2) ?>%;background:#4a2c0a"
                             aria-valuenow="<?= round($bobotFinal[$i]*100,2) ?>"
                             aria-valuemin="0" aria-valuemax="100">
                            <?= round($bobotFinal[$i]*100,2) ?>%
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <hr class="mt-4">

            <div class="table-responsive mt-2">
            <table class="table table-bordered table-sm text-center" style="max-width:500px;font-size:13px">
                <thead class="table-dark">
                    <tr><th>Kode</th><th>Nama Kriteria</th><th>Bobot AHP</th><th>(%)</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($KRITERIA as $i => $k): ?>
                    <tr>
                        <td class="fw-bold"><?= $k ?></td>
                        <td class="text-start"><?= $NAMA_KRITERIA[$k] ?></td>
                        <td class="fw-bold text-primary"><?= fmt($bobotFinal[$i], 4) ?></td>
                        <td><?= fmt($bobotFinal[$i]*100, 2) ?>%</td>
                    </tr>
                    <?php endforeach; ?>
                    <tr class="table-warning fw-bold">
                        <td colspan="2">Total</td>
                        <td><?= fmt(array_sum($bobotFinal), 4) ?></td>
                        <td>100.00%</td>
                    </tr>
                </tbody>
            </table>
            </div>

        </div>
    </div>

</div>

<?php include "layout/footer.php"; ?>