<?php
include 'config/db.php';

if (isset($_POST['simpan_saldo'])) {

    $kas         = $_POST['kas'];
    $bank        = $_POST['bank'];
    $modal       = $_POST['modal'];
    $persediaan  = $_POST['persediaan']; // DARI FORM (AUTO JS)

    mysqli_query($conn, "
        INSERT INTO tb_saldo_awal 
        (kas, bank, modal, persediaan)
        VALUES 
        ('$kas', '$bank', '$modal', '$persediaan')
    ");

    header("Location: saldo_awal.php");
}
