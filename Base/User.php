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
namespace Alixar\Base;

use Alixar\Helpers\DolUtils;
use Alxarafe\Helpers\Config;
use Alixar\Base\CommonObject;
use Alixar\Helpers\Globals;

class User extends CommonObject
{

    /**
     * @var string ID to identify managed object
     */
    public $element = 'user';

    /**
     * @var string Name of table without prefix where object is stored
     */
    public $table_element = 'user';

    /**
     * @var int Field with ID of parent key if this field has a parent
     */
    public $fk_element = 'fk_user';

    /**
     * 0=No test on entity, 1=Test with field entity, 2=Test with link by societe
     * @var int
     */
    public $ismultientitymanaged = 1;
    public $id = 0;
    public $statut;
    public $ldap_sid;
    public $search_sid;
    public $employee;
    public $gender;
    public $birth;
    public $email;
    public $skype;
    public $twitter;
    public $facebook;
    public $job;   // job position
    public $signature;

    /**
     * @var string Address
     */
    public $address;
    public $zip;
    public $town;
    public $state_id;  // The state/department
    public $state_code;
    public $state;
    public $office_phone;
    public $office_fax;
    public $user_mobile;
    public $admin;
    public $login;
    public $api_key;

    /**
     * @var int Entity
     */
    public $entity;
//! Clear password in memory
    public $pass;
//! Clear password in database (defined if DATABASE_PWD_ENCRYPTED=0)
    public $pass_indatabase;
//! Encrypted password in database (always defined)
    public $pass_indatabase_crypted;
    public $datec;
    public $datem;
//! If this is defined, it is an external user
    /**
     * @deprecated
     * @see socid
     */
    public $societe_id;

    /**
     * @deprecated
     * @see contactid
     */
    public $contact_id;
    public $socid;
    public $contactid;

    /**
     * @var int ID
     */
    public $fk_member;

    /**
     * @var int User ID
     */
    public $fk_user;
    public $clicktodial_url;
    public $clicktodial_login;
    public $clicktodial_password;
    public $clicktodial_poste;
    public $datelastlogin;
    public $datepreviouslogin;
    public $photo;
    public $lang;
    public $rights;                        // Array of permissions user->rights->permx
    public $all_permissions_are_loaded;    // All permission are loaded
    public $nb_rights;              // Number of rights granted to the user
    private $_tab_loaded = array();     // Cache array of already loaded permissions
    public $conf;             // To store personal config
    public $default_values;         // To store default values for user
    public $lastsearch_values_tmp;  // To store current search criterias for user
    public $lastsearch_values;      // To store last saved search criterias for user
    public $users = array();  // To store all tree of users hierarchy
    public $parentof;    // To store an array of all parents for all ids.
    private $cache_childids;
    public $accountancy_code;   // Accountancy code in prevision of the complete accountancy module
    public $thm;     // Average cost of employee - Used for valuation of time spent
    public $tjm;     // Average cost of employee
    public $salary;     // Monthly salary       - Denormalized value from llx_user_employment
    public $salaryextra;    // Monthly salary extra - Denormalized value from llx_user_employment
    public $weeklyhours;    // Weekly hours         - Denormalized value from llx_user_employment
    public $color;      // Define background color for user in agenda
    public $dateemployment;   // Define date of employment by company
    public $dateemploymentend;  // Define date of employment end by company
    public $default_c_exp_tax_cat;
    public $default_range;
    public $fields = array(
        'rowid' => array('type' => 'integer', 'label' => 'TechnicalID', 'enabled' => 1, 'visible' => -2, 'notnull' => 1, 'index' => 1, 'position' => 1, 'comment' => 'Id'),
        'lastname' => array('type' => 'varchar(50)', 'label' => 'Name', 'enabled' => 1, 'visible' => 1, 'notnull' => 1, 'showoncombobox' => 1, 'index' => 1, 'position' => 20, 'searchall' => 1, 'comment' => 'Reference of object'),
        'firstname' => array('type' => 'varchar(50)', 'label' => 'Name', 'enabled' => 1, 'visible' => 1, 'notnull' => 1, 'showoncombobox' => 1, 'index' => 1, 'position' => 10, 'searchall' => 1, 'comment' => 'Reference of object'),
    );

    /**
     *    Constructor of the class
     *
     *    @param   DoliDb  $db     Database handler
     */
    function __construct()
    {
// User preference
        $this->liste_limit = 0;
        $this->clicktodial_loaded = 0;

// For cache usage
        $this->all_permissions_are_loaded = 0;
        $this->nb_rights = 0;

// Force some default values
        $this->admin = 0;
        $this->employee = 1;

        $this->conf = new \stdClass();
        $this->rights = new \stdClass();
        $this->rights->user = new \stdClass();
        $this->rights->user->user = new \stdClass();
        $this->rights->user->self = new \stdClass();
    }

    /**
     * 	Load a user from database with its id or ref (login).
     *  This function does not load permissions, only user properties. Use getrights() for this just after the fetch.
     *
     * 	@param	int		$id		       		If defined, id to used for search
     * 	@param  string	$login       		If defined, login to used for search
     * 	@param  string	$sid				If defined, sid to used for search
     * 	@param	int		$loadpersonalconf	1=also load personal conf of user (in Globals::$user->conf->xxx), 0=do not load personal conf.
     *  @param  int     $entity             If a value is >= 0, we force the search on a specific entity. If -1, means search depens on default setup.
     * 	@return	int							<0 if KO, 0 not found, >0 if OK
     */
    function fetch($id = '', $login = '', $sid = '', $loadpersonalconf = 0, $entity = -1)
    {
// global $conf, Globals::$user;
// Clean parameters
        $login = trim($login);

// Get user
        $sql = "SELECT u.rowid, u.lastname, u.firstname, u.employee, u.gender, u.birth, u.email, u.job, u.skype, u.twitter, u.facebook,";
        $sql .= " u.signature, u.office_phone, u.office_fax, u.user_mobile,";
        $sql .= " u.address, u.zip, u.town, u.fk_state as state_id, u.fk_country as country_id,";
        $sql .= " u.admin, u.login, u.note,";
        $sql .= " u.pass, u.pass_crypted, u.pass_temp, u.api_key,";
        $sql .= " u.fk_soc, u.fk_socpeople, u.fk_member, u.fk_user, u.ldap_sid,";
        $sql .= " u.statut, u.lang, u.entity,";
        $sql .= " u.datec as datec,";
        $sql .= " u.tms as datem,";
        $sql .= " u.datelastlogin as datel,";
        $sql .= " u.datepreviouslogin as datep,";
        $sql .= " u.photo as photo,";
        $sql .= " u.openid as openid,";
        $sql .= " u.accountancy_code,";
        $sql .= " u.thm,";
        $sql .= " u.tjm,";
        $sql .= " u.salary,";
        $sql .= " u.salaryextra,";
        $sql .= " u.weeklyhours,";
        $sql .= " u.color,";
        $sql .= " u.dateemployment, u.dateemploymentend,";
        $sql .= " u.ref_int, u.ref_ext,";
        $sql .= " u.default_range, u.default_c_exp_tax_cat,";   // Expense report default mode
        $sql .= " c.code as country_code, c.label as country,";
        $sql .= " d.code_departement as state_code, d.nom as state";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user as u";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_country as c ON u.fk_country = c.rowid";
        $sql .= " LEFT JOIN " . MAIN_DB_PREFIX . "c_departements as d ON u.fk_state = d.rowid";

        if ($entity < 0) {
            if ((empty(Globals::$conf->multicompany->enabled) || empty(Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE)) && (!empty(Globals::$user->entity))) {
                $sql .= " WHERE u.entity IN (0," . Globals::$conf->entity . ")";
            } else {
                $sql .= " WHERE u.entity IS NOT NULL";    // multicompany is on in transverse mode or user making fetch is on entity 0, so user is allowed to fetch anywhere into database
            }
        } else {  // The fetch was forced on an entity
            if (!empty(Globals::$conf->multicompany->enabled) && !empty(Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE)) {
                $sql .= " WHERE u.entity IS NOT NULL";    // multicompany is on in transverse mode or user making fetch is on entity 0, so user is allowed to fetch anywhere into database
            } else {
                $sql .= " WHERE u.entity IN (0, " . (($entity != '' && $entity >= 0) ? $entity : Globals::$conf->entity) . ")";   // search in entity provided in parameter
            }
        }

        if ($sid) {    // permet une recherche du user par son SID ActiveDirectory ou Samba
//$sql .= " AND (u.ldap_sid = '" . Config::$dbEngine->escape($sid) . "' OR u.login = '" . Config::$dbEngine->escape($login) . "') LIMIT 1";
            $sql .= " AND (u.ldap_sid = '" . $sid . "' OR u.login = '" . Config::$dbEngine->escape($login) . "') LIMIT 1";
        } else if ($login) {
// $sql .= " AND u.login = '" . Config::$dbEngine->escape($login) . "'";
            $sql .= " AND u.login = '" . $login . "'";
        } else {
            $sql .= " AND u.rowid = " . $id;
        }
        $sql .= " ORDER BY u.entity ASC";    // Avoid random result when there is 2 login in 2 different entities

        $result = Config::$dbEngine->select($sql);
// echo "<p>SQL in 2477 of User: '$sql'</p>";
        if ($result) {
            if (count($result) > 0) {

// Convert array to stdclass
                $obj = json_decode(json_encode($result[0]));

                $this->id = $obj->rowid;
                $this->ref = $obj->rowid;

                $this->ref_int = $obj->ref_int;
                $this->ref_ext = $obj->ref_ext;

                $this->ldap_sid = $obj->ldap_sid;
                $this->lastname = $obj->lastname;
                $this->firstname = $obj->firstname;

                $this->employee = $obj->employee;

                $this->login = $obj->login;
                $this->gender = $obj->gender;
// $this->birth = Config::$dbEngine->jdate($obj->birth);
                $this->birth = $obj->birth;

                $this->pass_indatabase = $obj->pass;
                $this->pass_indatabase_crypted = $obj->pass_crypted;
                $this->pass = $obj->pass;
                $this->pass_temp = $obj->pass_temp;
                $this->api_key = $obj->api_key;

                $this->address = $obj->address;
                $this->zip = $obj->zip;
                $this->town = $obj->town;

                $this->country_id = $obj->country_id;
                $this->country_code = $obj->country_id ? $obj->country_code : '';
//$this->country 		= $obj->country_id?(Globals::$langs->trans('Country'.$obj->country_code)!='Country'.$obj->country_code?Globals::$langs->transnoentities('Country'.$obj->country_code):$obj->country):'';

                $this->state_id = $obj->state_id;
                $this->state_code = $obj->state_code;
                $this->state = ($obj->state != '-' ? $obj->state : '');

                $this->office_phone = $obj->office_phone;
                $this->office_fax = $obj->office_fax;
                $this->user_mobile = $obj->user_mobile;
                $this->email = $obj->email;
                $this->skype = $obj->skype;
                $this->twitter = $obj->twitter;
                $this->facebook = $obj->facebook;
                $this->job = $obj->job;
                $this->signature = $obj->signature;
                $this->admin = $obj->admin;
                $this->note = $obj->note;
                $this->statut = $obj->statut;
                $this->photo = $obj->photo;
                $this->openid = $obj->openid;
                $this->lang = $obj->lang;
                $this->entity = $obj->entity;
                $this->accountancy_code = $obj->accountancy_code;
                $this->thm = $obj->thm;
                $this->tjm = $obj->tjm;
                $this->salary = $obj->salary;
                $this->salaryextra = $obj->salaryextra;
                $this->weeklyhours = $obj->weeklyhours;
                $this->color = $obj->color;

                /*
                  $this->dateemployment = Config::$dbEngine->jdate($obj->dateemployment);
                  $this->dateemploymentend = Config::$dbEngine->jdate($obj->dateemploymentend);

                  $this->datec = Config::$dbEngine->jdate($obj->datec);
                  $this->datem = Config::$dbEngine->jdate($obj->datem);
                  $this->datelastlogin = Config::$dbEngine->jdate($obj->datel);
                  $this->datepreviouslogin = Config::$dbEngine->jdate($obj->datep);
                 */
                $this->dateemployment = $obj->dateemployment;
                $this->dateemploymentend = $obj->dateemploymentend;

                $this->datec = $obj->datec;
                $this->datem = $obj->datem;
                $this->datelastlogin = $obj->datel;
                $this->datepreviouslogin = $obj->datep;

                $this->societe_id = $obj->fk_soc;  // deprecated
                $this->contact_id = $obj->fk_socpeople; // deprecated
                $this->socid = $obj->fk_soc;
                $this->contactid = $obj->fk_socpeople;
                $this->fk_member = $obj->fk_member;
                $this->fk_user = $obj->fk_user;

                $this->default_range = $obj->default_range;
                $this->default_c_exp_tax_cat = $obj->default_c_exp_tax_cat;

// Protection when module multicompany was set, admin was set to first entity and then, the module was disabled,
// in such case, this admin user must be admin for ALL entities.
                if (empty(Globals::$conf->multicompany->enabled) && $this->admin && $this->entity == 1) {
                    $this->entity = 0;
                }

// Retreive all extrafield
// fetch optionals attributes and labels
                $this->fetch_optionals();
            } else {
                $this->error = "USERNOTFOUND";
                DolUtils::dol_syslog(get_class($this) . "::fetch user not found", LOG_DEBUG);
                return 0;
            }
        } else {
            $this->error = Config::$dbEngine->lasterror();
            return -1;
        }

// To get back the global configuration unique to the user
        if ($loadpersonalconf) {
// Load user->conf for user
            $sql = "SELECT param, value FROM " . MAIN_DB_PREFIX . "user_param";
            $sql .= " WHERE fk_user = " . $this->id;
            $sql .= " AND entity = " . Globals::$conf->entity;
//DolUtils::dol_syslog(get_class($this).'::fetch load personalized conf', LOG_DEBUG);
            $resql = Config::$dbEngine->select($sql);
            if (is_array($resql)) {
                foreach ($resql as $array) {
                    $obj = json_decode(json_encode($array));

                    $p = (!empty($obj->param) ? $obj->param : '');
                    if (!empty($p)) {
                        $this->conf->$p = $obj->value;
                    }
                }
            } else {
// $this->error = Config::$dbEngine->lasterror();
                return -2;
            }

            $result = $this->loadDefaultValues();

            if ($result < 0) {
// $this->error = Config::$dbEngine->lasterror();
                return -3;
            }
        }

        return 1;
    }

    /**
     *  Load default value in property ->default_values
     *
     *  @return int						> 0 if OK, < 0 if KO
     */
    function loadDefaultValues()
    {
// global $conf;
// Load user->default_values for user. TODO Save this in memcached ?
        $sql = "SELECT rowid, entity, type, page, param, value";
        $sql .= " FROM " . MAIN_DB_PREFIX . "default_values";
        $sql .= " WHERE entity IN (" . ($this->entity > 0 ? $this->entity . ", " : "") . Globals::$conf->entity . ")"; // Entity of user (if defined) + current entity
        $sql .= " AND user_id IN (0" . ($this->id > 0 ? ", " . $this->id : "") . ")";       // User 0 (all) + me (if defined)
        $resql = Config::$dbEngine->select($sql);
        if (is_array($resql)) {
            foreach ($resql as $array) {
                $obj = json_decode(json_encode($array));

                $pagewithoutquerystring = $obj->page;
                $pagequeries = '';
                if (preg_match('/^([^\?]+)\?(.*)$/', $pagewithoutquerystring, $reg)) { // There is query param
                    $pagewithoutquerystring = $reg[1];
                    $pagequeries = $reg[2];
                }
                $this->default_values[$pagewithoutquerystring][$obj->type][$pagequeries ? $pagequeries : '_noquery_'][$obj->param] = $obj->value;
//if ($pagequeries) $this->default_values[$pagewithoutquerystring][$obj->type.'_queries']=$pagequeries;
            }
// Sort by key, so _noquery_ is last
            if (!empty($this->default_values)) {
                foreach ($this->default_values as $a => $b) {
                    foreach ($b as $c => $d) {
                        krsort($this->default_values[$a][$c]);
                    }
                }
            }
// Config::$dbEngine->free($resql);

            return 1;
        }
        DolUtils::dol_print_error(Config::$dbEngine);
        return -1;
    }

    /**
     *  Add a right to the user
     *
     * 	@param	int		$rid			Id of permission to add or 0 to add several permissions
     *  @param  string	$allmodule		Add all permissions of module $allmodule
     *  @param  string	$allperms		Add all permissions of module $allmodule, subperms $allperms only
     *  @param	int		$entity			Entity to use
     *  @param  int	    $notrigger		1=Does not execute triggers, 0=Execute triggers
     *  @return int						> 0 if OK, < 0 if KO
     *  @see	clearrights, delrights, getrights
     */
    function addrights($rid, $allmodule = '', $allperms = '', $entity = 0, $notrigger = 0)
    {
//global $conf, Globals::$user, Globals::$langs;

        $entity = (!empty($entity) ? $entity : Globals::$conf->entity);

        DolUtils::dol_syslog(get_class($this) . "::addrights $rid, $allmodule, $allperms, $entity");
        $error = 0;
        $whereforadd = '';

        Config::$dbEngine->begin();

        if (!empty($rid)) {
// Si on a demande ajout d'un droit en particulier, on recupere
// les caracteristiques (module, perms et subperms) de ce droit.
            $sql = "SELECT module, perms, subperms";
            $sql .= " FROM " . MAIN_DB_PREFIX . "rights_def";
            $sql .= " WHERE id = '" . Config::$dbEngine->escape($rid) . "'";
            $sql .= " AND entity = " . $entity;

            $result = Config::$dbEngine->query($sql);
            if ($result) {
                $obj = Config::$dbEngine->fetch_object($result);
                $module = $obj->module;
                $perms = $obj->perms;
                $subperms = $obj->subperms;
            } else {
                $error++;
                dol_print_error(Config::$dbEngine);
            }

// Where pour la liste des droits a ajouter
            $whereforadd = "id=" . Config::$dbEngine->escape($rid);
// Ajout des droits induits
            if (!empty($subperms)) {
                $whereforadd .= " OR (module='$module' AND perms='$perms' AND (subperms='lire' OR subperms='read'))";
            } else {
                if (!empty($perms)) {
                    $whereforadd .= " OR (module='$module' AND (perms='lire' OR perms='read') AND subperms IS NULL)";
                }
            }
        } else {
// On a pas demande un droit en particulier mais une liste de droits
// sur la base d'un nom de module de de perms
// Where pour la liste des droits a ajouter
            if (!empty($allmodule)) {
                if ($allmodule == 'allmodules') {
                    $whereforadd = 'allmodules';
                } else {
                    $whereforadd = "module='" . Config::$dbEngine->escape($allmodule) . "'";
                    if (!empty($allperms)) {
                        $whereforadd .= " AND perms='" . Config::$dbEngine->escape($allperms) . "'";
                    }
                }
            }
        }

// Ajout des droits trouves grace au critere whereforadd
        if (!empty($whereforadd)) {
//print "$module-$perms-$subperms";
            $sql = "SELECT id";
            $sql .= " FROM " . MAIN_DB_PREFIX . "rights_def";
            $sql .= " WHERE entity = " . $entity;
            if (!empty($whereforadd) && $whereforadd != 'allmodules') {
                $sql .= " AND " . $whereforadd;
            }

            $result = Config::$dbEngine->query($sql);
            if ($result) {
                $num = Config::$dbEngine->num_rows($result);
                $i = 0;
                while ($i < $num) {
                    $obj = Config::$dbEngine->fetch_object($result);
                    $nid = $obj->id;

                    $sql = "DELETE FROM " . MAIN_DB_PREFIX . "user_rights WHERE fk_user = " . $this->id . " AND fk_id=" . $nid . " AND entity = " . $entity;
                    if (!Config::$dbEngine->query($sql))
                        $error++;
                    $sql = "INSERT INTO " . MAIN_DB_PREFIX . "user_rights (entity, fk_user, fk_id) VALUES (" . $entity . ", " . $this->id . ", " . $nid . ")";
                    if (!Config::$dbEngine->query($sql))
                        $error++;

                    $i++;
                }
            }
            else {
                $error++;
                dol_print_error(Config::$dbEngine);
            }
        }

        if (!$error && !$notrigger) {
            Globals::$langs->load("other");
            $this->context = array('audit' => Globals::$langs->trans("PermissionsAdd") . ($rid ? ' (id=' . $rid . ')' : ''));

// Call trigger
            $result = $this->call_trigger('USER_MODIFY', Globals::$user);
            if ($result < 0) {
                $error++;
            }
// End call triggers
        }

        if ($error) {
            Config::$dbEngine->rollback();
            return -$error;
        } else {
            Config::$dbEngine->commit();
            return 1;
        }
    }

    /**
     *  Remove a right to the user
     *
     *  @param	int		$rid        Id du droit a retirer
     *  @param  string	$allmodule  Retirer tous les droits du module allmodule
     *  @param  string	$allperms   Retirer tous les droits du module allmodule, perms allperms
     *  @param	int		$entity		Entity to use
     *  @param  int	    $notrigger	1=Does not execute triggers, 0=Execute triggers
     *  @return int         		> 0 if OK, < 0 if OK
     *  @see	clearrights, addrights, getrights
     */
    function delrights($rid, $allmodule = '', $allperms = '', $entity = 0, $notrigger = 0)
    {
//global $conf, Globals::$user, Globals::$langs;

        $error = 0;
        $wherefordel = '';
        $entity = (!empty($entity) ? $entity : Globals::$conf->entity);

        Config::$dbEngine->begin();

        if (!empty($rid)) {
// Si on a demande supression d'un droit en particulier, on recupere
// les caracteristiques module, perms et subperms de ce droit.
            $sql = "SELECT module, perms, subperms";
            $sql .= " FROM " . MAIN_DB_PREFIX . "rights_def";
            $sql .= " WHERE id = '" . Config::$dbEngine->escape($rid) . "'";
            $sql .= " AND entity = " . $entity;

            $result = Config::$dbEngine->query($sql);
            if ($result) {
                $obj = Config::$dbEngine->fetch_object($result);
                $module = $obj->module;
                $perms = $obj->perms;
                $subperms = $obj->subperms;
            } else {
                $error++;
                dol_print_error(Config::$dbEngine);
            }

// Where pour la liste des droits a supprimer
            $wherefordel = "id=" . Config::$dbEngine->escape($rid);
// Suppression des droits induits
            if ($subperms == 'lire' || $subperms == 'read') {
                $wherefordel .= " OR (module='$module' AND perms='$perms' AND subperms IS NOT NULL)";
            }
            if ($perms == 'lire' || $perms == 'read') {
                $wherefordel .= " OR (module='$module')";
            }
        } else {
// On a demande suppression d'un droit sur la base d'un nom de module ou perms
// Where pour la liste des droits a supprimer
            if (!empty($allmodule)) {
                if ($allmodule == 'allmodules') {
                    $wherefordel = 'allmodules';
                } else {
                    $wherefordel = "module='" . Config::$dbEngine->escape($allmodule) . "'";
                    if (!empty($allperms)) {
                        $whereforadd .= " AND perms='" . Config::$dbEngine->escape($allperms) . "'";
                    }
                }
            }
        }

// Suppression des droits selon critere defini dans wherefordel
        if (!empty($wherefordel)) {
//print "$module-$perms-$subperms";
            $sql = "SELECT id";
            $sql .= " FROM " . MAIN_DB_PREFIX . "rights_def";
            $sql .= " WHERE entity = " . $entity;
            if (!empty($wherefordel) && $wherefordel != 'allmodules') {
                $sql .= " AND " . $wherefordel;
            }

            $result = Config::$dbEngine->query($sql);
            if ($result) {
                $num = Config::$dbEngine->num_rows($result);
                $i = 0;
                while ($i < $num) {
                    $obj = Config::$dbEngine->fetch_object($result);
                    $nid = $obj->id;

                    $sql = "DELETE FROM " . MAIN_DB_PREFIX . "user_rights";
                    $sql .= " WHERE fk_user = " . $this->id . " AND fk_id=" . $nid;
                    $sql .= " AND entity = " . $entity;
                    if (!Config::$dbEngine->query($sql)) {
                        $error++;
                    }

                    $i++;
                }
            } else {
                $error++;
                dol_print_error(Config::$dbEngine);
            }
        }

        if (!$error && !$notrigger) {
            Globals::$langs->load("other");
            $this->context = array('audit' => Globals::$langs->trans("PermissionsDelete") . ($rid ? ' (id=' . $rid . ')' : ''));

// Call trigger
            $result = $this->call_trigger('USER_MODIFY', Globals::$user);
            if ($result < 0) {
                $error++;
            }
// End call triggers
        }

        if ($error) {
            Config::$dbEngine->rollback();
            return -$error;
        } else {
            Config::$dbEngine->commit();
            return 1;
        }
    }

    /**
     *  Clear all permissions array of user
     *
     *  @return	void
     *  @see	getrights
     */
    function clearrights()
    {
        DolUtils::dol_syslog(get_class($this) . "::clearrights reset user->rights");
        $this->rights = '';
        $this->all_permissions_are_loaded = false;
        $this->_tab_loaded = array();
    }

    /**
     * 	Load permissions granted to user into object user
     *
     * 	@param  string	$moduletag		Limit permission for a particular module ('' by default means load all permissions)
     *  @param	int		$forcereload	Force reload of permissions even if they were already loaded (ignore cache)
     * 	@return	void
     *  @see	clearrights, delrights, addrights
     */
    function getrights($moduletag = '', $forcereload = 0)
    {
        global $conf;

        if (empty($forcereload)) {
            if ($moduletag && isset($this->_tab_loaded[$moduletag]) && $this->_tab_loaded[$moduletag]) {
// Rights for this module are already loaded, so we leave
                return;
            }

            if ($this->all_permissions_are_loaded) {
// We already loaded all rights for this user, so we leave
                return;
            }
        }

// Recuperation des droits utilisateurs + recuperation des droits groupes
// D'abord les droits utilisateurs
        $sql = "SELECT DISTINCT r.module, r.perms, r.subperms";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user_rights as ur";
        $sql .= ", " . MAIN_DB_PREFIX . "rights_def as r";
        $sql .= " WHERE r.id = ur.fk_id";
        if (!empty(Globals::$conf->global->MULTICOMPANY_BACKWARD_COMPATIBILITY)) {
            $sql .= " AND r.entity IN (0," . (!empty(Globals::$conf->multicompany->enabled) && !empty(Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE) ? "1," : "") . Globals::$conf->entity . ")";
        } else {
            $sql .= " AND ur.entity = " . Globals::$conf->entity;
        }
        $sql .= " AND ur.fk_user= " . $this->id;
        $sql .= " AND r.perms IS NOT NULL";
        if ($moduletag) {
// $sql .= " AND r.module = '" . Config::$dbEngine->escape($moduletag) . "'";
            $sql .= " AND r.module = '" . $moduletag . "'";
        }

        $resql = Config::$dbEngine->select($sql);
        if (is_array($resql)) {
            foreach ($resql as $array) {
                $obj = json_decode(json_encode($array));

                $module = $obj->module;
                $perms = $obj->perms;
                $subperms = $obj->subperms;

                if ($perms) {
                    if (!isset($this->rights) || !is_object($this->rights)) {
                        $this->rights = new \stdClass(); // For avoid error
                    }
                    if ($module) {
                        if (!isset($this->rights->$module) || !is_object($this->rights->$module)) {
                            $this->rights->$module = new \stdClass();
                        }
                        if ($subperms) {
                            if (!isset($this->rights->$module->$perms) || !is_object($this->rights->$module->$perms)) {
                                $this->rights->$module->$perms = new \stdClass();
                            }
                            if (empty($this->rights->$module->$perms->$subperms)) {
                                $this->nb_rights++;
                            }
                            $this->rights->$module->$perms->$subperms = 1;
                        } else {
                            if (empty($this->rights->$module->$perms)) {
                                $this->nb_rights++;
                            }
                            $this->rights->$module->$perms = 1;
                        }
                    }
                }
            }
        }

// Maintenant les droits groupes
        $sql = "SELECT DISTINCT r.module, r.perms, r.subperms";
        $sql .= " FROM " . MAIN_DB_PREFIX . "usergroup_rights as gr,";
        $sql .= " " . MAIN_DB_PREFIX . "usergroup_user as gu,";
        $sql .= " " . MAIN_DB_PREFIX . "rights_def as r";
        $sql .= " WHERE r.id = gr.fk_id";
        if (!empty(Globals::$conf->global->MULTICOMPANY_BACKWARD_COMPATIBILITY)) {
            if (!empty(Globals::$conf->multicompany->enabled) && !empty(Globals::$conf->global->MULTICOMPANY_TRANSVERSE_MODE)) {
                $sql .= " AND gu.entity IN (0," . Globals::$conf->entity . ")";
            } else {
                $sql .= " AND r.entity = " . Globals::$conf->entity;
            }
        } else {
            $sql .= " AND gr.entity = " . Globals::$conf->entity;
            $sql .= " AND r.entity = " . Globals::$conf->entity;
        }
        $sql .= " AND gr.fk_usergroup = gu.fk_usergroup";
        $sql .= " AND gu.fk_user = " . $this->id;
        $sql .= " AND r.perms IS NOT NULL";
        if ($moduletag) {
            $sql .= " AND r.module = '" . $moduletag . "'";
        }

        $resql = Config::$dbEngine->select($sql);
        if (is_array($resql)) {
            foreach ($resql as $array) {
                $obj = json_decode(json_encode($array));

                $module = $obj->module;
                $perms = $obj->perms;
                $subperms = $obj->subperms;

                if ($perms) {
                    if (!isset($this->rights) || !is_object($this->rights)) {
                        $this->rights = new \stdClass(); // For avoid error
                    }
                    if (!isset($this->rights->$module) || !is_object($this->rights->$module)) {
                        $this->rights->$module = new \stdClass();
                    }
                    if ($subperms) {
                        if (!isset($this->rights->$module->$perms) || !is_object($this->rights->$module->$perms)) {
                            $this->rights->$module->$perms = new \stdClass();
                        }
                        if (empty($this->rights->$module->$perms->$subperms)) {
                            $this->nb_rights++;
                        }
                        $this->rights->$module->$perms->$subperms = 1;
                    } else {
                        if (empty($this->rights->$module->$perms)) {
                            $this->nb_rights++;
                        }
// if we have already define a subperm like this $this->rights->$module->level1->level2 with llx_user_rights, we don't want override level1 because the level2 can be not define on user group
                        if (!is_object($this->rights->$module->$perms)) {
                            $this->rights->$module->$perms = 1;
                        }
                    }
                }
            }
        }

// For backward compatibility
        if (isset($this->rights->propale) && !isset($this->rights->propal)) {
            $this->rights->propal = $this->rights->propale;
        }
        if (isset($this->rights->propal) && !isset($this->rights->propale)) {
            $this->rights->propale = $this->rights->propal;
        }

        if (!$moduletag) {
// Si module etait non defini, alors on a tout charge, on peut donc considerer
// que les droits sont en cache (car tous charges) pour cet instance de user
            $this->all_permissions_are_loaded = 1;
        } else {
// Si module defini, on le marque comme charge en cache
            $this->_tab_loaded[$moduletag] = 1;
        }
    }

    /**
     *  Change status of a user
     *
     * 	@param	int		$statut		Status to set
     *  @return int     			<0 if KO, 0 if nothing is done, >0 if OK
     */
    function setstatus($statut)
    {
// global $conf, Globals::$langs, Globals::$user;

        $error = 0;

// Check parameters
        if ($this->statut == $statut) {
            return 0;
        } else {
            $this->statut = $statut;
        }

        Config::$dbEngine->begin();

// Deactivate user
        $sql = "UPDATE " . MAIN_DB_PREFIX . "user";
        $sql .= " SET statut = " . $this->statut;
        $sql .= " WHERE rowid = " . $this->id;
        $result = Config::$dbEngine->query($sql);

        DolUtils::dol_syslog(get_class($this) . "::setstatus", LOG_DEBUG);
        if ($result) {
// Call trigger
            $result = $this->call_trigger('USER_ENABLEDISABLE', Globals::$user);
            if ($result < 0) {
                $error++;
            }
// End call triggers
        }

        if ($error) {
            Config::$dbEngine->rollback();
            return -$error;
        } else {
            Config::$dbEngine->commit();
            return 1;
        }
    }

    /**
     * Sets object to supplied categories.
     *
     * Deletes object from existing categories not supplied.
     * Adds it to non existing supplied categories.
     * Existing categories are left untouch.
     *
     * @param int[]|int $categories Category or categories IDs
     * @return void
     */
    public function setCategories($categories)
    {
// Handle single category
        if (!is_array($categories)) {
            $categories = array($categories);
        }

// Get current categories
        require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
        $c = new Categorie(Config::$dbEngine);
        $existing = $c->containing($this->id, Categorie::TYPE_USER, 'id');

// Diff
        if (is_array($existing)) {
            $to_del = array_diff($existing, $categories);
            $to_add = array_diff($categories, $existing);
        } else {
            $to_del = array(); // Nothing to delete
            $to_add = $categories;
        }

// Process
        foreach ($to_del as $del) {
            if ($c->fetch($del) > 0) {
                $c->del_type($this, 'user');
            }
        }
        foreach ($to_add as $add) {
            if ($c->fetch($add) > 0) {
                $c->add_type($this, 'user');
            }
        }

        return;
    }

    /**
     *    	Delete the user
     *
     * 		@return		int		<0 if KO, >0 if OK
     */
    function delete()
    {
//global Globals::$user, $conf, Globals::$langs;

        $error = 0;

        Config::$dbEngine->begin();

        $this->fetch($this->id);

        DolUtils::dol_syslog(get_class($this) . "::delete", LOG_DEBUG);

// Remove rights
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "user_rights WHERE fk_user = " . $this->id;

        if (!$error && !Config::$dbEngine->query($sql)) {
            $error++;
            $this->error = Config::$dbEngine->lasterror();
        }

// Remove group
        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "usergroup_user WHERE fk_user  = " . $this->id;
        if (!$error && !Config::$dbEngine->query($sql)) {
            $error++;
            $this->error = Config::$dbEngine->lasterror();
        }

// If contact, remove link
        if ($this->contact_id) {
            $sql = "UPDATE " . MAIN_DB_PREFIX . "socpeople SET fk_user_creat = null WHERE rowid = " . $this->contact_id;
            if (!$error && !Config::$dbEngine->query($sql)) {
                $error++;
                $this->error = Config::$dbEngine->lasterror();
            }
        }

// Remove extrafields
        if ((!$error) && (empty(Globals::$conf->global->MAIN_EXTRAFIELDS_DISABLED))) { // For avoid conflicts if trigger used
            $result = $this->deleteExtraFields();
            if ($result < 0) {
                $error++;
                DolUtils::dol_syslog(get_class($this) . "::delete error -4 " . $this->error, LOG_ERR);
            }
        }

// Remove user
        if (!$error) {
            $sql = "DELETE FROM " . MAIN_DB_PREFIX . "user WHERE rowid = " . $this->id;
            DolUtils::dol_syslog(get_class($this) . "::delete", LOG_DEBUG);
            if (!Config::$dbEngine->query($sql)) {
                $error++;
                $this->error = Config::$dbEngine->lasterror();
            }
        }

        if (!$error) {
// Call trigger
            $result = $this->call_trigger('USER_DELETE', Globals::$user);
            if ($result < 0) {
                $error++;
                Config::$dbEngine->rollback();
                return -1;
            }
// End call triggers

            Config::$dbEngine->commit();
            return 1;
        } else {
            Config::$dbEngine->rollback();
            return -1;
        }
    }

    /**
     *  Create a user into database
     *
     *  @param	User	Globals::$user        	Objet user doing creation
     *  @param  int		$notrigger		1=do not execute triggers, 0 otherwise
     *  @return int			         	<0 if KO, id of created user if OK
     */
    function create($user, $notrigger = 0)
    {
//global $conf, Globals::$langs;
//global $mysoc;
// Clean parameters
        $this->login = trim($this->login);
        if (!isset($this->entity)) {
            $this->entity = Globals::$conf->entity; // If not defined, we use default value
        }
        DolUtils::dol_syslog(get_class($this) . "::create login=" . $this->login . ", user=" . (is_object(Globals::$user) ? Globals::$user->id : ''), LOG_DEBUG);

// Check parameters
        if (!empty(Globals::$conf->global->USER_MAIL_REQUIRED) && !isValidEMail($this->email)) {
            Globals::$langs->load("errors");
            $this->error = Globals::$langs->trans("ErrorBadEMail", $this->email);
            return -1;
        }
        if (empty($this->login)) {
            Globals::$langs->load("errors");
            $this->error = Globals::$langs->trans("ErrorFieldRequired", Globals::$langs->transnoentitiesnoconv("Login"));
            return -1;
        }

        $this->datec = dol_now();

        $error = 0;
        Config::$dbEngine->begin();

        $sql = "SELECT login FROM " . MAIN_DB_PREFIX . "user";
        $sql .= " WHERE login ='" . Config::$dbEngine->escape($this->login) . "'";
        $sql .= " AND entity IN (0," . Config::$dbEngine->escape(Globals::$conf->entity) . ")";

        DolUtils::dol_syslog(get_class($this) . "::create", LOG_DEBUG);
        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            $num = Config::$dbEngine->num_rows($resql);
            Config::$dbEngine->free($resql);

            if ($num) {
                $this->error = 'ErrorLoginAlreadyExists';
                DolUtils::dol_syslog(get_class($this) . "::create " . $this->error, LOG_WARNING);
                Config::$dbEngine->rollback();
                return -6;
            } else {
                $sql = "INSERT INTO " . MAIN_DB_PREFIX . "user (datec,login,ldap_sid,entity)";
                $sql .= " VALUES('" . Config::$dbEngine->idate($this->datec) . "','" . Config::$dbEngine->escape($this->login) . "','" . Config::$dbEngine->escape($this->ldap_sid) . "'," . Config::$dbEngine->escape($this->entity) . ")";
                $result = Config::$dbEngine->query($sql);

                DolUtils::dol_syslog(get_class($this) . "::create", LOG_DEBUG);
                if ($result) {
                    $this->id = Config::$dbEngine->last_insert_id(MAIN_DB_PREFIX . "user");

// Set default rights
                    if ($this->set_default_rights() < 0) {
                        $this->error = 'ErrorFailedToSetDefaultRightOfUser';
                        Config::$dbEngine->rollback();
                        return -5;
                    }

// Update minor fields
                    $result = $this->update(Globals::$user, 1, 1);
                    if ($result < 0) {
                        Config::$dbEngine->rollback();
                        return -4;
                    }

                    if (!empty(Globals::$conf->global->STOCK_USERSTOCK_AUTOCREATE)) {
                        require_once DOL_DOCUMENT_ROOT . '/product/stock/class/entrepot.class.php';
                        Globals::$langs->load("stocks");
                        $entrepot = new Entrepot(Config::$dbEngine);
                        $entrepot->libelle = Globals::$langs->trans("PersonalStock", $this->getFullName(Globals::$langs));
                        $entrepot->description = Globals::$langs->trans("ThisWarehouseIsPersonalStock", $this->getFullName(Globals::$langs));
                        $entrepot->statut = 1;
                        $entrepot->country_id = $mysoc->country_id;
                        $entrepot->create(Globals::$user);
                    }

                    if (!$notrigger) {
// Call trigger
                        $result = $this->call_trigger('USER_CREATE', Globals::$user);
                        if ($result < 0) {
                            $error++;
                        }
// End call triggers
                    }

                    if (!$error) {
                        Config::$dbEngine->commit();
                        return $this->id;
                    } else {
//$this->error=$interface->error;
                        DolUtils::dol_syslog(get_class($this) . "::create " . $this->error, LOG_ERR);
                        Config::$dbEngine->rollback();
                        return -3;
                    }
                } else {
                    $this->error = Config::$dbEngine->lasterror();
                    Config::$dbEngine->rollback();
                    return -2;
                }
            }
        } else {
            $this->error = Config::$dbEngine->lasterror();
            Config::$dbEngine->rollback();
            return -1;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Create a user from a contact object. User will be internal but if contact is linked to a third party, user will be external
     *
     *  @param	Contact	$contact    Object for source contact
     * 	@param  string	$login      Login to force
     *  @param  string	$password   Password to force
     *  @return int 				<0 if error, if OK returns id of created user
     */
    function create_from_contact($contact, $login = '', $password = '')
    {
// phpcs:enable
//global $conf, Globals::$user, Globals::$langs;

        $error = 0;

// Define parameters
        $this->admin = 0;
        $this->lastname = $contact->lastname;
        $this->firstname = $contact->firstname;
        $this->gender = $contact->gender;
        $this->email = $contact->email;
        $this->skype = $contact->skype;
        $this->twitter = $contact->twitter;
        $this->facebook = $contact->facebook;
        $this->office_phone = $contact->phone_pro;
        $this->office_fax = $contact->fax;
        $this->user_mobile = $contact->phone_mobile;
        $this->address = $contact->address;
        $this->zip = $contact->zip;
        $this->town = $contact->town;
        $this->state_id = $contact->state_id;
        $this->country_id = $contact->country_id;
        $this->employee = 0;

        if (empty($login)) {
            $login = strtolower(substr($contact->firstname, 0, 4)) . strtolower(substr($contact->lastname, 0, 4));
        }
        $this->login = $login;

        Config::$dbEngine->begin();

// Cree et positionne $this->id
        $result = $this->create(Globals::$user);
        if ($result > 0) {
            $sql = "UPDATE " . MAIN_DB_PREFIX . "user";
            $sql .= " SET fk_socpeople=" . $contact->id;
            if ($contact->socid) {
                $sql .= ", fk_soc=" . $contact->socid;
            }
            $sql .= " WHERE rowid=" . $this->id;
            $resql = Config::$dbEngine->query($sql);

            DolUtils::dol_syslog(get_class($this) . "::create_from_contact", LOG_DEBUG);
            if ($resql) {
                $this->context['createfromcontact'] = 'createfromcontact';

// Call trigger
                $result = $this->call_trigger('USER_CREATE', Globals::$user);
                if ($result < 0) {
                    $error++;
                    Config::$dbEngine->rollback();
                    return -1;
                }
// End call triggers

                Config::$dbEngine->commit();
                return $this->id;
            } else {
                $this->error = Config::$dbEngine->error();

                Config::$dbEngine->rollback();
                return -1;
            }
        } else {
// $this->error deja positionne
            DolUtils::dol_syslog(get_class($this) . "::create_from_contact - 0");

            Config::$dbEngine->rollback();
            return $result;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Create a user into database from a member object
     *
     *  @param	Adherent	$member		Object member source
     * 	@param	string		$login		Login to force
     *  @return int						<0 if KO, if OK, return id of created account
     */
    function create_from_member($member, $login = '')
    {
// phpcs:enable
// global $conf, Globals::$user, Globals::$langs;
// Positionne parametres
        $this->admin = 0;
        $this->lastname = $member->lastname;
        $this->firstname = $member->firstname;
        $this->gender = $member->gender;
        $this->email = $member->email;
        $this->fk_member = $member->id;
        $this->pass = $member->pass;
        $this->address = $member->address;
        $this->zip = $member->zip;
        $this->town = $member->town;
        $this->state_id = $member->state_id;
        $this->country_id = $member->country_id;

        if (empty($login)) {
            $login = strtolower(substr($member->firstname, 0, 4)) . strtolower(substr($member->lastname, 0, 4));
        }
        $this->login = $login;

        Config::$dbEngine->begin();

// Create and set $this->id
        $result = $this->create(Globals::$user);
        if ($result > 0) {
            $newpass = $this->setPassword(Globals::$user, $this->pass);
            if (is_numeric($newpass) && $newpass < 0) {
                $result = -2;
            }

            if ($result > 0 && $member->fk_soc) { // If member is linked to a thirdparty
                $sql = "UPDATE " . MAIN_DB_PREFIX . "user";
                $sql .= " SET fk_soc=" . $member->fk_soc;
                $sql .= " WHERE rowid=" . $this->id;

                DolUtils::dol_syslog(get_class($this) . "::create_from_member", LOG_DEBUG);
                $resql = Config::$dbEngine->query($sql);
                if ($resql) {
                    Config::$dbEngine->commit();
                    return $this->id;
                } else {
                    $this->error = Config::$dbEngine->lasterror();

                    Config::$dbEngine->rollback();
                    return -1;
                }
            }
        }

        if ($result > 0) {
            Config::$dbEngine->commit();
            return $this->id;
        } else {
// $this->error deja positionne
            Config::$dbEngine->rollback();
            return -2;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *    Assign rights by default
     *
     *    @return     integer erreur <0, si ok renvoi le nbre de droits par defaut positionnes
     */
    function set_default_rights()
    {
// phpcs:enable
        global $conf;

        $sql = "SELECT id FROM " . MAIN_DB_PREFIX . "rights_def";
        $sql .= " WHERE bydefault = 1";
        $sql .= " AND entity = " . Globals::$conf->entity;

        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            $num = Config::$dbEngine->num_rows($resql);
            $i = 0;
            $rd = array();
            while ($i < $num) {
                $row = Config::$dbEngine->fetch_row($resql);
                $rd[$i] = $row[0];
                $i++;
            }
            Config::$dbEngine->free($resql);
        }
        $i = 0;
        while ($i < $num) {

            $sql = "DELETE FROM " . MAIN_DB_PREFIX . "user_rights WHERE fk_user = $this->id AND fk_id=$rd[$i]";
            $result = Config::$dbEngine->query($sql);

            $sql = "INSERT INTO " . MAIN_DB_PREFIX . "user_rights (fk_user, fk_id) VALUES ($this->id, $rd[$i])";
            $result = Config::$dbEngine->query($sql);
            if (!$result) {
                return -1;
            }
            $i++;
        }

        return $i;
    }

    /**
     *  	Update a user into database (and also password if this->pass is defined)
     *
     * 		@param	User	Globals::$user				User qui fait la mise a jour
     *    	@param  int		$notrigger			1 ne declenche pas les triggers, 0 sinon
     * 		@param	int		$nosyncmember		0=Synchronize linked member (standard info), 1=Do not synchronize linked member
     * 		@param	int		$nosyncmemberpass	0=Synchronize linked member (password), 1=Do not synchronize linked member
     * 		@param	int		$nosynccontact		0=Synchronize linked contact, 1=Do not synchronize linked contact
     *    	@return int 		        		<0 si KO, >=0 si OK
     */
    function update($user, $notrigger = 0, $nosyncmember = 0, $nosyncmemberpass = 0, $nosynccontact = 0)
    {
//global $conf, Globals::$langs;

        $nbrowsaffected = 0;
        $error = 0;

        DolUtils::dol_syslog(get_class($this) . "::update notrigger=" . $notrigger . ", nosyncmember=" . $nosyncmember . ", nosyncmemberpass=" . $nosyncmemberpass);

// Clean parameters
        $this->lastname = trim($this->lastname);
        $this->firstname = trim($this->firstname);
        $this->employee = $this->employee ? $this->employee : 0;
        $this->login = trim($this->login);
        $this->gender = trim($this->gender);
        $this->birth = trim($this->birth);
        $this->pass = trim($this->pass);
        $this->api_key = trim($this->api_key);
        $this->address = $this->address ? trim($this->address) : trim($this->address);
        $this->zip = $this->zip ? trim($this->zip) : trim($this->zip);
        $this->town = $this->town ? trim($this->town) : trim($this->town);
        $this->state_id = trim($this->state_id);
        $this->country_id = ($this->country_id > 0) ? $this->country_id : 0;
        $this->office_phone = trim($this->office_phone);
        $this->office_fax = trim($this->office_fax);
        $this->user_mobile = trim($this->user_mobile);
        $this->email = trim($this->email);

        $this->skype = trim($this->skype);
        $this->twitter = trim($this->twitter);
        $this->facebook = trim($this->facebook);

        $this->job = trim($this->job);
        $this->signature = trim($this->signature);
        $this->note = trim($this->note);
        $this->openid = trim(empty($this->openid) ? '' : $this->openid);    // Avoid warning
        $this->admin = $this->admin ? $this->admin : 0;
        $this->address = empty($this->address) ? '' : $this->address;
        $this->zip = empty($this->zip) ? '' : $this->zip;
        $this->town = empty($this->town) ? '' : $this->town;
        $this->accountancy_code = trim($this->accountancy_code);
        $this->color = empty($this->color) ? '' : $this->color;
        $this->dateemployment = empty($this->dateemployment) ? '' : $this->dateemployment;
        $this->dateemploymentend = empty($this->dateemploymentend) ? '' : $this->dateemploymentend;

// Check parameters
        if (!empty(Globals::$conf->global->USER_MAIL_REQUIRED) && !isValidEMail($this->email)) {
            Globals::$langs->load("errors");
            $this->error = Globals::$langs->trans("ErrorBadEMail", $this->email);
            return -1;
        }
        if (empty($this->login)) {
            Globals::$langs->load("errors");
            $this->error = Globals::$langs->trans("ErrorFieldRequired", $this->login);
            return -1;
        }

        Config::$dbEngine->begin();

// Update datas
        $sql = "UPDATE " . MAIN_DB_PREFIX . "user SET";
        $sql .= " lastname = '" . Config::$dbEngine->escape($this->lastname) . "'";
        $sql .= ", firstname = '" . Config::$dbEngine->escape($this->firstname) . "'";
        $sql .= ", employee = " . $this->employee;
        $sql .= ", login = '" . Config::$dbEngine->escape($this->login) . "'";
        $sql .= ", api_key = " . ($this->api_key ? "'" . Config::$dbEngine->escape($this->api_key) . "'" : "null");
        $sql .= ", gender = " . ($this->gender != -1 ? "'" . Config::$dbEngine->escape($this->gender) . "'" : "null"); // 'man' or 'woman'
        $sql .= ", birth=" . (strval($this->birth) != '' ? "'" . Config::$dbEngine->idate($this->birth) . "'" : 'null');
        if (!empty(Globals::$user->admin)) {
            $sql .= ", admin = " . $this->admin; // admin flag can be set/unset only by an admin user
        }
        $sql .= ", address = '" . Config::$dbEngine->escape($this->address) . "'";
        $sql .= ", zip = '" . Config::$dbEngine->escape($this->zip) . "'";
        $sql .= ", town = '" . Config::$dbEngine->escape($this->town) . "'";
        $sql .= ", fk_state = " . ((!empty($this->state_id) && $this->state_id > 0) ? "'" . Config::$dbEngine->escape($this->state_id) . "'" : "null");
        $sql .= ", fk_country = " . ((!empty($this->country_id) && $this->country_id > 0) ? "'" . Config::$dbEngine->escape($this->country_id) . "'" : "null");
        $sql .= ", office_phone = '" . Config::$dbEngine->escape($this->office_phone) . "'";
        $sql .= ", office_fax = '" . Config::$dbEngine->escape($this->office_fax) . "'";
        $sql .= ", user_mobile = '" . Config::$dbEngine->escape($this->user_mobile) . "'";
        $sql .= ", email = '" . Config::$dbEngine->escape($this->email) . "'";
        $sql .= ", skype = '" . Config::$dbEngine->escape($this->skype) . "'";
        $sql .= ", twitter = '" . Config::$dbEngine->escape($this->twitter) . "'";
        $sql .= ", facebook = '" . Config::$dbEngine->escape($this->facebook) . "'";
        $sql .= ", job = '" . Config::$dbEngine->escape($this->job) . "'";
        $sql .= ", signature = '" . Config::$dbEngine->escape($this->signature) . "'";
        $sql .= ", accountancy_code = '" . Config::$dbEngine->escape($this->accountancy_code) . "'";
        $sql .= ", color = '" . Config::$dbEngine->escape($this->color) . "'";
        $sql .= ", dateemployment=" . (strval($this->dateemployment) != '' ? "'" . Config::$dbEngine->idate($this->dateemployment) . "'" : 'null');
        $sql .= ", dateemploymentend=" . (strval($this->dateemploymentend) != '' ? "'" . Config::$dbEngine->idate($this->dateemploymentend) . "'" : 'null');
        $sql .= ", note = '" . Config::$dbEngine->escape($this->note) . "'";
        $sql .= ", photo = " . ($this->photo ? "'" . Config::$dbEngine->escape($this->photo) . "'" : "null");
        $sql .= ", openid = " . ($this->openid ? "'" . Config::$dbEngine->escape($this->openid) . "'" : "null");
        $sql .= ", fk_user = " . ($this->fk_user > 0 ? "'" . Config::$dbEngine->escape($this->fk_user) . "'" : "null");
        if (isset($this->thm) || $this->thm != '') {
            $sql .= ", thm= " . ($this->thm != '' ? "'" . Config::$dbEngine->escape($this->thm) . "'" : "null");
        }
        if (isset($this->tjm) || $this->tjm != '') {
            $sql .= ", tjm= " . ($this->tjm != '' ? "'" . Config::$dbEngine->escape($this->tjm) . "'" : "null");
        }
        if (isset($this->salary) || $this->salary != '') {
            $sql .= ", salary= " . ($this->salary != '' ? "'" . Config::$dbEngine->escape($this->salary) . "'" : "null");
        }
        if (isset($this->salaryextra) || $this->salaryextra != '') {
            $sql .= ", salaryextra= " . ($this->salaryextra != '' ? "'" . Config::$dbEngine->escape($this->salaryextra) . "'" : "null");
        }
        $sql .= ", weeklyhours= " . ($this->weeklyhours != '' ? "'" . Config::$dbEngine->escape($this->weeklyhours) . "'" : "null");
        $sql .= ", entity = '" . Config::$dbEngine->escape($this->entity) . "'";
        $sql .= ", default_range = " . ($this->default_range > 0 ? $this->default_range : 'null');
        $sql .= ", default_c_exp_tax_cat = " . ($this->default_c_exp_tax_cat > 0 ? $this->default_c_exp_tax_cat : 'null');

        $sql .= " WHERE rowid = " . $this->id;

        DolUtils::dol_syslog(get_class($this) . "::update", LOG_DEBUG);
        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            $nbrowsaffected += Config::$dbEngine->affected_rows($resql);

// Update password
            if (!empty($this->pass)) {
                if ($this->pass != $this->pass_indatabase && $this->pass != $this->pass_indatabase_crypted) {
// Si mot de passe saisi et different de celui en base
                    $result = $this->setPassword(Globals::$user, $this->pass, 0, $notrigger, $nosyncmemberpass);
                    if (!$nbrowsaffected) {
                        $nbrowsaffected++;
                    }
                }
            }

// If user is linked to a member, remove old link to this member
            if ($this->fk_member > 0) {
                DolUtils::dol_syslog(get_class($this) . "::update remove link with member. We will recreate it later", LOG_DEBUG);
                $sql = "UPDATE " . MAIN_DB_PREFIX . "user SET fk_member = NULL where fk_member = " . $this->fk_member;
                $resql = Config::$dbEngine->query($sql);
                if (!$resql) {
                    $this->error = Config::$dbEngine->error();
                    Config::$dbEngine->rollback();
                    return -5;
                }
            }
// Set link to user
            DolUtils::dol_syslog(get_class($this) . "::update set link with member", LOG_DEBUG);
            $sql = "UPDATE " . MAIN_DB_PREFIX . "user SET fk_member =" . ($this->fk_member > 0 ? $this->fk_member : 'null') . " where rowid = " . $this->id;
            $resql = Config::$dbEngine->query($sql);
            if (!$resql) {
                $this->error = Config::$dbEngine->error();
                Config::$dbEngine->rollback();
                return -5;
            }

            if ($nbrowsaffected) { // If something has changed in data
                if ($this->fk_member > 0 && !$nosyncmember) {
                    DolUtils::dol_syslog(get_class($this) . "::update user is linked with a member. We try to update member too.", LOG_DEBUG);

                    require_once DOL_DOCUMENT_ROOT . '/adherents/class/adherent.class.php';

// This user is linked with a member, so we also update member information
// if this is an update.
                    $adh = new Adherent(Config::$dbEngine);
                    $result = $adh->fetch($this->fk_member);

                    if ($result >= 0) {
                        $adh->firstname = $this->firstname;
                        $adh->lastname = $this->lastname;
                        $adh->login = $this->login;
                        $adh->gender = $this->gender;
                        $adh->birth = $this->birth;

                        $adh->pass = $this->pass;

                        $adh->societe = (empty($adh->societe) && $this->societe_id ? $this->societe_id : $adh->societe);

                        $adh->address = $this->address;
                        $adh->town = $this->town;
                        $adh->zip = $this->zip;
                        $adh->state_id = $this->state_id;
                        $adh->country_id = $this->country_id;

                        $adh->email = $this->email;

                        $adh->skype = $this->skype;
                        $adh->twitter = $this->twitter;
                        $adh->facebook = $this->facebook;

                        $adh->phone = $this->office_phone;
                        $adh->phone_mobile = $this->user_mobile;

                        $adh->user_id = $this->id;
                        $adh->user_login = $this->login;

                        $result = $adh->update(Globals::$user, 0, 1, 0);
                        if ($result < 0) {
                            $this->error = $adh->error;
                            $this->errors = $adh->errors;
                            DolUtils::dol_syslog(get_class($this) . "::update error after calling adh->update to sync it with user: " . $this->error, LOG_ERR);
                            $error++;
                        }
                    } else {
                        $this->error = $adh->error;
                        $this->errors = $adh->errors;
                        $error++;
                    }
                }

                if ($this->contact_id > 0 && !$nosynccontact) {
                    DolUtils::dol_syslog(get_class($this) . "::update user is linked with a contact. We try to update contact too.", LOG_DEBUG);

                    require_once DOL_DOCUMENT_ROOT . '/contact/class/contact.class.php';

// This user is linked with a contact, so we also update contact information
// if this is an update.
                    $tmpobj = new Contact(Config::$dbEngine);
                    $result = $tmpobj->fetch($this->contact_id);

                    if ($result >= 0) {
                        $tmpobj->firstname = $this->firstname;
                        $tmpobj->lastname = $this->lastname;
                        $tmpobj->login = $this->login;
                        $tmpobj->gender = $this->gender;
                        $tmpobj->birth = $this->birth;

//$tmpobj->pass=$this->pass;
//$tmpobj->societe=(empty($tmpobj->societe) && $this->societe_id ? $this->societe_id : $tmpobj->societe);

                        $tmpobj->email = $this->email;

                        $tmpobj->skype = $this->skype;
                        $tmpobj->twitter = $this->twitter;
                        $tmpobj->facebook = $this->facebook;

                        $tmpobj->phone_pro = $this->office_phone;
                        $tmpobj->phone_mobile = $this->user_mobile;
                        $tmpobj->fax = $this->office_fax;

                        $tmpobj->address = $this->address;
                        $tmpobj->town = $this->town;
                        $tmpobj->zip = $this->zip;
                        $tmpobj->state_id = $this->state_id;
                        $tmpobj->country_id = $this->country_id;

                        $tmpobj->user_id = $this->id;
                        $tmpobj->user_login = $this->login;

                        $result = $tmpobj->update($tmpobj->id, Globals::$user, 0, 'update', 1);
                        if ($result < 0) {
                            $this->error = $tmpobj->error;
                            $this->errors = $tmpobj->errors;
                            DolUtils::dol_syslog(get_class($this) . "::update error after calling adh->update to sync it with user: " . $this->error, LOG_ERR);
                            $error++;
                        }
                    } else {
                        $this->error = $tmpobj->error;
                        $this->errors = $tmpobj->errors;
                        $error++;
                    }
                }
            }

            $action = 'update';

// Actions on extra fields
            if (!$error && empty(Globals::$conf->global->MAIN_EXTRAFIELDS_DISABLED)) {
                $result = $this->insertExtraFields();
                if ($result < 0) {
                    $error++;
                }
            }

            if (!$error && !$notrigger) {
// Call trigger
                $result = $this->call_trigger('USER_MODIFY', Globals::$user);
                if ($result < 0) {
                    $error++;
                }
// End call triggers
            }

            if (!$error) {
                Config::$dbEngine->commit();
                return $nbrowsaffected;
            } else {
                DolUtils::dol_syslog(get_class($this) . "::update error=" . $this->error, LOG_ERR);
                Config::$dbEngine->rollback();
                return -1;
            }
        } else {
            $this->error = Config::$dbEngine->lasterror();
            Config::$dbEngine->rollback();
            return -2;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *    Mise a jour en base de la date de derniere connexion d'un utilisateur
     * 	  Fonction appelee lors d'une nouvelle connexion
     *
     *    @return     <0 si echec, >=0 si ok
     */
    function update_last_login_date()
    {
        // phpcs:enable
        // $now = DolUtils::dol_now();
        $now = date("Y-m-d H:i:s");

        $sql = "UPDATE " . MAIN_DB_PREFIX . "user SET";
        $sql .= " datepreviouslogin = datelastlogin,";
        // $sql .= " datelastlogin = '" . Config::$dbEngine->idate($now) . "',";
        $sql .= " datelastlogin = '{$now}',";
        $sql .= " tms = tms";    // La date de derniere modif doit changer sauf pour la mise a jour de date de derniere connexion
        $sql .= " WHERE rowid = " . $this->id;

        DolUtils::dol_syslog(get_class($this) . "::update_last_login_date user->id=" . $this->id . " " . $sql, LOG_DEBUG);
        $resql = Config::$dbEngine->exec($sql);
        if ($resql) {
            $this->datepreviouslogin = $this->datelastlogin;
            $this->datelastlogin = $now;
            return 1;
        } else {
            $this->error = Config::$dbEngine->lasterror() . ' sql=' . $sql;
            return -1;
        }
    }

    /**
     *  Change password of a user
     *
     *  @param	User	Globals::$user             		Object user of user making change
     *  @param  string	$password         		New password in clear text (to generate if not provided)
     * 	@param	int		$changelater			1=Change password only after clicking on confirm email
     * 	@param	int		$notrigger				1=Does not launch triggers
     * 	@param	int		$nosyncmember	        Do not synchronize linked member
     *  @return string 			          		If OK return clear password, 0 if no change, < 0 if error
     */
    function setPassword($user, $password = '', $changelater = 0, $notrigger = 0, $nosyncmember = 0)
    {
//global $conf, Globals::$langs;
        require_once DOL_DOCUMENT_ROOT . '/core/lib/security2.lib.php';

        $error = 0;

        DolUtils::dol_syslog(get_class($this) . "::setPassword user=" . Globals::$user->id . " password=" . preg_replace('/./i', '*', $password) . " changelater=" . $changelater . " notrigger=" . $notrigger . " nosyncmember=" . $nosyncmember, LOG_DEBUG);

// If new password not provided, we generate one
        if (!$password) {
            $password = getRandomPassword(false);
        }

// Crypt password
        $password_crypted = dol_hash($password);

// Mise a jour
        if (!$changelater) {
            if (!is_object($this->oldcopy)) {
                $this->oldcopy = clone $this;
            }

            Config::$dbEngine->begin();

            $sql = "UPDATE " . MAIN_DB_PREFIX . "user";
            $sql .= " SET pass_crypted = '" . Config::$dbEngine->escape($password_crypted) . "',";
            $sql .= " pass_temp = null";
            if (!empty(Globals::$conf->global->DATABASE_PWD_ENCRYPTED)) {
                $sql .= ", pass = null";
            } else {
                $sql .= ", pass = '" . Config::$dbEngine->escape($password) . "'";
            }
            $sql .= " WHERE rowid = " . $this->id;

            DolUtils::dol_syslog(get_class($this) . "::setPassword", LOG_DEBUG);
            $result = Config::$dbEngine->query($sql);
            if ($result) {
                if (Config::$dbEngine->affected_rows($result)) {
                    $this->pass = $password;
                    $this->pass_indatabase = $password;
                    $this->pass_indatabase_crypted = $password_crypted;

                    if ($this->fk_member && !$nosyncmember) {
                        require_once DOL_DOCUMENT_ROOT . '/adherents/class/adherent.class.php';

// This user is linked with a member, so we also update members informations
// if this is an update.
                        $adh = new Adherent(Config::$dbEngine);
                        $result = $adh->fetch($this->fk_member);

                        if ($result >= 0) {
                            $result = $adh->setPassword(Globals::$user, $this->pass, (empty(Globals::$conf->global->DATABASE_PWD_ENCRYPTED) ? 0 : 1), 1); // Cryptage non gere dans module adherent
                            if ($result < 0) {
                                $this->error = $adh->error;
                                DolUtils::dol_syslog(get_class($this) . "::setPassword " . $this->error, LOG_ERR);
                                $error++;
                            }
                        } else {
                            $this->error = $adh->error;
                            $error++;
                        }
                    }

                    DolUtils::dol_syslog(get_class($this) . "::setPassword notrigger=" . $notrigger . " error=" . $error, LOG_DEBUG);

                    if (!$error && !$notrigger) {
// Call trigger
                        $result = $this->call_trigger('USER_NEW_PASSWORD', Globals::$user);
                        if ($result < 0) {
                            $error++;
                            Config::$dbEngine->rollback();
                            return -1;
                        }
// End call triggers
                    }

                    Config::$dbEngine->commit();
                    return $this->pass;
                } else {
                    Config::$dbEngine->rollback();
                    return 0;
                }
            } else {
                Config::$dbEngine->rollback();
                dol_print_error(Config::$dbEngine);
                return -1;
            }
        } else {
// We store clear password in password temporary field.
// After receiving confirmation link, we will crypt it and store it in pass_crypted
            $sql = "UPDATE " . MAIN_DB_PREFIX . "user";
            $sql .= " SET pass_temp = '" . Config::$dbEngine->escape($password) . "'";
            $sql .= " WHERE rowid = " . $this->id;

            DolUtils::dol_syslog(get_class($this) . "::setPassword", LOG_DEBUG); // No log
            $result = Config::$dbEngine->query($sql);
            if ($result) {
                return $password;
            } else {
                dol_print_error(Config::$dbEngine);
                return -3;
            }
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Send new password by email
     *
     *  @param	User	Globals::$user           Object user that send email
     *  @param	string	$password       New password
     * 	@param	int		$changelater	0=Send clear passwod into email, 1=Change password only after clicking on confirm email. @TODO Add method 2 = Send link to reset password
     *  @return int 		            < 0 si erreur, > 0 si ok
     */
    function send_password($user, $password = '', $changelater = 0)
    {
// phpcs:enable
//global $conf, Globals::$langs;
//global $dolibarr_main_url_root;

        require_once DOL_DOCUMENT_ROOT . '/core/class/CMailFile.class.php';

        $msgishtml = 0;

// Define $msg
        $mesg = '';

        $outputlangs = new Translate("", $conf);
        if (isset($this->conf->MAIN_LANG_DEFAULT) && $this->conf->MAIN_LANG_DEFAULT != 'auto') { // If user has defined its own language (rare because in most cases, auto is used)
            $outputlangs->getDefaultLang($this->conf->MAIN_LANG_DEFAULT);
        } else { // If user has not defined its own language, we used current language
            $outputlangs = Globals::$langs;
        }

// Load translation files required by the page
        $outputlangs->loadLangs(array("main", "errors", "users", "other"));

        $appli = constant('DOL_APPLICATION_TITLE');
        if (!empty(Globals::$conf->global->MAIN_APPLICATION_TITLE)) {
            $appli = Globals::$conf->global->MAIN_APPLICATION_TITLE;
        }

        $subject = $outputlangs->transnoentitiesnoconv("SubjectNewPassword", $appli);

// Define $urlwithroot
        $urlwithouturlroot = preg_replace('/' . preg_quote(DOL_URL_ROOT, '/') . '$/i', '', trim($dolibarr_main_url_root));
        $urlwithroot = $urlwithouturlroot . DOL_URL_ROOT;  // This is to use external domain name found into config file

        if (!$changelater) {
            $url = $urlwithroot . '/';

            $mesg .= $outputlangs->transnoentitiesnoconv("RequestToResetPasswordReceived") . ".\n";
            $mesg .= $outputlangs->transnoentitiesnoconv("NewKeyIs") . " :\n\n";
            $mesg .= $outputlangs->transnoentitiesnoconv("Login") . " = " . $this->login . "\n";
            $mesg .= $outputlangs->transnoentitiesnoconv("Password") . " = " . $password . "\n\n";
            $mesg .= "\n";

            $mesg .= $outputlangs->transnoentitiesnoconv("ClickHereToGoTo", $appli) . ': ' . $url . "\n\n";
            $mesg .= "--\n";
            $mesg .= Globals::$user->getFullName($outputlangs); // Username that make then sending

            DolUtils::dol_syslog(get_class($this) . "::send_password changelater is off, url=" . $url);
        } else {
            $url = $urlwithroot . '/user/passwordforgotten.php?action=validatenewpassword&username=' . $this->login . "&passwordhash=" . dol_hash($password);

            $mesg .= $outputlangs->transnoentitiesnoconv("RequestToResetPasswordReceived") . "\n";
            $mesg .= $outputlangs->transnoentitiesnoconv("NewKeyWillBe") . " :\n\n";
            $mesg .= $outputlangs->transnoentitiesnoconv("Login") . " = " . $this->login . "\n";
            $mesg .= $outputlangs->transnoentitiesnoconv("Password") . " = " . $password . "\n\n";
            $mesg .= "\n";
            $mesg .= $outputlangs->transnoentitiesnoconv("YouMustClickToChange") . " :\n";
            $mesg .= $url . "\n\n";
            $mesg .= $outputlangs->transnoentitiesnoconv("ForgetIfNothing") . "\n\n";

            DolUtils::dol_syslog(get_class($this) . "::send_password changelater is on, url=" . $url);
        }

        $mailfile = new CMailFile(
            $subject, $this->email, Globals::$conf->global->MAIN_MAIL_EMAIL_FROM, $mesg, array(), array(), array(), '', '', 0, $msgishtml
        );

        if ($mailfile->sendfile()) {
            return 1;
        } else {
            Globals::$langs->trans("errors");
            $this->error = Globals::$langs->trans("ErrorFailedToSendPassword") . ' ' . $mailfile->error;
            return -1;
        }
    }

    /**
     * 		Renvoie la derniere erreur fonctionnelle de manipulation de l'objet
     *
     * 		@return    string      chaine erreur
     */
    function error()
    {
        return $this->error;
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *    	Read clicktodial information for user
     *
     * 		@return		<0 if KO, >0 if OK
     */
    function fetch_clicktodial()
    {
// phpcs:enable
        $sql = "SELECT url, login, pass, poste ";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user_clicktodial as u";
        $sql .= " WHERE u.fk_user = " . $this->id;

        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            if (Config::$dbEngine->num_rows($resql)) {
                $obj = Config::$dbEngine->fetch_object($resql);

                $this->clicktodial_url = $obj->url;
                $this->clicktodial_login = $obj->login;
                $this->clicktodial_password = $obj->pass;
                $this->clicktodial_poste = $obj->poste;
            }

            $this->clicktodial_loaded = 1; // Data loaded (found or not)

            Config::$dbEngine->free($resql);
            return 1;
        } else {
            $this->error = Config::$dbEngine->error();
            return -1;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Update clicktodial info
     *
     *  @return	integer
     */
    function update_clicktodial()
    {
// phpcs:enable
        Config::$dbEngine->begin();

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "user_clicktodial";
        $sql .= " WHERE fk_user = " . $this->id;

        DolUtils::dol_syslog(get_class($this) . '::update_clicktodial', LOG_DEBUG);
        $result = Config::$dbEngine->query($sql);

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "user_clicktodial";
        $sql .= " (fk_user,url,login,pass,poste)";
        $sql .= " VALUES (" . $this->id;
        $sql .= ", '" . Config::$dbEngine->escape($this->clicktodial_url) . "'";
        $sql .= ", '" . Config::$dbEngine->escape($this->clicktodial_login) . "'";
        $sql .= ", '" . Config::$dbEngine->escape($this->clicktodial_password) . "'";
        $sql .= ", '" . Config::$dbEngine->escape($this->clicktodial_poste) . "')";

        DolUtils::dol_syslog(get_class($this) . '::update_clicktodial', LOG_DEBUG);
        $result = Config::$dbEngine->query($sql);
        if ($result) {
            Config::$dbEngine->commit();
            return 1;
        } else {
            Config::$dbEngine->rollback();
            $this->error = Config::$dbEngine->lasterror();
            return -1;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Add user into a group
     *
     *  @param	int	$group      Id of group
     *  @param  int		$entity     Entity
     *  @param  int		$notrigger  Disable triggers
     *  @return int  				<0 if KO, >0 if OK
     */
    function SetInGroup($group, $entity, $notrigger = 0)
    {
// phpcs:enable
//global $conf, Globals::$langs, Globals::$user;

        $error = 0;

        Config::$dbEngine->begin();

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "usergroup_user";
        $sql .= " WHERE fk_user  = " . $this->id;
        $sql .= " AND fk_usergroup = " . $group;
        $sql .= " AND entity = " . $entity;

        $result = Config::$dbEngine->query($sql);

        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "usergroup_user (entity, fk_user, fk_usergroup)";
        $sql .= " VALUES (" . $entity . "," . $this->id . "," . $group . ")";

        $result = Config::$dbEngine->query($sql);
        if ($result) {
            if (!$error && !$notrigger) {
                $this->newgroupid = $group;    // deprecated. Remove this.
                $this->context = array('audit' => Globals::$langs->trans("UserSetInGroup"), 'newgroupid' => $group);

// Call trigger
                $result = $this->call_trigger('USER_MODIFY', Globals::$user);
                if ($result < 0) {
                    $error++;
                }
// End call triggers
            }

            if (!$error) {
                Config::$dbEngine->commit();
                return 1;
            } else {
                DolUtils::dol_syslog(get_class($this) . "::SetInGroup " . $this->error, LOG_ERR);
                Config::$dbEngine->rollback();
                return -2;
            }
        } else {
            $this->error = Config::$dbEngine->lasterror();
            Config::$dbEngine->rollback();
            return -1;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Remove a user from a group
     *
     *  @param	int   $group       Id of group
     *  @param  int		$entity      Entity
     *  @param  int		$notrigger   Disable triggers
     *  @return int  			     <0 if KO, >0 if OK
     */
    function RemoveFromGroup($group, $entity, $notrigger = 0)
    {
// phpcs:enable
//global $conf, Globals::$langs, Globals::$user;

        $error = 0;

        Config::$dbEngine->begin();

        $sql = "DELETE FROM " . MAIN_DB_PREFIX . "usergroup_user";
        $sql .= " WHERE fk_user  = " . $this->id;
        $sql .= " AND fk_usergroup = " . $group;
        $sql .= " AND entity = " . $entity;

        $result = Config::$dbEngine->query($sql);
        if ($result) {
            if (!$error && !$notrigger) {
                $this->oldgroupid = $group;    // deprecated. Remove this.
                $this->context = array('audit' => Globals::$langs->trans("UserRemovedFromGroup"), 'oldgroupid' => $group);

// Call trigger
                $result = $this->call_trigger('USER_MODIFY', Globals::$user);
                if ($result < 0) {
                    $error++;
                }
// End call triggers
            }

            if (!$error) {
                Config::$dbEngine->commit();
                return 1;
            } else {
                $this->error = $interface->error;
                DolUtils::dol_syslog(get_class($this) . "::RemoveFromGroup " . $this->error, LOG_ERR);
                Config::$dbEngine->rollback();
                return -2;
            }
        } else {
            $this->error = Config::$dbEngine->lasterror();
            Config::$dbEngine->rollback();
            return -1;
        }
    }

    /**
     *  Return a link with photo
     * 	Use this->id,this->photo
     *
     * 	@param	int		$width			Width of image
     * 	@param	int		$height			Height of image
     *  @param	string	$cssclass		Force a css class
     * 	@param	string	$imagesize		'mini', 'small' or '' (original)
     * 	@return	string					String with URL link
     */
    function getPhotoUrl($width, $height, $cssclass = '', $imagesize = '')
    {
// $result = '<a href="' . DOL_URL_ROOT . '/user/card.php?id=' . $this->id . '">';
        $result = '<a href="' . BASE_URI . '?controller=user&method=card&id=' . $this->id . '">';
        $result .= Form::showphoto('userphoto', $this, $width, $height, 0, $cssclass, $imagesize);
        $result .= '</a>';

        return $result;
    }

    /**
     *  Return a link to the user card (with optionaly the picto)
     * 	Use this->id,this->lastname, this->firstname
     *
     * 	@param	int		$withpictoimg				Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto, -1=Include photo into link, -2=Only picto photo, -3=Only photo very small)
     * 	@param	string	$option						On what the link point to ('leave', 'nolink', )
     *  @param  integer $infologin      			0=Add default info tooltip, 1=Add complete info tooltip, -1=No info tooltip
     *  @param	integer	$notooltip					1=Disable tooltip on picto and name
     *  @param	int		$maxlen						Max length of visible user name
     *  @param	int		$hidethirdpartylogo			Hide logo of thirdparty if user is external user
     *  @param  string  $mode               		''=Show firstname and lastname, 'firstname'=Show only firstname, 'login'=Show login
     *  @param  string  $morecss            		Add more css on link
     *  @param  int     $save_lastsearch_value    	-1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
     * 	@return	string								String with URL
     */
    function getNomUrl($withpictoimg = 0, $option = '', $infologin = 0, $notooltip = 0, $maxlen = 24, $hidethirdpartylogo = 0, $mode = '', $morecss = '', $save_lastsearch_value = -1)
    {
//global Globals::$langs, $conf, $db, Globals::$hookManager, Globals::$user;
//global $dolibarr_main_authentication, $dolibarr_main_demo;
//global $menumanager;

        if (!Globals::$user->rights->user->user->lire && Globals::$user->id != $this->id) {
            $option = 'nolink';
        }

        if (!empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER) && $withpictoimg) {
            $withpictoimg = 0;
        }

        $result = '';
        $label = '';
        $link = '';
        $linkstart = '';
        $linkend = '';

        if (!empty($this->photo)) {
            $label .= '<div class="photointooltip">';
            $label .= Form::showphoto('userphoto', $this, 0, 60, 0, 'photowithmargin photologintooltip', 'small', 0, 1); // Force height to 60 so we total height of tooltip can be calculated and collision can be managed
            $label .= '</div><div style="clear: both;"></div>';
        }

// Info Login
        $label .= '<div class="centpercent">';
        $label .= '<u>' . Globals::$langs->trans("User") . '</u><br>';
        $label .= '<b>' . Globals::$langs->trans('Name') . ':</b> ' . $this->getFullName(Globals::$langs, '');
        if (!empty($this->login)) {
            $label .= '<br><b>' . Globals::$langs->trans('Login') . ':</b> ' . $this->login;
        }
        $label .= '<br><b>' . Globals::$langs->trans("EMail") . ':</b> ' . $this->email;
        if (!empty($this->admin)) {
            $label .= '<br><b>' . Globals::$langs->trans("Administrator") . '</b>: ' . DolUtils::yn($this->admin);
        }
        if (!empty($this->socid)) { // Add thirdparty for external users
            $thirdpartystatic = new Societe($db);
            $thirdpartystatic->fetch($this->socid);
            if (empty($hidethirdpartylogo)) {
                $companylink = ' ' . $thirdpartystatic->getNomUrl(2, (($option == 'nolink') ? 'nolink' : '')); // picto only of company
            }
            $company = ' (' . Globals::$langs->trans("Company") . ': ' . $thirdpartystatic->name . ')';
        }
        $type = ($this->socid ? Globals::$langs->trans("External") . $company : Globals::$langs->trans("Internal"));
        $label .= '<br><b>' . Globals::$langs->trans("Type") . ':</b> ' . $type;
        $label .= '<br><b>' . Globals::$langs->trans("Status") . '</b>: ' . $this->getLibStatut(0);
        $label .= '</div>';
        if ($infologin > 0) {
            $label .= '<br>';
            $label .= '<br><u>' . Globals::$langs->trans("Connection") . '</u>';
            $label .= '<br><b>' . Globals::$langs->trans("IPAddress") . '</b>: ' . $_SERVER["REMOTE_ADDR"];
            if (!empty(Globals::$conf->global->MAIN_MODULE_MULTICOMPANY)) {
                $label .= '<br><b>' . Globals::$langs->trans("ConnectedOnMultiCompany") . ':</b> ' . Globals::$conf->entity . ' (user entity ' . $this->entity . ')';
            }
            $label .= '<br><b>' . Globals::$langs->trans("AuthenticationMode") . ':</b> ' . $_SESSION["dol_authmode"] . (empty($dolibarr_main_demo) ? '' : ' (demo)');
            $label .= '<br><b>' . Globals::$langs->trans("ConnectedSince") . ':</b> ' . DolUtils::dol_print_date($this->datelastlogin, "dayhour", 'tzuser');
            $label .= '<br><b>' . Globals::$langs->trans("PreviousConnexion") . ':</b> ' . DolUtils::dol_print_date($this->datepreviouslogin, "dayhour", 'tzuser');
            $label .= '<br><b>' . Globals::$langs->trans("CurrentTheme") . ':</b> ' . Globals::$conf->theme;
            $label .= '<br><b>' . Globals::$langs->trans("CurrentMenuManager") . ':</b> ' . Globals::$menuManager->name;
            $s = DolUtils::picto_from_langcode(Globals::$langs->getDefaultLang());
            $label .= '<br><b>' . Globals::$langs->trans("CurrentUserLanguage") . ':</b> ' . ($s ? $s . ' ' : '') . Globals::$langs->getDefaultLang();
            $label .= '<br><b>' . Globals::$langs->trans("Browser") . ':</b> ' . Globals::$conf->browser->name . (Globals::$conf->browser->version ? ' ' . Globals::$conf->browser->version : '') . ' (' . $_SERVER['HTTP_USER_AGENT'] . ')';
            $label .= '<br><b>' . Globals::$langs->trans("Layout") . ':</b> ' . Globals::$conf->browser->layout;
            $label .= '<br><b>' . Globals::$langs->trans("Screen") . ':</b> ' . $_SESSION['dol_screenwidth'] . ' x ' . $_SESSION['dol_screenheight'];
            if (Globals::$conf->browser->layout == 'phone') {
                $label .= '<br><b>' . Globals::$langs->trans("Phone") . ':</b> ' . Globals::$langs->trans("Yes");
            }
            if (!empty($_SESSION["disablemodules"])) {
                $label .= '<br><b>' . Globals::$langs->trans("DisabledModules") . ':</b> <br>' . join(', ', explode(',', $_SESSION["disablemodules"]));
            }
        }
        if ($infologin < 0) {
            $label = '';
        }

// $url = DOL_URL_ROOT . '/user/card.php?id=' . $this->id;
        $url = BASE_URI . '?controller=user&method=card&id=' . $this->id;
        if ($option == 'leave') {
//$url = DOL_URL_ROOT . '/holiday/list.php?id=' . $this->id;
            $url = BASE_URI . '?controller=holiday&method=list&id=' . $this->id;
        }
        if ($option != 'nolink') {
// Add param to save lastsearch_values or not
            $add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
            if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
                $add_save_lastsearch_values = 1;
            }
            if ($add_save_lastsearch_values) {
                $url .= '&save_lastsearch_values=1';
            }
        }

        $linkstart = '<a href="' . $url . '"';
        $linkclose = "";
        if (empty($notooltip)) {
            if (!empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                Globals::$langs->load("users");
                $label = Globals::$langs->trans("ShowUser");
                $linkclose .= ' alt="' . DolUtils::dol_escape_htmltag($label, 1) . '"';
            }
            $linkclose .= ' title="' . DolUtils::dol_escape_htmltag($label, 1) . '"';
            $linkclose .= ' class="classfortooltip' . ($morecss ? ' ' . $morecss : '') . '"';

            /*
              Globals::$hookManager->initHooks(array('userdao'));
              $parameters=array('id'=>$this->id);
              $reshook=Globals::$hookManager->executeHooks('getnomurltooltip',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks
              if ($reshook > 0) $linkclose = Globals::$hookManager->resPrint;
             */
        }

        $linkstart .= $linkclose . '>';
        $linkend = '</a>';

//if ($withpictoimg == -1) $result.='<div class="nowrap">';
        $result .= (($option == 'nolink') ? '' : $linkstart);
        if ($withpictoimg) {
            $paddafterimage = '';
            if (abs($withpictoimg) == 1) {
                $paddafterimage = 'style="margin-right: 3px;"';
            }
// Only picto
            if ($withpictoimg > 0) {
                $picto = '<!-- picto user --><div class="inline-block nopadding userimg' . ($morecss ? ' ' . $morecss : '') . '">' . img_object('', 'user', $paddafterimage . ' ' . ($notooltip ? '' : 'class="classfortooltip"'), 0, 0, $notooltip ? 0 : 1) . '</div>';
            } else { // Picto must be a photo
                $picto = '<!-- picto photo user --><div class="inline-block nopadding userimg' . ($morecss ? ' ' . $morecss : '') . '"' . ($paddafterimage ? ' ' . $paddafterimage : '') . '>' . Form::showphoto('userphoto', $this, 0, 0, 0, 'userphoto' . ($withpictoimg == -3 ? 'small' : ''), 'mini', 0, 1) . '</div>';
            }
            $result .= $picto;
        }
        if ($withpictoimg > -2 && $withpictoimg != 2) {
            if (empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
                $result .= '<div class="inline-block nopadding valignmiddle usertext' . ((!isset($this->statut) || $this->statut) ? '' : ' strikefordisabled') . ($morecss ? ' ' . $morecss : '') . '">';
            }
            if ($mode == 'login') {
                $result .= dol_trunc($this->login, $maxlen);
            } else {
                $result .= $this->getFullName(Globals::$langs, '', ($mode == 'firstname' ? 2 : -1), $maxlen);
            }
            if (empty(Globals::$conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
                $result .= '</div>';
        }
        $result .= (($option == 'nolink') ? '' : $linkend);
//if ($withpictoimg == -1) $result.='</div>';

        if (isset($companylink)) {
            $result .= $companylink;
        }

        global $action;
        Globals::$hookManager->initHooks(array('userdao'));
        $parameters = array('id' => $this->id, 'getnomurl' => $result);
        $reshook = Globals::$hookManager->executeHooks('getNomUrl', $parameters, $this, $action);    // Note that $action and $object may have been modified by some hooks
        if ($reshook > 0) {
            $result = Globals::$hookManager->resPrint;
        } else {
            $result .= Globals::$hookManager->resPrint;
        }

        return $result;
    }

    /**
     *  Return clickable link of login (eventualy with picto)
     *
     * 	@param	int		$withpicto		Include picto into link
     * 	@param	string	$option			Sur quoi pointe le lien
     * 	@return	string					Chaine avec URL
     */
    function getLoginUrl($withpicto = 0, $option = '')
    {
        //global Globals::$langs, Globals::$user;

        $result = '';

// $linkstart = '<a href="'.DOL_URL_ROOT.'/user/card.php?id='.$this->id.'">';
        $linkstart = '<a href="' . BASE_URI . '?controller=user&method=card&id=' . $this->id . '">';
        $linkend = '</a>';

//Check user's rights to see an other user
        if ((!Globals::$user->rights->user->user->lire && $this->id != Globals::$user->id)) {
            $option = 'nolink';
        }

        if ($option == 'xxx') {
//$linkstart = '<a href="'.DOL_URL_ROOT.'/user/card.php?id='.$this->id.'">';
            $linkstart = '<a href="' . BASE_URI . '?controller=user&method=card.php&id=' . $this->id . '">';
            $linkend = '</a>';
        }

        if ($option == 'nolink') {
            $linkstart = '';
            $linkend = '';
        }

        $result .= $linkstart;
        if ($withpicto) {
            $result .= img_object(Globals::$langs->trans("ShowUser"), 'user', 'class="paddingright"');
        }
        $result .= $this->login;
        $result .= $linkend;
        return $result;
    }

    /**
     *  Return label of status of user (active, inactive)
     *
     *  @param	int		$mode          0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *  @return	string 			       Label of status
     */
    function getLibStatut($mode = 0)
    {
        return $this->LibStatut($this->statut, $mode);
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Renvoi le libelle d'un statut donne
     *
     *  @param	int		$statut        	Id statut
     *  @param  int		$mode          	0=libelle long, 1=libelle court, 2=Picto + Libelle court, 3=Picto, 4=Picto + Libelle long, 5=Libelle court + Picto
     *  @return string 			       	Label of status
     */
    function LibStatut($statut, $mode = 0)
    {
// phpcs:enable
        //global Globals::$langs;
        Globals::$langs->load('users');

        if ($mode == 0) {
            if ($statut == 1) {
                return Globals::$langs->trans('Enabled');
            } elseif ($statut == 0) {
                return Globals::$langs->trans('Disabled');
            }
        } elseif ($mode == 1) {
            if ($statut == 1) {
                return Globals::$langs->trans('Enabled');
            } elseif ($statut == 0) {
                return Globals::$langs->trans('Disabled');
            }
        } elseif ($mode == 2) {
            if ($statut == 1) {
                return img_picto(Globals::$langs->trans('Enabled'), 'statut4', 'class="pictostatus"') . ' ' . Globals::$langs->trans('Enabled');
            } elseif ($statut == 0) {
                return img_picto(Globals::$langs->trans('Disabled'), 'statut5', 'class="pictostatus"') . ' ' . Globals::$langs->trans('Disabled');
            }
        } elseif ($mode == 3) {
            if ($statut == 1) {
                return img_picto(Globals::$langs->trans('Enabled'), 'statut4', 'class="pictostatus"');
            } elseif ($statut == 0) {
                return img_picto(Globals::$langs->trans('Disabled'), 'statut5', 'class="pictostatus"');
            }
        } elseif ($mode == 4) {
            if ($statut == 1) {
                return img_picto(Globals::$langs->trans('Enabled'), 'statut4', 'class="pictostatus"') . ' ' . Globals::$langs->trans('Enabled');
            } elseif ($statut == 0) {
                return img_picto(Globals::$langs->trans('Disabled'), 'statut5', 'class="pictostatus"') . ' ' . Globals::$langs->trans('Disabled');
            }
        } elseif ($mode == 5) {
            if ($statut == 1) {
                return Globals::$langs->trans('Enabled') . ' ' . img_picto(Globals::$langs->trans('Enabled'), 'statut4', 'class="pictostatus"');
            } elseif ($statut == 0) {
                return Globals::$langs->trans('Disabled') . ' ' . img_picto(Globals::$langs->trans('Disabled'), 'statut5', 'class="pictostatus"');
            }
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     * 	Retourne chaine DN complete dans l'annuaire LDAP pour l'objet
     *
     * 	@param	array	$info		Info array loaded by _load_ldap_info
     * 	@param	int		$mode		0=Return full DN (uid=qqq,ou=xxx,dc=aaa,dc=bbb)
     * 								1=Return parent (ou=xxx,dc=aaa,dc=bbb)
     * 								2=Return key only (RDN) (uid=qqq)
     * 	@return	string				DN
     */
    function _load_ldap_dn($info, $mode = 0)
    {
// phpcs:enable
        global $conf;
        $dn = '';
        if ($mode == 0) {
            $dn = Globals::$conf->global->LDAP_KEY_USERS . "=" . $info[Globals::$conf->global->LDAP_KEY_USERS] . "," . Globals::$conf->global->LDAP_USER_DN;
        } elseif ($mode == 1) {
            $dn = Globals::$conf->global->LDAP_USER_DN;
        } elseif ($mode == 2) {
            $dn = Globals::$conf->global->LDAP_KEY_USERS . "=" . $info[Globals::$conf->global->LDAP_KEY_USERS];
        }
        return $dn;
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     * 	Initialize the info array (array of LDAP values) that will be used to call LDAP functions
     *
     * 	@return		array		Tableau info des attributs
     */
    function _load_ldap_info()
    {
// phpcs:enable
        //global $conf, Globals::$langs;

        $info = array();
        $keymodified = false;

// Object classes
        $info["objectclass"] = explode(',', Globals::$conf->global->LDAP_USER_OBJECT_CLASS);

        $this->fullname = $this->getFullName(Globals::$langs);

// Possible LDAP KEY (constname => varname)
        $ldapkey = array(
            'LDAP_FIELD_FULLNAME' => 'fullname',
            'LDAP_FIELD_NAME' => 'lastname',
            'LDAP_FIELD_FIRSTNAME' => 'firstname',
            'LDAP_FIELD_LOGIN' => 'login',
            'LDAP_FIELD_LOGIN_SAMBA' => 'login',
            'LDAP_FIELD_PHONE' => 'office_phone',
            'LDAP_FIELD_MOBILE' => 'user_mobile',
            'LDAP_FIELD_FAX' => 'office_fax',
            'LDAP_FIELD_MAIL' => 'email',
            'LDAP_FIELD_SID' => 'ldap_sid',
            'LDAP_FIELD_SKYPE' => 'skype',
            'LDAP_FIELD_TWITTER' => 'twitter',
            'LDAP_FIELD_FACEBOOK' => 'facebook'
        );

// Champs
        foreach ($ldapkey as $constname => $varname) {
            if (!empty($this->$varname) && !empty(Globals::$conf->global->$constname)) {
                $info[Globals::$conf->global->$constname] = $this->$varname;

// Check if it is the LDAP key and if its value has been changed
                if (!empty(Globals::$conf->global->LDAP_KEY_USERS) && Globals::$conf->global->LDAP_KEY_USERS == Globals::$conf->global->$constname) {
                    if (!empty($this->oldcopy) && $this->$varname != $this->oldcopy->$varname) {
                        $keymodified = true; // For check if LDAP key has been modified
                    }
                }
            }
        }
        if ($this->address && !empty(Globals::$conf->global->LDAP_FIELD_ADDRESS)) {
            $info[Globals::$conf->global->LDAP_FIELD_ADDRESS] = $this->address;
        }
        if ($this->zip && !empty(Globals::$conf->global->LDAP_FIELD_ZIP)) {
            $info[Globals::$conf->global->LDAP_FIELD_ZIP] = $this->zip;
        }
        if ($this->town && !empty(Globals::$conf->global->LDAP_FIELD_TOWN)) {
            $info[Globals::$conf->global->LDAP_FIELD_TOWN] = $this->town;
        }
        if ($this->note_public && !empty(Globals::$conf->global->LDAP_FIELD_DESCRIPTION)) {
            $info[Globals::$conf->global->LDAP_FIELD_DESCRIPTION] = dol_string_nohtmltag($this->note_public, 2);
        }
        if ($this->socid > 0) {
            $soc = new Societe(Config::$dbEngine);
            $soc->fetch($this->socid);

            $info[Globals::$conf->global->LDAP_FIELD_COMPANY] = $soc->name;
            if ($soc->client == 1) {
                $info["businessCategory"] = "Customers";
            }
            if ($soc->client == 2) {
                $info["businessCategory"] = "Prospects";
            }
            if ($soc->fournisseur == 1) {
                $info["businessCategory"] = "Suppliers";
            }
        }

// When password is modified
        if (!empty($this->pass)) {
            if (!empty(Globals::$conf->global->LDAP_FIELD_PASSWORD)) {
                $info[Globals::$conf->global->LDAP_FIELD_PASSWORD] = $this->pass; // this->pass = mot de passe non crypte
            }
            if (!empty(Globals::$conf->global->LDAP_FIELD_PASSWORD_CRYPTED)) {
                $info[Globals::$conf->global->LDAP_FIELD_PASSWORD_CRYPTED] = dol_hash($this->pass, 4); // Create OpenLDAP MD5 password (TODO add type of encryption)
            }
        }
// Set LDAP password if possible
        elseif (Globals::$conf->global->LDAP_SERVER_PROTOCOLVERSION !== '3') { // If ldap key is modified and LDAPv3 we use ldap_rename function for avoid lose encrypt password
            if (!empty(Globals::$conf->global->DATABASE_PWD_ENCRYPTED)) {
// Just for the default MD5 !
                if (empty(Globals::$conf->global->MAIN_SECURITY_HASH_ALGO)) {
                    if ($this->pass_indatabase_crypted && !empty(Globals::$conf->global->LDAP_FIELD_PASSWORD_CRYPTED)) {
                        $info[Globals::$conf->global->LDAP_FIELD_PASSWORD_CRYPTED] = dol_hash($this->pass_indatabase_crypted, 5); // Create OpenLDAP MD5 password from Dolibarr MD5 password
                    }
                }
            }
// Use $this->pass_indatabase value if exists
            elseif (!empty($this->pass_indatabase)) {
                if (!empty(Globals::$conf->global->LDAP_FIELD_PASSWORD)) {
                    $info[Globals::$conf->global->LDAP_FIELD_PASSWORD] = $this->pass_indatabase; // $this->pass_indatabase = mot de passe non crypte
                }
                if (!empty(Globals::$conf->global->LDAP_FIELD_PASSWORD_CRYPTED)) {
                    $info[Globals::$conf->global->LDAP_FIELD_PASSWORD_CRYPTED] = dol_hash($this->pass_indatabase, 4); // md5 for OpenLdap TODO add type of encryption
                }
            }
        }

        if (Globals::$conf->global->LDAP_SERVER_TYPE == 'egroupware') {
            $info["objectclass"][4] = "phpgwContact"; // compatibilite egroupware

            $info['uidnumber'] = $this->id;

            $info['phpgwTz'] = 0;
            $info['phpgwMailType'] = 'INTERNET';
            $info['phpgwMailHomeType'] = 'INTERNET';

            $info["phpgwContactTypeId"] = 'n';
            $info["phpgwContactCatId"] = 0;
            $info["phpgwContactAccess"] = "public";

            if (dol_strlen($this->egroupware_id) == 0) {
                $this->egroupware_id = 1;
            }

            $info["phpgwContactOwner"] = $this->egroupware_id;

            if ($this->email) {
                $info["rfc822Mailbox"] = $this->email;
            }
            if ($this->phone_mobile) {
                $info["phpgwCellTelephoneNumber"] = $this->phone_mobile;
            }
        }

        return $info;
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
        // global Globals::$user, Globals::$langs;

        $now = dol_now();

// Initialise parametres
        $this->id = 0;
        $this->ref = 'SPECIMEN';
        $this->specimen = 1;

        $this->lastname = 'DOLIBARR';
        $this->firstname = 'SPECIMEN';
        $this->gender = 'man';
        $this->note = 'This is a note';
        $this->email = 'email@specimen.com';
        $this->skype = 'skypepseudo';
        $this->twitter = 'twitterpseudo';
        $this->facebook = 'facebookpseudo';
        $this->office_phone = '0999999999';
        $this->office_fax = '0999999998';
        $this->user_mobile = '0999999997';
        $this->admin = 0;
        $this->login = 'dolibspec';
        $this->pass = 'dolibspec';
//$this->pass_indatabase='dolibspec';									Set after a fetch
//$this->pass_indatabase_crypted='e80ca5a88c892b0aaaf7e154853bccab';	Set after a fetch
        $this->datec = $now;
        $this->datem = $now;

        $this->datelastlogin = $now;
        $this->datepreviouslogin = $now;
        $this->statut = 1;

//$this->societe_id = 1;	For external users
//$this->contact_id = 1;	For external users
        $this->entity = 1;
    }

    /**
     *  Load info of user object
     *
     *  @param  int		$id     Id of user to load
     *  @return	void
     */
    function info($id)
    {
        $sql = "SELECT u.rowid, u.login as ref, u.datec,";
        $sql .= " u.tms as date_modification, u.entity";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user as u";
        $sql .= " WHERE u.rowid = " . $id;

        $result = Config::$dbEngine->query($sql);
        if ($result) {
            if (Config::$dbEngine->num_rows($result)) {
                $obj = Config::$dbEngine->fetch_object($result);

                $this->id = $obj->rowid;

                $this->ref = (!$obj->ref) ? $obj->rowid : $obj->ref;
                $this->date_creation = Config::$dbEngine->jdate($obj->datec);
                $this->date_modification = Config::$dbEngine->jdate($obj->date_modification);
                $this->entity = $obj->entity;
            }

            Config::$dbEngine->free($result);
        } else {
            dol_print_error(Config::$dbEngine);
        }
    }

    /**
     *    Return number of mass Emailing received by this contacts with its email
     *
     *    @return       int     Number of EMailings
     */
    function getNbOfEMailings()
    {
        $sql = "SELECT count(mc.email) as nb";
        $sql .= " FROM " . MAIN_DB_PREFIX . "mailing_cibles as mc";
        $sql .= " WHERE mc.email = '" . Config::$dbEngine->escape($this->email) . "'";
        $sql .= " AND mc.statut NOT IN (-1,0)";      // -1 erreur, 0 non envoye, 1 envoye avec succes

        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            $obj = Config::$dbEngine->fetch_object($resql);
            $nb = $obj->nb;

            Config::$dbEngine->free($resql);
            return $nb;
        } else {
            $this->error = Config::$dbEngine->error();
            return -1;
        }
    }

    /**
     *  Return number of existing users
     *
     *  @param	string	$limitTo	Limit to '' or 'active'
     *  @param	string	$option		'superadmin' = return for entity 0 only
     *  @param	int		$admin		Filter on admin tag
     *  @return int  				Number of users
     */
    function getNbOfUsers($limitTo, $option = '', $admin = -1)
    {
        global $conf;

        $sql = "SELECT count(rowid) as nb";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user";
        if ($option == 'superadmin') {
            $sql .= " WHERE entity = 0";
            if ($admin >= 0) {
                $sql .= " AND admin = " . $admin;
            }
        } else {
            $sql .= " WHERE entity IN (" . getEntity('user', 0) . ")";
            if ($limitTo == 'active') {
                $sql .= " AND statut = 1";
            }
            if ($admin >= 0) {
                $sql .= " AND admin = " . $admin;
            }
        }

        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            $obj = Config::$dbEngine->fetch_object($resql);
            $nb = $obj->nb;

            Config::$dbEngine->free($resql);
            return $nb;
        } else {
            $this->error = Config::$dbEngine->lasterror();
            return -1;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Update user using data from the LDAP
     *
     *  @param	ldapuser	$ldapuser	Ladp User
     *
     *  @return int  				<0 if KO, >0 if OK
     */
    function update_ldap2dolibarr(&$ldapuser)
    {
// phpcs:enable
// TODO: Voir pourquoi le update met à jour avec toutes les valeurs vide (global Globals::$user écrase ?)
        //global Globals::$user, $conf;

        $this->firstname = $ldapuser->{Globals::$conf->global->LDAP_FIELD_FIRSTNAME};
        $this->lastname = $ldapuser->{Globals::$conf->global->LDAP_FIELD_NAME};
        $this->login = $ldapuser->{Globals::$conf->global->LDAP_FIELD_LOGIN};
        $this->pass = $ldapuser->{Globals::$conf->global->LDAP_FIELD_PASSWORD};
        $this->pass_indatabase_crypted = $ldapuser->{Globals::$conf->global->LDAP_FIELD_PASSWORD_CRYPTED};

        $this->office_phone = $ldapuser->{Globals::$conf->global->LDAP_FIELD_PHONE};
        $this->user_mobile = $ldapuser->{Globals::$conf->global->LDAP_FIELD_MOBILE};
        $this->office_fax = $ldapuser->{Globals::$conf->global->LDAP_FIELD_FAX};
        $this->email = $ldapuser->{Globals::$conf->global->LDAP_FIELD_MAIL};
        $this->skype = $ldapuser->{Globals::$conf->global->LDAP_FIELD_SKYPE};
        $this->twitter = $ldapuser->{Globals::$conf->global->LDAP_FIELD_TWITTER};
        $this->facebook = $ldapuser->{Globals::$conf->global->LDAP_FIELD_FACEBOOK};
        $this->ldap_sid = $ldapuser->{Globals::$conf->global->LDAP_FIELD_SID};

        $this->job = $ldapuser->{Globals::$conf->global->LDAP_FIELD_TITLE};
        $this->note = $ldapuser->{Globals::$conf->global->LDAP_FIELD_DESCRIPTION};

        $result = $this->update(Globals::$user);

        DolUtils::dol_syslog(get_class($this) . "::update_ldap2dolibarr result=" . $result, LOG_DEBUG);

        return $result;
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     * Return and array with all instanciated first level children users of current user
     *
     * @return	void
     * @see getAllChildIds
     */
    function get_children()
    {
// phpcs:enable
        $sql = "SELECT rowid FROM " . MAIN_DB_PREFIX . "user";
        $sql .= " WHERE fk_user = " . $this->id;

        DolUtils::dol_syslog(get_class($this) . "::get_children result=" . $result, LOG_DEBUG);
        $res = Config::$dbEngine->query($sql);
        if ($res) {
            $this->users = array();
            while ($rec = Config::$dbEngine->fetch_array($res)) {
                Globals::$user = new User(Config::$dbEngine);
                Globals::$user->fetch($rec['rowid']);
                $this->users[] = Globals::$user;
            }
            return $this->users;
        } else {
            dol_print_error(Config::$dbEngine);
            return -1;
        }
    }

    /**
     * 	Load this->parentof that is array(id_son=>id_parent, ...)
     *
     * 	@return		int		<0 if KO, >0 if OK
     */
    private function loadParentOf()
    {
        global $conf;

        $this->parentof = array();

// Load array[child]=parent
        $sql = "SELECT fk_user as id_parent, rowid as id_son";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user";
        $sql .= " WHERE fk_user <> 0";
        $sql .= " AND entity IN (" . getEntity('user') . ")";

        DolUtils::dol_syslog(get_class($this) . "::loadParentOf", LOG_DEBUG);
        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            while ($obj = Config::$dbEngine->fetch_object($resql)) {
                $this->parentof[$obj->id_son] = $obj->id_parent;
            }
            return 1;
        } else {
            dol_print_error(Config::$dbEngine);
            return -1;
        }
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     * 	Reconstruit l'arborescence hierarchique des users sous la forme d'un tableau
     * 	Set and return this->users that is an array sorted according to tree with arrays of:
     * 				id = id user
     * 				lastname
     * 				firstname
     * 				fullname = nom avec chemin complet du user
     * 				fullpath = chemin complet compose des id: "_grandparentid_parentid_id"
     *
     *  @param      int		$deleteafterid      Removed all users including the leaf $deleteafterid (and all its child) in user tree.
     *  @param		string	$filter				SQL filter on users
     * 	@return		array		      		  	Array of users $this->users. Note: $this->parentof is also set.
     */
    function get_full_tree($deleteafterid = 0, $filter = '')
    {
// phpcs:enable
        //global $conf, Globals::$user;
        //global Globals::$hookManager;
// Actions hooked (by external module)
        Globals::$hookManager->initHooks(array('userdao'));

        $this->users = array();

// Init this->parentof that is array(id_son=>id_parent, ...)
        $this->loadParentOf();

// Init $this->users array
        $sql = "SELECT DISTINCT u.rowid, u.firstname, u.lastname, u.fk_user, u.fk_soc, u.login, u.email, u.gender, u.admin, u.statut, u.photo, u.entity"; // Distinct reduce pb with old tables with duplicates
        $sql .= " FROM " . MAIN_DB_PREFIX . "user as u";
// Add fields from hooks
        $parameters = array();
        $reshook = Globals::$hookManager->executeHooks('printUserListWhere', $parameters);    // Note that $action and $object may have been modified by hook
        if ($reshook > 0) {
            $sql .= Globals::$hookManager->resPrint;
        } else {
            $sql .= " WHERE u.entity IN (" . getEntity('user') . ")";
        }
        if ($filter) {
            $sql .= " AND " . $filter;
        }

        DolUtils::dol_syslog(get_class($this) . "::get_full_tree get user list", LOG_DEBUG);
        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            $i = 0;
            while ($obj = Config::$dbEngine->fetch_object($resql)) {
                $this->users[$obj->rowid]['rowid'] = $obj->rowid;
                $this->users[$obj->rowid]['id'] = $obj->rowid;
                $this->users[$obj->rowid]['fk_user'] = $obj->fk_user;
                $this->users[$obj->rowid]['fk_soc'] = $obj->fk_soc;
                $this->users[$obj->rowid]['firstname'] = $obj->firstname;
                $this->users[$obj->rowid]['lastname'] = $obj->lastname;
                $this->users[$obj->rowid]['login'] = $obj->login;
                $this->users[$obj->rowid]['statut'] = $obj->statut;
                $this->users[$obj->rowid]['entity'] = $obj->entity;
                $this->users[$obj->rowid]['email'] = $obj->email;
                $this->users[$obj->rowid]['gender'] = $obj->gender;
                $this->users[$obj->rowid]['admin'] = $obj->admin;
                $this->users[$obj->rowid]['photo'] = $obj->photo;
                $i++;
            }
        } else {
            dol_print_error(Config::$dbEngine);
            return -1;
        }

// We add the fullpath property to each elements of first level (no parent exists)
        DolUtils::dol_syslog(get_class($this) . "::get_full_tree call to build_path_from_id_user", LOG_DEBUG);
        foreach ($this->users as $key => $val) {
            $result = $this->build_path_from_id_user($key, 0); // Process a branch from the root user key (this user has no parent)
            if ($result < 0) {
                $this->error = 'ErrorLoopInHierarchy';
                return -1;
            }
        }

// Exclude leaf including $deleteafterid from tree
        if ($deleteafterid) {
//print "Look to discard user ".$deleteafterid."\n";
            $keyfilter1 = '^' . $deleteafterid . '$';
            $keyfilter2 = '_' . $deleteafterid . '$';
            $keyfilter3 = '^' . $deleteafterid . '_';
            $keyfilter4 = '_' . $deleteafterid . '_';
            foreach ($this->users as $key => $val) {
                if (preg_match('/' . $keyfilter1 . '/', $val['fullpath']) || preg_match('/' . $keyfilter2 . '/', $val['fullpath']) || preg_match('/' . $keyfilter3 . '/', $val['fullpath']) || preg_match('/' . $keyfilter4 . '/', $val['fullpath'])) {
                    unset($this->users[$key]);
                }
            }
        }

        DolUtils::dol_syslog(get_class($this) . "::get_full_tree dol_sort_array", LOG_DEBUG);
        $this->users = dol_sort_array($this->users, 'fullname', 'asc', true, false);

//var_dump($this->users);

        return $this->users;
    }

    /**
     * 	Return list of all child users id in herarchy (all sublevels).
     *  Note: Calling this function also reset full list of users into $this->users.
     *
     *  @param      int      $addcurrentuser    1=Add also current user id to the list.
     * 	@return		array		      		  	Array of user id lower than user (all levels under user). This overwrite this->users.
     *  @see get_children
     */
    function getAllChildIds($addcurrentuser = 0)
    {
        $childids = array();

        if (isset($this->cache_childids[$this->id])) {
            $childids = $this->cache_childids[$this->id];
        } else {
// Init this->users
            $this->get_full_tree();

            $idtoscan = $this->id;

            DolUtils::dol_syslog("Build childid for id = " . $idtoscan);
            foreach ($this->users as $id => $val) {
//var_dump($val['fullpath']);
                if (preg_match('/_' . $idtoscan . '_/', $val['fullpath'])) {
                    $childids[$val['id']] = $val['id'];
                }
            }
        }
        $this->cache_childids[$this->id] = $childids;

        if ($addcurrentuser) {
            $childids[$this->id] = $this->id;
        }

        return $childids;
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     * 	For user id_user and its childs available in this->users, define property fullpath and fullname.
     *  Function called by get_full_tree().
     *
     * 	@param		int		$id_user		id_user entry to update
     * 	@param		int		$protection		Deep counter to avoid infinite loop (no more required, a protection is added with array useridfound)
     * 	@return		int                     < 0 if KO (infinit loop), >= 0 if OK
     */
    function build_path_from_id_user($id_user, $protection = 0)
    {
// phpcs:enable
        DolUtils::dol_syslog(get_class($this) . "::build_path_from_id_user id_user=" . $id_user . " protection=" . $protection, LOG_DEBUG);

        if (!empty($this->users[$id_user]['fullpath'])) {
// Already defined
            DolUtils::dol_syslog(get_class($this) . "::build_path_from_id_user fullpath and fullname already defined", LOG_WARNING);
            return 0;
        }

// Define fullpath and fullname
        $this->users[$id_user]['fullpath'] = '_' . $id_user;
        $this->users[$id_user]['fullname'] = $this->users[$id_user]['lastname'];
        $i = 0;
        $cursor_user = $id_user;

        Globals::$useridfound = array($id_user);
        while (!empty($this->parentof[$cursor_user])) {
            if (in_array($this->parentof[$cursor_user], Globals::$useridfound)) {
                DolUtils::dol_syslog("The hierarchy of user has a recursive loop", LOG_WARNING);
                return -1;     // Should not happen. Protection against looping hierarchy
            }
            Globals::$useridfound[] = $this->parentof[$cursor_user];
            $this->users[$id_user]['fullpath'] = '_' . $this->parentof[$cursor_user] . $this->users[$id_user]['fullpath'];
            $this->users[$id_user]['fullname'] = $this->users[$this->parentof[$cursor_user]]['lastname'] . ' >> ' . $this->users[$id_user]['fullname'];
            $i++;
            $cursor_user = $this->parentof[$cursor_user];
        }

// We count number of _ to have level
        $this->users[$id_user]['level'] = dol_strlen(preg_replace('/[^_]/i', '', $this->users[$id_user]['fullpath']));

        return 1;
    }

    /**
     * Function used to replace a thirdparty id with another one.
     *
     * @param DoliDB $db Database handler
     * @param int $origin_id Old thirdparty id
     * @param int $dest_id New thirdparty id
     * @return bool
     */
    public static function replaceThirdparty(DoliDB $db, $origin_id, $dest_id)
    {
        $tables = array(
            'user',
        );

        return CommonObject::commonReplaceThirdparty($db, $origin_id, $dest_id, $tables);
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *      Charge indicateurs this->nb pour le tableau de bord
     *
     *      @return     int         <0 if KO, >0 if OK
     */
    function load_state_board()
    {
// phpcs:enable
       // global $conf;

        $this->nb = array();

        $sql = "SELECT count(u.rowid) as nb";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user as u";
        $sql .= " WHERE u.statut > 0";
//$sql.= " AND employee != 0";
        $sql .= " AND u.entity IN (" . getEntity('user') . ")";

        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            while ($obj = Config::$dbEngine->fetch_object($resql)) {
                $this->nb["users"] = $obj->nb;
            }
            Config::$dbEngine->free($resql);
            return 1;
        } else {
            dol_print_error(Config::$dbEngine);
            $this->error = Config::$dbEngine->error();
            return -1;
        }
    }

    /**
     *  Create a document onto disk according to template module.
     *
     * 	@param	    string		$modele			Force model to use ('' to not force)
     * 	@param		Translate	$outputlangs	Object langs to use for output
     *  @param      int			$hidedetails    Hide details of lines
     *  @param      int			$hidedesc       Hide description
     *  @param      int			$hideref        Hide ref
     *  @param   null|array  $moreparams     Array to provide more information
     * 	@return     int         				0 if KO, 1 if OK
     */
    public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
    {
        //global $conf, Globals::$user, Globals::$langs;

        Globals::$langs->load("user");

// Positionne le modele sur le nom du modele a utiliser
        if (!dol_strlen($modele)) {
            if (!empty(Globals::$conf->global->USER_ADDON_PDF)) {
                $modele = Globals::$conf->global->USER_ADDON_PDF;
            } else {
                $modele = 'bluesky';
            }
        }

        $modelpath = "core/modules/user/doc/";

        return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
    }

// phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
    /**
     *  Return property of user from its id
     *
     *  @param	int		$rowid      id of contact
     *  @param  string	$mode       'email' or 'mobile'
     *  @return string  			Email of user with format: "Full name <email>"
     */
    function user_get_property($rowid, $mode)
    {
// phpcs:enable
        $this->user_property = '';

        if (empty($rowid)) {
            return '';
        }

        $sql = "SELECT rowid, email, user_mobile, civility, lastname, firstname";
        $sql .= " FROM " . MAIN_DB_PREFIX . "user";
        $sql .= " WHERE rowid = '" . $rowid . "'";

        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            $nump = Config::$dbEngine->num_rows($resql);

            if ($nump) {
                $obj = Config::$dbEngine->fetch_object($resql);

                if ($mode == 'email') {
                    $this->user_property = dolGetFirstLastname($obj->firstname, $obj->lastname) . " <" . $obj->email . ">";
                } else if ($mode == 'mobile') {
                    $this->user_property = $obj->user_mobile;
                }
            }
            return $this->user_property;
        } else {
            dol_print_error(Config::$dbEngine);
        }
    }

    /**
     * 	Load all objects into $this->users
     *
     *  @param	string		$sortorder		sort order
     *  @param	string		$sortfield		sort field
     *  @param	int			$limit			limit page
     *  @param	int			$offset			page
     *  @param	array		$filter			Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
     *  @param  string      $filtermode		Filter mode (AND or OR)
     *  @return int							<0 if KO, >0 if OK
     */
    function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = array(), $filtermode = 'AND')
    {
        global $conf;

        $sql = "SELECT t.rowid";
        $sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element . ' as t ';
        $sql .= " WHERE 1";

// Manage filter
        $sqlwhere = array();
        if (!empty($filter)) {
            foreach ($filter as $key => $value) {
                if ($key == 't.rowid') {
                    $sqlwhere[] = $key . '=' . $value;
                } elseif (strpos($key, 'date') !== false) {
                    $sqlwhere[] = $key . ' = \'' . Config::$dbEngine->idate($value) . '\'';
                } elseif ($key == 'customsql') {
                    $sqlwhere[] = $value;
                } else {
                    $sqlwhere[] = $key . ' LIKE \'%' . Config::$dbEngine->escape($value) . '%\'';
                }
            }
        }
        if (count($sqlwhere) > 0) {
            $sql .= ' AND (' . implode(' ' . $filtermode . ' ', $sqlwhere) . ')';
        }
        $sql .= Config::$dbEngine->order($sortfield, $sortorder);
        if ($limit) {
            $sql .= Config::$dbEngine->plimit($limit + 1, $offset);
        }

        DolUtils::dol_syslog(get_class($this) . "::" . __METHOD__, LOG_DEBUG);

        $resql = Config::$dbEngine->query($sql);
        if ($resql) {
            $this->users = array();
            $num = Config::$dbEngine->num_rows($resql);
            if ($num) {
                while ($obj = Config::$dbEngine->fetch_object($resql)) {
                    $line = new self(Config::$dbEngine);
                    $result = $line->fetch($obj->rowid);
                    if ($result > 0 && !empty($line->id)) {
                        $this->users[$obj->rowid] = clone $line;
                    }
                }
                Config::$dbEngine->free($resql);
            }
            return $num;
        } else {
            $this->errors[] = Config::$dbEngine->lasterror();
            return -1;
        }
    }
}
