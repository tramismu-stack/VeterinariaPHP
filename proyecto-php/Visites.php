<?php
require 'db.php';
$sql = "SELECT vi.id as ID, vi.data_visita as Data_Visita, vi.motiu as Motiu, vi.diagnostic as Diagnostic, vi.preu as Preu, an.nom as Nom, pr.nom as Propietari
FROM visites vi JOIN animals an ON vi.id_animal=an.id JOIN propietaris pr ON an.id_propietari = pr.id ORDER BY vi.id";
$res = mysqli_query($conn, $sql);
if (!$res) { die('Error: ' . mysqli_error($conn)); }
?>

<?php require 'header.php'; ?>

<?php
// Missatges d'èxit i d'error
$missatge = '';
$tipus = 'ok';

if (isset($_GET['ok'])) $missatge = 'Visita guardada correctament!';
if (isset($_GET['esborrat'])) $missatge = 'Visita esborrada correctament!';
if (isset($_GET['actualitzat'])) $missatge = 'Visita actualitzada correctament!';

if (isset($_GET['error'])) {
    $missatge = 'Hi ha hagut un error: ' . htmlspecialchars($_GET['error']);
    $tipus = 'error';
}
?>

<?php if ($missatge): ?>
<p class='missatge-<?php echo $tipus; ?>'><?php echo $missatge; ?></p>
<?php endif; ?>

<h1>Visites <a class='btn-editar' href='Insert_Visites.php'>+</a> </h1>
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
        <?php foreach ($fila as $v) { 
            echo '<td>' . htmlspecialchars($v ?? '') . '</td>'; 
        } ?>
        <td>
            <a href='update_visites.php?id=<?php echo $fila['ID']; ?>' class='btn-editar'>&#9998;</a>
            <a href='borrar_visites.php?id=<?php echo $fila['ID']; ?>' class='btn-esborrar'>&#10005;</a>
        </td>
    </tr>
    <?php } ?>
</tbody>
</table>
<?php require 'footer.php'; ?>