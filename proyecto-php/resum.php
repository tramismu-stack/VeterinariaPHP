<?php
require 'db.php';

// 1. Estadístiques globals (Total propietaris, animals, visites)
$sql_totals = "SELECT 
    (SELECT COUNT(*) FROM propietaris) as total_prop,
    (SELECT COUNT(*) FROM animals) as total_anim,
    (SELECT COUNT(*) FROM visites) as total_visites";
$res_totals = mysqli_query($conn, $sql_totals);
$totals = mysqli_fetch_assoc($res_totals);

// 2. Ingressos del mes actual
$sql_ingressos = "SELECT SUM(preu) as total_mes FROM visites 
                  WHERE MONTH(data_visita) = MONTH(CURRENT_DATE()) 
                  AND YEAR(data_visita) = YEAR(CURRENT_DATE())";
$res_ingressos = mysqli_query($conn, $sql_ingressos);
$ingressos = mysqli_fetch_assoc($res_ingressos);
$total_mes = $ingressos['total_mes'] ? $ingressos['total_mes'] : 0;

// 3. Els 3 animals amb més visites
// Nota: Comprova que la teva columna de la taula visites es diu 'id_animal'.
$sql_top = "SELECT a.nom, a.especie, COUNT(v.id) as num_visites 
            FROM animals a 
            JOIN visites v ON a.id = v.id_animal 
            GROUP BY a.id 
            ORDER BY num_visites DESC LIMIT 3";
$res_top = mysqli_query($conn, $sql_top);

// 4. Últimes 5 visites realitzades
$sql_ultimes = "SELECT v.data_visita, a.nom as animal, p.nom as propietari, v.motiu, v.preu 
                FROM visites v 
                JOIN animals a ON v.id_animal = a.id 
                JOIN propietaris p ON a.id_propietari = p.id 
                ORDER BY v.data_visita DESC LIMIT 5";
$res_ultimes = mysqli_query($conn, $sql_ultimes);

require 'header.php'; 
?>

<h1>Dashboard Clínic</h1>

<div class="dashboard-grid">
    <div class="card bg-blau">
        <h3>Propietaris</h3>
        <div class="num"><?php echo $totals['total_prop']; ?></div>
    </div>
    <div class="card bg-verd">
        <h3>Animals</h3>
        <div class="num"><?php echo $totals['total_anim']; ?></div>
    </div>
    <div class="card bg-taronja">
        <h3>Visites Totals</h3>
        <div class="num"><?php echo $totals['total_visites']; ?></div>
    </div>
    <div class="card bg-lila">
        <h3>Ingressos (Mes Actual)</h3>
        <div class="num"><?php echo number_format($total_mes, 2); ?> €</div>
    </div>
</div>

<div class="dashboard-flex">
    <div class="panel meitat">
        <h2>🏆 Pacients Més Freqüents</h2>
        <ul>
            <?php while ($top = mysqli_fetch_assoc($res_top)) { ?>
                <li>
                    <strong><?php echo htmlspecialchars($top['nom']); ?></strong> 
                    (<?php echo htmlspecialchars($top['especie']); ?>) - 
                    <span class="badge"><?php echo $top['num_visites']; ?> visites</span>
                </li>
            <?php } ?>
            <?php if (mysqli_num_rows($res_top) == 0) echo "<li>No hi ha dades suficients.</li>"; ?>
        </ul>
    </div>

    <div class="panel meitat">
        <h2>⏱️ Últimes 5 Visites</h2>
        <table class="taula-resum">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Animal</th>
                    <th>Motiu</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($visita = mysqli_fetch_assoc($res_ultimes)) { ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($visita['data_visita'])); ?></td>
                        <td><?php echo htmlspecialchars($visita['animal']); ?></td>
                        <td><?php echo htmlspecialchars($visita['motiu']); ?></td>
                    </tr>
                <?php } ?>
                <?php if (mysqli_num_rows($res_ultimes) == 0) echo "<tr><td colspan='3'>Cap visita recent.</td></tr>"; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'footer.php'; ?>
