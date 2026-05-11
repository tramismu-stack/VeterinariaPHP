<?php
require 'db.php';
$missatge = '';
$tipus = '';
$errors = [];

// 🔥 ANIMALES + PROPIETARIOS
$sql_animals = "
    SELECT 
        animals.id, 
        animals.nom AS animal_nom,
        propietaris.nom AS propietari_nom
    FROM animals
    LEFT JOIN propietaris ON animals.id_propietari = propietaris.id
    ORDER BY animals.nom";
$result_animals = mysqli_query($conn, $sql_animals);

// 🔥 INSERT VISITA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data_visita = trim($_POST["data_visita"] ?? '');
    $motiu      = trim($_POST["motiu"] ?? '');
    $diagnostic = trim($_POST["diagnostic"] ?? '');
    $preu = str_replace(',', '.', trim($_POST["preu"] ?? ''));
    $id_animal  = trim($_POST["id_animal"] ?? '');

    // --- VALIDACIONES AMIGABLES ---

    if (empty($data_visita)) {
        $errors[] = "Has de posar la <strong>data de la visita</strong>.";
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $data_visita);
        if (!$d || $d->format('Y-m-d') !== $data_visita) {
            $errors[] = "La <strong>data de la visita</strong> no té un format vàlid.";
        } elseif ($d > new DateTime()) {
            $errors[] = "La <strong>data de la visita</strong> no pot ser en el futur.";
        }
    }

    if (empty($motiu)) {
        $errors[] = "Has d'escriure el <strong>motiu de la visita</strong>.";
    } elseif (strlen($motiu) > 255) {
        $errors[] = "El <strong>motiu</strong> és massa llarg (màxim 255 caràcters).";
    }

    if (empty($diagnostic)) {
        $errors[] = "Has d'escriure el <strong>diagnòstic</strong>.";
    } elseif (strlen($diagnostic) > 255) {
        $errors[] = "El <strong>diagnòstic</strong> és massa llarg (màxim 255 caràcters).";
    }

    if ($preu === '') {
        $errors[] = "Has d'indicar el <strong>preu</strong> de la visita.";
    } elseif (!is_numeric($preu)) {
        $errors[] = "El <strong>preu</strong> ha de ser un número (per exemple: 25 o 12.50).";
    } elseif (floatval($preu) < 0) {
        $errors[] = "El <strong>preu</strong> no pot ser negatiu.";
    } elseif (floatval($preu) > 99999.99) {
        $errors[] = "El <strong>preu</strong> introduït és massa elevat.";
    }

    if (empty($id_animal)) {
        $errors[] = "Has de seleccionar un <strong>animal</strong>.";
    } else {
        $id_check = intval($id_animal);
        $check = mysqli_query($conn, "SELECT id FROM animals WHERE id = $id_check");
        if (!$check || mysqli_num_rows($check) === 0) {
            $errors[] = "L'<strong>animal seleccionat</strong> no existeix a la base de dades.";
        }
    }

    if (empty($errors)) {
        $data_visita_esc = mysqli_real_escape_string($conn, $data_visita);
        $motiu_esc       = mysqli_real_escape_string($conn, $motiu);
        $diagnostic_esc  = mysqli_real_escape_string($conn, $diagnostic);
        $preu_esc        = mysqli_real_escape_string($conn, $preu);
        $id_animal_esc   = intval($id_animal);

        $sql = "INSERT INTO visites (data_visita, motiu, diagnostic, preu, id_animal)
                VALUES ('$data_visita_esc', '$motiu_esc', '$diagnostic_esc', '$preu_esc', '$id_animal_esc')";

        if (mysqli_query($conn, $sql)) {
            header('Location: Visites.php?ok=1');
            exit;
        } else {
            $db_error = mysqli_errno($conn);
            if ($db_error === 1062) {
                $errors[] = "Ja existeix una visita amb aquestes dades. Comprova que no l'hagis registrat dues vegades.";
            } elseif ($db_error === 1048) {
                $errors[] = "Falta algun camp obligatori. Revisa que tots els camps estiguin emplenats.";
            } elseif ($db_error === 1406) {
                $errors[] = "Un dels textos introduïts és massa llarg. Escurça el motiu o el diagnòstic.";
            } elseif ($db_error === 1452) {
                $errors[] = "L'animal seleccionat ja no existeix al sistema. Torna a carregar la pàgina i intenta-ho de nou.";
            } else {
                $errors[] = "S'ha produït un error inesperat en guardar la visita. Si el problema persisteix, contacta amb el teu informàtic.";
                error_log("MySQL error [{$db_error}]: " . mysqli_error($conn));
            }
        }
    }
}
?>
<?php require 'header.php'; ?>
<h1>Nova Visita</h1>

<!-- BLOQUE DE ERRORES (se llena en tiempo real por JS, o por PHP si hubo submit) -->
<div class="error-box" id="error-box" style="display:none;">
    <p class="error-title">⚠️ Si us plau, corregeix el següent abans de continuar:</p>
    <ul id="error-list"></ul>
</div>

<?php if (!empty($errors)): ?>
<script>
    // Errores de PHP (post-submit) precargados al cargar la página
    window._phpErrors = <?php echo json_encode(array_values($errors)); ?>;
</script>
<?php endif; ?>

<form action="Insert_Visites.php" method="POST" id="visita-form">
    <label>Data de visita</label><br>
    <input type="date" name="data_visita" id="data_visita"
           value="<?php echo htmlspecialchars($data_visita ?? ''); ?>" required><br><br>

    <label>Motiu</label><br>
    <input type="text" name="motiu" id="motiu"
           value="<?php echo htmlspecialchars($motiu ?? ''); ?>" required><br><br>

    <label>Diagnòstic</label><br>
    <input type="text" name="diagnostic" id="diagnostic"
           value="<?php echo htmlspecialchars($diagnostic ?? ''); ?>" required><br><br>

    <label>Preu</label><br>
    <input type="text" name="preu" id="preu"
           value="<?php echo htmlspecialchars($preu ?? ''); ?>"
           oninput="this.value = this.value.replace(',', '.')"
           required><br><br>

    <label>Animal</label><br>
    <select name="id_animal" id="animal-select" required style="width:300px;">
        <option value="">-- Selecciona un animal --</option>
        <?php
        if ($result_animals) mysqli_data_seek($result_animals, 0);
        while ($animal = mysqli_fetch_assoc($result_animals)):
        ?>
            <option value="<?php echo $animal['id']; ?>"
                <?php echo (isset($id_animal) && $id_animal == $animal['id']) ? 'selected' : ''; ?>>
                <?php
                    echo htmlspecialchars($animal['animal_nom']);
                    if ($animal['propietari_nom']) {
                        echo " (Dueño: " . htmlspecialchars($animal['propietari_nom']) . ")";
                    }
                ?>
            </option>
        <?php endwhile; ?>
    </select>
    <br><br>

    <button type="submit">Guardar visita</button>
    <a href="Visites.php">Cancel·lar</a>
</form>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('#animal-select').select2({
        placeholder: "Busca per animal o propietari...",
        allowClear: true,
        width: 'resolve'
    });

    // --- VALIDACIÓN EN TIEMPO REAL ---

    function validateForm() {
        const errors = [];
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        // 1. Fecha
        const dataVal = document.getElementById('data_visita').value;
        if (!dataVal) {
            errors.push("Has de posar la <strong>data de la visita</strong>.");
        } else {
            const d = new Date(dataVal + 'T00:00:00');
            if (isNaN(d.getTime())) {
                errors.push("La <strong>data de la visita</strong> no té un format vàlid.");
            } else if (d > today) {
                errors.push("La <strong>data de la visita</strong> no pot ser en el futur.");
            }
        }

        // 2. Motivo
        const motiuVal = document.getElementById('motiu').value.trim();
        if (!motiuVal) {
            errors.push("Has d'escriure el <strong>motiu de la visita</strong>.");
        } else if (motiuVal.length > 255) {
            errors.push("El <strong>motiu</strong> és massa llarg (màxim 255 caràcters).");
        }

        // 3. Diagnóstico
        const diagVal = document.getElementById('diagnostic').value.trim();
        if (!diagVal) {
            errors.push("Has d'escriure el <strong>diagnòstic</strong>.");
        } else if (diagVal.length > 255) {
            errors.push("El <strong>diagnòstic</strong> és massa llarg (màxim 255 caràcters).");
        }

        // 4. Precio
        const preuVal = document.getElementById('preu').value.replace(',', '.');
        if (preuVal === '') {
            errors.push("Has d'indicar el <strong>preu</strong> de la visita.");
        } else if (isNaN(preuVal) || preuVal.trim() === '') {
            errors.push("El <strong>preu</strong> ha de ser un número (per exemple: 25 o 12.50).");
        } else if (parseFloat(preuVal) < 0) {
            errors.push("El <strong>preu</strong> no pot ser negatiu.");
        } else if (parseFloat(preuVal) > 99999.99) {
            errors.push("El <strong>preu</strong> introduït és massa elevat.");
        }

        // 5. Animal
        const animalVal = document.getElementById('animal-select').value;
        if (!animalVal) {
            errors.push("Has de seleccionar un <strong>animal</strong>.");
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

    // Escuchar cambios en todos los campos
    ['data_visita', 'motiu', 'diagnostic', 'preu'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            renderErrors(validateForm());
        });
        document.getElementById(id).addEventListener('change', function() {
            renderErrors(validateForm());
        });
    });

    // Select2 necesita evento especial
    $('#animal-select').on('change', function() {
        renderErrors(validateForm());
    });

    // Si PHP mandó errores tras el submit, mostrarlos de entrada
    if (typeof window._phpErrors !== 'undefined' && window._phpErrors.length > 0) {
        renderErrors(window._phpErrors);
    } else {
        // Validar al cargar si ya hay datos (ej: volver atrás en el navegador)
        const initial = validateForm();
        if (initial.length > 0) renderErrors(initial);
    }
});
</script>

<?php require 'footer.php'; ?>