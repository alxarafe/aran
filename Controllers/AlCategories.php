<?php
/* Copyright (C) 2005		Matthieu Valleton	<mv@seeschloss.org>
 * Copyright (C) 2006-2017	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2014	Regis Houssin		<regis.houssin@inodbox.com>
 * Copyright (C) 2007		Patrick Raguin		<patrick.raguin@gmail.com>
 * Copyright (C) 2013		Florian Henry		<florian.henry@open-concept.pro>
 * Copyright (C) 2015       Raphaël Doursenaud  <rdoursenaud@gpcsolutions.fr>
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
namespace Alixar\Controllers;

use Alxarafe\Helpers\Skin;
use Alixar\Base\AlixarController;
use Alixar\Views\CategoriesView;
use Alixar\Views\CategoriesIndexView;
use Alixar\Helpers\Globals;
use Alixar\Helpers\AlDolUtils;
use Alixar\Base\AlCategorie;
use Alixar\Base\AlExtraFields;

// require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
// require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
// require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';

class AlCategories extends AlixarController
{

    public $action;
    public $cancel;
    public $origin;
    public $catorigin;
    public $type;
    public $urlfrom;
    public $backtopage;
    public $id;
    public $socid;
    public $label;
    public $description;
    public $color;
    public $visible;
    public $parent;
    public $object;
    public $catname;

    public function __construct()
    {
        die('En constructor');
        parent::__construct();

        // Load translation files required by the page
        Globals::$langs->load("categories");

        // Security check
        $this->socid = AlDolUtils::GETPOST('socid', 'int');
        if (!Globals::$user->rights->categorie->lire) {
            accessforbidden();
        }

        $this->getVars();
    }

    function getVars()
    {

        $this->action = AlDolUtils::GETPOST('action', 'alpha');
        $this->cancel = AlDolUtils::GETPOST('cancel', 'alpha');
        $this->origin = AlDolUtils::GETPOST('origin', 'alpha');
        $this->catorigin = AlDolUtils::GETPOST('catorigin', 'int');
        $this->type = AlDolUtils::GETPOST('type', 'alpha');
        $this->urlfrom = AlDolUtils::GETPOST('urlfrom', 'alpha');
        $this->backtopage = AlDolUtils::GETPOST('backtopage', 'alpha');

        $this->id = AlDolUtils::GETPOST('id', 'int');
        $this->socid = AlDolUtils::GETPOST('socid', 'int');
        $this->label = AlDolUtils::GETPOST('label');
        $this->description = AlDolUtils::GETPOST('description');
        $this->color = AlDolUtils::GETPOST('color');
        $this->visible = AlDolUtils::GETPOST('visible');
        $this->parent = AlDolUtils::GETPOST('parent');
        $this->catname = AlDolUtils::GETPOST('catname', 'alpha');
    }

    function index()
    {
        die('En index');
        Skin::$view = new CategoriesIndexView($this);
    }

    function main()
    {
        die('En main');
        Skin::$view = new CategoriesView($this);
        if ($this->origin) {
            if ($this->type == AlCategorie::TYPE_PRODUCT) {
                $idProdOrigin = $this->origin;
            }
            if ($this->type == AlCategorie::TYPE_SUPPLIER) {
                $idSupplierOrigin = $this->origin;
            }
            if ($this->type == AlCategorie::TYPE_CUSTOMER) {
                $idCompanyOrigin = $this->origin;
            }
            if ($this->type == AlCategorie::TYPE_MEMBER) {
                $idMemberOrigin = $this->origin;
            }
            if ($this->type == AlCategorie::TYPE_CONTACT) {
                $idContactOrigin = $this->origin;
            }
            if ($this->type == AlCategorie::TYPE_PROJECT) {
                $idProjectOrigin = $this->origin;
            }
        }

        if ($this->catorigin && $this->type == AlCategorie::TYPE_PRODUCT) {
            $idCatOrigin = $this->catorigin;
        }

        $this->object = new AlCategorie();

        $extrafields = new AlExtraFields();
        $extralabels = $extrafields->fetch_name_optionals_label($this->object->table_element);

        // Initialize technical object to manage hooks. Note that conf->hooks_modules contains array array
        Globals::$hookManager->initHooks(array('categorycard'));


        /*
         * 	Actions
         */

        // Add action
        if ($this->action == 'add' && Globals::$user->rights->categorie->creer) {
            // Action ajout d'une categorie
            if ($this->cancel) {
                if ($this->urlfrom) {
                    header("Location: " . $urlfrom);
                    exit;
                }
                if ($idProdOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idProdOrigin . '&type=' . $this->type);
                    exit;
                }
                if ($idCompanyOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idCompanyOrigin . '&type=' . $this->type);
                    exit;
                }
                if ($idSupplierOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idSupplierOrigin . '&type=' . $this->type);
                    exit;
                }
                if ($idMemberOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idMemberOrigin . '&type=' . $this->type);
                    exit;
                }
                if ($idContactOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idContactOrigin . '&type=' . $this->type);
                    exit;
                }
                if ($idProjectOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idProjectOrigin . '&type=' . $this->type);
                    exit;
                }
                header("Location: " . DOL_URL_ROOT . '/categories/index.php?leftmenu=cat&type=' . $this->type);
                exit;
            }

            $object->label = $this->label;
            $object->color = $this->color;
            $object->description = AlDolUtils::dol_htmlcleanlastbr($this->description);
            $object->socid = ($this->socid ? $this->socid : 'null');
            $object->visible = $this->visible;
            $object->type = $this->type;

            if ($parent != "-1") {
                $object->fk_parent = $parent;
            }

            $ret = $extrafields->setOptionalsFromPost($extralabels, $object);
            if ($ret < 0) {
                $error++;
            }

            if (!$object->label) {
                $error++;
                setEventMessages(Globals::$langs->trans("ErrorFieldRequired", Globals::$langs->transnoentities("Ref")), null, 'errors');
                $this->action = 'create';
            }

            // Create category in database
            if (!$error) {
                $result = $object->create($user);
                if ($result > 0) {
                    $this->action = 'confirmed';
                    $_POST["addcat"] = '';
                } else {
                    setEventMessages($object->error, $object->errors, 'errors');
                }
            }
        }

        // Confirm action
        if (($this->action == 'add' || $this->action == 'confirmed') && Globals::$user->rights->categorie->creer) {
            // Action confirmation de creation categorie
            if ($this->action == 'confirmed') {
                if ($urlfrom) {
                    header("Location: " . $urlfrom);
                    exit;
                }
                if ($backtopage) {
                    header("Location: " . $backtopage);
                    exit;
                }
                if ($idProdOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idProdOrigin . '&type=' . $this->type . '&mesg=' . urlencode(Globals::$langs->trans("CatCreated")));
                    exit;
                }
                if ($idCompanyOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idCompanyOrigin . '&type=' . $this->type . '&mesg=' . urlencode(Globals::$langs->trans("CatCreated")));
                    exit;
                }
                if ($idSupplierOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idSupplierOrigin . '&type=' . $this->type . '&mesg=' . urlencode(Globals::$langs->trans("CatCreated")));
                    exit;
                }
                if ($idMemberOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idMemberOrigin . '&type=' . $this->type . '&mesg=' . urlencode(Globals::$langs->trans("CatCreated")));
                    exit;
                }
                if ($idContactOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idContactOrigin . '&type=' . $this->type . '&mesg=' . urlencode(Globals::$langs->trans("CatCreated")));
                    exit;
                }
                if ($idProjectOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idProjectOrigin . '&type=' . $this->type . '&mesg=' . urlencode(Globals::$langs->trans("CatCreated")));
                    exit;
                }

                header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $result . '&type=' . $this->type);
                exit;
            }
        }
    }
}
