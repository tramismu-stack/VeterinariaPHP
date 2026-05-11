<?php
require 'db.php';

// Llegim l'id de la URL de forma segura (evita el Warning)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Comprovem que l'id és vàlid
if ($id <= 0) {
    header('Location: Propietaris.php');
    exit;
}

// 1. COMPROVACIÓ: Té mascotes aquest propietari?
$check_sql = "SELECT COUNT(*) AS total FROM animals WHERE id_propietari = $id";
$check_res = mysqli_query($conn, $check_sql);

if ($check_res) {
    $row = mysqli_fetch_assoc($check_res);
    if ($row['total'] > 0) {
        // Si té mascotes (> 0), redirigim al llistat amb l'error personalitzat
        header('Location: Propietaris.php?error_mascotes=1');
        exit;
    }
}

// 2. Si no en té, executem el DELETE
$sql = "DELETE FROM propietaris WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    header('Location: Propietaris.php?esborrat=1');
} else {
    header('Location: Propietaris.php?error=' . urlencode(mysqli_error($conn)));
}
exit;
?>