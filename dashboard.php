<?php

include "db.php";
include "layout/header.php";

$jml_kriteria = mysqli_num_rows(
    mysqli_query($koneksi,"SELECT * FROM tbl_kriteria")
);

$jml_alternatif = mysqli_num_rows(
    mysqli_query($koneksi,"SELECT * FROM tbl_alternatif")
);

$jml_responden = mysqli_num_rows(
    mysqli_query($koneksi,"SELECT * FROM tbl_responden")
);

?>

<style>
.page-header { margin-bottom: 28px; }
.page-header h2 { font-size: 22px; font-weight: 700; color: #3e2723; letter-spacing: -0.3px; margin: 0; }
.page-header p { font-size: 13px; color: #8d6e63; margin-top: 4px; }

.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
.stat-card { border-radius: 18px; padding: 22px 20px; position: relative; overflow: hidden; }
.stat-card.kriteria   { background: linear-gradient(135deg, #5d4037, #3e2723); color: white; }
.stat-card.alternatif { background: linear-gradient(135deg, #795548, #5d4037); color: white; }
.stat-card.responden  { background: linear-gradient(135deg, #8d6e63, #6d4c41); color: white; }
.stat-icon   { font-size: 26px; margin-bottom: 12px; display: block; }
.stat-label  { font-size: 11px; font-weight: 600; letter-spacing: 1.2px; text-transform: uppercase; opacity: 0.75; margin-bottom: 6px; }
.stat-number { font-size: 42px; font-weight: 700; line-height: 1; margin-bottom: 6px; }
.stat-desc   { font-size: 12px; opacity: 0.7; }
.stat-badge  { position: absolute; top: 16px; right: 16px; background: rgba(255,255,255,0.15); border-radius: 8px; padding: 4px 10px; font-size: 11px; font-weight: 600; }

.info-card { background: #fffaf5; border-radius: 20px; padding: 26px; box-shadow: 0 4px 20px rgba(93,64,55,0.08); border: 1px solid rgba(141,110,99,0.1); margin-bottom: 20px; }
.info-card-header { display: flex; align-items: center; gap: 10px; margin-bottom: 16px; }
.info-card-header .icon { width: 38px; height: 38px; border-radius: 10px; background: linear-gradient(135deg, #8d6e63, #5d4037); display: flex; align-items: center; justify-content: center; font-size: 18px; }
.info-card-header h4 { font-size: 15px; font-weight: 700; color: #3e2723; margin: 0; }
.info-card p { font-size: 13px; color: #6d4c41; line-height: 1.8; margin: 0; }
.divider { height: 1px; background: linear-gradient(to right, rgba(141,110,99,0.3), transparent); margin: 20px 0; }

.meta-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.meta-box { background: linear-gradient(135deg, #f8f0e8, #f0e4d4); border-radius: 14px; padding: 16px; text-align: center; border: 1px solid rgba(141,110,99,0.15); }
.meta-box .meta-icon  { font-size: 22px; margin-bottom: 8px; }
.meta-box .meta-title { font-size: 11px; font-weight: 700; color: #8d6e63; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 4px; }
.meta-box .meta-val   { font-size: 14px; font-weight: 600; color: #3e2723; margin: 0; }

.alur-card { background: #fffaf5; border-radius: 20px; padding: 26px; box-shadow: 0 4px 20px rgba(93,64,55,0.08); border: 1px solid rgba(141,110,99,0.1); }
.alur-title { font-size: 14px; font-weight: 700; color: #3e2723; margin-bottom: 16px; }
.alur-steps { display: flex; align-items: center; }
.alur-step  { flex: 1; text-align: center; position: relative; }
.alur-step:not(:last-child)::after { content: '→'; position: absolute; right: -10px; top: 50%; transform: translateY(-60%); color: #8d6e63; font-size: 16px; font-weight: 700; }
.step-dot   { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #8d6e63, #5d4037); color: white; font-size: 16px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin: 0 auto 8px; box-shadow: 0 4px 12px rgba(93,64,55,0.3); }
.step-label { font-size: 11px; font-weight: 600; color: #5d4037; }
</style>

<div class="page-header">
    <h2>🤖 Dashboard SPK Pemilihan Platform AI</h2>
    <p>Sistem Pendukung Keputusan · Metode AHP–TOPSIS</p>
</div>

<div class="stats-grid">
    <div class="stat-card kriteria">
        <span class="stat-badge">AHP</span>
        <span class="stat-icon">📋</span>
        <div class="stat-label">Kriteria</div>
        <div class="stat-number"><?= $jml_kriteria ?></div>
        <div class="stat-desc">Total bobot kriteria</div>
    </div>
    <div class="stat-card alternatif">
        <span class="stat-badge">TOPSIS</span>
        <span class="stat-icon">🤖</span>
        <div class="stat-label">Alternatif AI</div>
        <div class="stat-number"><?= $jml_alternatif ?></div>
        <div class="stat-desc">Platform AI yang dinilai</div>
    </div>
    <div class="stat-card responden">
        <span class="stat-badge">Data</span>
        <span class="stat-icon">👥</span>
        <div class="stat-label">Responden</div>
        <div class="stat-number"><?= $jml_responden ?></div>
        <div class="stat-desc">Data responden aktif</div>
    </div>
</div>

<div class="info-card">
    <div class="info-card-header">
        <div class="icon">📖</div>
        <h4>Informasi Sistem</h4>
    </div>
    <p>Metode AHP digunakan untuk memperoleh bobot kriteria berdasarkan hasil pengolahan kuesioner responden menggunakan Geometric Mean. Selanjutnya bobot tersebut digunakan pada metode TOPSIS untuk menentukan peringkat platform Artificial Intelligence (AI) terbaik berdasarkan enam kriteria penilaian.</p>
    <div class="divider"></div>
    <div class="meta-grid">
        <div class="meta-box">
            <div class="meta-icon">📌</div>
            <div class="meta-title">Metode</div>
            <div class="meta-val">AHP – TOPSIS</div>
        </div>
        <div class="meta-box">
            <div class="meta-icon">🤖</div>
            <div class="meta-title">Alternatif</div>
            <div class="meta-val"><?= $jml_alternatif ?> Platform AI</div>
        </div>
        <div class="meta-box">
            <div class="meta-icon">🎯</div>
            <div class="meta-title">Tujuan</div>
            <div class="meta-val">Rekomendasi AI Terbaik</div>
        </div>
    </div>
</div>

<div class="alur-card">
    <div class="alur-title">⚡ Alur Perhitungan</div>
    <div class="alur-steps">
        <div class="alur-step">
            <div class="step-dot">1</div>
            <div class="step-label">Kuesioner</div>
        </div>
        <div class="alur-step">
            <div class="step-dot">2</div>
            <div class="step-label">AHP</div>
        </div>
        <div class="alur-step">
            <div class="step-dot">3</div>
            <div class="step-label">Bobot</div>
        </div>
        <div class="alur-step">
            <div class="step-dot">4</div>
            <div class="step-label">TOPSIS</div>
        </div>
        <div class="alur-step">
            <div class="step-dot">5</div>
            <div class="step-label">Ranking</div>
        </div>
    </div>
</div>

<?php include "layout/footer.php"; ?>