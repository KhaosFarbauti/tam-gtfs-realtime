# GTFS-RT PHP API pour les Transports de Montpellier Méditerranée Métropole (TaM)

Cette implémentation en PHP permet de lire les données en temps réel de l'offre de transport de Montpellier Méditerranée Métropole (TaM) via l'API GTFS-RT. Il se concentre sur les informations de retards.

**Source de l'API :** [Montpellier Méditerranée Métropole - Offre de transport TaM en temps réel](https://data.montpellier3m.fr/dataset/offre-de-transport-tam-en-temps-reel)

## Prérequis

- **PHP** (version 7.4 ou supérieure)
- **php protobuf** : Ce module est nécessaire pour décoder les fichiers au format protobuf utilisés par l'API GTFS-RT.
- **php-gd** : Pour la génération des graphiques

## URL de Démonstration

https://tam.chaosklub.com/

## CRON

update_arrets.php actualise les éléments de référence (Nom des lignes, nom des arrêts, ...)
Ce fichier doit être appelé via une tache cron
