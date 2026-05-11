<?php
require 'db.php';
$sql = "SELECT an.id as ID, an.nom as Nom, an.especie as Especie , an.raca as Raça, an.data_naixement as 'Data Naixement', pr.nom as Propietari
FROM animals an JOIN propietaris pr ON pr.id= an.id_propietari ORDER BY an.id";
$res = mysqli_query($conn, $sql);
if (!$res) { die('Error: ' . mysqli_error($conn)); }
?>

<?php require 'header.php'; ?>

<?php
// Missatges d'èxit i d'error
$missatge = '';
$tipus = 'ok';

if (isset($_GET['ok'])) $missatge = 'Animal guardat correctament!';
if (isset($_GET['esborrat'])) $missatge = 'Animal esborrat correctament!';
if (isset($_GET['actualitzat'])) $missatge = 'Animal actualitzat correctament!';

// Per quan tinguis l'arxiu borrar_animals.php i no puguis esborrar per tenir visites
if (isset($_GET['error_visites'])) {
    $missatge = '⚠️ No pots esborrar aquest animal perquè encara té visites assignades al seu historial.';
    $tipus = 'error';
}

if (isset($_GET['error'])) {
    $missatge = 'Hi ha hagut un error: ' . htmlspecialchars($_GET['error']);
    $tipus = 'error';
}
?>

<?php if ($missatge): ?>
<p class='missatge-<?php echo $tipus; ?>'><?php echo $missatge; ?></p>
<?php endif; ?>

<h1>Animals <a class='btn-editar' href='Insert_Animals.php'>+</a> </h1>
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
            <a href='update_animals.php?id=<?php echo $fila['ID']; ?>' class='btn-editar'>&#9998;</a>
            <a href='borrar_animals.php?id=<?php echo $fila['ID']; ?>' class='btn-esborrar'>&#10005;</a>
        </td>
    </tr>
    <?php } ?>
</tbody>
</table>

<div id="modalConfirmacion" class="modal-overlay">
    <div class="modal-box">
        <h2 class="modal-titol">⚠️ Confirmar Esborrat</h2>
        <p>Estàs segur que vols esborrar aquest animal? Aquesta acció eliminarà el registre per sempre.</p>
        <div class="modal-botons">
            <button id="btnCancel" class="btn-cancelar">Cancel·lar</button>
            <a id="btnConfirm" href="#" class="btn-confirmar">Sí, esborrar</a>
        </div>
    </div>
</div>

<style>
    .modal-overlay {
        display: none; /* Ocult per defecte */
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 9999;
        align-items: center; justify-content: center;
        opacity: 0; transition: opacity 0.3s ease;
    }
    .modal-overlay.show { display: flex; opacity: 1; }
    .modal-box {
        background: #fff; padding: 25px 30px; border-radius: 8px;
        max-width: 400px; text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        transform: translateY(-20px); transition: transform 0.3s ease;
    }
    .modal-overlay.show .modal-box { transform: translateY(0); }
    .modal-titol { color: #d9534f; margin-top: 0; }
    .modal-botons { margin-top: 20px; display: flex; justify-content: space-around; gap: 10px; }
    .btn-cancelar {
        background: #e0e0e0; color: #333; border: none; padding: 10px 20px;
        border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 14px;
    }
    .btn-cancelar:hover { background: #d0d0d0; }
    .btn-confirmar {
        background: #d9534f; color: #fff; text-decoration: none; padding: 10px 20px;
        border-radius: 4px; font-weight: bold; font-size: 14px;
    }
    .btn-confirmar:hover { background: #c9302c; }
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const modal = document.getElementById("modalConfirmacion");
    const btnCancel = document.getElementById("btnCancel");
    const btnConfirm = document.getElementById("btnConfirm");

    // Seleccionem tots els botons d'esborrar. 
    // NOTA: Assegura't que l'<a> tingui la classe 'btn-esborrar'
    const botonsEsborrar = document.querySelectorAll('.btn-esborrar');

    botonsEsborrar.forEach(boto => {
        boto.addEventListener('click', function(e) {
            e.preventDefault(); // Evita que vagi directe al PHP de borrar
            const urlEsborrar = this.getAttribute('href'); // Agafa la URL de l'enllaç
            btnConfirm.setAttribute('href', urlEsborrar); // Li posem la URL al botó vermell de la modal
            modal.classList.add('show'); // Mostrem la modal
        });
    });

    btnCancel.addEventListener('click', function() {
        modal.classList.remove('show'); // Amagem la modal si cancel·la
    });
});
</script>

<?php require 'footer.php'; ?>