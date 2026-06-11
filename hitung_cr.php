<?php
session_start();
include "db.php";

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$id = (int)$_GET['id'];
if ($id == 0) {
    die("ID tidak diterima! URL: " . $_SERVER['REQUEST_URI']);
}
$kriteria = ['C1','C2','C3','C4','C5','C6'];
$n = count($kriteria);
$RI = 1.24;

// Ambil data pairwise
$q = mysqli_query($koneksi,
    "SELECT * FROM tbl_pairwise_responden WHERE id_responden='$id'"
);

if (mysqli_num_rows($q) == 0) {
    echo "<script>alert('Data pairwise tidak ditemukan untuk responden ini.'); history.back();</script>";
    exit;
}

// Bangun matrix
$idx = array_flip($kriteria);
$mat = [];
for ($i = 0; $i < $n; $i++)
    for ($j = 0; $j < $n; $j++)
        $mat[$i][$j] = ($i === $j) ? 1.0 : 0.0;

while ($row = mysqli_fetch_assoc($q)) {
    $i = $idx[$row['kriteria1']] ?? null;
    $j = $idx[$row['kriteria2']] ?? null;
    if ($i === null || $j === null) continue;
    $val = (float)$row['nilai'];
    $mat[$i][$j] = $val;
    $mat[$j][$i] = ($val != 0) ? 1.0 / $val : 0.0;
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
$CR = $CI / $RI;
$konsisten = ($CR <= 0.10);

$status = $konsisten
    ? 'Konsisten (CR=' . number_format($CR, 2) . ')'
    : 'Tidak Konsisten (CR=' . number_format($CR, 2) . ')';

mysqli_query($koneksi,
    "UPDATE tbl_responden SET status_kuesioner='$status' WHERE id_responden='$id'"
);

header("Location: responden.php");
exit;
?>