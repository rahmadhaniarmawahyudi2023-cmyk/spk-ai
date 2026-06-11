<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "db.php";

if(isset($_POST['daftar'])){
    $nama     = mysqli_real_escape_string($koneksi,$_POST['nama']);
    $username = mysqli_real_escape_string($koneksi,$_POST['username']);
    $email    = mysqli_real_escape_string($koneksi,$_POST['email']);
    $cek = mysqli_query(
        $koneksi,
        "SELECT * FROM tbl_admin
        WHERE username='$username'
        OR email='$email'"
    );

    if(mysqli_num_rows($cek) > 0){
        echo "
        <script>
            alert('Username sudah digunakan!');
        </script>
        ";
    }else{
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        mysqli_query(
            $koneksi,
            "INSERT INTO tbl_admin
            (Nama, Username, Email, Password)
            VALUES
            ('$nama','$username','$email','$hash')"
        );

        echo "
        <script>
            alert('Registrasi berhasil!');
            window.location='index.php';
        </script>
        ";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Sign Up</title>
<link
href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">
<link
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
rel="stylesheet">
<style>
body{
    margin:0;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:
    linear-gradient(
        135deg,
        #4e342e,
        #6d4c41,
        #8d6e63
    );
    font-family:'Poppins',sans-serif;
}

.signup-card{

    width:380px;

    background:white;

    padding:30px;

    border-radius:20px;

    box-shadow:
    0 15px 40px rgba(0,0,0,.2);
}

.logo-box{

    text-align:center;

    margin-bottom:25px;
}

.logo-box i{

    font-size:55px;

    color:#6d4c41;
}

.logo-box h3{

    margin-top:10px;

    font-weight:700;
}

.form-control{

    border-radius:10px;
}

.btn-daftar{

    width:100%;

    border:none;

    padding:12px;

    border-radius:10px;

    color:white;

    font-weight:600;

    background:
    linear-gradient(
        135deg,
        #6d4c41,
        #4e342e
    );
}

.btn-daftar:hover{

    opacity:.9;
}

.login-link{

    text-align:center;

    margin-top:15px;
}

.login-link a{

    text-decoration:none;
}

</style>

</head>
<body>

<div class="signup-card">

    <div class="logo-box">

        <i class="fas fa-user-plus"></i>

        <h3>Sign Up</h3>

        <small>
            Buat akun administrator baru
        </small>

    </div>

    <form method="post">

        <div class="mb-3">

            <label>Nama Lengkap</label>

            <input
                type="text"
                name="nama"
                class="form-control"
                required>

        </div>

        <div class="mb-3">

            <label>Username</label>

            <input
                type="text"
                name="username"
                class="form-control"
                required>

        </div>

        <div class="mb-3">

            <label>Email</label>

            <input
                type="email"
                name="email"
                class="form-control"
                required>

        </div>

        <div class="mb-3">

            <label>Password</label>

            <input
                type="password"
                name="password"
                class="form-control"
                required>

        </div>

        <button
            type="submit"
            name="daftar"
            class="btn-daftar">

            Daftar

        </button>

    </form>

    <div class="login-link">

        Sudah punya akun?

        <a href="index.php">
            Login
        </a>

    </div>

</div>

</body>
</html>