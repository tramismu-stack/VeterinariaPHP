<?php
require 'db.php';

// Llegim l'id de la URL de forma segura
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Comprovem que l'id és vàlid
if ($id <= 0) {
    header('Location: Animals.php');
    exit;
}

// 1. COMPROVACIÓ: Té visites aquest animal al seu historial?
// Nota: Comprova que la teva columna a la taula visites es digui 'id_animal'. Si es diu diferent, canvia-ho aquí.
$check_sql = "SELECT COUNT(*) AS total FROM visites WHERE id_animal = $id";
$check_res = mysqli_query($conn, $check_sql);

if ($check_res) {
    $row = mysqli_fetch_assoc($check_res);
    if ($row['total'] > 0) {
        // Si té visites (> 0), redirigim al llistat amb l'error personalitzat que ja vam configurar
        header('Location: Animals.php?error_visites=1');
        exit;
    }
}

// 2. Si no té visites, executem el DELETE
$sql = "DELETE FROM animals WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    header('Location: Animals.php?esborrat=1');
} else {
    // Si hi ha un altre error de base de dades, el passem per la URL
    header('Location: Animals.php?error=' . urlencode(mysqli_error($conn)));
}
exit;
?>