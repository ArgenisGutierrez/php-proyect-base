<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../storage/logs/app.log');

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../config/routes.php';
