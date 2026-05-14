<?php
require 'db.php';
$errors = [];

//  PROPIETARIOS para el select
$sql_propietaris = "SELECT id, nom FROM propietaris ORDER BY nom";
$result_propietaris = mysqli_query($conn, $sql_propietaris);

//  INSERT ANIMAL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom            = trim($_POST["nom"] ?? '');
    $especie        = trim($_POST["especie"] ?? '');
    $raca           = trim($_POST["raca"] ?? '');
    $data_naixement = trim($_POST["data_naixement"] ?? '');
    $id_propietari  = trim($_POST["id_propietari"] ?? '');

    // --- VALIDACIONES ---

    // 1. Nom
    if (empty($nom)) {
        $errors[] = "Has d'escriure el <strong>nom de l'animal</strong>.";
    } elseif (strlen($nom) > 100) {
        $errors[] = "El <strong>nom</strong> és massa llarg (màxim 100 caràcters).";
    }

    // 2. Especie
    if (empty($especie)) {
        $errors[] = "Has d'escriure l'<strong>espècie</strong> de l'animal.";
    } elseif (strlen($especie) > 100) {
        $errors[] = "L'<strong>espècie</strong> és massa llarga (màxim 100 caràcters).";
    }

    // 3. Raça
    if (empty($raca)) {
        $errors[] = "Has d'escriure la <strong>raça</strong> de l'animal.";
    } elseif (strlen($raca) > 100) {
        $errors[] = "La <strong>raça</strong> és massa llarga (màxim 100 caràcters).";
    }

    // 4. Data naixement
    if (empty($data_naixement)) {
        $errors[] = "Has de posar la <strong>data de naixement</strong>.";
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $data_naixement);
        if (!$d || $d->format('Y-m-d') !== $data_naixement) {
            $errors[] = "La <strong>data de naixement</strong> no té un format vàlid.";
        } elseif ($d > new DateTime()) {
            $errors[] = "La <strong>data de naixement</strong> no pot ser en el futur.";
        }
    }

    // 5. Propietari
    if (empty($id_propietari)) {
        $errors[] = "Has de seleccionar un <strong>propietari</strong>.";
    } else {
        $id_check = intval($id_propietari);
        $check = mysqli_query($conn, "SELECT id FROM propietaris WHERE id = $id_check");
        if (!$check || mysqli_num_rows($check) === 0) {
            $errors[] = "El <strong>propietari seleccionat</strong> no existeix a la base de dades.";
        }
    }

    // --- INSERT ---
    if (empty($errors)) {
        $nom_esc            = mysqli_real_escape_string($conn, $nom);
        $especie_esc        = mysqli_real_escape_string($conn, $especie);
        $raca_esc           = mysqli_real_escape_string($conn, $raca);
        $data_naixement_esc = mysqli_real_escape_string($conn, $data_naixement);
        $id_propietari_esc  = intval($id_propietari);

        $sql = "INSERT INTO animals (nom, especie, raca, data_naixement, id_propietari)
                VALUES ('$nom_esc', '$especie_esc', '$raca_esc', '$data_naixement_esc', '$id_propietari_esc')";

        if (mysqli_query($conn, $sql)) {
            header('Location: Animals.php?ok=1');
            exit;
        } else {
            $db_error = mysqli_errno($conn);
            if ($db_error === 1062) {
                $errors[] = "Ja existeix un animal amb aquestes dades. Comprova que no l'hagis registrat dues vegades.";
            } elseif ($db_error === 1048) {
                $errors[] = "Falta algun camp obligatori. Revisa que tots els camps estiguin emplenats.";
            } elseif ($db_error === 1406) {
                $errors[] = "Un dels textos introduïts és massa llarg.";
            } elseif ($db_error === 1452) {
                $errors[] = "El propietari seleccionat ja no existeix al sistema. Torna a carregar la pàgina i intenta-ho de nou.";
            } else {
                $errors[] = "S'ha produït un error inesperat en guardar l'animal. Si el problema persisteix, contacta amb el teu informàtic.";
                error_log("MySQL error [{$db_error}]: " . mysqli_error($conn));
            }
        }
    }
}
?>
<?php require 'header.php'; ?>
<h1>Nou Animal</h1>

<!-- BLOQUE DE ERRORES -->
<div class="error-box" id="error-box" style="display:none;">
    <p class="error-title">⚠️ Si us plau, corregeix el següent abans de continuar:</p>
    <ul id="error-list"></ul>
</div>

<?php if (!empty($errors)): ?>
<script>
    window._phpErrors = <?php echo json_encode(array_values($errors)); ?>;
</script>
<?php endif; ?>

<form action="Insert_Animals.php" method="POST" id="animal-form">
    <label>Nom</label><br>
    <input type="text" name="nom" id="nom"
           value="<?php echo htmlspecialchars($nom ?? ''); ?>" required><br><br>

    <label>Espècie</label><br>
    <input type="text" name="especie" id="especie"
           value="<?php echo htmlspecialchars($especie ?? ''); ?>" required><br><br>

    <label>Raça</label><br>
    <input type="text" name="raca" id="raca"
           value="<?php echo htmlspecialchars($raca ?? ''); ?>" required><br><br>

    <label>Data Naixement</label><br>
    <input type="date" name="data_naixement" id="data_naixement"
           value="<?php echo htmlspecialchars($data_naixement ?? ''); ?>" required><br><br>

    <label>Propietari</label><br>
    <select name="id_propietari" id="propietari-select" required style="width:300px;">
        <option value="">-- Selecciona un propietari --</option>
        <?php
        if ($result_propietaris) mysqli_data_seek($result_propietaris, 0);
        while ($prop = mysqli_fetch_assoc($result_propietaris)):
        ?>
            <option value="<?php echo $prop['id']; ?>"
                <?php echo (isset($id_propietari) && $id_propietari == $prop['id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($prop['nom']); ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <button type="submit">Guardar animal</button>
    <a href="Animals.php">Cancel·lar</a>
</form>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#propietari-select').select2({
        placeholder: "Busca un propietari...",
        allowClear: true,
        width: 'resolve'
    });

    // --- VALIDACIÓN SOLO AL SUBMIT ---

    function validateForm() {
        const errors = [];
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // 1. Nom
        const nomVal = document.getElementById('nom').value.trim();
        if (!nomVal) {
            errors.push("Has d'escriure el <strong>nom de l'animal</strong>.");
        } else if (nomVal.length > 100) {
            errors.push("El <strong>nom</strong> és massa llarg (màxim 100 caràcters).");
        }

        // 2. Especie
        const especieVal = document.getElementById('especie').value.trim();
        if (!especieVal) {
            errors.push("Has d'escriure l'<strong>espècie</strong> de l'animal.");
        } else if (especieVal.length > 100) {
            errors.push("L'<strong>espècie</strong> és massa llarga (màxim 100 caràcters).");
        }

        // 3. Raça
        const racaVal = document.getElementById('raca').value.trim();
        if (!racaVal) {
            errors.push("Has d'escriure la <strong>raça</strong> de l'animal.");
        } else if (racaVal.length > 100) {
            errors.push("La <strong>raça</strong> és massa llarga (màxim 100 caràcters).");
        }

        // 4. Data naixement
        const dataVal = document.getElementById('data_naixement').value;
        if (!dataVal) {
            errors.push("Has de posar la <strong>data de naixement</strong>.");
        } else {
            const d = new Date(dataVal + 'T00:00:00');
            if (isNaN(d.getTime())) {
                errors.push("La <strong>data de naixement</strong> no té un format vàlid.");
            } else if (d > today) {
                errors.push("La <strong>data de naixement</strong> no pot ser en el futur.");
            }
        }

        // 5. Propietari
        const propVal = document.getElementById('propietari-select').value;
        if (!propVal) {
            errors.push("Has de seleccionar un <strong>propietari</strong>.");
        }

        return errors;
    }

    function renderErrors(errors) {
        const box  = document.getElementById('error-box');
        const list = document.getElementById('error-list');
        if (errors.length === 0) {
            box.style.display = 'none';
            list.innerHTML = '';
        } else {
            list.innerHTML = errors.map(e => `<li>${e}</li>`).join('');
            box.style.display = 'block';
        }
    }

    // SOLO validar cuando intentes hacer submit
    document.getElementById('animal-form').addEventListener('submit', function(e) {
        const errors = validateForm();
        if (errors.length > 0) {
            e.preventDefault(); // Evita que se envíe el formulario
            renderErrors(errors);
            // Scroll al cuadro de errores
            document.getElementById('error-box').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });

    // Mostrar errores si vuelves del PHP con errores de base de datos
    if (typeof window._phpErrors !== 'undefined' && window._phpErrors.length > 0) {
        renderErrors(window._phpErrors);
        document.getElementById('error-box').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
});
</script>

<?php require 'footer.php'; ?>