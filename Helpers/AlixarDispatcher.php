<?php
/**
 * Alxarafe. Development of PHP applications in a flash!
 * Copyright (C) 2018 Alxarafe <info@alxarafe.com>
 */
namespace Alixar\Helpers;

use Alxarafe\Helpers\Dispatcher;

class AlixarDispatcher extends Dispatcher
{

    public $path;

    public function __construct()
    {
        parent::__construct();

        $this->searchDir['Alixar'] = constant('BASE_PATH');
        // $this->searchDir['Plugins'] = constant('BASE_PATH') . '/plugins';

        $this->path = null;
    }

    /**
     * Define the constants of the application
     */
    public function defineConstants(): void
    {
        parent::defineConstants();

        /**
         * Alixar is a fork of Dolibarr powered with Alxarafe
         * Alxarafe. Development of PHP applications in a flash!
         * Copyright (C) 2018 Alxarafe <info@alxarafe.com>
         */

        define('DOL_BASE_PATH', BASE_PATH . '/dolibarr/htdocs');
        define('DOL_BASE_URI', BASE_URI . '/dolibarr/htdocs');
        define('DOL_DOCUMENT_ROOT', DOL_BASE_PATH);

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

        /**
         * Alixar:
         *
         * When updating ckeditor, the moono-lisa skin disappears
         * and includes kama and moono.
         */
        define('CKEDITOR_SKIN', 'moono-lisa'); // Do not use moono-lisa. Use kama o moono.
    }

    /**
     * Run the application.
     * First check if an Alxarafe class is being invoked.
     * If not, try to locate a modified Dolibarr file to include in the index.php.
     * The name of the file will be returned in $this->path.
     *
     * @link (Spanish) https://alxarafe.es/crear-el-primer-controlador-con-alxarafe-para-alixar-fork-de-dolibarr/
     * @link (English) https://alxarafe.com/create-the-first-controller-with-alxarafe-for-alixar-fork-of-dolibarr/
     *
     * @return bool
     */
    public function process(): bool
    {
        if (!filter_input(INPUT_GET, 'call')) {
            $controller = filter_input(INPUT_GET, 'controller') ?: 'home';
            $method = filter_input(INPUT_GET, 'method') ?: 'home';
            $this->path = "dolibarr/htdocs/$controller/$method.php";
            if (file_exists($this->path)) {
                return true;
            }
        }

        $this->path = null;

        if (parent::process()) {
            return true;
        }

        return false;
    }
}
