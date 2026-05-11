<?php
require 'db.php';
$errors = [];

// 🔥 INSERT PROPIETARI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom     = trim($_POST["nom"] ?? '');
    $telefon = trim($_POST["telefon"] ?? '');
    $email   = trim($_POST["email"] ?? '');
    $adreca  = trim($_POST["adreca"] ?? '');

    // --- VALIDACIONS ---

    // 1. Nom
    if (empty($nom)) {
        $errors[] = "Has d'escriure el <strong>nom del propietari</strong>.";
    } elseif (strlen($nom) > 100) {
        $errors[] = "El <strong>nom</strong> és massa llarg (màxim 100 caràcters).";
    }

    // 2. Telèfon
    if (empty($telefon)) {
        $errors[] = "Has d'escriure el <strong>telèfon</strong>.";
    } elseif (strlen($telefon) > 20) {
        $errors[] = "El <strong>telèfon</strong> és massa llarg (màxim 20 caràcters).";
    }

    // 3. Email
    if (empty($email)) {
        $errors[] = "Has d'escriure l'<strong>email</strong>.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'<strong>email</strong> no té un format vàlid.";
    } elseif (strlen($email) > 100) {
        $errors[] = "L'<strong>email</strong> és massa llarg (màxim 100 caràcters).";
    }

    // 4. Adreça
    if (empty($adreca)) {
        $errors[] = "Has d'escriure l'<strong>adreça</strong>.";
    } elseif (strlen($adreca) > 255) {
        $errors[] = "L'<strong>adreça</strong> és massa llarga (màxim 255 caràcters).";
    }

    // --- INSERT ---
    if (empty($errors)) {
        $nom_esc     = mysqli_real_escape_string($conn, $nom);
        $telefon_esc = mysqli_real_escape_string($conn, $telefon);
        $email_esc   = mysqli_real_escape_string($conn, $email);
        $adreca_esc  = mysqli_real_escape_string($conn, $adreca);

        $sql = "INSERT INTO propietaris (nom, telefon, email, adreca)
                VALUES ('$nom_esc', '$telefon_esc', '$email_esc', '$adreca_esc')";

        if (mysqli_query($conn, $sql)) {
            header('Location: Propietaris.php?ok=1');
            exit;
        } else {
            $db_error = mysqli_errno($conn);
            if ($db_error === 1062) {
                $errors[] = "Ja existeix un propietari amb aquestes dades (potser l'email o el telèfon estan duplicats).";
            } elseif ($db_error === 1048) {
                $errors[] = "Falta algun camp obligatori. Revisa que tots els camps estiguin emplenats.";
            } elseif ($db_error === 1406) {
                $errors[] = "Un dels textos introduïts és massa llarg per a la base de dades.";
            } else {
                $errors[] = "S'ha produït un error inesperat en guardar el propietari. Contacta amb l'administrador.";
                error_log("MySQL error [{$db_error}]: " . mysqli_error($conn));
            }
        }
    }
}
?>
<?php require 'header.php'; ?>
<h1>Nou Propietari</h1>

<div class="error-box" id="error-box" style="display:none;">
    <p class="error-title">⚠️ Si us plau, corregeix el següent abans de continuar:</p>
    <ul id="error-list"></ul>
</div>

<?php if (!empty($errors)): ?>
<script>
    window._phpErrors = <?php echo json_encode(array_values($errors)); ?>;
</script>
<?php endif; ?>

<form action="Insert_Propietaris.php" method="POST" id="propietari-form">
    <label>Nom</label><br>
    <input type="text" name="nom" id="nom" 
           value="<?php echo htmlspecialchars($nom ?? ''); ?>" required><br><br>

    <label>Telèfon</label><br>
    <input type="text" name="telefon" id="telefon" 
           value="<?php echo htmlspecialchars($telefon ?? ''); ?>" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" id="email" 
           value="<?php echo htmlspecialchars($email ?? ''); ?>" required><br><br>

    <label>Adreça</label><br>
    <input type="text" name="adreca" id="adreca" 
           value="<?php echo htmlspecialchars($adreca ?? ''); ?>" required><br><br>

    <button type="submit">Guardar propietari</button>
    <a href="Propietaris.php">Cancel·lar</a>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    // --- VALIDACIÓ EN TEMPS REAL ---
    function validateForm() {
        const errors = [];

        // 1. Nom
        const nomVal = document.getElementById('nom').value.trim();
        if (!nomVal) {
            errors.push("Has d'escriure el <strong>nom del propietari</strong>.");
        } else if (nomVal.length > 100) {
            errors.push("El <strong>nom</strong> és massa llarg (màxim 100 caràcters).");
        }

        // 2. Telèfon
        const telefonVal = document.getElementById('telefon').value.trim();
        if (!telefonVal) {
            errors.push("Has d'escriure el <strong>telèfon</strong>.");
        } else if (telefonVal.length > 20) {
            errors.push("El <strong>telèfon</strong> és massa llarg (màxim 20 caràcters).");
        }

        // 3. Email
        const emailVal = document.getElementById('email').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal) {
            errors.push("Has d'escriure l'<strong>email</strong>.");
        } else if (!emailRegex.test(emailVal)) {
            errors.push("L'<strong>email</strong> no té un format vàlid.");
        } else if (emailVal.length > 100) {
            errors.push("L'<strong>email</strong> és massa llarg (màxim 100 caràcters).");
        }

        // 4. Adreça
        const adrecaVal = document.getElementById('adreca').value.trim();
        if (!adrecaVal) {
            errors.push("Has d'escriure l'<strong>adreça</strong>.");
        } else if (adrecaVal.length > 255) {
            errors.push("L'<strong>adreça</strong> és massa llarga (màxim 255 caràcters).");
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

    // Escoltar els canvis en tots els inputs
    ['nom', 'telefon', 'email', 'adreca'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            renderErrors(validateForm());
        });
        document.getElementById(id).addEventListener('change', function() {
            renderErrors(validateForm());
        });
    });

    // Càrrega inicial d'errors si ve de PHP o està buit a l'inici
    if (typeof window._phpErrors !== 'undefined' && window._phpErrors.length > 0) {
        renderErrors(window._phpErrors);
    } else {
        const initial = validateForm();
        if (initial.length > 0) renderErrors(initial);
    }
});
</script>

<?php require 'footer.php'; ?>