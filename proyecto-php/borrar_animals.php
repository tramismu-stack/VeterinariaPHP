<?php
require 'db.php';

// Llegim l'id de la URL de forma segura
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Comprovem que l'id és vàlid
if ($id <= 0) {
    header('Location: Animals.php');
    exit;
}

// 1. BORRAR LES VISITES DE L'ANIMAL
$delete_visites_sql = "DELETE FROM visites WHERE id_animal = $id";

if (!mysqli_query($conn, $delete_visites_sql)) {
    // Si hi ha error en borrar les visites
    header('Location: Animals.php?error=' . urlencode(mysqli_error($conn)));
    exit;
}

// 2. BORRAR L'ANIMAL
$delete_animal_sql = "DELETE FROM animals WHERE id = $id";

if (mysqli_query($conn, $delete_animal_sql)) {
    // Éxit: animal i visites esborrats
    header('Location: Animals.php?esborrat=1');
} else {
    // Error en borrar l'animal
    header('Location: Animals.php?error=' . urlencode(mysqli_error($conn)));
}
exit;
?>