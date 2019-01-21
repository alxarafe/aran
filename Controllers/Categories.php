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

use Alixar\Base\AlixarController;
use Alixar\Views\CategoriesView;
use Alixar\Helpers\Globals;
use Alixar\Helpers\DolUtils;
use Alixar\Base\Categorie;
use Alixar\Base\ExtraFields;

// require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
// require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
// require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';

class Categories extends AlixarController
{

    public function __construct()
    {
        parent::__construct();
        Skin::$view = new CategoriesView($this);
    }

    function main()
    {

// Load translation files required by the page
       Globals::$langs->load("categories");

// Security check
        $socid = DolUtils::GETPOST('socid', 'int');
        if (!Globals::$user->rights->categorie->lire)
            accessforbidden();

        $action = DolUtils::GETPOST('action', 'alpha');
        $cancel = DolUtils::GETPOST('cancel', 'alpha');
        $origin = DolUtils::GETPOST('origin', 'alpha');
        $catorigin = DolUtils::GETPOST('catorigin', 'int');
        $type = DolUtils::GETPOST('type', 'alpha');
        $urlfrom = DolUtils::GETPOST('urlfrom', 'alpha');
        $backtopage = DolUtils::GETPOST('backtopage', 'alpha');

        $socid = DolUtils::GETPOST('socid', 'int');
        $label = DolUtils::GETPOST('label');
        $description = DolUtils::GETPOST('description');
        $color = DolUtils::GETPOST('color');
        $visible = DolUtils::GETPOST('visible');
        $parent = DolUtils::GETPOST('parent');

        if ($origin) {
            if ($type == Categorie::TYPE_PRODUCT)
                $idProdOrigin = $origin;
            if ($type == Categorie::TYPE_SUPPLIER)
                $idSupplierOrigin = $origin;
            if ($type == Categorie::TYPE_CUSTOMER)
                $idCompanyOrigin = $origin;
            if ($type == Categorie::TYPE_MEMBER)
                $idMemberOrigin = $origin;
            if ($type == Categorie::TYPE_CONTACT)
                $idContactOrigin = $origin;
            if ($type == Categorie::TYPE_PROJECT)
                $idProjectOrigin = $origin;
        }

        if ($catorigin && $type == Categorie::TYPE_PRODUCT)
            $idCatOrigin = $catorigin;

        $object = new Categorie();

        $extrafields = new ExtraFields();
        $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

// Initialize technical object to manage hooks. Note that conf->hooks_modules contains array array
        Globals::$hookManager->initHooks(array('categorycard'));


        /*
         * 	Actions
         */

// Add action
        if ($action == 'add' && Globals::$user->rights->categorie->creer) {
            // Action ajout d'une categorie
            if ($cancel) {
                if ($urlfrom) {
                    header("Location: " . $urlfrom);
                    exit;
                } else if ($idProdOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idProdOrigin . '&type=' . $type);
                    exit;
                } else if ($idCompanyOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idCompanyOrigin . '&type=' . $type);
                    exit;
                } else if ($idSupplierOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idSupplierOrigin . '&type=' . $type);
                    exit;
                } else if ($idMemberOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idMemberOrigin . '&type=' . $type);
                    exit;
                } else if ($idContactOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idContactOrigin . '&type=' . $type);
                    exit;
                } else if ($idProjectOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idProjectOrigin . '&type=' . $type);
                    exit;
                } else {
                    header("Location: " . DOL_URL_ROOT . '/categories/index.php?leftmenu=cat&type=' . $type);
                    exit;
                }
            }



            $object->label = $label;
            $object->color = $color;
            $object->description = dol_htmlcleanlastbr($description);
            $object->socid = ($socid ? $socid : 'null');
            $object->visible = $visible;
            $object->type = $type;

            if ($parent != "-1")
                $object->fk_parent = $parent;

            $ret = $extrafields->setOptionalsFromPost($extralabels, $object);
            if ($ret < 0)
                $error++;

            if (!$object->label) {
                $error++;
                setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
                $action = 'create';
            }

            // Create category in database
            if (!$error) {
                $result = $object->create($user);
                if ($result > 0) {
                    $action = 'confirmed';
                    $_POST["addcat"] = '';
                } else {
                    setEventMessages($object->error, $object->errors, 'errors');
                }
            }
        }

// Confirm action
        if (($action == 'add' || $action == 'confirmed') && Globals::$user->rights->categorie->creer) {
            // Action confirmation de creation categorie
            if ($action == 'confirmed') {
                if ($urlfrom) {
                    header("Location: " . $urlfrom);
                    exit;
                } elseif ($backtopage) {
                    header("Location: " . $backtopage);
                    exit;
                } else if ($idProdOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idProdOrigin . '&type=' . $type . '&mesg=' . urlencode($langs->trans("CatCreated")));
                    exit;
                } else if ($idCompanyOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idCompanyOrigin . '&type=' . $type . '&mesg=' . urlencode($langs->trans("CatCreated")));
                    exit;
                } else if ($idSupplierOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idSupplierOrigin . '&type=' . $type . '&mesg=' . urlencode($langs->trans("CatCreated")));
                    exit;
                } else if ($idMemberOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idMemberOrigin . '&type=' . $type . '&mesg=' . urlencode($langs->trans("CatCreated")));
                    exit;
                } else if ($idContactOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idContactOrigin . '&type=' . $type . '&mesg=' . urlencode($langs->trans("CatCreated")));
                    exit;
                } else if ($idProjectOrigin) {
                    header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $idProjectOrigin . '&type=' . $type . '&mesg=' . urlencode($langs->trans("CatCreated")));
                    exit;
                }

                header("Location: " . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $result . '&type=' . $type);
                exit;
            }
        }


    }
}
