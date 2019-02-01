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
namespace Alixar\Views;

use Alxarafe\Helpers\Skin;
use Alixar\Helpers\Globals;
use Alixar\Helpers\AlDolUtils;
use Alixar\Helpers\AlDolUtils2;

// require_once DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php';

class LoginView extends \Alixar\Base\AlixarBasicView
{

    public $backgroundImage;
    public $mainLoginBackgroundImage;
    public $useJMobile;
    public $focusElement;
    public $url;
    public $token;
    public $hideTopmenu;
    public $hideLeftmenu;
    public $optimizeSmallscreen;
    public $noMouseHover;

    public function __construct($ctrl)
    {
        parent::__construct($ctrl);
        $this->draw();
        $this->vars();
        Skin::setTemplate('login');
    }

    public function vars()
    {
        $this->backgroundImage = Globals::$conf->global->ADD_UNSPLASH_LOGIN_BACKGROUND ?? null;
        $this->mainLoginBackgroundImage = Globals::$conf->global->MAIN_LOGIN_BACKGROUND ?? 'img/logo.';
        $this->focusElement = $focus_element ?? false;
        $this->token = $_SESSION['newtoken'];
        $this->hideTopmenu = AlDolUtils::GETPOST('dol_hide_topmenu', 'int', 3);
        $this->hideLeftmenu = AlDolUtils::GETPOST('dol_hide_leftmenu', 'int', 3);
        $this->optimizeSmallscreen = AlDolUtils::GETPOST('dol_optimize_smallscreen', 'int', 3);
        $this->noMouseHover = AlDolUtils::GETPOST('dol_no_mouse_hover', 'int', 3);
        $this->useJMobile = AlDolUtils::GETPOST('dol_use_jmobile', 'int', 3) ?? Globals::$conf->dol_use_jmobile ?? false;
    }

    public function draw()
    {
        header('Cache-Control: Public, must-revalidate');
        header("Content-type: text/html; charset=" . Globals::$conf->file->character_set_client);

        // Need global variable $title to be defined by caller (like dol_loginfunction)
        // Caller can also set 	$morelogincontent = array(['options']=>array('js'=>..., 'table'=>...);
        // Protection to avoid direct call of template
        if (empty(Globals::$conf) || !is_object(Globals::$conf)) {
            print "Error, template page can't be called as URL";
            exit;
        }

        if (AlDolUtils::GETPOST('dol_hide_topmenu')) {
            Globals::$conf->dol_hide_topmenu = 1;
        }
        if (AlDolUtils::GETPOST('dol_hide_leftmenu')) {
            Globals::$conf->dol_hide_leftmenu = 1;
        }
        if (AlDolUtils::GETPOST('dol_optimize_smallscreen')) {
            Globals::$conf->dol_optimize_smallscreen = 1;
        }
        if (AlDolUtils::GETPOST('dol_no_mouse_hover')) {
            Globals::$conf->dol_no_mouse_hover = 1;
        }
        if (AlDolUtils::GETPOST('dol_use_jmobile')) {
            Globals::$conf->dol_use_jmobile = 1;
        }

// If we force to use jmobile, then we reenable javascript
        if (!empty(Globals::$conf->dol_use_jmobile)) {
            Globals::$conf->use_javascript_ajax = 1;
        }

        $php_self = AlDolUtils::dol_escape_htmltag($_SERVER['PHP_SELF']);
        $php_self .= AlDolUtils::dol_escape_htmltag($_SERVER["QUERY_STRING"]) ? '?' . AlDolUtils::dol_escape_htmltag($_SERVER["QUERY_STRING"]) : '';
        if (!preg_match('/mainmenu=/', $php_self)) {
            $php_self .= (preg_match('/\?/', $php_self) ? '&' : '?') . 'mainmenu=home';
        }

        $this->url = $php_self;


// Javascript code on logon page only to detect user tz, dst_observed, dst_first, dst_second
        /*
          $arrayofjs=array(
          '/includes/jstz/jstz.min.js'.(empty(Globals::$conf->dol_use_jmobile)?'':'?version='.urlencode(DOL_VERSION)),
          '/core/js/dst.js'.(empty(Globals::$conf->dol_use_jmobile)?'':'?version='.urlencode(DOL_VERSION))
          );
         */
        $arrayofjs = array(
            DOL_BASE_URI . '/includes/jstz/jstz.min.js' . (empty(Globals::$conf->dol_use_jmobile) ? '' : '?version=' . urlencode(DOL_VERSION)),
        DOL_BASE_URI . '/core/js/dst.js' . (empty(Globals::$conf->dol_use_jmobile) ? '' : '?version=' . urlencode(DOL_VERSION))
        );

        // $titletruedolibarrversion is defined by dol_loginfunction in security2.lib.php.
        // We must keep the @, some tools use it to know it is login page and find true dolibarr version.
        // $titleofloginpage = Globals::$langs->trans('Login') . ' @ ' . $titletruedolibarrversion;
        $titleofloginpage = Globals::$langs->trans('Login') . ' @ ' . DOL_VERSION;

        $disablenofollow = 1;
        if (!preg_match('/' . constant('DOL_APPLICATION_TITLE') . '/', $this->title)) {
            $disablenofollow = 0;
        }

        print $this->top_htmlhead('', $titleofloginpage, 0, 0, $arrayofjs, array(), 0, $disablenofollow);


        $colorbackhmenu1 = '60,70,100';      // topmenu
        if (!isset(Globals::$conf->global->THEME_ELDY_TOPMENU_BACK1)) {
            Globals::$conf->global->THEME_ELDY_TOPMENU_BACK1 = $colorbackhmenu1;
        }
        $colorbackhmenu1 = empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED) ? (empty(Globals::$conf->global->THEME_ELDY_TOPMENU_BACK1) ? $colorbackhmenu1 : Globals::$conf->global->THEME_ELDY_TOPMENU_BACK1) : (empty($user->conf->THEME_ELDY_TOPMENU_BACK1) ? $colorbackhmenu1 : $user->conf->THEME_ELDY_TOPMENU_BACK1);
        $colorbackhmenu1 = join(',', AlDolUtils2::colorStringToArray($colorbackhmenu1));    // Normalize value to 'x,y,z'
    }
}
