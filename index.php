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
$averageDelay = round($delayData['averageDelay']/60,0);
$alerts = getServiceAlerts();

$delayFilter = $_GET['delayFilter'] ?? 'all'; // Par défaut : tous les retards

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Retards et alertes du réseau TaM</title>
	<link rel="stylesheet" href="styles.css">
</head>
<body>
	<h1>Informations du réseau TaM</h1>
	<p><i>Donn&eacute;es issues de <a href="https://data.montpellier3m.fr/" />https://data.montpellier3m.fr/</a></i></p>

	<table class="dataTable">
		<thead>
			<tr>
				<th>Etat Global du réseau</th>
				<th>Nombre de bus/tram</th>
				<th>Retard moyen</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?= round($delaysCount / ($delaysCount+$ontimeCount+$earlyCount) * 100,0); ?>% de bus/tram en retard</td>
				<td>en retard : <?= $delaysCount ?><br />à l'heure : <?= $ontimeCount ?><br />en avance : <?= $earlyCount ?></td>
				<td><?= $averageDelay ?> minute<?php if ($averageDelay>1): ?>s<?php endif; ?></td>
			</tr>
		</tbody>
	</table>

<?php storeDB($delaysCount, ($delaysCount+$ontimeCount+$earlyCount), $averageDelay, "etat_global"); ?>

	<h2>Alertes en cours</h2>
<?php if (!empty($alerts)): ?>
	<table class="dataTable">
		<thead>
			<tr>
				<th>Classification</th>
				<th>Message</th>
			</tr>
		</thead>
		<tbody>
<?php $alert = null; ?>
<?php $alertpre = null; ?>
<?php foreach ($alerts as $alert): ?>
<?php if ($alert != $alertpre): ?>
			<tr>
				<td><?= htmlspecialchars($alert['header']) ?></td>
				<td><?= htmlspecialchars($alert['description']) ?></td>
			</tr>
<?php $alertpre = $alert; ?>
<?php endif; ?>
<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p>Aucune alerte en cours pour le moment.</p>
<?php endif; ?>

	<h2>Liste des retards du r&eacute;seau TaM</h2>
<?php if (!empty($delays)): ?>

	<form method="GET" action="">
		<label for="delayFilter">Afficher :</label>
			<select id="delayFilter" name="delayFilter">
				<option value="all" <?= (!isset($_GET['delayFilter']) || $_GET['delayFilter'] == 'all') ? 'selected' : '' ?>>Tous les décalages</option>
				<option value="over60" <?= (isset($_GET['delayFilter']) && $_GET['delayFilter'] == 'over60') ? 'selected' : '' ?>>Les retards supérieurs à 60 secondes</option>
			</select>
		<button type="submit">Filtrer</button>
	</form>


	<table class="dataTable">
		<thead>
			<tr>
				<th>Ligne</th>
				<th>Arrêt</th>
				<th>Retard (s)</th>
				<th>Heure de r&eacute;f&eacute;rence</th>
				<th>Heure de d&eacute;part estim&eacute;e</th>
			</tr>
		</thead>
		<tbody>
<?php foreach ($delays as $delay): ?>
<?php storeDB($delay['route_id'], $delay['stop_id'], $delay['delay'], "historisation"); ?>
<?php
if ($delayFilter == 'over60' && $delay['delay'] <= 60) {
	continue;
}
?>
<?php if ($delay['delay']!=0): ?>
			<tr>
				<td><?= htmlspecialchars($delay['route']) ?></td>
				<td><?= htmlspecialchars($delay['stop']) ?></td>
				<td><?= htmlspecialchars($delay['delay']) ?></td>
				<td><?= htmlspecialchars($delay['scheduled_departure']) ?></td>
				<td><?= htmlspecialchars($delay['estimated_departure']) ?></td>
			</tr>
<?php endif; ?>
<?php endforeach; ?>
		</tbody>
	</table>
<?php else: ?>
	<p>Aucun retard détecté pour le moment.</p>
<?php endif; ?>
	<p class="footer">Cod&eacute; par Khaos Farbauti Ibn Oblivion - <a href="https://github.com/KhaosFarbauti/tam-gtfs-realtime">Sources Github</a></p>
</body>
</html>