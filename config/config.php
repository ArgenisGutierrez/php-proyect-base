<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Configuracion Global
define('BASE_URL', $_ENV['BASE_URL']);
define('APP_ENV', $_ENV['APP_ENV']);
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('JWT_SECRET_KEY', $_ENV['JWT_SECRET_KEY']);
