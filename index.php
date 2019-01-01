<?php
/**
 * Alixar is a fork of Dolibarr powered with Alxarafe
 * Alxarafe. Development of PHP applications in a flash!
 * Copyright (C) 2018 Alxarafe <info@alxarafe.com>
 */

define('BASE_PATH', __DIR__);

use Alxarafe\Helpers\Debug;

include BASE_PATH . '/config/constants.php';

require_once BASE_PATH . '/vendor/autoload.php';

$controller = filter_input(INPUT_GET, 'controller') ?: 'home';
$method = filter_input(INPUT_GET, 'method') ?: 'index';

if (isset($_POST['next'])) {
    $method = $_POST['next'];
    Debug::addMessage('Deprecated', 'This is necessary for installation. Current pass: ' . $method);
}

include "dolibarr/htdocs/$controller/$method.php";
