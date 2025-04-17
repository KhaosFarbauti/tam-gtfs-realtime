<?php

// URL du fichier ZIP GTFS
$url = 'https://data.montpellier3m.fr/TAM_MMM_GTFSRT/GTFS.zip';
// Nom local du fichier ZIP
$zipFile = 'GTFS.zip';
// Dossier où extraire les fichiers
$extractDir = __DIR__ . '/gtfs_extract';

// Téléchargement du fichier ZIP
file_put_contents($zipFile, fopen($url, 'r'));

// Vérification de la création du fichier
if (!file_exists($zipFile)) {
    die("Erreur : Le fichier ZIP n'a pas pu être téléchargé.\n");
}

// Création du dossier d'extraction s'il n'existe pas
if (!is_dir($extractDir)) {
    mkdir($extractDir, 0755, true);
}

// Ouverture de l'archive ZIP
$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    // Liste des fichiers à extraire
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

// Suppression du fichier ZIP
unlink($zipFile);
echo "Fichier ZIP supprimé : $zipFile\n";

?>
