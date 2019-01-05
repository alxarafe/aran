<?php
/**
 * Alxarafe. Development of PHP applications in a flash!
 * Copyright (C) 2018 Alxarafe <info@alxarafe.com>
 */
namespace Alixar\Helpers;

class AlixarDispatcher extends \Alxarafe\Helpers\Dispatcher
{

    public $path;

    public function __construct()
    {
        parent::__construct();

        $this->searchDir[] = BASE_PATH . '/plugins';
        $path = null;
    }

    /**
     * Define the constants of the application
     */
    public function defineConstants()
    {
        parent::defineConstants();

        /**
         * Alixar is a fork of Dolibarr powered with Alxarafe
         * Alxarafe. Development of PHP applications in a flash!
         * Copyright (C) 2018 Alxarafe <info@alxarafe.com>
         */
        /**

          define('APP_URI', pathinfo(filter_input(INPUT_SERVER, 'SCRIPT_NAME'), PATHINFO_DIRNAME));

          define('SERVER_NAME', filter_input(INPUT_SERVER, 'SERVER_NAME'));
          define('APP_PROTOCOL', filter_input(INPUT_SERVER, 'REQUEST_SCHEME'));
          define('SITE_URL', APP_PROTOCOL . '://' . SERVER_NAME);
          define('BASE_URI', SITE_URL . APP_URI);
         */
        define('DOL_BASE_PATH', BASE_PATH . '/dolibarr/htdocs');
        define('DOL_BASE_URI', BASE_URI . '/dolibarr/htdocs');
//define('DOL_DOCUMENT_ROOT', DOL_BASE_PATH);

        define('CORE_FOLDER', '/core');
        define('CONFIG_FOLDER', '/core');
        define('CONTROLLERS_FOLDER', '/controllers');
        define('HELPERS_FOLDER', '/helpers');
        define('MODELS_FOLDER', '/models');
        define('SKINS_FOLDER', '/views/skins');
        define('TEMPLATES_FOLDER', '/views/templates');
        define('PLUGINS_FOLDER', '/plugins');
        define('CACHE_FOLDER', '/../cache');
        //define('VENDOR_FOLDER', BASE_URI . '/vendor');

        define('CORE_PATH', BASE_PATH . CORE_FOLDER);
        define('CONFIG_PATH', BASE_PATH . CONFIG_FOLDER);
        define('CONTROLLERS_PATH', BASE_PATH . CONTROLLERS_FOLDER);
        define('HELPERS_PATH', BASE_PATH . HELPERS_FOLDER);
        define('MODELS_PATH', BASE_PATH . MODELS_FOLDER);
        define('SKINS_PATH', BASE_PATH . SKINS_FOLDER);
        define('TEMPLATES_PATH', BASE_PATH . TEMPLATES_FOLDER);
        define('PLUGINS_PATH', BASE_PATH . PLUGINS_FOLDER);
        define('CACHE_PATH', BASE_PATH . CACHE_FOLDER);
        //define('VENDOR_PATH', BASE_PATH . VENDOR_FOLDER);
    }

    public function process()
    {
        if (parent::process()) {
            return;
        }

        $controller = filter_input(INPUT_GET, 'controller') ?: 'home';
        $method = filter_input(INPUT_GET, 'method') ?: 'home';

        $this->path = "dolibarr/htdocs/$controller/$method.php";
        if (file_exists($this->path)) {
            return true;
        }
        $this->path = null;
        return false;
    }
}
