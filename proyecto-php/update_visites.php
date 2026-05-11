<?php
require 'db.php';

// Llegim l'id de la URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: Visites.php');
    exit;
}

$errors = [];

// Carreguem la visita actual
$sql_visita = "SELECT * FROM visites WHERE id = $id";
$res_visita = mysqli_query($conn, $sql_visita);
$visita = mysqli_fetch_assoc($res_visita);

if (!$visita) {
    header('Location: Visites.php');
    exit;
}

// Carreguem els animals pel desplegable <select>
$sql_animals = "SELECT id, nom FROM animals ORDER BY nom";
$result_animals = mysqli_query($conn, $sql_animals);

// Inicialitzem variables amb la Base de Dades
$data_visita = $visita['data_visita'];
$motiu       = $visita['motiu'];
$diagnostic  = $visita['diagnostic'];
$preu        = $visita['preu'];
$id_animal   = $visita['id_animal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data_visita = trim($_POST["data_visita"] ?? '');
    $motiu       = trim($_POST["motiu"] ?? '');
    $diagnostic  = trim($_POST["diagnostic"] ?? '');
    $preu        = trim($_POST["preu"] ?? '');
    $id_animal   = trim($_POST["id_animal"] ?? '');

    // Validacions PHP
    if (empty($data_visita)) { $errors[] = "Has d'indicar la data de la visita."; }
    if (empty($motiu)) { $errors[] = "Has d'escriure el motiu."; }
    if (empty($preu) && $preu !== '0') { $errors[] = "Has d'indicar un preu."; }
    elseif (!is_numeric($preu) || $preu < 0) { $errors[] = "El preu ha de ser un número vàlid i positiu."; }
    if (empty($id_animal)) { $errors[] = "Has de seleccionar un animal."; }

    if (empty($errors)) {
        $data_visita_esc = mysqli_real_escape_string($conn, $data_visita);
        $motiu_esc       = mysqli_real_escape_string($conn, $motiu);
        $diagnostic_esc  = mysqli_real_escape_string($conn, $diagnostic);
        $preu_esc        = floatval($preu);
        $id_animal_esc   = intval($id_animal);

        $sql = "UPDATE visites 
                SET data_visita='$data_visita_esc', motiu='$motiu_esc', diagnostic='$diagnostic_esc', preu='$preu_esc', id_animal='$id_animal_esc' 
                WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            header('Location: Visites.php?actualitzat=1');
            exit;
        } else {
            $errors[] = "Error a la base de dades: " . mysqli_error($conn);
        }
    }
}
?>
<?php require 'header.php'; ?>

<h1>Editar Visita</h1>

<div class="error-box" id="error-box" style="display:none;">
    <p class="error-title">⚠️ Corregeix el següent:</p>
    <ul id="error-list"></ul>
</div>

<?php if (!empty($errors)): ?>
<script> window._phpErrors = <?php echo json_encode(array_values($errors)); ?>; </script>
<?php endif; ?>

<form action="update_visites.php?id=<?php echo $id; ?>" method="POST" id="visita-form">
    
    <label>Animal</label><br>
    <select name="id_animal" id="id_animal" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" required>
        <option value="">-- Selecciona un animal --</option>
        <?php
        if ($result_animals) mysqli_data_seek($result_animals, 0);
        while ($an = mysqli_fetch_assoc($result_animals)):
        ?>
            <option value="<?php echo $an['id']; ?>" <?php echo ($id_animal == $an['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($an['nom']); ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <label>Data Visita</label><br>
    <input type="date" name="data_visita" id="data_visita" value="<?php echo htmlspecialchars($data_visita); ?>" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" required><br><br>

    <label>Motiu</label><br>
    <input type="text" name="motiu" id="motiu" value="<?php echo htmlspecialchars($motiu); ?>" required><br><br>

    <label>Diagnòstic</label><br>
    <textarea name="diagnostic" id="diagnostic" rows="4" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: Arial, sans-serif;"><?php echo htmlspecialchars($diagnostic); ?></textarea><br><br>

    <label>Preu (€)</label><br>
    <input type="number" step="0.01" name="preu" id="preu" value="<?php echo htmlspecialchars($preu); ?>" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" required><br><br>

    <button type="submit">Guardar canvis</button>
    <a href="Visites.php" class="btn-tornar" style="margin-top: 0;">Cancel·lar</a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function validateForm() {
        const errors = [];
        
        if (!document.getElementById('id_animal').value) errors.push("Has de seleccionar un animal.");
        if (!document.getElementById('data_visita').value) errors.push("Has d'indicar la data.");
        if (!document.getElementById('motiu').value.trim()) errors.push("Has d'escriure el motiu.");
        
        const preuVal = document.getElementById('preu').value;
        if (!preuVal) {
            errors.push("Has d'indicar un preu.");
        } else if (isNaN(preuVal) || parseFloat(preuVal) < 0) {
            errors.push("El preu ha de ser un número positiu.");
        }

        return errors;
    }

    function renderErrors(errors) {
        const box  = document.getElementById('error-box');
        const list = document.getElementById('error-list');
        if (errors.length === 0) {
            box.style.display = 'none'; list.innerHTML = '';
        } else {
            list.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
            box.style.display = 'block';
        }
    }

    ['id_animal', 'data_visita', 'motiu', 'preu'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() { renderErrors(validateForm()); });
        document.getElementById(id).addEventListener('change', function() { renderErrors(validateForm()); });
    });

    if (typeof window._phpErrors !== 'undefined' && window._phpErrors.length > 0) {
        renderErrors(window._phpErrors);
    }
});
</script>

<?php require 'footer.php'; ?>