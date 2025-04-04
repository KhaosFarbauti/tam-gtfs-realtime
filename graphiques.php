<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'gtfs.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Graphiques des mesures</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin: 20px;
        }
        .graph {
            display: inline-block;
            border: 1px solid #ccc;
            padding: 10px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Etat global</h1>
    <div class="graph">
        <a href="/graphs/pourcentage_retard.php"><img src="/graphs/pourcentage_retard.php" alt="Pourcentage de lignes en retard"></a>
    </div>
	<div class="graph">
        <a href="/graphs/moyenne_globale.php"><img src="/graphs/moyenne_globale.php" alt="Retard moyen"></a>
    </div>
	<p><a href="/">Voir les donn&eacute;es temps r&eacute;el</a></p>
	<h1>Retard par lignes</h1>
<?php
	$liste_lignes = getLignes();
	for ($i = 0; $i < count($liste_lignes); ++$i){
		$routeName=isset($routes[$liste_lignes[$i]]) ? $routes[$liste_lignes[$i]] : $liste_lignes[$i];
?>
	<h2><?= htmlspecialchars($routeName) ?></h2>
	<div class="graph">
		<a href="/graphs/retard.php?route_id=<?= htmlspecialchars($liste_lignes[$i]) ?>"><img src="/graphs/retard.php?route_id=<?= htmlspecialchars($liste_lignes[$i]) ?>" alt="Retard ligne <?= htmlspecialchars($liste_lignes[$i]) ?>"></a>
	</div>
<?php
	};
?>
	<p class="footer">Donn&eacute;es issues de <a href="https://data.montpellier3m.fr/" />https://data.montpellier3m.fr/</a> - Cod&eacute; par Khaos Farbauti Ibn Oblivion - <a href="https://github.com/KhaosFarbauti/tam-gtfs-realtime">Sources Github</a></p>
</body>
</html>