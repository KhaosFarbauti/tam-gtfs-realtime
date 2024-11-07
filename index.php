<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'gtfs.php';

$delayData = getDelays();
$delays = $delayData['delays'];
$delaysCount = $delayData['delaysCount'];
$ontimeCount = $delayData['ontimeCount'];
$earlyCount = $delayData['earlyCount'];
$averageDelay = $delayData['averageDelay'];
$alerts = getServiceAlerts();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retards et alertes du réseau TaM</title>
</head>
<body>
	<h1>Informations du réseau TaM</h1>
	<p><i>Donn&eacute;es issues de <a href="https://data.montpellier3m.fr/" />https://data.montpellier3m.fr/</a></i></p>
	
	<p><strong>Etat Global du réseau : </strong><?= round($delaysCount / ($delaysCount+$ontimeCount+$earlyCount) * 100,0); ?>% de bus/tram en retard (Retard moyen : <?= round($averageDelay / 60,0) ?> minutes)</p>

    <p><strong>Nombre de bus/tram en retard : </strong><?= $delaysCount ?><br /><strong>Nombre de bus/tram à l'heure : </strong><?= $ontimeCount ?><br /><strong>Nombre de bus/tram en avance : </strong><?= $earlyCount ?></p>

	<h2>Incidents en cours</h2>
    <?php if (!empty($alerts)): ?>
        <ul>
            <?php foreach ($alerts as $alert): ?>
                <li>
                    <strong>Gravité : </strong><?= htmlspecialchars($alert['severity']) ?><br>
                    <strong>Titre : </strong><?= htmlspecialchars($alert['header']) ?><br>
                    <strong>Description : </strong><?= htmlspecialchars($alert['description']) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Aucun incident en cours pour le moment.</p>
    <?php endif; ?>

    <h2>Liste des retards du réseau TaM</h2>
    <?php if (!empty($delays)): ?>
        <table>
            <tr>
                <th>Route</th>
                <th>Arrêt</th>
                <th>Retard</th>
				<th>Heure de départ estimée</th>
            </tr>
            <?php foreach ($delays as $delay): ?>
				<?php if ($delay['delay']!=0): ?>
                <tr>
                    <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($delay['route']) ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($delay['stop']) ?></td>
                    <td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($delay['delay']) ?></td>
					<td style="text-align: center; vertical-align: middle;"><?= htmlspecialchars($delay['estimated_departure']) ?></td>
                </tr>
				<?php endif; ?>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Aucun retard détecté pour le moment.</p>
    <?php endif; ?>
</body>
</html>
