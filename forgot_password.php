<?php

include "db.php";

if(isset($_POST['reset'])){

    $username = $_POST['username'];
    $email    = $_POST['email'];
    $password = md5($_POST['password']);

    $cek = mysqli_query(
        $koneksi,
        "SELECT *
         FROM tbl_daftar
         WHERE username='$username'
         AND email='$email'"
    );

    if(mysqli_num_rows($cek)>0){

        mysqli_query(
            $koneksi,
            "UPDATE tbl_daftar
             SET password='$password'
             WHERE username='$username'"
        );

        echo "<script>
        alert('Password berhasil direset');
        window.location='index.php';
        </script>";

    }else{

        echo "<script>
        alert('Username atau Email tidak cocok');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Reset Password</title>

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

        Reset Password

    </h3>

    <form method="post">

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
        placeholder="Password Baru"
        required>

        <button
        type="submit"
        name="reset"
        class="btn btn-danger w-100">

        Reset Password

        </button>

    </form>

</div>

</body>
</html>