<?php
session_start();
include "db.php";

if(isset($_POST['register'])){

    $nama     = $_POST['nama'];
    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = md5($_POST['password']);

    $cek = mysqli_query(
        $koneksi,
        "SELECT * FROM tbl_daftar
         WHERE username='$username'
         OR email='$email'"
    );

    if(mysqli_num_rows($cek)>0){

        echo "<script>
        alert('Username atau Email sudah digunakan');
        </script>";

    }else{

        mysqli_query(
            $koneksi,
            "INSERT INTO tbl_daftar
            (nama,username,email,password)
            VALUES
            ('$nama','$username','$email','$password')"
        );

        echo "<script>
        alert('Registrasi berhasil');
        window.location='index.php';
        </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Registrasi Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:linear-gradient(
        135deg,
        #0f172a,
        #1e3a8a
    );
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.card{
    width:450px;
    padding:30px;
    border-radius:20px;
}

</style>

</head>
<body>

<div class="card shadow">

    <h3 class="text-center mb-4">
        Registrasi Admin
    </h3>

    <form method="post">

        <input
        type="text"
        name="nama"
        class="form-control mb-3"
        placeholder="Nama Lengkap"
        required>

        <input
        type="text"
        name="username"
        class="form-control mb-3"
        placeholder="Username"
        required>

        <input
        type="email"
        name="email"
        class="form-control mb-3"
        placeholder="Email"
        required>

        <input
        type="password"
        name="password"
        class="form-control mb-3"
        placeholder="Password"
        required>

        <button
        type="submit"
        name="register"
        class="btn btn-primary w-100">

        Daftar

        </button>

    </form>

    <div class="text-center mt-3">

        <a href="index.php">
            Kembali Login
        </a>

    </div>

</div>

</body>
</html>