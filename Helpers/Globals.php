<?php
/* Copyright (C) 2018       Alxarafe            <info@alxarafe.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */
namespace Alixar\Helpers;

use Alixar\Base\Conf;
use Alixar\Base\HookManager;
use Alixar\Base\Langs;
use Alixar\Base\User;

/**
 * All variables and global functions are centralized through the static class Config.
 * The Config class can be instantiated and passed to the class that needs it,
 * sharing the data and methods that are in them.
 *
 * @package Alxarafe\Helpers
 */
class Globals
{

    static public $conf;
    static public $hookManager;
    static public $langs;
    static public $user;

    /*
      static private $errors;
      static $dbEngine;
      static $sqlHelper;
      static $bbddStructure;
      static $user;
      static $username;
      static $configFilename;
     */

    static public function initGlobals()
    {
        if (!defined('DOL_APPLICATION_TITLE')) {
            define('DOL_APPLICATION_TITLE', 'Dolibarr');
        }

        if (!defined('DOL_VERSION')) {
            define('DOL_VERSION', '10.0.0-alpha');  // a.b.c-alpha, a.b.c-beta, a.b.c-rcX or a.b.c
        }

        if (!defined('EURO')) {
            define('EURO', chr(128));
        }

        self::initConf();
        self::initHookManager();
        self::initLangs();
        self::initUser();
    }

    static public function initConf()
    {
        self::$conf = new Conf();

        include DOL_BASE_PATH . '/conf/conf.php';

        // Set properties specific to database
        Globals::$conf->db->host = $dolibarr_main_db_host;
        Globals::$conf->db->port = $dolibarr_main_db_port;
        Globals::$conf->db->name = $dolibarr_main_db_name;
        Globals::$conf->db->user = $dolibarr_main_db_user;
        Globals::$conf->db->pass = $dolibarr_main_db_pass;
        Globals::$conf->db->type = $dolibarr_main_db_type;
        Globals::$conf->db->prefix = $dolibarr_main_db_prefix;
        Globals::$conf->db->character_set = $dolibarr_main_db_character_set;
        Globals::$conf->db->dolibarr_main_db_collation = $dolibarr_main_db_collation;
        Globals::$conf->db->dolibarr_main_db_encryption = $dolibarr_main_db_encryption ?? 0;
        Globals::$conf->db->dolibarr_main_db_cryptkey = $dolibarr_main_db_cryptkey ?? '';
        if (defined('TEST_DB_FORCE_TYPE')) {
            Globals::$conf->db->type = constant('TEST_DB_FORCE_TYPE'); // Force db type (for test purpose, by PHP unit for example)
        }
// Define prefix
        if (!isset($dolibarr_main_db_prefix) || !$dolibarr_main_db_prefix) {
            $dolibarr_main_db_prefix = 'llx_';
        }
        define('MAIN_DB_PREFIX', (isset($dolibarr_main_db_prefix) ? $dolibarr_main_db_prefix : ''));

        // Set properties specific to conf file
        Globals::$conf->file->main_limit_users = $dolibarr_main_limit_users ?? 0;
        Globals::$conf->file->mailing_limit_sendbyweb = $dolibarr_mailing_limit_sendbyweb ?? 0;
        Globals::$conf->file->mailing_limit_sendbycli = $dolibarr_mailing_limit_sendbycli ?? 0;
        Globals::$conf->file->main_authentication = empty($dolibarr_main_authentication) ? '' : $dolibarr_main_authentication; // Identification mode
        Globals::$conf->file->main_force_https = empty($dolibarr_main_force_https) ? '' : $dolibarr_main_force_https;   // Force https
        Globals::$conf->file->strict_mode = empty($dolibarr_strict_mode) ? '' : $dolibarr_strict_mode;     // Force php strict mode (for debug)
        Globals::$conf->file->cookie_cryptkey = empty($dolibarr_main_cookie_cryptkey) ? '' : $dolibarr_main_cookie_cryptkey; // Cookie cryptkey
        Globals::$conf->file->dol_document_root = array('main' => (string) DOL_DOCUMENT_ROOT);        // Define array of document root directories ('/home/htdocs')
        Globals::$conf->file->dol_url_root = array('main' => (string) DOL_BASE_URI);         // Define array of url root path ('' or '/dolibarr')
        if (!empty($dolibarr_main_document_root_alt)) {
            // dolibarr_main_document_root_alt can contains several directories
            $values = preg_split('/[;,]/', $dolibarr_main_document_root_alt);
            $i = 0;
            foreach ($values as $value) {
                Globals::$conf->file->dol_document_root['alt' . ($i++)] = (string) $value;
            }
            $values = preg_split('/[;,]/', $dolibarr_main_url_root_alt);
            $i = 0;
            foreach ($values as $value) {
                if (preg_match('/^http(s)?:/', $value)) {
                    // Show error message
                    $correct_value = str_replace($dolibarr_main_url_root, '', $value);
                    print '<b>Error:</b><br>' . "\n";
                    print 'Wrong <b>$dolibarr_main_url_root_alt</b> value in <b>conf.php</b> file.<br>' . "\n";
                    print 'We now use a relative path to $dolibarr_main_url_root to build alternate URLs.<br>' . "\n";
                    print 'Value found: ' . $value . '<br>' . "\n";
                    print 'Should be replaced by: ' . $correct_value . '<br>' . "\n";
                    print "Or something like following examples:<br>\n";
                    print "\"/extensions\"<br>\n";
                    print "\"/extensions1,/extensions2,...\"<br>\n";
                    print "\"/../extensions\"<br>\n";
                    print "\"/custom\"<br>\n";
                    exit;
                }
                Globals::$conf->file->dol_url_root['alt' . ($i++)] = (string) $value;
            }
        }
    }

    static public function initHookManager()
    {
        // Create the global $hookmanager object
//include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
        Globals::$hookManager = new HookManager();
    }

    static public function initLangs()
    {
        /*
         * Creation objet $langs (must be before all other code)
         */
        if (!defined('NOREQUIRETRAN')) {
            Globals::$langs = new Langs();
        }
    }

    static public function initUser()
    {
        if (!defined('NOREQUIREUSER')) {
            Globals::$user = new User();
        }
    }

    public static function getConfigFileName()// : ?string - NetBeans only supports up to php7.0, for this you need php7.1
    {
        if (isset(self::$configFilename)) {
            return self::$configFilename;
        }
        $filename = CONFIGURATION_PATH . '/config.yaml';
        if (file_exists($filename) || is_dir(CONFIGURATION_PATH) || mkdir(CONFIGURATION_PATH, 0777, true)) {
            self::$configFilename = $filename;
        }
        return self::$configFilename;
    }

    /**
     * Return true y the config file exists
     *
     * @return bool
     */
    public static function configFileExists(): bool
    {
        return (file_exists(self::getConfigFileName()));
    }

    /**
     * Returns an array with the configuration defined in the configuration file.
     * If the configuration file does not exist, take us to the application
     * configuration form to create it
     *
     * @return array
     */
    public static function loadConfigurationFile(): array
    {
        $filename = self::getConfigFileName();
        if (isset($filename)) {
            /*
              // TODO: Duplicate? It is done in Dispatcher->getConfiguration()
              if (!self::configFileExists()) {
              (new EditConfig())->run();
              }
             */
            $yaml = file_get_contents($filename);
            if ($yaml) {
                return YAML::parse($yaml);
            }
        }
        return null;
    }

    /**
     * Set the display settings.
     *
     * @return void
     */
    public static function loadViewsConfig()
    {
        Skin::setTemplatesEngine(self::getVar('templatesEngine') ?? 'twig');
        Skin::setSkin(self::getVar('skin') ?? 'default');
        Skin::setTemplate(self::getVar('template') ?? 'default');
        Skin::setCommonTemplatesFolder(self::getVar('commonTemplatesFolder') ?? Skin::COMMON_FOLDER);
    }

    /**
     * Initializes the global variable with the configuration, connects with
     * the database and authenticates the user.
     *
     * @return void
     */
    public static function loadConfig()
    {
        self::$global = self::loadConfigurationFile();
        if (isset(self::$global['skin'])) {
            $templatesFolder = BASE_PATH . Skin::SKINS_FOLDER;
            $skinFolder = $templatesFolder . '/' . self::$global['skin'];
            if (is_dir($templatesFolder) && !is_dir($skinFolder)) {
                Config::setError("Skin folder '$skinFolder' does not exists!");
                //(new EditConfig())->run();
                new EditConfig();
                return;
            }
            Skin::setSkin(self::$global['skin']);
        }
        if (!self::connectToDataBase()) {
            self::setError('Database Connection error...');
            //(new EditConfig())->run();
            new EditConfig();
            return;
        }
        if (self::$user === null) {
            self::$user = new Auth();
            self::$username = self::$user->getUser();
            if (self::$username == null) {
                self::$user->login();
            }
        }
    }

    /**
     * Stores all the variables in a permanent file so that they can be loaded
     * later with loadConfigFile()
     * Returns true if there is no error when saving the file.
     *
     * @return bool
     */
    public static function saveConfigFile(): bool
    {
        $configFile = self::getConfigurationFile();
        if (!isset($configFile)) {
            return false;
        }
        return file_put_contents($configFile, YAML::dump(self::$global)) !== FALSE;
    }

    /**
     * Register a new error message
     *
     * @param string $error
     */
    public static function setError(string $error)
    {
        self::$errors[] = $error;
    }

    /**
     * Returns an array with the pending error messages, and empties the list.
     *
     * @return array
     */
    public static function getErrors()
    {
        $errors = self::$errors;
        self::$errors = [];
        return $errors;
    }

    /**
     * Stores a variable.
     *
     * @param string $name
     * @param string $value
     */
    public static function setVar(string $name, string $value)
    {
        self::$global[$name] = $value;
    }

    /**
     * Gets the contents of a variable. If the variable does not exist, return null.
     *
     * @param string $name
     *
     * @return string|null
     */
    public static function getVar(string $name)// : ?string - NetBeans only supports up to php7.0, for this you need php7.1
    {
        return self::$global[$name] ?? null;
    }

    /**
     * If Config::$dbEngine contain null, create an Engine instance with the
     * database connection and assigns it to Config::$dbEngine.
     *
     * @return bool
     *
     * @throws \DebugBar\DebugBarException
     */
    public static function connectToDatabase(): bool
    {
        if (self::$dbEngine == null) {
            $dbEngineName = self::$global['dbEngineName'] ?? 'PdoMySql';
            $helperName = 'Sql' . substr($dbEngineName, 3);

            Debug::addMessage('SQL', "Using '$dbEngineName' engine.");
            Debug::addMessage('SQL', "Using '$helperName' SQL helper engine.");

            $sqlEngine = '\\Alxarafe\\Database\\SqlHelpers\\' . $helperName;
            $engine = '\\Alxarafe\\Database\\Engines\\' . $dbEngineName;
            try {
                Config::$sqlHelper = new $sqlEngine();
                Config::$dbEngine = new $engine([
                    'dbUser' => self::$global['dbUser'],
                    'dbPass' => self::$global['dbPass'],
                    'dbName' => self::$global['dbName'],
                    'dbHost' => self::$global['dbHost'],
                    'dbPort' => self::$global['dbPort'],
                ]);
                return isset(self::$dbEngine) && self::$dbEngine->connect() && Config::$dbEngine->checkConnection();
            } catch (Exception $e) {
                Debug::addException($e);
                return false;
            }
        }
    }
}
