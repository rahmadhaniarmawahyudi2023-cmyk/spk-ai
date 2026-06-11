<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<div class="logo">
<i class="fas fa-brain"></i>

<h4>AI Platform DSS</h4>
<small>AHP-TOPSIS Method</small>
</div>
<div class="sidebar-menu">

<small>BERANDA</small>
<a href="dashboard.php"
   class="<?= ($current_page == 'dashboard.php') ? 'active-menu' : ''; ?>">
    <i class="fas fa-chart-pie"></i>
    Dashboard Sistem
</a>
<hr>

<small>DATA PENELITIAN</small>

<a href="ahp_responden.php"
   class="<?= ($current_page == 'ahp_responden.php') ? 'active-menu' : ''; ?>">
    <i class="fas fa-calculator"></i>
    AHP Responden
</a>

<a href="responden.php"
   class="<?= in_array($current_page,['responden.php','tambah_responden.php','edit_responden.php']) ? 'active-menu' : ''; ?>">
    <i class="fas fa-users"></i>
    Data Responden
</a>
<a href="alternatif.php"
   class="<?= ($current_page == 'alternatif.php') ? 'active-menu' : ''; ?>">
    <i class="fas fa-robot"></i>
    Platform AI
</a>
<a href="kriteria_penelitian.php"
   class="<?= ($current_page == 'kriteria_penelitian.php') ? 'active-menu' : ''; ?>">
    <i class="fas fa-list-check"></i>
    Kriteria Penelitian
</a>
<hr>

<small>ANALISIS AHP</small>

<a href="kriteria.php"
   class="<?= ($current_page == 'kriteria.php') ? 'active-menu' : ''; ?>">
    <i class="fas fa-weight-scale"></i>
    Bobot AHP
</a>

<a href="bobot_ahp.php">
    <i class="fas fa-weight-scale"></i>
    Kelola Bobot AHP
</a>

<hr>

<small>ANALISIS TOPSIS</small>

<a href="perhitungan.php"
   class="<?= ($current_page == 'perhitungan.php') ? 'active-menu' : ''; ?>">
    <i class="fas fa-calculator"></i>
    Perhitungan TOPSIS
</a>

<hr>

<small>PENGELOLA SISTEM</small>

<div class="admin-card">

    <i class="fas fa-user-circle"></i>

    <div>
        <strong><?= $_SESSION['username']; ?></strong>
    </div>

    <small>Profil Admin</small>

</div>

<br>

<small>AKUN</small>

<a href="logout.php" style="background:#dc3545;">
    <i class="fas fa-right-from-bracket"></i>
    Keluar
</a>
```

</div>
