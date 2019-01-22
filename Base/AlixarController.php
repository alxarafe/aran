<?php
/* Copyright (C) 2019       Alxarafe                    <info@alxarafe.com>
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
namespace Alixar\Base;

use Alxarafe\Helpers\Debug;
use Alixar\Helpers\Globals;
use Alixar\Helpers\DolUtils;
use Alixar\Helpers\Security;
use Alixar\Helpers\Security2;
use Alixar\Helpers\DateLib;
use Alixar\Base\Interfaces;
use Alixar\Base\MenuManager;

/**
 * This class contains the methods and attributes common to all Alixar controllers
 *
 * @author Alxarafe
 */
class AlixarController extends \Alxarafe\Base\Controller
{

    public $authmode;
    public $dol_authmode;

    function __construct()
    {
        parent::__construct();

        $this->checkRequires();

        // Include the conf.php and functions.lib.php
        // require_once DOL_BASE_PATH . '/filefunc.inc.php';
        Globals::initGlobals();

        // Init session. Name of session is specific to Dolibarr instance.
        // Note: the function dol_getprefix may have been redefined to return a different key to manage another area to protect.
        $prefix = DolUtils::dol_getprefix('');

        $sessionname = 'DOLSESSID_' . $prefix;
        $sessiontimeout = 'DOLSESSTIMEOUT_' . $prefix;
        if (!empty($_COOKIE[$sessiontimeout])) {
            ini_set('session.gc_maxlifetime', $_COOKIE[$sessiontimeout]);
        }
        session_name($sessionname);
        session_set_cookie_params(0, '/', null, false, true);   // Add tag httponly on session cookie (same as setting session.cookie_httponly into php.ini). Must be called before the session_start.
        // This create lock, released when session_write_close() or end of page.
        // We need this lock as long as we read/write $_SESSION ['vars']. We can remove lock when finished.
        if (!defined('NOSESSION')) {
            session_start();
            /* if (ini_get('register_globals'))    // Deprecated in 5.3 and removed in 5.4. To solve bug in using $_SESSION
              {
              foreach ($_SESSION as $key=>$value)
              {
              if (isset($GLOBALS[$key])) unset($GLOBALS[$key]);
              }
              } */
        }

// Init the 5 global objects, this include will make the new and set properties for: Globals::$conf, $db, Globals::$langs, Globals::$user, $mysoc
        //require_once 'master.inc.php';
// Activate end of page function
        //register_shutdown_function('dol_shutdown');
// Detection browser
        if (isset($_SERVER["HTTP_USER_AGENT"])) {
            $tmp = DolUtils::getBrowserInfo($_SERVER["HTTP_USER_AGENT"]);
            Globals::$conf->browser->name = $tmp['browsername'];
            Globals::$conf->browser->os = $tmp['browseros'];
            Globals::$conf->browser->version = $tmp['browserversion'];
            Globals::$conf->browser->layout = $tmp['layout'];     // 'classic', 'phone', 'tablet'
//var_dump(Globals::$conf->browser);

            if (Globals::$conf->browser->layout == 'phone') {
                Globals::$conf->dol_no_mouse_hover = 1;
            }
            if (Globals::$conf->browser->layout == 'phone') {
                Globals::$conf->global->MAIN_TESTMENUHIDER = 1;
            }
        }

// Force HTTPS if required (Globals::$conf->file->main_force_https is 0/1 or https dolibarr root url)
// $_SERVER["HTTPS"] is 'on' when link is https, otherwise $_SERVER["HTTPS"] is empty or 'off'
        if (!empty(Globals::$conf->file->main_force_https) && (empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != 'on')) {
            $newurl = '';
            if (is_numeric(Globals::$conf->file->main_force_https)) {
                if (Globals::$conf->file->main_force_https == '1' && !empty($_SERVER["SCRIPT_URI"])) { // If SCRIPT_URI supported by server
                    if (preg_match('/^http:/i', $_SERVER["SCRIPT_URI"]) && !preg_match('/^https:/i', $_SERVER["SCRIPT_URI"])) { // If link is http
                        $newurl = preg_replace('/^http:/i', 'https:', $_SERVER["SCRIPT_URI"]);
                    }
                } else { // Check HTTPS environment variable (Apache/mod_ssl only)
                    $newurl = preg_replace('/^http:/i', 'https:', DOL_MAIN_URL_ROOT) . $_SERVER["REQUEST_URI"];
                }
            } else {
// Check HTTPS environment variable (Apache/mod_ssl only)
                $newurl = Globals::$conf->file->main_force_https . $_SERVER["REQUEST_URI"];
            }
// Start redirect
            if ($newurl) {
                DolUtils::dol_syslog("main.inc: dolibarr_main_force_https is on, we make a redirect to " . $newurl);
                echo $newurl;
                throw Exception('x');
                header("Location: " . $newurl);
                exit;
            } else {
                DolUtils::dol_syslog("main.inc: dolibarr_main_force_https is on but we failed to forge new https url so no redirect is done", LOG_WARNING);
            }
        }

        if (!defined('NOLOGIN') && !defined('NOIPCHECK') && !empty($dolibarr_main_restrict_ip)) {
            $listofip = explode(',', $dolibarr_main_restrict_ip);
            $found = false;
            foreach ($listofip as $ip) {
                $ip = trim($ip);
                if ($ip == $_SERVER['REMOTE_ADDR']) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                print 'Access refused by IP protection';
                exit;
            }
        }

// Loading of additional presentation includes
        if (!defined('NOREQUIREHTML')) {
            require_once DOL_BASE_PATH . '/core/class/html.form.class.php';     // Need 660ko memory (800ko in 2.2)
        }
        if (!defined('NOREQUIREAJAX') && Globals::$conf->use_javascript_ajax) {
            require_once DOL_BASE_PATH . '/core/lib/ajax.lib.php'; // Need 22ko memory
        }
// If install or upgrade process not done or not completely finished, we call the install page.
        if (!empty(Globals::$conf->global->MAIN_NOT_INSTALLED) || !empty(Globals::$conf->global->MAIN_NOT_UPGRADED)) {
            DolUtils::dol_syslog("main.inc: A previous install or upgrade was not complete. Redirect to install page.", LOG_WARNING);
            throw Exception('x');
            header("Location: " . DOL_BASE_URI . "/install/index.php");
            exit;
        }
// If an upgrade process is required, we call the install page.
        if ((!empty(Globals::$conf->global->MAIN_VERSION_LAST_UPGRADE) && (Globals::$conf->global->MAIN_VERSION_LAST_UPGRADE != DOL_VERSION)) || (empty(Globals::$conf->global->MAIN_VERSION_LAST_UPGRADE) && !empty(Globals::$conf->global->MAIN_VERSION_LAST_INSTALL) && (Globals::$conf->global->MAIN_VERSION_LAST_INSTALL != DOL_VERSION))) {
            $versiontocompare = empty(Globals::$conf->global->MAIN_VERSION_LAST_UPGRADE) ? Globals::$conf->global->MAIN_VERSION_LAST_INSTALL : Globals::$conf->global->MAIN_VERSION_LAST_UPGRADE;
            require_once DOL_BASE_PATH . '/core/lib/admin.lib.php';
            $dolibarrversionlastupgrade = preg_split('/[.-]/', $versiontocompare);
            $dolibarrversionprogram = preg_split('/[.-]/', DOL_VERSION);
            $rescomp = versioncompare($dolibarrversionprogram, $dolibarrversionlastupgrade);
            if ($rescomp > 0) {   // Programs have a version higher than database. We did not add "&& $rescomp < 3" because we want upgrade process for build upgrades
                DolUtils::dol_syslog("main.inc: database version " . $versiontocompare . " is lower than programs version " . DOL_VERSION . ". Redirect to install page.", LOG_WARNING);
                throw Exception('x');
                header("Location: " . DOL_BASE_URI . "/install/index.php");
                exit;
            }
        }

// Creation of a token against CSRF vulnerabilities
        if (!defined('NOTOKENRENEWAL')) {
// roulement des jetons car cree a chaque appel
            if (isset($_SESSION['newtoken'])) {
                $_SESSION['token'] = $_SESSION['newtoken'];
            }

// Save in $_SESSION['newtoken'] what will be next token. Into forms, we will add param token = $_SESSION['newtoken']
            $token = Security::dol_hash(uniqid(mt_rand(), true)); // Generates a hash of a random number
            $_SESSION['newtoken'] = $token;
        }
        if ((!defined('NOCSRFCHECK') && empty($dolibarr_nocsrfcheck) && !empty(Globals::$conf->global->MAIN_SECURITY_CSRF_WITH_TOKEN)) || defined('CSRFCHECK_WITH_TOKEN')) { // Check validity of token, only if option MAIN_SECURITY_CSRF_WITH_TOKEN enabled or if constant CSRFCHECK_WITH_TOKEN is set
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && !DolUtils::GETPOST('token', 'alpha')) { // Note, offender can still send request by GET
                print "Access refused by CSRF protection in main.inc.php. Token not provided.\n";
                print "If you access your server behind a proxy using url rewriting, you might check that all HTTP header is propagated (or add the line \$dolibarr_nocsrfcheck=1 into your conf.php file).\n";
                die;
            }
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {  // This test must be after loading $_SESSION['token'].
                if (DolUtils::GETPOST('token', 'alpha') != $_SESSION['token']) {
                    DolUtils::dol_syslog("Invalid token in " . $_SERVER['HTTP_REFERER'] . ", action=" . DolUtils::GETPOST('action', 'aZ09') . ", _POST['token']=" . DolUtils::GETPOST('token', 'alpha') . ", _SESSION['token']=" . $_SESSION['token'], LOG_WARNING);
//print 'Unset POST by CSRF protection in main.inc.php.';	// Do not output anything because this create problems when using the BACK button on browsers.
                    unset($_POST);
                }
            }
        }

// Disable modules (this must be after session_start and after conf has been loaded)
        if (DolUtils::GETPOST('disablemodules', 'alpha')) {
            $_SESSION["disablemodules"] = DolUtils::GETPOST('disablemodules', 'alpha');
        }
        if (!empty($_SESSION["disablemodules"])) {
            $disabled_modules = explode(',', $_SESSION["disablemodules"]);
            foreach ($disabled_modules as $module) {
                if ($module) {
                    if (empty(Globals::$conf->$module)) {
                        Globals::$conf->$module = new stdClass();
                    }
                    Globals::$conf->$module->enabled = false;
                    if ($module == 'fournisseur') {  // Special case
                        Globals::$conf->supplier_order->enabled = 0;
                        Globals::$conf->supplier_invoice->enabled = 0;
                    }
                }
            }
        }

        /*
         * Phase authentication / login
         */
        $login = '';
        if (!defined('NOLOGIN')) {
// $authmode lists the different means of identification to be tested in order of preference.
// Example: 'http', 'dolibarr', 'ldap', 'http,forceuser', '...'

            if (defined('MAIN_AUTHENTICATION_MODE')) {
                $dolibarr_main_authentication = constant('MAIN_AUTHENTICATION_MODE');
            } else {
// Authentication mode
                if (empty($dolibarr_main_authentication)) {
                    $dolibarr_main_authentication = 'http,dolibarr';
                }
// Authentication mode: forceuser
                if ($dolibarr_main_authentication == 'forceuser' && empty($dolibarr_auto_user)) {
                    $dolibarr_auto_user = 'auto';
                }
            }
// Set authmode
            $this->authmode = explode(',', $dolibarr_main_authentication);

// No authentication mode
            if (!count($this->authmode)) {
                Globals::$langs->load('main');
                dol_print_error('', Globals::$langs->trans("ErrorConfigParameterNotDefined", 'dolibarr_main_authentication'));
                exit;
            }

// If login request was already post, we retrieve login from the session
// Call module if not realized that his request.
// At the end of this phase, the variable $login is defined.
            $resultFetchUser = '';
            $test = true;
            if (!isset($_SESSION["dol_login"])) {
// It is not already authenticated and it requests the login / password
                //include_once DOL_BASE_PATH . '/core/lib/security2.lib.php';

                $dol_dst_observed = DolUtils::GETPOST("dst_observed", 'int', 3);
                $dol_dst_first = DolUtils::GETPOST("dst_first", 'int', 3);
                $dol_dst_second = DolUtils::GETPOST("dst_second", 'int', 3);
                $dol_screenwidth = DolUtils::GETPOST("screenwidth", 'int', 3);
                $dol_screenheight = DolUtils::GETPOST("screenheight", 'int', 3);
                $dol_hide_topmenu = DolUtils::GETPOST('dol_hide_topmenu', 'int', 3);
                $dol_hide_leftmenu = DolUtils::GETPOST('dol_hide_leftmenu', 'int', 3);
                $dol_optimize_smallscreen = DolUtils::GETPOST('dol_optimize_smallscreen', 'int', 3);
                $dol_no_mouse_hover = DolUtils::GETPOST('dol_no_mouse_hover', 'int', 3);
                $dol_use_jmobile = DolUtils::GETPOST('dol_use_jmobile', 'int', 3);
//dol_syslog("POST key=".join(array_keys($_POST),',').' value='.join($_POST,','));
// If in demo mode, we check we go to home page through the public/demo/index.php page
                if (!empty($dolibarr_main_demo) && $_SERVER['PHP_SELF'] == DOL_BASE_URI . '/index.php') {  // We ask index page
                    if (empty($_SERVER['HTTP_REFERER']) || !preg_match('/public/', $_SERVER['HTTP_REFERER'])) {
                        DolUtils::dol_syslog("Call index page from another url than demo page (call is done from page " . $_SERVER['HTTP_REFERER'] . ")");
                        $url = '';
                        $url .= ($url ? '&' : '') . ($dol_hide_topmenu ? 'dol_hide_topmenu=' . $dol_hide_topmenu : '');
                        $url .= ($url ? '&' : '') . ($dol_hide_leftmenu ? 'dol_hide_leftmenu=' . $dol_hide_leftmenu : '');
                        $url .= ($url ? '&' : '') . ($dol_optimize_smallscreen ? 'dol_optimize_smallscreen=' . $dol_optimize_smallscreen : '');
                        $url .= ($url ? '&' : '') . ($dol_no_mouse_hover ? 'dol_no_mouse_hover=' . $dol_no_mouse_hover : '');
                        $url .= ($url ? '&' : '') . ($dol_use_jmobile ? 'dol_use_jmobile=' . $dol_use_jmobile : '');
                        $url = DOL_BASE_URI . '/public/demo/index.php' . ($url ? '?' . $url : '');
                        echo $url;
                        throw Exception('x');
                        header("Location: " . $url);
                        exit;
                    }
                }

// Verification security graphic code
                if (DolUtils::GETPOST("username", "alpha", 2) && !empty(Globals::$conf->global->MAIN_SECURITY_ENABLECAPTCHA)) {
                    $sessionkey = 'dol_antispam_value';
                    $ok = (array_key_exists($sessionkey, $_SESSION) === true && (strtolower($_SESSION[$sessionkey]) == strtolower($_POST['code'])));

// Check code
                    if (!$ok) {
                        DolUtils::dol_syslog('Bad value for code, connexion refused');
// Load translation files required by page
                        Globals::$langs->loadLangs(array('main', 'errors'));

                        $_SESSION["dol_loginmesg"] = Globals::$langs->trans("ErrorBadValueForCode");
                        $test = false;

// Call trigger for the "security events" log
                        Globals::$user->trigger_mesg = 'ErrorBadValueForCode - login=' . DolUtils::GETPOST("username", "alpha", 2);
// Call of triggers
                        //include_once DOL_BASE_PATH . '/core/class/interfaces.class.php';
                        $interface = new Interfaces($db);
                        $result = $interface->run_triggers('USER_LOGIN_FAILED', Globals::$user, Globals::$user, Globals::$langs, Globals::$conf);
                        if ($result < 0) {
                            $error++;
                        }
// End Call of triggers
// Hooks on failed login
                        $action = '';
                        Globals::$hookManager->initHooks(array('login'));
                        $parameters = array('dol_authmode' => $this->dol_authmode, 'dol_loginmesg' => $_SESSION["dol_loginmesg"]);
                        $reshook = Globals::$hookManager->executeHooks('afterLoginFailed', $parameters, Globals::$user, $action);    // Note that $action and $object may have been modified by some hooks
                        if ($reshook < 0)
                            $error++;

// Note: exit is done later
                    }
                }

                $allowedmethodtopostusername = 2;
                if (defined('MAIN_AUTHENTICATION_POST_METHOD')) {
                    $allowedmethodtopostusername = constant('MAIN_AUTHENTICATION_POST_METHOD');
                }
                $usertotest = (!empty($_COOKIE['login_dolibarr']) ? $_COOKIE['login_dolibarr'] : DolUtils::GETPOST("username", "alpha", $allowedmethodtopostusername));
                $passwordtotest = DolUtils::GETPOST('password', 'none', $allowedmethodtopostusername);
                $entitytotest = (DolUtils::GETPOST('entity', 'int') ? DolUtils::GETPOST('entity', 'int') : (!empty(Globals::$conf->entity) ? Globals::$conf->entity : 1));

// Define if we received data to test the login.
                $goontestloop = false;
                if (isset($_SERVER["REMOTE_USER"]) && in_array('http', $this->authmode)) {
                    $goontestloop = true;
                }
                if ($dolibarr_main_authentication == 'forceuser' && !empty($dolibarr_auto_user)) {
                    $goontestloop = true;
                }
                if (DolUtils::GETPOST("username", "alpha", $allowedmethodtopostusername) || !empty($_COOKIE['login_dolibarr']) || DolUtils::GETPOST('openid_mode', 'alpha', 1)) {
                    $goontestloop = true;
                }

                if (!is_object(Globals::$langs)) { // This can occurs when calling page with NOREQUIRETRAN defined, however we need langs for error messages.
                    // include_once DOL_BASE_PATH . '/core/class/translate.class.php';
                    Globals::$langs = new Translate("", Globals::$conf);
                    $langcode = (DolUtils::GETPOST('lang', 'aZ09', 1) ? DolUtils::GETPOST('lang', 'aZ09', 1) : (empty(Globals::$conf->global->MAIN_LANG_DEFAULT) ? 'auto' : Globals::$conf->global->MAIN_LANG_DEFAULT));
                    if (defined('MAIN_LANG_DEFAULT')) {
                        $langcode = constant('MAIN_LANG_DEFAULT');
                    }
                    Globals::$langs->setDefaultLang($langcode);
                }

// Validation of login/pass/entity
// If ok, the variable login will be returned
// If error, we will put error message in session under the name dol_loginmesg
                if ($test && $goontestloop) {
                    $login = Security2::checkLoginPassEntity($usertotest, $passwordtotest, $entitytotest, $this->authmode);
                    if ($login) {
                        $this->dol_authmode = Globals::$conf->authmode; // This properties is defined only when logged, to say what mode was successfully used
                        $dol_tz = $_POST["tz"];
                        $dol_tz_string = $_POST["tz_string"];
                        $dol_tz_string = preg_replace('/\s*\(.+\)$/', '', $dol_tz_string);
                        $dol_tz_string = preg_replace('/,/', '/', $dol_tz_string);
                        $dol_tz_string = preg_replace('/\s/', '_', $dol_tz_string);
                        $dol_dst = 0;
                        if (isset($_POST["dst_first"]) && isset($_POST["dst_second"])) {
                            // include_once DOL_BASE_PATH . '/core/lib/date.lib.php';
                            $datenow = DolUtils::dol_now();
                            $datefirst = DateLib::dol_stringtotime($_POST["dst_first"]);
                            $datesecond = DateLib::dol_stringtotime($_POST["dst_second"]);
                            if ($datenow >= $datefirst && $datenow < $datesecond) {
                                $dol_dst = 1;
                            }
                        }
//print $datefirst.'-'.$datesecond.'-'.$datenow.'-'.$dol_tz.'-'.$dol_tzstring.'-'.$dol_dst; exit;
                    }

                    if (!$login) {
                        DolUtils::dol_syslog('Bad password, connexion refused', LOG_DEBUG);
// Load translation files required by page
                        Globals::$langs->loadLangs(array('main', 'errors'));

// Bad password. No authmode has found a good password.
// We set a generic message if not defined inside function checkLoginPassEntity or subfunctions
                        if (empty($_SESSION["dol_loginmesg"])) {
                            $_SESSION["dol_loginmesg"] = Globals::$langs->trans("ErrorBadLoginPassword");
                        }

                        // Call trigger for the "security events" log
                        Globals::$user->trigger_mesg = Globals::$langs->trans("ErrorBadLoginPassword") . ' - login=' . DolUtils::GETPOST("username", "alpha", 2);

                        // Call of triggers
                        //include_once DOL_BASE_PATH . '/core/class/interfaces.class.php';
                        $interface = new Interfaces();
                        $result = $interface->run_triggers('USER_LOGIN_FAILED', Globals::$user, Globals::$user, Globals::$langs, Globals::$conf, DolUtils::GETPOST("username", "alpha", 2));
                        if ($result < 0) {
                            $error++;
                        }
// End Call of triggers
// Hooks on failed login
                        $action = '';
                        Globals::$hookManager->initHooks(array('login'));
                        $parameters = array('dol_authmode' => $this->dol_authmode, 'dol_loginmesg' => $_SESSION["dol_loginmesg"]);
                        $reshook = Globals::$hookManager->executeHooks('afterLoginFailed', $parameters, Globals::$user, $action);    // Note that $action and $object may have been modified by some hooks
                        if ($reshook < 0) {
                            $error++;
                        }

                    // Note: exit is done in next chapter
                    }
                }

                // End test login / passwords
                if (!$login || (in_array('ldap', $this->authmode) && empty($passwordtotest))) { // With LDAP we refused empty password because some LDAP are "opened" for anonymous access so connexion is a success.
                // No data to test login, so we show the login page
                    DolUtils::dol_syslog("--- Access to " . $_SERVER["PHP_SELF"] . " showing the login form and exit");
                    if (defined('NOREDIRECTBYMAINTOLOGIN')) {
                        return 'ERROR_NOT_LOGGED';
                    } else {
                        Security2::dol_loginfunction($this);
                    }
                    exit;
                }

                $resultFetchUser = Globals::$user->fetch('', $login, '', 1, ($entitytotest > 0 ? $entitytotest : -1));
                var_dump($resultFetchUser);
                if ($resultFetchUser <= 0) {
                    DolUtils::dol_syslog('User not found, connexion refused');
                    session_destroy();
                    session_name($sessionname);
                    session_set_cookie_params(0, '/', null, false, true);   // Add tag httponly on session cookie
                    session_start();    // Fixing the bug of register_globals here is useless since session is empty

                    if ($resultFetchUser == 0) {
// Load translation files required by page
                        Globals::$langs->loadLangs(array('main', 'errors'));

                        $_SESSION["dol_loginmesg"] = Globals::$langs->trans("ErrorCantLoadUserFromDolibarrDatabase", $login);

                        Globals::$user->trigger_mesg = 'ErrorCantLoadUserFromDolibarrDatabase - login=' . $login;
                    }
                    if ($resultFetchUser < 0) {
                        $_SESSION["dol_loginmesg"] = Globals::$user->error;

                        Globals::$user->trigger_mesg = Globals::$user->error;
                    }

// Call triggers for the "security events" log
                    //include_once DOL_BASE_PATH . '/core/class/interfaces.class.php';
                    $interface = new Interfaces();
                    $result = $interface->run_triggers('USER_LOGIN_FAILED', Globals::$user, Globals::$user, Globals::$langs, Globals::$conf);
                    if ($result < 0) {
                        $error++;
                    }
// End call triggers
// Hooks on failed login
                    $action = '';
                    Globals::$hookManager->initHooks(array('login'));
                    $parameters = array('dol_authmode' => $this->dol_authmode, 'dol_loginmesg' => $_SESSION["dol_loginmesg"]);
                    $reshook = Globals::$hookManager->executeHooks('afterLoginFailed', $parameters, Globals::$user, $action);    // Note that $action and $object may have been modified by some hooks
                    if ($reshook < 0) {
                        $error++;
                    }

                    $paramsurl = array();
                    if (DolUtils::GETPOST('textbrowser', 'int')) {
                        $paramsurl[] = 'textbrowser=' . DolUtils::GETPOST('textbrowser', 'int');
                    }
                    if (DolUtils::GETPOST('nojs', 'int')) {
                        $paramsurl[] = 'nojs=' . DolUtils::GETPOST('nojs', 'int');
                    }
                    if (DolUtils::GETPOST('lang', 'aZ09')) {
                        $paramsurl[] = 'lang=' . DolUtils::GETPOST('lang', 'aZ09');
                    }
                    echo 'Location: ' . DOL_BASE_URI . '/index.php' . (count($paramsurl) ? '?' . implode('&', $paramsurl) : '');
                    throw Exception('x');
                    header('Location: ' . DOL_BASE_URI . '/index.php' . (count($paramsurl) ? '?' . implode('&', $paramsurl) : ''));
                    exit;
                }
            } else {
// We are already into an authenticated session
                $login = $_SESSION["dol_login"];
                $entity = $_SESSION["dol_entity"];
                DolUtils::dol_syslog("- This is an already logged session. _SESSION['dol_login']=" . $login . " _SESSION['dol_entity']=" . $entity, LOG_DEBUG);

                $resultFetchUser = Globals::$user->fetch('', $login, '', 1, ($entity > 0 ? $entity : -1));
                if ($resultFetchUser <= 0) {
// Account has been removed after login
                    DolUtils::dol_syslog("Can't load user even if session logged. _SESSION['dol_login']=" . $login, LOG_WARNING);
                    session_destroy();
                    session_name($sessionname);
                    session_set_cookie_params(0, '/', null, false, true);   // Add tag httponly on session cookie
                    session_start();    // Fixing the bug of register_globals here is useless since session is empty

                    if ($resultFetchUser == 0) {
// Load translation files required by page
                        Globals::$langs->loadLangs(array('main', 'errors'));

                        $_SESSION["dol_loginmesg"] = Globals::$langs->trans("ErrorCantLoadUserFromDolibarrDatabase", $login);

                        Globals::$user->trigger_mesg = 'ErrorCantLoadUserFromDolibarrDatabase - login=' . $login;
                    }
                    if ($resultFetchUser < 0) {
                        $_SESSION["dol_loginmesg"] = Globals::$user->error;

                        Globals::$user->trigger_mesg = Globals::$user->error;
                    }

// Call triggers for the "security events" log
                    //include_once DOL_BASE_PATH . '/core/class/interfaces.class.php';
                    $interface = new Interfaces($db);
                    $result = $interface->run_triggers('USER_LOGIN_FAILED', Globals::$user, Globals::$user, Globals::$langs, Globals::$conf);
                    if ($result < 0) {
                        $error++;
                    }
// End call triggers
// Hooks on failed login
                    $action = '';
                    Globals::$hookManager->initHooks(array('login'));
                    $parameters = array('dol_authmode' => $this->dol_authmode, 'dol_loginmesg' => $_SESSION["dol_loginmesg"]);
                    $reshook = Globals::$hookManager->executeHooks('afterLoginFailed', $parameters, Globals::$user, $action);    // Note that $action and $object may have been modified by some hooks
                    if ($reshook < 0) {
                        $error++;
                    }

                    $paramsurl = array();
                    if (DolUtils::GETPOST('textbrowser', 'int')) {
                        $paramsurl[] = 'textbrowser=' . DolUtils::GETPOST('textbrowser', 'int');
                    }
                    if (DolUtils::GETPOST('nojs', 'int')) {
                        $paramsurl[] = 'nojs=' . DolUtils::GETPOST('nojs', 'int');
                    }
                    if (DolUtils::GETPOST('lang', 'aZ09')) {
                        $paramsurl[] = 'lang=' . DolUtils::GETPOST('lang', 'aZ09');
                    }
                    echo 'Location: ' . DOL_BASE_URI . '/index.php' . (count($paramsurl) ? '?' . implode('&', $paramsurl) : '');
                    throw Exception('x');
                    header('Location: ' . DOL_BASE_URI . '/index.php' . (count($paramsurl) ? '?' . implode('&', $paramsurl) : ''));
                    exit;
                } else {
// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
                    Globals::$hookManager->initHooks(array('main'));

// Code for search criteria persistence.
                    if (!empty($_GET['save_lastsearch_values'])) {    // We must use $_GET here
                        $relativepathstring = preg_replace('/\?.*$/', '', $_SERVER["HTTP_REFERER"]);
                        $relativepathstring = preg_replace('/^https?:\/\/[^\/]*/', '', $relativepathstring);     // Get full path except host server
// Clean $relativepathstring
                        if (constant('DOL_BASE_URI')) {
                            $relativepathstring = preg_replace('/^' . preg_quote(constant('DOL_BASE_URI'), '/') . '/', '', $relativepathstring);
                        }
                        $relativepathstring = preg_replace('/^\//', '', $relativepathstring);
                        $relativepathstring = preg_replace('/^custom\//', '', $relativepathstring);
//var_dump($relativepathstring);
// We click on a link that leave a page we have to save search criteria, contextpage, limit and page. We save them from tmp to no tmp
                        if (!empty($_SESSION['lastsearch_values_tmp_' . $relativepathstring])) {
                            $_SESSION['lastsearch_values_' . $relativepathstring] = $_SESSION['lastsearch_values_tmp_' . $relativepathstring];
                            unset($_SESSION['lastsearch_values_tmp_' . $relativepathstring]);
                        }
                        if (!empty($_SESSION['lastsearch_contextpage_tmp_' . $relativepathstring])) {
                            $_SESSION['lastsearch_contextpage_' . $relativepathstring] = $_SESSION['lastsearch_contextpage_tmp_' . $relativepathstring];
                            unset($_SESSION['lastsearch_contextpage_tmp_' . $relativepathstring]);
                        }
                        if (!empty($_SESSION['lastsearch_page_tmp_' . $relativepathstring]) && $_SESSION['lastsearch_page_tmp_' . $relativepathstring] > 1) {
                            $_SESSION['lastsearch_page_' . $relativepathstring] = $_SESSION['lastsearch_page_tmp_' . $relativepathstring];
                            unset($_SESSION['lastsearch_page_tmp_' . $relativepathstring]);
                        }
                        if (!empty($_SESSION['lastsearch_limit_tmp_' . $relativepathstring]) && $_SESSION['lastsearch_limit_tmp_' . $relativepathstring] != Globals::$conf->liste_limit) {
                            $_SESSION['lastsearch_limit_' . $relativepathstring] = $_SESSION['lastsearch_limit_tmp_' . $relativepathstring];
                            unset($_SESSION['lastsearch_limit_tmp_' . $relativepathstring]);
                        }
                    }

                    $action = '';
                    $reshook = Globals::$hookManager->executeHooks('updateSession', array(), Globals::$user, $action);
                    if ($reshook < 0) {
                        setEventMessages(Globals::$hookManager->error, Globals::$hookManager->errors, 'errors');
                    }
                }
            }

// Is it a new session that has started ?
// If we are here, this means authentication was successfull.
            if (!isset($_SESSION["dol_login"])) {
// New session for this login has started.
                $error = 0;

// Store value into session (values always stored)
                $_SESSION["dol_login"] = Globals::$user->login;
                $_SESSION["dol_authmode"] = isset($this->dol_authmode) ? $this->dol_authmode : '';
                $_SESSION["dol_tz"] = isset($dol_tz) ? $dol_tz : '';
                $_SESSION["dol_tz_string"] = isset($dol_tz_string) ? $dol_tz_string : '';
                $_SESSION["dol_dst"] = isset($dol_dst) ? $dol_dst : '';
                $_SESSION["dol_dst_observed"] = isset($dol_dst_observed) ? $dol_dst_observed : '';
                $_SESSION["dol_dst_first"] = isset($dol_dst_first) ? $dol_dst_first : '';
                $_SESSION["dol_dst_second"] = isset($dol_dst_second) ? $dol_dst_second : '';
                $_SESSION["dol_screenwidth"] = isset($dol_screenwidth) ? $dol_screenwidth : '';
                $_SESSION["dol_screenheight"] = isset($dol_screenheight) ? $dol_screenheight : '';
                $_SESSION["dol_company"] = Globals::$conf->global->MAIN_INFO_SOCIETE_NOM ?? '';
                $_SESSION["dol_entity"] = Globals::$conf->entity;
// Store value into session (values stored only if defined)
                if (!empty($dol_hide_topmenu)) {
                    $_SESSION['dol_hide_topmenu'] = $dol_hide_topmenu;
                }
                if (!empty($dol_hide_leftmenu)) {
                    $_SESSION['dol_hide_leftmenu'] = $dol_hide_leftmenu;
                }
                if (!empty($dol_optimize_smallscreen)) {
                    $_SESSION['dol_optimize_smallscreen'] = $dol_optimize_smallscreen;
                }
                if (!empty($dol_no_mouse_hover)) {
                    $_SESSION['dol_no_mouse_hover'] = $dol_no_mouse_hover;
                }
                if (!empty($dol_use_jmobile)) {
                    $_SESSION['dol_use_jmobile'] = $dol_use_jmobile;
                }

                DolUtils::dol_syslog("This is a new started user session. _SESSION['dol_login']=" . $_SESSION["dol_login"] . " Session id=" . session_id());

                Config::$dbEngine->begin();

                Globals::$user->update_last_login_date();

                $loginfo = 'TZ=' . $_SESSION["dol_tz"] . ';TZString=' . $_SESSION["dol_tz_string"] . ';Screen=' . $_SESSION["dol_screenwidth"] . 'x' . $_SESSION["dol_screenheight"];

// Call triggers for the "security events" log
                Globals::$user->trigger_mesg = $loginfo;
// Call triggers
                //include_once DOL_BASE_PATH . '/core/class/interfaces.class.php';
                $interface = new Interfaces($db);
                $result = $interface->run_triggers('USER_LOGIN', Globals::$user, Globals::$user, Globals::$langs, Globals::$conf);
                if ($result < 0) {
                    $error++;
                }
// End call triggers
// Hooks on successfull login
                $action = '';
                Globals::$hookManager->initHooks(array('login'));
                $parameters = array('dol_authmode' => $this->dol_authmode, 'dol_loginfo' => $loginfo);
                $reshook = Globals::$hookManager->executeHooks('afterLogin', $parameters, Globals::$user, $action);    // Note that $action and $object may have been modified by some hooks
                if ($reshook < 0) {
                    $error++;
                }

                if ($error) {
                    Config::$dbEngine->rollback();
                    session_destroy();
                    dol_print_error($db, 'Error in some triggers USER_LOGIN or in some hooks afterLogin');
                    exit;
                } else {
                    Config::$dbEngine->commit();
                }

// Change landing page if defined.
                $landingpage = (empty(Globals::$user->conf->MAIN_LANDING_PAGE) ? (empty(Globals::$conf->global->MAIN_LANDING_PAGE) ? '' : Globals::$conf->global->MAIN_LANDING_PAGE) : Globals::$user->conf->MAIN_LANDING_PAGE);
                if (!empty($landingpage)) {    // Example: /index.php
                    $newpath = dol_buildpath($landingpage, 1);
                    if ($_SERVER["PHP_SELF"] != $newpath) {   // not already on landing page (avoid infinite loop)
                        echo $newpath;
                        throw Exception('x');
                        header('Location: ' . $newpath);
                        exit;
                    }
                }
            }


// If user admin, we force the rights-based modules
            if (Globals::$user->admin) {
                Globals::$user->rights->user->user->lire = 1;
                Globals::$user->rights->user->user->creer = 1;
                Globals::$user->rights->user->user->password = 1;
                Globals::$user->rights->user->user->supprimer = 1;
                Globals::$user->rights->user->self->creer = 1;
                Globals::$user->rights->user->self->password = 1;
            }

            /*
             * Overwrite some configs globals (try to avoid this and have code to use instead Globals::$user->conf->xxx)
             */

// Set liste_limit
            if (isset(Globals::$user->conf->MAIN_SIZE_LISTE_LIMIT)) {
                Globals::$conf->liste_limit = Globals::$user->conf->MAIN_SIZE_LISTE_LIMIT; // Can be 0
            }
            if (isset(Globals::$user->conf->PRODUIT_LIMIT_SIZE)) {
                Globals::$conf->product->limit_size = Globals::$user->conf->PRODUIT_LIMIT_SIZE; // Can be 0
// Replace conf->css by personalized value if theme not forced
            }
            if (empty(Globals::$conf->global->MAIN_FORCETHEME) && !empty(Globals::$user->conf->MAIN_THEME)) {
                Globals::$conf->theme = Globals::$user->conf->MAIN_THEME;
// Globals::$conf->css = "/theme/" . Globals::$conf->theme . "/style.css.php";
                Globals::$conf->css = '?controller=theme/' . Globals::$conf->theme . '&method=style.css';
            }
        }

// Case forcing style from url
        if (DolUtils::GETPOST('theme', 'alpha')) {
            Globals::$conf->theme = DolUtils::GETPOST('theme', 'alpha', 1);
// Globals::$conf->css = "/theme/" . Globals::$conf->theme . "/style.css.php";
            Globals::$conf->css = '?controller=theme/' . Globals::$conf->theme . '&method=style.css';
        }


// Set javascript option
        if (!DolUtils::GETPOST('nojs', 'int')) {   // If javascript was not disabled on URL
            if (!empty(Globals::$user->conf->MAIN_DISABLE_JAVASCRIPT)) {
                Globals::$conf->use_javascript_ajax = !$user->conf->MAIN_DISABLE_JAVASCRIPT;
            }
        } else {
            Globals::$conf->use_javascript_ajax = 0;
        }
// Set MAIN_OPTIMIZEFORTEXTBROWSER
        if (DolUtils::GETPOST('textbrowser', 'int') || (!empty(Globals::$conf->browser->name) && Globals::$conf->browser->name == 'lynxlinks') || !empty(Globals::$user->conf->MAIN_OPTIMIZEFORTEXTBROWSER)) {   // If we must enable text browser
            Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER = 1;
        } elseif (!empty(Globals::$user->conf->MAIN_OPTIMIZEFORTEXTBROWSER)) {
            Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER = Globals::$user->conf->MAIN_OPTIMIZEFORTEXTBROWSER;
        }

// Set terminal output option according to conf->browser.
        if (DolUtils::GETPOST('dol_hide_leftmenu', 'int') || !empty($_SESSION['dol_hide_leftmenu'])) {
            Globals::$conf->dol_hide_leftmenu = 1;
        }
        if (DolUtils::GETPOST('dol_hide_topmenu', 'int') || !empty($_SESSION['dol_hide_topmenu'])) {
            Globals::$conf->dol_hide_topmenu = 1;
        }
        if (DolUtils::GETPOST('dol_optimize_smallscreen', 'int') || !empty($_SESSION['dol_optimize_smallscreen'])) {
            Globals::$conf->dol_optimize_smallscreen = 1;
        }
        if (DolUtils::GETPOST('dol_no_mouse_hover', 'int') || !empty($_SESSION['dol_no_mouse_hover'])) {
            Globals::$conf->dol_no_mouse_hover = 1;
        }
        if (DolUtils::GETPOST('dol_use_jmobile', 'int') || !empty($_SESSION['dol_use_jmobile'])) {
            Globals::$conf->dol_use_jmobile = 1;
        }
        if (!empty(Globals::$conf->browser->layout) && Globals::$conf->browser->layout != 'classic') {
            Globals::$conf->dol_no_mouse_hover = 1;
        }
        if ((!empty(Globals::$conf->browser->layout) && Globals::$conf->browser->layout == 'phone') || (!empty($_SESSION['dol_screenwidth']) && $_SESSION['dol_screenwidth'] < 400) || (!empty($_SESSION['dol_screenheight']) && $_SESSION['dol_screenheight'] < 400)
        ) {
            Globals::$conf->dol_optimize_smallscreen = 1;
        }
// If we force to use jmobile, then we reenable javascript
        if (!empty(Globals::$conf->dol_use_jmobile)) {
            Globals::$conf->use_javascript_ajax = 1;
        }
// Replace themes bugged with jmobile with eldy
        if (!empty(Globals::$conf->dol_use_jmobile) && in_array(Globals::$conf->theme, array('bureau2crea', 'cameleo', 'amarok'))) {
            Globals::$conf->theme = 'eldy';
// Globals::$conf->css = "/theme/" . Globals::$conf->theme . "/style.css.php";
            Globals::$conf->css = '?controller=theme/' . Globals::$conf->theme . '&method=style.css';
        }

        if (!defined('NOREQUIRETRAN')) {
            if (!DolUtils::GETPOST('lang', 'aZ09')) { // If language was not forced on URL
// If user has chosen its own language
                if (!empty(Globals::$user->conf->MAIN_LANG_DEFAULT)) {
// If different than current language
//print ">>>".Globals::$langs->getDefaultLang()."-".$user->conf->MAIN_LANG_DEFAULT;
                    if (Globals::$langs->getDefaultLang() != Globals::$user->conf->MAIN_LANG_DEFAULT) {
                        Globals::$langs->setDefaultLang(Globals::$user->conf->MAIN_LANG_DEFAULT);
                    }
                }
            }
        }

        if (!defined('NOLOGIN')) {
// If the login is not recovered, it is identified with an account that does not exist.
// Hacking attempt?
            if (!Globals::$user->login) {
                accessforbidden();
            }

// Check if user is active
            if (Globals::$user->statut < 1) {
// If not active, we refuse the user
                Globals::$langs->load("other");
                DolUtils::dol_syslog("Authentification ko as login is disabled");
                accessforbidden(Globals::$langs->trans("ErrorLoginDisabled"));
                exit;
            }

// Load permissions
            Globals::$user->getrights();
        }


        DolUtils::dol_syslog("--- Access to " . $_SERVER["PHP_SELF"] . ' - action=' . DolUtils::GETPOST('action', 'az09') . ', massaction=' . DolUtils::GETPOST('massaction', 'az09'));
//Another call for easy debugg
//dol_syslog("Access to ".$_SERVER["PHP_SELF"].' GET='.join(',',array_keys($_GET)).'->'.join(',',$_GET).' POST:'.join(',',array_keys($_POST)).'->'.join(',',$_POST));
// Load main languages files
        if (!defined('NOREQUIRETRAN')) {
// Load translation files required by page
            Globals::$langs->loadLangs(array('main', 'dict'));
        }

// Define some constants used for style of arrays
        $bc = array(0 => 'class="impair"', 1 => 'class="pair"');
        $bcdd = array(0 => 'class="drag drop oddeven"', 1 => 'class="drag drop oddeven"');
        $bcnd = array(0 => 'class="nodrag nodrop nohover"', 1 => 'class="nodrag nodrop nohoverpair"');  // Used for tr to add new lines
        $bctag = array(0 => 'class="impair tagtr"', 1 => 'class="pair tagtr"');

// Define messages variables
        $mesg = '';
        $warning = '';
        $error = 0;
// deprecated, see setEventMessages() and dol_htmloutput_events()
        $mesgs = array();
        $warnings = array();
        $errors = array();

// Constants used to defined number of lines in textarea
        if (empty(Globals::$conf->browser->firefox)) {
            define('ROWS_1', 1);
            define('ROWS_2', 2);
            define('ROWS_3', 3);
            define('ROWS_4', 4);
            define('ROWS_5', 5);
            define('ROWS_6', 6);
            define('ROWS_7', 7);
            define('ROWS_8', 8);
            define('ROWS_9', 9);
        } else {
            define('ROWS_1', 0);
            define('ROWS_2', 1);
            define('ROWS_3', 2);
            define('ROWS_4', 3);
            define('ROWS_5', 4);
            define('ROWS_6', 5);
            define('ROWS_7', 6);
            define('ROWS_8', 7);
            define('ROWS_9', 8);
        }

        $heightforframes = 50;

// Init menu manager
        if (!defined('NOREQUIREMENU')) {
            if (empty(Globals::$user->societe_id)) {    // If internal user or not defined
                Globals::$conf->standard_menu = (empty(Globals::$conf->global->MAIN_MENU_STANDARD_FORCED) ? (empty(Globals::$conf->global->MAIN_MENU_STANDARD) ? 'eldy_menu.php' : Globals::$conf->global->MAIN_MENU_STANDARD) : Globals::$conf->global->MAIN_MENU_STANDARD_FORCED);
            } else {                        // If external user
                Globals::$conf->standard_menu = (empty(Globals::$conf->global->MAIN_MENUFRONT_STANDARD_FORCED) ? (empty(Globals::$conf->global->MAIN_MENUFRONT_STANDARD) ? 'eldy_menu.php' : Globals::$conf->global->MAIN_MENUFRONT_STANDARD) : Globals::$conf->global->MAIN_MENUFRONT_STANDARD_FORCED);
            }

// Load the menu manager (only if not already done)
            $file_menu = Globals::$conf->standard_menu;
            if (DolUtils::GETPOST('menu', 'alpha')) {
                $file_menu = DolUtils::GETPOST('menu', 'alpha');     // example: menu=eldy_menu.php
            }
            if (!class_exists('MenuManager')) {
                $menufound = 0;
                $dirmenus = array_merge(array("/core/menus/"), (array) Globals::$conf->modules_parts['menus']);
                foreach ($dirmenus as $dirmenu) {
                    // $menufound = dol_include_once($dirmenu . "standard/" . $file_menu);
                    if (class_exists('MenuManager')) {
                        break;
                    }
                }
                if (!class_exists('MenuManager')) { // If failed to include, we try with standard eldy_menu.php
                    DolUtils::dol_syslog("You define a menu manager '" . $file_menu . "' that can not be loaded.", LOG_WARNING);
                    $file_menu = 'eldy_menu.php';
                    // include_once DOL_DOCUMENT_ROOT . "/core/menus/standard/" . $file_menu;
                }
            }
            Globals::$menuManager = new MenuManager(empty(Globals::$user->societe_id) ? 0 : 1);
            Globals::$menuManager->loadMenu();
        }
    }

    function checkRequires()
    {
        /**
         * $_GET = array_map('stripslashes_deep', $_GET);
         * $_POST = array_map('stripslashes_deep', $_POST);
         * $_FILES = array_map('stripslashes_deep', $_FILES);
         * // $_COOKIE  = array_map('stripslashes_deep', $_COOKIE); // Useless because a cookie should never be outputed on screen nor used into sql
         * @set_magic_quotes_runtime(0);
         */
        // Check consistency of NOREQUIREXXX DEFINES
        if ((defined('NOREQUIREDB') || defined('NOREQUIRETRAN')) && !defined('NOREQUIREMENU')) {
            print 'If define NOREQUIREDB or NOREQUIRETRAN are set, you must also set NOREQUIREMENU or not set them';
            exit;
        }

        // Sanity check on URL
        if (!empty($_SERVER["PHP_SELF"])) {
            $morevaltochecklikepost = array($_SERVER["PHP_SELF"]);
            $this->analyseVarsForSqlAndScriptsInjection($morevaltochecklikepost, 2);
        }

        // Sanity check on GET parameters
        if (!defined('NOSCANGETFORINJECTION') && !empty($_SERVER["QUERY_STRING"])) {
            $morevaltochecklikeget = array($_SERVER["QUERY_STRING"]);
            $this->analyseVarsForSqlAndScriptsInjection($morevaltochecklikeget, 1);
        }

        // Sanity check on POST
        if (!defined('NOSCANPOSTFORINJECTION')) {
            $this->analyseVarsForSqlAndScriptsInjection($_POST, 0);
        }

        // This is to make Dolibarr working with Plesk
        if (!empty($_SERVER['DOCUMENT_ROOT']) && substr($_SERVER['DOCUMENT_ROOT'], -6) !== 'htdocs') {
            set_include_path($_SERVER['DOCUMENT_ROOT'] . '/htdocs');
        }

        // If there is a POST parameter to tell to save automatically some POST parameters into cookies, we do it.
        // This is used for example by form of boxes to save personalization of some options.
        // DOL_AUTOSET_COOKIE=cookiename:val1,val2 and  cookiename_val1=aaa cookiename_val2=bbb will set cookie_name with value json_encode(array('val1'=> , ))
        if (!empty($_POST["DOL_AUTOSET_COOKIE"])) {
            $tmpautoset = explode(':', $_POST["DOL_AUTOSET_COOKIE"], 2);
            $tmplist = explode(',', $tmpautoset[1]);
            $cookiearrayvalue = array();
            foreach ($tmplist as $tmpkey) {
                $postkey = $tmpautoset[0] . '_' . $tmpkey;
//var_dump('tmpkey='.$tmpkey.' postkey='.$postkey.' value='.$_POST[$postkey]);
                if (!empty($_POST[$postkey])) {
                    $cookiearrayvalue[$tmpkey] = $_POST[$postkey];
                }
            }
            $cookiename = $tmpautoset[0];
            $cookievalue = json_encode($cookiearrayvalue);
//var_dump('setcookie cookiename='.$cookiename.' cookievalue='.$cookievalue);
            setcookie($cookiename, empty($cookievalue) ? '' : $cookievalue, empty($cookievalue) ? 0 : (time() + (86400 * 354)), '/', null, false, true); // keep cookie 1 year and add tag httponly
            if (empty($cookievalue)) {
                unset($_COOKIE[$cookiename]);
            }
        }
    }

    /**
     * DEPRECATED?
     *
     * Forcing parameter setting magic_quotes_gpc and cleaning parameters
     * (Otherwise he would have for each position, condition
     * Reading stripslashes variable according to state get_magic_quotes_gpc).
     * Off mode recommended (just do Config::$dbEngine->escape for insert / update).
     */
    function stripslashes_deep($value)
    {
        return (is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value));
    }

    /**
     * Security: SQL Injection and XSS Injection (scripts) protection (Filters on GET, POST, PHP_SELF).
     *
     * @param       string      $val        Value
     * @param       string      $type       1=GET, 0=POST, 2=PHP_SELF, 3=GET without sql reserved keywords (the less tolerant test)
     * @return      int                     >0 if there is an injection, 0 if none
     * @deprecated                          use $this->testSqlAndScriptInject
     * @see $this->testSqlAndScriptInject($val, $type)
     */
    function test_sql_and_script_inject($val, $type)
    {
// phpcs:enable
        return $this->testSqlAndScriptInject($val, $type);
    }

    /**
     * Security: SQL Injection and XSS Injection (scripts) protection (Filters on GET, POST, PHP_SELF).
     *
     * @param		string		$val		Value
     * @param		string		$type		1=GET, 0=POST, 2=PHP_SELF, 3=GET without sql reserved keywords (the less tolerant test)
     * @return		int						>0 if there is an injection, 0 if none
     */
    function testSqlAndScriptInject($val, $type)
    {
        $inj = 0;
// For SQL Injection (only GET are used to be included into bad escaped SQL requests)
        if ($type == 1 || $type == 3) {
            $inj += preg_match('/delete\s+from/i', $val);
            $inj += preg_match('/create\s+table/i', $val);
            $inj += preg_match('/insert\s+into/i', $val);
            $inj += preg_match('/select\s+from/i', $val);
            $inj += preg_match('/into\s+(outfile|dumpfile)/i', $val);
            $inj += preg_match('/user\s*\(/i', $val);      // avoid to use function user() that return current database login
            $inj += preg_match('/information_schema/i', $val);    // avoid to use request that read information_schema database
        }
        if ($type == 3) {
            $inj += preg_match('/select|update|delete|replace|group\s+by|concat|count|from/i', $val);
        }
        if ($type != 2) { // Not common key strings, so we can check them both on GET and POST
            $inj += preg_match('/updatexml\(/i', $val);
            $inj += preg_match('/update.+set.+=/i', $val);
            $inj += preg_match('/union.+select/i', $val);
            $inj += preg_match('/(\.\.%2f)+/i', $val);
        }
// For XSS Injection done by adding javascript with script
// This is all cases a browser consider text is javascript:
// When it found '<script', 'javascript:', '<style', 'onload\s=' on body tag, '="&' on a tag size with old browsers
// All examples on page: http://ha.ckers.org/xss.html#XSScalc
// More on https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet
        $inj += preg_match('/<script/i', $val);
        $inj += preg_match('/<iframe/i', $val);
        $inj += preg_match('/<audio/i', $val);
        $inj += preg_match('/Set\.constructor/i', $val); // ECMA script 6
        if (!defined('NOSTYLECHECK')) {
            $inj += preg_match('/<style/i', $val);
        }
        $inj += preg_match('/base[\s]+href/si', $val);
        $inj += preg_match('/<.*onmouse/si', $val);       // onmousexxx can be set on img or any html tag like <img title='...' onmouseover=alert(1)>
        $inj += preg_match('/onerror\s*=/i', $val);       // onerror can be set on img or any html tag like <img title='...' onerror = alert(1)>
        $inj += preg_match('/onfocus\s*=/i', $val);       // onfocus can be set on input text html tag like <input type='text' value='...' onfocus = alert(1)>
        $inj += preg_match('/onload\s*=/i', $val);        // onload can be set on svg tag <svg/onload=alert(1)> or other tag like body <body onload=alert(1)>
        $inj += preg_match('/onloadstart\s*=/i', $val);   // onload can be set on audio tag <audio onloadstart=alert(1)>
        $inj += preg_match('/onclick\s*=/i', $val);       // onclick can be set on img text html tag like <img onclick = alert(1)>
        $inj += preg_match('/onscroll\s*=/i', $val);      // onscroll can be on textarea
//$inj += preg_match('/on[A-Z][a-z]+\*=/', $val);   // To lock event handlers onAbort(), ...
        $inj += preg_match('/&#58;|&#0000058|&#x3A/i', $val);  // refused string ':' encoded (no reason to have it encoded) to lock 'javascript:...'
//if ($type == 1)
//{
        $inj += preg_match('/javascript:/i', $val);
        $inj += preg_match('/vbscript:/i', $val);
//}
// For XSS Injection done by adding javascript closing html tags like with onmousemove, etc... (closing a src or href tag with not cleaned param)
        if ($type == 1) {
            $inj += preg_match('/"/i', $val);  // We refused " in GET parameters value
        }
        if ($type == 2) {
            $inj += preg_match('/[;"]/', $val);  // PHP_SELF is a file system path. It can contains spaces.
        }
        return $inj;
    }

    /**
     * Return true if security check on parameters are OK, false otherwise.
     *
     * @param		string			$var		Variable name
     * @param		string			$type		1=GET, 0=POST, 2=PHP_SELF
     * @return		boolean|null				true if there is no injection. Stop code if injection found.
     */
    function analyseVarsForSqlAndScriptsInjection(&$var, $type)
    {
        if (is_array($var)) {
            foreach ($var as $key => $value) { // Warning, $key may also be used for attacks
                if ($this->analyseVarsForSqlAndScriptsInjection($key, $type) && $this->analyseVarsForSqlAndScriptsInjection($value, $type)) {
//$var[$key] = $value;	// This is useless
                } else {
                    print 'Access refused by SQL/Script injection protection in main.inc.php (type=' . htmlentities($type) . ' key=' . htmlentities($key) . ' value=' . htmlentities($value) . ' page=' . htmlentities($_SERVER["REQUEST_URI"]) . ')';
                    exit;
                }
            }
            return true;
        } else {
            return ($this->testSqlAndScriptInject($var, $type) <= 0);
        }
    }
}
