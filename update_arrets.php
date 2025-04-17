<?php

$url = 'https://data.montpellier3m.fr/TAM_MMM_GTFSRT/GTFS.zip'; // URL du fichier GTFS TaM
$zipFile = 'GTFS.zip';
$extractDir = __DIR__ . '/gtfs_extract';

file_put_contents($zipFile, fopen($url, 'r'));

if (!file_exists($zipFile)) {
    die("Erreur : Le fichier ZIP n'a pas pu être téléchargé.\n");
}

if (!is_dir($extractDir)) {
    mkdir($extractDir, 0755, true);
}

$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {

    $fichiersASauver = ['routes.txt', 'stops.txt'];
    
    foreach ($fichiersASauver as $fichier) {
        $index = $zip->locateName($fichier, ZipArchive::FL_NOCASE);
        if ($index !== false) {
            $zip->extractTo($extractDir, [$zip->getNameIndex($index)]);
            echo "Fichier extrait : $fichier\n";
        } else {
            echo "Fichier introuvable dans le zip : $fichier\n";
        }
    }

    $zip->close();
} else {
    die("Erreur : Impossible d'ouvrir l'archive ZIP.\n");
}

unlink($zipFile);
echo "Fichier ZIP supprimé : $zipFile\n";

?>
