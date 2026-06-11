<?php

$koneksi = mysqli_connect(
    "localhost",
    "root",
    "",
    "spk-ai"
);

if(!$koneksi){
    die("Koneksi gagal : ".mysqli_connect_error());
}
?>