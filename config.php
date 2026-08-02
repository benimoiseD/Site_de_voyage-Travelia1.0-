<?php

// Configuration de la base de données
// Utilisez des variables d'environnement lorsque possible :
// TRAVELIA_DB_HOST, TRAVELIA_DB_USER, TRAVELIA_DB_PASSWORD, TRAVELIA_DB_NAME

define('DB_HOST', getenv('TRAVELIA_DB_HOST') ?: 'localhost');
define('DB_USER', getenv('TRAVELIA_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('TRAVELIA_DB_PASSWORD') ?: '');
define('DB_NAME', getenv('TRAVELIA_DB_NAME') ?: 'travelia2_safari_rdc');
define('DB_CHARSET', 'utf8mb4');

define('APP_ROOT', __DIR__);
define('INCLUDE_DIR', APP_ROOT . '/INCLUDE');
define('DATA_DIR', APP_ROOT . '/BASE_DES_DONNEES');
define('SITE_URL', getenv('TRAVELIA_SITE_URL') ?: 'http://localhost/travelia2');
define('SITE_NAME', getenv('TRAVELIA_SITE_NAME') ?: 'SAFARI-RDC+');
define('APP_ENV', getenv('TRAVELIA_APP_ENV') ?: 'development');

define('DEFAULT_TIMEZONE', getenv('TRAVELIA_TIMEZONE') ?: 'UTC');
date_default_timezone_set(DEFAULT_TIMEZONE);
