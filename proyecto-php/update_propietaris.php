<?php
require 'db.php';

// 1. Llegim l'id de la URL
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: Propietaris.php');
    exit;
}

$errors = [];

// 2. Carreguem les dades actuals del propietari per pre-omplir el formulari
$sql_client = "SELECT * FROM propietaris WHERE id = $id";
$res_client = mysqli_query($conn, $sql_client);
$client = mysqli_fetch_assoc($res_client);

if (!$client) {
    header('Location: Propietaris.php');
    exit;
}

// Inicialitzem les variables amb les dades de la Base de Dades
$nom     = $client['nom'];
$telefon = $client['telefon'];
$email   = $client['email'];
$adreca  = $client['adreca'];

// 3. Si s'ha enviat el formulari, sobreescrivim les variables i validem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom     = trim($_POST["nom"] ?? '');
    $telefon = trim($_POST["telefon"] ?? '');
    $email   = trim($_POST["email"] ?? '');
    $adreca  = trim($_POST["adreca"] ?? '');

    // --- VALIDACIONS ---
    if (empty($nom)) { $errors[] = "Has d'escriure el <strong>nom del propietari</strong>."; } 
    elseif (strlen($nom) > 100) { $errors[] = "El <strong>nom</strong> és massa llarg."; }

    if (empty($telefon)) { $errors[] = "Has d'escriure el <strong>telèfon</strong>."; } 
    elseif (strlen($telefon) > 20) { $errors[] = "El <strong>telèfon</strong> és massa llarg."; }

    if (empty($email)) { $errors[] = "Has d'escriure l'<strong>email</strong>."; } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "L'<strong>email</strong> no té un format vàlid."; } 
    elseif (strlen($email) > 100) { $errors[] = "L'<strong>email</strong> és massa llarg."; }

    if (empty($adreca)) { $errors[] = "Has d'escriure l'<strong>adreça</strong>."; } 
    elseif (strlen($adreca) > 255) { $errors[] = "L'<strong>adreça</strong> és massa llarga."; }

    // --- UPDATE ---
    if (empty($errors)) {
        $nom_esc     = mysqli_real_escape_string($conn, $nom);
        $telefon_esc = mysqli_real_escape_string($conn, $telefon);
        $email_esc   = mysqli_real_escape_string($conn, $email);
        $adreca_esc  = mysqli_real_escape_string($conn, $adreca);

        $sql = "UPDATE propietaris 
                SET nom='$nom_esc', telefon='$telefon_esc', email='$email_esc', adreca='$adreca_esc' 
                WHERE id=$id";

        if (mysqli_query($conn, $sql)) {
            header('Location: Propietaris.php?actualitzat=1');
            exit;
        } else {
            $db_error = mysqli_errno($conn);
            if ($db_error === 1062) {
                $errors[] = "Les dades (potser l'email o el telèfon) xoquen amb les d'un altre propietari.";
            } else {
                $errors[] = "Error a la base de dades: " . mysqli_error($conn);
            }
        }
    }
}
?>
<?php require 'header.php'; ?>

<h1>Editar Propietari</h1>

<div class="error-box" id="error-box" style="display:none;">
    <p class="error-title">⚠️ Si us plau, corregeix el següent abans de continuar:</p>
    <ul id="error-list"></ul>
</div>

<?php if (!empty($errors)): ?>
<script>
    window._phpErrors = <?php echo json_encode(array_values($errors)); ?>;
</script>
<?php endif; ?>

<form action="update_propietaris.php?id=<?php echo $id; ?>" method="POST" id="propietari-form">
    <label>Nom *</label><br>
    <input type="text" name="nom" id="nom" 
           value="<?php echo htmlspecialchars($nom); ?>" required><br><br>

    <label>Telèfon</label><br>
    <input type="text" name="telefon" id="telefon" 
           value="<?php echo htmlspecialchars($telefon); ?>" required><br><br>

    <label>Email</label><br>
    <input type="email" name="email" id="email" 
           value="<?php echo htmlspecialchars($email); ?>" required><br><br>

    <label>Adreça</label><br>
    <input type="text" name="adreca" id="adreca" 
           value="<?php echo htmlspecialchars($adreca); ?>" required><br><br>

    <button type="submit">Guardar canvis</button>
    <a href="Propietaris.php">Cancel·lar</a>
</form>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {

    function validateForm() {
        const errors = [];

        const nomVal = document.getElementById('nom').value.trim();
        if (!nomVal) { errors.push("Has d'escriure el <strong>nom del propietari</strong>."); } 
        else if (nomVal.length > 100) { errors.push("El <strong>nom</strong> és massa llarg."); }

        const telefonVal = document.getElementById('telefon').value.trim();
        if (!telefonVal) { errors.push("Has d'escriure el <strong>telèfon</strong>."); } 
        else if (telefonVal.length > 20) { errors.push("El <strong>telèfon</strong> és massa llarg."); }

        const emailVal = document.getElementById('email').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailVal) { errors.push("Has d'escriure l'<strong>email</strong>."); } 
        else if (!emailRegex.test(emailVal)) { errors.push("L'<strong>email</strong> no té un format vàlid."); } 
        else if (emailVal.length > 100) { errors.push("L'<strong>email</strong> és massa llarg."); }

        const adrecaVal = document.getElementById('adreca').value.trim();
        if (!adrecaVal) { errors.push("Has d'escriure l'<strong>adreça</strong>."); } 
        else if (adrecaVal.length > 255) { errors.push("L'<strong>adreça</strong> és massa llarga."); }

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

    ['nom', 'telefon', 'email', 'adreca'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            renderErrors(validateForm());
        });
    });

    if (typeof window._phpErrors !== 'undefined' && window._phpErrors.length > 0) {
        renderErrors(window._phpErrors);
    }
});
</script>

<?php require 'footer.php'; ?>