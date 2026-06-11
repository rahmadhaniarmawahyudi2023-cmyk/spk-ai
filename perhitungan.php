<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once "db.php";

$kriteria = [];
$w = [];
$qKriteria = mysqli_query($koneksi, "SELECT * FROM tbl_kriteria ORDER BY id_kriteria");
while($k = mysqli_fetch_assoc($qKriteria)){
    $kode = $k['kode_kriteria'];
    $kriteria[$kode] = $k;
    $w[$kode] = (float)$k['bobot'];
}

$alternatif = [];
$qAlt = mysqli_query($koneksi, "SELECT * FROM tbl_alternatif ORDER BY id_alternatif");
while($a = mysqli_fetch_assoc($qAlt)) $alternatif[] = $a;

$X = [];
$qNilai = mysqli_query($koneksi, "SELECT * FROM tbl_penilaian");
while($n = mysqli_fetch_assoc($qNilai)){
    $kodeKriteria = "C".$n['id_kriteria'];
    $X[$n['id_alternatif']][$kodeKriteria] = (float)$n['nilai'];
}

$cols = ['C1','C2','C3','C4','C5','C6'];

$pembagi = [];
foreach($cols as $c){
    $sum = 0;
    foreach($alternatif as $a){
        $id = $a['id_alternatif'];
        $val = $X[$id][$c] ?? 0;
        $sum += pow($val, 2);
    }
    $pembagi[$c] = sqrt($sum);
}

$R = [];
foreach($alternatif as $a){
    $id = $a['id_alternatif'];
    foreach($cols as $c){
        $val = $X[$id][$c] ?? 0;
        $R[$id][$c] = ($pembagi[$c] == 0) ? 0 : $val / $pembagi[$c];
    }
}

$Y = [];
foreach($alternatif as $a){
    $id = $a['id_alternatif'];
    foreach($cols as $c){
        $Y[$id][$c] = ($R[$id][$c] ?? 0) * ($w[$c] ?? 0);
    }
}

$Aplus = [];
$Amin  = [];
foreach($cols as $c){
    $column = [];
    foreach($alternatif as $a) $column[] = $Y[$a['id_alternatif']][$c];
    $Aplus[$c] = max($column);
    $Amin[$c]  = min($column);
}

$Dplus = [];
$Dmin  = [];
foreach($alternatif as $a){
    $id = $a['id_alternatif'];
    $dp = 0; $dm = 0;
    foreach($cols as $c){
        $dp += pow($Y[$id][$c] - $Aplus[$c], 2);
        $dm += pow($Y[$id][$c] - $Amin[$c],  2);
    }
    $Dplus[$id] = sqrt($dp);
    $Dmin[$id]  = sqrt($dm);
}

$V = [];
foreach($alternatif as $a){
    $id = $a['id_alternatif'];
    $V[$id] = ($Dplus[$id] + $Dmin[$id] == 0) ? 0
        : $Dmin[$id] / ($Dplus[$id] + $Dmin[$id]);
}

$rankingData = [];
foreach($alternatif as $a){
    $id = $a['id_alternatif'];
    $rankingData[] = ['id'=>$id,'nama'=>$a['nama_alternatif'],'nilai'=>$V[$id]];
}
usort($rankingData, fn($a,$b) => $b['nilai'] <=> $a['nilai']);
$terbaik = $rankingData[0] ?? null;
?>

<?php include "layout/header.php"; ?>

<div class="container-fluid">

<h2 class="mb-4">📊 Perhitungan TOPSIS</h2>

<div class="alert alert-info">
    <strong>Tahapan Perhitungan TOPSIS</strong><br>
    Sistem menghitung secara otomatis:
    <ol class="mb-0 mt-2">
        <li>Matriks Keputusan</li>
        <li>Normalisasi Matriks</li>
        <li>Normalisasi Terbobot</li>
        <li>Solusi Ideal Positif (A+)</li>
        <li>Solusi Ideal Negatif (A-)</li>
        <li>Jarak Solusi (D+ dan D-)</li>
        <li>Nilai Preferensi</li>
        <li>Ranking Alternatif</li>
    </ol>
</div>

<!-- ===================== BOBOT ===================== -->
<div class="card card-modern mb-4">
    <div class="card-header">
        Bobot Kriteria (dari AHP)
    </div>
    <div class="card-body">
        <p class="text-muted small mb-2">Bobot diperoleh dari hasil perhitungan AHP menggunakan Geometric Mean seluruh responden.</p>
        <table class="table table-bordered table-sm text-center">
            <thead class="table-dark">
                <tr>
                    <?php foreach($cols as $c): ?><th><?= $c ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <?php foreach($cols as $c): ?>
                    <td><strong><?= number_format($w[$c],4) ?></strong></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== MATRIKS KEPUTUSAN ===================== -->
<div class="card card-modern mb-4">
    <div class="card-header">
        Langkah 1 — Matriks Keputusan (X)
    </div>
    <div class="card-body">
        <div class="alert alert-secondary py-2 mb-3">
            <strong>Sumber:</strong> Nilai rating tiap alternatif terhadap tiap kriteria dari <code>tbl_penilaian</code>.<br>
            <strong>Notasi:</strong> X<sub>ij</sub> = nilai alternatif ke-i pada kriteria ke-j
        </div>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Alternatif</th>
                    <?php foreach($cols as $c): ?><th><?= $c ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($alternatif as $a): ?>
                <tr>
                    <td><?= $a['nama_alternatif'] ?></td>
                    <?php foreach($cols as $c): ?>
                    <td><?= $X[$a['id_alternatif']][$c] ?? '-' ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
                <tr class="table-warning fw-bold">
                    <td>√(ΣX²) — Pembagi</td>
                    <?php foreach($cols as $c): ?>
                    <td><?= number_format($pembagi[$c],4) ?></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== NORMALISASI ===================== -->
<div class="card card-modern mb-4">
    <div class="card-header">
        Langkah 2 — Matriks Normalisasi (R)
    </div>
    <div class="card-body">
        <div class="alert alert-secondary py-2 mb-3">
            <strong>Rumus:</strong> r<sub>ij</sub> = x<sub>ij</sub> / √(Σx<sub>ij</sub>²)<br>
            <strong>Contoh C1 <?= $alternatif[0]['nama_alternatif'] ?>:</strong>
            r = <?= number_format($X[$alternatif[0]['id_alternatif']]['C1'] ?? 0, 4) ?> / <?= number_format($pembagi['C1'],4) ?>
            = <strong><?= number_format($R[$alternatif[0]['id_alternatif']]['C1'],4) ?></strong>
        </div>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Alternatif</th>
                    <?php foreach($cols as $c): ?><th><?= $c ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($alternatif as $a): ?>
                <?php $id = $a['id_alternatif']; ?>
                <tr>
                    <td><?= htmlspecialchars($a['nama_alternatif']) ?></td>
                    <?php foreach($cols as $c): ?>
                    <td><?= number_format($R[$id][$c],4) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== NORMALISASI TERBOBOT ===================== -->
<div class="card card-modern mb-4">
    <div class="card-header">
        Langkah 3 — Matriks Normalisasi Terbobot (Y)
    </div>
    <div class="card-body">
        <div class="alert alert-secondary py-2 mb-3">
            <strong>Rumus:</strong> y<sub>ij</sub> = w<sub>j</sub> × r<sub>ij</sub><br>
            <strong>Contoh C1 <?= $alternatif[0]['nama_alternatif'] ?>:</strong>
            y = <?= number_format($w['C1'],4) ?> × <?= number_format($R[$alternatif[0]['id_alternatif']]['C1'],4) ?>
            = <strong><?= number_format($Y[$alternatif[0]['id_alternatif']]['C1'],4) ?></strong>
        </div>
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Alternatif</th>
                    <?php foreach($cols as $c): ?><th><?= $c ?><br><small class="fw-normal">w=<?= number_format($w[$c],4) ?></small></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach($alternatif as $a): ?>
                <?php $id = $a['id_alternatif']; ?>
                <tr>
                    <td><?= htmlspecialchars($a['nama_alternatif']) ?></td>
                    <?php foreach($cols as $c): ?>
                    <td><?= number_format($Y[$id][$c],4) ?></td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== SOLUSI IDEAL ===================== -->
<div class="card card-modern mb-4">
    <div class="card-header bg-warning">
        Langkah 4 & 5 — Solusi Ideal Positif (A+) & Negatif (A-)
    </div>
    <div class="card-body">
        <div class="alert alert-secondary py-2 mb-3">
            <strong>Rumus:</strong><br>
            A+ = max(y<sub>ij</sub>) untuk setiap kriteria j (nilai terbesar tiap kolom Y)<br>
            A− = min(y<sub>ij</sub>) untuk setiap kriteria j (nilai terkecil tiap kolom Y)
        </div>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th></th>
                    <?php foreach($cols as $c): ?><th><?= $c ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <tr class="table-success">
                    <th>A+ (max)</th>
                    <?php foreach($cols as $c): ?>
                    <td><strong><?= number_format($Aplus[$c],4) ?></strong></td>
                    <?php endforeach; ?>
                </tr>
                <tr class="table-danger">
                    <th>A− (min)</th>
                    <?php foreach($cols as $c): ?>
                    <td><strong><?= number_format($Amin[$c],4) ?></strong></td>
                    <?php endforeach; ?>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== JARAK & PREFERENSI ===================== -->
<div class="card card-modern mb-4">
    <div class="card-header bg-dark text-white">
        Langkah 6 & 7 — Jarak Solusi (D+, D−) & Nilai Preferensi (V)
    </div>
    <div class="card-body">
        <div class="alert alert-secondary py-2 mb-3">
            <strong>Rumus D+:</strong> D⁺ᵢ = √Σ(yᵢⱼ − A⁺ⱼ)²<br>
            <strong>Rumus D−:</strong> D⁻ᵢ = √Σ(yᵢⱼ − A⁻ⱼ)²<br>
            <strong>Rumus Preferensi:</strong> Vᵢ = D⁻ᵢ / (D⁺ᵢ + D⁻ᵢ) &nbsp;→&nbsp; semakin besar semakin baik
        </div>
        <table class="table table-bordered text-center">
            <thead class="table-dark">
                <tr>
                    <th>Alternatif</th>
                    <th>D+</th>
                    <th>D−</th>
                    <th>V (Preferensi)</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($alternatif as $a): ?>
                <?php $id = $a['id_alternatif']; ?>
                <tr>
                    <td><?= htmlspecialchars($a['nama_alternatif']) ?></td>
                    <td><?= number_format($Dplus[$id],6) ?></td>
                    <td><?= number_format($Dmin[$id],6) ?></td>
                    <td><strong class="text-primary"><?= number_format($V[$id],6) ?></strong></td>
                    <td>
                        <?= number_format($Dmin[$id],4) ?> / (<?= number_format($Dplus[$id],4) ?> + <?= number_format($Dmin[$id],4) ?>)
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== HASIL TERBAIK ===================== -->
<?php if($terbaik): ?>
<div class="card card-modern mb-4">
    <div class="card-body text-center">
        <h3>🏆 Alternatif Terbaik</h3>
        <h2 class="text-success"><?= htmlspecialchars($terbaik['nama']) ?></h2>
        <h4 class="text-primary"><?= number_format($terbaik['nilai'], 6) ?></h4>
        <p class="text-muted">Nilai preferensi tertinggi menunjukkan platform AI paling direkomendasikan</p>
    </div>
</div>
<?php endif; ?>

<!-- ===================== RANKING ===================== -->
<div class="card card-modern">
    <div class="card-header">
        Langkah 8 — Ranking Alternatif
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Alternatif diurutkan berdasarkan nilai preferensi V dari terbesar ke terkecil.</p>

        <?php foreach($rankingData as $i => $r): ?>
        <?php
        $persen = $r['nilai'] * 100;
        if($i==0){ $warna="bg-success"; $icon="🏆"; }
        elseif($i==1){ $warna="bg-primary"; $icon="🥈"; }
        elseif($i==2){ $warna="bg-info"; $icon="🥉"; }
        else{ $warna="bg-secondary"; $icon="#".($i+1); }
        ?>
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-2">
                <strong><?= $icon ?> <?= htmlspecialchars($r['nama']) ?></strong>
                <span>V = <?= number_format($r['nilai'],6) ?> (<?= number_format($persen,2) ?>%)</span>
            </div>
            <div class="progress" style="height:35px;border-radius:20px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated <?= $warna ?> bar-ranking"
                     data-width="<?= $persen ?>">
                    <?= number_format($persen,2) ?>%
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>

</div>

<?php include "layout/footer.php"; ?>

<script>
window.onload = function(){
    document.querySelectorAll('.bar-ranking').forEach(function(bar){
        bar.style.width = bar.dataset.width + '%';
    });
};
</script>