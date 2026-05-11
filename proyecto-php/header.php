<!DOCTYPE html>
<html lang='ca'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Clínica Veterinària</title>
    <link rel='stylesheet' href='css/estils.css?v=<?php echo time(); ?>'>
</head>
<body>

<?php
// Detectem el nom del fitxer actual per marcar l'enllaç actiu
$pagina_actual = basename($_SERVER['PHP_SELF']);
?>

<nav>
    <a href='resum.php' class="<?php echo ($pagina_actual == 'resum.php') ? 'active' : ''; ?>">Resum General</a>
    
    <a href='Propietaris.php' class="<?php echo ($pagina_actual == 'Propietaris.php') ? 'active' : ''; ?>">Propietaris</a>
    
    <a href='Animals.php' class="<?php echo ($pagina_actual == 'Animals.php') ? 'active' : ''; ?>">Animals</a>
    
    <a href='Visites.php' class="<?php echo ($pagina_actual == 'Visites.php') ? 'active' : ''; ?>">Visites</a>
</nav>

<div class="contenidor">