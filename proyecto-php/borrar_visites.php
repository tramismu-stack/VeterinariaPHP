<?php
require 'db.php';

// Llegim l'id de la URL de forma segura
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Comprovem que l'id és vàlid
if ($id <= 0) {
    header('Location: Visites.php');
    exit;
}

// Executem el DELETE directament
$sql = "DELETE FROM visites WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    // Redirigim amb el missatge d'èxit
    header('Location: Visites.php?esborrat=1');
} else {
    // Si falla, enviem l'error
    header('Location: Visites.php?error=' . urlencode(mysqli_error($conn)));
}
exit;
?>