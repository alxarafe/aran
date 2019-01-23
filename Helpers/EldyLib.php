<?php
/* Copyright (C) 2010-2014 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2010      Regis Houssin        <regis.houssin@inodbox.com>
 * Copyright (C) 2012-2015 Juanjo Menent        <jmenent@2byte.es>
 * Copyright (C) 2013      Cédric Salvador      <csalvador@gpcsolutions.fr>
 * Copyright (C) 2015      Marcos García        <marcosgdf@gmail.com>
 * Copyright (C) 2018      Ferran Marcet        <fmarcet@2byte.es>
 * Copyright (C) 2018       Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2018-2019  Alxarafe                <info@alxarafe.com>
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
 * or see http://www.gnu.org/
 */
namespace Alixar\Helpers;

/**
 *  \file		htdocs/core/menus/standard/eldy.lib.php
 *  \brief		Library for file eldy menus
 */
use Alxarafe\Helpers\Config;
use Alixar\Base\Menubase;
use Alixar\Base\Conf;
use Alixar\Helpers\DolUtils;

class EldyLib
{

    /**
     * Core function to output top menu eldy
     *
     * @param 	DoliDB	Config::$dbEngine				Database handler
     * @param 	string	$atarget		Target (Example: '' or '_top')
     * @param 	int		$type_user     	0=Menu for backoffice, 1=Menu for front office
     * @param  	array	$tabMenu        If array with menu entries already loaded, we put this array here (in most cases, it's empty)
     * @param	Menu	$menu			Object Menu to return back list of menu entries
     * @param	int		$noout			1=Disable output (Initialise &$menu only).
     * @param	string	$mode			'top', 'topnb', 'left', 'jmobile'
     * @return	int						0
     */
    static function print_eldy_menu($dba, $atarget, $type_user, &$tabMenu, &$menu, $noout = 0, $mode = '')
    {
        //global Globals::$user, $conf, $langs, $dolibarr_main_db_name;

        $mainmenu = (empty($_SESSION["mainmenu"]) ? '' : $_SESSION["mainmenu"]);
        $leftmenu = (empty($_SESSION["leftmenu"]) ? '' : $_SESSION["leftmenu"]);

        $id = 'mainmenu';
        $listofmodulesforexternal = explode(',', Globals::$conf->global->MAIN_MODULES_FOR_EXTERNAL ?? '');

        if (empty($noout)) {
            EldyLib::print_start_menu_array();
        }

        $usemenuhider = (DolUtils::GETPOST('testmenuhider', 'int') ||!empty(Globals::$conf->global->MAIN_TESTMENUHIDER));

// Show/Hide vertical menu
        if ($mode != 'jmobile' && $mode != 'topnb' && $usemenuhider && empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
            $showmode = 1;
            $classname = 'class="tmenu menuhider"';
            $idsel = 'menu';

            $menu->add('#', '', 0, $showmode, $atarget, "xxx", '', 0, $id, $idsel, $classname);
        }

// Home
        $showmode = 1;
        $classname = "";
        if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "home") {
            $classname = 'class="tmenusel"';
            $_SESSION['idmenu'] = '';
        } else {
            $classname = 'class="tmenu"';
        }
        $idsel = 'home';

        $titlehome = Globals::$langs->trans("Home");
        if (!empty(Globals::$conf->global->THEME_TOPMENU_DISABLE_IMAGE)) {
            $titlehome = '&nbsp; <span class="fa fa-home"></span> &nbsp;';
        }
//$menu->add('/index.php?mainmenu=home&leftmenu=home', $titlehome, 0, $showmode, $atarget, "home", '', 10, $id, $idsel, $classname);
        $menu->add('?mainmenu=home&leftmenu=home', $titlehome, 0, $showmode, $atarget, "home", '', 10, $id, $idsel, $classname);

// Members
        $tmpentry = array(
            'enabled' => (!empty(Globals::$conf->adherent->enabled)),
            'perms' => (!empty(Globals::$user->rights->adherent->lire)),
            'module' => 'adherent',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "members") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'members';

//$menu->add('/adherents/index.php?mainmenu=members&leftmenu=', Globals::$langs->trans("MenuMembers"), 0, $showmode, $atarget, "members", '', 18, $id, $idsel, $classname);
            $menu->add('?controller=adherents&method=index&mainmenu=members&leftmenu=', Globals::$langs->trans("MenuMembers"), 0, $showmode, $atarget, "members", '', 18, $id, $idsel, $classname);
        }

// Third parties
        $tmpentry = array(
            'enabled' => ((!empty(Globals::$conf->societe->enabled) && (empty(Globals::$conf->global->SOCIETE_DISABLE_PROSPECTS) || empty(Globals::$conf->global->SOCIETE_DISABLE_CUSTOMERS))) ||!empty(Globals::$conf->fournisseur->enabled)),
        'perms' => (!empty(Globals::$user->rights->societe->lire) || !empty(Globals::$user->rights->fournisseur->lire)),
            'module' => 'societe|fournisseur',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
// Load translation files required by the page
            Globals::$langs->loadLangs(array("companies", "suppliers"));

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "companies") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'companies';

//$menu->add('/societe/index.php?mainmenu=companies&leftmenu=', Globals::$langs->trans("ThirdParties"), 0, $showmode, $atarget, "companies", '', 20, $id, $idsel, $classname);
            $menu->add('?controller=societe&method=index&mainmenu=companies&leftmenu=', Globals::$langs->trans("ThirdParties"), 0, $showmode, $atarget, "companies", '', 20, $id, $idsel, $classname);
        }

// Products-Services
        $tmpentry = array(
            'enabled' => (!empty(Globals::$conf->product->enabled) || !empty(Globals::$conf->service->enabled)),
            'perms' => (!empty(Globals::$user->rights->produit->lire) || !empty(Globals::$user->rights->service->lire)),
            'module' => 'product|service',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
            Globals::$langs->load("products");

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "products") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'products';

            $chaine = "";
            if (!empty(Globals::$conf->product->enabled)) {
                $chaine .= Globals::$langs->trans("TMenuProducts");
            }
            if (!empty(Globals::$conf->product->enabled) && !empty(Globals::$conf->service->enabled)) {
                $chaine .= " | ";
            }
            if (!empty(Globals::$conf->service->enabled)) {
                $chaine .= Globals::$langs->trans("TMenuServices");
            }

//$menu->add('/product/index.php?mainmenu=products&leftmenu=', $chaine, 0, $showmode, $atarget, "products", '', 30, $id, $idsel, $classname);
            $menu->add('?controller=product&method=index&mainmenu=products&leftmenu=', $chaine, 0, $showmode, $atarget, "products", '', 30, $id, $idsel, $classname);
        }

// Projects
        $tmpentry = array('enabled' => (!empty(Globals::$conf->projet->enabled)),
            'perms' => (!empty(Globals::$user->rights->projet->lire)),
            'module' => 'projet');
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
            Globals::$langs->load("projects");

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "project") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'project';

            $title = Globals::$langs->trans("LeadsOrProjects"); // Leads and opportunities by default
            $showmodel = $showmodep = $showmode;
            if (empty(Globals::$conf->global->PROJECT_USE_OPPORTUNITIES)) {
                $title = Globals::$langs->trans("Projects");
                $showmodel = 0;
            }
            if (Globals::$conf->global->PROJECT_USE_OPPORTUNITIES == 2) {
                $title = Globals::$langs->trans("Leads");
                $showmodep = 0;
            }

//$menu->add('/projet/index.php?mainmenu=project&leftmenu=', $title, 0, $showmode, $atarget, "project", '', 35, $id, $idsel, $classname);
            $menu->add('?controller=projet&method=index&mainmenu=project&leftmenu=', $title, 0, $showmode, $atarget, "project", '', 35, $id, $idsel, $classname);
//$menu->add('/projet/index.php?mainmenu=project&leftmenu=&search_opp_status=openedopp', Globals::$langs->trans("ListLeads"), 0, $showmodel & Globals::$conf->global->PROJECT_USE_OPPORTUNITIES, $atarget, "project", '', 70, $id, $idsel, $classname);
//$menu->add('/projet/index.php?mainmenu=project&leftmenu=&search_opp_status=notopenedopp', Globals::$langs->trans("ListProjects"), 0, $showmodep, $atarget, "project", '', 70, $id, $idsel, $classname);
        }

// Commercial
        $menuqualified = 0;
        if (!empty(Globals::$conf->propal->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->commande->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->supplier_order->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->supplier_proposal->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->contrat->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->ficheinter->enabled)) {
            $menuqualified++;
        }
        $tmpentry = array(
            'enabled' => $menuqualified,
            'perms' => (!empty(Globals::$user->rights->societe->lire) || !empty(Globals::$user->rights->societe->contact->lire)),
            'module' => 'propal|commande|supplier_order|contrat|ficheinter',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
            Globals::$langs->load("commercial");

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "commercial") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'commercial';

//$menu->add('/comm/index.php?mainmenu=commercial&leftmenu=', Globals::$langs->trans("Commercial"), 0, $showmode, $atarget, "commercial", "", 40, $id, $idsel, $classname);
            $menu->add('?controller=comm&method=index&mainmenu=commercial&leftmenu=', Globals::$langs->trans("Commercial"), 0, $showmode, $atarget, "commercial", "", 40, $id, $idsel, $classname);
        }

// Billing - Financial
        $menuqualified = 0;
        if (!empty(Globals::$conf->facture->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->don->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->tax->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->salaries->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->supplier_invoice->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->loan->enabled)) {
            $menuqualified++;
        }
        $tmpentry = array(
            'enabled' => $menuqualified,
            'perms' => (!empty(Globals::$user->rights->facture->lire) || !empty(Globals::$user->rights->don->lire) || !empty(Globals::$user->rights->tax->charges->lire) || !empty(Globals::$user->rights->salaries->read) || !empty(Globals::$user->rights->fournisseur->facture->lire) || !empty(Globals::$user->rights->loan->read)),
            'module' => 'facture|supplier_invoice|don|tax|salaries|loan',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
            Globals::$langs->load("compta");

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "billing") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'billing';

//$menu->add('/compta/index.php?mainmenu=billing&leftmenu=', Globals::$langs->trans("MenuFinancial"), 0, $showmode, $atarget, "billing", '', 50, $id, $idsel, $classname);
            $menu->add('?controller=compta&method=index&mainmenu=billing&leftmenu=', Globals::$langs->trans("MenuFinancial"), 0, $showmode, $atarget, "billing", '', 50, $id, $idsel, $classname);
        }

// Bank
        $tmpentry = array(
            'enabled' => (!empty(Globals::$conf->banque->enabled) || !empty(Globals::$conf->prelevement->enabled)),
            'perms' => (!empty(Globals::$user->rights->banque->lire) || !empty(Globals::$user->rights->prelevement->lire)),
            'module' => 'banque|prelevement',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
// Load translation files required by the page
            Globals::$langs->loadLangs(array("compta", "banks"));

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "bank") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else
                $classname = 'class="tmenu"';
            $idsel = 'bank';

//$menu->add('/compta/bank/list.php?mainmenu=bank&leftmenu=', Globals::$langs->trans("MenuBankCash"), 0, $showmode, $atarget, "bank", '', 52, $id, $idsel, $classname);
            $menu->add('?controller=compta/bank&method=list&mainmenu=bank&leftmenu=', Globals::$langs->trans("MenuBankCash"), 0, $showmode, $atarget, "bank", '', 52, $id, $idsel, $classname);
        }

// Accounting
        $menuqualified = 0;
        if (!empty(Globals::$conf->comptabilite->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->accounting->enabled)) {
            $menuqualified++;
        }
        if (!empty(Globals::$conf->asset->enabled)) {
            $menuqualified++;
        }
        $tmpentry = array(
            'enabled' => $menuqualified,
            'perms' => (!empty(Globals::$user->rights->compta->resultat->lire) || !empty(Globals::$user->rights->accounting->mouvements->lire) || !empty(Globals::$user->rights->asset->read)),
            'module' => 'comptabilite|accounting',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
            Globals::$langs->load("compta");

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "accountancy") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'accountancy';

//$menu->add('/accountancy/index.php?mainmenu=accountancy&leftmenu=', Globals::$langs->trans("MenuAccountancy"), 0, $showmode, $atarget, "accountancy", '', 54, $id, $idsel, $classname);
            $menu->add('?controller=accountancy&method=index&mainmenu=accountancy&leftmenu=', Globals::$langs->trans("MenuAccountancy"), 0, $showmode, $atarget, "accountancy", '', 54, $id, $idsel, $classname);
        }

// HRM
        $tmpentry = array(
            'enabled' => (!empty(Globals::$conf->hrm->enabled) || !empty(Globals::$conf->holiday->enabled) || !empty(Globals::$conf->deplacement->enabled) || !empty(Globals::$conf->expensereport->enabled)),
            'perms' => (!empty(Globals::$user->rights->hrm->employee->read) || !empty(Globals::$user->rights->holiday->write) || !empty(Globals::$user->rights->deplacement->lire) || !empty(Globals::$user->rights->expensereport->lire)),
            'module' => 'hrm|holiday|deplacement|expensereport',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
            Globals::$langs->load("holiday");

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "hrm") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'hrm';

//$menu->add('/hrm/index.php?mainmenu=hrm&leftmenu=', Globals::$langs->trans("HRM"), 0, $showmode, $atarget, "hrm", '', 80, $id, $idsel, $classname);
            $menu->add('?controller=hrm&method=index&mainmenu=hrm&leftmenu=', Globals::$langs->trans("HRM"), 0, $showmode, $atarget, "hrm", '', 80, $id, $idsel, $classname);
        }

// Tools
        $tmpentry = array(
            'enabled' => 1,
            'perms' => 1,
            'module' => '',
        );
        $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);
        if ($showmode) {
            Globals::$langs->load("other");

            $classname = "";
            if ($_SESSION["mainmenu"] && $_SESSION["mainmenu"] == "tools") {
                $classname = 'class="tmenusel"';
                $_SESSION['idmenu'] = '';
            } else {
                $classname = 'class="tmenu"';
            }
            $idsel = 'tools';

//$menu->add('/core/tools.php?mainmenu=tools&leftmenu=', Globals::$langs->trans("Tools"), 0, $showmode, $atarget, "tools", '', 90, $id, $idsel, $classname);
            $menu->add('?controller=core&method=tools&mainmenu=tools&leftmenu=', Globals::$langs->trans("Tools"), 0, $showmode, $atarget, "tools", '', 90, $id, $idsel, $classname);
        }

// Show personalized menus
        $menuArbo = new Menubase(Config::$dbEngine, 'eldy');
        $newTabMenu = $menuArbo->menuTopCharger('', '', $type_user, 'eldy', $tabMenu); // Return tabMenu with only top entries

        $num = count($newTabMenu);
        for ($i = 0; $i < $num; $i++) {
            $idsel = (empty($newTabMenu[$i]['mainmenu']) ? 'none' : $newTabMenu[$i]['mainmenu']);

            $showmode = DolUtils::isVisibleToUserType($type_user, $newTabMenu[$i], $listofmodulesforexternal);
            if ($showmode == 1) {
                $substitarray = array('__LOGIN__' => Globals::$user->login, '__USER_ID__' => Globals::$user->id, '__USER_SUPERVISOR_ID__' => Globals::$user->fk_user);
                $substitarray['__USERID__'] = Globals::$user->id; // For backward compatibility
                $newTabMenu[$i]['url'] = DolUtils::make_substitutions($newTabMenu[$i]['url'], $substitarray);

// url = url from host, shorturl = relative path into dolibarr sources
                $url = $shorturl = $newTabMenu[$i]['url'];
                if (!preg_match("/^(http:\/\/|https:\/\/)/i", $newTabMenu[$i]['url'])) { // Do not change url content for external links
                    $tmp = explode('?', $newTabMenu[$i]['url'], 2);
                    $url = $shorturl = $tmp[0];
                    $param = (isset($tmp[1]) ? $tmp[1] : '');

                    if (!preg_match('/mainmenu/i', $param) || !preg_match('/leftmenu/i', $param)) {
                        $param .= ($param ? '&' : '') . 'mainmenu=' . $newTabMenu[$i]['mainmenu'] . '&leftmenu=';
                    }
//$url.="idmenu=".$newTabMenu[$i]['rowid'];    // Already done by menuLoad
                    $url = DolUtils::dol_buildpath($url, 1) . ($param ? '?' . $param : '');
//$shorturl = $shorturl.($param?'?'.$param:'');
                    $shorturl = $url;
                    if (BASE_URI) {
                        $shorturl = preg_replace('/^' . preg_quote(BASE_URI, '/') . '/', '', $shorturl);
                    }
                }

// Define the class (top menu selected or not)
                if (!empty($_SESSION['idmenu']) && $newTabMenu[$i]['rowid'] == $_SESSION['idmenu']) {
                    $classname = 'class="tmenusel"';
                } else if (!empty($_SESSION["mainmenu"]) && $newTabMenu[$i]['mainmenu'] == $_SESSION["mainmenu"]) {
                    $classname = 'class="tmenusel"';
                } else {
                    $classname = 'class="tmenu"';
                }
            } else if ($showmode == 2) {
                $classname = 'class="tmenu"';
            }

            $menu->add($shorturl, $newTabMenu[$i]['titre'], 0, $showmode, ($newTabMenu[$i]['target'] ? $newTabMenu[$i]['target'] : $atarget), ($newTabMenu[$i]['mainmenu'] ? $newTabMenu[$i]['mainmenu'] : $newTabMenu[$i]['rowid']), ($newTabMenu[$i]['leftmenu'] ? $newTabMenu[$i]['leftmenu'] : ''), $newTabMenu[$i]['position'], $id, $idsel, $classname);
        }

// Sort on position
        $menu->liste = DolUtils::dol_sort_array($menu->liste, 'position');

// Output menu entries
        if (empty($noout)) {
            foreach ($menu->liste as $menkey => $menuval) {
                self::print_start_menu_entry($menuval['idsel'], $menuval['classname'], $menuval['enabled']);
                self::print_text_menu_entry($menuval['titre'], $menuval['enabled'], (($menuval['url'] != '#' && !preg_match('/^(http:\/\/|https:\/\/)/i', $menuval['url'])) ? BASE_URI : '') . $menuval['url'], $menuval['id'], $menuval['idsel'], $menuval['classname'], ($menuval['target'] ? $menuval['target'] : $atarget));
                self::print_end_menu_entry($menuval['enabled']);
            }
        }

        $showmode = 1;
        if (empty($noout)) {
            self::print_start_menu_entry('', 'class="tmenuend"', $showmode);
            self::print_end_menu_entry($showmode);
            self::print_end_menu_array();
        }

        return 0;
    }

    /**
     * Output start menu array
     *
     * @return	void
     */
    static function print_start_menu_array()
    {
        // global $conf;

        print '<div class="tmenudiv">';
        print '<ul class="tmenu"' . (empty(Globals::$conf->MAIN_OPTIMIZEFORTEXTBROWSER) ? '' : ' title="Top menu"') . '>';
    }

    /**
     * Output start menu entry
     *
     * @param	string	$idsel		Text
     * @param	string	$classname	String to add a css class
     * @param	int		$showmode	0 = hide, 1 = allowed or 2 = not allowed
     * @return	void
     */
    static function print_start_menu_entry($idsel, $classname, $showmode)
    {
        if ($showmode) {
            print '<li ' . $classname . ' id="mainmenutd_' . $idsel . '">';
//print '<div class="tmenuleft tmenusep"></div>';
            print '<div class="tmenucenter">';
        }
    }

    /**
     * Output menu entry
     *
     * @param	string	$text		Text
     * @param	int		$showmode	0 = hide, 1 = allowed or 2 = not allowed
     * @param	string	$url		Url
     * @param	string	$id			Id
     * @param	string	$idsel		Id sel
     * @param	string	$classname	Class name
     * @param	string	$atarget	Target
     * @return	void
     */
    static function print_text_menu_entry($text, $showmode, $url, $id, $idsel, $classname, $atarget)
    {
        // global Globals::$langs;

        if ($showmode == 1) {
            print '<a class="tmenuimage" tabindex="-1" href="' . $url . '"' . ($atarget ? ' target="' . $atarget . '"' : '') . '>';
            print '<div class="' . $id . ' ' . $idsel . ' topmenuimage"><span class="' . $id . ' tmenuimage" id="mainmenuspan_' . $idsel . '"></span></div>';
            print '</a>';
            print '<a ' . $classname . ' id="mainmenua_' . $idsel . '" href="' . $url . '"' . ($atarget ? ' target="' . $atarget . '"' : '') . '>';
            print '<span class="mainmenuaspan">';
            print $text;
            print '</span>';
            print '</a>';
        } elseif ($showmode == 2) {
            print '<div class="' . $id . ' ' . $idsel . ' topmenuimage tmenudisabled"><span class="' . $id . '" id="mainmenuspan_' . $idsel . '"></span></div>';
            print '<a class="tmenudisabled" id="mainmenua_' . $idsel . '" href="#" title="' . dol_escape_htmltag(Globals::$langs->trans("NotAllowed")) . '">';
            print '<span class="mainmenuaspan">';
            print $text;
            print '</span>';
            print '</a>';
        }
    }

    /**
     * Output end menu entry
     *
     * @param	int		$showmode	0 = hide, 1 = allowed or 2 = not allowed
     * @return	void
     */
    static function print_end_menu_entry($showmode)
    {
        if ($showmode) {
            print '</div></li>';
        }
        print "\n";
    }

    /**
     * Output menu array
     *
     * @return	void
     */
    static function print_end_menu_array()
    {
        print '</ul>';
        print '</div>';
        print "\n";
    }

    /**
     * Core function to output left menu eldy
     * Fill &$menu (example with $forcemainmenu='home' $forceleftmenu='all', return left menu tree of Home)
     *
     * @param	DoliDB		Config::$dbEngine                 Database handler
     * @param 	array		$menu_array_before  Table of menu entries to show before entries of menu handler (menu->liste filled with menu->add)
     * @param   array		$menu_array_after   Table of menu entries to show after entries of menu handler (menu->liste filled with menu->add)
     * @param	array		$tabMenu       		If array with menu entries already loaded, we put this array here (in most cases, it's empty)
     * @param	Menu		$menu				Object Menu to return back list of menu entries
     * @param	int			$noout				Disable output (Initialise &$menu only).
     * @param	string		$forcemainmenu		'x'=Force mainmenu to mainmenu='x'
     * @param	string		$forceleftmenu		'all'=Force leftmenu to '' (= all). If value come being '', we change it to value in session and 'none' if not defined in session.
     * @param	array		$moredata			An array with more data to output
     * @return	int								nb of menu entries
     */
    static function print_left_eldy_menu($dba, $menu_array_before, $menu_array_after, &$tabMenu, &$menu, $noout = 0, $forcemainmenu = '', $forceleftmenu = '', $moredata = null)
    {
        // global Globals::$user, $conf, Globals::$langs, $dolibarr_main_db_name, $mysoc;
//var_dump($tabMenu);

        $newmenu = $menu;

        $mainmenu = ($forcemainmenu ? $forcemainmenu : $_SESSION["mainmenu"]);
        $leftmenu = ($forceleftmenu ? '' : (empty($_SESSION["leftmenu"]) ? 'none' : $_SESSION["leftmenu"]));

        $usemenuhider = (DolUtils::GETPOST('testmenuhider', 'int') ||!empty(Globals::$conf->global->MAIN_TESTMENUHIDER));

// Show logo company
        if (empty(Globals::$conf->global->MAIN_MENU_INVERT) && empty($noout) &&!empty(Globals::$conf->global->MAIN_SHOW_LOGO) && empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
            $mysoc->logo_mini = Globals::$conf->global->MAIN_INFO_SOCIETE_LOGO_MINI;
            if (!empty($mysoc->logo_mini) && is_readable(Globals::$conf->mycompany->dir_output . '/logos/thumbs/' . $mysoc->logo_mini)) {
                $urllogo = DOL_BASE_URI . '/viewimage.php?cache=1&modulepart=mycompany&file=' . urlencode('logos/thumbs/' . $mysoc->logo_mini);
            } else {
                $urllogo = DOL_BASE_URI . '/theme/dolibarr_logo.png';
            }
            $title = Globals::$langs->trans("GoIntoSetupToChangeLogo");
            print "\n" . '<!-- Show logo on menu -->' . "\n";
            print '<div class="blockvmenuimpair blockvmenulogo">' . "\n";
            print '<div class="menu_titre" id="menu_titre_logo"></div>';
            print '<div class="menu_top" id="menu_top_logo"></div>';
            print '<div class="menu_contenu" id="menu_contenu_logo">';
            print '<div class="center"><img class="mycompany" title="' . dol_escape_htmltag($title) . '" alt="" src="' . $urllogo . '" style="max-width: 70%"></div>' . "\n";
            print '</div>';
            print '<div class="menu_end" id="menu_end_logo"></div>';
            print '</div>' . "\n";
        }

        if (is_array($moredata) && !empty($moredata['searchform'])) { // searchform can contains select2 code or link to show old search form or link to switch on search page
            print "\n";
            print "<!-- Begin SearchForm -->\n";
            print '<div id="blockvmenusearch" class="blockvmenusearch">' . "\n";
            print $moredata['searchform'];
            print '</div>' . "\n";
            print "<!-- End SearchForm -->\n";
        }

        if (is_array($moredata) && !empty($moredata['bookmarks'])) {
            print "\n";
            print "<!-- Begin Bookmarks -->\n";
            print '<div id="blockvmenubookmarks" class="blockvmenubookmarks">' . "\n";
            print $moredata['bookmarks'];
            print '</div>' . "\n";
            print "<!-- End Bookmarks -->\n";
        }

        /**
         * We update newmenu with entries found into database
         * --------------------------------------------------
         */
        if ($mainmenu) { // If this is empty, loading hard coded menu and loading personalised menu will fail
            /*
             * Menu HOME
             */
            if ($mainmenu == 'home') {
                Globals::$langs->load("users");

// Home - dashboard
//$newmenu->add("/index.php?mainmenu=home&leftmenu=home", Globals::$langs->trans("MyDashboard"), 0, 1, '', $mainmenu, 'home', 0, '', '', '', '<i class="fa fa-bar-chart fa-fw paddingright"></i>');
                $newmenu->add(BASE_URI . "?controller=home&method=home&mainmenu=home&leftmenu=home", Globals::$langs->trans("MyDashboard"), 0, 1, '', $mainmenu, 'home', 0, '', '', '', '<i class="fa fa-bar-chart fa-fw paddingright"></i>');

// Setup
//$newmenu->add("/admin/index.php?mainmenu=home&leftmenu=setup", Globals::$langs->trans("Setup"), 0, Globals::$user->admin, '', $mainmenu, 'setup', 0, '', '', '', '<i class="fa fa-wrench fa-fw paddingright"></i>');
                $newmenu->add(BASE_URI . "?controller=admin&method=index&mainmenu=home&leftmenu=setup", Globals::$langs->trans("Setup"), 0, Globals::$user->admin, '', $mainmenu, 'setup', 0, '', '', '', '<i class="fa fa-wrench fa-fw paddingright"></i>');

                if ($usemenuhider || empty($leftmenu) || $leftmenu == "setup") {
// Load translation files required by the page
                    Globals::$langs->loadLangs(array("admin", "help"));

                    $warnpicto = '';
                    if (empty(Globals::$conf->global->MAIN_INFO_SOCIETE_NOM) || empty(Globals::$conf->global->MAIN_INFO_SOCIETE_COUNTRY)) {
                        Globals::$langs->load("errors");
                        $warnpicto = ' ' . img_warning(Globals::$langs->trans("WarningMandatorySetupNotComplete"));
                    }
//$newmenu->add("/admin/company.php?mainmenu=home", Globals::$langs->trans("MenuCompanySetup") . $warnpicto, 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=company&mainmenu=home", Globals::$langs->trans("MenuCompanySetup") . $warnpicto, 1);
                    $warnpicto = '';
                    if (count(Globals::$conf->modules) <= (empty(Globals::$conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING) ? 1 : Globals::$conf->global->MAIN_MIN_NB_ENABLED_MODULE_FOR_WARNING)) { // If only user module enabled
                        Globals::$langs->load("errors");
                        $warnpicto = ' ' . img_warning(Globals::$langs->trans("WarningMandatorySetupNotComplete"));
                    }
                    /*
                      $newmenu->add("/admin/modules.php?mainmenu=home", Globals::$langs->trans("Modules") . $warnpicto, 1);
                      $newmenu->add("/admin/menus.php?mainmenu=home", Globals::$langs->trans("Menus"), 1);
                      $newmenu->add("/admin/ihm.php?mainmenu=home", Globals::$langs->trans("GUISetup"), 1);

                      $newmenu->add("/admin/translation.php?mainmenu=home", Globals::$langs->trans("Translation"), 1);
                      $newmenu->add("/admin/defaultvalues.php?mainmenu=home", Globals::$langs->trans("DefaultValues"), 1);
                      $newmenu->add("/admin/boxes.php?mainmenu=home", Globals::$langs->trans("Boxes"), 1);
                      $newmenu->add("/admin/delais.php?mainmenu=home", Globals::$langs->trans("MenuWarnings"), 1);
                      $newmenu->add("/admin/security_other.php?mainmenu=home", Globals::$langs->trans("Security"), 1);
                      $newmenu->add("/admin/limits.php?mainmenu=home", Globals::$langs->trans("MenuLimits"), 1);
                      $newmenu->add("/admin/pdf.php?mainmenu=home", Globals::$langs->trans("PDF"), 1);
                      $newmenu->add("/admin/mails.php?mainmenu=home", Globals::$langs->trans("Emails"), 1);
                      $newmenu->add("/admin/sms.php?mainmenu=home", Globals::$langs->trans("SMS"), 1);
                      $newmenu->add("/admin/dict.php?mainmenu=home", Globals::$langs->trans("Dictionary"), 1);
                      $newmenu->add("/admin/const.php?mainmenu=home", Globals::$langs->trans("OtherSetup"), 1);
                     */
                    $newmenu->add(BASE_URI . "?controller=admin&method=modules&mainmenu=home", Globals::$langs->trans("Modules") . $warnpicto, 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=menus&mainmenu=home", Globals::$langs->trans("Menus"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=ihm&mainmenu=home", Globals::$langs->trans("GUISetup"), 1);

                    $newmenu->add(BASE_URI . "?controller=admin&method=translation&mainmenu=home", Globals::$langs->trans("Translation"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=defaultvalues&mainmenu=home", Globals::$langs->trans("DefaultValues"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=boxes&mainmenu=home", Globals::$langs->trans("Boxes"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=delais&mainmenu=home", Globals::$langs->trans("MenuWarnings"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=security_other&mainmenu=home", Globals::$langs->trans("Security"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=limits&mainmenu=home", Globals::$langs->trans("MenuLimits"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=pdf&mainmenu=home", Globals::$langs->trans("PDF"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=mails&mainmenu=home", Globals::$langs->trans("Emails"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=sms&mainmenu=home", Globals::$langs->trans("SMS"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=dict&mainmenu=home", Globals::$langs->trans("Dictionary"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin&method=const&mainmenu=home", Globals::$langs->trans("OtherSetup"), 1);
                }

// System tools
//$newmenu->add("/admin/tools/index.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("AdminTools"), 0, Globals::$user->admin, '', $mainmenu, 'admintools', 0, '', '', '', '<i class="fa fa-server fa-fw paddingright"></i>');
                $newmenu->add(BASE_URI . "?controller=admin/tools&method=index&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("AdminTools"), 0, Globals::$user->admin, '', $mainmenu, 'admintools', 0, '', '', '', '<i class="fa fa-server fa-fw paddingright"></i>');

                if ($usemenuhider || empty($leftmenu) || preg_match('/^admintools/', $leftmenu)) {
// Load translation files required by the page
                    Globals::$langs->loadLangs(array('admin', 'help'));

//$newmenu->add('/admin/system/dolibarr.php?mainmenu=home&leftmenu=admintools_info', Globals::$langs->trans('InfoDolibarr'), 1);
                    $newmenu->add(BASE_URI . "?controller=admin/system&method=dolibarr&mainmenu=home&leftmenu=admintools_info", Globals::$langs->trans('InfoDolibarr'), 1);
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == 'admintools_info') {
                        /*
                          $newmenu->add('/admin/system/modules.php?mainmenu=home&leftmenu=admintools_info', Globals::$langs->trans('Modules'), 2);
                          $newmenu->add('/admin/triggers.php?mainmenu=home&leftmenu=admintools_info', Globals::$langs->trans('Triggers'), 2);
                          $newmenu->add('/admin/system/filecheck.php?mainmenu=home&leftmenu=admintools_info', Globals::$langs->trans('FileCheck'), 2);
                         */
                        $newmenu->add(BASE_URI . '?controller=admin/system&method=modules&mainmenu=home&leftmenu=admintools_info', Globals::$langs->trans('Modules'), 2);
                        $newmenu->add(BASE_URI . '?controller=admin&method=triggers&mainmenu=home&leftmenu=admintools_info', Globals::$langs->trans('Triggers'), 2);
                        $newmenu->add(BASE_URI . '?controller=admin/system&method=filecheck&mainmenu=home&leftmenu=admintools_info', Globals::$langs->trans('FileCheck'), 2);
                    }
                    /*
                      $newmenu->add('/admin/system/browser.php?mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoBrowser'), 1);
                      $newmenu->add('/admin/system/os.php?mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoOS'), 1);
                      $newmenu->add('/admin/system/web.php?mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoWebServer'), 1);
                      $newmenu->add('/admin/system/phpinfo.php?mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoPHP'), 1);
                      //if (function_exists('xdebug_is_enabled')) $newmenu->add('/admin/system/xdebug.php', Globals::$langs->trans('XDebug'),1);
                      $newmenu->add('/admin/system/database.php?mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoDatabase'), 1);
                      if (function_exists('eaccelerator_info'))
                      $newmenu->add("/admin/tools/eaccelerator.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("EAccelerator"), 1);
                      //$newmenu->add("/admin/system/perf.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("InfoPerf"),1);
                      $newmenu->add("/admin/tools/dolibarr_export.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Backup"), 1);
                      $newmenu->add("/admin/tools/dolibarr_import.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Restore"), 1);
                      $newmenu->add("/admin/tools/update.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("MenuUpgrade"), 1);
                      $newmenu->add("/admin/tools/purge.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Purge"), 1);
                      $newmenu->add("/admin/tools/listevents.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Audit"), 1);
                      $newmenu->add("/admin/tools/listsessions.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Sessions"), 1);
                      $newmenu->add('/admin/system/about.php?mainmenu=home&leftmenu=admintools', Globals::$langs->trans('ExternalResources'), 1);
                     */
                    $newmenu->add(BASE_URI . '?controller=admin/system&method=browser&mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoBrowser'), 1);
                    $newmenu->add(BASE_URI . '?controller=admin/system&method=os&mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoOS'), 1);
                    $newmenu->add(BASE_URI . '?controller=admin/system&method=web&mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoWebServer'), 1);
                    $newmenu->add(BASE_URI . '?controller=admin/system&method=phpinfo&mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoPHP'), 1);
//if (function_exists('xdebug_is_enabled')) $newmenu->add('/admin/system/xdebug.php', Globals::$langs->trans('XDebug'),1);
                    $newmenu->add(BASE_URI . '?controller=admin/system&method=database&mainmenu=home&leftmenu=admintools', Globals::$langs->trans('InfoDatabase'), 1);
                    if (function_exists('eaccelerator_info')) {
                        $newmenu->add(BASE_URI . "?controller=admin/tools&method=eaccelerator&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("EAccelerator"), 1);
                    }
//$newmenu->add("/admin/system/perf.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("InfoPerf"),1);
                    $newmenu->add(BASE_URI . "?controller=admin/tools&method=dolibarr_export&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Backup"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin/tools&method=dolibarr_import&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Restore"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin/tools&method=update&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("MenuUpgrade"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin/tools&method=purge&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Purge"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin/tools&method=listevents&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Audit"), 1);
                    $newmenu->add(BASE_URI . "?controller=admin/tools&method=listsessions&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("Sessions"), 1);
                    $newmenu->add(BASE_URI . '?controller=admin/system&method=about&mainmenu=home&leftmenu=admintools', Globals::$langs->trans('ExternalResources'), 1);

                    if (!empty(Globals::$conf->product->enabled) || !empty(Globals::$conf->service->enabled)) {
                        Globals::$langs->load("products");
//$newmenu->add("/product/admin/product_tools.php?mainmenu=home&leftmenu=admintools", Globals::$langs->trans("ProductVatMassChange"), 1, Globals::$user->admin);
                        $newmenu->add(BASE_URI . "?controller=product/admin&method=product_tools&mainmenu=home&leftmenu=admintools", Globals::$langs->trans("ProductVatMassChange"), 1, Globals::$user->admin);
                    }
                }

//$newmenu->add("/user/home.php?leftmenu=users", Globals::$langs->trans("MenuUsersAndGroups"), 0, Globals::$user->rights->user->user->lire, '', $mainmenu, 'users', 0, '', '', '', '<i class="fa fa-users fa-fw paddingright"></i>');
                $newmenu->add(BASE_URI . "?controller=user&method=home&leftmenu=users", Globals::$langs->trans("MenuUsersAndGroups"), 0, Globals::$user->rights->user->user->lire, '', $mainmenu, 'users', 0, '', '', '', '<i class="fa fa-users fa-fw paddingright"></i>');
                if (Globals::$user->rights->user->user->lire) {
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "users") {
                        $newmenu->add("", Globals::$langs->trans("Users"), 1, Globals::$user->rights->user->user->lire || Globals::$user->admin);
                        /*
                          $newmenu->add("/user/card.php?leftmenu=users&action=create", Globals::$langs->trans("NewUser"), 2, (Globals::$user->rights->user->user->creer || Globals::$user->admin) && !(!empty(Globals::$conf->multicompany->enabled) && Globals::$conf->entity > 1 && Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE), '', 'home');
                          $newmenu->add("/user/list.php?leftmenu=users", Globals::$langs->trans("ListOfUsers"), 2, Globals::$user->rights->user->user->lire || Globals::$user->admin);
                          $newmenu->add("/user/hierarchy.php?leftmenu=users", Globals::$langs->trans("HierarchicView"), 2, Globals::$user->rights->user->user->lire || Globals::$user->admin);
                          if (!empty(Globals::$conf->categorie->enabled)) {
                          Globals::$langs->load("categories");
                          $newmenu->add("/categories/index.php?leftmenu=users&type=7", Globals::$langs->trans("UsersCategoriesShort"), 2, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                          }
                          $newmenu->add("", Globals::$langs->trans("Groups"), 1, (Globals::$user->rights->user->user->lire || Globals::$user->admin) && !(!empty(Globals::$conf->multicompany->enabled) && Globals::$conf->entity > 1 && Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE));
                          $newmenu->add("/user/group/card.php?leftmenu=users&action=create", Globals::$langs->trans("NewGroup"), 2, ((Globals::$conf->global->MAIN_USE_ADVANCED_PERMS ? Globals::$user->rights->user->group_advance->write : Globals::$user->rights->user->user->creer) || Globals::$user->admin) && !(!empty(Globals::$conf->multicompany->enabled) && Globals::$conf->entity > 1 && Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE));
                          $newmenu->add("/user/group/list.php?leftmenu=users", Globals::$langs->trans("ListOfGroups"), 2, ((Globals::$conf->global->MAIN_USE_ADVANCED_PERMS ? Globals::$user->rights->user->group_advance->read : Globals::$user->rights->user->user->lire) || Globals::$user->admin) && !(!empty(Globals::$conf->multicompany->enabled) && Globals::$conf->entity > 1 && Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE));
                         */
                        $newmenu->add(BASE_URI . "?controller=user&method=card&leftmenu=users&action=create", Globals::$langs->trans("NewUser"), 2, (Globals::$user->rights->user->user->creer || Globals::$user->admin) &&!(!empty(Globals::$conf->multicompany->enabled) && Globals::$conf->entity > 1 && Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE), '', 'home');
                        $newmenu->add(BASE_URI . "?controller=user&method=list&leftmenu=users", Globals::$langs->trans("ListOfUsers"), 2, Globals::$user->rights->user->user->lire || Globals::$user->admin);
                        $newmenu->add(BASE_URI . "?controller=user&method=hierarchy&leftmenu=users", Globals::$langs->trans("HierarchicView"), 2, Globals::$user->rights->user->user->lire || Globals::$user->admin);
                        if (!empty(Globals::$conf->categorie->enabled)) {
                            Globals::$langs->load("categories");
                            $newmenu->add(BASE_URI . "?controller=categories&method=index&leftmenu=users&type=7", Globals::$langs->trans("UsersCategoriesShort"), 2, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                        }
                        $newmenu->add("", Globals::$langs->trans("Groups"), 1, (Globals::$user->rights->user->user->lire || Globals::$user->admin) &&!(!empty(Globals::$conf->multicompany->enabled) && Globals::$conf->entity > 1 && Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE));
                        $newmenu->add(BASE_URI . "?controller=user/group&method=card&leftmenu=users&action=create", Globals::$langs->trans("NewGroup"), 2, ((Globals::$conf->global->MAIN_USE_ADVANCED_PERMS ? Globals::$user->rights->user->group_advance->write : Globals::$user->rights->user->user->creer) || Globals::$user->admin) &&!(!empty(Globals::$conf->multicompany->enabled) && Globals::$conf->entity > 1 && Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE));
                        $newmenu->add(BASE_URI . "?controller=user/group&method=list&leftmenu=users", Globals::$langs->trans("ListOfGroups"), 2, ((Globals::$conf->global->MAIN_USE_ADVANCED_PERMS ? Globals::$user->rights->user->group_advance->read : Globals::$user->rights->user->user->lire) || Globals::$user->admin) &&!(!empty(Globals::$conf->multicompany->enabled) && Globals::$conf->entity > 1 && Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE));
                    }
                }
            }

            /*
             * Menu THIRDPARTIES
             */
            if ($mainmenu == 'companies') {
// Societes
                if (!empty(Globals::$conf->societe->enabled)) {
                    Globals::$langs->load("companies");
//$newmenu->add("/societe/index.php?leftmenu=thirdparties", Globals::$langs->trans("ThirdParty"), 0, Globals::$user->rights->societe->lire, '', $mainmenu, 'thirdparties');
                    $newmenu->add(BASE_URI . "?controller=societe&method=index&leftmenu=thirdparties", Globals::$langs->trans("ThirdParty"), 0, Globals::$user->rights->societe->lire, '', $mainmenu, 'thirdparties');

                    if (Globals::$user->rights->societe->creer) {
//$newmenu->add("/societe/card.php?action=create", Globals::$langs->trans("MenuNewThirdParty"), 1);
                        $newmenu->add(BASE_URI . "?controller=societe&method=card&action=create", Globals::$langs->trans("MenuNewThirdParty"), 1);
                        if (!Globals::$conf->use_javascript_ajax) {
//$newmenu->add("/societe/card.php?action=create&private=1", Globals::$langs->trans("MenuNewPrivateIndividual"), 1);
                            $newmenu->add(BASE_URI . "?controller=societe&method=card&action=create&private=1", Globals::$langs->trans("MenuNewPrivateIndividual"), 1);
                        }
                    }
                }

//$newmenu->add("/societe/list.php?leftmenu=thirdparties", Globals::$langs->trans("List"), 1);
                $newmenu->add(BASE_URI . "?controller=societe&method=list&leftmenu=thirdparties", Globals::$langs->trans("List"), 1);

// Prospects
                if (!empty(Globals::$conf->societe->enabled) && empty(Globals::$conf->global->SOCIETE_DISABLE_PROSPECTS)) {
                    Globals::$langs->load("commercial");
//$newmenu->add("/societe/list.php?type=p&leftmenu=prospects", Globals::$langs->trans("ListProspectsShort"), 1, Globals::$user->rights->societe->lire, '', $mainmenu, 'prospects');
                    $newmenu->add(BASE_URI . "?controller=societe&method=list&type=p&leftmenu=prospects", Globals::$langs->trans("ListProspectsShort"), 1, Globals::$user->rights->societe->lire, '', $mainmenu, 'prospects');
                    /* no more required, there is a filter that can do more
                      if ($usemenuhider || empty($leftmenu) || $leftmenu=="prospects") $newmenu->add("/societe/list.php?type=p&sortfield=s.datec&sortorder=desc&begin=&search_stcomm=-1", Globals::$langs->trans("LastProspectDoNotContact"), 2, Globals::$user->rights->societe->lire);
                      if ($usemenuhider || empty($leftmenu) || $leftmenu=="prospects") $newmenu->add("/societe/list.php?type=p&sortfield=s.datec&sortorder=desc&begin=&search_stcomm=0", Globals::$langs->trans("LastProspectNeverContacted"), 2, Globals::$user->rights->societe->lire);
                      if ($usemenuhider || empty($leftmenu) || $leftmenu=="prospects") $newmenu->add("/societe/list.php?type=p&sortfield=s.datec&sortorder=desc&begin=&search_stcomm=1", Globals::$langs->trans("LastProspectToContact"), 2, Globals::$user->rights->societe->lire);
                      if ($usemenuhider || empty($leftmenu) || $leftmenu=="prospects") $newmenu->add("/societe/list.php?type=p&sortfield=s.datec&sortorder=desc&begin=&search_stcomm=2", Globals::$langs->trans("LastProspectContactInProcess"), 2, Globals::$user->rights->societe->lire);
                      if ($usemenuhider || empty($leftmenu) || $leftmenu=="prospects") $newmenu->add("/societe/list.php?type=p&sortfield=s.datec&sortorder=desc&begin=&search_stcomm=3", Globals::$langs->trans("LastProspectContactDone"), 2, Globals::$user->rights->societe->lire);
                     */
//$newmenu->add("/societe/card.php?leftmenu=prospects&action=create&type=p", Globals::$langs->trans("MenuNewProspect"), 2, Globals::$user->rights->societe->creer);
                    $newmenu->add(BASE_URI . "?controller=societe&method=card&leftmenu=prospects&action=create&type=p", Globals::$langs->trans("MenuNewProspect"), 2, Globals::$user->rights->societe->creer);
//$newmenu->add("/contact/list.php?leftmenu=customers&type=p", Globals::$langs->trans("Contacts"), 2, Globals::$user->rights->societe->contact->lire);
                }

// Customers/Prospects
                if (!empty(Globals::$conf->societe->enabled) && empty(Globals::$conf->global->SOCIETE_DISABLE_CUSTOMERS)) {
                    Globals::$langs->load("commercial");
//$newmenu->add("/societe/list.php?type=c&leftmenu=customers", Globals::$langs->trans("ListCustomersShort"), 1, Globals::$user->rights->societe->lire, '', $mainmenu, 'customers');
                    $newmenu->add(BASE_URI . "?controller=societe&method=list&type=c&leftmenu=customers", Globals::$langs->trans("ListCustomersShort"), 1, Globals::$user->rights->societe->lire, '', $mainmenu, 'customers');

//$newmenu->add("/societe/card.php?leftmenu=customers&action=create&type=c", Globals::$langs->trans("MenuNewCustomer"), 2, Globals::$user->rights->societe->creer);
                    $newmenu->add(BASE_URI . "?controller=societe&method=card&leftmenu=customers&action=create&type=c", Globals::$langs->trans("MenuNewCustomer"), 2, Globals::$user->rights->societe->creer);
//$newmenu->add("/contact/list.php?leftmenu=customers&type=c", Globals::$langs->trans("Contacts"), 2, Globals::$user->rights->societe->contact->lire);
                }

// Suppliers
                if (!empty(Globals::$conf->societe->enabled) && (!empty(Globals::$conf->fournisseur->enabled) || !empty(Globals::$conf->supplier_proposal->enabled))) {
                    Globals::$langs->load("suppliers");
//$newmenu->add("/societe/list.php?type=f&leftmenu=suppliers", Globals::$langs->trans("ListSuppliersShort"), 1, (Globals::$user->rights->fournisseur->lire || Globals::$user->rights->supplier_proposal->lire), '', $mainmenu, 'suppliers');
//$newmenu->add("/societe/card.php?leftmenu=suppliers&action=create&type=f", Globals::$langs->trans("MenuNewSupplier"), 2, Globals::$user->rights->societe->creer && (Globals::$user->rights->fournisseur->lire || Globals::$user->rights->supplier_proposal->lire));
                    $newmenu->add(BASE_URI . "?controller=societe&method=list&type=f&leftmenu=suppliers", Globals::$langs->trans("ListSuppliersShort"), 1, (Globals::$user->rights->fournisseur->lire || Globals::$user->rights->supplier_proposal->lire), '', $mainmenu, 'suppliers');
                    $newmenu->add(BASE_URI . "?controller=societe&method=card&leftmenu=suppliers&action=create&type=f", Globals::$langs->trans("MenuNewSupplier"), 2, Globals::$user->rights->societe->creer && (Globals::$user->rights->fournisseur->lire || Globals::$user->rights->supplier_proposal->lire));
                }

// Categories
                if (!empty(Globals::$conf->categorie->enabled)) {
                    Globals::$langs->load("categories");
                    if (empty(Globals::$conf->global->SOCIETE_DISABLE_PROSPECTS) || empty(Globals::$conf->global->SOCIETE_DISABLE_CUSTOMERS)) {
// Categories prospects/customers
                        $menutoshow = Globals::$langs->trans("CustomersProspectsCategoriesShort");
                        if (!empty(Globals::$conf->global->SOCIETE_DISABLE_PROSPECTS)) {
                            $menutoshow = Globals::$langs->trans("CustomersCategoriesShort");
                        }
                        if (!empty(Globals::$conf->global->SOCIETE_DISABLE_CUSTOMERS)) {
                            $menutoshow = Globals::$langs->trans("ProspectsCategoriesShort");
                        }
//$newmenu->add("/categories/index.php?leftmenu=cat&type=2", $menutoshow, 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                        $newmenu->add(BASE_URI . "?controller=categories&method=index&leftmenu=cat&type=2", $menutoshow, 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                    }
// Categories suppliers
                    if (!empty(Globals::$conf->fournisseur->enabled)) {
//$newmenu->add("/categories/index.php?leftmenu=catfournish&type=1", Globals::$langs->trans("SuppliersCategoriesShort"), 1, Globals::$user->rights->categorie->lire);
                        $newmenu->add(BASE_URI . "?controller=categories&method=index&leftmenu=catfournish&type=1", Globals::$langs->trans("SuppliersCategoriesShort"), 1, Globals::$user->rights->categorie->lire);
                    }
                }

// Contacts
//$newmenu->add("/societe/index.php?leftmenu=thirdparties", (!empty(Globals::$conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? Globals::$langs->trans("Contacts") : Globals::$langs->trans("ContactsAddresses")), 0, Globals::$user->rights->societe->contact->lire, '', $mainmenu, 'contacts');
//$newmenu->add("/contact/card.php?leftmenu=contacts&action=create", (!empty(Globals::$conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? Globals::$langs->trans("NewContact") : Globals::$langs->trans("NewContactAddress")), 1, Globals::$user->rights->societe->contact->creer);
//$newmenu->add("/contact/list.php?leftmenu=contacts", Globals::$langs->trans("List"), 1, Globals::$user->rights->societe->contact->lire);
                $newmenu->add(BASE_URI . "?controller=societe&method=index&leftmenu=thirdparties", (!empty(Globals::$conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? Globals::$langs->trans("Contacts") : Globals::$langs->trans("ContactsAddresses")), 0, Globals::$user->rights->societe->contact->lire, '', $mainmenu, 'contacts');
                $newmenu->add(BASE_URI . "?controller=contact&method=card&leftmenu=contacts&action=create", (!empty(Globals::$conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? Globals::$langs->trans("NewContact") : Globals::$langs->trans("NewContactAddress")), 1, Globals::$user->rights->societe->contact->creer);
                $newmenu->add(BASE_URI . "?controller=contact&method=list&leftmenu=contacts", Globals::$langs->trans("List"), 1, Globals::$user->rights->societe->contact->lire);
                if (empty(Globals::$conf->global->SOCIETE_DISABLE_PROSPECTS)) {
//$newmenu->add("/contact/list.php?leftmenu=contacts&type=p", Globals::$langs->trans("Prospects"), 2, Globals::$user->rights->societe->contact->lire);
                    $newmenu->add(BASE_URI . "?controller=contact&method=list&leftmenu=contacts&type=p", Globals::$langs->trans("Prospects"), 2, Globals::$user->rights->societe->contact->lire);
                }
                if (empty(Globals::$conf->global->SOCIETE_DISABLE_CUSTOMERS)) {
//$newmenu->add("/contact/list.php?leftmenu=contacts&type=c", Globals::$langs->trans("Customers"), 2, Globals::$user->rights->societe->contact->lire);
                    $newmenu->add(BASE_URI . "?controller=contact&method=list&leftmenu=contacts&type=c", Globals::$langs->trans("Customers"), 2, Globals::$user->rights->societe->contact->lire);
                }
                if (!empty(Globals::$conf->fournisseur->enabled)) {
//$newmenu->add("/contact/list.php?leftmenu=contacts&type=f", Globals::$langs->trans("Suppliers"), 2, Globals::$user->rights->societe->contact->lire);
                    $newmenu->add(BASE_URI . "?controller=contact&method=list&leftmenu=contacts&type=f", Globals::$langs->trans("Suppliers"), 2, Globals::$user->rights->societe->contact->lire);
                }
//$newmenu->add("/contact/list.php?leftmenu=contacts&type=o", Globals::$langs->trans("ContactOthers"), 2, Globals::$user->rights->societe->contact->lire);
                $newmenu->add(BASE_URI . "?controller=contact&methos=list&leftmenu=contacts&type=o", Globals::$langs->trans("ContactOthers"), 2, Globals::$user->rights->societe->contact->lire);
//$newmenu->add("/contact/list.php?userid=Globals::$user->id", Globals::$langs->trans("MyContacts"), 1, Globals::$user->rights->societe->contact->lire);
// Categories
                if (!empty(Globals::$conf->categorie->enabled)) {
                    Globals::$langs->load("categories");
// Categories Contact
//$newmenu->add("/categories/index.php?leftmenu=catcontact&type=4", Globals::$langs->trans("ContactCategoriesShort"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                    $newmenu->add(BASE_URI . "?controller=categories&method=index&leftmenu=catcontact&type=4", Globals::$langs->trans("ContactCategoriesShort"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                }
            }

            /*
             * Menu COMMERCIAL
             */
            if ($mainmenu == 'commercial') {
                Globals::$langs->load("companies");

// Customer proposal
                if (!empty(Globals::$conf->propal->enabled)) {
                    Globals::$langs->load("propal");
//$newmenu->add("/comm/propal/index.php?leftmenu=propals", Globals::$langs->trans("Proposals"), 0, Globals::$user->rights->propale->lire, '', $mainmenu, 'propals', 100);
//$newmenu->add("/comm/propal/card.php?action=create&leftmenu=propals", Globals::$langs->trans("NewPropal"), 1, Globals::$user->rights->propale->creer);
//$newmenu->add("/comm/propal/list.php?leftmenu=propals", Globals::$langs->trans("List"), 1, Globals::$user->rights->propale->lire);
                    $newmenu->add(BASE_URI . "?controller=comm/propal&method=index&leftmenu=propals", Globals::$langs->trans("Proposals"), 0, Globals::$user->rights->propale->lire, '', $mainmenu, 'propals', 100);
                    $newmenu->add(BASE_URI . "?controller=comm/propal&method=card&action=create&leftmenu=propals", Globals::$langs->trans("NewPropal"), 1, Globals::$user->rights->propale->creer);
                    $newmenu->add(BASE_URI . "?controller=comm/propal&method=list&leftmenu=propals", Globals::$langs->trans("List"), 1, Globals::$user->rights->propale->lire);
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "propals") {
                        /*
                          $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=0", Globals::$langs->trans("PropalsDraft"), 2, Globals::$user->rights->propale->lire);
                          $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=1", Globals::$langs->trans("PropalsOpened"), 2, Globals::$user->rights->propale->lire);
                          $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=2", Globals::$langs->trans("PropalStatusSigned"), 2, Globals::$user->rights->propale->lire);
                          $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=3", Globals::$langs->trans("PropalStatusNotSigned"), 2, Globals::$user->rights->propale->lire);
                          $newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=4", Globals::$langs->trans("PropalStatusBilled"), 2, Globals::$user->rights->propale->lire);
                         */
                        $newmenu->add(BASE_URI . "?controller=comm/propal&method=list&leftmenu=propals&viewstatut=0", Globals::$langs->trans("PropalsDraft"), 2, Globals::$user->rights->propale->lire);
                        $newmenu->add(BASE_URI . "?controller=comm/propal&method=list&leftmenu=propals&viewstatut=1", Globals::$langs->trans("PropalsOpened"), 2, Globals::$user->rights->propale->lire);
                        $newmenu->add(BASE_URI . "?controller=comm/propal&method=list&leftmenu=propals&viewstatut=2", Globals::$langs->trans("PropalStatusSigned"), 2, Globals::$user->rights->propale->lire);
                        $newmenu->add(BASE_URI . "?controller=comm/propal&method=list&leftmenu=propals&viewstatut=3", Globals::$langs->trans("PropalStatusNotSigned"), 2, Globals::$user->rights->propale->lire);
                        $newmenu->add(BASE_URI . "?controller=comm/propal&method=list&leftmenu=propals&viewstatut=4", Globals::$langs->trans("PropalStatusBilled"), 2, Globals::$user->rights->propale->lire);
//$newmenu->add("/comm/propal/list.php?leftmenu=propals&viewstatut=2,3,4", Globals::$langs->trans("PropalStatusClosedShort"), 2, Globals::$user->rights->propale->lire);
                    }
//$newmenu->add("/comm/propal/stats/index.php?leftmenu=propals", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->propale->lire);
                    $newmenu->add(BASE_URI . "?controller=comm/propal/stats&method=index&leftmenu=propals", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->propale->lire);
                }

// Customers orders
                if (!empty(Globals::$conf->commande->enabled)) {
                    Globals::$langs->load("orders");
//$newmenu->add("/commande/index.php?leftmenu=orders", Globals::$langs->trans("CustomersOrders"), 0, Globals::$user->rights->commande->lire, '', $mainmenu, 'orders', 200);
//$newmenu->add("/commande/card.php?action=create&leftmenu=orders", Globals::$langs->trans("NewOrder"), 1, Globals::$user->rights->commande->creer);
//$newmenu->add("/commande/list.php?leftmenu=orders", Globals::$langs->trans("List"), 1, Globals::$user->rights->commande->lire);
                    $newmenu->add(BASE_URI . "?controller=commande&method=index&leftmenu=orders", Globals::$langs->trans("CustomersOrders"), 0, Globals::$user->rights->commande->lire, '', $mainmenu, 'orders', 200);
                    $newmenu->add(BASE_URI . "?controller=commande&method=card&action=create&leftmenu=orders", Globals::$langs->trans("NewOrder"), 1, Globals::$user->rights->commande->creer);
                    $newmenu->add(BASE_URI . "?controller=commande&method=list&leftmenu=orders", Globals::$langs->trans("List"), 1, Globals::$user->rights->commande->lire);
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "orders") {
//$newmenu->add("/commande/list.php?leftmenu=orders&viewstatut=0", Globals::$langs->trans("StatusOrderDraftShort"), 2, Globals::$user->rights->commande->lire);
//$newmenu->add("/commande/list.php?leftmenu=orders&viewstatut=1", Globals::$langs->trans("StatusOrderValidated"), 2, Globals::$user->rights->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=commande&method=list&leftmenu=orders&viewstatut=0", Globals::$langs->trans("StatusOrderDraftShort"), 2, Globals::$user->rights->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=commande&method=list&leftmenu=orders&viewstatut=1", Globals::$langs->trans("StatusOrderValidated"), 2, Globals::$user->rights->commande->lire);
                        if (!empty(Globals::$conf->expedition->enabled)) {
//$newmenu->add("/commande/list.php?leftmenu=orders&viewstatut=2", Globals::$langs->trans("StatusOrderSentShort"), 2, Globals::$user->rights->commande->lire);
                            $newmenu->add(BASE_URI . "?controller=commande&method=list&leftmenu=orders&viewstatut=2", Globals::$langs->trans("StatusOrderSentShort"), 2, Globals::$user->rights->commande->lire);
                        }
//$newmenu->add("/commande/list.php?leftmenu=orders&viewstatut=3", Globals::$langs->trans("StatusOrderDelivered"), 2, Globals::$user->rights->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=commande&method=list&leftmenu=orders&viewstatut=3", Globals::$langs->trans("StatusOrderDelivered"), 2, Globals::$user->rights->commande->lire);
//$newmenu->add("/commande/list.php?leftmenu=orders&viewstatut=4", Globals::$langs->trans("StatusOrderProcessed"), 2, Globals::$user->rights->commande->lire);
//$newmenu->add("/commande/list.php?leftmenu=orders&viewstatut=-1", Globals::$langs->trans("StatusOrderCanceledShort"), 2, Globals::$user->rights->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=commande&method=list&leftmenu=orders&viewstatut=-1", Globals::$langs->trans("StatusOrderCanceledShort"), 2, Globals::$user->rights->commande->lire);
                    }
//$newmenu->add("/commande/stats/index.php?leftmenu=orders", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->commande->lire);
                    $newmenu->add(BASE_URI . "?controller=commande/stats&method=index&leftmenu=orders", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->commande->lire);
                }

// Suppliers orders
                if (!empty(Globals::$conf->supplier_order->enabled)) {
                    Globals::$langs->load("orders");
//$newmenu->add("/fourn/commande/index.php?leftmenu=orders_suppliers", Globals::$langs->trans("SuppliersOrders"), 0, Globals::$user->rights->fournisseur->commande->lire, '', $mainmenu, 'orders_suppliers', 400);
//$newmenu->add("/fourn/commande/card.php?action=create&leftmenu=orders_suppliers", Globals::$langs->trans("NewOrder"), 1, Globals::$user->rights->fournisseur->commande->creer);
//$newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers", Globals::$langs->trans("List"), 1, Globals::$user->rights->fournisseur->commande->lire);
                    $newmenu->add(BASE_URI . "?controller=fourn/commande&method=index&leftmenu=orders_suppliers", Globals::$langs->trans("SuppliersOrders"), 0, Globals::$user->rights->fournisseur->commande->lire, '', $mainmenu, 'orders_suppliers', 400);
                    $newmenu->add(BASE_URI . "?controller=fourn/commande&method=card&action=create&leftmenu=orders_suppliers", Globals::$langs->trans("NewOrder"), 1, Globals::$user->rights->fournisseur->commande->creer);
                    $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers", Globals::$langs->trans("List"), 1, Globals::$user->rights->fournisseur->commande->lire);

                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "orders_suppliers") {
//$newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&statut=0", Globals::$langs->trans("StatusOrderDraftShort"), 2, Globals::$user->rights->fournisseur->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers&statut=0", Globals::$langs->trans("StatusOrderDraftShort"), 2, Globals::$user->rights->fournisseur->commande->lire);
                        if (empty(Globals::$conf->global->SUPPLIER_ORDER_HIDE_VALIDATED)) {
//$newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&statut=1", Globals::$langs->trans("StatusOrderValidated"), 2, Globals::$user->rights->fournisseur->commande->lire);
                            $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers&statut=1", Globals::$langs->trans("StatusOrderValidated"), 2, Globals::$user->rights->fournisseur->commande->lire);
                        }
                        /*
                          $newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&statut=2", Globals::$langs->trans("StatusOrderApprovedShort"), 2, Globals::$user->rights->fournisseur->commande->lire);
                          $newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&statut=3", Globals::$langs->trans("StatusOrderOnProcessShort"), 2, Globals::$user->rights->fournisseur->commande->lire);
                          $newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&statut=4", Globals::$langs->trans("StatusOrderReceivedPartiallyShort"), 2, Globals::$user->rights->fournisseur->commande->lire);
                          $newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&statut=5", Globals::$langs->trans("StatusOrderReceivedAll"), 2, Globals::$user->rights->fournisseur->commande->lire);
                          $newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&statut=6,7", Globals::$langs->trans("StatusOrderCanceled"), 2, Globals::$user->rights->fournisseur->commande->lire);
                          $newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&statut=9", Globals::$langs->trans("StatusOrderRefused"), 2, Globals::$user->rights->fournisseur->commande->lire);
                         */
                        $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers&statut=2", Globals::$langs->trans("StatusOrderApprovedShort"), 2, Globals::$user->rights->fournisseur->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers&statut=3", Globals::$langs->trans("StatusOrderOnProcessShort"), 2, Globals::$user->rights->fournisseur->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers&statut=4", Globals::$langs->trans("StatusOrderReceivedPartiallyShort"), 2, Globals::$user->rights->fournisseur->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers&statut=5", Globals::$langs->trans("StatusOrderReceivedAll"), 2, Globals::$user->rights->fournisseur->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers&statut=6,7", Globals::$langs->trans("StatusOrderCanceled"), 2, Globals::$user->rights->fournisseur->commande->lire);
                        $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders_suppliers&statut=9", Globals::$langs->trans("StatusOrderRefused"), 2, Globals::$user->rights->fournisseur->commande->lire);
                    }
// Billed is another field. We should add instead a dedicated filter on list. if ($usemenuhider || empty($leftmenu) || $leftmenu=="orders_suppliers") $newmenu->add("/fourn/commande/list.php?leftmenu=orders_suppliers&billed=1", Globals::$langs->trans("StatusOrderBilled"), 2, Globals::$user->rights->fournisseur->commande->lire);
//$newmenu->add("/commande/stats/index.php?leftmenu=orders_suppliers&mode=supplier", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->fournisseur->commande->lire);
                    $newmenu->add(BASE_URI . "?controller=commande/stats&method=index&leftmenu=orders_suppliers&mode=supplier", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->fournisseur->commande->lire);
                }

// Contrat
                if (!empty(Globals::$conf->contrat->enabled)) {
                    Globals::$langs->load("contracts");
                    /*
                      $newmenu->add("/contrat/index.php?leftmenu=contracts", Globals::$langs->trans("ContractsSubscriptions"), 0, Globals::$user->rights->contrat->lire, '', $mainmenu, 'contracts', 2000);
                      $newmenu->add("/contrat/card.php?action=create&leftmenu=contracts", Globals::$langs->trans("NewContractSubscription"), 1, Globals::$user->rights->contrat->creer);
                      $newmenu->add("/contrat/list.php?leftmenu=contracts", Globals::$langs->trans("List"), 1, Globals::$user->rights->contrat->lire);
                      $newmenu->add("/contrat/services_list.php?leftmenu=contracts", Globals::$langs->trans("MenuServices"), 1, Globals::$user->rights->contrat->lire);
                     */
                    $newmenu->add(BASE_URI . "?controller=contrat&method=index&leftmenu=contracts", Globals::$langs->trans("ContractsSubscriptions"), 0, Globals::$user->rights->contrat->lire, '', $mainmenu, 'contracts', 2000);
                    $newmenu->add(BASE_URI . "?controller=contrat&method=card&action=create&leftmenu=contracts", Globals::$langs->trans("NewContractSubscription"), 1, Globals::$user->rights->contrat->creer);
                    $newmenu->add(BASE_URI . "?controller=contrat&method=list&leftmenu=contracts", Globals::$langs->trans("List"), 1, Globals::$user->rights->contrat->lire);
                    $newmenu->add(BASE_URI . "?controller=contrat&method=services_list&leftmenu=contracts", Globals::$langs->trans("MenuServices"), 1, Globals::$user->rights->contrat->lire);
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "contracts") {
                        /*
                          $newmenu->add("/contrat/services_list.php?leftmenu=contracts&mode=0", Globals::$langs->trans("MenuInactiveServices"), 2, Globals::$user->rights->contrat->lire);
                          $newmenu->add("/contrat/services_list.php?leftmenu=contracts&mode=4", Globals::$langs->trans("MenuRunningServices"), 2, Globals::$user->rights->contrat->lire);
                          $newmenu->add("/contrat/services_list.php?leftmenu=contracts&mode=4&filter=expired", Globals::$langs->trans("MenuExpiredServices"), 2, Globals::$user->rights->contrat->lire);
                          $newmenu->add("/contrat/services_list.php?leftmenu=contracts&mode=5", Globals::$langs->trans("MenuClosedServices"), 2, Globals::$user->rights->contrat->lire);
                         */
                        $newmenu->add(BASE_URI . "?controller=contrat&method=services_list&leftmenu=contracts&mode=0", Globals::$langs->trans("MenuInactiveServices"), 2, Globals::$user->rights->contrat->lire);
                        $newmenu->add(BASE_URI . "?controller=contrat&method=services_list&leftmenu=contracts&mode=4", Globals::$langs->trans("MenuRunningServices"), 2, Globals::$user->rights->contrat->lire);
                        $newmenu->add(BASE_URI . "?controller=contrat&method=services_list&leftmenu=contracts&mode=4&filter=expired", Globals::$langs->trans("MenuExpiredServices"), 2, Globals::$user->rights->contrat->lire);
                        $newmenu->add(BASE_URI . "?controller=contrat&method=services_list&leftmenu=contracts&mode=5", Globals::$langs->trans("MenuClosedServices"), 2, Globals::$user->rights->contrat->lire);
                    }
                }

// Interventions
                if (!empty(Globals::$conf->ficheinter->enabled)) {
                    Globals::$langs->load("interventions");
                    /*
                      $newmenu->add("/fichinter/index.php?leftmenu=ficheinter", Globals::$langs->trans("Interventions"), 0, Globals::$user->rights->ficheinter->lire, '', $mainmenu, 'ficheinter', 2200);
                      $newmenu->add("/fichinter/card.php?action=create&leftmenu=ficheinter", Globals::$langs->trans("NewIntervention"), 1, Globals::$user->rights->ficheinter->creer, '', '', '', 201);
                      $newmenu->add("/fichinter/list.php?leftmenu=ficheinter", Globals::$langs->trans("List"), 1, Globals::$user->rights->ficheinter->lire, '', '', '', 202);
                      $newmenu->add("/fichinter/card-red.php?leftmenu=ficheinter", Globals::$langs->trans("ModelList"), 1, Globals::$user->rights->ficheinter->lire, '', '', '', 203);
                      $newmenu->add("/fichinter/stats/index.php?leftmenu=ficheinter", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->fournisseur->commande->lire);
                     */
                    $newmenu->add(BASE_URI . "?controller=fichinter&method=index&leftmenu=ficheinter", Globals::$langs->trans("Interventions"), 0, Globals::$user->rights->ficheinter->lire, '', $mainmenu, 'ficheinter', 2200);
                    $newmenu->add(BASE_URI . "?controller=fichinter&method=card&action=create&leftmenu=ficheinter", Globals::$langs->trans("NewIntervention"), 1, Globals::$user->rights->ficheinter->creer, '', '', '', 201);
                    $newmenu->add(BASE_URI . "?controller=fichinter&method=list&leftmenu=ficheinter", Globals::$langs->trans("List"), 1, Globals::$user->rights->ficheinter->lire, '', '', '', 202);
                    $newmenu->add(BASE_URI . "?controller=fichinter&method=card-red&leftmenu=ficheinter", Globals::$langs->trans("ModelList"), 1, Globals::$user->rights->ficheinter->lire, '', '', '', 203);
                    $newmenu->add(BASE_URI . "?controller=fichinter/stats&method=index&leftmenu=ficheinter", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->fournisseur->commande->lire);
                }
            }

            /*
             * Menu COMPTA-FINANCIAL
             */
            if ($mainmenu == 'billing') {
                Globals::$langs->load("companies");

// Customers invoices
                if (!empty(Globals::$conf->facture->enabled)) {
                    Globals::$langs->load("bills");
                    /*
                      $newmenu->add("/compta/facture/list.php?leftmenu=customers_bills", Globals::$langs->trans("BillsCustomers"), 0, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills');
                      $newmenu->add("/compta/facture/card.php?action=create", Globals::$langs->trans("NewBill"), 1, Globals::$user->rights->facture->creer);
                      $newmenu->add("/compta/facture/list.php?leftmenu=customers_bills", Globals::$langs->trans("List"), 1, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills_list');
                     */
                    $newmenu->add(BASE_URI . "?controller=compta/facture&method=list&leftmenu=customers_bills", Globals::$langs->trans("BillsCustomers"), 0, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills');
                    $newmenu->add(BASE_URI . "?controller=compta/facture&method=card&action=create", Globals::$langs->trans("NewBill"), 1, Globals::$user->rights->facture->creer);
                    $newmenu->add(BASE_URI . "?controller=compta/facture&method=list&leftmenu=customers_bills", Globals::$langs->trans("List"), 1, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills_list');

                    if ($usemenuhider || empty($leftmenu) || preg_match('/customers_bills(|_draft|_notpaid|_paid|_canceled)$/', $leftmenu)) {
                        /*
                          $newmenu->add("/compta/facture/list.php?leftmenu=customers_bills_draft&search_status=0", Globals::$langs->trans("BillShortStatusDraft"), 2, Globals::$user->rights->facture->lire);
                          $newmenu->add("/compta/facture/list.php?leftmenu=customers_bills_notpaid&search_status=1", Globals::$langs->trans("BillShortStatusNotPaid"), 2, Globals::$user->rights->facture->lire);
                          $newmenu->add("/compta/facture/list.php?leftmenu=customers_bills_paid&search_status=2", Globals::$langs->trans("BillShortStatusPaid"), 2, Globals::$user->rights->facture->lire);
                          $newmenu->add("/compta/facture/list.php?leftmenu=customers_bills_canceled&search_status=3", Globals::$langs->trans("BillShortStatusCanceled"), 2, Globals::$user->rights->facture->lire);
                         */
                        $newmenu->add(BASE_URI . "?controller=compta/facture&method=list&leftmenu=customers_bills_draft&search_status=0", Globals::$langs->trans("BillShortStatusDraft"), 2, Globals::$user->rights->facture->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/facture&method=list&leftmenu=customers_bills_notpaid&search_status=1", Globals::$langs->trans("BillShortStatusNotPaid"), 2, Globals::$user->rights->facture->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/facture&method=list&leftmenu=customers_bills_paid&search_status=2", Globals::$langs->trans("BillShortStatusPaid"), 2, Globals::$user->rights->facture->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/facture&method=list&leftmenu=customers_bills_canceled&search_status=3", Globals::$langs->trans("BillShortStatusCanceled"), 2, Globals::$user->rights->facture->lire);
                    }
//$newmenu->add("/compta/facture/invoicetemplate_list.php?leftmenu=customers_bills_templates", Globals::$langs->trans("ListOfTemplates"), 1, Globals::$user->rights->facture->creer, '', $mainmenu, 'customers_bills_templates');    // No need to see recurring invoices, if user has no permission to create invoice.
//$newmenu->add("/compta/paiement/list.php?leftmenu=customers_bills_payment", Globals::$langs->trans("Payments"), 1, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills_payment');
                    $newmenu->add(BASE_URI . "?controller=compta/facture&method=invoicetemplate_list&leftmenu=customers_bills_templates", Globals::$langs->trans("ListOfTemplates"), 1, Globals::$user->rights->facture->creer, '', $mainmenu, 'customers_bills_templates');    // No need to see recurring invoices, if user has no permission to create invoice.
                    $newmenu->add(BASE_URI . "?controller=compta/paiement&method=list&leftmenu=customers_bills_payment", Globals::$langs->trans("Payments"), 1, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills_payment');

                    if (!empty(Globals::$conf->global->BILL_ADD_PAYMENT_VALIDATION)) {
//$newmenu->add("/compta/paiement/tovalidate.php?leftmenu=customers_bills_tovalid", Globals::$langs->trans("MenuToValid"), 2, Globals::$user->rights->facture->lire, '', $mainmenu, 'customer_bills_tovalid');
                        $newmenu->add(BASE_URI . "?controller=compta/paiement&method=tovalidate&leftmenu=customers_bills_tovalid", Globals::$langs->trans("MenuToValid"), 2, Globals::$user->rights->facture->lire, '', $mainmenu, 'customer_bills_tovalid');
                    }
//$newmenu->add("/compta/paiement/rapport.php?leftmenu=customers_bills_reports", Globals::$langs->trans("Reportings"), 2, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills_reports');
//$newmenu->add("/compta/facture/stats/index.php?leftmenu=customers_bills_stats", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills_stats');
                    $newmenu->add(BASE_URI . "?controller=compta/paiement&method=rapport&leftmenu=customers_bills_reports", Globals::$langs->trans("Reportings"), 2, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills_reports');
                    $newmenu->add(BASE_URI . "?controller=compta/facture/stats&method=index&leftmenu=customers_bills_stats", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->facture->lire, '', $mainmenu, 'customers_bills_stats');
                }

// Suppliers invoices
                if (!empty(Globals::$conf->societe->enabled) && !empty(Globals::$conf->supplier_invoice->enabled)) {
                    Globals::$langs->load("bills");
//$newmenu->add("/fourn/facture/list.php?leftmenu=suppliers_bills", Globals::$langs->trans("BillsSuppliers"), 0, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills');
//$newmenu->add("/fourn/facture/card.php?leftmenu=suppliers_bills&action=create", Globals::$langs->trans("NewBill"), 1, Globals::$user->rights->fournisseur->facture->creer, '', $mainmenu, 'suppliers_bills_create');
//$newmenu->add("/fourn/facture/list.php?leftmenu=suppliers_bills", Globals::$langs->trans("List"), 1, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_list');
                    $newmenu->add(BASE_URI . "?controller=fourn/facture&method=list&leftmenu=suppliers_bills", Globals::$langs->trans("BillsSuppliers"), 0, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills');
                    $newmenu->add(BASE_URI . "?controller=fourn/facture&method=card&leftmenu=suppliers_bills&action=create", Globals::$langs->trans("NewBill"), 1, Globals::$user->rights->fournisseur->facture->creer, '', $mainmenu, 'suppliers_bills_create');
                    $newmenu->add(BASE_URI . "?controller=fourn/facture&method=list&leftmenu=suppliers_bills", Globals::$langs->trans("List"), 1, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_list');

                    if ($usemenuhider || empty($leftmenu) || preg_match('/suppliers_bills/', $leftmenu)) {
//$newmenu->add("/fourn/facture/list.php?leftmenu=suppliers_bills_draft&search_status=0", Globals::$langs->trans("BillShortStatusDraft"), 2, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_draft');
//$newmenu->add("/fourn/facture/list.php?leftmenu=suppliers_bills_notpaid&search_status=1", Globals::$langs->trans("BillShortStatusNotPaid"), 2, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_notpaid');
//$newmenu->add("/fourn/facture/list.php?leftmenu=suppliers_bills_paid&search_status=2", Globals::$langs->trans("BillShortStatusPaid"), 2, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_paid');
                        $newmenu->add(BASE_URI . "?controller=fourn/facture&method=list&leftmenu=suppliers_bills_draft&search_status=0", Globals::$langs->trans("BillShortStatusDraft"), 2, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_draft');
                        $newmenu->add(BASE_URI . "?controller=fourn/facture&method=list&leftmenu=suppliers_bills_notpaid&search_status=1", Globals::$langs->trans("BillShortStatusNotPaid"), 2, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_notpaid');
                        $newmenu->add(BASE_URI . "?controller=fourn/facture&method=list&leftmenu=suppliers_bills_paid&search_status=2", Globals::$langs->trans("BillShortStatusPaid"), 2, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_paid');
                    }

//$newmenu->add("/fourn/facture/paiement.php?leftmenu=suppliers_bills_payment", Globals::$langs->trans("Payments"), 1, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_payment');
//$newmenu->add("/fourn/facture/rapport.php?leftmenu=suppliers_bills_report", Globals::$langs->trans("Reportings"), 2, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_report');
//$newmenu->add("/compta/facture/stats/index.php?mode=supplier&leftmenu=suppliers_bills_stats", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_stats');
                    $newmenu->add(BASE_URI . "?controller=fourn/facture&method=paiement&leftmenu=suppliers_bills_payment", Globals::$langs->trans("Payments"), 1, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_payment');
                    $newmenu->add(BASE_URI . "?controller=fourn/facture&method=rapport&leftmenu=suppliers_bills_report", Globals::$langs->trans("Reportings"), 2, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_report');
                    $newmenu->add(BASE_URI . "?controller=compta/facture/stats&method=index&mode=supplier&leftmenu=suppliers_bills_stats", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->fournisseur->facture->lire, '', $mainmenu, 'suppliers_bills_stats');
                }

// Orders
                if (!empty(Globals::$conf->commande->enabled)) {
                    Globals::$langs->load("orders");
                    if (!empty(Globals::$conf->facture->enabled)) {
//$newmenu->add("/commande/list.php?leftmenu=orders&viewstatut=-3&billed=0&contextpage=billableorders", Globals::$langs->trans("MenuOrdersToBill2"), 0, Globals::$user->rights->commande->lire, '', $mainmenu, 'orders');
                        $newmenu->add(BASE_URI . "?controller=commande&method=list&leftmenu=orders&viewstatut=-3&billed=0&contextpage=billableorders", Globals::$langs->trans("MenuOrdersToBill2"), 0, Globals::$user->rights->commande->lire, '', $mainmenu, 'orders');
                    }
//                  if ($usemenuhider || empty($leftmenu) || $leftmenu=="orders") $newmenu->add("/commande/", Globals::$langs->trans("StatusOrderToBill"), 1, Globals::$user->rights->commande->lire);
                }

// Supplier Orders to bill
                if (!empty(Globals::$conf->supplier_invoice->enabled)) {
                    if (!empty(Globals::$conf->global->SUPPLIER_MENU_ORDER_RECEIVED_INTO_INVOICE)) {
                        Globals::$langs->load("supplier");
//$newmenu->add("/fourn/commande/list.php?leftmenu=orders&search_status=5&billed=0", Globals::$langs->trans("MenuOrdersSupplierToBill"), 0, Globals::$user->rights->commande->lire, '', $mainmenu, 'orders');
                        $newmenu->add(BASE_URI . "?controller=fourn/commande&method=list&leftmenu=orders&search_status=5&billed=0", Globals::$langs->trans("MenuOrdersSupplierToBill"), 0, Globals::$user->rights->commande->lire, '', $mainmenu, 'orders');
//                  if ($usemenuhider || empty($leftmenu) || $leftmenu=="orders") $newmenu->add("/commande/", Globals::$langs->trans("StatusOrderToBill"), 1, Globals::$user->rights->commande->lire);
                    }
                }


// Donations
                if (!empty(Globals::$conf->don->enabled)) {
                    Globals::$langs->load("donations");
//$newmenu->add("/don/index.php?leftmenu=donations&mainmenu=billing", Globals::$langs->trans("Donations"), 0, Globals::$user->rights->don->lire, '', $mainmenu, 'donations');
                    $newmenu->add(BASE_URI . "?controller=don&method=index&leftmenu=donations&mainmenu=billing", Globals::$langs->trans("Donations"), 0, Globals::$user->rights->don->lire, '', $mainmenu, 'donations');
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "donations") {
//$newmenu->add("/don/card.php?leftmenu=donations&action=create", Globals::$langs->trans("NewDonation"), 1, Globals::$user->rights->don->creer);
//$newmenu->add("/don/list.php?leftmenu=donations", Globals::$langs->trans("List"), 1, Globals::$user->rights->don->lire);
                        $newmenu->add(BASE_URI . "?controller=don&method=card&leftmenu=donations&action=create", Globals::$langs->trans("NewDonation"), 1, Globals::$user->rights->don->creer);
                        $newmenu->add(BASE_URI . "?controller=don&method=list&leftmenu=donations", Globals::$langs->trans("List"), 1, Globals::$user->rights->don->lire);
                    }
// if ($leftmenu=="donations") $newmenu->add("/don/stats/index.php",Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->don->lire);
                }

// Taxes and social contributions
                if (!empty(Globals::$conf->tax->enabled) || !empty(Globals::$conf->salaries->enabled) || !empty(Globals::$conf->loan->enabled) || !empty(Globals::$conf->banque->enabled)) {
                    global $mysoc;

                    $permtoshowmenu = ((!empty(Globals::$conf->tax->enabled) && Globals::$user->rights->tax->charges->lire) || (!empty(Globals::$conf->salaries->enabled) && !empty(Globals::$user->rights->salaries->read)) || (!empty(Globals::$conf->loan->enabled) && Globals::$user->rights->loan->read) || (!empty(Globals::$conf->banque->enabled) && Globals::$user->rights->banque->lire));
//$newmenu->add("/compta/charges/index.php?leftmenu=tax&mainmenu=billing", Globals::$langs->trans("MenuSpecialExpenses"), 0, $permtoshowmenu, '', $mainmenu, 'tax');
                    $newmenu->add(BASE_URI . "?controller=compta/charges&method=index&leftmenu=tax&mainmenu=billing", Globals::$langs->trans("MenuSpecialExpenses"), 0, $permtoshowmenu, '', $mainmenu, 'tax');

// Social contributions
                    if (!empty(Globals::$conf->tax->enabled)) {
//$newmenu->add("/compta/sociales/list.php?leftmenu=tax_social", Globals::$langs->trans("MenuSocialContributions"), 1, Globals::$user->rights->tax->charges->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/sociales&method=list&leftmenu=tax_social", Globals::$langs->trans("MenuSocialContributions"), 1, Globals::$user->rights->tax->charges->lire);
                        if ($usemenuhider || empty($leftmenu) || preg_match('/^tax_social/i', $leftmenu)) {
//$newmenu->add("/compta/sociales/card.php?leftmenu=tax_social&action=create", Globals::$langs->trans("MenuNewSocialContribution"), 2, Globals::$user->rights->tax->charges->creer);
//$newmenu->add("/compta/sociales/list.php?leftmenu=tax_social", Globals::$langs->trans("List"), 2, Globals::$user->rights->tax->charges->lire);
//$newmenu->add("/compta/sociales/payments.php?leftmenu=tax_social&mainmenu=billing&mode=sconly", Globals::$langs->trans("Payments"), 2, Globals::$user->rights->tax->charges->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/sociales&method=card&leftmenu=tax_social&action=create", Globals::$langs->trans("MenuNewSocialContribution"), 2, Globals::$user->rights->tax->charges->creer);
                            $newmenu->add(BASE_URI . "?controller=compta/sociales&method=list&leftmenu=tax_social", Globals::$langs->trans("List"), 2, Globals::$user->rights->tax->charges->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/sociales&method=payments&leftmenu=tax_social&mainmenu=billing&mode=sconly", Globals::$langs->trans("Payments"), 2, Globals::$user->rights->tax->charges->lire);
                        }
// VAT
                        if (empty(Globals::$conf->global->TAX_DISABLE_VAT_MENUS)) {
//$newmenu->add("/compta/tva/list.php?leftmenu=tax_vat&mainmenu=billing", Globals::$langs->transcountry("VAT", $mysoc->country_code), 1, Globals::$user->rights->tax->charges->lire, '', $mainmenu, 'tax_vat');
                            $newmenu->add(BASE_URI . "?controller=compta/tva&method=list&leftmenu=tax_vat&mainmenu=billing", Globals::$langs->transcountry("VAT", $mysoc->country_code), 1, Globals::$user->rights->tax->charges->lire, '', $mainmenu, 'tax_vat');
                            if ($usemenuhider || empty($leftmenu) || preg_match('/^tax_vat/i', $leftmenu)) {
                                /*
                                  $newmenu->add("/compta/tva/card.php?leftmenu=tax_vat&action=create", Globals::$langs->trans("New"), 2, Globals::$user->rights->tax->charges->creer);
                                  $newmenu->add("/compta/tva/list.php?leftmenu=tax_vat", Globals::$langs->trans("List"), 2, Globals::$user->rights->tax->charges->lire);
                                  $newmenu->add("/compta/tva/index.php?leftmenu=tax_vat", Globals::$langs->trans("ReportByMonth"), 2, Globals::$user->rights->tax->charges->lire);
                                  $newmenu->add("/compta/tva/clients.php?leftmenu=tax_vat", Globals::$langs->trans("ReportByCustomers"), 2, Globals::$user->rights->tax->charges->lire);
                                  $newmenu->add("/compta/tva/quadri_detail.php?leftmenu=tax_vat", Globals::$langs->trans("ReportByQuarter"), 2, Globals::$user->rights->tax->charges->lire);
                                 */
                                $newmenu->add(BASE_URI . "?controller=compta/tva&method=card&leftmenu=tax_vat&action=create", Globals::$langs->trans("New"), 2, Globals::$user->rights->tax->charges->creer);
                                $newmenu->add(BASE_URI . "?controller=compta/tva&method=list&leftmenu=tax_vat", Globals::$langs->trans("List"), 2, Globals::$user->rights->tax->charges->lire);
                                $newmenu->add(BASE_URI . "?controller=compta/tva&method=index&leftmenu=tax_vat", Globals::$langs->trans("ReportByMonth"), 2, Globals::$user->rights->tax->charges->lire);
                                $newmenu->add(BASE_URI . "?controller=compta/tva&method=clients&leftmenu=tax_vat", Globals::$langs->trans("ReportByCustomers"), 2, Globals::$user->rights->tax->charges->lire);
                                $newmenu->add(BASE_URI . "?controller=compta/tva&method=quadri_detail&leftmenu=tax_vat", Globals::$langs->trans("ReportByQuarter"), 2, Globals::$user->rights->tax->charges->lire);
                            }
                            global $mysoc;

//Local Taxes 1
                            if ($mysoc->useLocalTax(1) && (isset($mysoc->localtax1_assuj) && $mysoc->localtax1_assuj == "1")) {
//$newmenu->add("/compta/localtax/list.php?leftmenu=tax_1_vat&mainmenu=billing&localTaxType=1", Globals::$langs->transcountry("LT1", $mysoc->country_code), 1, Globals::$user->rights->tax->charges->lire);
                                $newmenu->add(BASE_URI . "?controller=compta/localtax&method=list&leftmenu=tax_1_vat&mainmenu=billing&localTaxType=1", Globals::$langs->transcountry("LT1", $mysoc->country_code), 1, Globals::$user->rights->tax->charges->lire);
                                if ($usemenuhider || empty($leftmenu) || preg_match('/^tax_1_vat/i', $leftmenu)) {
                                    /*
                                      $newmenu->add("/compta/localtax/card.php?leftmenu=tax_1_vat&action=create&localTaxType=1", Globals::$langs->trans("New"), 2, Globals::$user->rights->tax->charges->creer);
                                      $newmenu->add("/compta/localtax/list.php?leftmenu=tax_1_vat&localTaxType=1", Globals::$langs->trans("List"), 2, Globals::$user->rights->tax->charges->lire);
                                      $newmenu->add("/compta/localtax/index.php?leftmenu=tax_1_vat&localTaxType=1", Globals::$langs->trans("ReportByMonth"), 2, Globals::$user->rights->tax->charges->lire);
                                      $newmenu->add("/compta/localtax/clients.php?leftmenu=tax_1_vat&localTaxType=1", Globals::$langs->trans("ReportByCustomers"), 2, Globals::$user->rights->tax->charges->lire);
                                      $newmenu->add("/compta/localtax/quadri_detail.php?leftmenu=tax_1_vat&localTaxType=1", Globals::$langs->trans("ReportByQuarter"), 2, Globals::$user->rights->tax->charges->lire);
                                     */
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=card&leftmenu=tax_1_vat&action=create&localTaxType=1", Globals::$langs->trans("New"), 2, Globals::$user->rights->tax->charges->creer);
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=list&leftmenu=tax_1_vat&localTaxType=1", Globals::$langs->trans("List"), 2, Globals::$user->rights->tax->charges->lire);
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=index&leftmenu=tax_1_vat&localTaxType=1", Globals::$langs->trans("ReportByMonth"), 2, Globals::$user->rights->tax->charges->lire);
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=clients&leftmenu=tax_1_vat&localTaxType=1", Globals::$langs->trans("ReportByCustomers"), 2, Globals::$user->rights->tax->charges->lire);
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=quadri_detail&leftmenu=tax_1_vat&localTaxType=1", Globals::$langs->trans("ReportByQuarter"), 2, Globals::$user->rights->tax->charges->lire);
                                }
                            }
//Local Taxes 2
                            if ($mysoc->useLocalTax(2) && (isset($mysoc->localtax2_assuj) && $mysoc->localtax2_assuj == "1")) {
//$newmenu->add("/compta/localtax/list.php?leftmenu=tax_2_vat&mainmenu=billing&localTaxType=2", Globals::$langs->transcountry("LT2", $mysoc->country_code), 1, Globals::$user->rights->tax->charges->lire);
                                $newmenu->add(BASE_URI . "?controller=compta/localtax&method=list&leftmenu=tax_2_vat&mainmenu=billing&localTaxType=2", Globals::$langs->transcountry("LT2", $mysoc->country_code), 1, Globals::$user->rights->tax->charges->lire);
                                if ($usemenuhider || empty($leftmenu) || preg_match('/^tax_2_vat/i', $leftmenu)) {
                                    /*
                                      $newmenu->add("/compta/localtax/card.php?leftmenu=tax_2_vat&action=create&localTaxType=2", Globals::$langs->trans("New"), 2, Globals::$user->rights->tax->charges->creer);
                                      $newmenu->add("/compta/localtax/list.php?leftmenu=tax_2_vat&localTaxType=2", Globals::$langs->trans("List"), 2, Globals::$user->rights->tax->charges->lire);
                                      $newmenu->add("/compta/localtax/index.php?leftmenu=tax_2_vat&localTaxType=2", Globals::$langs->trans("ReportByMonth"), 2, Globals::$user->rights->tax->charges->lire);
                                      $newmenu->add("/compta/localtax/clients.php?leftmenu=tax_2_vat&localTaxType=2", Globals::$langs->trans("ReportByCustomers"), 2, Globals::$user->rights->tax->charges->lire);
                                      $newmenu->add("/compta/localtax/quadri_detail.php?leftmenu=tax_2_vat&localTaxType=2", Globals::$langs->trans("ReportByQuarter"), 2, Globals::$user->rights->tax->charges->lire);
                                     */
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=card&leftmenu=tax_2_vat&action=create&localTaxType=2", Globals::$langs->trans("New"), 2, Globals::$user->rights->tax->charges->creer);
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=list&leftmenu=tax_2_vat&localTaxType=2", Globals::$langs->trans("List"), 2, Globals::$user->rights->tax->charges->lire);
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=index&leftmenu=tax_2_vat&localTaxType=2", Globals::$langs->trans("ReportByMonth"), 2, Globals::$user->rights->tax->charges->lire);
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=clients&leftmenu=tax_2_vat&localTaxType=2", Globals::$langs->trans("ReportByCustomers"), 2, Globals::$user->rights->tax->charges->lire);
                                    $newmenu->add(BASE_URI . "?controller=compta/localtax&method=quadri_detail&leftmenu=tax_2_vat&localTaxType=2", Globals::$langs->trans("ReportByQuarter"), 2, Globals::$user->rights->tax->charges->lire);
                                }
                            }
                        }
                    }

// Salaries
                    if (!empty(Globals::$conf->salaries->enabled)) {
                        Globals::$langs->load("salaries");
//$newmenu->add("/compta/salaries/list.php?leftmenu=tax_salary&mainmenu=billing", Globals::$langs->trans("Salaries"), 1, Globals::$user->rights->salaries->read, '', $mainmenu, 'tax_salary');
                        $newmenu->add(BASE_URI . "?controller=compta/salaries&method=list&leftmenu=tax_salary&mainmenu=billing", Globals::$langs->trans("Salaries"), 1, Globals::$user->rights->salaries->read, '', $mainmenu, 'tax_salary');
                        if ($usemenuhider || empty($leftmenu) || preg_match('/^tax_salary/i', $leftmenu)) {
//$newmenu->add("/compta/salaries/card.php?leftmenu=tax_salary&action=create", Globals::$langs->trans("NewPayment"), 2, Globals::$user->rights->salaries->write);
//$newmenu->add("/compta/salaries/list.php?leftmenu=tax_salary", Globals::$langs->trans("Payments"), 2, Globals::$user->rights->salaries->read);
//$newmenu->add("/compta/salaries/stats/index.php?leftmenu=tax_salary", Globals::$langs->trans("Statistics"), 2, Globals::$user->rights->salaries->read);
                            $newmenu->add(BASE_URI . "?controller=compta/salaries&method=card&leftmenu=tax_salary&action=create", Globals::$langs->trans("NewPayment"), 2, Globals::$user->rights->salaries->write);
                            $newmenu->add(BASE_URI . "?controller=compta/salaries&method=list&leftmenu=tax_salary", Globals::$langs->trans("Payments"), 2, Globals::$user->rights->salaries->read);
                            $newmenu->add(BASE_URI . "?controller=compta/salaries/stats&method=index&leftmenu=tax_salary", Globals::$langs->trans("Statistics"), 2, Globals::$user->rights->salaries->read);
                        }
                    }

// Loan
                    if (!empty(Globals::$conf->loan->enabled)) {
                        Globals::$langs->load("loan");
//$newmenu->add("/loan/list.php?leftmenu=tax_loan&mainmenu=billing", Globals::$langs->trans("Loans"), 1, Globals::$user->rights->loan->read, '', $mainmenu, 'tax_loan');
                        $newmenu->add(BASE_URI . "?controller=loan&method=list&leftmenu=tax_loan&mainmenu=billing", Globals::$langs->trans("Loans"), 1, Globals::$user->rights->loan->read, '', $mainmenu, 'tax_loan');
                        if ($usemenuhider || empty($leftmenu) || preg_match('/^tax_loan/i', $leftmenu)) {
//$newmenu->add("/loan/card.php?leftmenu=tax_loan&action=create", Globals::$langs->trans("NewLoan"), 2, Globals::$user->rights->loan->write);
                            $newmenu->add(BASE_URI . "?controller=loan&method=card&leftmenu=tax_loan&action=create", Globals::$langs->trans("NewLoan"), 2, Globals::$user->rights->loan->write);
//$newmenu->add("/loan/payment/list.php?leftmenu=tax_loan",Globals::$langs->trans("Payments"),2,Globals::$user->rights->loan->read);
                        }
                    }

// Various payment
                    if (!empty(Globals::$conf->banque->enabled) && empty(Globals::$conf->global->BANK_USE_OLD_VARIOUS_PAYMENT)) {
                        Globals::$langs->load("banks");
//$newmenu->add("/compta/bank/various_payment/list.php?leftmenu=tax_various&mainmenu=billing", Globals::$langs->trans("MenuVariousPayment"), 1, Globals::$user->rights->banque->lire, '', $mainmenu, 'tax_various');
                        $newmenu->add(BASE_URI . "?controller=compta/bank/various_payment&method=list&leftmenu=tax_various&mainmenu=billing", Globals::$langs->trans("MenuVariousPayment"), 1, Globals::$user->rights->banque->lire, '', $mainmenu, 'tax_various');
                        if ($usemenuhider || empty($leftmenu) || preg_match('/^tax_various/i', $leftmenu)) {
//$newmenu->add("/compta/bank/various_payment/card.php?leftmenu=tax_various&action=create", Globals::$langs->trans("New"), 2, Globals::$user->rights->banque->modifier);
//$newmenu->add("/compta/bank/various_payment/list.php?leftmenu=tax_various", Globals::$langs->trans("List"), 2, Globals::$user->rights->banque->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/bank/various_payment&method=card&leftmenu=tax_various&action=create", Globals::$langs->trans("New"), 2, Globals::$user->rights->banque->modifier);
                            $newmenu->add(BASE_URI . "?controller=compta/bank/various_payment&method=list&leftmenu=tax_various", Globals::$langs->trans("List"), 2, Globals::$user->rights->banque->lire);
                        }
                    }
                }
            }

            /*
             * Menu COMPTA-FINANCIAL
             */
            if ($mainmenu == 'accountancy') {
                Globals::$langs->load("companies");

// Accounting Expert
                if (!empty(Globals::$conf->accounting->enabled)) {
                    Globals::$langs->load("accountancy");

                    $permtoshowmenu = (!empty(Globals::$conf->accounting->enabled) || Globals::$user->rights->accounting->bind->write || Globals::$user->rights->compta->resultat->lire);
//$newmenu->add("/accountancy/index.php?leftmenu=accountancy", Globals::$langs->trans("MenuAccountancy"), 0, $permtoshowmenu, '', $mainmenu, 'accountancy');
                    $newmenu->add(BASE_URI . "?controller=accountancy&method=index&leftmenu=accountancy", Globals::$langs->trans("MenuAccountancy"), 0, $permtoshowmenu, '', $mainmenu, 'accountancy');

// Chart of account
//$newmenu->add("/accountancy/index.php?leftmenu=accountancy_admin", Globals::$langs->trans("Setup"), 1, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin', 1);
                    $newmenu->add(BASE_URI . "?controller=accountancy&method=index&leftmenu=accountancy_admin", Globals::$langs->trans("Setup"), 1, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin', 1);
                    if ($usemenuhider || empty($leftmenu) || preg_match('/accountancy_admin/', $leftmenu)) {
                        /*
                          $newmenu->add("/accountancy/admin/index.php?mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("General"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_general', 10);
                          $newmenu->add("/accountancy/admin/journals_list.php?id=35&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("AccountingJournals"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_journal', 20);
                          $newmenu->add("/accountancy/admin/accountmodel.php?id=31&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("Pcg_version"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_chartmodel', 30);
                          $newmenu->add("/accountancy/admin/account.php?mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("Chartofaccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_chart', 40);
                          $newmenu->add("/accountancy/admin/categories_list.php?id=32&search_country_id=" . $mysoc->country_id . "&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("AccountingCategory"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_chart', 41);
                          $newmenu->add("/accountancy/admin/defaultaccounts.php?mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuDefaultAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_default', 50);
                         */
                        $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=index&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("General"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_general', 10);
                        $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=journals_list&id=35&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("AccountingJournals"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_journal', 20);
                        $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=accountmodel&id=31&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("Pcg_version"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_chartmodel', 30);
                        $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=account&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("Chartofaccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_chart', 40);
                        $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=categories_list&id=32&search_country_id=" . $mysoc->country_id . "&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("AccountingCategory"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_chart', 41);
                        $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=defaultaccounts&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuDefaultAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_default', 50);
                        if (!empty(Globals::$conf->banque->enabled)) {
//$newmenu->add("/compta/bank/list.php?mainmenu=accountancy&leftmenu=accountancy_admin&search_status=-1", Globals::$langs->trans("MenuBankAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_bank', 51);
                            $newmenu->add(BASE_URI . "?controller=compta/bank&method=list&mainmenu=accountancy&leftmenu=accountancy_admin&search_status=-1", Globals::$langs->trans("MenuBankAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_bank', 51);
                        }
                        if (!empty(Globals::$conf->facture->enabled) || !empty(Globals::$conf->fournisseur->enabled)) {
//$newmenu->add("/admin/dict.php?id=10&from=accountancy&search_country_id=" . $mysoc->country_id . "&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuVatAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_default', 52);
                            $newmenu->add(BASE_URI . "?controller=admin&method=dict&id=10&from=accountancy&search_country_id=" . $mysoc->country_id . "&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuVatAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_default', 52);
                        }
                        if (!empty(Globals::$conf->tax->enabled)) {
//$newmenu->add("/admin/dict.php?id=7&from=accountancy&search_country_id=" . $mysoc->country_id . "&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuTaxAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_default', 53);
                            $newmenu->add(BASE_URI . "?controller=admin&method=dict&id=7&from=accountancy&search_country_id=" . $mysoc->country_id . "&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuTaxAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_default', 53);
                        }
                        if (!empty(Globals::$conf->expensereport->enabled)) {
//$newmenu->add("/admin/dict.php?id=17&from=accountancy&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuExpenseReportAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_default', 54);
                            $newmenu->add(BASE_URI . "?controller=admin&method=dict&id=17&from=accountancy&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuExpenseReportAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_default', 54);
                        }
//$newmenu->add("/accountancy/admin/productaccount.php?mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuProductsAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_product', 55);
//$newmenu->add("/accountancy/admin/export.php?mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("ExportOptions"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_export', 60);
                        $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=productaccount&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("MenuProductsAccounts"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_product', 55);
                        $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=export&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("ExportOptions"), 2, Globals::$user->rights->accounting->chartofaccount, '', $mainmenu, 'accountancy_admin_export', 60);

// Fiscal year
                        if (Globals::$conf->global->MAIN_FEATURES_LEVEL > 1) {
// Not yet used. In a future will lock some periods.
//$newmenu->add("/accountancy/admin/fiscalyear.php?mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("FiscalPeriod"), 2, Globals::$user->rights->accounting->fiscalyear, '', $mainmenu, 'fiscalyear');
                            $newmenu->add(BASE_URI . "?controller=accountancy/admin&method=fiscalyear&mainmenu=accountancy&leftmenu=accountancy_admin", Globals::$langs->trans("FiscalPeriod"), 2, Globals::$user->rights->accounting->fiscalyear, '', $mainmenu, 'fiscalyear');
                        }
                    }

// Binding
                    if (!empty(Globals::$conf->facture->enabled)) {
//$newmenu->add("/accountancy/customer/index.php?leftmenu=accountancy_dispatch_customer&mainmenu=accountancy", Globals::$langs->trans("CustomersVentilation"), 1, Globals::$user->rights->accounting->bind->write, '', $mainmenu, 'dispatch_customer');
                        $newmenu->add(BASE_URI . "?controller=accountancy/customer&method=index&leftmenu=accountancy_dispatch_customer&mainmenu=accountancy", Globals::$langs->trans("CustomersVentilation"), 1, Globals::$user->rights->accounting->bind->write, '', $mainmenu, 'dispatch_customer');
                        if ($usemenuhider || empty($leftmenu) || preg_match('/accountancy_dispatch_customer/', $leftmenu)) {
//$newmenu->add("/accountancy/customer/list.php?mainmenu=accountancy&leftmenu=accountancy_dispatch_customer", Globals::$langs->trans("ToBind"), 2, Globals::$user->rights->accounting->bind->write);
//$newmenu->add("/accountancy/customer/lines.php?mainmenu=accountancy&leftmenu=accountancy_dispatch_customer", Globals::$langs->trans("Binded"), 2, Globals::$user->rights->accounting->bind->write);
                            $newmenu->add(BASE_URI . "?controller=accountancy/customer&method=list&mainmenu=accountancy&leftmenu=accountancy_dispatch_customer", Globals::$langs->trans("ToBind"), 2, Globals::$user->rights->accounting->bind->write);
                            $newmenu->add(BASE_URI . "?controller=accountancy/customer&method=lines&mainmenu=accountancy&leftmenu=accountancy_dispatch_customer", Globals::$langs->trans("Binded"), 2, Globals::$user->rights->accounting->bind->write);
                        }
                    }
                    if (!empty(Globals::$conf->supplier_invoice->enabled)) {
//$newmenu->add("/accountancy/supplier/index.php?leftmenu=accountancy_dispatch_supplier&mainmenu=accountancy", Globals::$langs->trans("SuppliersVentilation"), 1, Globals::$user->rights->accounting->bind->write, '', $mainmenu, 'dispatch_supplier');
                        $newmenu->add(BASE_URI . "?controller=accountancy/supplier&method=index&leftmenu=accountancy_dispatch_supplier&mainmenu=accountancy", Globals::$langs->trans("SuppliersVentilation"), 1, Globals::$user->rights->accounting->bind->write, '', $mainmenu, 'dispatch_supplier');
                        if ($usemenuhider || empty($leftmenu) || preg_match('/accountancy_dispatch_supplier/', $leftmenu)) {
//$newmenu->add("/accountancy/supplier/list.php?mainmenu=accountancy&leftmenu=accountancy_dispatch_supplier", Globals::$langs->trans("ToBind"), 2, Globals::$user->rights->accounting->bind->write);
//$newmenu->add("/accountancy/supplier/lines.php?mainmenu=accountancy&leftmenu=accountancy_dispatch_supplier", Globals::$langs->trans("Binded"), 2, Globals::$user->rights->accounting->bind->write);
                            $newmenu->add(BASE_URI . "?controller=accountancy/supplier&method=list&mainmenu=accountancy&leftmenu=accountancy_dispatch_supplier", Globals::$langs->trans("ToBind"), 2, Globals::$user->rights->accounting->bind->write);
                            $newmenu->add(BASE_URI . "?controller=accountancy/supplier&method=lines&mainmenu=accountancy&leftmenu=accountancy_dispatch_supplier", Globals::$langs->trans("Binded"), 2, Globals::$user->rights->accounting->bind->write);
                        }
                    }

                    if (!empty(Globals::$conf->expensereport->enabled)) {
//$newmenu->add("/accountancy/expensereport/index.php?leftmenu=accountancy_dispatch_expensereport&mainmenu=accountancy", Globals::$langs->trans("ExpenseReportsVentilation"), 1, Globals::$user->rights->accounting->bind->write, '', $mainmenu, 'dispatch_expensereport');
                        $newmenu->add(BASE_URI . "?controller=accountancy/expensereport&method=index&leftmenu=accountancy_dispatch_expensereport&mainmenu=accountancy", Globals::$langs->trans("ExpenseReportsVentilation"), 1, Globals::$user->rights->accounting->bind->write, '', $mainmenu, 'dispatch_expensereport');
                        if ($usemenuhider || empty($leftmenu) || preg_match('/accountancy_dispatch_expensereport/', $leftmenu)) {
//$newmenu->add("/accountancy/expensereport/list.php?mainmenu=accountancy&leftmenu=accountancy_dispatch_expensereport", Globals::$langs->trans("ToBind"), 2, Globals::$user->rights->accounting->bind->write);
//$newmenu->add("/accountancy/expensereport/lines.php?mainmenu=accountancy&leftmenu=accountancy_dispatch_expensereport", Globals::$langs->trans("Binded"), 2, Globals::$user->rights->accounting->bind->write);
                            $newmenu->add(BASE_URI . "?controller=accountancy/expensereport&method=list&mainmenu=accountancy&leftmenu=accountancy_dispatch_expensereport", Globals::$langs->trans("ToBind"), 2, Globals::$user->rights->accounting->bind->write);
                            $newmenu->add(BASE_URI . "?controller=accountancy/expensereport&method=lines&mainmenu=accountancy&leftmenu=accountancy_dispatch_expensereport", Globals::$langs->trans("Binded"), 2, Globals::$user->rights->accounting->bind->write);
                        }
                    }

// Journals
                    if (!empty(Globals::$conf->accounting->enabled) && !empty(Globals::$user->rights->accounting->comptarapport->lire) && $mainmenu == 'accountancy') {
                        $newmenu->add('', Globals::$langs->trans("Journalization"), 1, Globals::$user->rights->accounting->comptarapport->lire);

// Multi journal
                        $sql = "SELECT rowid, code, label, nature";
                        $sql .= " FROM " . MAIN_DB_PREFIX . "accounting_journal";
                        $sql .= " WHERE entity = " . Globals::$conf->entity;
                        $sql .= " AND active = 1";
                        $sql .= " ORDER BY label DESC";

                        $resql = Config::$dbEngine->query($sql);
                        if ($resql) {
                            $numr = Config::$dbEngine->num_rows($resql);
                            $i = 0;

                            if ($numr > 0) {
                                while ($i < $numr) {
                                    $objp = Config::$dbEngine->fetch_object($resql);

                                    $nature = '';

// Must match array $sourceList defined into journals_list.php
                                    if ($objp->nature == 2 && !empty(Globals::$conf->facture->enabled))
                                        $nature = "sells";
                                    if ($objp->nature == 3 && !empty(Globals::$conf->fournisseur->enabled))
                                        $nature = "purchases";
                                    if ($objp->nature == 4 && !empty(Globals::$conf->banque->enabled))
                                        $nature = "bank";
                                    if ($objp->nature == 5 && !empty(Globals::$conf->expensereport->enabled))
                                        $nature = "expensereports";
                                    if ($objp->nature == 1)
                                        $nature = "various";
                                    if ($objp->nature == 8)
                                        $nature = "inventory";
                                    if ($objp->nature == 9)
                                        $nature = "hasnew";

// To enable when page exists
                                    if (empty(Globals::$conf->global->ACCOUNTANCY_SHOW_DEVELOP_JOURNAL)) {
                                        if ($nature == 'various' || $nature == 'hasnew' || $nature == 'inventory')
                                            $nature = '';
                                    }

                                    if ($nature) {
                                        Globals::$langs->load('accountancy');
                                        $journallabel = Globals::$langs->transnoentities($objp->label); // Labels in this table are set by loading llx_accounting_abc.sql. Label can be 'ACCOUNTING_SELL_JOURNAL', 'InventoryJournal', ...
//$newmenu->add('/accountancy/journal/' . $nature . 'journal.php?mainmenu=accountancy&leftmenu=accountancy_journal&id_journal=' . $objp->rowid, $journallabel, 2, Globals::$user->rights->accounting->comptarapport->lire);
                                        $newmenu->add(BASE_URI . '?controller=accountancy/journal&method=' . $nature . 'journal&mainmenu=accountancy&leftmenu=accountancy_journal&id_journal=' . $objp->rowid, $journallabel, 2, Globals::$user->rights->accounting->comptarapport->lire);
                                    }
                                    $i++;
                                }
                            } else {
// Should not happend. Entries are added
                                $newmenu->add('', Globals::$langs->trans("NoJournalDefined"), 2, Globals::$user->rights->accounting->comptarapport->lire);
                            }
                        } else
                            dol_print_error(Config::$dbEngine);
                        Config::$dbEngine->free($resql);
                    }

// General Ledger
//$newmenu->add("/accountancy/bookkeeping/list.php?mainmenu=accountancy&leftmenu=accountancy_generalledger", Globals::$langs->trans("Bookkeeping"), 1, Globals::$user->rights->accounting->mouvements->lire);
                    $newmenu->add(BASE_URI . "?controller=accountancy/bookkeeping&method=list&mainmenu=accountancy&leftmenu=accountancy_generalledger", Globals::$langs->trans("Bookkeeping"), 1, Globals::$user->rights->accounting->mouvements->lire);

// Balance
//$newmenu->add("/accountancy/bookkeeping/balance.php?mainmenu=accountancy&leftmenu=accountancy_balance", Globals::$langs->trans("AccountBalance"), 1, Globals::$user->rights->accounting->mouvements->lire);
                    $newmenu->add(BASE_URI . "?controller=accountancy/bookkeeping&method=balance&mainmenu=accountancy&leftmenu=accountancy_balance", Globals::$langs->trans("AccountBalance"), 1, Globals::$user->rights->accounting->mouvements->lire);

// Files
                    if (!empty(Globals::$conf->global->MAIN_FEATURES_LEVEL) && Globals::$conf->global->MAIN_FEATURES_LEVEL > 2) {
//$newmenu->add("/compta/compta-files.php?mainmenu=accountancy&leftmenu=accountancy_files", Globals::$langs->trans("AccountantFiles"), 1, Globals::$user->rights->accounting->mouvements->lire);
                        $newmenu->add(BASE_URI . "?controller=compta&method=compta-files&mainmenu=accountancy&leftmenu=accountancy_files", Globals::$langs->trans("AccountantFiles"), 1, Globals::$user->rights->accounting->mouvements->lire);
                    }

// Reports
                    Globals::$langs->load("compta");

//$newmenu->add("/compta/resultat/index.php?mainmenu=accountancy&leftmenu=accountancy_report", Globals::$langs->trans("Reportings"), 1, Globals::$user->rights->accounting->comptarapport->lire, '', $mainmenu, 'ca');
                    $newmenu->add(BASE_URI . "?controller=compta/resultat&method=index&mainmenu=accountancy&leftmenu=accountancy_report", Globals::$langs->trans("Reportings"), 1, Globals::$user->rights->accounting->comptarapport->lire, '', $mainmenu, 'ca');

                    if ($usemenuhider || empty($leftmenu) || preg_match('/accountancy_report/', $leftmenu)) {
//$newmenu->add("/compta/resultat/index.php?leftmenu=accountancy_report", Globals::$langs->trans("MenuReportInOut"), 2, Globals::$user->rights->accounting->comptarapport->lire);
//$newmenu->add("/compta/resultat/clientfourn.php?leftmenu=accountancy_report", Globals::$langs->trans("ByPredefinedAccountGroups"), 3, Globals::$user->rights->accounting->comptarapport->lire);
//$newmenu->add("/compta/resultat/result.php?leftmenu=accountancy_report", Globals::$langs->trans("ByPersonalizedAccountGroups"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/resultat&method=index&leftmenu=accountancy_report", Globals::$langs->trans("MenuReportInOut"), 2, Globals::$user->rights->accounting->comptarapport->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/resultat&method=clientfourn&leftmenu=accountancy_report", Globals::$langs->trans("ByPredefinedAccountGroups"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/resultat&method=result&leftmenu=accountancy_report", Globals::$langs->trans("ByPersonalizedAccountGroups"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                    }

                    $modecompta = 'CREANCES-DETTES';
                    if (!empty(Globals::$conf->accounting->enabled) && !empty(Globals::$user->rights->accounting->comptarapport->lire) && $mainmenu == 'accountancy')
                        $modecompta = 'BOOKKEEPING'; // Not yet implemented. Should be BOOKKEEPINGCOLLECTED
                    if ($modecompta) {
                        if ($usemenuhider || empty($leftmenu) || preg_match('/accountancy_report/', $leftmenu)) {
                            /*
                              $newmenu->add("/compta/stats/index.php?leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ReportTurnover"), 2, Globals::$user->rights->accounting->comptarapport->lire);
                              $newmenu->add("/compta/stats/casoc.php?leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByCompanies"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                              $newmenu->add("/compta/stats/cabyuser.php?leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByUsers"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                              $newmenu->add("/compta/stats/cabyprodserv.php?leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByProductsAndServices"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                              $newmenu->add("/compta/stats/byratecountry.php?leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByVatRate"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                             */
                            $newmenu->add(BASE_URI . "?controller=compta/stats&method=index&leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ReportTurnover"), 2, Globals::$user->rights->accounting->comptarapport->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/stats&method=casoc&leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByCompanies"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/stats&method=cabyuser&leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByUsers"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/stats&method=cabyprodserv&leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByProductsAndServices"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/stats&method=byratecountry&leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByVatRate"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                        }
                    }

                    $modecompta = 'RECETTES-DEPENSES';
//if (! empty(Globals::$conf->accounting->enabled) && ! empty(Globals::$user->rights->accounting->comptarapport->lire) && $mainmenu == 'accountancy') $modecompta='';	// Not yet implemented. Should be BOOKKEEPINGCOLLECTED
                    if ($modecompta) {
                        if ($usemenuhider || empty($leftmenu) || preg_match('/accountancy_report/', $leftmenu)) {
//$newmenu->add("/compta/stats/index.php?leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ReportTurnoverCollected"), 2, Globals::$user->rights->accounting->comptarapport->lire);
//$newmenu->add("/compta/stats/casoc.php?leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByCompanies"), 3, Globals::$user->rights->accounting->comptarapport->lire);
//$newmenu->add("/compta/stats/cabyuser.php?leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByUsers"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/stats&method=index&leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ReportTurnoverCollected"), 2, Globals::$user->rights->accounting->comptarapport->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/stats&method=casoc&leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByCompanies"), 3, Globals::$user->rights->accounting->comptarapport->lire);
                            $newmenu->add(BASE_URI . "?controller=compta/stats&method=cabyuser&leftmenu=accountancy_report&modecompta = " . $modecompta, Globals::$langs->trans("ByUsers"), 3, Globals::$user->rights->accounting->comptarapport->lire);
//$newmenu->add("/compta/stats/cabyprodserv.php?leftmenu=accountancy_report&modecompta = ".$modecompta, Globals::$langs->trans("ByProductsAndServices"),3,Globals::$user->rights->accounting->comptarapport->lire);
//$newmenu->add("/compta/stats/byratecountry.php?leftmenu=accountancy_report&modecompta = ".$modecompta, Globals::$langs->trans("ByVatRate"),3,Globals::$user->rights->accounting->comptarapport->lire);
                        }
                    }
                }

// Accountancy (simple)
                if (!empty(Globals::$conf->comptabilite->enabled)) {
                    Globals::$langs->load("compta");

// Bilan, resultats
//$newmenu->add("/compta/resultat/index.php?leftmenu=report&mainmenu=accountancy", Globals::$langs->trans("Reportings"), 0, Globals::$user->rights->compta->resultat->lire, '', $mainmenu, 'ca');
                    $newmenu->add(BASE_URI . "?controller=compta/resultat&method=index&leftmenu=report&mainmenu=accountancy", Globals::$langs->trans("Reportings"), 0, Globals::$user->rights->compta->resultat->lire, '', $mainmenu, 'ca');

                    if ($usemenuhider || empty($leftmenu) || preg_match('/report/', $leftmenu)) {
//$newmenu->add("/compta/resultat/index.php?leftmenu=report", Globals::$langs->trans("MenuReportInOut"), 1, Globals::$user->rights->compta->resultat->lire);
//$newmenu->add("/compta/resultat/clientfourn.php?leftmenu=report", Globals::$langs->trans("ByCompanies"), 2, Globals::$user->rights->compta->resultat->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/resultat&method=index&leftmenu=report", Globals::$langs->trans("MenuReportInOut"), 1, Globals::$user->rights->compta->resultat->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/resultat&method=clientfourn&leftmenu=report", Globals::$langs->trans("ByCompanies"), 2, Globals::$user->rights->compta->resultat->lire);
                        /* On verra ca avec module compabilite expert
                          $newmenu->add("/compta/resultat/compteres.php?leftmenu=report","Compte de resultat",2,Globals::$user->rights->compta->resultat->lire);
                          $newmenu->add("/compta/resultat/bilan.php?leftmenu=report","Bilan",2,Globals::$user->rights->compta->resultat->lire);
                         */
//$newmenu->add("/compta/stats/index.php?leftmenu=report", Globals::$langs->trans("ReportTurnover"), 1, Globals::$user->rights->compta->resultat->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/stats&method=index&leftmenu=report", Globals::$langs->trans("ReportTurnover"), 1, Globals::$user->rights->compta->resultat->lire);

                        /*
                          $newmenu->add("/compta/stats/cumul.php?leftmenu=report","Cumule",2,Globals::$user->rights->compta->resultat->lire);
                          if (! empty(Globals::$conf->propal->enabled)) {
                          $newmenu->add("/compta/stats/prev.php?leftmenu=report","Previsionnel",2,Globals::$user->rights->compta->resultat->lire);
                          $newmenu->add("/compta/stats/comp.php?leftmenu=report","Transforme",2,Globals::$user->rights->compta->resultat->lire);
                          }
                         */
//$newmenu->add("/compta/stats/casoc.php?leftmenu=report", Globals::$langs->trans("ByCompanies"), 2, Globals::$user->rights->compta->resultat->lire);
//$newmenu->add("/compta/stats/cabyuser.php?leftmenu=report", Globals::$langs->trans("ByUsers"), 2, Globals::$user->rights->compta->resultat->lire);
//$newmenu->add("/compta/stats/cabyprodserv.php?leftmenu=report", Globals::$langs->trans("ByProductsAndServices"), 2, Globals::$user->rights->compta->resultat->lire);
//$newmenu->add("/compta/stats/byratecountry.php?leftmenu=report", Globals::$langs->trans("ByVatRate"), 2, Globals::$user->rights->compta->resultat->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/stats&method=casoc&leftmenu=report", Globals::$langs->trans("ByCompanies"), 2, Globals::$user->rights->compta->resultat->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/stats&method=cabyuser&leftmenu=report", Globals::$langs->trans("ByUsers"), 2, Globals::$user->rights->compta->resultat->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/stats&method=cabyprodserv&leftmenu=report", Globals::$langs->trans("ByProductsAndServices"), 2, Globals::$user->rights->compta->resultat->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/stats&method=byratecountry&leftmenu=report", Globals::$langs->trans("ByVatRate"), 2, Globals::$user->rights->compta->resultat->lire);

// Journaux
//$newmenu->add("/compta/journal/sellsjournal.php?leftmenu=report", Globals::$langs->trans("SellsJournal"), 1, Globals::$user->rights->compta->resultat->lire, '', '', '', 50);
//$newmenu->add("/compta/journal/purchasesjournal.php?leftmenu=report", Globals::$langs->trans("PurchasesJournal"), 1, Globals::$user->rights->compta->resultat->lire, '', '', '', 51);
                        $newmenu->add(BASE_URI . "?controller=compta/journal&method=sellsjournal&leftmenu=report", Globals::$langs->trans("SellsJournal"), 1, Globals::$user->rights->compta->resultat->lire, '', '', '', 50);
                        $newmenu->add(BASE_URI . "?controller=compta/journal&method=purchasesjournal&leftmenu=report", Globals::$langs->trans("PurchasesJournal"), 1, Globals::$user->rights->compta->resultat->lire, '', '', '', 51);
                    }
//if ($leftmenu=="ca") $newmenu->add("/compta/journaux/index.php?leftmenu=ca",Globals::$langs->trans("Journaux"),1,Globals::$user->rights->compta->resultat->lire||Globals::$user->rights->accounting->comptarapport->lire);
                }

// Assets
                if (!empty(Globals::$conf->asset->enabled)) {
                    Globals::$langs->load("assets");
//$newmenu->add("/asset/list.php?leftmenu=asset&mainmenu=accountancy", Globals::$langs->trans("MenuAssets"), 0, Globals::$user->rights->asset->read, '', $mainmenu, 'asset');
//$newmenu->add("/asset/card.php?action=create", Globals::$langs->trans("MenuNewAsset"), 1, Globals::$user->rights->asset->write);
//$newmenu->add("/asset/list.php?leftmenu=asset&mainmenu=accountancy", Globals::$langs->trans("MenuListAssets"), 1, Globals::$user->rights->asset->read);
//$newmenu->add("/asset/type.php?leftmenu=asset_type", Globals::$langs->trans("MenuTypeAssets"), 1, Globals::$user->rights->asset->read, '', $mainmenu, 'asset_type');
                    $newmenu->add(BASE_URI . "?controller=asset&method=list&leftmenu=asset&mainmenu=accountancy", Globals::$langs->trans("MenuAssets"), 0, Globals::$user->rights->asset->read, '', $mainmenu, 'asset');
                    $newmenu->add(BASE_URI . "?controller=asset&method=card&action=create", Globals::$langs->trans("MenuNewAsset"), 1, Globals::$user->rights->asset->write);
                    $newmenu->add(BASE_URI . "?controller=asset&method=list&leftmenu=asset&mainmenu=accountancy", Globals::$langs->trans("MenuListAssets"), 1, Globals::$user->rights->asset->read);
                    $newmenu->add(BASE_URI . "?controller=asset&method=type&leftmenu=asset_type", Globals::$langs->trans("MenuTypeAssets"), 1, Globals::$user->rights->asset->read, '', $mainmenu, 'asset_type');
                    if ($usemenuhider || empty($leftmenu) || preg_match('/asset_type/', $leftmenu)) {
//$newmenu->add("/asset/type.php?leftmenu=asset_type&action=create", Globals::$langs->trans("MenuNewTypeAssets"), 2, Globals::$user->rights->asset->write);
//$newmenu->add("/asset/type.php?leftmenu=asset_type", Globals::$langs->trans("MenuListTypeAssets"), 2, Globals::$user->rights->asset->read);
                        $newmenu->add(BASE_URI . "?controller=asset&method=type&leftmenu=asset_type&action=create", Globals::$langs->trans("MenuNewTypeAssets"), 2, Globals::$user->rights->asset->write);
                        $newmenu->add(BASE_URI . "?controller=asset&mtehod=type&leftmenu=asset_type", Globals::$langs->trans("MenuListTypeAssets"), 2, Globals::$user->rights->asset->read);
                    }
                }
            }


            /*
             * Menu BANK
             */
            if ($mainmenu == 'bank') {
// Load translation files required by the page
                Globals::$langs->loadLangs(array("withdrawals", "banks", "bills", "categories"));

// Bank-Caisse
                if (!empty(Globals::$conf->banque->enabled)) {
                    /*
                      $newmenu->add("/compta/bank/list.php?leftmenu=bank&mainmenu=bank", Globals::$langs->trans("MenuBankCash"), 0, Globals::$user->rights->banque->lire, '', $mainmenu, 'bank');

                      $newmenu->add("/compta/bank/card.php?action=create", Globals::$langs->trans("MenuNewFinancialAccount"), 1, Globals::$user->rights->banque->configurer);
                      $newmenu->add("/compta/bank/list.php?leftmenu=bank&mainmenu=bank", Globals::$langs->trans("List"), 1, Globals::$user->rights->banque->lire, '', $mainmenu, 'bank');
                      $newmenu->add("/compta/bank/bankentries_list.php", Globals::$langs->trans("ListTransactions"), 1, Globals::$user->rights->banque->lire);
                      $newmenu->add("/compta/bank/budget.php", Globals::$langs->trans("ListTransactionsByCategory"), 1, Globals::$user->rights->banque->lire);

                      $newmenu->add("/compta/bank/transfer.php", Globals::$langs->trans("MenuBankInternalTransfer"), 1, Globals::$user->rights->banque->transfer);
                     */
                    $newmenu->add(BASE_URI . "?controller=compta/bank&method=list&leftmenu=bank&mainmenu=bank", Globals::$langs->trans("MenuBankCash"), 0, Globals::$user->rights->banque->lire, '', $mainmenu, 'bank');

                    $newmenu->add(BASE_URI . "?controller=compta/bank&method=card&action=create", Globals::$langs->trans("MenuNewFinancialAccount"), 1, Globals::$user->rights->banque->configurer);
                    $newmenu->add(BASE_URI . "?controller=compta/bank&method=list&leftmenu=bank&mainmenu=bank", Globals::$langs->trans("List"), 1, Globals::$user->rights->banque->lire, '', $mainmenu, 'bank');
                    $newmenu->add(BASE_URI . "?controller=compta/bank&method=bankentries_list", Globals::$langs->trans("ListTransactions"), 1, Globals::$user->rights->banque->lire);
                    $newmenu->add(BASE_URI . "?controller=compta/bank&method=budget", Globals::$langs->trans("ListTransactionsByCategory"), 1, Globals::$user->rights->banque->lire);

                    $newmenu->add(BASE_URI . "?controller=compta/bank&method=transfer", Globals::$langs->trans("MenuBankInternalTransfer"), 1, Globals::$user->rights->banque->transfer);
                }

                if (!empty(Globals::$conf->categorie->enabled)) {
                    Globals::$langs->load("categories");
//$newmenu->add("/categories/index.php?type = 5", Globals::$langs->trans("Rubriques"), 1, Globals::$user->rights->categorie->creer, '', $mainmenu, 'tags');
//$newmenu->add("/compta/bank/categ.php", Globals::$langs->trans("RubriquesTransactions"), 1, Globals::$user->rights->categorie->creer, '', $mainmenu, 'tags');
                    $newmenu->add(BASE_URI . "?controller=categories&method=index&type=5", Globals::$langs->trans("Rubriques"), 1, Globals::$user->rights->categorie->creer, '', $mainmenu, 'tags');
                    $newmenu->add(BASE_URI . "?controller=compta/bank&method=categ", Globals::$langs->trans("RubriquesTransactions"), 1, Globals::$user->rights->categorie->creer, '', $mainmenu, 'tags');
                }

// Prelevements
                if (!empty(Globals::$conf->prelevement->enabled)) {
//$newmenu->add("/compta/prelevement/index.php?leftmenu=withdraw&mainmenu=bank", Globals::$langs->trans("StandingOrders"), 0, Globals::$user->rights->prelevement->bons->lire, '', $mainmenu, 'withdraw');
                    $newmenu->add(BASE_URI . "?controller=compta/prelevement&method=index&leftmenu=withdraw&mainmenu=bank", Globals::$langs->trans("StandingOrders"), 0, Globals::$user->rights->prelevement->bons->lire, '', $mainmenu, 'withdraw');

                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "withdraw") {
//$newmenu->add("/compta/prelevement/demandes.php?status = 0&mainmenu=bank",Globals::$langs->trans("StandingOrderToProcess"),1,Globals::$user->rights->prelevement->bons->lire);

                        /*
                          $newmenu->add("/compta/prelevement/create.php?mainmenu=bank", Globals::$langs->trans("NewStandingOrder"), 1, Globals::$user->rights->prelevement->bons->creer);

                          $newmenu->add("/compta/prelevement/bons.php?mainmenu=bank", Globals::$langs->trans("WithdrawalsReceipts"), 1, Globals::$user->rights->prelevement->bons->lire);
                          $newmenu->add("/compta/prelevement/list.php?mainmenu=bank", Globals::$langs->trans("WithdrawalsLines"), 1, Globals::$user->rights->prelevement->bons->lire);
                          $newmenu->add("/compta/prelevement/rejets.php?mainmenu=bank", Globals::$langs->trans("Rejects"), 1, Globals::$user->rights->prelevement->bons->lire);
                          $newmenu->add("/compta/prelevement/stats.php?mainmenu=bank", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->prelevement->bons->lire);
                         */
                        $newmenu->add(BASE_URI . "?controller=compta/prelevement&method=create&mainmenu=bank", Globals::$langs->trans("NewStandingOrder"), 1, Globals::$user->rights->prelevement->bons->creer);

                        $newmenu->add(BASE_URI . "?controller=compta/prelevement&method=bons&mainmenu=bank", Globals::$langs->trans("WithdrawalsReceipts"), 1, Globals::$user->rights->prelevement->bons->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/prelevement&method=list&mainmenu=bank", Globals::$langs->trans("WithdrawalsLines"), 1, Globals::$user->rights->prelevement->bons->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/prelevement&method=rejets&mainmenu=bank", Globals::$langs->trans("Rejects"), 1, Globals::$user->rights->prelevement->bons->lire);
                        $newmenu->add(BASE_URI . "?controller=compta/prelevement&method=stats&mainmenu=bank", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->prelevement->bons->lire);

//$newmenu->add("/compta/prelevement/config.php",Globals::$langs->trans("Setup"),1,Globals::$user->rights->prelevement->bons->configurer);
                    }
                }

// Gestion cheques
                if (empty(Globals::$conf->global->BANK_DISABLE_CHECK_DEPOSIT) &&!empty(Globals::$conf->banque->enabled) && (!empty(Globals::$conf->facture->enabled) ||!empty(Globals::$conf->global->MAIN_MENU_CHEQUE_DEPOSIT_ON))) {
//$newmenu->add("/compta/paiement/cheque/index.php?leftmenu=checks&mainmenu=bank", Globals::$langs->trans("MenuChequeDeposits"), 0, Globals::$user->rights->banque->cheque, '', $mainmenu, 'checks');
                    $newmenu->add(BASE_URI . "?controller=compta/paiement/cheque&method=index&leftmenu=checks&mainmenu=bank", Globals::$langs->trans("MenuChequeDeposits"), 0, Globals::$user->rights->banque->cheque, '', $mainmenu, 'checks');
                    if (preg_match('/checks/', $leftmenu)) {
//$newmenu->add("/compta/paiement/cheque/card.php?leftmenu=checks_bis&action=new&mainmenu=bank", Globals::$langs->trans("NewChequeDeposit"), 1, Globals::$user->rights->banque->cheque);
//$newmenu->add("/compta/paiement/cheque/list.php?leftmenu=checks_bis&mainmenu=bank", Globals::$langs->trans("List"), 1, Globals::$user->rights->banque->cheque);
                        $newmenu->add(BASE_URI . "?controller=compta/paiement/cheque&method=card&leftmenu=checks_bis&action=new&mainmenu=bank", Globals::$langs->trans("NewChequeDeposit"), 1, Globals::$user->rights->banque->cheque);
                        $newmenu->add(BASE_URI . "?controller=compta/paiement/cheque&method=list&leftmenu=checks_bis&mainmenu=bank", Globals::$langs->trans("List"), 1, Globals::$user->rights->banque->cheque);
                    }
                }

// Cash Control
                if (!empty(Globals::$conf->takepos->enabled) || !empty(Globals::$conf->cashdesk->enabled)) {
                    $permtomakecashfence = (Globals::$user->rights->cashdesk->use || Globals::$user->rights->takepos->use);
//$newmenu->add("/compta/cashcontrol/cashcontrol_list.php?action=list", Globals::$langs->trans("POS"), 0, $permtomakecashfence, '', $mainmenu, 'cashcontrol');
//$newmenu->add("/compta/cashcontrol/cashcontrol_card.php?action=create", Globals::$langs->trans("NewCashFence"), 1, $permtomakecashfence);
//$newmenu->add("/compta/cashcontrol/cashcontrol_list.php?action=list", Globals::$langs->trans("List"), 1, $permtomakecashfence);
                    $newmenu->add(BASE_URI . "?controller=compta/cashcontrol&method=cashcontrol_list&action=list", Globals::$langs->trans("POS"), 0, $permtomakecashfence, '', $mainmenu, 'cashcontrol');
                    $newmenu->add(BASE_URI . "?controller=compta/cashcontrol&method=cashcontrol_card&action=create", Globals::$langs->trans("NewCashFence"), 1, $permtomakecashfence);
                    $newmenu->add(BASE_URI . "?controller=compta/cashcontrol&method=cashcontrol_list&action=list", Globals::$langs->trans("List"), 1, $permtomakecashfence);
                }
            }

            /*
             * Menu PRODUCTS-SERVICES
             */
            if ($mainmenu == 'products') {
// Products
                if (!empty(Globals::$conf->product->enabled)) {
//$newmenu->add("/product/index.php?leftmenu=product&type=0", Globals::$langs->trans("Products"), 0, Globals::$user->rights->produit->lire, '', $mainmenu, 'product');
//$newmenu->add("/product/card.php?leftmenu=product&action=create&type=0", Globals::$langs->trans("NewProduct"), 1, Globals::$user->rights->produit->creer);
//$newmenu->add("/product/list.php?leftmenu=product&type=0", Globals::$langs->trans("List"), 1, Globals::$user->rights->produit->lire);
                    $newmenu->add(BASE_URI . "?controller=product&method=index&leftmenu=product&type=0", Globals::$langs->trans("Products"), 0, Globals::$user->rights->produit->lire, '', $mainmenu, 'product');
                    $newmenu->add(BASE_URI . "?controller=product&method=card&leftmenu=product&action=create&type=0", Globals::$langs->trans("NewProduct"), 1, Globals::$user->rights->produit->creer);
                    $newmenu->add(BASE_URI . "?controller=product&method=list&leftmenu=product&type=0", Globals::$langs->trans("List"), 1, Globals::$user->rights->produit->lire);
                    if (!empty(Globals::$conf->stock->enabled)) {
//$newmenu->add("/product/reassort.php?type = 0", Globals::$langs->trans("Stocks"), 1, Globals::$user->rights->produit->lire && Globals::$user->rights->stock->lire);
                        $newmenu->add(BASE_URI . "?controller=product&method=reassort&type=0", Globals::$langs->trans("Stocks"), 1, Globals::$user->rights->produit->lire && Globals::$user->rights->stock->lire);
                    }
                    if (!empty(Globals::$conf->productbatch->enabled)) {
                        Globals::$langs->load("stocks");
//$newmenu->add("/product/reassortlot.php?type = 0", Globals::$langs->trans("StocksByLotSerial"), 1, Globals::$user->rights->produit->lire && Globals::$user->rights->stock->lire);
//$newmenu->add("/product/stock/productlot_list.php", Globals::$langs->trans("LotSerial"), 1, Globals::$user->rights->produit->lire && Globals::$user->rights->stock->lire);
                        $newmenu->add(BASE_URI . "?controller=product&method=reassortlot&type=0", Globals::$langs->trans("StocksByLotSerial"), 1, Globals::$user->rights->produit->lire && Globals::$user->rights->stock->lire);
                        $newmenu->add(BASE_URI . "?controller=product/stock&method=productlot_list", Globals::$langs->trans("LotSerial"), 1, Globals::$user->rights->produit->lire && Globals::$user->rights->stock->lire);
                    }
                    if (!empty(Globals::$conf->variants->enabled)) {
//$newmenu->add("/variants/list.php", Globals::$langs->trans("VariantAttributes"), 1, Globals::$user->rights->produit->lire);
                        $newmenu->add(BASE_URI . "?controller=variants&method=list", Globals::$langs->trans("VariantAttributes"), 1, Globals::$user->rights->produit->lire);
                    }
                    if (!empty(Globals::$conf->propal->enabled) || !empty(Globals::$conf->commande->enabled) || !empty(Globals::$conf->facture->enabled) || !empty(Globals::$conf->fournisseur->enabled) || !empty(Globals::$conf->supplier_proposal->enabled)) {
// $newmenu->add("/product/stats/card.php?id = all&leftmenu=stats&type=0", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->produit->lire && Globals::$user->rights->propale->lire);
                        $newmenu->add(BASE_URI . "?controller=product/stats&method=card&id=all&leftmenu=stats&type=0", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->produit->lire && Globals::$user->rights->propale->lire);
                    }

// Categories
                    if (!empty(Globals::$conf->categorie->enabled)) {
                        Globals::$langs->load("categories");
//$newmenu->add("/categories/index.php?leftmenu=cat&type=0", Globals::$langs->trans("Categories"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                        $newmenu->add(BASE_URI . "?controller=categories&method=index&leftmenu=cat&type=0", Globals::$langs->trans("Categories"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                        //if ($usemenuhider || empty($leftmenu) || $leftmenu=="cat") $newmenu->add("/categories/list.php", Globals::$langs->trans("List"), 1, Globals::$user->rights->categorie->lire);
                    }
                }

                // Services
                if (!empty(Globals::$conf->service->enabled)) {
                    //$newmenu->add("/product/index.php?leftmenu=service&type=1", Globals::$langs->trans("Services"), 0, Globals::$user->rights->service->lire, '', $mainmenu, 'service');
                    //$newmenu->add("/product/card.php?leftmenu=service&action=create&type=1", Globals::$langs->trans("NewService"), 1, Globals::$user->rights->service->creer);
                    //$newmenu->add("/product/list.php?leftmenu=service&type=1", Globals::$langs->trans("List"), 1, Globals::$user->rights->service->lire);
                    $newmenu->add(BASE_URI . "?controller=product&method=index&leftmenu=service&type=1", Globals::$langs->trans("Services"), 0, Globals::$user->rights->service->lire, '', $mainmenu, 'service');
                    $newmenu->add(BASE_URI . "?controller=product&method=card&leftmenu=service&action=create&type=1", Globals::$langs->trans("NewService"), 1, Globals::$user->rights->service->creer);
                    $newmenu->add(BASE_URI . "?controller=product&method=list&leftmenu=service&type=1", Globals::$langs->trans("List"), 1, Globals::$user->rights->service->lire);
                    if (!empty(Globals::$conf->propal->enabled) || !empty(Globals::$conf->commande->enabled) || !empty(Globals::$conf->facture->enabled) || !empty(Globals::$conf->fournisseur->enabled) || !empty(Globals::$conf->supplier_proposal->enabled)) {
                        //$newmenu->add("/product/stats/card.php?id = all&leftmenu=stats&type=1", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->service->lire && Globals::$user->rights->propale->lire);
                        $newmenu->add(BASE_URI . "?controller=product/stats&method=card&id=all&leftmenu=stats&type=1", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->service->lire && Globals::$user->rights->propale->lire);
                    }
                    // Categories
                    if (!empty(Globals::$conf->categorie->enabled)) {
                        Globals::$langs->load("categories");
                        //$newmenu->add("/categories/index.php?leftmenu=cat&type=0", Globals::$langs->trans("Categories"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                        $newmenu->add(BASE_URI . "?controller=categories&method=index&leftmenu=cat&type=0", Globals::$langs->trans("Categories"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                        //if ($usemenuhider || empty($leftmenu) || $leftmenu=="cat") $newmenu->add("/categories/list.php", Globals::$langs->trans("List"), 1, Globals::$user->rights->categorie->lire);
                    }
                }

                // Warehouse
                if (!empty(Globals::$conf->stock->enabled)) {
                    Globals::$langs->load("stocks");
                    /*
                      $newmenu->add("/product/stock/index.php?leftmenu=stock", Globals::$langs->trans("Warehouses"), 0, Globals::$user->rights->stock->lire, '', $mainmenu, 'stock');
                      $newmenu->add("/product/stock/card.php?action=create", Globals::$langs->trans("MenuNewWarehouse"), 1, Globals::$user->rights->stock->creer);
                      $newmenu->add("/product/stock/list.php", Globals::$langs->trans("List"), 1, Globals::$user->rights->stock->lire);
                      $newmenu->add("/product/stock/movement_list.php", Globals::$langs->trans("Movements"), 1, Globals::$user->rights->stock->mouvement->lire);

                      $newmenu->add("/product/stock/massstockmove.php", Globals::$langs->trans("MassStockTransferShort"), 1, Globals::$user->rights->stock->mouvement->creer);
                     */
                    $newmenu->add(BASE_URI . "?controller=product/stock&method=index&leftmenu=stock", Globals::$langs->trans("Warehouses"), 0, Globals::$user->rights->stock->lire, '', $mainmenu, 'stock');
                    $newmenu->add(BASE_URI . "?controller=product/stock&method=card&action=create", Globals::$langs->trans("MenuNewWarehouse"), 1, Globals::$user->rights->stock->creer);
                    $newmenu->add(BASE_URI . "?controller=product/stock&method=list", Globals::$langs->trans("List"), 1, Globals::$user->rights->stock->lire);
                    $newmenu->add(BASE_URI . "?controller=product/stock&method=movement_list", Globals::$langs->trans("Movements"), 1, Globals::$user->rights->stock->mouvement->lire);

                    $newmenu->add(BASE_URI . "?controller=product/stock&method=massstockmove", Globals::$langs->trans("MassStockTransferShort"), 1, Globals::$user->rights->stock->mouvement->creer);
                    if (Globals::$conf->supplier_order->enabled) {
                        //$newmenu->add("/product/stock/replenish.php", Globals::$langs->trans("Replenishment"), 1, Globals::$user->rights->stock->mouvement->creer && Globals::$user->rights->fournisseur->lire);
                        $newmenu->add(BASE_URI . "?controller=product/stock&method=replenish", Globals::$langs->trans("Replenishment"), 1, Globals::$user->rights->stock->mouvement->creer && Globals::$user->rights->fournisseur->lire);
                    }
                }

                // Inventory
                if (Globals::$conf->global->MAIN_FEATURES_LEVEL >= 2) {
                    if (!empty(Globals::$conf->stock->enabled)) {
                        Globals::$langs->load("stocks");
                        if (empty(Globals::$conf->global->MAIN_USE_ADVANCED_PERMS)) {
                            //$newmenu->add("/product/inventory/list.php?leftmenu=stock", Globals::$langs->trans("Inventory"), 0, Globals::$user->rights->stock->lire, '', $mainmenu, 'stock');
                            //$newmenu->add("/product/inventory/card.php?action=create", Globals::$langs->trans("NewInventory"), 1, Globals::$user->rights->stock->creer);
                            //$newmenu->add("/product/inventory/list.php", Globals::$langs->trans("List"), 1, Globals::$user->rights->stock->lire);
                            $newmenu->add(BASE_URI . "?controller=product/inventory&method=list&leftmenu=stock", Globals::$langs->trans("Inventory"), 0, Globals::$user->rights->stock->lire, '', $mainmenu, 'stock');
                            $newmenu->add(BASE_URI . "?controller=product/inventory&method=card&action=create", Globals::$langs->trans("NewInventory"), 1, Globals::$user->rights->stock->creer);
                            $newmenu->add(BASE_URI . "?controller=product/inventory&method=list", Globals::$langs->trans("List"), 1, Globals::$user->rights->stock->lire);
                        } else {
                            //$newmenu->add("/product/inventory/list.php?leftmenu=stock", Globals::$langs->trans("Inventory"), 0, Globals::$user->rights->stock->inventory_advance->read, '', $mainmenu, 'stock');
                            //$newmenu->add("/product/inventory/card.php?action=create", Globals::$langs->trans("NewInventory"), 1, Globals::$user->rights->stock->inventory_advance->write);
                            //$newmenu->add("/product/inventory/list.php", Globals::$langs->trans("List"), 1, Globals::$user->rights->stock->inventory_advance->read);
                            $newmenu->add(BASE_URI . "?controller=product/inventory&method=list&leftmenu=stock", Globals::$langs->trans("Inventory"), 0, Globals::$user->rights->stock->inventory_advance->read, '', $mainmenu, 'stock');
                            $newmenu->add(BASE_URI . "?controller=product/inventory&method=card&action=create", Globals::$langs->trans("NewInventory"), 1, Globals::$user->rights->stock->inventory_advance->write);
                            $newmenu->add(BASE_URI . "?controller=product/inventory&method=list", Globals::$langs->trans("List"), 1, Globals::$user->rights->stock->inventory_advance->read);
                        }
                    }
                }

                // Shipments
                if (!empty(Globals::$conf->expedition->enabled)) {
                    Globals::$langs->load("sendings");
                    //$newmenu->add("/expedition/index.php?leftmenu=sendings", Globals::$langs->trans("Shipments"), 0, Globals::$user->rights->expedition->lire, '', $mainmenu, 'sendings');
                    //$newmenu->add("/expedition/card.php?action=create2&leftmenu=sendings", Globals::$langs->trans("NewSending"), 1, Globals::$user->rights->expedition->creer);
                    //$newmenu->add("/expedition/list.php?leftmenu=sendings", Globals::$langs->trans("List"), 1, Globals::$user->rights->expedition->lire);
                    $newmenu->add(BASE_URI . "?controller=expedition&method=index&leftmenu=sendings", Globals::$langs->trans("Shipments"), 0, Globals::$user->rights->expedition->lire, '', $mainmenu, 'sendings');
                    $newmenu->add(BASE_URI . "?controller=expedition&method=card&action=create2&leftmenu=sendings", Globals::$langs->trans("NewSending"), 1, Globals::$user->rights->expedition->creer);
                    $newmenu->add(BASE_URI . "?controller=expedition&method=list&leftmenu=sendings", Globals::$langs->trans("List"), 1, Globals::$user->rights->expedition->lire);
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "sendings") {
                        //$newmenu->add("/expedition/list.php?leftmenu=sendings&viewstatut=0", Globals::$langs->trans("StatusSendingDraftShort"), 2, Globals::$user->rights->expedition->lire);
                        //$newmenu->add("/expedition/list.php?leftmenu=sendings&viewstatut=1", Globals::$langs->trans("StatusSendingValidatedShort"), 2, Globals::$user->rights->expedition->lire);
                        //$newmenu->add("/expedition/list.php?leftmenu=sendings&viewstatut=2", Globals::$langs->trans("StatusSendingProcessedShort"), 2, Globals::$user->rights->expedition->lire);
                        $newmenu->add(BASE_URI . "?controller=expedition&method=list&leftmenu=sendings&viewstatut=0", Globals::$langs->trans("StatusSendingDraftShort"), 2, Globals::$user->rights->expedition->lire);
                        $newmenu->add(BASE_URI . "?controller=expedition&method=list&leftmenu=sendings&viewstatut=1", Globals::$langs->trans("StatusSendingValidatedShort"), 2, Globals::$user->rights->expedition->lire);
                        $newmenu->add(BASE_URI . "?controller=expedition&method=list&leftmenu=sendings&viewstatut=2", Globals::$langs->trans("StatusSendingProcessedShort"), 2, Globals::$user->rights->expedition->lire);
                    }
                    //$newmenu->add("/expedition/stats/index.php?leftmenu=sendings", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->expedition->lire);
                    $newmenu->add(BASE_URI . "?controller=expedition/stats&method=index&leftmenu=sendings", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->expedition->lire);
                }

                // Receptions
                if (!empty(Globals::$conf->reception->enabled)) {
                    Globals::$langs->load("receptions");
                    //$newmenu->add("/reception/index.php?leftmenu=receptions", Globals::$langs->trans("Receptions"), 0, Globals::$user->rights->reception->lire, '', $mainmenu, 'receptions');
                    //$newmenu->add("/reception/card.php?action=create2&leftmenu=receptions", Globals::$langs->trans("NewReception"), 1, Globals::$user->rights->reception->creer);
                    //$newmenu->add("/reception/list.php?leftmenu=receptions", Globals::$langs->trans("List"), 1, Globals::$user->rights->reception->lire);
                    $newmenu->add(BASE_URI . "?controller=reception&method=index&leftmenu=receptions", Globals::$langs->trans("Receptions"), 0, Globals::$user->rights->reception->lire, '', $mainmenu, 'receptions');
                    $newmenu->add(BASE_URI . "?controller=reception&method=card&action=create2&leftmenu=receptions", Globals::$langs->trans("NewReception"), 1, Globals::$user->rights->reception->creer);
                    $newmenu->add(BASE_URI . "?controller=reception&method=list&leftmenu=receptions", Globals::$langs->trans("List"), 1, Globals::$user->rights->reception->lire);
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "receptions") {
                        //$newmenu->add("/reception/list.php?leftmenu=receptions&viewstatut=0", Globals::$langs->trans("StatusReceptionDraftShort"), 2, Globals::$user->rights->reception->lire);
                        $newmenu->add(BASE_URI . "?controller=reception&method=list&leftmenu=receptions&viewstatut=0", Globals::$langs->trans("StatusReceptionDraftShort"), 2, Globals::$user->rights->reception->lire);
                    }
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "receptions") {
                        //$newmenu->add("/reception/list.php?leftmenu=receptions&viewstatut=1", Globals::$langs->trans("StatusReceptionValidatedShort"), 2, Globals::$user->rights->reception->lire);
                        $newmenu->add(BASE_URI . "?controller=reception&method=list&leftmenu=receptions&viewstatut=1", Globals::$langs->trans("StatusReceptionValidatedShort"), 2, Globals::$user->rights->reception->lire);
                    }
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "receptions") {
                        //$newmenu->add("/reception/list.php?leftmenu=receptions&viewstatut=2", Globals::$langs->trans("StatusReceptionProcessedShort"), 2, Globals::$user->rights->reception->lire);
                        $newmenu->add(BASE_URI . "?controller=reception&method=list&leftmenu=receptions&viewstatut=2", Globals::$langs->trans("StatusReceptionProcessedShort"), 2, Globals::$user->rights->reception->lire);
                    }
                    //$newmenu->add("/reception/stats/index.php?leftmenu=receptions", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->reception->lire);
                    $newmenu->add(BASE_URI . "?controller=reception/stats&method=index&leftmenu=receptions", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->reception->lire);
                }
            }

            /*
             * Menu PROJECTS
             */
            if ($mainmenu == 'project') {
                if (!empty(Globals::$conf->projet->enabled)) {
                    Globals::$langs->load("projects");

                    $search_project_user = DolUtils::GETPOST('search_project_user', 'int');

                    $tmpentry = array(
                        'enabled' => (!empty(Globals::$conf->projet->enabled)),
                        'perms' => (!empty(Globals::$user->rights->projet->lire)),
                        'module' => 'projet',
                    );
                    $showmode = DolUtils::isVisibleToUserType($type_user, $tmpentry, $listofmodulesforexternal);

                    $titleboth = Globals::$langs->trans("LeadsOrProjects");
                    $titlenew = Globals::$langs->trans("NewLeadOrProject"); // Leads and opportunities by default
                    if (Globals::$conf->global->PROJECT_USE_OPPORTUNITIES == 0) {
                        $titleboth = Globals::$langs->trans("Projects");
                        $titlenew = Globals::$langs->trans("NewProject");
                    }
                    if (Globals::$conf->global->PROJECT_USE_OPPORTUNITIES == 2) { // 2 = leads only
                        $titleboth = Globals::$langs->trans("Leads");
                        $titlenew = Globals::$langs->trans("NewLead");
                    }

                    // Project assigned to user
                    //$newmenu->add("/projet/index.php?leftmenu=projects" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), $titleboth, 0, Globals::$user->rights->projet->lire, '', $mainmenu, 'projects');
                    //$newmenu->add("/projet/card.php?leftmenu=projects&action=create" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), $titlenew, 1, Globals::$user->rights->projet->creer);
                    $newmenu->add(BASE_URI . "?controller=projet&method=index&leftmenu=projects" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), $titleboth, 0, Globals::$user->rights->projet->lire, '', $mainmenu, 'projects');
                    $newmenu->add(BASE_URI . "?controller=projet&method=card&leftmenu=projects&action=create" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), $titlenew, 1, Globals::$user->rights->projet->creer);

                    if (Globals::$conf->global->PROJECT_USE_OPPORTUNITIES == 0) {
                        //$newmenu->add("/projet/list.php?leftmenu=projets" . ($search_project_user ? '&search_project_user=' . $search_project_user : '') . '&search_status=99', Globals::$langs->trans("List"), 1, $showmode, '', 'project', 'list');
                        $newmenu->add(BASE_URI . "?controller=projet&method=list&leftmenu=projets" . ($search_project_user ? '&search_project_user=' . $search_project_user : '') . '&search_status=99', Globals::$langs->trans("List"), 1, $showmode, '', 'project', 'list');
                    } elseif (Globals::$conf->global->PROJECT_USE_OPPORTUNITIES == 1) {
                        //$newmenu->add("/projet/list.php?leftmenu=projets" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("List"), 1, $showmode, '', 'project', 'list');
                        //$newmenu->add('/projet/list.php?mainmenu=project&leftmenu=list&search_opp_status=openedopp&search_status=99&contextpage=lead', Globals::$langs->trans("ListOpenLeads"), 2, $showmode);
                        //$newmenu->add('/projet/list.php?mainmenu=project&leftmenu=list&search_opp_status=notopenedopp&search_status=99&contextpage=project', Globals::$langs->trans("ListOpenProjects"), 2, $showmode);
                        $newmenu->add(BASE_URI . "?controller=projet&method=list&leftmenu=projets" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("List"), 1, $showmode, '', 'project', 'list');
                        $newmenu->add(BASE_URI . '?controller=projet&method=list&mainmenu=project&leftmenu=list&search_opp_status=openedopp&search_status=99&contextpage=lead', Globals::$langs->trans("ListOpenLeads"), 2, $showmode);
                        $newmenu->add(BASE_URI . '?controller=projet&method=list&mainmenu=project&leftmenu=list&search_opp_status=notopenedopp&search_status=99&contextpage=project', Globals::$langs->trans("ListOpenProjects"), 2, $showmode);
                    } elseif (Globals::$conf->global->PROJECT_USE_OPPORTUNITIES == 2) { // 2 = leads only
                        //$newmenu->add('/projet/list.php?mainmenu=project&leftmenu=list&search_opp_status=openedopp&search_status=99', Globals::$langs->trans("List"), 2, $showmode);
                        $newmenu->add(BASE_URI . '?controller=projet&method=list&mainmenu=project&leftmenu=list&search_opp_status=openedopp&search_status=99', Globals::$langs->trans("List"), 2, $showmode);
                    }

                    //$newmenu->add("/projet/stats/index.php?leftmenu=projects", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->projet->lire);
                    $newmenu->add(BASE_URI . "?controller=projet/stats&method=index&leftmenu=projects", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->projet->lire);

                    // Categories
                    if (!empty(Globals::$conf->categorie->enabled)) {
                        Globals::$langs->load("categories");
                        //$newmenu->add("/categories/index.php?leftmenu=cat&type=6", Globals::$langs->trans("Categories"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                        $newmenu->add(BASE_URI . "?controller=categories&method=index&leftmenu=cat&type=6", Globals::$langs->trans("Categories"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                    }

                    if (empty(Globals::$conf->global->PROJECT_HIDE_TASKS)) {
                        // Project affected to user
                        /*
                          $newmenu->add("/projet/activity/index.php?leftmenu=tasks" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("Activities"), 0, Globals::$user->rights->projet->lire);
                          $newmenu->add("/projet/tasks.php?leftmenu=tasks&action=create", Globals::$langs->trans("NewTask"), 1, Globals::$user->rights->projet->creer);
                          $newmenu->add("/projet/tasks/list.php?leftmenu=tasks" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("List"), 1, Globals::$user->rights->projet->lire);
                          $newmenu->add("/projet/tasks/stats/index.php?leftmenu=projects", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->projet->lire);

                          $newmenu->add("/projet/activity/perweek.php?leftmenu=tasks" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("NewTimeSpent"), 0, Globals::$user->rights->projet->lire);
                         */
                        $newmenu->add(BASE_URI . "?controller=projet/activity&method=index&leftmenu=tasks" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("Activities"), 0, Globals::$user->rights->projet->lire);
                        $newmenu->add(BASE_URI . "?controller=projet&method=tasks&leftmenu=tasks&action=create", Globals::$langs->trans("NewTask"), 1, Globals::$user->rights->projet->creer);
                        $newmenu->add(BASE_URI . "?controller=projet/tasks&method=list&leftmenu=tasks" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("List"), 1, Globals::$user->rights->projet->lire);
                        $newmenu->add(BASE_URI . "?controller=projet/tasks/stats&method=index&leftmenu=projects", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->projet->lire);

                        $newmenu->add(BASE_URI . "?controller=projet/activity&method=perweek&leftmenu=tasks" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("NewTimeSpent"), 0, Globals::$user->rights->projet->lire);
                    }
                }
            }

            /*
             * Menu HRM
             */
            if ($mainmenu == 'hrm') {
                // HRM module
                if (!empty(Globals::$conf->hrm->enabled)) {
                    Globals::$langs->load("hrm");

                    //$newmenu->add("/user/list.php?leftmenu=hrm&mode=employee", Globals::$langs->trans("Employees"), 0, Globals::$user->rights->hrm->employee->read, '', $mainmenu, 'hrm');
                    //$newmenu->add("/user/card.php?action=create&employee = 1", Globals::$langs->trans("NewEmployee"), 1, Globals::$user->rights->hrm->employee->write);
                    //$newmenu->add("/user/list.php?leftmenu=hrm&mode=employee&contextpage = employeelist", Globals::$langs->trans("List"), 1, Globals::$user->rights->hrm->employee->read);
                    $newmenu->add(BASE_URI . "?controller=user&method=list&leftmenu=hrm&mode=employee", Globals::$langs->trans("Employees"), 0, Globals::$user->rights->hrm->employee->read, '', $mainmenu, 'hrm');
                    $newmenu->add(BASE_URI . "?controller=user&method=card&action=create&employee = 1", Globals::$langs->trans("NewEmployee"), 1, Globals::$user->rights->hrm->employee->write);
                    $newmenu->add(BASE_URI . "?controller=user&method=list&leftmenu=hrm&mode=employee&contextpage = employeelist", Globals::$langs->trans("List"), 1, Globals::$user->rights->hrm->employee->read);
                }

                // Leave/Holiday/Vacation module
                if (!empty(Globals::$conf->holiday->enabled)) {
                    // Load translation files required by the page
                    Globals::$langs->loadLangs(array("holiday", "trips"));

                    //$newmenu->add("/holiday/list.php?leftmenu=hrm", Globals::$langs->trans("CPTitreMenu"), 0, Globals::$user->rights->holiday->read, '', $mainmenu, 'hrm');
                    //$newmenu->add("/holiday/card.php?action=request", Globals::$langs->trans("New"), 1, Globals::$user->rights->holiday->write);
                    //$newmenu->add("/holiday/list.php?leftmenu=hrm", Globals::$langs->trans("List"), 1, Globals::$user->rights->holiday->read);
                    $newmenu->add(BASE_URI . "?controller=holiday&method=list&leftmenu=hrm", Globals::$langs->trans("CPTitreMenu"), 0, Globals::$user->rights->holiday->read, '', $mainmenu, 'hrm');
                    $newmenu->add(BASE_URI . "?controller=holiday&method=card&action=request", Globals::$langs->trans("New"), 1, Globals::$user->rights->holiday->write);
                    $newmenu->add(BASE_URI . "?controller=holiday&method=list&leftmenu=hrm", Globals::$langs->trans("List"), 1, Globals::$user->rights->holiday->read);
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "hrm") {
                        /*
                          $newmenu->add("/holiday/list.php?search_statut = 1&leftmenu=hrm", Globals::$langs->trans("DraftCP"), 2, Globals::$user->rights->holiday->read);
                          $newmenu->add("/holiday/list.php?search_statut = 2&leftmenu=hrm", Globals::$langs->trans("ToReviewCP"), 2, Globals::$user->rights->holiday->read);
                          $newmenu->add("/holiday/list.php?search_statut = 3&leftmenu=hrm", Globals::$langs->trans("ApprovedCP"), 2, Globals::$user->rights->holiday->read);
                          $newmenu->add("/holiday/list.php?search_statut = 4&leftmenu=hrm", Globals::$langs->trans("CancelCP"), 2, Globals::$user->rights->holiday->read);
                          $newmenu->add("/holiday/list.php?search_statut = 5&leftmenu=hrm", Globals::$langs->trans("RefuseCP"), 2, Globals::$user->rights->holiday->read);
                         */
                        $newmenu->add(BASE_URI . "?controller=holiday&method=list&search_statut=1&leftmenu=hrm", Globals::$langs->trans("DraftCP"), 2, Globals::$user->rights->holiday->read);
                        $newmenu->add(BASE_URI . "?controller=holiday&method=list&search_statut=2&leftmenu=hrm", Globals::$langs->trans("ToReviewCP"), 2, Globals::$user->rights->holiday->read);
                        $newmenu->add(BASE_URI . "?controller=holiday&method=list&search_statut=3&leftmenu=hrm", Globals::$langs->trans("ApprovedCP"), 2, Globals::$user->rights->holiday->read);
                        $newmenu->add(BASE_URI . "?controller=holiday&method=list&search_statut=4&leftmenu=hrm", Globals::$langs->trans("CancelCP"), 2, Globals::$user->rights->holiday->read);
                        $newmenu->add(BASE_URI . "?controller=holiday&method=list&search_statut=5&leftmenu=hrm", Globals::$langs->trans("RefuseCP"), 2, Globals::$user->rights->holiday->read);
                    }
                    //$newmenu->add("/holiday/define_holiday.php?action=request", Globals::$langs->trans("MenuConfCP"), 1, Globals::$user->rights->holiday->read);
                    //$newmenu->add("/holiday/month_report.php", Globals::$langs->trans("MenuReportMonth"), 1, Globals::$user->rights->holiday->read_all);
                    //$newmenu->add("/holiday/view_log.php?action=request", Globals::$langs->trans("MenuLogCP"), 1, Globals::$user->rights->holiday->define_holiday);
                    $newmenu->add(BASE_URI . "?controller=holiday&method=define_holiday&action=request", Globals::$langs->trans("MenuConfCP"), 1, Globals::$user->rights->holiday->read);
                    $newmenu->add(BASE_URI . "?controller=holiday&method=month_report", Globals::$langs->trans("MenuReportMonth"), 1, Globals::$user->rights->holiday->read_all);
                    $newmenu->add(BASE_URI . "?controller=holiday&method=view_log&action=request", Globals::$langs->trans("MenuLogCP"), 1, Globals::$user->rights->holiday->define_holiday);
                }

                // Trips and expenses (old module)
                if (!empty(Globals::$conf->deplacement->enabled)) {
                    Globals::$langs->load("trips");
                    /*
                      $newmenu->add("/compta/deplacement/index.php?leftmenu=tripsandexpenses&mainmenu=hrm", Globals::$langs->trans("TripsAndExpenses"), 0, Globals::$user->rights->deplacement->lire, '', $mainmenu, 'tripsandexpenses');
                      $newmenu->add("/compta/deplacement/card.php?action=create&leftmenu=tripsandexpenses&mainmenu=hrm", Globals::$langs->trans("New"), 1, Globals::$user->rights->deplacement->creer);
                      $newmenu->add("/compta/deplacement/list.php?leftmenu=tripsandexpenses&mainmenu=hrm", Globals::$langs->trans("List"), 1, Globals::$user->rights->deplacement->lire);
                      $newmenu->add("/compta/deplacement/stats/index.php?leftmenu=tripsandexpenses&mainmenu=hrm", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->deplacement->lire);
                     */
                    $newmenu->add(BASE_URI . "?controller=compta/deplacement&method=index&leftmenu=tripsandexpenses&mainmenu=hrm", Globals::$langs->trans("TripsAndExpenses"), 0, Globals::$user->rights->deplacement->lire, '', $mainmenu, 'tripsandexpenses');
                    $newmenu->add(BASE_URI . "?controller=compta/deplacement&method=card&action=create&leftmenu=tripsandexpenses&mainmenu=hrm", Globals::$langs->trans("New"), 1, Globals::$user->rights->deplacement->creer);
                    $newmenu->add(BASE_URI . "?controller=compta/deplacement&method=list&leftmenu=tripsandexpenses&mainmenu=hrm", Globals::$langs->trans("List"), 1, Globals::$user->rights->deplacement->lire);
                    $newmenu->add(BASE_URI . "?controller=compta/deplacement/stats&method=index&leftmenu=tripsandexpenses&mainmenu=hrm", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->deplacement->lire);
                }

                // Expense report
                if (!empty(Globals::$conf->expensereport->enabled)) {
                    Globals::$langs->load("trips");
                    /*
                      $newmenu->add("/expensereport/index.php?leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("TripsAndExpenses"), 0, Globals::$user->rights->expensereport->lire, '', $mainmenu, 'expensereport');
                      $newmenu->add("/expensereport/card.php?action=create&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("New"), 1, Globals::$user->rights->expensereport->creer);
                      $newmenu->add("/expensereport/list.php?leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("List"), 1, Globals::$user->rights->expensereport->lire);
                      if ($usemenuhider || empty($leftmenu) || $leftmenu == "expensereport") {
                      $newmenu->add("/expensereport/list.php?search_status = 0&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Draft"), 2, Globals::$user->rights->expensereport->lire);
                      $newmenu->add("/expensereport/list.php?search_status = 2&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Validated"), 2, Globals::$user->rights->expensereport->lire);
                      $newmenu->add("/expensereport/list.php?search_status = 5&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Approved"), 2, Globals::$user->rights->expensereport->lire);
                      $newmenu->add("/expensereport/list.php?search_status = 6&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Paid"), 2, Globals::$user->rights->expensereport->lire);
                      $newmenu->add("/expensereport/list.php?search_status = 4&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Canceled"), 2, Globals::$user->rights->expensereport->lire);
                      $newmenu->add("/expensereport/list.php?search_status = 99&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Refused"), 2, Globals::$user->rights->expensereport->lire);
                      }
                      $newmenu->add("/expensereport/stats/index.php?leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->expensereport->lire);
                     */
                    $newmenu->add(BASE_URI . "?controller=expensereport&method=index&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("TripsAndExpenses"), 0, Globals::$user->rights->expensereport->lire, '', $mainmenu, 'expensereport');
                    $newmenu->add(BASE_URI . "?controller=expensereport&method=card&action=create&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("New"), 1, Globals::$user->rights->expensereport->creer);
                    $newmenu->add(BASE_URI . "?controller=expensereport&method=list&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("List"), 1, Globals::$user->rights->expensereport->lire);
                    if ($usemenuhider || empty($leftmenu) || $leftmenu == "expensereport") {
                        $newmenu->add(BASE_URI . "?controller=expensereport&method=list&search_status=0&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Draft"), 2, Globals::$user->rights->expensereport->lire);
                        $newmenu->add(BASE_URI . "?controller=expensereport&method=list&search_status=2&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Validated"), 2, Globals::$user->rights->expensereport->lire);
                        $newmenu->add(BASE_URI . "?controller=expensereport&method=list&search_status=5&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Approved"), 2, Globals::$user->rights->expensereport->lire);
                        $newmenu->add(BASE_URI . "?controller=expensereport&method=list&search_status=6&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Paid"), 2, Globals::$user->rights->expensereport->lire);
                        $newmenu->add(BASE_URI . "?controller=expensereport&method=list&search_status=4&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Canceled"), 2, Globals::$user->rights->expensereport->lire);
                        $newmenu->add(BASE_URI . "?controller=expensereport&method=list&search_status=99&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Refused"), 2, Globals::$user->rights->expensereport->lire);
                    }
                    $newmenu->add(BASE_URI . "?controller=expensereport/stats&method=index&leftmenu=expensereport&mainmenu=hrm", Globals::$langs->trans("Statistics"), 1, Globals::$user->rights->expensereport->lire);
                }

                if (!empty(Globals::$conf->projet->enabled)) {
                    if (empty(Globals::$conf->global->PROJECT_HIDE_TASKS)) {
                        Globals::$langs->load("projects");

                        $search_project_user = DolUtils::GETPOST('search_project_user', 'int');

                        //$newmenu->add("/projet/activity/perweek.php?leftmenu=tasks" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("NewTimeSpent"), 0, Globals::$user->rights->projet->lire);
                        $newmenu->add(BASE_URI . "?controller=projet/activity&method=perweek&leftmenu=tasks" . ($search_project_user ? '&search_project_user=' . $search_project_user : ''), Globals::$langs->trans("NewTimeSpent"), 0, Globals::$user->rights->projet->lire);
                    }
                }
            }


            /*
             * Menu TOOLS
             */
            if ($mainmenu == 'tools') {
                if (empty(Globals::$user->socid)) { // limit to internal users
                    Globals::$langs->load("mails");
                    //$newmenu->add("/admin/mails_templates.php?leftmenu=email_templates", Globals::$langs->trans("EMailTemplates"), 0, 1, '', $mainmenu, 'email_templates');
                    $newmenu->add(BASE_URI . "?controller=admin&method=mails_templates&leftmenu=email_templates", Globals::$langs->trans("EMailTemplates"), 0, 1, '', $mainmenu, 'email_templates');
                }

                if (!empty(Globals::$conf->mailing->enabled)) {
                    //$newmenu->add("/comm/mailing/index.php?leftmenu=mailing", Globals::$langs->trans("EMailings"), 0, Globals::$user->rights->mailing->lire, '', $mainmenu, 'mailing');
                    //$newmenu->add("/comm/mailing/card.php?leftmenu=mailing&action=create", Globals::$langs->trans("NewMailing"), 1, Globals::$user->rights->mailing->creer);
                    //$newmenu->add("/comm/mailing/list.php?leftmenu=mailing", Globals::$langs->trans("List"), 1, Globals::$user->rights->mailing->lire);
                    $newmenu->add(BASE_URI . "?controller=comm/mailing&method=index&leftmenu=mailing", Globals::$langs->trans("EMailings"), 0, Globals::$user->rights->mailing->lire, '', $mainmenu, 'mailing');
                    $newmenu->add(BASE_URI . "?controller=comm/mailing&method=card&leftmenu=mailing&action=create", Globals::$langs->trans("NewMailing"), 1, Globals::$user->rights->mailing->creer);
                    $newmenu->add(BASE_URI . "?controller=comm/mailing&method=list&leftmenu=mailing", Globals::$langs->trans("List"), 1, Globals::$user->rights->mailing->lire);
                }

                if (!empty(Globals::$conf->export->enabled)) {
                    Globals::$langs->load("exports");
                    //$newmenu->add("/exports/index.php?leftmenu=export", Globals::$langs->trans("FormatedExport"), 0, Globals::$user->rights->export->lire, '', $mainmenu, 'export');
                    //$newmenu->add("/exports/export.php?leftmenu=export", Globals::$langs->trans("NewExport"), 1, Globals::$user->rights->export->creer);
                    $newmenu->add(BASE_URI . "?controller=exports&methodindex&leftmenu=export", Globals::$langs->trans("FormatedExport"), 0, Globals::$user->rights->export->lire, '', $mainmenu, 'export');
                    $newmenu->add(BASE_URI . "?controller=exports&method=export&leftmenu=export", Globals::$langs->trans("NewExport"), 1, Globals::$user->rights->export->creer);
                    //$newmenu->add("/exports/export.php?leftmenu=export",Globals::$langs->trans("List"),1, Globals::$user->rights->export->lire);
                }

                if (!empty(Globals::$conf->import->enabled)) {
                    Globals::$langs->load("exports");
                    //$newmenu->add("/imports/index.php?leftmenu=import", Globals::$langs->trans("FormatedImport"), 0, Globals::$user->rights->import->run, '', $mainmenu, 'import');
                    //$newmenu->add("/imports/import.php?leftmenu=import", Globals::$langs->trans("NewImport"), 1, Globals::$user->rights->import->run);
                    $newmenu->add(BASE_URI . "?controller=imports&method=index&leftmenu=import", Globals::$langs->trans("FormatedImport"), 0, Globals::$user->rights->import->run, '', $mainmenu, 'import');
                    $newmenu->add(BASE_URI . "?controller=imports&method=import&leftmenu=import", Globals::$langs->trans("NewImport"), 1, Globals::$user->rights->import->run);
                }
            }

            /*
             * Menu MEMBERS
             */
            if ($mainmenu == 'members') {
                if (!empty(Globals::$conf->adherent->enabled)) {
                    // Load translation files required by the page
                    Globals::$langs->loadLangs(array("members", "compta"));
                    /*
                      $newmenu->add("/adherents/index.php?leftmenu=members&mainmenu=members", Globals::$langs->trans("Members"), 0, Globals::$user->rights->adherent->lire, '', $mainmenu, 'members');
                      $newmenu->add("/adherents/card.php?leftmenu=members&action=create", Globals::$langs->trans("NewMember"), 1, Globals::$user->rights->adherent->creer);
                      $newmenu->add("/adherents/list.php?leftmenu=members", Globals::$langs->trans("List"), 1, Globals::$user->rights->adherent->lire);
                      $newmenu->add("/adherents/list.php?leftmenu=members&statut=-1", Globals::$langs->trans("MenuMembersToValidate"), 2, Globals::$user->rights->adherent->lire);
                      $newmenu->add("/adherents/list.php?leftmenu=members&statut=1", Globals::$langs->trans("MenuMembersValidated"), 2, Globals::$user->rights->adherent->lire);
                      $newmenu->add("/adherents/list.php?leftmenu=members&statut=1&filter = uptodate", Globals::$langs->trans("MenuMembersUpToDate"), 2, Globals::$user->rights->adherent->lire);
                      $newmenu->add("/adherents/list.php?leftmenu=members&statut=1&filter = outofdate", Globals::$langs->trans("MenuMembersNotUpToDate"), 2, Globals::$user->rights->adherent->lire);
                      $newmenu->add("/adherents/list.php?leftmenu=members&statut=0", Globals::$langs->trans("MenuMembersResiliated"), 2, Globals::$user->rights->adherent->lire);
                      $newmenu->add("/adherents/stats/index.php?leftmenu=members", Globals::$langs->trans("MenuMembersStats"), 1, Globals::$user->rights->adherent->lire);

                      $newmenu->add("/adherents/cartes/carte.php?leftmenu=export", Globals::$langs->trans("MembersCards"), 1, Globals::$user->rights->adherent->export);
                      if (!empty(Globals::$conf->global->MEMBER_LINK_TO_HTPASSWDFILE) && ($usemenuhider || empty($leftmenu) || $leftmenu == 'none' || $leftmenu == "members" || $leftmenu == "export"))
                      $newmenu->add("/adherents/htpasswd.php?leftmenu=export", Globals::$langs->trans("Filehtpasswd"), 1, Globals::$user->rights->adherent->export);

                      if (!empty(Globals::$conf->categorie->enabled)) {
                      Globals::$langs->load("categories");
                      $newmenu->add("/categories/index.php?leftmenu=cat&type=3", Globals::$langs->trans("Categories"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                      }

                      $newmenu->add("/adherents/index.php?leftmenu=members&mainmenu=members", Globals::$langs->trans("Subscriptions"), 0, Globals::$user->rights->adherent->cotisation->lire);
                      $newmenu->add("/adherents/list.php?leftmenu=members&statut=-1, 1&mainmenu=members", Globals::$langs->trans("NewSubscription"), 1, Globals::$user->rights->adherent->cotisation->creer);
                      $newmenu->add("/adherents/subscription/list.php?leftmenu=members", Globals::$langs->trans("List"), 1, Globals::$user->rights->adherent->cotisation->lire);
                      $newmenu->add("/adherents/stats/index.php?leftmenu=members", Globals::$langs->trans("MenuMembersStats"), 1, Globals::$user->rights->adherent->lire);

                      //$newmenu->add("/adherents/index.php?leftmenu=export&mainmenu=members",Globals::$langs->trans("Tools"),0,Globals::$user->rights->adherent->export, '', $mainmenu, 'export');
                      //if (! empty(Globals::$conf->export->enabled) && ($usemenuhider || empty($leftmenu) || $leftmenu=="export")) $newmenu->add("/exports/index.php?leftmenu=export",Globals::$langs->trans("Datas"),1,Globals::$user->rights->adherent->export);
                      // Type
                      $newmenu->add("/adherents/type.php?leftmenu=setup&mainmenu=members", Globals::$langs->trans("MembersTypes"), 0, Globals::$user->rights->adherent->configurer, '', $mainmenu, 'setup');
                      $newmenu->add("/adherents/type.php?leftmenu=setup&mainmenu=members&action=create", Globals::$langs->trans("New"), 1, Globals::$user->rights->adherent->configurer);
                      $newmenu->add("/adherents/type.php?leftmenu=setup&mainmenu=members", Globals::$langs->trans("List"), 1, Globals::$user->rights->adherent->configurer);
                     */
                    $newmenu->add(BASE_URI . "?controller=adherents&method=index&leftmenu=members&mainmenu=members", Globals::$langs->trans("Members"), 0, Globals::$user->rights->adherent->lire, '', $mainmenu, 'members');
                    $newmenu->add(BASE_URI . "?controller=adherents&method=card&leftmenu=members&action=create", Globals::$langs->trans("NewMember"), 1, Globals::$user->rights->adherent->creer);
                    $newmenu->add(BASE_URI . "?controller=adherents&method=list&leftmenu=members", Globals::$langs->trans("List"), 1, Globals::$user->rights->adherent->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents&method=list&leftmenu=members&statut=-1", Globals::$langs->trans("MenuMembersToValidate"), 2, Globals::$user->rights->adherent->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents&method=list&leftmenu=members&statut=1", Globals::$langs->trans("MenuMembersValidated"), 2, Globals::$user->rights->adherent->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents&method=list&leftmenu=members&statut=1&filter = uptodate", Globals::$langs->trans("MenuMembersUpToDate"), 2, Globals::$user->rights->adherent->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents&method=list&leftmenu=members&statut=1&filter = outofdate", Globals::$langs->trans("MenuMembersNotUpToDate"), 2, Globals::$user->rights->adherent->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents&method=list&leftmenu=members&statut=0", Globals::$langs->trans("MenuMembersResiliated"), 2, Globals::$user->rights->adherent->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents/stats&method=index&leftmenu=members", Globals::$langs->trans("MenuMembersStats"), 1, Globals::$user->rights->adherent->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents/cartes&method=carte&leftmenu=export", Globals::$langs->trans("MembersCards"), 1, Globals::$user->rights->adherent->export);
                    if (!empty(Globals::$conf->global->MEMBER_LINK_TO_HTPASSWDFILE) && ($usemenuhider || empty($leftmenu) || $leftmenu == 'none' || $leftmenu == "members" || $leftmenu == "export")) {
                        $newmenu->add(BASE_URI . "?controller=adherents&method=htpasswd&leftmenu=export", Globals::$langs->trans("Filehtpasswd"), 1, Globals::$user->rights->adherent->export);
                    }
                    if (!empty(Globals::$conf->categorie->enabled)) {
                        Globals::$langs->load("categories");
                        $newmenu->add(BASE_URI . "?controller=categories&method=index&leftmenu=cat&type=3", Globals::$langs->trans("Categories"), 1, Globals::$user->rights->categorie->lire, '', $mainmenu, 'cat');
                    }
                    $newmenu->add(BASE_URI . "?controller=adherents&method=index&leftmenu=members&mainmenu=members", Globals::$langs->trans("Subscriptions"), 0, Globals::$user->rights->adherent->cotisation->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents&methodlist&leftmenu=members&statut=-1,1&mainmenu=members", Globals::$langs->trans("NewSubscription"), 1, Globals::$user->rights->adherent->cotisation->creer);
                    $newmenu->add(BASE_URI . "?controller=adherents/subscription&method=list&leftmenu=members", Globals::$langs->trans("List"), 1, Globals::$user->rights->adherent->cotisation->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents/stats&method=index&leftmenu=members", Globals::$langs->trans("MenuMembersStats"), 1, Globals::$user->rights->adherent->lire);
                    $newmenu->add(BASE_URI . "?controller=adherents&method=type&leftmenu=setup&mainmenu=members", Globals::$langs->trans("MembersTypes"), 0, Globals::$user->rights->adherent->configurer, '', $mainmenu, 'setup');
                    $newmenu->add(BASE_URI . "?controller=adherents&method=type&leftmenu=setup&mainmenu=members&action=create", Globals::$langs->trans("New"), 1, Globals::$user->rights->adherent->configurer);
                    $newmenu->add(BASE_URI . "?controller=adherents&method=type&leftmenu=setup&mainmenu=members", Globals::$langs->trans("List"), 1, Globals::$user->rights->adherent->configurer);
                }
            }

            // Add personalized menus and modules menus
            //var_dump($newmenu->liste);    //
            $menuArbo = new Menubase(Config::$dbEngine, 'eldy');
            $newmenu = $menuArbo->menuLeftCharger($newmenu, $mainmenu, $leftmenu, (empty(Globals::$user->societe_id) ? 0 : 1), 'eldy', $tabMenu);
            //var_dump($newmenu->liste);    //
            // We update newmenu for special dynamic menus
            if (!empty(Globals::$user->rights->banque->lire) && $mainmenu == 'bank') { // Entry for each bank account
                require_once DOL_DOCUMENT_ROOT . '/compta/bank/class/account.class.php';

                $sql = "SELECT rowid, label, courant, rappro";
                $sql .= " FROM " . MAIN_DB_PREFIX . "bank_account";
                $sql .= " WHERE entity = " . Globals::$conf->entity;
                $sql .= " AND clos = 0";
                $sql .= " ORDER BY label";

                $resql = Config::$dbEngine->query($sql);
                if ($resql) {
                    $numr = Config::$dbEngine->num_rows($resql);
                    $i = 0;

                    if ($numr > 0) {
                        //$newmenu->add('/compta/bank/list.php', Globals::$langs->trans("BankAccounts"), 0, Globals::$user->rights->banque->lire);
                        $newmenu->add(BASE_URI . '?controller=compta/bank?method=list', Globals::$langs->trans("BankAccounts"), 0, Globals::$user->rights->banque->lire);
                    }

                    while ($i < $numr) {
                        $objp = Config::$dbEngine->fetch_object($resql);
//$newmenu->add('/compta/bank/card.php?id=' . $objp->rowid, $objp->label, 1, Globals::$user->rights->banque->lire);
                        $newmenu->add(BASE_URI . '?controller=compta/bank&method=card&id=' . $objp->rowid, $objp->label, 1, Globals::$user->rights->banque->lire);
                        if ($objp->rappro && $objp->courant != Account::TYPE_CASH && empty($objp->clos)) {  // If not cash account and not closed and can be reconciliate
                            //$newmenu->add('/compta/bank/bankentries_list.php?action=reconcile&contextpage=banktransactionlist-' . $objp->rowid . '&account=' . $objp->rowid . '&id=' . $objp->rowid . '&search_conciliated=0', Globals::$langs->trans("Conciliate"), 2, Globals::$user->rights->banque->consolidate);
                            $newmenu->add(BASE_URI . '?controller=compta/bank&method=bankentries_list&action=reconcile&contextpage=banktransactionlist-' . $objp->rowid . '&account=' . $objp->rowid . '&id=' . $objp->rowid . '&search_conciliated=0', Globals::$langs->trans("Conciliate"), 2, Globals::$user->rights->banque->consolidate);
                        }
                        $i++;
                    }
                } else {
                    dol_print_error(Config::$dbEngine);
                }
                Config::$dbEngine->free($resql);
            }

            if (!empty(Globals::$conf->ftp->enabled) && $mainmenu == 'ftp') { // Entry for FTP
                $MAXFTP = 20;
                $i = 1;
                while ($i <= $MAXFTP) {
                    $paramkey = 'FTP_NAME_' . $i;
                    //print $paramkey;
                    if (!empty(Globals::$conf->global->$paramkey)) {
                        //$link = "/ftp/index.php?idmenu = " . $_SESSION["idmenu"] . "&numero_ftp = " . $i;
                        $link = BASE_URI . "?controller=ftp&method=index&idmenu=" . $_SESSION["idmenu"] . "&numero_ftp=" . $i;

                        $newmenu->add($link, dol_trunc(Globals::$conf->global->$paramkey, 24));
                    }
                    $i++;
                }
            }
        }

        //var_dump($tabMenu);    //
        //var_dump($newmenu->liste);
        // Build final $menu_array = $menu_array_before +$newmenu->liste + $menu_array_after
        //var_dump($menu_array_before);exit;
        //var_dump($menu_array_after);exit;
        $menu_array = $newmenu->liste;
        if (is_array($menu_array_before)) {
            $menu_array = array_merge($menu_array_before, $menu_array);
        }
        if (is_array($menu_array_after)) {
            $menu_array = array_merge($menu_array, $menu_array_after);
        }
        //var_dump($menu_array);exit;
        if (!is_array($menu_array)) {
            return 0;
        }

        // TODO Use the position property in menu_array to reorder the $menu_array
        //var_dump($menu_array);
        /* $new_menu_array = array();
          $level=0; $cusor=0; $position=0;
          $nbentry = count($menu_array);
          while (findNextEntryForLevel($menu_array, $cursor, $position, $level))
          {

          $cursor++;
          } */

        // Show menu
        $invert = empty(Globals::$conf->global->MAIN_MENU_INVERT) ? "" : "invert";
        if (empty($noout)) {
            $altok = 0;
            $blockvmenuopened = false;
            $lastlevel0 = '';
            $num = count($menu_array);
            for ($i = 0; $i < $num; $i++) {     // Loop on each menu entry
                $showmenu = true;
                if (!empty(Globals::$conf->global->MAIN_MENU_HIDE_UNAUTHORIZED) && empty($menu_array[$i]['enabled'])) {
                    $showmenu = false;
                }

                // Begin of new left menu block
                if (empty($menu_array[$i]['level']) && $showmenu) {
                    $altok++;
                    $blockvmenuopened = true;
                    $lastopened = true;
                    for ($j = ($i + 1); $j < $num; $j++) {
                        if (empty($menu_array[$j]['level'])) {
                            $lastopened = false;
                        }
                    }
                    if ($altok % 2 == 0) {
                        print '<div class="blockvmenu blockvmenuimpair' . $invert . ($lastopened ? ' blockvmenulast' : '') . ($altok == 1 ? ' blockvmenufirst' : '') . '">' . "\n";
                    } else {
                        print '<div class="blockvmenu blockvmenupair' . $invert . ($lastopened ? ' blockvmenulast' : '') . ($altok == 1 ? ' blockvmenufirst' : '') . '">' . "\n";
                    }
                }

                // Add tabulation
                $tabstring = '';
                $tabul = ($menu_array[$i]['level'] - 1);
                if ($tabul > 0) {
                    for ($j = 0; $j < $tabul; $j++) {
                        $tabstring .= '&nbsp;&nbsp;&nbsp;';
                    }
                }

                // $menu_array[$i]['url'] can be a relative url, a full external url. We try substitution
                $substitarray = array('__LOGIN__' => Globals::$user->login, '__USER_ID__' => Globals::$user->id, '__USER_SUPERVISOR_ID__' => Globals::$user->fk_user);
                $substitarray['__USERID__'] = Globals::$user->id; // For backward compatibility
                $menu_array[$i]['url'] = DolUtils::make_substitutions($menu_array[$i]['url'], $substitarray);

                $url = $shorturl = $shorturlwithoutparam = $menu_array[$i]['url'];
                if (!preg_match("/^(http:\/\/|https:\/\/)/i", $menu_array[$i]['url'])) {
                    $tmp = explode('?', $menu_array[$i]['url'], 2);
                    $url = $shorturl = $tmp[0];
                    $param = (isset($tmp[1]) ? $tmp[1] : '');    // params in url of the menu link
                    // Complete param to force leftmenu to '' to close open menu when we click on a link with no leftmenu defined.
                    if ((!preg_match('/mainmenu/i', $param)) && (!preg_match('/leftmenu/i', $param)) && !empty($menu_array[$i]['mainmenu'])) {
                        $param .= ($param ? '&' : '') . 'mainmenu=' . $menu_array[$i]['mainmenu'] . '&leftmenu=';
                    }
                    if ((!preg_match('/mainmenu/i', $param)) && (!preg_match('/leftmenu/i', $param)) && empty($menu_array[$i]['mainmenu'])) {
                        $param .= ($param ? '&' : '') . 'leftmenu=';
                    }
                    //$url.="idmenu = ".$menu_array[$i]['rowid'];    // Already done by menuLoad
                    $url = DolUtils::dol_buildpath($url, 1) . ($param ? '?' . $param : '');
                    $shorturlwithoutparam = $shorturl;
                    $shorturl = $shorturl . ($param ? '?' . $param : '');
                }


                print '<!-- Process menu entry with mainmenu=' . $menu_array[$i]['mainmenu'] . ', leftmenu=' . $menu_array[$i]['leftmenu'] . ', level=' . $menu_array[$i]['level'] . ' enabled=' . $menu_array[$i]['enabled'] . ', position=' . $menu_array[$i]['position'] . ' -->' . "\n";

                // Menu level 0
                if ($menu_array[$i]['level'] == 0) {
                    if ($menu_array[$i]['enabled']) {     // Enabled so visible
                        print '<div class="menu_titre">' . $tabstring;
                        if ($shorturlwithoutparam) {
                            print '<a class="vmenu" href="' . $url . '"' . ($menu_array[$i]['target'] ? ' target="' . $menu_array[$i]['target'] . '"' : '') . '>';
                        } else {
                            print '<span class="vmenu">';
                        }
                        print ($menu_array[$i]['prefix'] ? $menu_array[$i]['prefix'] : '') . $menu_array[$i]['titre'];
                        if ($shorturlwithoutparam) {
                            print '</a>';
                        } else {
                            print '</span>';
                        }
                        print '</div>' . "\n";
                        $lastlevel0 = 'enabled';
                    } else if ($showmenu) {                 // Not enabled but visible (so greyed)
                        print '<div class="menu_titre">' . $tabstring . '<font class="vmenudisabled">' . $menu_array[$i]['titre'] . '</font></div>' . "\n";
                        $lastlevel0 = 'greyed';
                    } else {
                        $lastlevel0 = 'hidden';
                    }
                    if ($showmenu) {
                        print '<div class="menu_top"></div>' . "\n";
                    }
                }

                // Menu level > 0
                if ($menu_array[$i]['level'] > 0) {
                    $cssmenu = '';
                    if ($menu_array[$i]['url']) {
                        $cssmenu = ' menu_contenu' . DolUtils::dol_string_nospecial(preg_replace('/\.php.*$/', '', $menu_array[$i]['url']));
                    }

                    if ($menu_array[$i]['enabled'] && $lastlevel0 == 'enabled') {     // Enabled so visible, except if parent was not enabled.
                        print '<div class="menu_contenu' . $cssmenu . '">' . $tabstring;
                        if ($shorturlwithoutparam) {
                            print '<a class="vsmenu" href="' . $url . '"' . ($menu_array[$i]['target'] ? ' target="' . $menu_array[$i]['target'] . '"' : '') . '>';
                        } else {
                            print '<span class="vsmenu">';
                        }
                        print $menu_array[$i]['titre'];
                        if ($shorturlwithoutparam) {
                            print '</a>';
                        } else {
                            print '</span>';
                        }
                        // If title is not pure text and contains a table, no carriage return added
                        if (!strstr($menu_array[$i]['titre'], '<table')) {
                            print '<br>';
                        }
                        print '</div>' . "\n";
                    } else if ($showmenu && $lastlevel0 == 'enabled') {       // Not enabled but visible (so greyed), except if parent was not enabled.
                        print '<div class="menu_contenu' . $cssmenu . '">' . $tabstring . '<font class="vsmenudisabled vsmenudisabledmargin">' . $menu_array[$i]['titre'] . '</font><br></div>' . "\n";
                    }
                }

                // If next is a new block or if there is nothing after
                if (empty($menu_array[$i + 1]['level'])) {               // End menu block
                    if ($showmenu) {
                        print '<div class="menu_end"></div>' . "\n";
                    }
                    if ($blockvmenuopened) {
                        print '</div>' . "\n";
                        $blockvmenuopened = false;
                    }
                }
            }

            if ($altok) {
                print '<div class="blockvmenuend"></div>';    // End menu block
            }
        }

        return count($menu_array);
    }
}
