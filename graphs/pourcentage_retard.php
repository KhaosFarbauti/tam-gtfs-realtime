<?php
include '../config.php';

$cache_dir = __DIR__ . '/cache_graphs';
$cache_lifetime = 300; // 5 minutes

if (!file_exists($cache_dir)) {
    mkdir($cache_dir, 0755, true);
}

$cache_key = md5("pourcentage");
$cache_file = "$cache_dir/$cache_key.png";

if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_lifetime) {
    // Sert l'image en cache
    header('Content-Type: image/png');
    readfile($cache_file);
} else {

$host = DB_SERVERNAME;
$user = DB_USERNAME;
$password = DB_PASSWORD;
$dbname = DB_NAME;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// Récupération des données
$query = $pdo->query("SELECT recorded_at, total_retard, total_lignes, delay_moyen FROM etat_global WHERE recorded_at >= NOW() - INTERVAL 7 DAY ORDER BY recorded_at ASC");
$data = $query->fetchAll(PDO::FETCH_ASSOC);

// Vérifier si des données sont récupérées
if (empty($data)) {
    die("Aucune donnée disponible pour les 7 derniers jours.");
}

// Extraction des mesures et des timestamps
$timestamps = array_column($data, 'recorded_at');
$mesures = array_column($data, 'total_retard');
$mesures2 = array_column($data, 'total_lignes');


// Calcul du ratio mesure / mesure2
$ratios = array_map(function ($m, $m2) {
    return $m2 != 0 ? $m / $m2 : null; // Éviter la division par zéro
}, $mesures, $mesures2);

// Filtrer les valeurs nulles pour le ratio (cas où mesure2 = 0)
$validRatios = array_filter($ratios, fn($r) => $r !== null);
$minRatio = min($validRatios);
$maxRatio = max($validRatios);

// Paramètres du graphique
$width = 1000;
$height = 600;
$marge = 50;
$img = imagecreate($width, $height);

// Couleurs
$background = imagecolorallocate($img, 255, 255, 255);
$lineColor = imagecolorallocate($img, 0, 0, 255); // Couleur pour le ratio
$textColor = imagecolorallocate($img, 0, 0, 0);
$gridColor = imagecolorallocate($img, 200, 200, 200);

// Dessiner le fond
imagefill($img, 0, 0, $background);

// Axes
imageline($img, $marge, $height - $marge, $width - $marge, $height - $marge, $textColor); // Axe X
imageline($img, $marge, $height - $marge, $marge, $marge, $textColor); // Axe Y

// Échelles
$nbPoints = count($ratios);
$stepX = ($width - 2 * $marge) / ($nbPoints - 1);
$stepY = ($height - 2 * $marge) / ($maxRatio - $minRatio);

// Grille et annotations pour l'axe Y (en pourcentage)
for ($i = 0; $i <= $maxRatio; $i += 0.1) { // Graduation tous les 0.1
    $y = $height - $marge - $i * $stepY;
    imageline($img, $marge, $y, $width - $marge, $y, $gridColor);
    $percentage = intval($i * 100); // Convertir en pourcentage
    imagestring($img, 2, 5, $y - 6, "$percentage%", $textColor);
}

// Annotations pour l'axe X
for ($i = 0; $i < $nbPoints; $i++) {
    $x = $marge + $i * $stepX;
    if ($i % max(1, floor($nbPoints / 10)) == 0) {
        $formattedDate = date('d/m H:i', strtotime($timestamps[$i]));
        imagestring($img, 2, $x - 20, $height - $marge + 5, $formattedDate, $textColor);
        imageline($img, $x, $height - $marge, $x, $marge, $gridColor); // Ligne de grille verticale
    }
}

// Tracer la courbe du ratio mesure / mesure2
for ($i = 0; $i < $nbPoints - 1; $i++) {
    if ($ratios[$i] !== null && $ratios[$i + 1] !== null) { // Sauter les points où mesure2 = 0
        $x1 = $marge + $i * $stepX;
        $y1 = $height - $marge - ($ratios[$i] - $minRatio) * $stepY;
        $x2 = $marge + ($i + 1) * $stepX;
        $y2 = $height - $marge - ($ratios[$i + 1] - $minRatio) * $stepY;
        imageline($img, $x1, $y1, $x2, $y2, $lineColor);
    }
}

// Légende
imagestring($img, 3, $width / 2 - 50, $marge - 20, "% de bus/tram en retard sur les 7 derniers jours", $lineColor);

imagepng($img, $cache_file);
imagedestroy($img);

// Affichage de l'image
header('Content-Type: image/png');
readfile($cache_file);
}

?>