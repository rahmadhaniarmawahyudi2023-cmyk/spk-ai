<?php

session_start();
include "db.php";

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Cek apakah ID dikirim
if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    if (isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    // Hapus data pairwise dulu
    mysqli_query($koneksi,
        "DELETE FROM tbl_pairwise_responden WHERE id_responden = $id"
    );

    // Hapus data EV jika ada
    mysqli_query($koneksi,
        "DELETE FROM tbl_ev_responden WHERE id_responden = $id"
    );

    // Baru hapus responden
    $hapus = mysqli_query($koneksi,
        "DELETE FROM tbl_responden WHERE id_responden = $id"
    );

    if ($hapus) {
        echo "<script>
                alert('Data responden berhasil dihapus');
                window.location='responden.php';
              </script>";
    } else {
        echo "<script>
                alert('Gagal menghapus data responden');
                window.location='responden.php';
              </script>";
    }

    // Hapus data responden berdasarkan ID
    $hapus = mysqli_query(
        $koneksi,
        "DELETE FROM tbl_responden WHERE id_responden = $id"
    );

    if ($hapus) {

        echo "<script>
                alert('Data responden berhasil dihapus');
                window.location='responden.php';
              </script>";

    } else {

        echo "<script>
                alert('Gagal menghapus data responden');
                window.location='responden.php';
              </script>";

    }

} else {

    echo "<script>
            alert('ID responden tidak ditemukan');
            window.location='responden.php';
          </script>";

}
}

?>