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

/**
 * This class contains the methods and attributes common to all Alixar view
 *
 * @author Alxarafe
 */
class AlixarBasicView extends \Alxarafe\Base\View
{

    public $defaultlang;
    public $favicon;
    public $ctrl;
    public $head;
    public $title;
    public $help_url;
    public $target;
    public $disablejs;
    public $disablehead;
    public $arrayofjs;
    public $arrayofcss;
    public $morequerystring;
    public $morecssonbody;
    public $replacemainareaby;

    public function __construct($ctrl)
    {
        parent::__construct();
        $this->ctrl = $ctrl;

        $this->defaultlang = 'ES';
        $this->favicon = $this->addResource('/img/favicon', 'ico');
        $this->title = 'Inicio - Alixar 0.0.0-alpha';
    }

    /**
     *  Show HTTP header
     *
     *  @param  string  $contenttype    Content type. For example, 'text/html'
     *  @param	int		$forcenocache	Force disabling of cache for the page
     *  @return	void
     */
    function top_httphead($contenttype = 'text/html', $forcenocache = 0)
    {
        // TODO: Nothing to do?
        return;


        /*
          //global $db, Globals::$conf, Globals::$hookManager;

          if ($contenttype == 'text/html') {
          header("Content-Type: text/html; charset=" . Globals::$conf->file->character_set_client);
          } else {
          header("Content-Type: " . $contenttype);
          }

          // Security options
          header("X-Content-Type-Options: nosniff");  // With the nosniff option, if the server says the content is text/html, the browser will render it as text/html (note that most browsers now force this option to on)
          header("X-Frame-Options: SAMEORIGIN");      // Frames allowed only if on same domain (stop some XSS attacks)
          // header("X-XSS-Protection: 1");      		// XSS protection of some browsers (note: use of Content-Security-Policy is more efficient). Disabled as deprecated.
          if (!defined('FORCECSP')) {
          //if (! isset(Globals::$conf->global->MAIN_HTTP_CONTENT_SECURITY_POLICY))
          //{
          //	// A default security policy that keep usage of js external component like ckeditor, stripe, google, working
          //	$contentsecuritypolicy = "font-src *; img-src *; style-src * 'unsafe-inline' 'unsafe-eval'; default-src 'self' *.stripe.com 'unsafe-inline' 'unsafe-eval'; script-src 'self' *.stripe.com 'unsafe-inline' 'unsafe-eval'; frame-src 'self' *.stripe.com; connect-src 'self';";
          //}
          //else $contentsecuritypolicy = Globals::$conf->global->MAIN_HTTP_CONTENT_SECURITY_POLICY;

          $contentsecuritypolicy = Globals::$conf->global->MAIN_HTTP_CONTENT_SECURITY_POLICY ?? '';
          //$contentsecuritypolicy = Globals::$conf->global->MAIN_HTTP_CONTENT_SECURITY_POLICY;

          /*
          if (!is_object(Globals::$hookManager)) {
          Globals::$hookManager = new HookManager($db);
          }
         * /
          Globals::$hookManager->initHooks("main");

          $parameters = array('contentsecuritypolicy' => $contentsecuritypolicy);
          $result = Globals::$hookManager->executeHooks('setContentSecurityPolicy', $parameters);    // Note that $action and $object may have been modified by some hooks
          if ($result > 0) {
          $contentsecuritypolicy = Globals::$hookManager->resPrint; // Replace CSP
          } else {
          $contentsecuritypolicy .= Globals::$hookManager->resPrint;    // Concat CSP
          }

          if (!empty($contentsecuritypolicy)) {
          // For example, to restrict 'script', 'object', 'frames' or 'img' to some domains:
          // script-src https://api.google.com https://anotherhost.com; object-src https://youtube.com; frame-src https://youtube.com; img-src: https://static.example.com
          // For example, to restrict everything to one domain, except 'object', ...:
          // default-src https://cdn.example.net; object-src 'none'
          // For example, to restrict everything to itself except img that can be on other servers:
          // default-src 'self'; img-src *;
          // Pre-existing site that uses too much inline code to fix but wants to ensure resources are loaded only over https and disable plugins:
          // default-src http: https: 'unsafe-eval' 'unsafe-inline'; object-src 'none'
          header("Content-Security-Policy: " . $contentsecuritypolicy);
          }
          } elseif (constant('FORCECSP')) {
          header("Content-Security-Policy: " . constant('FORCECSP'));
          }
          if ($forcenocache) {
          header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
          }
         */
    }

    /**
     * Ouput html header of a page.
     * This code is also duplicated into security2.lib.php::dol_loginfunction
     *
     * @param 	string 	$head			 Optionnal head lines
     * @param 	string 	$title			 HTML title
     * @param 	int    	$disablejs		 Disable js output
     * @param 	int    	$disablehead	 Disable head output
     * @param 	array  	$arrayofjs		 Array of complementary js files
     * @param 	array  	$arrayofcss		 Array of complementary css files
     * @param 	int    	$disablejmobile	 Disable jmobile (No more used)
     * @param   int     $disablenofollow Disable no follow tag
     * @return	void
     */
    function top_htmlhead(/* $head, $title = '', $disablejs = 0, $disablehead = 0, $arrayofjs = '', $arrayofcss = '', $disablejmobile = 0, $disablenofollow = 0 */)
    {
        $this->top_httphead();  // TODO: Null method!
        // TODO: Nothing to do?
        return;

        /*
          //global $db, Globals::$conf, Globals::$langs, Globals::$user, Globals::$hookManager;

          if (empty(Globals::$conf->css)) {
          // Globals::$conf->css = '/theme/eldy/style.css.php'; // If not defined, eldy by default
          Globals::$conf->css = '?controller=theme/eldy&method=style.css';
          }
          print '<!doctype html>' . "\n";

          if (!empty(Globals::$conf->global->MAIN_USE_CACHE_MANIFEST))
          print '<html lang="' . substr(Globals::$langs->defaultlang, 0, 2) . '" manifest="' . DOL_BASE_URI . '/cache.manifest">' . "\n";
          else
          print '<html lang="' . substr(Globals::$langs->defaultlang, 0, 2) . '">' . "\n";
          //print '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr">'."\n";
          if (empty($disablehead)) {
          $ext = 'layout=' . Globals::$conf->browser->layout . '&version=' . urlencode(DOL_VERSION);

          print "<head>\n";

          if (DolUtils::GETPOST('dol_basehref', 'alpha'))
          print '<base href="' . dol_escape_htmltag(DolUtils::GETPOST('dol_basehref', 'alpha')) . '">' . "\n";

          // Displays meta
          print '<meta charset="UTF-8">' . "\n";
          print '<meta name="robots" content="noindex' . ($disablenofollow ? '' : ',nofollow') . '">' . "\n"; // Do not index
          print '<meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";  // Scale for mobile device
          print '<meta name="author" content="Dolibarr Development Team">' . "\n";

          // Favicon
          // $favicon = DolUtils::dol_buildpath('/theme/' . Globals::$conf->theme . '/img/favicon.ico', 1);
          $favicon = $this->addResource('/img/favicon', 'ico');
          if (!empty(Globals::$conf->global->MAIN_FAVICON_URL)) {
          $favicon = Globals::$conf->global->MAIN_FAVICON_URL;
          }
          if (empty(Globals::$conf->dol_use_jmobile))
          print '<link rel="shortcut icon" type="image/x-icon" href="' . $favicon . '"/>' . "\n"; // Not required into an Android webview
          //if (empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) print '<link rel="top" title="'.Globals::$langs->trans("Home").'" href="'.(DOL_BASE_URI?DOL_BASE_URI:'/').'">'."\n";
          //if (empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) print '<link rel="copyright" title="GNU General Public License" href="http://www.gnu.org/copyleft/gpl.html#SEC1">'."\n";
          //if (empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) print '<link rel="author" title="Dolibarr Development Team" href="https://www.dolibarr.org">'."\n";
          // Auto refresh page
          if (DolUtils::GETPOST('autorefresh', 'int') > 0)
          print '<meta http-equiv="refresh" content="' . GETPOST('autorefresh', 'int') . '">';

          // Displays title
          $appli = constant('DOL_APPLICATION_TITLE');
          if (!empty(Globals::$conf->global->MAIN_APPLICATION_TITLE))
          $appli = Globals::$conf->global->MAIN_APPLICATION_TITLE;

          print '<title>';
          $titletoshow = '';
          if ($title && !empty(Globals::$conf->global->MAIN_HTML_TITLE) && preg_match('/noapp/', Globals::$conf->global->MAIN_HTML_TITLE))
          $titletoshow = DolUtils::dol_htmlentities($title);
          else if ($title)
          $titletoshow = DolUtils::dol_htmlentities($appli . ' - ' . $title);
          else
          $titletoshow = DolUtils::dol_htmlentities($appli);

          if (!is_object(Globals::$hookManager))
          Globals::$hookManager = new HookManager($db);
          Globals::$hookManager->initHooks("main");
          $parameters = array('title' => $titletoshow);
          $result = Globals::$hookManager->executeHooks('setHtmlTitle', $parameters);  // Note that $action and $object may have been modified by some hooks
          if ($result > 0)
          $titletoshow = Globals::$hookManager->resPrint;    // Replace Title to show
          else
          $titletoshow .= Globals::$hookManager->resPrint;      // Concat to Title to show

          print $titletoshow;
          print '</title>';

          print "\n";

          if (DolUtils::GETPOST('version', 'int')) {
          $ext = 'version=' . GETPOST('version', 'int'); // usefull to force no cache on css/js
          }
          if (DolUtils::GETPOST('testmenuhider', 'int') || !empty(Globals::$conf->global->MAIN_TESTMENUHIDER)) {
          $ext .= '&testmenuhider=' . (DolUtils::GETPOST('testmenuhider', 'int') ? GETPOST('testmenuhider', 'int') : Globals::$conf->global->MAIN_TESTMENUHIDER);
          }
          $themeparam = '&lang=' . Globals::$langs->defaultlang . '&amp;theme=' . Globals::$conf->theme . (DolUtils::GETPOST('optioncss', 'aZ09') ? '&amp;optioncss=' . GETPOST('optioncss', 'aZ09', 1) : '') . '&amp;userid=' . Globals::$user->id . '&amp;entity=' . Globals::$conf->entity;
          $themeparam .= ($ext ? '&amp;' . $ext : '');
          if (!empty($_SESSION['dol_resetcache'])) {
          $themeparam .= '&amp;dol_resetcache=' . $_SESSION['dol_resetcache'];
          }
          if (DolUtils::GETPOST('dol_hide_topmenu', 'int')) {
          $themeparam .= '&amp;dol_hide_topmenu=' . GETPOST('dol_hide_topmenu', 'int');
          }
          if (DolUtils::GETPOST('dol_hide_leftmenu', 'int')) {
          $themeparam .= '&amp;dol_hide_leftmenu=' . GETPOST('dol_hide_leftmenu', 'int');
          }
          if (DolUtils::GETPOST('dol_optimize_smallscreen', 'int')) {
          $themeparam .= '&amp;dol_optimize_smallscreen=' . GETPOST('dol_optimize_smallscreen', 'int');
          }
          if (DolUtils::GETPOST('dol_no_mouse_hover', 'int')) {
          $themeparam .= '&amp;dol_no_mouse_hover=' . GETPOST('dol_no_mouse_hover', 'int');
          }
          if (DolUtils::GETPOST('dol_use_jmobile', 'int')) {
          $themeparam .= '&amp;dol_use_jmobile=' . GETPOST('dol_use_jmobile', 'int');
          Globals::$conf->dol_use_jmobile = DolUtils::GETPOST('dol_use_jmobile', 'int');
          }

          if (!defined('DISABLE_JQUERY') && !$disablejs && Globals::$conf->use_javascript_ajax) {
          print '<!-- Includes CSS for JQuery (Ajax library) -->' . "\n";
          $jquerytheme = 'base';
          if (!empty(Globals::$conf->global->MAIN_USE_JQUERY_THEME)) {
          $jquerytheme = Globals::$conf->global->MAIN_USE_JQUERY_THEME;
          }
          if (constant('JS_JQUERY_UI')) {
          print '<link rel="stylesheet" type="text/css" href="' . JS_JQUERY_UI . 'css/' . $jquerytheme . '/jquery-ui.min.css' . ($ext ? '?' . $ext : '') . '">' . "\n";  // JQuery
          } else {
          print '<link rel="stylesheet" type="text/css" href="' . DOL_BASE_URI . '/includes/jquery/css/' . $jquerytheme . '/jquery-ui.css' . ($ext ? '?' . $ext : '') . '">' . "\n";    // JQuery
          }
          if (!defined('DISABLE_JQUERY_JNOTIFY')) {
          print '<link rel="stylesheet" type="text/css" href="' . DOL_BASE_URI . '/includes/jquery/plugins/jnotify/jquery.jnotify-alt.min.css' . ($ext ? '?' . $ext : '') . '">' . "\n";          // JNotify
          }
          if (!defined('DISABLE_SELECT2') && (!empty(Globals::$conf->global->MAIN_USE_JQUERY_MULTISELECT) || defined('REQUIRE_JQUERY_MULTISELECT'))) {     // jQuery plugin "mutiselect", "multiple-select", "select2"...
          $tmpplugin = empty(Globals::$conf->global->MAIN_USE_JQUERY_MULTISELECT) ? constant('REQUIRE_JQUERY_MULTISELECT') : Globals::$conf->global->MAIN_USE_JQUERY_MULTISELECT;
          print '<link rel="stylesheet" type="text/css" href="' . DOL_BASE_URI . '/includes/jquery/plugins/' . $tmpplugin . '/dist/css/' . $tmpplugin . '.css' . ($ext ? '?' . $ext : '') . '">' . "\n";
          }
          }

          if (!defined('DISABLE_FONT_AWSOME')) {
          print '<!-- Includes CSS for font awesome -->' . "\n";
          // print '<link rel="stylesheet" type="text/css" href="' . DOL_BASE_URI . '/theme/common/fontawesome/css/font-awesome.min.css' . ($ext ? '?' . $ext : '') . '">' . "\n";
          // TODO: Check the fontawesome version we are going to use
          //print '<link rel="stylesheet" type="text/css" href="' . BASE_URI . '/vendor/components/font-awesome/css/fontawesome.min.css' . ($ext ? '?' . $ext : '') . '">' . "\n";
          print '<link rel="stylesheet" type="text/css" href="' . BASE_URI . '/vendor/maximebf/debugbar/src/DebugBar/Resources/vendor/font-awesome/css/font-awesome.min.css' . ($ext ? '?' . $ext : '') . '">' . "\n";
          }

          print '<!-- Includes CSS for Dolibarr theme -->' . "\n";
          // Output style sheets (optioncss='print' or ''). Note: Globals::$conf->css looks like '/theme/eldy/style.css.php'
          $themepath = DolUtils::dol_buildpath(Globals::$conf->css, 3);
          $themesubdir = '';
          if (!empty(Globals::$conf->modules_parts['theme'])) { // This slow down
          foreach (Globals::$conf->modules_parts['theme'] as $reldir) {
          if (file_exists(dol_buildpath($reldir . Globals::$conf->css, 0))) {
          $themepath = DolUtils::dol_buildpath($reldir . Globals::$conf->css, 1);
          $themesubdir = $reldir;
          break;
          }
          }
          }

          //print 'themepath='.$themepath.' themeparam='.$themeparam;exit;
          print '<link rel="stylesheet" type="text/css" href="' . $themepath . $themeparam . '">' . "\n";
          if (!empty(Globals::$conf->global->MAIN_FIX_FLASH_ON_CHROME))
          print '<!-- Includes CSS that does not exists as a workaround of flash bug of chrome -->' . "\n" . '<link rel="stylesheet" type="text/css" href="filethatdoesnotexiststosolvechromeflashbug">' . "\n";

          // CSS forced by modules (relative url starting with /)
          if (!empty(Globals::$conf->modules_parts['css'])) {
          $arraycss = (array) Globals::$conf->modules_parts['css'];
          foreach ($arraycss as $modcss => $filescss) {
          $filescss = (array) $filescss; // To be sure filecss is an array
          foreach ($filescss as $cssfile) {
          if (empty($cssfile))
          dol_syslog("Warning: module " . $modcss . " declared a css path file into its descriptor that is empty.", LOG_WARNING);
          // cssfile is a relative path
          print '<!-- Includes CSS added by module ' . $modcss . ' -->' . "\n" . '<link rel="stylesheet" type="text/css" href="' . dol_buildpath($cssfile, 1);
          // We add params only if page is not static, because some web server setup does not return content type text/css if url has parameters, so browser cache is not used.
          if (!preg_match('/\.css$/i', $cssfile))
          print $themeparam;
          print '">' . "\n";
          }
          }
          }
          // CSS forced by page in top_htmlhead call (relative url starting with /)
          if (is_array($arrayofcss)) {
          foreach ($arrayofcss as $cssfile) {
          print '<!-- Includes CSS added by page -->' . "\n" . '<link rel="stylesheet" type="text/css" title="default" href="' . dol_buildpath($cssfile, 1);
          // We add params only if page is not static, because some web server setup does not return content type text/css if url has parameters and browser cache is not used.
          if (!preg_match('/\.css$/i', $cssfile))
          print $themeparam;
          print '">' . "\n";
          }
          }

          // Output standard javascript links
          if (!defined('DISABLE_JQUERY') && !$disablejs && !empty(Globals::$conf->use_javascript_ajax)) {
          // JQuery. Must be before other includes
          print '<!-- Includes JS for JQuery -->' . "\n";
          if (defined('JS_JQUERY') && constant('JS_JQUERY')) {
          print '<script type="text/javascript" src="' . JS_JQUERY . 'jquery.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          } else {
          // print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/js/jquery.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . BASE_URI . '/vendor/components/jquery/jquery.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          if (!empty(Globals::$conf->global->MAIN_FEATURES_LEVEL) && !defined('JS_JQUERY_MIGRATE_DISABLED')) {
          if (defined('JS_JQUERY_MIGRATE') && constant('JS_JQUERY_MIGRATE')) {
          print '<script type="text/javascript" src="' . JS_JQUERY_MIGRATE . 'jquery-migrate.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          } else {
          // print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/js/jquery-migrate.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . BASE_URI . '/vendor/components/jquery/jquery-migrate.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          }
          if (defined('JS_JQUERY_UI') && constant('JS_JQUERY_UI')) {
          print '<script type="text/javascript" src="' . JS_JQUERY_UI . 'jquery-ui.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          } else {
          // print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/js/jquery-ui.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . BASE_URI . '/vendor/components/jqueryui/jquery-ui.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          if (!defined('DISABLE_JQUERY_TABLEDND')) {
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/tablednd/jquery.tablednd.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          // jQuery jnotify
          if (empty(Globals::$conf->global->MAIN_DISABLE_JQUERY_JNOTIFY) && !defined('DISABLE_JQUERY_JNOTIFY')) {
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/jnotify/jquery.jnotify.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          // Flot
          if (empty(Globals::$conf->global->MAIN_DISABLE_JQUERY_FLOT) && !defined('DISABLE_JQUERY_FLOT')) {
          if (constant('JS_JQUERY_FLOT')) {
          print '<script type="text/javascript" src="' . JS_JQUERY_FLOT . 'jquery.flot.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . JS_JQUERY_FLOT . 'jquery.flot.pie.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . JS_JQUERY_FLOT . 'jquery.flot.stack.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          } else {
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/flot/jquery.flot.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/flot/jquery.flot.pie.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/flot/jquery.flot.stack.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          }
          // jQuery jeditable
          if (!empty(Globals::$conf->global->MAIN_USE_JQUERY_JEDITABLE) && !defined('DISABLE_JQUERY_JEDITABLE')) {
          print '<!-- JS to manage editInPlace feature -->' . "\n";
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/jeditable/jquery.jeditable.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/jeditable/jquery.jeditable.ui-datepicker.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/jeditable/jquery.jeditable.ui-autocomplete.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript">' . "\n";
          print 'var urlSaveInPlace = \'' . DOL_BASE_URI . '/core/ajax/saveinplace.php\';' . "\n";
          print 'var urlLoadInPlace = \'' . DOL_BASE_URI . '/core/ajax/loadinplace.php\';' . "\n";
          print 'var tooltipInPlace = \'' . Globals::$langs->transnoentities('ClickToEdit') . '\';' . "\n"; // Added in title attribute of span
          print 'var placeholderInPlace = \'&nbsp;\';' . "\n"; // If we put another string than Globals::$langs->trans("ClickToEdit") here, nothing is shown. If we put empty string, there is error, Why ?
          print 'var cancelInPlace = \'' . Globals::$langs->trans('Cancel') . '\';' . "\n";
          print 'var submitInPlace = \'' . Globals::$langs->trans('Ok') . '\';' . "\n";
          print 'var indicatorInPlace = \'<img src="' . DOL_BASE_URI . "/theme/" . Globals::$conf->theme . "/img/working.gif" . '">\';' . "\n";
          print 'var withInPlace = 300;';  // width in pixel for default string edit
          print '</script>' . "\n";
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/core/js/editinplace.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          // print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/jeditable/jquery.jeditable.ckeditor.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/jeditable/jquery.jeditable.ckeditor.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          // jQuery Timepicker
          if (!empty(Globals::$conf->global->MAIN_USE_JQUERY_TIMEPICKER) || defined('REQUIRE_JQUERY_TIMEPICKER')) {
          // print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/timepicker/jquery-ui-timepicker-addon.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . BASE_URI . '/vendor/components/jqueryui/ui/widgets/timepicker.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . BASE_PATH . '?controller=core/js/&method=timepicker.js&lang=' . Globals::$langs->defaultlang . ($ext ? '&amp;' . $ext : '') . '"></script>' . "\n";
          }
          if (!defined('DISABLE_SELECT2') && (!empty(Globals::$conf->global->MAIN_USE_JQUERY_MULTISELECT) || defined('REQUIRE_JQUERY_MULTISELECT'))) {     // jQuery plugin "mutiselect", "multiple-select", "select2", ...
          $tmpplugin = empty(Globals::$conf->global->MAIN_USE_JQUERY_MULTISELECT) ? constant('REQUIRE_JQUERY_MULTISELECT') : Globals::$conf->global->MAIN_USE_JQUERY_MULTISELECT;
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/' . $tmpplugin . '/dist/js/' . $tmpplugin . '.full.min.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n"; // We include full because we need the support of containerCssClass
          }
          if (!defined('DISABLE_MULTISELECT')) {     // jQuery plugin "mutiselect" to select with checkboxes
          print '<script type="text/javascript" src="' . DOL_BASE_URI . '/includes/jquery/plugins/multiselect/jquery.multi-select.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          }

          if (!$disablejs && !empty(Globals::$conf->use_javascript_ajax)) {
          // CKEditor
          if (!empty(Globals::$conf->fckeditor->enabled) && (empty(Globals::$conf->global->FCKEDITOR_EDITORNAME) || Globals::$conf->global->FCKEDITOR_EDITORNAME == 'ckeditor') && !defined('DISABLE_CKEDITOR')) {
          print '<!-- Includes JS for CKEditor -->' . "\n";
          // $pathckeditor = DOL_BASE_URI . '/includes/ckeditor/ckeditor/';
          $pathckeditor = BASE_URI . '/vendor/ckeditor/ckeditor/';
          $jsckeditor = 'ckeditor.js';
          if (constant('JS_CKEDITOR')) { // To use external ckeditor 4 js lib
          $pathckeditor = constant('JS_CKEDITOR');
          }
          print '<script type="text/javascript">';
          print 'var CKEDITOR_BASEPATH = \'' . $pathckeditor . '\';' . "\n";
          print 'var ckeditorConfig = \'' . dol_buildpath($themesubdir . '/theme/' . Globals::$conf->theme . '/ckeditor/config.js' . ($ext ? '?' . $ext : ''), 1) . '\';' . "\n";  // $themesubdir='' in standard usage
          print 'var ckeditorFilebrowserBrowseUrl = \'' . DOL_BASE_URI . '/core/filemanagerdol/browser/default/browser.php?Connector=' . DOL_BASE_URI . '/core/filemanagerdol/connectors/php/connector.php\';' . "\n";
          print 'var ckeditorFilebrowserImageBrowseUrl = \'' . DOL_BASE_URI . '/core/filemanagerdol/browser/default/browser.php?Type=Image&Connector=' . DOL_BASE_URI . '/core/filemanagerdol/connectors/php/connector.php\';' . "\n";
          print '</script>' . "\n";
          print '<script type="text/javascript" src="' . $pathckeditor . $jsckeditor . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }

          // Browser notifications
          if (!defined('DISABLE_BROWSER_NOTIF')) {
          $enablebrowsernotif = false;
          if (!empty(Globals::$conf->agenda->enabled) && !empty(Globals::$conf->global->AGENDA_REMINDER_BROWSER))
          $enablebrowsernotif = true;
          if (Globals::$conf->browser->layout == 'phone')
          $enablebrowsernotif = false;
          if ($enablebrowsernotif) {
          print '<!-- Includes JS of Dolibarr (brwoser layout = ' . Globals::$conf->browser->layout . ')-->' . "\n";
          //print '<script type="text/javascript" src="' . DOL_BASE_URI . '/core/js/lib_notification.js.php' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . BASE_URI . '?controller=core/js/&method=lib_notification.js' . ($ext ? '?' . $ext : '') . '"></script>' . "\n";
          }
          }

          // Global js function
          print '<!-- Includes JS of Dolibarr -->' . "\n";
          //print '<script type="text/javascript" src="' . DOL_BASE_URI . '/core/js/lib_head.js.php?lang=' . Globals::$langs->defaultlang . ($ext ? '&' . $ext : '') . '"></script>' . "\n";
          print '<script type="text/javascript" src="' . BASE_URI . '?controller=core/js/&method=lib_head.js&lang=' . Globals::$langs->defaultlang . ($ext ? '&' . $ext : '') . '"></script>' . "\n";

          // JS forced by modules (relative url starting with /)
          if (!empty(Globals::$conf->modules_parts['js'])) {  // Globals::$conf->modules_parts['js'] is array('module'=>array('file1','file2'))
          $arrayjs = (array) Globals::$conf->modules_parts['js'];
          foreach ($arrayjs as $modjs => $filesjs) {
          $filesjs = (array) $filesjs; // To be sure filejs is an array
          foreach ($filesjs as $jsfile) {
          // jsfile is a relative path
          print '<!-- Include JS added by module ' . $modjs . '-->' . "\n" . '<script type="text/javascript" src="' . dol_buildpath($jsfile, 1) . '"></script>' . "\n";
          }
          }
          }
          // JS forced by page in top_htmlhead (relative url starting with /)
          if (is_array($arrayofjs)) {
          print '<!-- Includes JS added by page -->' . "\n";
          foreach ($arrayofjs as $jsfile) {
          if (preg_match('/^http/i', $jsfile)) {
          print '<script type="text/javascript" src="' . $jsfile . '"></script>' . "\n";
          } else {
          print '<script type="text/javascript" src="' . dol_buildpath($jsfile, 1) . '"></script>' . "\n";
          }
          }
          }
          }

          if (!empty($head))
          print $head . "\n";
          if (!empty(Globals::$conf->global->MAIN_HTML_HEADER))
          print Globals::$conf->global->MAIN_HTML_HEADER . "\n";

          print "<!-- Alixar debugBar header -->";
          print Debug::getRenderHeader(); // Includes Alixar debugBar header

          print "</head>\n\n";
          }

          Globals::$conf->headerdone = 1; // To tell header was output
         */
    }
}
