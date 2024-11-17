<?php
include '../config.php';

$host = DB_SERVERNAME;
$user = DB_USERNAME;
$password = DB_PASSWORD;
$dbname = DB_NAME;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération du route_id depuis la query string
$route_id = isset($_GET['route_id']) ? $_GET['route_id'] : null;

if (!$route_id) {
    die("Veuillez spécifier un route_id dans la query string (par exemple : ?route_id=1).");
}

// Récupération des moyennes horaires pour le route_id spécifié
$query = $pdo->prepare("
    SELECT HOUR(recorded_at) AS hour, AVG(retard) AS avg_delay
    FROM historisation
    WHERE route_id = :route_id
    GROUP BY HOUR(recorded_at)
    ORDER BY hour ASC
");

$query->execute(['route_id' => $route_id]);
$data = $query->fetchAll(PDO::FETCH_ASSOC);

if (empty($data)) {
    die("Aucune donnée disponible pour le route_id spécifié.");
}

// Extraction des heures et des moyennes
$hours = array_column($data, 'hour');
$avg_delays = array_column($data, 'avg_delay');

// Paramètres du graphique
$width = 800;
$height = 400;
$marge = 50;
$barWidth = 20; // Largeur des barres
$img = imagecreate($width, $height);

// Couleurs
$background = imagecolorallocate($img, 255, 255, 255);
$barColor = imagecolorallocate($img, 0, 0, 255); // Couleur pour les barres
$textColor = imagecolorallocate($img, 0, 0, 0);
$gridColor = imagecolorallocate($img, 200, 200, 200);

// Dessiner le fond
imagefill($img, 0, 0, $background);

// Axes
imageline($img, $marge, $height - $marge, $width - $marge, $height - $marge, $textColor); // Axe X
imageline($img, $marge, $height - $marge, $marge, $marge, $textColor); // Axe Y

// Calcul de l'échelle
$maxDelay = ceil(max($avg_delays)); // Maximum arrondi à l'entier supérieur
$maxDelay = (ceil($maxDelay / 10) * 10);
$stepY = ($height - 2 * $marge) / $maxDelay;

for ($i = 0; $i <= $maxDelay; $i += 10) {
    $y = $height - $marge - $i * $stepY;
    imageline($img, $marge, $y, $width - $marge, $y, $gridColor);
    imagestring($img, 2, 10, $y - 6, (string)$i, $textColor);
}

// Dessiner les barres de l'histogramme
$stepX = ($width - 2 * $marge) / 24; // Division par 24 pour les 24 heures

for ($hour = 0; $hour < 24; $hour++) {
    $x = $marge + $hour * $stepX; // Position de la barre en fonction de l'heure
    $avgDelay = in_array($hour, $hours) ? $avg_delays[array_search($hour, $hours)] : 0;
    $barHeight = $avgDelay * $stepY; // Hauteur de la barre basée sur la moyenne
    $y1 = $height - $marge; // Bas de la barre
    $y2 = $y1 - $barHeight; // Haut de la barre

    // Dessin de la barre
    imagefilledrectangle($img, $x - $barWidth / 2, $y1, $x + $barWidth / 2, $y2, $barColor);

    // Annotation de l'heure en bas
    imagestring($img, 2, $x - 10, $height - $marge + 5, (string)$hour, $textColor);
}

// Légende
imagestring($img, 3, $width / 2 - 50, $marge - 20, "Retard moyen par heure de la ligne $route_id (en secondes)", $textColor);

// Affichage de l'image
header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
?>
