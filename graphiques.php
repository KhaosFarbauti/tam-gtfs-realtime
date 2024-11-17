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
        <img src="/graphs/pourcentage_retard.php" alt="Pourcentage de lignes en retard">
    </div>
	<div class="graph">
        <img src="/graphs/moyenne_globale.php" alt="Retard moyen">
    </div>
	<p><a href="/">Voir les chiffres</a></p>
	<h1>Retard par ligne</h1>
<?php
	$liste_lignes = array(1,2,3,4,6,7,8,9,10,11,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,30,31,32,33,34,35,36,38,40,41,42,43,44,46,50,51,52,53,91,92,93,94,95,96);
	for ($i = 0; $i < count($liste_lignes); ++$i){
		$routeId="4-".$liste_lignes[$i];
		$routeName=isset($routes[$routeId]) ? $routes[$routeId] : $routeId;
?>
	<h2><?= htmlspecialchars($routeName) ?></h2>
	<div class="graph">
		<img src="/graphs/retard.php?route_id=<?= htmlspecialchars($liste_lignes[$i]) ?>" alt="Retard ligne <?= htmlspecialchars($liste_lignes[$i]) ?>">
	</div>
<?php
	};
?>
</body>
</html>