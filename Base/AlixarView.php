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
class AlixarView extends \Alixar\Base\AlixarBasicView
{

    //public $menumanager;

    public function __construct($ctrl)
    {
        parent::__construct($ctrl);
        //Globals::$menuManager = $this->ctrl->menuManager;
    }

    public function getTopMenu()
    {
        $ret[] = [
            'text' => '<i class="fa fa-cog fa-fw"></i> Config',
            'href' => '?call=EditConfig',
        ];
        $ret[] = [
            'text' => '<i class="fa fa-database fa-fw"></i> Database',
            'href' => 'index.html',
            'options' => [
                [
                    'text' => '<i class="fa fa-address-book fa-fw"></i> People',
                    'href' => '?call=People'
                ],
                [
                    'text' => '<i class="fa fa-automobile fa-fw"></i> Vehicles',
                    'href' => '?call=Vehicles',
                    'options' => [
                        'text' => '<i class="fa fa-address-book fa-fw"></i> People',
                        'href' => '?call=People'
                    ]
                ]
            ]
        ];

        return $ret;
    }

    public function getLeftMenu(): array
    {
        $ret[] = [
            'text' => '<i class="fa fa-cog fa-fw"></i> Config',
            'href' => '?call=EditConfig',
        ];
        $ret[] = [
            'text' => '<i class="fa fa-database fa-fw"></i> Database',
            'href' => 'index.html',
            'options' => [
                [
                    'text' => '<i class="fa fa-address-book fa-fw"></i> People',
                    'href' => '?call=People'
                ],
                [
                    'text' => '<i class="fa fa-automobile fa-fw"></i> Vehicles',
                    'href' => '?call=Vehicles',
                    'options' => [
                        'text' => '<i class="fa fa-address-book fa-fw"></i> People',
                        'href' => '?call=People'
                    ]
                ]
            ]
        ];

        return $ret;
    }

    /**
     * 	Show HTML header HTML + BODY + Top menu + left menu + DIV
     *
     * @param 	string 	$head				Optionnal head lines
     * @param 	string 	$title				HTML title
     * @param	string	$help_url			Url links to help page
     * 		                            	Syntax is: For a wiki page: EN:EnglishPage|FR:FrenchPage|ES:SpanishPage
     *                                  	For other external page: http://server/url
     * @param	string	$target				Target to use on links
     * @param 	int    	$disablejs			More content into html header
     * @param 	int    	$disablehead		More content into html header
     * @param 	array  	$arrayofjs			Array of complementary js files
     * @param 	array  	$arrayofcss			Array of complementary css files
     * @param	string	$morequerystring	Query string to add to the link "print" to get same parameters (use only if autodetect fails)
     * @param   string  $morecssonbody      More CSS on body tag.
     * @param	string	$replacemainareaby	Replace call to main_area() by a print of this string
     * @return	void
     */
    function llxHeader($head = '', $title = '', $help_url = '', $target = '', $disablejs = 0, $disablehead = 0, $arrayofjs = '', $arrayofcss = '', $morequerystring = '', $morecssonbody = '', $replacemainareaby = '')
    {
//global $conf;
// html header
        $this->top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);

        print '<body id="mainbody"' . ($morecssonbody ? ' class="' . $morecssonbody . '"' : '') . '>' . "\n";

// top menu and left menu area
        if (empty(Globals::$conf->dol_hide_topmenu) || GETPOST('dol_invisible_topmenu', 'int')) {
            $this->top_menu($head, $title, $target, $disablejs, $disablehead, $arrayofjs, $arrayofcss, $morequerystring, $help_url);
        }

        if (empty(Globals::$conf->dol_hide_leftmenu)) {
            $this->left_menu('', $help_url, '', '', 1, $title, 1);  // $menumanager is retreived with a global $menumanager inside this function
        }

// main area
        if ($replacemainareaby) {
            print $replacemainareaby;
            return;
        }
        $this->main_area($title);
    }

    /**
     *  Show an HTML header + a BODY + The top menu bar
     *
     *  @param      string	$head    			Lines in the HEAD
     *  @param      string	$title   			Title of web page
     *  @param      string	$target  			Target to use in menu links (Example: '' or '_top')
     * 	@param		int		$disablejs			Do not output links to js (Ex: qd fonction utilisee par sous formulaire Ajax)
     * 	@param		int		$disablehead		Do not output head section
     * 	@param		array	$arrayofjs			Array of js files to add in header
     * 	@param		array	$arrayofcss			Array of css files to add in header
     *  @param		string	$morequerystring	Query string to add to the link "print" to get same parameters (use only if autodetect fails)
     *  @param      string	$helppagename    	Name of wiki page for help ('' by default).
     * 				     		                Syntax is: For a wiki page: EN:EnglishPage|FR:FrenchPage|ES:SpanishPage
     * 						                    For other external page: http://server/url
     *  @return		void
     */
    function top_menu($head, $title = '', $target = '', $disablejs = 0, $disablehead = 0, $arrayofjs = '', $arrayofcss = '', $morequerystring = '', $helppagename = '')
    {
// global Globals::$user, Globals::$conf, Globals::$langs, $db;
// global $dolibarr_main_authentication, $dolibarr_main_demo;
// global Globals::$hookManager, $menumanager;

        $searchform = '';
        $bookmarks = '';

// Instantiate hooks of thirdparty module
        Globals::$hookManager->initHooks(array('toprightmenu'));

        $toprightmenu = '';

// For backward compatibility with old modules
        if (empty(Globals::$conf->headerdone)) {
            $this->top_htmlhead($head, $title, $disablejs, $disablehead, $arrayofjs, $arrayofcss);
            print '<body id="mainbody">';
        }

        /*
         * Top menu
         */
        if ((empty(Globals::$conf->dol_hide_topmenu) || GETPOST('dol_invisible_topmenu', 'int')) && (!defined('NOREQUIREMENU') || !constant('NOREQUIREMENU'))) {
            print "\n" . '<!-- Start top horizontal -->' . "\n";

            print '<div class="side-nav-vert' . (DolUtils::GETPOST('dol_invisible_topmenu', 'int') ? ' hidden' : '') . '"><div id="id-top">';  // dol_invisible_topmenu differs from dol_hide_topmenu: dol_invisible_topmenu means we output menu but we make it invisible.
// Show menu entries
            print '<div id="tmenu_tooltip' . (empty(Globals::$conf->global->MAIN_MENU_INVERT) ? '' : 'invert') . '" class="tmenu">' . "\n";
            Globals::$menuManager->atarget = $target;
            Globals::$menuManager->showmenu('top', array('searchform' => $searchform, 'bookmarks' => $bookmarks));      // This contains a \n
            print "</div>\n";

// Define link to login card
            $appli = constant('DOL_APPLICATION_TITLE');
            if (!empty(Globals::$conf->global->MAIN_APPLICATION_TITLE)) {
                $appli = Globals::$conf->global->MAIN_APPLICATION_TITLE;
                if (preg_match('/\d\.\d/', $appli)) {
                    if (!preg_match('/' . preg_quote(DOL_VERSION) . '/', $appli))
                        $appli .= " (" . DOL_VERSION . ")"; // If new title contains a version that is different than core
                } else
                    $appli .= " " . DOL_VERSION;
            } else
                $appli .= " " . DOL_VERSION;

            if (!empty(Globals::$conf->global->MAIN_FEATURES_LEVEL))
                $appli .= "<br>" . Globals::$langs->trans("LevelOfFeature") . ': ' . Globals::$conf->global->MAIN_FEATURES_LEVEL;

            $logouttext = '';
            if (empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
//$logouthtmltext=$appli.'<br>';
                $logouthtmltext = '';
                if ($_SESSION["dol_authmode"] != 'forceuser' && $_SESSION["dol_authmode"] != 'http') { {
                        $logouthtmltext .= Globals::$langs->trans("Logout") . '<br>';
                    }

//$logouttext .='<a accesskey="l" href="'.DOL_BASE_URI.'/user/logout.php">';
                    $logouttext .= '<a accesskey="l" href="' . BASE_URI . '?controller=user&method=logout">';
//$logouttext .= img_picto(Globals::$langs->trans('Logout').":".Globals::$langs->trans('Logout'), 'logout_top.png', 'class="login"', 0, 0, 1);
                    $logouttext .= '<span class="fa fa-sign-out atoplogin"></span>';
                    $logouttext .= '</a>';
                } else {
                    $logouthtmltext .= Globals::$langs->trans("NoLogoutProcessWithAuthMode", $_SESSION["dol_authmode"]);
                    $logouttext .= img_picto(Globals::$langs->trans('Logout') . ":" . Globals::$langs->trans('Logout'), 'logout_top.png', 'class="login"', 0, 0, 1);
                }
            }

            print '<div class="login_block">' . "\n";

// Add login user link
            $toprightmenu .= '<div class="login_block_user">';

// Login name with photo and tooltip
            $mode = -1;
            $toprightmenu .= '<div class="inline-block nowrap"><div class="inline-block login_block_elem login_block_elem_name" style="padding: 0px;">';
            $toprightmenu .= Globals::$user->getNomUrl($mode, '', 1, 0, 11, 0, (Globals::$user->firstname ? 'firstname' : -1), 'atoplogin');
            $toprightmenu .= '</div></div>';

            $toprightmenu .= '</div>' . "\n";

            $toprightmenu .= '<div class="login_block_other">';

// Execute hook printTopRightMenu (hooks should output string like '<div class="login"><a href="">mylink</a></div>')
            $parameters = array();
            $result = Globals::$hookManager->executeHooks('printTopRightMenu', $parameters);    // Note that $action and $object may have been modified by some hooks
            if (is_numeric($result)) {
                if ($result == 0)
                    $toprightmenu .= Globals::$hookManager->resPrint;  // add
                else
                    $toprightmenu = Globals::$hookManager->resPrint;      // replace
            }
            else {
                $toprightmenu .= $result; // For backward compatibility
            }

// Link to module builder
            if (!empty(Globals::$conf->modulebuilder->enabled)) {
//$text = '<a href="' . DOL_BASE_URI . '/modulebuilder/index.php?mainmenu=home&leftmenu=admintools" target="_modulebuilder">';
                $text = '<a href="' . BASE_URI . '?controller=modulebuilder&method=index&mainmenu=home&leftmenu=admintools" target="_modulebuilder">';
//$text.= img_picto(":".Globals::$langs->trans("ModuleBuilder"), 'printer_top.png', 'class="printer"');
                $text .= '<span class="fa fa-bug atoplogin"></span>';
                $text .= '</a>';
                $toprightmenu .= @Form::textwithtooltip('', Globals::$langs->trans("ModuleBuilder"), 2, 1, $text, 'login_block_elem', 2);
            }

// Link to print main content area
            if (empty(Globals::$conf->global->MAIN_PRINT_DISABLELINK) && empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) && Globals::$conf->browser->layout != 'phone') {
                $qs = dol_escape_htmltag($_SERVER["QUERY_STRING"]);

                if (is_array($_POST)) {
                    foreach ($_POST as $key => $value) {
                        if ($key !== 'action' && $key !== 'password' && !is_array($value)) {
                            $qs .= '&' . $key . '=' . urlencode($value);
                        }
                    }
                }
                $qs .= (($qs && $morequerystring) ? '&' : '') . $morequerystring;
                $text = '<a href="' . dol_escape_htmltag($_SERVER["PHP_SELF"]) . '?' . $qs . ($qs ? '&' : '') . 'optioncss=print" target="_blank">';
//$text.= img_picto(":".Globals::$langs->trans("PrintContentArea"), 'printer_top.png', 'class="printer"');
                $text .= '<span class="fa fa-print atoplogin"></span>';
                $text .= '</a>';
                $toprightmenu .= @Form::textwithtooltip('', Globals::$langs->trans("PrintContentArea"), 2, 1, $text, 'login_block_elem', 2);
            }

// Link to Dolibarr wiki pages
            if (empty(Globals::$conf->global->MAIN_HELP_DISABLELINK) && empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                Globals::$langs->load("help");

                $helpbaseurl = '';
                $helppage = '';
                $mode = '';

                if (empty($helppagename)) {
                    $helppagename = 'EN:User_documentation|FR:Documentation_utilisateur|ES:Documentación_usuarios';
                }
// Get helpbaseurl, helppage and mode from helppagename and langs
                $arrayres = getHelpParamFor($helppagename, Globals::$langs);
                $helpbaseurl = $arrayres['helpbaseurl'];
                $helppage = $arrayres['helppage'];
                $mode = $arrayres['mode'];

// Link to help pages
                if ($helpbaseurl && $helppage) {
                    $text = '';
                    if (!empty(Globals::$conf->global->MAIN_SHOWDATABASENAMEINHELPPAGESLINK)) {
                        Globals::$langs->load('admin');
                        $appli .= '<br>' . Globals::$langs->trans("Database") . ': ' . $db->database_name;
                    }
                    $title = $appli . '<br>';
                    $title .= Globals::$langs->trans($mode == 'wiki' ? 'GoToWikiHelpPage' : 'GoToHelpPage');
                    if ($mode == 'wiki') {
                        $title .= ' - ' . Globals::$langs->trans("PageWiki") . ' &quot;' . dol_escape_htmltag(strtr($helppage, '_', ' ')) . '&quot;';
                    }
                    $text .= '<a class="help" target="_blank" rel="noopener" href="';
                    if ($mode == 'wiki') {
                        $text .= sprintf($helpbaseurl, urlencode(html_entity_decode($helppage)));
                    } else {
                        $text .= sprintf($helpbaseurl, $helppage);
                    }
                    $text .= '">';
//$text.=img_picto('', 'helpdoc_top').' ';
                    $text .= '<span class="fa fa-question-circle atoplogin"></span>';
//$toprightmenu.=Globals::$langs->trans($mode == 'wiki' ? 'OnlineHelp': 'Help');
//if ($mode == 'wiki') $text.=' ('.dol_trunc(strtr($helppage,'_',' '),8).')';
                    $text .= '</a>';
//$toprightmenu.='</div>'."\n";
                    $toprightmenu .= @Form::textwithtooltip('', $title, 2, 1, $text, 'login_block_elem', 2);
                }
            }

// Logout link
            $toprightmenu .= @Form::textwithtooltip('', $logouthtmltext, 2, 1, $logouttext, 'login_block_elem', 2);

            $toprightmenu .= '</div>';

            print $toprightmenu;

            print "</div>\n";  // end div class="login_block"

            print '</div></div>';

            print '<div style="clear: both;"></div>';
            print "<!-- End top horizontal menu -->\n\n";
        }

        if (empty(Globals::$conf->dol_hide_leftmenu) && empty(Globals::$conf->dol_use_jmobile))
            print '<!-- Begin div id-container --><div id="id-container" class="id-container' . ($morecss ? ' ' . $morecss : '') . '">';
    }

    /**
     *  Show left menu bar
     *
     *  @param  array	$menu_array_before 	       	Table of menu entries to show before entries of menu handler. This param is deprectaed and must be provided to ''.
     *  @param  string	$helppagename    	       	Name of wiki page for help ('' by default).
     * 				     		                   	Syntax is: For a wiki page: EN:EnglishPage|FR:FrenchPage|ES:SpanishPage
     * 									         	For other external page: http://server/url
     *  @param  string	$notused             		Deprecated. Used in past to add content into left menu. Hooks can be used now.
     *  @param  array	$menu_array_after           Table of menu entries to show after entries of menu handler
     *  @param  int		$leftmenuwithoutmainarea    Must be set to 1. 0 by default for backward compatibility with old modules.
     *  @param  string	$title                      Title of web page
     *  @param  string  $acceptdelayedhtml          1 if caller request to have html delayed content not returned but saved into global $delayedhtmlcontent (so caller can show it at end of page to avoid flash FOUC effect)
     *  @return	void
     */
    function left_menu($menu_array_before, $helppagename = '', $notused = '', $menu_array_after = '', $leftmenuwithoutmainarea = 0, $title = '', $acceptdelayedhtml = 0)
    {
// global Globals::$user, Globals::$conf, Globals::$langs, $db, $form;
// global Globals::$hookManager, $menumanager;

        $searchform = '';
        $bookmarks = '';

        if (!empty($menu_array_before))
            dol_syslog("Deprecated parameter menu_array_before was used when calling main::left_menu function. Menu entries of module should now be defined into module descriptor and not provided when calling left_menu.", LOG_WARNING);

        if (empty(Globals::$conf->dol_hide_leftmenu) && (!defined('NOREQUIREMENU') || !constant('NOREQUIREMENU'))) {
// Instantiate hooks of thirdparty module
            Globals::$hookManager->initHooks(array('searchform', 'leftblock'));

            print "\n" . '<!-- Begin side-nav id-left -->' . "\n" . '<div class="side-nav"><div id="id-left">' . "\n";

            if (Globals::$conf->browser->layout == 'phone')
                Globals::$conf->global->MAIN_USE_OLD_SEARCH_FORM = 1; // Select into select2 is awfull on smartphone. TODO Is this still true with select2 v4 ?

            print "\n";

            if (!is_object($form)) {
                $form = new Form($db);
            }
            $selected = -1;
            $usedbyinclude = 1;
            include_once DOL_BASE_PATH . '/core/ajax/selectsearchbox.php'; // This set $arrayresult

            if (Globals::$conf->use_javascript_ajax && empty(Globals::$conf->global->MAIN_USE_OLD_SEARCH_FORM)) {
//$searchform.=$form->selectArrayAjax('searchselectcombo', DOL_BASE_URI.'/core/ajax/selectsearchbox.php', $selected, '', '', 0, 1, 'vmenusearchselectcombo', 1, Globals::$langs->trans("Search"), 1);
                $searchform .= $form->selectArrayFilter('searchselectcombo', $arrayresult, $selected, '', 1, 0, (empty(Globals::$conf->global->MAIN_SEARCHBOX_CONTENT_LOADED_BEFORE_KEY) ? 1 : 0), 'vmenusearchselectcombo', 1, Globals::$langs->trans("Search"), 1);
            } else {
                foreach ($arrayresult as $key => $val) {
//$searchform.=printSearchForm($val['url'], $val['url'], $val['label'], 'maxwidth100', 'sall', $val['shortcut'], 'searchleft', img_picto('',$val['img']));
                    $searchform .= printSearchForm($val['url'], $val['url'], $val['label'], 'maxwidth125', 'sall', $val['shortcut'], 'searchleft', img_picto('', $val['img'], '', false, 1, 1));
                }
            }

// Execute hook printSearchForm
            $parameters = array('searchform' => $searchform);

            $reshook = Globals::$hookManager->executeHooks('printSearchForm', $parameters);    // Note that $action and $object may have been modified by some hooks
            if (empty($reshook)) {
                $searchform .= Globals::$hookManager->resPrint;
            } else
                $searchform = Globals::$hookManager->resPrint;

// Force special value for $searchform
            if (!empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) || empty(Globals::$conf->use_javascript_ajax)) {
                $urltosearch = DOL_BASE_URI . '/core/search_page.php?showtitlebefore=1';
                $searchform = '<div class="blockvmenuimpair blockvmenusearchphone"><div id="divsearchforms1"><a href="' . $urltosearch . '" alt="' . dol_escape_htmltag(Globals::$langs->trans("ShowSearchFields")) . '">' . Globals::$langs->trans("Search") . '...</a></div></div>';
            } elseif (Globals::$conf->use_javascript_ajax && !empty(Globals::$conf->global->MAIN_USE_OLD_SEARCH_FORM)) {
                $searchform = '<div class="blockvmenuimpair blockvmenusearchphone"><div id="divsearchforms1"><a href="#" alt="' . dol_escape_htmltag(Globals::$langs->trans("ShowSearchFields")) . '">' . Globals::$langs->trans("Search") . '...</a></div><div id="divsearchforms2" style="display: none">' . $searchform . '</div>';
                $searchform .= '<script type="text/javascript">
            	jQuery(document).ready(function () {
            		jQuery("#divsearchforms1").click(function(){
	                   jQuery("#divsearchforms2").toggle();
	               });
            	});
                </script>' . "\n";
                $searchform .= '</div>';
            }

// Define $bookmarks
            if (!empty(Globals::$conf->bookmark->enabled) && Globals::$user->rights->bookmark->lire) {
                include_once DOL_BASE_PATH . '/bookmarks/bookmarks.lib.php';
                Globals::$langs->load("bookmarks");

                $bookmarks = printBookmarksList($db, Globals::$langs);
            }

// Left column
            print '<!-- Begin left menu -->' . "\n";

            print '<div class="vmenu"' . (empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) ? '' : ' title="Left menu"') . '>' . "\n\n";

// Show left menu with other forms
            Globals::$menuManager->menu_array = $menu_array_before;
            Globals::$menuManager->menu_array_after = $menu_array_after;
            Globals::$menuManager->showmenu('left', array('searchform' => $searchform, 'bookmarks' => $bookmarks)); // output menu_array and menu found in database
// Dolibarr version + help + bug report link
            print "\n";
            print "<!-- Begin Help Block-->\n";
            print '<div id="blockvmenuhelp" class="blockvmenuhelp">' . "\n";

// Version
            if (empty(Globals::$conf->global->MAIN_HIDE_VERSION)) {    // Version is already on help picto and on login page.
                $doliurl = 'https://www.dolibarr.org';
//local communities
                if (preg_match('/fr/i', Globals::$langs->defaultlang))
                    $doliurl = 'https://www.dolibarr.fr';
                if (preg_match('/es/i', Globals::$langs->defaultlang))
                    $doliurl = 'https://www.dolibarr.es';
                if (preg_match('/de/i', Globals::$langs->defaultlang))
                    $doliurl = 'https://www.dolibarr.de';
                if (preg_match('/it/i', Globals::$langs->defaultlang))
                    $doliurl = 'https://www.dolibarr.it';
                if (preg_match('/gr/i', Globals::$langs->defaultlang))
                    $doliurl = 'https://www.dolibarr.gr';

                $appli = constant('DOL_APPLICATION_TITLE');
                if (!empty(Globals::$conf->global->MAIN_APPLICATION_TITLE)) {
                    $appli = Globals::$conf->global->MAIN_APPLICATION_TITLE;
                    $doliurl = '';
                    if (preg_match('/\d\.\d/', $appli)) {
                        if (!preg_match('/' . preg_quote(DOL_VERSION) . '/', $appli))
                            $appli .= " (" . DOL_VERSION . ")"; // If new title contains a version that is different than core
                    } else
                        $appli .= " " . DOL_VERSION;
                } else
                    $appli .= " " . DOL_VERSION;
                print '<div id="blockvmenuhelpapp" class="blockvmenuhelp">';
                if ($doliurl)
                    print '<a class="help" target="_blank" rel="noopener" href="' . $doliurl . '">';
                else
                    print '<span class="help">';
                print $appli;
                if ($doliurl)
                    print '</a>';
                else
                    print '</span>';
                print '</div>' . "\n";
            }

// Link to bugtrack
            if (!empty(Globals::$conf->global->MAIN_BUGTRACK_ENABLELINK)) {
                require_once DOL_BASE_PATH . '/core/lib/functions2.lib.php';

                $bugbaseurl = 'https://github.com/Dolibarr/dolibarr/issues/new';
                $bugbaseurl .= '?title=';
                $bugbaseurl .= urlencode("Bug: ");
                $bugbaseurl .= '&body=';
                $bugbaseurl .= urlencode("# Bug\n");
                $bugbaseurl .= urlencode("\n");
                $bugbaseurl .= urlencode("## Environment\n");
                $bugbaseurl .= urlencode("- **Version**: " . DOL_VERSION . "\n");
                $bugbaseurl .= urlencode("- **OS**: " . php_uname('s') . "\n");
                $bugbaseurl .= urlencode("- **Web server**: " . $_SERVER["SERVER_SOFTWARE"] . "\n");
                $bugbaseurl .= urlencode("- **PHP**: " . php_sapi_name() . ' ' . phpversion() . "\n");
                $bugbaseurl .= urlencode("- **Database**: " . $db::LABEL . ' ' . $db->getVersion() . "\n");
                $bugbaseurl .= urlencode("- **URL**: " . $_SERVER["REQUEST_URI"] . "\n");
                $bugbaseurl .= urlencode("\n");
                $bugbaseurl .= urlencode("## Report\n");
                print '<div id="blockvmenuhelpbugreport" class="blockvmenuhelp">';
                print '<a class="help" target="_blank" rel="noopener" href="' . $bugbaseurl . '">' . Globals::$langs->trans("FindBug") . '</a>';
                print '</div>';
            }

            print "</div>\n";
            print "<!-- End Help Block-->\n";
            print "\n";

            print "</div>\n";
            print "<!-- End left menu -->\n";
            print "\n";

// Execute hook printLeftBlock
            $parameters = array();
            $reshook = Globals::$hookManager->executeHooks('printLeftBlock', $parameters);    // Note that $action and $object may have been modified by some hooks
            print Globals::$hookManager->resPrint;

            print '</div></div> <!-- End side-nav id-left -->'; // End div id="side-nav" div id="id-left"
        }

        print "\n";
        print '<!-- Begin right area -->' . "\n";

        if (empty($leftmenuwithoutmainarea))
            main_area($title);
    }

    /**
     *  Begin main area
     *
     *  @param	string	$title		Title
     *  @return	void
     */
    function main_area($title = '')
    {
// global Globals::$conf, Globals::$langs;

        if (empty(Globals::$conf->dol_hide_leftmenu))
            print '<div id="id-right">';

        print "\n";

        print '<!-- Begin div class="fiche" -->' . "\n" . '<div class="fiche">' . "\n";

        if (!empty(Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED))
            print info_admin(Globals::$langs->trans("WarningYouAreInMaintenanceMode", Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED));
    }

    /**
     *  Return helpbaseurl, helppage and mode
     *
     *  @param	string		$helppagename		Page name ('EN:xxx,ES:eee,FR:fff...' or 'http://localpage')
     *  @param  Translate	Globals::$langs				Language
     *  @return	array		Array of help urls
     */
    function getHelpParamFor($helppagename, $langs)
    {
        $helpbaseurl = '';
        $helppage = '';
        $mode = '';

        if (preg_match('/^http/i', $helppagename)) {
// If complete URL
            $helpbaseurl = '%s';
            $helppage = $helppagename;
            $mode = 'local';
        } else {
// If WIKI URL
            if (preg_match('/^es/i', Globals::$langs->defaultlang)) {
                $helpbaseurl = 'http://wiki.dolibarr.org/index.php/%s';
                if (preg_match('/ES:([^|]+)/i', $helppagename, $reg))
                    $helppage = $reg[1];
            }
            if (preg_match('/^fr/i', Globals::$langs->defaultlang)) {
                $helpbaseurl = 'http://wiki.dolibarr.org/index.php/%s';
                if (preg_match('/FR:([^|]+)/i', $helppagename, $reg))
                    $helppage = $reg[1];
            }
            if (empty($helppage)) { // If help page not already found
                $helpbaseurl = 'http://wiki.dolibarr.org/index.php/%s';
                if (preg_match('/EN:([^|]+)/i', $helppagename, $reg))
                    $helppage = $reg[1];
            }
            $mode = 'wiki';
        }
        return array('helpbaseurl' => $helpbaseurl, 'helppage' => $helppage, 'mode' => $mode);
    }

    /**
     *  Show a search area.
     *  Used when the javascript quick search is not used.
     *
     *  @param  string	$urlaction          Url post
     *  @param  string	$urlobject          Url of the link under the search box
     *  @param  string	$title              Title search area
     *  @param  string	$htmlmorecss        Add more css
     *  @param  string	$htmlinputname      Field Name input form
     *  @param	string	$accesskey			Accesskey
     *  @param  string  $prefhtmlinputname  Complement for id to avoid multiple same id in the page
     *  @param	string	$img				Image to use
     *  @param	string	$showtitlebefore	Show title before input text instead of into placeholder. This can be set when output is dedicated for text browsers.
     *  @return	string
     */
    function printSearchForm($urlaction, $urlobject, $title, $htmlmorecss, $htmlinputname, $accesskey = '', $prefhtmlinputname = '', $img = '', $showtitlebefore = 0)
    {
        // global Globals::$conf, Globals::$langs, Globals::$user;

        $ret = '';
        $ret .= '<form action="' . $urlaction . '" method="post" class="searchform">';
        $ret .= '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
        $ret .= '<input type="hidden" name="mode" value="search">';
        $ret .= '<input type="hidden" name="savelogin" value="' . dol_escape_htmltag(Globals::$user->login) . '">';
        if ($showtitlebefore)
            $ret .= $title . ' ';
        $ret .= '<input type="text" class="flat ' . $htmlmorecss . '"';
        $ret .= ' style="text-indent: 22px; background-image: url(\'' . $img . '\'); background-repeat: no-repeat; background-position: 3px;"';
        $ret .= ($accesskey ? ' accesskey="' . $accesskey . '"' : '');
        $ret .= ' placeholder="' . strip_tags($title) . '"';
        $ret .= ' name="' . $htmlinputname . '" id="' . $prefhtmlinputname . $htmlinputname . '" />';
//$ret.='<input type="submit" class="button" style="padding-top: 4px; padding-bottom: 4px; padding-left: 6px; padding-right: 6px" value="'.Globals::$langs->trans("Go").'">';
        $ret .= '<button type="submit" class="button" style="padding-top: 4px; padding-bottom: 4px; padding-left: 6px; padding-right: 6px">';
        $ret .= '<span class="fa fa-search"></span>';
        $ret .= '</button>';
        $ret .= "</form>\n";
        return $ret;
    }

    /**
     * Show HTML footer
     * Close div /DIV class=fiche + /DIV id-right + /DIV id-container + /BODY + /HTML.
     * If global var $delayedhtmlcontent was filled, we output it just before closing the body.
     *
     * @param	string	$comment    				A text to add as HTML comment into HTML generated page
     * @param	string	$zone						'private' (for private pages) or 'public' (for public pages)
     * @param	int		$disabledoutputofmessages	Clear all messages stored into session without diplaying them
     * @return	void
     */
    function llxFooter($comment = '', $zone = 'private', $disabledoutputofmessages = 0)
    {
        // global Globals::$conf, Globals::$langs, Globals::$user, $object;
        // global $delayedhtmlcontent;
        // global $contextpage, $page, $limit;

        $ext = 'layout=' . Globals::$conf->browser->layout . '&version=' . urlencode(DOL_VERSION);

// Global html output events ($mesgs, $errors, $warnings)
        dol_htmloutput_events($disabledoutputofmessages);

// Code for search criteria persistence.
// Globals::$user->lastsearch_values was set by the GETPOST when form field search_xxx exists
        if (is_object(Globals::$user) && !empty(Globals::$user->lastsearch_values_tmp) && is_array(Globals::$user->lastsearch_values_tmp)) {
// Clean and save data
            foreach (Globals::$user->lastsearch_values_tmp as $key => $val) {
                unset($_SESSION['lastsearch_values_tmp_' . $key]);   // Clean array to rebuild it just after
                if (count($val) && empty($_POST['button_removefilter'])) { // If there is search criteria to save and we did not click on 'Clear filter' button
                    if (empty($val['sortfield']))
                        unset($val['sortfield']);
                    if (empty($val['sortorder']))
                        unset($val['sortorder']);
                    dol_syslog('Save lastsearch_values_tmp_' . $key . '=' . json_encode($val, 0) . " (systematic recording of last search criterias)");
                    $_SESSION['lastsearch_values_tmp_' . $key] = json_encode($val);
                    unset($_SESSION['lastsearch_values_' . $key]);
                }
            }
        }


        $relativepathstring = $_SERVER["PHP_SELF"];
// Clean $relativepathstring
        if (constant('DOL_BASE_URI'))
            $relativepathstring = preg_replace('/^' . preg_quote(constant('DOL_BASE_URI'), '/') . '/', '', $relativepathstring);
        $relativepathstring = preg_replace('/^\//', '', $relativepathstring);
        $relativepathstring = preg_replace('/^custom\//', '', $relativepathstring);
        if (preg_match('/list\.php$/', $relativepathstring)) {
            unset($_SESSION['lastsearch_contextpage_tmp_' . $relativepathstring]);
            unset($_SESSION['lastsearch_page_tmp_' . $relativepathstring]);
            unset($_SESSION['lastsearch_limit_tmp_' . $relativepathstring]);

            if (!empty($contextpage))
                $_SESSION['lastsearch_contextpage_tmp_' . $relativepathstring] = $contextpage;
            if (!empty($page) && $page > 1)
                $_SESSION['lastsearch_page_tmp_' . $relativepathstring] = $page;
            if (!empty($limit) && $limit != Globals::$conf->limit)
                $_SESSION['lastsearch_limit_tmp_' . $relativepathstring] = $limit;

            unset($_SESSION['lastsearch_contextpage_' . $relativepathstring]);
            unset($_SESSION['lastsearch_page_' . $relativepathstring]);
            unset($_SESSION['lastsearch_limit_' . $relativepathstring]);
        }

// Core error message
        if (!empty(Globals::$conf->global->MAIN_CORE_ERROR)) {
// Ajax version
            if (Globals::$conf->use_javascript_ajax) {
                $title = img_warning() . ' ' . Globals::$langs->trans('CoreErrorTitle');
                print ajax_dialog($title, Globals::$langs->trans('CoreErrorMessage'));
            }
// html version
            else {
                $msg = img_warning() . ' ' . Globals::$langs->trans('CoreErrorMessage');
                print '<div class="error">' . $msg . '</div>';
            }

//define("MAIN_CORE_ERROR",0);      // Constant was defined and we can't change value of a constant
        }

        print "\n\n";

        print '</div> <!-- End div class="fiche" -->' . "\n"; // End div fiche

        if (empty(Globals::$conf->dol_hide_leftmenu))
            print '</div> <!-- End div id-right -->' . "\n"; // End div id-right

        if (empty(Globals::$conf->dol_hide_leftmenu) && empty(Globals::$conf->dol_use_jmobile))
            print '</div> <!-- End div id-container -->' . "\n"; // End div container

        print "\n";
        if ($comment)
            print '<!-- ' . $comment . ' -->' . "\n";

        printCommonFooter($zone);

        if (!empty($delayedhtmlcontent))
            print $delayedhtmlcontent;

        if (!empty(Globals::$conf->use_javascript_ajax)) {
            print "\n" . '<!-- Includes JS Footer of Dolibarr -->' . "\n";
            print '<script type="text/javascript" src="' . BASE_URI . '?controller=core/js/&method=lib_foot.js&lang=' . Globals::$langs->defaultlang . ($ext ? '&' . $ext : '') . '"></script>' . "\n";
        }

// Wrapper to add log when clicking on download or preview
        if (!empty(Globals::$conf->blockedlog->enabled) && is_object($object) && $object->id > 0 && $object->statut > 0) {
            if (in_array($object->element, array('facture'))) {       // Restrict for the moment to element 'facture'
                print "\n<!-- JS CODE TO ENABLE log when making a download or a preview of a document -->\n";

                ?>
                <script type="text/javascript">
                    jQuery(document).ready(function () {
                        $('a.documentpreview').click(function () {
                            $.post('<?php echo DOL_BASE_URI . "/blockedlog/ajax/block-add.php" ?>'
                                    , {
                                        id:<?php echo $object->id; ?>
                                        , element: '<?php echo $object->element ?>'
                                        , action: 'DOC_PREVIEW'
                                    }
                            );
                        });
                        $('a.documentdownload').click(function () {
                            $.post('<?php echo DOL_BASE_URI . "/blockedlog/ajax/block-add.php" ?>'
                                    , {
                                        id:<?php echo $object->id; ?>
                                        , element: '<?php echo $object->element ?>'
                                        , action: 'DOC_DOWNLOAD'
                                    }
                            );
                        });
                    });
                </script>
                <?php
            }
        }

// A div for the address popup
        print "\n<!-- A div to allow dialog popup -->\n";
        print '<div id="dialogforpopup" style="display: none;"></div>' . "\n";

        print "</body>\n";

        print "<!-- Alixar debugBar footer -->";
        print Debug::getRenderFooter(); // Includes Alixar debugBar footer

        print "</html>\n";
    }
}
