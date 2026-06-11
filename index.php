<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "db.php";

if(isset($_POST['login'])){
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $query = mysqli_query(
        $koneksi,
        "SELECT * FROM tbl_admin WHERE Username='$username'"
    );

    if(mysqli_num_rows($query) > 0){
        $data = mysqli_fetch_assoc($query);
        $valid = password_verify($password, $data['Password'])
                || ($password === $data['Password']);

        if($valid){
            $_SESSION['username'] = $data['Username'];
            $_SESSION['nama']     = $data['Nama'];
            $_SESSION['id_admin'] = $data['id_admin'];
            $_SESSION['notif_toast'] = 'login';
            header("Location: dashboard.php");
            exit;
        }
    }

    echo "<script>alert('Login gagal! Username atau Password salah.');</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SPK AI Platform</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

<!-- TOAST NOTIF (untuk notif logout) -->
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

<div class="login-container">
    <div class="login-card">
        <div class="hero-box">
            <i class="fas fa-brain ai-icon"></i>
            <h1>AI Platform DSS</h1>
            <h5>AHP-TOPSIS Method</h5>
        </div>

        <form method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" required style="width:100%;padding-right:45px;">
                    <span onclick="togglePassword()" style="position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;">
                        <i id="eyeIcon" class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" name="login" class="btn-login">Login</button>
        </form>

        <div class="links">
            <a href="signup.php">Sign Up</a>
            <a href="forgot_password.php">Forgot Password?</a>
        </div>
    </div>
</div>

<script>
function togglePassword(){
    let password = document.getElementById('password');
    let eye = document.getElementById('eyeIcon');
    if(password.type === 'password'){
        password.type = 'text';
        eye.classList.remove('fa-eye');
        eye.classList.add('fa-eye-slash');
    } else {
        password.type = 'password';
        eye.classList.remove('fa-eye-slash');
        eye.classList.add('fa-eye');
    }
}

function showToast(msg, icon){
    icon = icon || 'fa-check-circle';
    const toast    = document.getElementById('toastNotif');
    const toastMsg  = document.getElementById('toastMsg');
    const toastIcon = document.getElementById('toastIcon');

    toastMsg.textContent = msg;
    toastIcon.className  = 'fas ' + icon;
    toastIcon.style.color = '#ffcc80';

    toast.style.opacity   = '1';
    toast.style.transform = 'translateX(-50%) translateY(0)';

    setTimeout(function(){
        toast.style.opacity   = '0';
        toast.style.transform = 'translateX(-50%) translateY(-80px)';
    }, 2500);
}

// Cek cookie notif logout
window.addEventListener('load', function(){
    const cookie = document.cookie.split(';').find(c => c.trim().startsWith('notif_toast='));
    if(cookie){
        const val = cookie.split('=')[1].trim();
        if(val === 'logout'){
            showToast('👋 Anda telah keluar. Sampai jumpa!', 'fa-right-from-bracket');
        }
        document.cookie = 'notif_toast=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/';
    }
});
</script>

</body>
</html>