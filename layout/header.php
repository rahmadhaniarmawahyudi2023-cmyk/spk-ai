<?php
include_once "db.php";
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
?>
<?php $currentPage = basename($_SERVER['PHP_SELF']); ?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AI Platform DSS</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*{
    font-family:'Poppins',sans-serif;
    font-size:12px;
}

body{
    margin:0;
    background:
    linear-gradient(
        135deg,
        #f5f1ea,
        #efe7dc
    );
}

/* ======================
   HEADING
====================== */
h1{font-size:28px;}
h2{font-size:22px;}
h3{font-size:20px;}
h4{font-size:16px;}
h5{font-size:16px;}

/* ======================
   SIDEBAR
====================== */
.sidebar{
    position:fixed;
    left:0;
    top:0;
    width:260px;
    height:100vh;
    background:
    linear-gradient(
        180deg,
        #3e2723,
        #5d4037,
        #795548
    );

    color:white;
    overflow-y:auto;
    transition:all .3s ease;
    z-index:1000;
    box-shadow:
    5px 0 25px rgba(0,0,0,.25);
}

.content{
    margin-left:260px;
    padding:30px;
    transition:.3s;
}

.sidebar{
    width:260px;
    transition:all .3s ease;
}

/* saat sidebar ditutup */
.sidebar.collapsed{
    width:80px;
}

.sidebar.collapsed .logo h4,
.sidebar.collapsed .logo small,
.sidebar.collapsed .sidebar-menu small,
.sidebar.collapsed .sidebar-menu a span,
.sidebar.collapsed .admin-card div,
.sidebar.collapsed .admin-card small{
    display:none;
}

.sidebar.collapsed .sidebar-menu a{
    justify-content:center;
}

.sidebar.collapsed .admin-card{
    padding:10px;
}

.sidebar.collapsed .admin-card i{
    margin-bottom:0;
}

.content.expanded{
    margin-left:80px;
}

.logo{
    text-align:center;
    padding:25px 15px;
    border-bottom:1px solid rgba(255,255,255,.08);
}

.logo i{
    font-size:52px;
    color:#ffcc80;
    margin-bottom:12px;
}

.logo h4{
    margin:0;
    font-size:24px;
    font-weight:700;
    font-family:'Playfair Display',serif;
}

.logo small{
    color:#cbd5e1;
}

.sidebar-menu{
    padding:15px;
}

.sidebar-menu small{
    display:block;
    color:#94a3b8;
    font-size:11px;
    font-weight:700;
    letter-spacing:1px;
    margin-bottom:10px;
    margin-top:10px;
}

.sidebar-menu a{
    display:flex;
    align-items:center;
    gap:12px;
    padding:12px 15px;
    color:white;
    text-decoration:none;
    border-radius:10px;
    margin-bottom:5px;
    transition:.3s;
}

.sidebar-menu a:hover{
    background:#334155;
    color:white;
    transform:translateX(5px);
}

.sidebar-menu hr{
    border-color:rgba(255,255,255,.08);
    margin:15px 0;
}

/* ======================
   ACTIVE MENU
====================== */
.active-menu{
    background:
    linear-gradient(
        135deg,
        #8d6e63,
        #5d4037
    ) !important;
    color:white !important;
    font-weight:600;
    border-left:4px solid #ffcc80;
    box-shadow:
    0 8px 20px rgba(93,64,55,.35);
    transform:translateX(4px);
}

/* Saat sidebar collapse */
.sidebar.collapsed a span,
.sidebar.collapsed .admin-card span,
.sidebar.collapsed .admin-card div,
.sidebar.collapsed .logo h4,
.sidebar.collapsed .logo small,
.sidebar.collapsed .sidebar-menu small{
    display:none;
}

/* tombol menu menjadi rata tengah */
.sidebar.collapsed .sidebar-menu a{
    justify-content:center;
    padding:12px;
}

/* tombol logout khusus */
.sidebar.collapsed .logout-btn{
    justify-content:center;
}

.sidebar.collapsed .logout-btn span{
    display:none;
}

/* ======================
   ADMIN CARD
====================== */
.admin-card{
    background:#6d4c41;
    border-radius:12px;
    padding:15px;
    text-align:center;
    color:white;
}

.admin-card i{
    font-size:42px;
    color:#ffcc80;
    margin-bottom:10px;
}

.admin-card small{
    color:#cbd5e1;
}

a.active-menu .admin-card {
    background: transparent;
}

/* ======================
   CONTENT
====================== */
.content{
    transition:all .3s ease;
}

/* ======================
   PAGE TITLE
====================== */

.page-title{
    font-weight:700;
    color:#212529;
}

/* ======================
   CARD
====================== */
.card-modern{
    border:none;
    border-radius:20px;
    background:#fffaf5;
    box-shadow:
    0 10px 25px rgba(0,0,0,.08);
}

/* ======================
   TABLE
====================== */

.table{
    margin-bottom:0;
}

.table thead{
    background:#0d6efd;
}

.table thead th{
    color:white;
    border:none;
    padding:14px;
}

.table tbody tr:hover{
    background:#f8fafc;
}

.table td{
    vertical-align:middle;
}

/* ======================
   FORM
====================== */

.form-control,
.form-select{
    border-radius:10px;
}

/* ======================
   BUTTON
====================== */
.btn{
    border-radius:10px;
}

.menu-btn{
    position:fixed;
    top:20px;
    left:20px;
    width:50px;
    height:50px;
    border:none;
    border-radius:14px;
    background:
    linear-gradient(
        135deg,
        #6d4c41,
        #4e342e
    );

    color:white;
    font-size:18px;
    cursor:pointer;
    z-index:2000;
    box-shadow:
    0 8px 20px rgba(0,0,0,.25);
}

.menu-btn:hover{
    transform:scale(1.05);
}

/* ======================
   RANKING
====================== */

.bar-ranking{
    width:0;
    transition:all 2s ease;
    font-weight:600;
}

/* ======================
   SCROLLBAR
====================== */

::-webkit-scrollbar{
    width:8px;
}

::-webkit-scrollbar-thumb{
    background:#64748b;
    border-radius:10px;
}

.card-header{
    background:
    linear-gradient(
        135deg,
        #6d4c41,
        #4e342e
    ) !important;
    color:white !important;
}

.table thead th{
    background:
    linear-gradient(
        135deg,
        #6d4c41,
        #4e342e
    ) !important;
    color:white;
}

.btn-primary{
    background:
    linear-gradient(
        135deg,
        #8d6e63,
        #5d4037
    ) !important;
    border:none;
}

.btn-success{
    background:
    linear-gradient(
        135deg,
        #a1887f,
        #6d4c41
    ) !important;
    border:none;
}

/* =========================
   TOGGLE SIDEBAR
========================= */

.menu-toggle{
    position:fixed;
    top:20px;
    left:20px;

    width:50px;
    height:50px;

    border:none;
    border-radius:14px;

    background:
    linear-gradient(
        135deg,
        #6d4c41,
        #4e342e
    );

    color:white;

    font-size:18px;

    cursor:pointer;

    z-index:2001;

    box-shadow:
    0 8px 20px rgba(0,0,0,.25);
}

.menu-toggle:hover{
    transform:scale(1.05);
}

.sidebar.collapsed{
    width:80px;
}

.sidebar.collapsed .logo h4,
.sidebar.collapsed .logo small,
.sidebar.collapsed .sidebar-menu small,
.sidebar.collapsed .sidebar-menu a span,
.sidebar.collapsed .admin-card div,
.sidebar.collapsed .admin-card span{
    display:none;
}

.sidebar.collapsed .sidebar-menu a{
    justify-content:center;
}

.sidebar.collapsed .sidebar-menu a i{
    margin:0;
}

.content{
    margin-left:260px;
    padding:30px;
    transition:all .3s ease;
}

.content.expanded{
    margin-left:80px;
}

.overlay{
    display:none;
}
</style>

</head>

<body>
    <!-- NOTIF TOAST -->
<div id="toastNotif" style="
    position:fixed;
    top:30px;
    left:50%;
    transform:translateX(-50%) translateY(-80px);
    background:linear-gradient(135deg,#3e2723,#5d4037);
    color:white;
    padding:14px 28px;
    border-radius:16px;
    font-size:13px;
    font-weight:600;
    z-index:99999;
    box-shadow:0 8px 30px rgba(0,0,0,.3);
    display:flex;
    align-items:center;
    gap:10px;
    transition:transform .4s cubic-bezier(.34,1.56,.64,1), opacity .4s ease;
    opacity:0;
    white-space:nowrap;
">
    <i id="toastIcon" class="fas fa-check-circle" style="color:#ffcc80;font-size:18px;"></i>
    <span id="toastMsg">Notifikasi</span>
</div>

<script>
function showToast(msg, icon){
    icon = icon || 'fa-check-circle';
    const toast = document.getElementById('toastNotif');
    const toastMsg  = document.getElementById('toastMsg');
    const toastIcon = document.getElementById('toastIcon');

    toastMsg.textContent  = msg;
    toastIcon.className   = 'fas ' + icon + ' ' + 'me-1';
    toastIcon.style.color = '#ffcc80';

    // Masuk
    toast.style.opacity   = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';

    // Keluar otomatis setelah 2.5 detik
    setTimeout(function(){
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(-50%) translateY(-80px)';
    }, 2500);
}
</script>
<!-- LOADING SCREEN -->
<div id="loadingScreen" style="
    position:fixed;
    inset:0;
    background:linear-gradient(135deg,#3e2723,#5d4037);
    z-index:9999;
    display:none;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:16px;
">
    <i class="fas fa-brain" style="font-size:52px;color:#ffcc80;animation:pulse 1.2s infinite;"></i>
    <div id="loadingText" style="color:white;font-size:14px;font-weight:600;letter-spacing:1px;">Memuat...</div>
    <div style="
        width:180px;height:4px;
        background:rgba(255,255,255,.15);
        border-radius:10px;
        overflow:hidden;
    ">
        <div id="loadingBar" style="
            width:0%;height:100%;
            background:linear-gradient(90deg,#ffcc80,#ff8f00);
            border-radius:10px;
            transition:width .3s ease;
        "></div>
    </div>
</div>

<style>
@keyframes pulse {
    0%,100%{transform:scale(1);opacity:1;}
    50%{transform:scale(1.15);opacity:.7;}
}
</style>

<script>
function showLoading(text){
    text = text || 'Memuat...';
    const screen = document.getElementById('loadingScreen');
    const bar    = document.getElementById('loadingBar');
    const label  = document.getElementById('loadingText');

    label.textContent = text;
    bar.style.width   = '0%';
    screen.style.display = 'flex';

    let width = 0;
    window._loadingInterval = setInterval(function(){
        width += Math.random() * 30;
        if(width >= 90) width = 90;
        bar.style.width = width + '%';
    }, 120);
}

function hideLoading(){
    const screen = document.getElementById('loadingScreen');
    const bar    = document.getElementById('loadingBar');
    clearInterval(window._loadingInterval);
    bar.style.width = '100%';
    setTimeout(function(){
        screen.style.opacity = '0';
        setTimeout(function(){
            screen.style.display = 'none';
            screen.style.opacity = '1';
        }, 300);
    }, 200);
}

// Trigger otomatis pada form submit
document.addEventListener('submit', function(e){
    const form = e.target;

    // Tentukan teks loading berdasarkan konteks
    let text = 'Menyimpan...';
    if(form.action && form.action.includes('logout')) text = 'Keluar...';

    showLoading(text);
});

// Trigger pada link logout & login
document.addEventListener('click', function(e){
    const a = e.target.closest('a');
    if(!a) return;

    const href = a.getAttribute('href') || '';

    if(href.includes('logout.php')){
        showLoading('Keluar dari sistem...');
    } else if(href.includes('login') || href.includes('index.php')){
        showLoading('Masuk...');
    }
});
</script>

<div id="overlay" class="overlay"></div>
    <button id="menuBtn" class="menu-toggle">
        <i class="fas fa-bars"></i>
    </button>
<div id="sidebar" class="sidebar">

    <div class="logo">
        <i class="fas fa-brain"></i>
        <h4>AI Platform DSS</h4>
        <small>AHP-TOPSIS Method</small>
    </div>

    <div class="sidebar-menu">

        <small>BERANDA</small>

        <a href="dashboard.php" class="<?= $currentPage == 'dashboard.php' ? 'active-menu' : '' ?>">
            <i class="fas fa-chart-pie"></i>
            <span>Dashboard Sistem</span>
        </a>

        <a href="responden.php" class="<?= $currentPage == 'responden.php' ? 'active-menu' : '' ?>">
            <i class="fas fa-users"></i>
            <span>Data Responden</span>
        </a>

        <a href="alternatif.php" class="<?= $currentPage == 'alternatif.php' ? 'active-menu' : '' ?>">
            <i class="fas fa-robot"></i>
            <span>Platform AI</span>
        </a>

        <a href="kriteria_penelitian.php" class="<?= $currentPage == 'kriteria_penelitian.php' ? 'active-menu' : '' ?>">
            <i class="fas fa-list-check"></i>
            <span>Kriteria Penelitian</span>
        </a>

        <a href="kriteria.php" class="<?= $currentPage == 'kriteria.php' ? 'active-menu' : '' ?>">
            <i class="fas fa-weight-scale"></i>
            <span>Bobot AHP</span>
        </a>

        <a href="ahp_responden.php" class="<?= $currentPage == 'ahp_responden.php' ? 'active-menu' : '' ?>">
            <i class="fas fa-users-gear"></i>
            <span>AHP Per Responden</span>
        </a>

        <a href="perhitungan.php" class="<?= $currentPage == 'perhitungan.php' ? 'active-menu' : '' ?>">
            <i class="fas fa-calculator"></i>
            <span>Perhitungan TOPSIS</span>
        </a>

        <a href="input_penilaian.php" class="<?= $currentPage == 'input_penilaian.php' ? 'active-menu' : '' ?>">
            <i class="fas fa-table"></i>
            <span>Input Penilaian</span>
        </a>

        <hr>

        <small>PENGELOLA SISTEM</small>

        <?php
        $foto_sidebar = null;
        $username_sb  = $_SESSION['username'] ?? '';
        if ($username_sb) {
            $adm_sb = mysqli_fetch_assoc(mysqli_query($koneksi,
                "SELECT foto FROM tbl_admin WHERE Username='$username_sb' LIMIT 1"
            ));
            if (!empty($adm_sb['foto'])) {
                $f_abs = dirname(__DIR__) . "/uploads/profil/" . $adm_sb['foto'];
                if (file_exists($f_abs)) {
                    $foto_sidebar = "/spk-ai/uploads/profil/" . $adm_sb['foto'];
                }
            }
        }
        ?>
        <a href="pengelola.php" class="<?= $currentPage == 'pengelola.php' ? 'active-menu' : '' ?>">
            <div class="admin-card">
                <?php if ($foto_sidebar): ?>
                    <img src="<?= $foto_sidebar ?>?v=<?= time() ?>"
                        style="width:60px;height:60px;border-radius:50%;object-fit:cover;
                                border:3px solid #ffcc80;margin-bottom:8px;display:block;margin-left:auto;margin-right:auto;">
                <?php else: ?>
                    <i class="fas fa-user-circle"></i>
                <?php endif; ?>
                <div><strong><?= htmlspecialchars($username_sb) ?></strong></div>
                <span>Pengelola Sistem</span>
            </div>
        </a>

        <br>

        <small>AKUN</small>

        <a href="logout.php"
        class="logout-btn"
        style="background:#dc3545;">
            <i class="fas fa-right-from-bracket"></i>
            <span>Keluar</span>
        </a>

    </div>

</div>

<div id="content" class="content">
