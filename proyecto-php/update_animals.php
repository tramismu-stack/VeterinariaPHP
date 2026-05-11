<?php
require 'db.php';

// Llegim l'id de la URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: Animals.php');
    exit;
}

$errors = [];

// Carreguem l'animal actual per pre-omplir
$sql_animal = "SELECT * FROM animals WHERE id = $id";
$res_animal = mysqli_query($conn, $sql_animal);
$animal = mysqli_fetch_assoc($res_animal);

if (!$animal) {
    header('Location: Animals.php');
    exit;
}

// Carreguem els propietaris pel desplegable <select>
$sql_propietaris = "SELECT id, nom FROM propietaris ORDER BY nom";
$result_propietaris = mysqli_query($conn, $sql_propietaris);

// Inicialitzem amb les dades de la Base de Dades
$nom            = $animal['nom'];
$especie        = $animal['especie'];
$raca           = $animal['raca'];
$data_naixement = $animal['data_naixement'];
$id_propietari  = $animal['id_propietari'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom            = trim($_POST["nom"] ?? '');
    $especie        = trim($_POST["especie"] ?? '');
    $raca           = trim($_POST["raca"] ?? '');
    $data_naixement = trim($_POST["data_naixement"] ?? '');
    $id_propietari  = trim($_POST["id_propietari"] ?? '');

    // Validacions PHP
    if (empty($nom)) { $errors[] = "Has d'escriure el nom."; } 
    if (empty($especie)) { $errors[] = "Has d'escriure l'espècie."; } 
    if (empty($raca)) { $errors[] = "Has d'escriure la raça."; } 
    if (empty($data_naixement)) { $errors[] = "Has de posar la data de naixement."; } 
    if (empty($id_propietari)) { $errors[] = "Has de seleccionar un propietari."; }

    if (empty($errors)) {
        $nom_esc            = mysqli_real_escape_string($conn, $nom);
        $especie_esc        = mysqli_real_escape_string($conn, $especie);
        $raca_esc           = mysqli_real_escape_string($conn, $raca);
        $data_naixement_esc = mysqli_real_escape_string($conn, $data_naixement);
        $id_propietari_esc  = intval($id_propietari);

        $sql = "UPDATE animals 
                SET nom='$nom_esc', especie='$especie_esc', raca='$raca_esc', data_naixement='$data_naixement_esc', id_propietari='$id_propietari_esc' 
                WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            header('Location: Animals.php?actualitzat=1');
            exit;
        } else {
            $errors[] = "Error a la base de dades: " . mysqli_error($conn);
        }
    }
}
?>
<?php require 'header.php'; ?>

<h1>Editar Animal</h1>

<div class="error-box" id="error-box" style="display:none;">
    <p class="error-title">⚠️ Corregeix el següent:</p>
    <ul id="error-list"></ul>
</div>

<?php if (!empty($errors)): ?>
<script> window._phpErrors = <?php echo json_encode(array_values($errors)); ?>; </script>
<?php endif; ?>

<form action="update_animals.php?id=<?php echo $id; ?>" method="POST" id="animal-form">
    <label>Nom</label><br>
    <input type="text" name="nom" id="nom" value="<?php echo htmlspecialchars($nom); ?>" required><br><br>

    <label>Espècie</label><br>
    <input type="text" name="especie" id="especie" value="<?php echo htmlspecialchars($especie); ?>" required><br><br>

    <label>Raça</label><br>
    <input type="text" name="raca" id="raca" value="<?php echo htmlspecialchars($raca); ?>" required><br><br>

    <label>Data Naixement</label><br>
    <input type="date" name="data_naixement" id="data_naixement" value="<?php echo htmlspecialchars($data_naixement); ?>" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" required><br><br>

    <label>Propietari</label><br>
    <select name="id_propietari" id="id_propietari" style="width: 100%; padding: 8px 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;" required>
        <option value="">-- Selecciona un propietari --</option>
        <?php
        if ($result_propietaris) mysqli_data_seek($result_propietaris, 0);
        while ($prop = mysqli_fetch_assoc($result_propietaris)):
        ?>
            <option value="<?<?php
require 'db.php';

$sql = "SELECT pr.id, pr.nom as Nom, pr.telefon as Telefon, pr.email as Email, pr.adreca as Adreça, GROUP_CONCAT(DISTINCT an.nom SEPARATOR ', ') AS Mascotes FROM propietaris pr LEFT JOIN animals an ON pr.id = an.id_propietari GROUP BY pr.id, pr.nom;";
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
<thead><?php
require 'db.php';

$sql = "SELECT pr.id, pr.nom as Nom, pr.telefon as Telefon, pr.email as Email, pr.adreca as Adreça, GROUP_CONCAT(DISTINCT an.nom SEPARATOR ', ') AS Mascotes FROM propietaris pr LEFT JOIN animals an ON pr.id = an.id_propietari GROUP BY pr.id, pr.nom;";
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
<?php
require 'db.php';

$sql = "SELECT pr.id, pr.nom as Nom, pr.telefon as Telefon, pr.email as Email, pr.adreca as Adreça, GROUP_CONCAT(DISTINCT an.nom SEPARATOR ', ') AS Mascotes FROM propietaris pr LEFT JOIN animals an ON pr.id = an.id_propietari GROUP BY pr.id, pr.nom;";
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
<?php require 'footer.php'; ?>php echo $prop['id']; ?>" <?php echo ($id_propietari == $prop['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($prop['nom']); ?>
            </option>
        <?php endwhile; ?>
    </select><br><br>

    <button type="submit">Guardar canvis</button>
    <a href="Animals.php" class="btn-tornar" style="margin-top: 0;">Cancel·lar</a>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function validateForm() {
        const errors = [];
        if (!document.getElementById('nom').value.trim()) errors.push("Has d'escriure el nom.");
        if (!document.getElementById('especie').value.trim()) errors.push("Has d'escriure l'espècie.");
        if (!document.getElementById('raca').value.trim()) errors.push("Has d'escriure la raça.");
        if (!document.getElementById('data_naixement').value) errors.push("Has de posar la data de naixement.");
        if (!document.getElementById('id_propietari').value) errors.push("Has de seleccionar un propietari.");
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

    ['nom', 'especie', 'raca', 'data_naixement', 'id_propietari'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() { renderErrors(validateForm()); });
        document.getElementById(id).addEventListener('change', function() { renderErrors(validateForm()); });
    });

    if (typeof window._phpErrors !== 'undefined' && window._phpErrors.length > 0) {
        renderErrors(window._phpErrors);
    }
});
</script>

<?php require 'footer.php'; ?>