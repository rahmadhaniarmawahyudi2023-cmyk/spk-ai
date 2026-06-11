<?php

session_start();
include "db.php";

if(!isset($_SESSION['username'])){
    header("Location:index.php");
    exit;
}

if(isset($_POST['simpan'])){

    $GM1 = (float)$_POST['GM1'];
    $GM2 = (float)$_POST['GM2'];
    $GM3 = (float)$_POST['GM3'];
    $GM4 = (float)$_POST['GM4'];
    $GM5 = (float)$_POST['GM5'];
    $GM6 = (float)$_POST['GM6'];

    $GMTotal =
        $GM1 + $GM2 + $GM3 +
        $GM4 + $GM5 + $GM6;

    if($GMTotal > 0){

        $C1 = $GM1 / $GMTotal;
        $C2 = $GM2 / $GMTotal;
        $C3 = $GM3 / $GMTotal;
        $C4 = $GM4 / $GMTotal;
        $C5 = $GM5 / $GMTotal;
        $C6 = $GM6 / $GMTotal;

    }else{

        $C1=$C2=$C3=
        $C4=$C5=$C6=0;
    }

    mysqli_query(
        $koneksi,
        "UPDATE tbl_kriteria SET

        GM1='$GM1',
        GM2='$GM2',
        GM3='$GM3',
        GM4='$GM4',
        GM5='$GM5',
        GM6='$GM6',

        GMTotal='$GMTotal',

        C1='$C1',
        C2='$C2',
        C3='$C3',
        C4='$C4',
        C5='$C5',
        C6='$C6',

        total='1'

        WHERE id_kriteria=1"
    );

    echo "
    <script>
    alert('Bobot berhasil diperbarui');
    window.location='bobot_ahp.php';
    </script>
    ";
}

$data =
mysqli_query(
    $koneksi,
    "SELECT * FROM tbl_kriteria
     WHERE id_kriteria=1"
);

$row =
mysqli_fetch_assoc($data);

include "layout/header.php";
?>

<h2 class="mb-4">
    ⚖️ Kelola Bobot AHP
</h2>

<div class="card card-modern">

    <div class="card-body">

        <form method="POST">

            <div class="row">

                <?php
                for($i=1;$i<=6;$i++){
                ?>

                <div class="col-md-4 mb-3">

                    <label>

                        GM<?= $i ?>

                    </label>

                    <input
                        type="number"
                        step="0.0001"
                        name="GM<?= $i ?>"
                        value="<?= $row['GM'.$i] ?>"
                        class="form-control"
                        required>

                </div>

                <?php } ?>

            </div>

            <button
                type="submit"
                name="simpan"
                class="btn btn-primary">

                Simpan Bobot

            </button>

        </form>

    </div>

</div>

<?php include "layout/footer.php"; ?>