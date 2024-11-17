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

// Récupération des données agrégées par heure
$query = $pdo->query("
    SELECT HOUR(recorded_at) AS hour, AVG(delay_moyen) AS avg_delay
    FROM etat_global
    GROUP BY HOUR(recorded_at)
    ORDER BY hour ASC
");
$data = $query->fetchAll(PDO::FETCH_ASSOC);

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
$stepY = ($height - 2 * $marge) / $maxDelay;

// Grille et annotations pour l'axe Y
for ($i = 0; $i <= $maxDelay; $i++) {
    $y = $height - $marge - $i * $stepY;
    imageline($img, $marge, $y, $width - $marge, $y, $gridColor);
    imagestring($img, 2, 5, $y - 6, (string)$i, $textColor);
}

// Dessiner les barres de l'histogramme
$nbHours = count($hours);
$stepX = ($width - 2 * $marge) / 24; // Division par 24 pour les 24 heures

foreach ($hours as $index => $hour) {
    $x = $marge + $hour * $stepX; // Position de la barre en fonction de l'heure
    $barHeight = $avg_delays[$index] * $stepY; // Hauteur de la barre basée sur la moyenne
    $y1 = $height - $marge; // Bas de la barre
    $y2 = $y1 - $barHeight; // Haut de la barre

    // Dessin de la barre
    imagefilledrectangle($img, $x - $barWidth / 2, $y1, $x + $barWidth / 2, $y2, $barColor);

    // Annotation de l'heure en bas
    imagestring($img, 2, $x - 10, $height - $marge + 5, (string)$hour, $textColor);
}

// Légende
imagestring($img, 3, $width - 200, $marge - 20, "Retard moyen par heure", $textColor);

// Affichage de l'image
header('Content-Type: image/png');
imagepng($img);
imagedestroy($img);
?>
