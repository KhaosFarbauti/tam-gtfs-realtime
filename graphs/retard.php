<?php
declare(strict_types=1);

// Affiche les erreurs
ini_set('display_errors', '1');
error_reporting(E_ALL);

// Configuration
require_once '../config.php';

$cache_dir = __DIR__ . '/cache_graphs';
$cache_lifetime = 600; // 10 minutes

if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

// Route ID sécurisé
$route_id = filter_input(INPUT_GET, 'route_id', FILTER_SANITIZE_STRING);
if (!$route_id) {
    afficherMessageErreur("Parametre 'route_id' manquant.");
}

// Fichier cache
$cache_key = md5($route_id);
$cache_file = "$cache_dir/{$cache_key}.png";

// Si cache valide, on le sert directement
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_lifetime) {
    header('Content-Type: image/png');
    readfile($cache_file);
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_SERVERNAME . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    afficherMessageErreur("Erreur de connexion BDD.");
}

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
    afficherMessageErreur("Aucune donnee disponible");
}

$hours = array_column($data, 'hour');
$avg_delays = array_column($data, 'avg_delay');

// Paramètres du graphique
$width = 800;
$height = 400;
$marge = 50;
$barWidth = 20;

$img = imagecreatetruecolor($width, $height);

// Couleurs
$white = imagecolorallocate($img, 255, 255, 255);
$blue = imagecolorallocate($img, 0, 102, 204);
$black = imagecolorallocate($img, 0, 0, 0);
$gray = imagecolorallocate($img, 200, 200, 200);

// Fond
imagefill($img, 0, 0, $white);

// Axes
imageline($img, $marge, $height - $marge, $width - $marge, $height - $marge, $black);
imageline($img, $marge, $marge, $marge, $height - $marge, $black);

// Échelle Y
$maxDelay = max($avg_delays);
$maxY = ceil($maxDelay / 10) * 10;
$scaleY = ($height - 2 * $marge) / $maxY;

for ($i = 0; $i <= $maxY; $i += 10) {
    $y = $height - $marge - ($i * $scaleY);
    imageline($img, $marge, (int)$y, $width - $marge, (int)$y, $gray);
    imagestring($img, 2, 10, (int)($y - 6), "$i", $black);
}

// Barres
$stepX = ($width - 2 * $marge) / 24;

for ($h = 0; $h < 24; $h++) {
    $x = $marge + $h * $stepX;
    $index = array_search($h, $hours);
    $val = $index !== false ? $avg_delays[$index] : 0;
    $barHeight = $val * $scaleY;
    imagefilledrectangle($img, (int)($x - $barWidth / 2), $height - $marge, (int)($x + $barWidth / 2), (int)($height - $marge - $barHeight), $blue);
    imagestring($img, 2, (int)$x - 8, $height - $marge + 4, str_pad((string)$h, 2, '0', STR_PAD_LEFT), $black);
}

// Légende
imagestring($img, 3, ($width / 2) - 120, $marge - 25, "Retard moyen par heure - ligne $route_id (sec)", $black);

// Sauvegarde + sortie
imagepng($img, $cache_file);
imagedestroy($img);

header('Content-Type: image/png');
readfile($cache_file);
exit;

// Fonction pour afficher un message image si erreur
function afficherMessageErreur(string $message): void {
    $width = 600; $height = 200;
    $img = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($img, 255, 255, 255);
    $red = imagecolorallocate($img, 200, 0, 0);
    imagefill($img, 0, 0, $white);
    imagestring($img, 5, 20, $height / 2 - 10, $message, $red);
    header('Content-Type: image/png');
    imagepng($img);
    imagedestroy($img);
    exit;
}