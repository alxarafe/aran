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
use Alixar\Base\Form;
use Alixar\Base\FormOther;

class CategoriesView extends \Alixar\Base\AlixarView
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
        Skin::setTemplate('categories');
    }

    public function vars()
    {

    }

    public function draw()
    {
        /*
         * View
         */

        var_dump($_POST);
        var_dump($_GET);

        $action = filter_input(INPUT_GET, 'action') ?? '';

        $form = new Form();
        $formother = new FormOther();

        $helpurl = '';
        $this->llxHeader("", Globals::$langs->trans("Categories"), $helpurl);

        if (Globals::$user->rights->categorie->creer) {
            // Create or add
            if ($action == 'create' || filter_input(INPUT_POST, "addcat") == 'addcat') {
                DolUtils::dol_set_focus('#label');

                print '<form action="' . $_SERVER['PHP_SELF'] . '?type=' . $type . '" method="POST">';
                print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
                print '<input type="hidden" name="urlfrom" value="' . $urlfrom . '">';
                print '<input type="hidden" name="action" value="add">';
                print '<input type="hidden" name="addcat" value="addcat">';
                print '<input type="hidden" name="id" value="' . DolUtils::GETPOST('origin', 'alpha') . '">';
                print '<input type="hidden" name="type" value="' . $type . '">';
                print '<input type="hidden" name="backtopage" value="' . $backtopage . '">';
                if ($origin)
                {
                    print '<input type="hidden" name="origin" value="' . $origin . '">';
                }
                if ($catorigin)
                {
                    print '<input type="hidden" name="catorigin" value="' . $catorigin . '">';
                }

                print load_fiche_titre($langs->trans("CreateCat"));

                DolUtils::dol_fiche_head('');

                print '<table width="100%" class="border">';

                // Ref
                print '<tr>';
                print '<td class="titlefieldcreate fieldrequired">' . $langs->trans("Ref") . '</td><td><input id="label" class="minwidth100" name="label" value="' . $label . '">';
                print'</td></tr>';

                // Description
                print '<tr><td class="tdtop">' . $langs->trans("Description") . '</td><td>';
                //require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
                $doleditor = new DolEditor('description', $description, '', 200, 'dolibarr_notes', '', false, true, Globals::$conf->global->FCKEDITOR_ENABLE_PRODUCTDESC, ROWS_6, '90%');
                $doleditor->Create();
                print '</td></tr>';

                // Color
                print '<tr><td>' . $langs->trans("Color") . '</td><td>';
                print $formother->selectColor($color, 'color');
                print '</td></tr>';

                // Parent category
                print '<tr><td>' . $langs->trans("AddIn") . '</td><td>';
                print $form->select_all_categories($type, $catorigin, 'parent');
                print ajax_combobox('parent');
                print '</td></tr>';

                $parameters = array();
                $reshook = Globals::$hookManager->executeHooks('formObjectOptions', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
                print Globals::$hookManager->resPrint;
                if (empty($reshook)) {
                    print $object->showOptionals($extrafields, 'edit');
                }

                print '</table>';

                DolUtils::dol_fiche_end('');

                print '<div class="center">';
                print '<input type="submit" class="button" value="' . $langs->trans("CreateThisCat") . '" name="creation" />';
                print '&nbsp; &nbsp; &nbsp;';
                print '<input type="submit" class="button" value="' . $langs->trans("Cancel") . '" name="cancel" />';
                print '</div>';

                print '</form>';
            }
        }

// End of page
        $this->llxFooter();
        //$db->close();
    }
}
