<?php

\set_time_limit(172800);

define('ROOT_PATH', realpath(__DIR__ . '/../..'));

spl_autoload_register(function (string $class) {
    // utilisation des namespaces pour l'autoload
    $class = \mb_ereg_replace('\\\\', '/', $class);
    // MB pour le dossier framework
    $class = \mb_ereg_replace('MB', 'framework', $class);
    // App pour le dossier application
    $class = \mb_ereg_replace('App', 'application', $class);
    $filePath = ROOT_PATH . "/$class.php";

    // chargement de la classe
    require $filePath;
});

$updatingService = new \App\services\UpdatingService();

$updatingService->run();