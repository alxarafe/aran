<?php
/* Copyright (C) 2007-2009	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2009-2012	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2018       Frédéric France     <frederic.france@netlogic.fr>
 * Copyright (C) 2019       Alxarafe            <info@alxarafe.com>
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

use Alxarafe\Helpers\Config;
use Alixar\Helpers\Globals;
use Alixar\Helpers\DolUtils;

/**
 *  \file       htdocs/core/class/menubase.class.php
 *  \ingroup    core
 *  \brief      File of class to manage dynamic menu entries
 */

/**
 *  Class to manage menu entries
 */
class Menubase
{

    /**
     * @var DoliDB Database handler.
     */
    //public Globals::$db;

    /**
     * @var string Error code (or message)
     */
    public $error;

    /**
     * @var string[] Error codes (or messages)
     */
    public $errors = array();

    /**
     * @var int ID
     */
    public $id;
    public $menu_handler;
    public $module;
    public $type;
    public $mainmenu;

    /**
     * @var int ID
     */
    public $fk_menu;

    /**
     * @var string fk_mainmenu
     */
    public $fk_mainmenu;

    /**
     * @var string fk_leftmenu
     */
    public $fk_leftmenu;

    /**
     * @var int position
     */
    public $position;
    public $url;
    public $target;
    public $titre;
    //public Globals::$langs;
    public $level;
    public $leftmenu;  //<! Not used
    public $perms;
    public $enabled;
    //public $user;
    public $tms;

    /**
     * 	Constructor
     *
     *  @param		DoliDB		Globals::$db 		    Database handler
     *  @param     	string		$menu_handler	Menu handler
     */
    function __construct($menu_handler = '')
    {
        //Globals::$db = Globals::$db;
        $this->menu_handler = $menu_handler;
        return 1;
    }

    /**
     *      Create menu entry into database
     *
     *      @param      User	$user       User that create
     *      @return     int      			<0 if KO, Id of record if OK
     */
    function create($user = null)
    {
        //global $conf, Globals::$langs;
        // Clean parameters
        $this->menu_handler = trim($this->menu_handler);
        $this->module = trim($this->module);
        $this->type = trim($this->type);
        $this->mainmenu = trim($this->mainmenu);
        $this->leftmenu = trim($this->leftmenu);
        $this->fk_menu = (int) $this->fk_menu;          // If -1, fk_mainmenu and fk_leftmenu must be defined
        $this->fk_mainmenu = trim($this->fk_mainmenu);
        $this->fk_leftmenu = trim($this->fk_leftmenu);
        $this->position = (int) $this->position;
        $this->url = trim($this->url);
        $this->target = trim($this->target);
        $this->titre = trim($this->titre);
        $this->langs = trim($this->langs);
        $this->perms = trim($this->perms);
        $this->enabled = trim($this->enabled);
        $this->user = trim($this->user);
        if (empty($this->position)) {
            $this->position = 0;
        }
        if (!$this->level) {
            $this->level = 0;
        }

        // Check parameters
        if (empty($this->menu_handler)) {
            return -1;
        }

        // For PGSQL, we must first found the max rowid and use it as rowid in insert because postgresql
        // may use an already used value because its internal cursor does not increase when we do
        // an insert with a forced id.
        if (in_array(Globals::$db->type, array('pgsql'))) {
            $sql = "SELECT MAX(rowid) as maxrowid FROM " . MAIN_DB_PREFIX . "menu";
            $resqlrowid = Globals::$db->query($sql);
            if ($resqlrowid) {
                $obj = Globals::$db->fetch_object($resqlrowid);
                $maxrowid = $obj->maxrowid;

                // Max rowid can be empty if there is no record yet
                if (empty($maxrowid)) {
                    $maxrowid = 1;
                }

                $sql = "SELECT setval('" . MAIN_DB_PREFIX . "menu_rowid_seq', " . ($maxrowid) . ")";
                //print $sql; exit;
                $resqlrowidset = Globals::$db->query($sql);
                if (!$resqlrowidset) {
                    dol_print_error(Globals::$db);
                }
            } else {
                dol_print_error(Globals::$db);
            }
        }

        // Check that entry does not exists yet on key menu_handler-fk_menu-position-url-entity, to avoid errors with postgresql
        $sql = "SELECT count(*)";
        $sql .= " FROM " . MAIN_DB_PREFIX . "menu";
        $sql .= " WHERE menu_handler = '" . Globals::$db->escape($this->menu_handler) . "'";
        $sql .= " AND fk_menu = " . ((int) $this->fk_menu);
        $sql .= " AND position = " . ((int) $this->position);
        $sql .= " AND url = '" . Globals::$db->escape($this->url) . "'";
        $sql .= " AND entity = " . Globals::$conf->entity;

        $result = Globals::$db->query($sql);
        if ($result) {
            $row = Globals::$db->fetch_row($result);

            if ($row[0] == 0) {   // If not found
                // Insert request
                $sql = "INSERT INTO " . MAIN_DB_PREFIX . "menu(";
                $sql .= "menu_handler,";
                $sql .= "entity,";
                $sql .= "module,";
                $sql .= "type,";
                $sql .= "mainmenu,";
                $sql .= "leftmenu,";
                $sql .= "fk_menu,";
                $sql .= "fk_mainmenu,";
                $sql .= "fk_leftmenu,";
                $sql .= "position,";
                $sql .= "url,";
                $sql .= "target,";
                $sql .= "titre,";
                $sql .= "langs,";
                $sql .= "perms,";
                $sql .= "enabled,";
                $sql .= "usertype";
                $sql .= ") VALUES (";
                $sql .= " '" . Globals::$db->escape($this->menu_handler) . "',";
                $sql .= " '" . Globals::$db->escape(Globals::$conf->entity) . "',";
                $sql .= " '" . Globals::$db->escape($this->module) . "',";
                $sql .= " '" . Globals::$db->escape($this->type) . "',";
                $sql .= " " . ($this->mainmenu ? "'" . Globals::$db->escape($this->mainmenu) . "'" : "''") . ",";    // Can't be null
                $sql .= " " . ($this->leftmenu ? "'" . Globals::$db->escape($this->leftmenu) . "'" : "null") . ",";
                $sql .= " " . ((int) $this->fk_menu) . ",";
                $sql .= " " . ($this->fk_mainmenu ? "'" . Globals::$db->escape($this->fk_mainmenu) . "'" : "null") . ",";
                $sql .= " " . ($this->fk_leftmenu ? "'" . Globals::$db->escape($this->fk_leftmenu) . "'" : "null") . ",";
                $sql .= " " . ((int) $this->position) . ",";
                $sql .= " '" . Globals::$db->escape($this->url) . "',";
                $sql .= " '" . Globals::$db->escape($this->target) . "',";
                $sql .= " '" . Globals::$db->escape($this->titre) . "',";
                $sql .= " '" . Globals::$db->escape($this->langs) . "',";
                $sql .= " '" . Globals::$db->escape($this->perms) . "',";
                $sql .= " '" . Globals::$db->escape($this->enabled) . "',";
                $sql .= " '" . Globals::$db->escape($this->user) . "'";
                $sql .= ")";

                DolUtils::dol_syslog(get_class($this) . "::create", LOG_DEBUG);
                $resql = Globals::$db->query($sql);
                if ($resql) {
                    $this->id = Globals::$db->last_insert_id(MAIN_DB_PREFIX . "menu");
                    DolUtils::dol_syslog(get_class($this) . "::create record added has rowid=" . $this->id, LOG_DEBUG);

                    return $this->id;
                } else {
                    $this->error = "Error " . Globals::$db->lasterror();
                    return -1;
                }
            } else {
                DolUtils::dol_syslog(get_class($this) . "::create menu entry already exists", LOG_WARNING);
                $this->error = 'Error Menu entry already exists';
                return 0;
            }
        } else {
            return -1;
        }
    }

    /**
     *  Update menu entry into database.
     *
     *  @param	User	$user        	User that modify
     *  @param  int		$notrigger	    0=no, 1=yes (no update trigger)
     *  @return int 		        	<0 if KO, >0 if OK
     */
    function update($user = null, $notrigger = 0)
    {
        //global $conf, Globals::$langs;
        // Clean parameters
        $this->rowid = trim($this->rowid);
        $this->menu_handler = trim($this->menu_handler);
        $this->module = trim($this->module);
        $this->type = trim($this->type);
        $this->mainmenu = trim($this->mainmenu);
        $this->leftmenu = trim($this->leftmenu);
        $this->fk_menu = (int) $this->fk_menu;
        $this->fk_mainmenu = trim($this->fk_mainmenu);
        $this->fk_leftmenu = trim($this->fk_leftmenu);
        $this->position = (int) $this->position;
        $this->url = trim($this->url);
        $this->target = trim($this->target);
        $this->titre = trim($this->titre);
        $this->langs = trim($this->langs);
        $this->perms = trim($this->perms);
        $this->enabled = trim($this->enabled);
        $this->user = trim($this->user);

        // Check parameters
        // Put here code to add control on parameters values
        // Update request
        $sql = "UPDATE " . MAIN_DB_PREFIX . "menu SET";
        $sql .= " menu_handler='" . Globals::$db->escape($this->menu_handler) . "',";
        $sql .= " module='" . Globals::$db->escape($this->module) . "',";
        $sql .= " type='" . Globals::$db->escape($this->type) . "',";
        $sql .= " mainmenu='" . Globals::$db->escape($this->mainmenu) . "',";
        $sql .= " leftmenu='" . Globals::$db->escape($this->leftmenu) . "',";
        $sql .= " fk_menu=" . $this->fk_menu . ",";
        $sql .= " fk_mainmenu=" . ($this->fk_mainmenu ? "'" . Globals::$db->escape($this->fk_mainmenu) . "'" : "null") . ",";
        $sql .= " fk_leftmenu=" . ($this->fk_leftmenu ? "'" . Globals::$db->escape($this->fk_leftmenu) . "'" : "null") . ",";
        $sql .= " position=" . ($this->position > 0 ? $this->position : 0) . ",";
        $sql .= " url='" . Globals::$db->escape($this->url) . "',";
        $sql .= " target='" . Globals::$db->escape($this->target) . "',";
        $sql .= " titre='" . Globals::$db->escape($this->titre) . "',";
        $sql .= " langs='" . Globals::$db->escape($this->langs) . "',";
        $sql .= " perms='" . Globals::$db->escape($this->perms) . "',";
        $sql .= " enabled='" . Globals::$db->escape($this->enabled) . "',";
        $sql .= " usertype='" . Globals::$db->escape($this->user) . "'";
        $sql .= " WHERE rowid=" . $this->id;

        DolUtils::dol_syslog(get_class($this) . "::update", LOG_DEBUG);
        $resql = Globals::$db->query($sql);
        if (!$resql) {
            $this->error = "Error " . Globals::$db->lasterror();
            return -1;
        }

        return 1;
    }

    /**
     *   Load object in memory from database
     *
     *   @param		int		$id         Id object
     *   @param		User    $user       User that load
     *   @return	int         		<0 if KO, >0 if OK
     */
    function fetch($id, $user = null)
    {
        //global Globals::$langs;

        $sql = "SELECT";
        $sql .= " t.rowid,";
        $sql .= " t.menu_handler,";
        $sql .= " t.entity,";
        $sql .= " t.module,";
        $sql .= " t.type,";
        $sql .= " t.mainmenu,";
        $sql .= " t.leftmenu,";
        $sql .= " t.fk_menu,";
        $sql .= " t.fk_mainmenu,";
        $sql .= " t.fk_leftmenu,";
        $sql .= " t.position,";
        $sql .= " t.url,";
        $sql .= " t.target,";
        $sql .= " t.titre,";
        $sql .= " t.langs,";
        $sql .= " t.perms,";
        $sql .= " t.enabled,";
        $sql .= " t.usertype as user,";
        $sql .= " t.tms";
        $sql .= " FROM " . MAIN_DB_PREFIX . "menu as t";
        $sql .= " WHERE t.rowid = " . $id;

        DolUtils::dol_syslog(get_class($this) . "::fetch", LOG_DEBUG);
        $resql = Globals::$db->query($sql);
        if ($resql) {
            if (Globals::$db->num_rows($resql)) {
                $obj = Globals::$db->fetch_object($resql);

                $this->id = $obj->rowid;

                $this->menu_handler = $obj->menu_handler;
                $this->entity = $obj->entity;
                $this->module = $obj->module;
                $this->type = $obj->type;
                $this->mainmenu = $obj->mainmenu;
                $this->leftmenu = $obj->leftmenu;
                $this->fk_menu = $obj->fk_menu;
                $this->fk_mainmenu = $obj->fk_mainmenu;
                $this->fk_leftmenu = $obj->fk_leftmenu;
                $this->position = $obj->position;
                $this->url = $obj->url;
                $this->target = $obj->target;
                $this->titre = $obj->titre;
                $this->langs = $obj->langs;
                $this->perms = $obj->perms;
                $this->enabled = str_replace("\"", "'", $obj->enabled);
                $this->user = $obj->user;
                $this->tms = Globals::$db->jdate($obj->tms);
            }
            Globals::$db->free($resql);

            return 1;
        } else {
            $this->error = "Error " . Globals::$db->lasterror();
            return -1;
        }
    }

    /**
     *  Delete object in database
     *
     * 	@param	User	$user       User that delete
     * 	@return	int					<0 if KO, >0 if OK
     */
    function delete($user)
    {
        // global $conf, Globals::$langs;

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "menu";
        $sql .= " WHERE rowid=" . $this->id;

        DolUtils::dol_syslog(get_class($this) . "::delete", LOG_DEBUG);
        $resql = Globals::$db->query($sql);
        if (!$resql) {
            $this->error = "Error " . Globals::$db->lasterror();
            return -1;
        }

        return 1;
    }

    /**
     *  Initialise an instance with random values.
     *  Used to build previews or test instances.
     * 	id must be 0 if object instance is a specimen.
     *
     *  @return	void
     */
    function initAsSpecimen()
    {
        $this->id = 0;

        $this->menu_handler = 'all';
        $this->module = 'specimen';
        $this->type = 'top';
        $this->mainmenu = '';
        $this->fk_menu = '0';
        $this->position = '';
        $this->url = 'http://dummy';
        $this->target = '';
        $this->titre = 'Specimen menu';
        $this->langs = '';
        $this->level = '';
        $this->leftmenu = '';
        $this->perms = '';
        $this->enabled = '';
        $this->user = '';
        $this->tms = '';
    }

    /**
     * 	Load tabMenu array with top menu entries found into database.
     *
     * 	@param	string	$mymainmenu		Value for mainmenu to filter menu to load (always '')
     * 	@param	string	$myleftmenu		Value for leftmenu to filter menu to load (always '')
     * 	@param	int		$type_user		0=Menu for backoffice, 1=Menu for front office
     * 	@param	string	$menu_handler	Filter on name of menu_handler used (auguria, eldy...)
     * 	@param  array	$tabMenu       If array with menu entries already loaded, we put this array here (in most cases, it's empty)
     * 	@return	array					Return array with menu entries for top menu
     */
    function menuTopCharger($mymainmenu, $myleftmenu, $type_user, $menu_handler, &$tabMenu)
    {
        // global Globals::$langs, $user, $conf; // To export to DolUtils::dol_eval function
        // global $mainmenu, $leftmenu;  // To export to DolUtils::dol_eval function

        $mainmenu = $mymainmenu;  // To export to DolUtils::dol_eval function
        $leftmenu = $myleftmenu;  // To export to DolUtils::dol_eval function

        $newTabMenu = array();
        foreach ($tabMenu as $val) {
            if ($val['type'] == 'top') {
                $newTabMenu[] = $val;
            }
        }

        return $newTabMenu;
    }

    /**
     * 	Load entries found from database (and stored into $tabMenu) in $this->newmenu array.
     *  Warning: Entries in $tabMenu must have child after parent
     *
     * 	@param	Menu	$newmenu        Menu array to complete (in most cases, it's empty, may be already initialized with some menu manager like eldy)
     * 	@param	string	$mymainmenu		Value for mainmenu to filter menu to load (often $_SESSION["mainmenu"])
     * 	@param	string	$myleftmenu		Value for leftmenu to filter menu to load (always '')
     * 	@param	int		$type_user		0=Menu for backoffice, 1=Menu for front office
     * 	@param	string	$menu_handler	Filter on name of menu_handler used (auguria, eldy...)
     * 	@param  array	$tabMenu       Array with menu entries already loaded
     * 	@return Menu    		       	Menu array for particular mainmenu value or full tabArray
     */
    function menuLeftCharger($newmenu, $mymainmenu, $myleftmenu, $type_user, $menu_handler, &$tabMenu)
    {
        //global Globals::$langs, $user, $conf;  // To export to DolUtils::dol_eval function
        // global $mainmenu, $leftmenu;  // To export to DolUtils::dol_eval function

        $mainmenu = $mymainmenu;  // To export to DolUtils::dol_eval function
        $leftmenu = $myleftmenu;  // To export to DolUtils::dol_eval function
        // Detect what is top mainmenu id
        $menutopid = '';
        foreach ($tabMenu as $key => $val) {
            // Define menutopid of mainmenu
            if (empty($menutopid) && $val['type'] == 'top' && $val['mainmenu'] == $mainmenu) {
                $menutopid = $val['rowid'];
                break;
            }
        }

        // We initialize newmenu with first already found menu entries
        $this->newmenu = $newmenu;

        // Now complete $this->newmenu->list to add entries found into $tabMenu that are childs of mainmenu=$menutopid, using the fk_menu link that is int (old method)
        $this->recur($tabMenu, $menutopid, 1);

        // Now complete $this->newmenu->list when fk_menu value is -1 (left menu added by modules with no top menu)
        foreach ($tabMenu as $key => $val) {
            //var_dump($tabMenu);
            if ($val['fk_menu'] == -1 && $val['fk_mainmenu'] == $mainmenu) {    // We found a menu entry not linked to parent with good mainmenu
                //print 'Try to add menu (current is mainmenu='.$mainmenu.' leftmenu='.$leftmenu.') for '.join(',',$val).' fk_mainmenu='.$val['fk_mainmenu'].' fk_leftmenu='.$val['fk_leftmenu'].'<br>';
                //var_dump($this->newmenu->liste);exit;
                if (empty($val['fk_leftmenu'])) {
                    $this->newmenu->add($val['url'], $val['titre'], 0, $val['perms'], $val['target'], $val['mainmenu'], $val['leftmenu'], $val['position']);
                    //var_dump($this->newmenu->liste);
                } else {
                    // Search first menu with this couple (mainmenu,leftmenu)=(fk_mainmenu,fk_leftmenu)
                    $searchlastsub = 0;
                    $lastid = 0;
                    $nextid = 0;
                    $found = 0;
                    foreach ($this->newmenu->liste as $keyparent => $valparent) {
                        //var_dump($valparent);
                        if ($searchlastsub) {    // If we started to search for last submenu
                            if ($valparent['level'] >= $searchlastsub) {
                                $lastid = $keyparent;
                            }
                            if ($valparent['level'] < $searchlastsub) {
                                $nextid = $keyparent;
                                break;
                            }
                        }
                        if ($valparent['mainmenu'] == $val['fk_mainmenu'] && $valparent['leftmenu'] == $val['fk_leftmenu']) {
                            //print "We found parent: keyparent='.$keyparent.' - level=".$valparent['level'].' - '.join(',',$valparent).'<br>';
                            // Now we look to find last subelement of this parent (we add at end)
                            $searchlastsub = ($valparent['level'] + 1);
                            $lastid = $keyparent;
                            $found = 1;
                        }
                    }
                    //print 'We must insert menu entry between entry '.$lastid.' and '.$nextid.'<br>';
                    if ($found) {
                        $this->newmenu->insert($lastid, $val['url'], $val['titre'], $searchlastsub, $val['perms'], $val['target'], $val['mainmenu'], $val['leftmenu'], $val['position']);
                    } else {
                        DolUtils::dol_syslog("Error. Modules " . $val['module'] . " has defined a menu entry with a parent='fk_mainmenu=" . $val['fk_leftmenu'] . ",fk_leftmenu=" . $val['fk_leftmenu'] . "' and position=" . $val['position'] . '. The parent was not found. May be you forget it into your definition of menu, or may be the parent has a "position" that is after the child (fix field "position" of parent or child in this case).', LOG_WARNING);
                        //print "Parent menu not found !!<br>";
                    }
                }
            }
        }

        return $this->newmenu;
    }

    /**
     *  Load entries found in database into variable $tabMenu. Note that only "database menu entries" are loaded here, hardcoded will not be present into output.
     *
     *  @param	string	$mymainmenu     Value for mainmenu that defined mainmenu
     *  @param	string	$myleftmenu     Value for left that defined leftmenu
     *  @param  int		$type_user      Looks for menu entry for 0=Internal users, 1=External users
     *  @param  string	$menu_handler   Name of menu_handler used ('auguria', 'eldy'...)
     *  @param  array	$tabMenu        Array to store new entries found (in most cases, it's empty, but may be alreay filled)
     *  @return int     		        >0 if OK, <0 if KO
     */
    function menuLoad($mymainmenu, $myleftmenu, $type_user, $menu_handler, &$tabMenu)
    {
        //global Globals::$langs, $user, $conf; // To export to DolUtils::dol_eval function
        //global $mainmenu, $leftmenu; // To export to DolUtils::dol_eval function

        $menutopid = 0;
        $mainmenu = $mymainmenu;  // To export to DolUtils::dol_eval function
        $leftmenu = $myleftmenu;  // To export to DolUtils::dol_eval function

        $sql = "SELECT m.rowid, m.type, m.module, m.fk_menu, m.fk_mainmenu, m.fk_leftmenu, m.url, m.titre, m.langs, m.perms, m.enabled, m.target, m.mainmenu, m.leftmenu, m.position";
        $sql .= " FROM " . MAIN_DB_PREFIX . "menu as m";
        $sql .= " WHERE m.entity IN (0," . Globals::$conf->entity . ")";
        $sql .= " AND m.menu_handler IN ('" . $menu_handler . "','all')";
        if ($type_user == 0) {
            $sql .= " AND m.usertype IN (0,2)";
        }
        if ($type_user == 1) {
            $sql .= " AND m.usertype IN (1,2)";
        }
        $sql .= " ORDER BY m.position, m.rowid";
        //print $sql;
        //$tmp1=microtime(true);
        //print '>>> 1 0<br>';
        DolUtils::dol_syslog(get_class($this) . "::menuLoad mymainmenu=" . $mymainmenu . " myleftmenu=" . $myleftmenu . " type_user=" . $type_user . " menu_handler=" . $menu_handler . " tabMenu size=" . count($tabMenu) . "", LOG_DEBUG);
        $resql = Config::$dbEngine->select($sql);
        if (is_array($resql)) {
            $a = 0;
            $b = 0;
            foreach ($resql as $array) {
                //$objm = Globals::$db->fetch_object($resql);
                //$menu = json_decode(json_encode($array));
                $menu = $array;

                // Define $right
                $perms = true;
                if ($menu['perms']) {
                    $tmpcond = $menu['perms'];
                    if ($leftmenu == 'all') {
                        $tmpcond = preg_replace('/\$leftmenu\s*==\s*["\'a-zA-Z_]+/', '1==1', $tmpcond); // Force part of condition to true
                    }
                    $perms = DolUtils::verifCond($tmpcond);
                    //print "verifCond rowid=".$menu['rowid']." ".$tmpcond.":".$perms."<br>\n";
                }

                // Define $enabled
                $enabled = true;
                if ($menu['enabled']) {
                    $tmpcond = $menu['enabled'];
                    if ($leftmenu == 'all') {
                        $tmpcond = preg_replace('/\$leftmenu\s*==\s*["\'a-zA-Z_]+/', '1==1', $tmpcond); // Force part of condition to true
                    }
                    $enabled = DolUtils::verifCond($tmpcond);
                }

                // Define $title
                if ($enabled) {
                    $title = Globals::$langs->trans($menu['titre']);  // If $menu['titre'] start with $, a DolUtils::dol_eval is done.
                    //var_dump($title.'-'.$menu['titre']);
                    if ($title == $menu['titre']) {   // Translation not found
                        if (!empty($menu['langs'])) {    // If there is a dedicated translation file
                            //print 'Load file '.$menu['langs'].'<br>';
                            Globals::$langs->load($menu['langs']);
                        }

                        $substitarray = array('__LOGIN__' => Globals::$user->login, '__USER_ID__' => Globals::$user->id, '__USER_SUPERVISOR_ID__' => Globals::$user->fk_user);
                        $menu['titre'] = DolUtils::make_substitutions($menu['titre'], $substitarray);

                        if (preg_match("/\//", $menu['titre'])) { // To manage translation when title is string1/string2
                            $tab_titre = explode("/", $menu['titre']);
                            $title = Globals::$langs->trans($tab_titre[0]) . "/" . Globals::$langs->trans($tab_titre[1]);
                        } else if (preg_match('/\|\|/', $menu['titre'])) { // To manage different translation (Title||AltTitle@ConditionForAltTitle)
                            $tab_title = explode("||", $menu['titre']);
                            $alt_title = explode("@", $tab_title[1]);
                            $title_enabled = verifCond($alt_title[1]);
                            $title = ($title_enabled ? Globals::$langs->trans($alt_title[0]) : Globals::$langs->trans($tab_title[0]));
                        } else {
                            $title = Globals::$langs->trans($menu['titre']);
                        }
                    }
                    //$tmp4=microtime(true);
                    //print '>>> 3 '.($tmp4 - $tmp3).'<br>';
                    // We complete tabMenu
                    $tabMenu[$b]['rowid'] = $menu['rowid'];
                    $tabMenu[$b]['module'] = $menu['module'];
                    $tabMenu[$b]['fk_menu'] = $menu['fk_menu'];
                    $tabMenu[$b]['url'] = $menu['url'];
                    if (!preg_match("/^(http:\/\/|https:\/\/)/i", $tabMenu[$b]['url'])) {
                        if (preg_match('/\?/', $tabMenu[$b]['url'])) {
                            $tabMenu[$b]['url'] .= '&amp;idmenu=' . $menu['rowid'];
                        } else {
                            $tabMenu[$b]['url'] .= '?idmenu=' . $menu['rowid'];
                        }
                    }
                    $tabMenu[$b]['titre'] = $title;
                    $tabMenu[$b]['target'] = $menu['target'];
                    $tabMenu[$b]['mainmenu'] = $menu['mainmenu'];
                    $tabMenu[$b]['leftmenu'] = $menu['leftmenu'];
                    $tabMenu[$b]['perms'] = $perms;
                    $tabMenu[$b]['enabled'] = $enabled;
                    $tabMenu[$b]['type'] = $menu['type'];
                    //$tabMenu[$b]['langs']       = $menu['langs'];
                    $tabMenu[$b]['fk_mainmenu'] = $menu['fk_mainmenu'];
                    $tabMenu[$b]['fk_leftmenu'] = $menu['fk_leftmenu'];
                    $tabMenu[$b]['position'] = (int) $menu['position'];

                    $b++;
                }

                $a++;
            }
            //Globals::$db->free($resql);
            // Currently $tabMenu is sorted on position.
            // If a child have a position lower that its parent, we can make a loop to fix this here, but we prefer to show a warning
            // into the leftMenuCharger later to avoid useless operations.

            return 1;
        } else {
            dol_print_error(Globals::$db);
            return -1;
        }
    }

    /**
     *  Complete this->newmenu with menu entry found in $tab
     *
     *  @param  array	$tab			Tab array with all menu entries
     *  @param  int		$pere			Id of parent
     *  @param  int		$level			Level
     *  @return	void
     */
    private function recur($tab, $pere, $level)
    {
        // Loop on tab array
        $num = count($tab);
        for ($x = 0; $x < $num; $x++) {
            //si un element a pour pere : $pere
            if ((($tab[$x]['fk_menu'] >= 0 && $tab[$x]['fk_menu'] == $pere)) && $tab[$x]['enabled']) {
                $this->newmenu->add($tab[$x]['url'], $tab[$x]['titre'], ($level - 1), $tab[$x]['perms'], $tab[$x]['target'], $tab[$x]['mainmenu'], $tab[$x]['leftmenu']);
                $this->recur($tab, $tab[$x]['rowid'], ($level + 1));
            }
        }
    }
}
