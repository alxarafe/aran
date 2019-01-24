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

class Conf
{

    /** \public */
    //! To store properties found in conf file
    var $file;

    /**
     * @var DoliDB Database handler.
     */
    public $db;
    //! To store properties found into database
    public $global;
    //! To store browser info
    var $browser;
    //! To store if javascript/ajax is enabked
    public $use_javascript_ajax;
    //! Used to store current currency (ISO code like 'USD', 'EUR', ...)
    public $currency;
    //! Used to store current css (from theme)
    public $theme;        // Contains current theme ("eldy", "auguria", ...)
    public $css;          // Contains full path of css page ("/theme/eldy/style.css.php", ...)
    //! Used to store current menu handler
    public $standard_menu;
    public $modules = array(); // List of activated modules
    public $modules_parts = array('css' => array(), 'js' => array(), 'tabs' => array(), 'triggers' => array(), 'login' => array(), 'substitutions' => array(), 'menus' => array(), 'theme' => array(), 'sms' => array(), 'tpl' => array(), 'barcode' => array(), 'models' => array(), 'societe' => array(), 'hooks' => array(), 'dir' => array(), 'syslog' => array());
    var $logbuffer = array();

    /**
     * @var LogHandlerInterface[]
     */
    var $loghandlers = array();
    //! To store properties of multi-company
    public $multicompany;
    //! Used to store running instance for multi-company (default 1)
    public $entity = 1;
    //! Used to store list of entities to use for each element
    public $entities = array();
    public $dol_hide_topmenu;   // Set if we force param dol_hide_topmenu into login url
    public $dol_hide_leftmenu;   // Set if we force param dol_hide_leftmenu into login url
    public $dol_optimize_smallscreen; // Set if we force param dol_optimize_smallscreen into login url or if browser is smartphone
    public $dol_no_mouse_hover;   // Set if we force param dol_no_mouse_hover into login url or if browser is smartphone
    public $dol_use_jmobile;   // Set if we force param dol_use_jmobile into login url
    public $user;

    /**
     * Constructor
     */
    function __construct()
    {
        // Properly declare multi-modules objects.
        $this->file = new \stdClass();
        $this->db = new \stdClass();
        $this->global = new \stdClass();
        $this->mycompany = new \stdClass();
        $this->admin = new \stdClass();
        $this->user = new \stdClass();
        $this->syslog = new \stdClass();
        $this->browser = new \stdClass();
        $this->medias = new \stdClass();
        $this->multicompany = new \stdClass();

        //! Charset for HTML output and for storing data in memory
        $this->file->character_set_client = 'UTF-8';   // UTF-8, ISO-8859-1
        // First level object
        // TODO Remove this part.
        $this->expedition_bon = new \stdClass();
        $this->livraison_bon = new \stdClass();
        $this->fournisseur = new \stdClass();
        $this->product = new \stdClass();
        $this->service = new \stdClass();
        $this->contrat = new \stdClass();
        $this->actions = new \stdClass();
        $this->commande = new \stdClass();
        $this->propal = new \stdClass();
        $this->facture = new \stdClass();
        $this->contrat = new \stdClass();
        $this->usergroup = new \stdClass();
        $this->adherent = new \stdClass();
        $this->bank = new \stdClass();
        $this->notification = new \stdClass();
        $this->mailing = new \stdClass();
        $this->expensereport = new \stdClass();
        $this->productbatch = new \stdClass();
    }

    /**
     * 	Load setup values into conf object (read llx_const)
     *  Note that this->db->xxx, this->file->xxx and this->multicompany have been already loaded when setValues is called.
     *
     * 	@param      DoliDB		$db		Database handler
     * 	@return     int					< 0 if KO, >= 0 if OK
     */
    function setValues($dba)
    {
        // global $conf;

        var_dump($this);
        die('en setValues');

        dol_syslog(get_class($this) . "::setValues");

        //Define all global constants into Conf::$global->key=value
        $sql = "SELECT " . $db->decrypt('name') . " as name,";
        $sql .= " " . $db->decrypt('value') . " as value, entity";
        $sql .= " FROM " . MAIN_DB_PREFIX . "const";
        $sql .= " WHERE entity IN (0," . $this->entity . ")";
        $sql .= " ORDER BY entity"; // This is to have entity 0 first, then entity 1 that overwrite.

        $resql = $db->query($sql);
        if ($resql) {
            $i = 0;
            $numr = $db->num_rows($resql);
            while ($i < $numr) {
                $objp = $db->fetch_object($resql);
                $key = $objp->name;
                $value = $objp->value;
                if ($key) {
                    //if (! defined("$key")) define("$key", $value);	// In some cases, the constant might be already forced (Example: SYSLOG_HANDLERS during install)
                    Conf::$global->$key = $value;

                    if ($value && preg_match('/^MAIN_MODULE_/', $key)) {
                        // If this is constant for a new tab page activated by a module. It initializes modules_parts['tabs'].
                        if (preg_match('/^MAIN_MODULE_([0-9A-Z_]+)_TABS_/i', $key)) {
                            $partname = 'tabs';
                            $params = explode(':', $value, 2);
                            if (!isset($this->modules_parts[$partname]) || !is_array($this->modules_parts[$partname])) {
                                $this->modules_parts[$partname] = array();
                            }
                            $this->modules_parts[$partname][$params[0]][] = $value; // $value may be a string or an array
                        }
                        // If this is constant for all generic part activated by a module. It initializes
                        // modules_parts['login'], modules_parts['menus'], modules_parts['substitutions'], modules_parts['triggers'], modules_parts['tpl'],
                        // modules_parts['models'], modules_parts['theme']
                        // modules_parts['sms'],
                        // modules_parts['css'], ...
                        elseif (preg_match('/^MAIN_MODULE_([0-9A-Z_]+)_([A-Z]+)$/i', $key, $reg)) {
                            $modulename = strtolower($reg[1]);
                            $partname = strtolower($reg[2]);
                            if (!isset($this->modules_parts[$partname]) || !is_array($this->modules_parts[$partname])) {
                                $this->modules_parts[$partname] = array();
                            }
                            $arrValue = json_decode($value, true);
                            if (is_array($arrValue) && !empty($arrValue))
                                $value = $arrValue;
                            else if (in_array($partname, array('login', 'menus', 'substitutions', 'triggers', 'tpl')))
                                $value = '/' . $modulename . '/core/' . $partname . '/';
                            else if (in_array($partname, array('models', 'theme')))
                                $value = '/' . $modulename . '/';
                            else if (in_array($partname, array('sms')))
                                $value = '/' . $modulename . '/';
                            else if ($value == 1)
                                $value = '/' . $modulename . '/core/modules/' . $partname . '/'; // ex: partname = societe
                            $this->modules_parts[$partname] = array_merge($this->modules_parts[$partname], array($modulename => $value)); // $value may be a string or an array
                        }
                        // If this is a module constant (must be at end)
                        elseif (preg_match('/^MAIN_MODULE_([0-9A-Z_]+)$/i', $key, $reg)) {
                            $modulename = strtolower($reg[1]);
                            if ($modulename == 'propale')
                                $modulename = 'propal';
                            if ($modulename == 'supplierproposal')
                                $modulename = 'supplier_proposal';
                            if (!isset($this->$modulename) || !is_object($this->$modulename))
                                $this->$modulename = new \stdClass();
                            $this->$modulename->enabled = true;
                            $this->modules[] = $modulename;              // Add this module in list of enabled modules
                        }
                    }
                }
                $i++;
            }

            $db->free($resql);
        }

        // Include other local consts.php files and fetch their values to the corresponding database constants.
        if (!empty(Globals::$conf->$global->LOCAL_CONSTS_FILES)) {
            $filesList = explode(":", Conf::$global->LOCAL_CONSTS_FILES);
            foreach ($filesList as $file) {
                $file = dol_sanitizeFileName($file);
                include_once DOL_DOCUMENT_ROOT . "/" . $file . "/" . $file . "_consts.php";
                foreach ($file2bddconsts as $key => $value) {
                    Conf::$global->$key = $value;
                }
            }
        }

        //var_dump($this->modules);
        //var_dump($this->modules_parts['theme']);
        // If you can't set timezone of your PHP, set this constant. Better is to set it to UTC.
        // In future, this constant will be forced to 'UTC' so PHP server timezone will not have effect anymore.
        //Conf::$global->MAIN_SERVER_TZ='Europe/Paris';
        if (!empty(Globals::$conf->$global->MAIN_SERVER_TZ) && Conf::$global->MAIN_SERVER_TZ != 'auto') {
            try {
                date_default_timezone_set(Globals::$conf->$global->MAIN_SERVER_TZ);
            } catch (Exception $e) {
                dol_syslog("Error: Bad value for parameter MAIN_SERVER_TZ=" . Conf::$global->MAIN_SERVER_TZ, LOG_ERR);
            }
        }

        // Object $mc
        if (!defined('NOREQUIREMC') && !empty($this->multicompany->enabled)) {
            // global $mc;
            $ret = @dol_include_once('/multicompany/class/actions_multicompany.class.php');
            if ($ret) {
                Globals::$mc = new ActionsMulticompany($db);
            }
        }

        // Clean some variables
        if (empty(Globals::$conf->$global->MAIN_MENU_STANDARD))
            Conf::$global->MAIN_MENU_STANDARD = "eldy_menu.php";
        if (empty(Globals::$conf->$global->MAIN_MENUFRONT_STANDARD))
            Conf::$global->MAIN_MENUFRONT_STANDARD = "eldy_menu.php";
        if (empty(Globals::$conf->$global->MAIN_MENU_SMARTPHONE))
            Conf::$global->MAIN_MENU_SMARTPHONE = "eldy_menu.php"; // Use eldy by default because smartphone does not work on all phones
        if (empty(Globals::$conf->$global->MAIN_MENUFRONT_SMARTPHONE))
            Conf::$global->MAIN_MENUFRONT_SMARTPHONE = "eldy_menu.php"; // Use eldy by default because smartphone does not work on all phones






// Clean var use vat for company
        if (!isset(Globals::$conf->$global->FACTURE_TVAOPTION))
            Conf::$global->FACTURE_TVAOPTION = 1;
        else if (!empty(Globals::$conf->$global->FACTURE_TVAOPTION) && !is_numeric(Globals::$conf->$global->FACTURE_TVAOPTION)) {
            // Old value of option, we clean to use new value (0 or 1)
            if (Globals::$conf->$global->FACTURE_TVAOPTION != "franchise")
                Conf::$global->FACTURE_TVAOPTION = 1;
            else
                Conf::$global->FACTURE_TVAOPTION = 0;
        }

        // Variable globales LDAP
        if (empty(Globals::$conf->$global->LDAP_FIELD_FULLNAME))
            Conf::$global->LDAP_FIELD_FULLNAME = '';
        if (!isset(Globals::$conf->$global->LDAP_KEY_USERS))
            Conf::$global->LDAP_KEY_USERS = Conf::$global->LDAP_FIELD_FULLNAME;
        if (!isset(Globals::$conf->$global->LDAP_KEY_GROUPS))
            Conf::$global->LDAP_KEY_GROUPS = Conf::$global->LDAP_FIELD_FULLNAME;
        if (!isset(Globals::$conf->$global->LDAP_KEY_CONTACTS))
            Conf::$global->LDAP_KEY_CONTACTS = Conf::$global->LDAP_FIELD_FULLNAME;
        if (!isset(Globals::$conf->$global->LDAP_KEY_MEMBERS))
            Conf::$global->LDAP_KEY_MEMBERS = Conf::$global->LDAP_FIELD_FULLNAME;
        if (!isset(Globals::$conf->$global->LDAP_KEY_MEMBERS_TYPES))
            Conf::$global->LDAP_KEY_MEMBERS_TYPES = Conf::$global->LDAP_FIELD_FULLNAME;

        // Load translation object with current language
        if (empty(Globals::$conf->$global->MAIN_LANG_DEFAULT))
            Conf::$global->MAIN_LANG_DEFAULT = "en_US";

        $rootfordata = DOL_DATA_ROOT;
        $rootforuser = DOL_DATA_ROOT;
        // If multicompany module is enabled, we redefine the root of data
        if (!empty($this->multicompany->enabled) && !empty($this->entity) && $this->entity > 1) {
            $rootfordata .= '/' . $this->entity;
        }

        // Define default dir_output and dir_temp for directories of modules
        foreach ($this->modules as $module) {
            //var_dump($module);
            // For multicompany sharings
            $this->$module->multidir_output = array($this->entity => $rootfordata . "/" . $module);
            $this->$module->multidir_temp = array($this->entity => $rootfordata . "/" . $module . "/temp");
            // For backward compatibility
            $this->$module->dir_output = $rootfordata . "/" . $module;
            $this->$module->dir_temp = $rootfordata . "/" . $module . "/temp";
        }

        // External modules storage
        if (!empty($this->modules_parts['dir'])) {
            foreach ($this->modules_parts['dir'] as $module => $dirs) {
                if (!empty($this->$module->enabled)) {
                    foreach ($dirs as $type => $name) {
                        $subdir = ($type == 'temp' ? '/temp' : '');
                        // For multicompany sharings
                        $varname = 'multidir_' . $type;
                        $this->$module->$varname = array($this->entity => $rootfordata . "/" . $name . $subdir);
                        // For backward compatibility
                        $varname = 'dir_' . $type;
                        $this->$module->$varname = $rootfordata . "/" . $name . $subdir;
                    }
                }
            }
        }

        // For mycompany storage
        $this->mycompany->dir_output = $rootfordata . "/mycompany";
        $this->mycompany->dir_temp = $rootfordata . "/mycompany/temp";

        // For admin storage
        $this->admin->dir_output = $rootfordata . '/admin';
        $this->admin->dir_temp = $rootfordata . '/admin/temp';

        // For user storage
        $this->user->multidir_output = array($this->entity => $rootfordata . "/users");
        $this->user->multidir_temp = array($this->entity => $rootfordata . "/users/temp");
        // For backward compatibility
        $this->user->dir_output = $rootforuser . "/users";
        $this->user->dir_temp = $rootforuser . "/users/temp";

        // For usergroup storage
        $this->usergroup->dir_output = $rootforuser . "/usergroups";
        $this->usergroup->dir_temp = $rootforuser . "/usergroups/temp";

        // For proposal storage
        $this->propal->multidir_output = array($this->entity => $rootfordata . "/propale");
        $this->propal->multidir_temp = array($this->entity => $rootfordata . "/propale/temp");
        // For backward compatibility
        $this->propal->dir_output = $rootfordata . "/propale";
        $this->propal->dir_temp = $rootfordata . "/propale/temp";

        // For medias storage
        $this->medias->multidir_output = array($this->entity => $rootfordata . "/medias");
        $this->medias->multidir_temp = array($this->entity => $rootfordata . "/medias/temp");

        // Exception: Some dir are not the name of module. So we keep exception here for backward compatibility.
        // Sous module bons d'expedition
        $this->expedition_bon->enabled = (!empty(Globals::$conf->$global->MAIN_SUBMODULE_EXPEDITION) ? Conf::$global->MAIN_SUBMODULE_EXPEDITION : 0);
        // Sous module bons de livraison
        $this->livraison_bon->enabled = (!empty(Globals::$conf->$global->MAIN_SUBMODULE_LIVRAISON) ? Conf::$global->MAIN_SUBMODULE_LIVRAISON : 0);

        // Module fournisseur
        if (!empty($this->fournisseur)) {
            $this->fournisseur->commande = new \stdClass();
            $this->fournisseur->commande->multidir_output = array($this->entity => $rootfordata . "/fournisseur/commande");
            $this->fournisseur->commande->multidir_temp = array($this->entity => $rootfordata . "/fournisseur/commande/temp");
            $this->fournisseur->commande->dir_output = $rootfordata . "/fournisseur/commande";  // For backward compatibility
            $this->fournisseur->commande->dir_temp = $rootfordata . "/fournisseur/commande/temp"; // For backward compatibility
            $this->fournisseur->facture = new \stdClass();
            $this->fournisseur->facture->multidir_output = array($this->entity => $rootfordata . "/fournisseur/facture");
            $this->fournisseur->facture->multidir_temp = array($this->entity => $rootfordata . "/fournisseur/facture/temp");
            $this->fournisseur->facture->dir_output = $rootfordata . "/fournisseur/facture";  // For backward compatibility
            $this->fournisseur->facture->dir_temp = $rootfordata . "/fournisseur/facture/temp"; // For backward compatibility
            $this->supplierproposal = new \stdClass();
            $this->supplierproposal->multidir_output = array($this->entity => $rootfordata . "/supplier_proposal");
            $this->supplierproposal->multidir_temp = array($this->entity => $rootfordata . "/supplier_proposal/temp");
            $this->supplierproposal->dir_output = $rootfordata . "/supplier_proposal";    // For backward compatibility
            $this->supplierproposal->dir_temp = $rootfordata . "/supplier_proposal/temp";   // For backward compatibility
            $this->fournisseur->payment = new \stdClass();
            $this->fournisseur->payment->multidir_output = array($this->entity => $rootfordata . "/fournisseur/payment");
            $this->fournisseur->payment->multidir_temp = array($this->entity => $rootfordata . "/fournisseur/payment/temp");
            $this->fournisseur->payment->dir_output = $rootfordata . "/fournisseur/payment";  // For backward compatibility
            $this->fournisseur->payment->dir_temp = $rootfordata . "/fournisseur/payment/temp"; // For backward compatibility
            // To prepare split of module fournisseur into fournisseur + supplier_order + supplier_invoice
            if (!empty($this->fournisseur->enabled) && empty(Globals::$conf->$global->MAIN_USE_NEW_SUPPLIERMOD)) {  // By default, if module supplier is on, we set new properties
                $this->supplier_order = new \stdClass();
                $this->supplier_order->enabled = 1;
                $this->supplier_order->multidir_output = array($this->entity => $rootfordata . "/fournisseur/commande");
                $this->supplier_order->multidir_temp = array($this->entity => $rootfordata . "/fournisseur/commande/temp");
                $this->supplier_order->dir_output = $rootfordata . "/fournisseur/commande";   // For backward compatibility
                $this->supplier_order->dir_temp = $rootfordata . "/fournisseur/commande/temp";  // For backward compatibility
                $this->supplier_invoice = new \stdClass();
                $this->supplier_invoice->enabled = 1;
                $this->supplier_invoice->multidir_output = array($this->entity => $rootfordata . "/fournisseur/facture");
                $this->supplier_invoice->multidir_temp = array($this->entity => $rootfordata . "/fournisseur/facture/temp");
                $this->supplier_invoice->dir_output = $rootfordata . "/fournisseur/facture";  // For backward compatibility
                $this->supplier_invoice->dir_temp = $rootfordata . "/fournisseur/facture/temp";  // For backward compatibility
                $this->supplierproposal = new \stdClass();
                $this->supplierproposal->multidir_output = array($this->entity => $rootfordata . "/supplier_proposal");
                $this->supplierproposal->multidir_temp = array($this->entity => $rootfordata . "/supplier_proposal/temp");
                $this->supplierproposal->dir_output = $rootfordata . "/supplier_proposal";   // For backward compatibility
                $this->supplierproposal->dir_temp = $rootfordata . "/supplier_proposal/temp";  // For backward compatibility
            }
        }

        // Module product/service
        $this->product->multidir_output = array($this->entity => $rootfordata . "/produit");
        $this->product->multidir_temp = array($this->entity => $rootfordata . "/produit/temp");
        $this->service->multidir_output = array($this->entity => $rootfordata . "/produit");
        $this->service->multidir_temp = array($this->entity => $rootfordata . "/produit/temp");
        // For backward compatibility
        $this->product->dir_output = $rootfordata . "/produit";
        $this->product->dir_temp = $rootfordata . "/produit/temp";
        $this->service->dir_output = $rootfordata . "/produit";
        $this->service->dir_temp = $rootfordata . "/produit/temp";

        // Module productbatch
        $this->productbatch->multidir_output = array($this->entity => $rootfordata . "/produitlot");
        $this->productbatch->multidir_temp = array($this->entity => $rootfordata . "/produitlot/temp");

        // Module contrat
        $this->contrat->multidir_output = array($this->entity => $rootfordata . "/contract");
        $this->contrat->multidir_temp = array($this->entity => $rootfordata . "/contract/temp");
        // For backward compatibility
        $this->contrat->dir_output = $rootfordata . "/contract";
        $this->contrat->dir_temp = $rootfordata . "/contract/temp";

        // Module bank
        $this->bank->dir_output = $rootfordata . "/bank";
        $this->bank->dir_temp = $rootfordata . "/bank/temp";


        // Set some default values
        //Conf::$global->MAIN_LIST_FILTER_ON_DAY=1;		// On filter that show date, we must show input field for day before or after month
        Conf::$global->MAIN_ACTIVATE_HTML5 = 1;
        Conf::$global->MAIN_MAIL_USE_MULTI_PART = 1;

        // societe
        if (empty(Globals::$conf->$global->SOCIETE_CODECLIENT_ADDON))
            Conf::$global->SOCIETE_CODECLIENT_ADDON = "mod_codeclient_leopard";
        if (empty(Globals::$conf->$global->SOCIETE_CODECOMPTA_ADDON))
            Conf::$global->SOCIETE_CODECOMPTA_ADDON = "mod_codecompta_panicum";

        if (empty(Globals::$conf->$global->CHEQUERECEIPTS_ADDON))
            Conf::$global->CHEQUERECEIPTS_ADDON = 'mod_chequereceipt_mint';
        if (empty(Globals::$conf->global->TICKETSUP_ADDON))
            Conf::$global->TICKETSUP_ADDON = 'mod_ticket_simple';

        // Security
        if (empty(Globals::$conf->$global->USER_PASSWORD_GENERATED))
            Conf::$global->USER_PASSWORD_GENERATED = 'standard'; // Default password generator
        if (empty(Globals::$conf->$global->MAIN_UMASK))
            Conf::$global->MAIN_UMASK = '0664';         // Default mask







// conf->use_javascript_ajax
        $this->use_javascript_ajax = 1;
        if (isset(Globals::$conf->$global->MAIN_DISABLE_JAVASCRIPT))
            $this->use_javascript_ajax = !Conf::$global->MAIN_DISABLE_JAVASCRIPT;
        // If no javascript_ajax, Ajax features are disabled.
        if (empty($this->use_javascript_ajax)) {
            unset(Globals::$conf->$global->PRODUIT_USE_SEARCH_TO_SELECT);
            unset(Globals::$conf->$global->COMPANY_USE_SEARCH_TO_SELECT);
            unset(Globals::$conf->$global->CONTACT_USE_SEARCH_TO_SELECT);
            unset(Globals::$conf->$global->PROJECT_USE_SEARCH_TO_SELECT);
        }

        if (!empty($this->productbatch->enabled)) {
            Conf::$global->STOCK_CALCULATE_ON_BILL = 0;
            Conf::$global->STOCK_CALCULATE_ON_VALIDATE_ORDER = 0;
            Conf::$global->STOCK_CALCULATE_ON_SHIPMENT = 1;
            Conf::$global->STOCK_CALCULATE_ON_SHIPMENT_CLOSE = 0;
            Conf::$global->STOCK_CALCULATE_ON_SUPPLIER_BILL = 0;
            Conf::$global->STOCK_CALCULATE_ON_SUPPLIER_VALIDATE_ORDER = 0;
            if (empty($this->reception->enabled))
                Conf::$global->STOCK_CALCULATE_ON_SUPPLIER_DISPATCH_ORDER = 1;
            else {
                Conf::$global->STOCK_CALCULATE_ON_RECEPTION = 1;
                Conf::$global->STOCK_CALCULATE_ON_RECEPTION_CLOSE = 0;
            }
        }

        // conf->currency
        if (empty(Globals::$conf->$global->MAIN_MONNAIE))
            Conf::$global->MAIN_MONNAIE = 'EUR';
        $this->currency = Conf::$global->MAIN_MONNAIE;

        if (empty(Globals::$conf->$global->MAIN_BROWSER_NOTIFICATION_FREQUENCY))
            Conf::$global->MAIN_BROWSER_NOTIFICATION_FREQUENCY = 30;   // Less than 1 minutes to be sure







// conf->global->ACCOUNTING_MODE = Option des modules Comptabilites (simple ou expert). Defini le mode de calcul des etats comptables (CA,...)
        if (empty(Globals::$conf->$global->ACCOUNTING_MODE))
            Conf::$global->ACCOUNTING_MODE = 'RECETTES-DEPENSES';  // By default. Can be 'RECETTES-DEPENSES' ou 'CREANCES-DETTES'







// By default, suppliers objects can be linked to all projects
        Conf::$global->PROJECT_CAN_ALWAYS_LINK_TO_ALL_SUPPLIERS = 1;

        // MAIN_HTML_TITLE
        if (!isset(Globals::$conf->$global->MAIN_HTML_TITLE))
            Conf::$global->MAIN_HTML_TITLE = 'noapp,thirdpartynameonly,contactnameonly,projectnameonly';

        // conf->liste_limit = constante de taille maximale des listes
        if (empty(Globals::$conf->$global->MAIN_SIZE_LISTE_LIMIT))
            Conf::$global->MAIN_SIZE_LISTE_LIMIT = 25;
        $this->liste_limit = Conf::$global->MAIN_SIZE_LISTE_LIMIT;

        // conf->product->limit_size = constante de taille maximale des select de produit
        if (!isset(Globals::$conf->$global->PRODUIT_LIMIT_SIZE))
            Conf::$global->PRODUIT_LIMIT_SIZE = 1000;
        $this->product->limit_size = Conf::$global->PRODUIT_LIMIT_SIZE;

        // conf->theme et $this->css
        if (empty(Globals::$conf->$global->MAIN_THEME))
            Conf::$global->MAIN_THEME = "eldy";
        if (!empty(Globals::$conf->$global->MAIN_FORCETHEME))
            Conf::$global->MAIN_THEME = Conf::$global->MAIN_FORCETHEME;
        $this->theme = Conf::$global->MAIN_THEME;
        //$this->css  = "/theme/".$this->theme."/style.css.php";
        $this->css = '?controller=theme/' . $this->theme . '&method=style.css';

        // conf->email_from = email pour envoi par dolibarr des mails automatiques
        $this->email_from = "robot@example.com";
        if (!empty(Globals::$conf->$global->MAIN_MAIL_EMAIL_FROM))
            $this->email_from = Conf::$global->MAIN_MAIL_EMAIL_FROM;

        // conf->notification->email_from = email pour envoi par Dolibarr des notifications
        $this->notification->email_from = $this->email_from;
        if (!empty(Globals::$conf->$global->NOTIFICATION_EMAIL_FROM))
            $this->notification->email_from = Conf::$global->NOTIFICATION_EMAIL_FROM;

        // conf->mailing->email_from = email pour envoi par Dolibarr des mailings
        $this->mailing->email_from = $this->email_from;
        if (!empty(Globals::$conf->$global->MAILING_EMAIL_FROM))
            $this->mailing->email_from = Conf::$global->MAILING_EMAIL_FROM;
        if (!isset(Globals::$conf->$global->MAIN_EMAIL_ADD_TRACK_ID))
            Conf::$global->MAIN_EMAIL_ADD_TRACK_ID = 1;

        // Format for date (used by default when not found or not searched in lang)
        $this->format_date_short = "%d/%m/%Y";            // Format of day with PHP/C tags (strftime functions)
        $this->format_date_short_java = "dd/MM/yyyy";     // Format of day with Java tags
        $this->format_hour_short = "%H:%M";
        $this->format_hour_short_duration = "%H:%M";
        $this->format_date_text_short = "%d %b %Y";
        $this->format_date_text = "%d %B %Y";
        $this->format_date_hour_short = "%d/%m/%Y %H:%M";
        $this->format_date_hour_sec_short = "%d/%m/%Y %H:%M:%S";
        $this->format_date_hour_text_short = "%d %b %Y %H:%M";
        $this->format_date_hour_text = "%d %B %Y %H:%M";

        // Duration of workday
        if (!isset(Globals::$conf->$global->MAIN_DURATION_OF_WORKDAY))
            Conf::$global->MAIN_DURATION_OF_WORKDAY = 86400;

        // Limites decimales si non definie (peuvent etre egale a 0)
        if (!isset(Globals::$conf->$global->MAIN_MAX_DECIMALS_UNIT))
            Conf::$global->MAIN_MAX_DECIMALS_UNIT = 5;
        if (!isset(Globals::$conf->$global->MAIN_MAX_DECIMALS_TOT))
            Conf::$global->MAIN_MAX_DECIMALS_TOT = 2;
        if (!isset(Globals::$conf->$global->MAIN_MAX_DECIMALS_SHOWN))
            Conf::$global->MAIN_MAX_DECIMALS_SHOWN = 8;

        // Default pdf option
        if (!isset(Globals::$conf->$global->MAIN_PDF_DASH_BETWEEN_LINES))
            Conf::$global->MAIN_PDF_DASH_BETWEEN_LINES = 1;    // use dash between lines
        if (!isset(Globals::$conf->$global->PDF_ALLOW_HTML_FOR_FREE_TEXT))
            Conf::$global->PDF_ALLOW_HTML_FOR_FREE_TEXT = 1;  // allow html content into free footer text







// Set default value to MAIN_SHOW_LOGO
        if (!isset(Globals::$conf->$global->MAIN_SHOW_LOGO))
            Conf::$global->MAIN_SHOW_LOGO = 1;

        // Default max file size for upload
        $this->maxfilesize = (empty(Globals::$conf->$global->MAIN_UPLOAD_DOC) ? 0 : (int) Conf::$global->MAIN_UPLOAD_DOC * 1024);

        // By default, we propagate contacts
        if (!isset(Globals::$conf->$global->MAIN_PROPAGATE_CONTACTS_FROM_ORIGIN))
            Conf::$global->MAIN_PROPAGATE_CONTACTS_FROM_ORIGIN = '*';  // Can be also '*' or '^(BILLING|SHIPPING|CUSTOMER|.*)$' (regex not yet implemented)







// By default, we do not use the zip town table but the table of third parties
        if (!isset(Globals::$conf->$global->MAIN_USE_ZIPTOWN_DICTIONNARY))
            Conf::$global->MAIN_USE_ZIPTOWN_DICTIONNARY = 0;

        // By default, we open card if one found
        if (!isset(Globals::$conf->$global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE))
            Conf::$global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE = 1;

        // By default, we show state code in combo list
        if (!isset(Globals::$conf->$global->MAIN_SHOW_STATE_CODE))
            Conf::$global->MAIN_SHOW_STATE_CODE = 1;

        // Define list of limited modules (value must be key found for "name" property of module, so for example 'supplierproposal' for Module "Supplier Proposal"
        if (!isset(Globals::$conf->$global->MAIN_MODULES_FOR_EXTERNAL))
            Conf::$global->MAIN_MODULES_FOR_EXTERNAL = 'user,societe,propal,commande,facture,categorie,supplierproposal,fournisseur,contact,projet,contrat,ficheinter,expedition,agenda,resource,adherent,blockedlog'; // '' means 'all'. Note that contact is added here as it should be a module later.
        if (!empty($this->modules_parts['moduleforexternal'])) {  // Module part to include an external module into the MAIN_MODULES_FOR_EXTERNAL list
            foreach ($this->modules_parts['moduleforexternal'] as $key => $value)
                Conf::$global->MAIN_MODULES_FOR_EXTERNAL .= "," . $key;
        }

        // Enable select2
        if (empty(Globals::$conf->$global->MAIN_USE_JQUERY_MULTISELECT) || Conf::$global->MAIN_USE_JQUERY_MULTISELECT == '1')
            Conf::$global->MAIN_USE_JQUERY_MULTISELECT = 'select2';

        // Timeouts
        if (empty(Globals::$conf->$global->MAIN_USE_CONNECT_TIMEOUT))
            Conf::$global->MAIN_USE_CONNECT_TIMEOUT = 10;
        if (empty(Globals::$conf->$global->MAIN_USE_RESPONSE_TIMEOUT))
            Conf::$global->MAIN_USE_RESPONSE_TIMEOUT = 30;

        // Set default variable to calculate VAT as if option tax_mode was 0 (standard)
        if (empty(Globals::$conf->$global->TAX_MODE_SELL_PRODUCT))
            Conf::$global->TAX_MODE_SELL_PRODUCT = 'invoice';
        if (empty(Globals::$conf->$global->TAX_MODE_BUY_PRODUCT))
            Conf::$global->TAX_MODE_BUY_PRODUCT = 'invoice';
        if (empty(Globals::$conf->$global->TAX_MODE_SELL_SERVICE))
            Conf::$global->TAX_MODE_SELL_SERVICE = 'payment';
        if (empty(Globals::$conf->$global->TAX_MODE_BUY_SERVICE))
            Conf::$global->TAX_MODE_BUY_SERVICE = 'payment';

        // Delay before warnings
        // Avoid strict errors. TODO: Replace xxx->warning_delay with a property ->warning_delay_xxx
        if (isset($this->agenda)) {
            $this->adherent->subscription = new \stdClass();
            $this->adherent->subscription->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_MEMBERS) ? Conf::$global->MAIN_DELAY_MEMBERS : 0) * 24 * 60 * 60;
        }
        if (isset($this->agenda))
            $this->agenda->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_ACTIONS_TODO) ? Conf::$global->MAIN_DELAY_ACTIONS_TODO : 7) * 24 * 60 * 60;
        if (isset($this->projet)) {
            $this->projet->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_PROJECT_TO_CLOSE) ? Conf::$global->MAIN_DELAY_PROJECT_TO_CLOSE : 7) * 24 * 60 * 60;
            $this->projet->task = new StdClass();
            $this->projet->task->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_TASKS_TODO) ? Conf::$global->MAIN_DELAY_TASKS_TODO : 7) * 24 * 60 * 60;
        }

        if (isset($this->commande)) {
            $this->commande->client = new \stdClass();
            $this->commande->fournisseur = new \stdClass();
            $this->commande->client->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_ORDERS_TO_PROCESS) ? Conf::$global->MAIN_DELAY_ORDERS_TO_PROCESS : 2) * 24 * 60 * 60;
            $this->commande->fournisseur->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_SUPPLIER_ORDERS_TO_PROCESS) ? Conf::$global->MAIN_DELAY_SUPPLIER_ORDERS_TO_PROCESS : 7) * 24 * 60 * 60;
        }
        if (isset($this->propal)) {
            $this->propal->cloture = new \stdClass();
            $this->propal->facturation = new \stdClass();
            $this->propal->cloture->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_PROPALS_TO_CLOSE) ? Conf::$global->MAIN_DELAY_PROPALS_TO_CLOSE : 0) * 24 * 60 * 60;
            $this->propal->facturation->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_PROPALS_TO_BILL) ? Conf::$global->MAIN_DELAY_PROPALS_TO_BILL : 0) * 24 * 60 * 60;
        }
        if (isset($this->facture)) {
            $this->facture->client = new \stdClass();
            $this->facture->fournisseur = new \stdClass();
            $this->facture->client->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_CUSTOMER_BILLS_UNPAYED) ? Conf::$global->MAIN_DELAY_CUSTOMER_BILLS_UNPAYED : 0) * 24 * 60 * 60;
            $this->facture->fournisseur->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_SUPPLIER_BILLS_TO_PAY) ? Conf::$global->MAIN_DELAY_SUPPLIER_BILLS_TO_PAY : 0) * 24 * 60 * 60;
        }
        if (isset($this->contrat)) {
            $this->contrat->services = new \stdClass();
            $this->contrat->services->inactifs = new \stdClass();
            $this->contrat->services->expires = new \stdClass();
            $this->contrat->services->inactifs->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_NOT_ACTIVATED_SERVICES) ? Conf::$global->MAIN_DELAY_NOT_ACTIVATED_SERVICES : 0) * 24 * 60 * 60;
            $this->contrat->services->expires->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_RUNNING_SERVICES) ? Conf::$global->MAIN_DELAY_RUNNING_SERVICES : 0) * 24 * 60 * 60;
        }
        if (isset($this->commande)) {
            $this->bank->rappro = new \stdClass();
            $this->bank->cheque = new \stdClass();
            $this->bank->rappro->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_TRANSACTIONS_TO_CONCILIATE) ? Conf::$global->MAIN_DELAY_TRANSACTIONS_TO_CONCILIATE : 0) * 24 * 60 * 60;
            $this->bank->cheque->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_CHEQUES_TO_DEPOSIT) ? Conf::$global->MAIN_DELAY_CHEQUES_TO_DEPOSIT : 0) * 24 * 60 * 60;
        }
        if (isset($this->expensereport)) {
            $this->expensereport->approve = new \stdClass();
            $this->expensereport->approve->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_EXPENSEREPORTS) ? Conf::$global->MAIN_DELAY_EXPENSEREPORTS : 0) * 24 * 60 * 60;
            $this->expensereport->payment = new \stdClass();
            $this->expensereport->payment->warning_delay = (isset(Globals::$conf->$global->MAIN_DELAY_EXPENSEREPORTS_TO_PAY) ? Conf::$global->MAIN_DELAY_EXPENSEREPORTS_TO_PAY : 0) * 24 * 60 * 60;
        }

        if (!empty(Globals::$conf->$global->PRODUIT_MULTIPRICES) && empty(Globals::$conf->$global->PRODUIT_MULTIPRICES_LIMIT)) {
            Conf::$global->PRODUIT_MULTIPRICES_LIMIT = 5;
        }

        // For modules that want to disable top or left menu
        if (!empty(Globals::$conf->$global->MAIN_HIDE_TOP_MENU))
            $this->dol_hide_topmenu = Conf::$global->MAIN_HIDE_TOP_MENU;
        if (!empty(Globals::$conf->$global->MAIN_HIDE_LEFT_MENU))
            $this->dol_hide_leftmenu = Conf::$global->MAIN_HIDE_LEFT_MENU;

        if (empty(Globals::$conf->$global->MAIN_SIZE_SHORTLIST_LIMIT))
            Conf::$global->MAIN_SIZE_SHORTLIST_LIMIT = 3;

        if (!isset(Globals::$conf->$global->THEME_HIDE_BORDER_ON_INPUT))
            Conf::$global->THEME_HIDE_BORDER_ON_INPUT = 0;

        // Save inconsistent option
        if (empty(Globals::$conf->$global->AGENDA_USE_EVENT_TYPE) && (!isset(Globals::$conf->$global->AGENDA_DEFAULT_FILTER_TYPE) || Conf::$global->AGENDA_DEFAULT_FILTER_TYPE == 'AC_NON_AUTO')) {
            Conf::$global->AGENDA_DEFAULT_FILTER_TYPE = '0';    // 'AC_NON_AUTO' does not exists when AGENDA_DEFAULT_FILTER_TYPE is not on.
        }

        if (!isset(Globals::$conf->$global->MAIN_EXTRAFIELDS_IN_ONE_TD))
            Conf::$global->MAIN_EXTRAFIELDS_IN_ONE_TD = 1;

        Conf::$global->MAIN_MODULE_DOLISTORE_API_SRV = 'https://www.dolistore.com';
        Conf::$global->MAIN_MODULE_DOLISTORE_API_KEY = 'dolistorecatalogpublickey1234567';

        // For backward compatibility
        if (isset($this->product))
            $this->produit = $this->product;
        if (isset($this->facture))
            $this->invoice = $this->facture;
        if (isset($this->commande))
            $this->order = $this->commande;
        if (isset($this->contrat))
            $this->contract = $this->contrat;
        if (isset($this->categorie))
            $this->category = $this->categorie;

        // Object $mc
        if (!defined('NOREQUIREMC') && !empty($this->multicompany->enabled)) {
            if (is_object(Globals::$mc)) {
                Globals::$mc->setValues($this);
            }
        }

        // We init log handlers
        if (!empty(Globals::$conf->$global->SYSLOG_HANDLERS)) {
            $handlers = json_decode(Globals::$conf->$global->SYSLOG_HANDLERS);
        } else {
            $handlers = array();
        }

        foreach ($handlers as $handler) {
            $handler_files = array();
            $dirsyslogs = array_merge(array('/core/modules/syslog/'), $this->modules_parts['syslog']);
            foreach ($dirsyslogs as $reldir) {
                $dir = dol_buildpath($reldir, 0);
                $newdir = dol_osencode($dir);
                if (is_dir($newdir)) {
                    $file = $newdir . $handler . '.php';
                    if (file_exists($file)) {
                        $handler_files[] = $file;
                    }
                }
            }

            if (empty($handler_files)) {
                throw new Exception('Missing log handler file ' . $handler . '.php');
            }

            require_once $handler_files[0];
            $loghandlerinstance = new $handler();
            if (!$loghandlerinstance instanceof LogHandlerInterface) {
                throw new Exception('Log handler does not extend LogHandlerInterface');
            }

            if (empty($this->loghandlers[$handler])) {
                $this->loghandlers[$handler] = $loghandlerinstance;
            }
        }
    }
}