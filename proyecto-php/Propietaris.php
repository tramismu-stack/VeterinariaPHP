<?php
require 'db.php';

$sql = "SELECT pr.id, pr.nom as Nom, pr.telefon as Telefon, pr.email as Email, pr.adreca as Adreça, GROUP_CONCAT(DISTINCT an.nom SEPARATOR ', ') AS Mascotes FROM propietaris pr LEFT JOIN animals an ON pr.id = an.id_propietari GROUP BY pr.id, pr.nom";
$res = mysqli_query($conn, $sql);
if (!$res) { die('Error: ' . mysqli_error($conn)); }
?>

<?php require 'header.php'; ?>

<?php
// Missatges d'èxit i d'error
$missatge = '';
$tipus = 'ok';

if (isset($_GET['ok'])) $missatge = 'Propietari guardat correctament!';
if (isset($_GET['esborrat'])) $missatge = 'Propietari esborrat correctament!';
if (isset($_GET['actualitzat'])) $missatge = 'Propietari actualitzat correctament!';

// Error de mascotes
if (isset($_GET['error_mascotes'])) {
    $missatge = '⚠️ No pots esborrar aquest propietari perquè encara té mascotes assignades.';
    $tipus = 'error';
}

if (isset($_GET['error'])) {
    $missatge = 'No s\'ha pogut esborrar: ' . htmlspecialchars($_GET['error']);
    $tipus = 'error';
}
?>

<?php if ($missatge): ?>
<p class='missatge-<?php echo $tipus; ?>'><?php echo $missatge; ?></p>
<?php endif; ?>

<h1>Propietaris <a class='btn-editar' href='Insert_Propietaris.php'>+</a></h1>
<table>
<thead>
    <tr>
        <?php foreach (mysqli_fetch_fields($res) as $c) {
            echo '<th>' . $c->name . '</th>'; 
        } ?>
        <th>Accions</th>
    </tr>
</thead>
<tbody>
    <?php while ($fila = mysqli_fetch_assoc($res)) { ?>
    <tr>
        <?php foreach ($fila as $valor) {
            echo '<td>' . htmlspecialchars($valor ?? '') . '</td>';
        } ?>    
        <td>
            <a href='update_propietaris.php?id=<?php echo $fila['id']; ?>' class='btn-editar'>&#9998;</a>
            <a href='borrar_propietaris.php?id=<?php echo $fila['id']; ?>' class='btn-esborrar'>&#10005;</a>
        </td>
    </tr>
    <?php } ?>
</tbody>
</table>
<?php require 'footer.php'; ?>