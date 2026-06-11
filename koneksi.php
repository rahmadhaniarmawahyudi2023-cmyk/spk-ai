<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "spk-ai"
);

if(!$conn){
    die("Koneksi gagal : ".mysqli_connect_error());
}
?>