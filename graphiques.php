<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'gtfs.php';

$host = DB_SERVERNAME;
$user = DB_USERNAME;
$password = DB_PASSWORD;
$dbname = DB_NAME;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

function afficherGraphique($routeId, $routeName) {
    $safeRouteId = htmlspecialchars($routeId);
    $safeRouteName = htmlspecialchars($routeName);
    return <<<HTML
    <h2>{$safeRouteName}</h2>
    <div class="graph">
        <a href="/graphs/retard.php?route_id={$safeRouteId}">
            <img src="/graphs/retard.php?route_id={$safeRouteId}" alt="Retard ligne {$safeRouteId}" title="Cliquez pour voir les détails de {$safeRouteName}">
        </a>
    </div>
HTML;
}
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
            margin: 10px;
        }
    </style>
</head>
<body>
    <h1>État global</h1>
    <div class="graph">
        <a href="/graphs/pourcentage_retard.php">
            <img src="/graphs/pourcentage_retard.php" alt="Pourcentage de lignes en retard" title="Cliquez pour voir le pourcentage de retard">
        </a>
    </div>
    <div class="graph">
        <a href="/graphs/moyenne_globale.php">
            <img src="/graphs/moyenne_globale.php" alt="Retard moyen" title="Cliquez pour voir la moyenne des retards">
        </a>
    </div>
    <p><a href="/">Voir les données temps réel</a></p>

    <h1>Retard par lignes</h1>
<?php
    $liste_lignes = getLignes();
    $aujourdhui = date("Y-m-d");
    $nb_lignes = count($liste_lignes);

    for ($i = 0; $i < $nb_lignes; ++$i) {
        $routeId = $liste_lignes[$i];
        $routeName = isset($routes[$routeId]) ? $routes[$routeId] : $routeId;

        $query = $pdo->prepare("SELECT id FROM historisation WHERE route_id = :route_id AND recorded_at >= :aujourdhui LIMIT 1");
        $query->bindParam(':route_id', $routeId);
        $query->bindParam(':aujourdhui', $aujourdhui);
        $query->execute();
        $data = $query->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($data)) {
            echo afficherGraphique($routeId, $routeName);
        }
    }
?>
    <p class="footer">
        Données issues de <a href="https://data.montpellier3m.fr/">https://data.montpellier3m.fr/</a> —
        Codé par Khaos Farbauti Ibn Oblivion —
        <a href="https://github.com/KhaosFarbauti/tam-gtfs-realtime">Sources GitHub</a>
    </p>
</body>
</html>