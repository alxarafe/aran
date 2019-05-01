<?php
/**
 * Alixar is a fork of Dolibarr powered with Alxarafe
 * Alxarafe. Development of PHP applications in a flash!
 * Copyright (C) 2018 Alxarafe <info@alxarafe.com>
 */
//session_start();

define('BASE_PATH', __DIR__);
define('DEBUG', true);

require_once BASE_PATH . '/vendor/autoload.php';
require_once BASE_PATH . '/alxarafe/vendor/autoload.php';

use Alixar\Helpers\AlixarDispatcher;

/**
 * If the execution of a module were through a class, this code would not be necessary.
 * It would be enough to invoke $dispatcher->run().
 * By not using a class and being direct code, it is necessary to obtain the route and
 * do an include of the code.
 */
$dispatcher = new AlixarDispatcher();

$controller = filter_input(INPUT_GET, 'call');
if (isset($controller)) {
    $className = $controller;
    $method = filter_input(INPUT_GET, 'method') ?: 'main';
    foreach ($dispatcher->searchDir as $nameSpace => $path) {
        $className = $nameSpace . '\\Controllers\\' . $controller;
        $controllerPath = $path . '/Controllers/' . $controller . '.php';
        if (file_exists($controllerPath)) {
            //require_once $controllerPath;
            $class = new $className;
            if (method_exists($class, $method)) {
                (new $className())->{$method}();
                return;
            }
        }
    }
}

/**
 * The installation uses the variable POST next to indicate the next step.
 * If it arrives here it is necessary to change the method to execute
 * (it is another file).
 */
$controller = filter_input(INPUT_GET, 'controller') ?: 'home';
$method = filter_input(INPUT_GET, 'next') ?: filter_input(INPUT_GET, 'method') ?: 'home';

$path = BASE_PATH . "/dolibarr/htdocs/$controller/$method.php";
include($path);
die('End');

if ($controller == 'install' && isset($method)) {
    include("dolibarr/htdocs/$controller/$method.php");
    exit;
}

if (isset($dispatcher)) {
    $dispatcher->run(); // It will be the only line needed in this block when the code is organized in classes.
    if (isset($dispatcher->path)) {
        include($dispatcher->path);
    }
}
