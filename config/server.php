<?php

namespace config;

\set_time_limit(360);

// Les cookies sont disponibles "int" secondes et uniquement pour le chemin d'acces "string"
\session_set_cookie_params(86400, (\mb_eregi_replace('[\\\\|/]public', '', RELATIVE_ROOT_PATH)));

// Temps maximal de conservation des cookies : "int" secondes
\ini_set('session.gc_maxlifetime', 86400);

// Nom du cookie de session : "string"
\ini_set('session.name', 'session_project');

// Les sessions seront stockés dans "string"
\ini_set('session.save_path', ROOT_PATH . '/var/sessions');
