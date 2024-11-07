<?php
include 'config.php';
require 'GPBMetadata/GtfsRealtime.php';

function requireAllPhpFiles($directory) {
    foreach (glob($directory . '/*.php') as $filename) {
        require $filename;
    }

    foreach (glob($directory . '/*', GLOB_ONLYDIR) as $subdir) {
        requireAllPhpFiles($subdir);
    }
}

requireAllPhpFiles('Transit_realtime');


use \Transit_realtime\FeedMessage;


function loadRoutes($filename) {
    $routes = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $routeId = $data[array_search('route_id', $header)];
            $routeName = $data[array_search('route_long_name', $header)];
            $routes[$routeId] = $routeName;
        }
        fclose($handle);
    }
    return $routes;
}

function loadStops($filename) {
    $stops = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        $header = fgetcsv($handle, 1000, ",");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $stopId = $data[array_search('stop_id', $header)];
            $stopName = $data[array_search('stop_name', $header)];
            $stops[$stopId] = $stopName;
        }
        fclose($handle);
    }
    return $stops;
}

$routes = loadRoutes('routes.txt');
$stops = loadStops('stops.txt');

function getDelays() {
    global $routes, $stops;
    $url = GTFS_RT_URL_DELAYS;

    $rawData = file_get_contents($url);

    if ($rawData === FALSE) {
        echo "<p>Erreur : impossible de récupérer les données du flux GTFS-RT.</p>";
        return [];
    }

    try {
        $feed = new FeedMessage();
        $feed->mergeFromString($rawData);
    } catch (Exception $e) {
        echo "<p>Erreur : échec du décodage des données GTFS-RT - " . htmlspecialchars($e->getMessage()) . "</p>";
        return [];
    }

    $delays = [];
	$delaysCount = 0;
    $ontimeCount = 0;
    $earlyCount = 0;
	$totalDelay = 0;

	try {
        foreach ($feed->getEntity() as $entity) {
            if ($entity->hasTripUpdate()) {
                $tripUpdate = $entity->getTripUpdate();
                $routeId = substr($tripUpdate->getTrip()->getRouteId(),2);
                $stopTimeUpdates = $tripUpdate->getStopTimeUpdate();
                
                // Vérifier que l'array getStopTimeUpdate() n'est pas vide avant d'y accéder
                if (!empty($stopTimeUpdates) && (count($stopTimeUpdates) > 0)) {
                    $stopTimeUpdate = $stopTimeUpdates[0]; // Récupérer le premier élément
					if (is_null($stopTimeUpdate->getDeparture())) {
						$delay = 0;
						$scheduledDeparture = 0;
					} else {
						$delay = $stopTimeUpdate->getDeparture()->getDelay();
						$scheduledDeparture = $stopTimeUpdate->getDeparture()->getTime();
					}
                    $stopId = $stopTimeUpdate->getStopId();

                    // Calcul de l'heure de départ estimée
                    $estimatedDeparture = $scheduledDeparture + $delay;
                    $estimatedDepartureFormatted = date("H:i", $estimatedDeparture);

                    // Obtenir les noms de la ligne et de l'arrêt
                    $routeName = isset($routes[$routeId]) ? $routes[$routeId] : $routeId;
                    $stopName = isset($stops[$stopId]) ? $stops[$stopId] : $stopId;

                    // Ajouter au tableau des retards
                    $delays[] = [
                        'route' => $routeName,
                        'stop' => $stopName,
                        'delay' => $delay,
                        'estimated_departure' => $estimatedDepartureFormatted
                    ];

                    // Décompte des lignes en retard, à l'heure et en avance
                    if ($delay > 0) {
                        $delaysCount++;
						$totalDelay += $delay;
                    } elseif ($delay == 0) {
                        $ontimeCount++;
                    } elseif ($delay < 0) {
                        $earlyCount++;
                    }
                }
            }
        }		
		
    } catch (Exception $e) {
        echo "<p>Erreur lors de la lecture des données de retards : " . htmlspecialchars($e->getMessage()) . "</p>";
        return [];
    }
	
    usort($delays, function($a, $b) {
        $routeComparison = strcmp($a['route'], $b['route']);
        if ($routeComparison === 0) {
            return strcmp($a['stop'], $b['stop']);
        }
        return $routeComparison;
    });

	$averageDelay = $delaysCount > 0 ? $totalDelay / $delaysCount : 0;

    return [
        'delays' => $delays,
        'delaysCount' => $delaysCount,
        'ontimeCount' => $ontimeCount,
        'earlyCount' => $earlyCount,
		'averageDelay' => $averageDelay
    ];
}

function getServiceAlerts() {
    $url = GTFS_RT_URL_ALERTS;

    $rawData = file_get_contents($url);

    if ($rawData === FALSE) {
        echo "<p>Erreur : impossible de récupérer les données du flux GTFS-RT.</p>";
        return [];
    }

    try {
        $feed = new FeedMessage();
        $feed->mergeFromString($rawData);
    } catch (Exception $e) {
        echo "<p>Erreur : échec du décodage des données GTFS-RT - " . htmlspecialchars($e->getMessage()) . "</p>";
        return [];
    }

    $alerts = [];
    foreach ($feed->getEntity() as $entity) {
        if ($entity->hasAlert()) {
            $alert = $entity->getAlert();
            $severityLevel = $alert->getSeverityLevel();
            $alertHeader = $alert->getHeaderText()->getTranslation()[0]->getText();
            $alertDescription = $alert->getDescriptionText()->getTranslation()[0]->getText();

            $alerts[] = [
                'severity' => $severityLevel,
                'header' => $alertHeader,
                'description' => $alertDescription
            ];
        }
    }

    return $alerts;
}

?>