<?php


use MB\Kernel;

// utilisation de l'utf-8 pour toutes les fonctions multibytes et regexp
mb_internal_encoding("UTF-8");
mb_regex_encoding("UTF-8");

// définition de constantes de chemin d'acces
define('ROOT_PATH', realpath(__DIR__ . '/..'));
$relativeRootPath = dirname($_SERVER['SCRIPT_NAME']);
define('RELATIVE_ROOT_PATH', $relativeRootPath === '/' ? '' : $relativeRootPath);

// on inclus les fichiers de config et le Kernel pour l'autoload
include_once ROOT_PATH . '/config/server.php';
require_once ROOT_PATH . '/framework/Kernel.php';

$Kernel = new Kernel();
$Kernel->run();