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
use Alixar\Helpers\DolUtils;
use Alixar\Base\Categorie;

class CategoriesIndexView extends \Alixar\Base\AlixarView
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
        Skin::setTemplate('categories_index');
    }

    public function vars()
    {

    }

    public function draw()
    {
        $catname = $this->ctrl->catname;
        $id = $this->ctrl->id;

// Load translation files required by the page
        Globals::$langs->load("categories");

        if (!Globals::$user->rights->categorie->lire) {
            accessforbidden();
        }

        $this->type = (DolUtils::GETPOST('type', 'aZ09') ? DolUtils::GETPOST('type', 'aZ09') : Categorie::TYPE_PRODUCT);

        if (is_numeric($this->type)) {
            $this->type = Categorie::$MAP_ID_TO_CODE[$this->type]; // For backward compatibility
        }

        /*
         * View
         */

        $categstatic = new Categorie();
        $form = new Form();

        if ($this->type == Categorie::TYPE_PRODUCT) {
            $this->title = Globals::$langs->trans("ProductsCategoriesArea");
            $this->typetext = 'product';
        } elseif ($this->type == Categorie::TYPE_SUPPLIER) {
            $this->title = Globals::$langs->trans("SuppliersCategoriesArea");
            $this->typetext = 'supplier';
        } elseif ($this->type == Categorie::TYPE_CUSTOMER) {
            $this->title = Globals::$langs->trans("CustomersCategoriesArea");
            $this->typetext = 'customer';
        } elseif ($this->type == Categorie::TYPE_MEMBER) {
            $this->title = Globals::$langs->trans("MembersCategoriesArea");
            $this->typetext = 'member';
        } elseif ($this->type == Categorie::TYPE_CONTACT) {
            $this->title = Globals::$langs->trans("ContactsCategoriesArea");
            $this->typetext = 'contact';
        } elseif ($this->type == Categorie::TYPE_ACCOUNT) {
            $this->title = Globals::$langs->trans("AccountsCategoriesArea");
            $this->typetext = 'bank_account';
        } elseif ($this->type == Categorie::TYPE_PROJECT) {
            $this->title = Globals::$langs->trans("ProjectsCategoriesArea");
            $this->typetext = 'project';
        } elseif ($this->type == Categorie::TYPE_USER) {
            $this->title = Globals::$langs->trans("UsersCategoriesArea");
            $this->typetext = 'user';
        } else {
            $this->title = Globals::$langs->trans("CategoriesArea");
            $this->typetext = 'unknown';
        }

        $arrayofjs = array('/includes/jquery/plugins/jquerytreeview/jquery.treeview.js', '/includes/jquery/plugins/jquerytreeview/lib/jquery.cookie.js');
        $arrayofcss = array('/includes/jquery/plugins/jquerytreeview/jquery.treeview.css');

        $this->llxHeader('', $this->title, '', '', 0, 0, $arrayofjs, $arrayofcss);

        $newcardbutton = '<a class="butActionNew" href="' . BASE_URI . '/categories/card.php?action=create&type=' . $this->type . '&backtopage=' . urlencode($_SERVER["PHP_SELF"] . '?type=' . $this->type) . '"><span class="valignmiddle">' . Globals::$langs->trans("NewCategory") . '</span>';
        $newcardbutton .= '<span class="fa fa-plus-circle valignmiddle"></span>';
        $newcardbutton .= '</a>';

        print DolUtils::load_fiche_titre($this->title, $newcardbutton);

//print '<table border="0" width="100%" class="notopnoleftnoright">';
//print '<tr><td valign="top" width="30%" class="notopnoleft">';
        print '<div class="fichecenter"><div class="fichethirdleft">';


        /*
         * Zone recherche produit/service
         */
        print '<form method="post" action="index.php?type=' . $this->type . '">';
        print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
        print '<input type="hidden" name="type" value="' . $this->type . '">';


        print '<table class="noborder nohover" width="100%">';
        print '<tr class="liste_titre">';
        print '<td colspan="3">' . Globals::$langs->trans("Search") . '</td>';
        print '</tr>';
        print '<tr class="oddeven"><td>';
        print Globals::$langs->trans("Name") . ':</td><td><input class="flat inputsearch" type="text" name="catname" value="' . $catname . '"/></td><td><input type="submit" class="button" value="' . Globals::$langs->trans("Search") . '"></td></tr>';
        /*
          // faire une rech dans une sous categorie uniquement
          print '<tr '.$bc[0].'><td>';
          print Globals::$langs->trans("SubCatOf").':</td><td>';

          print $form->select_all_categories('','subcatof');
          print '</td>';
          print '<td><input type="submit" class="button" value="'.Globals::$langs->trans ("Search").'"></td></tr>';
         */

        print '</table></form>';


//print '</td><td valign="top" width="70%">';
        print '</div><div class="fichetwothirdright"><div class="ficheaddleft">';


        /*
         * Categories found
         */
        if ($catname || $id > 0) {
            $cats = $categstatic->rechercher($id, $catname, $this->typetext);

            print '<table class="noborder" width="100%">';
            print '<tr class="liste_titre"><td colspan="2">' . Globals::$langs->trans("FoundCats") . '</td></tr>';

            foreach ($cats as $cat) {
                print "\t" . '<tr class="oddeven">' . "\n";
                print "\t\t<td>";
                $categstatic->id = $cat->id;
                $categstatic->ref = $cat->label;
                $categstatic->label = $cat->label;
                $categstatic->type = $cat->type;
                $categstatic->color = $cat->color;
                print '<span class="noborderoncategories" ' . ($categstatic->color ? ' style="background: #' . $categstatic->color . ';"' : ' style="background: #aaa"') . '>';
                print $categstatic->getNomUrl(1, '');
                print '</span>';
                print "</td>\n";
                print "\t\t<td>";
                print dolGetFirstLineOfText($cat->description);
                print "</td>\n";
                print "\t</tr>\n";
            }
            print "</table>";
        } else
            print '&nbsp;';


//print '</td></tr></table>';
        print '</div></div></div>';

        print '<div class="fichecenter"><br>';


// Charge tableau des categories
        $cate_arbo = $categstatic->get_full_arbo($this->typetext);

// Define fulltree array
        $fulltree = $cate_arbo;

// Define data (format for treeview)
        $data = array();
        $data[] = array('rowid' => 0, 'fk_menu' => -1, 'title' => "racine", 'mainmenu' => '', 'leftmenu' => '', 'fk_mainmenu' => '', 'fk_leftmenu' => '');
        foreach ($fulltree as $key => $val) {
            $categstatic->id = $val['id'];
            $categstatic->ref = $val['label'];
            $categstatic->color = $val['color'];
            $categstatic->type = $this->type;
            $li = $categstatic->getNomUrl(1, '', 60);
            $desc = dol_htmlcleanlastbr($val['description']);

            $data[] = array(
                'rowid' => $val['rowid'],
                'fk_menu' => $val['fk_parent'],
                'entry' => '<table class="nobordernopadding centpercent"><tr><td><span class="noborderoncategories" ' . ($categstatic->color ? ' style="background: #' . $categstatic->color . ';"' : ' style="background: #aaa"') . '>' . $li . '</span></td>' .
                //'<td width="50%">'.dolGetFirstLineOfText($desc).'</td>'.
                '<td align="right" width="20px;"><a href="' . DOL_URL_ROOT . '/categories/viewcat.php?id=' . $val['id'] . '&type=' . $this->type . '">' . img_view() . '</a></td>' .
                '</tr></table>'
            );
        }


//print_barre_liste('', 0, $_SERVER["PHP_SELF"], '', '', '', '', 0, 0, '', 0, $newcardbutton, '', 0, 1, 1);

        print '<table class="liste nohover" width="100%">';
        print '<tr class="liste_titre"><td>' . Globals::$langs->trans("Categories") . '</td><td></td><td align="right">';
        if (!empty($conf->use_javascript_ajax)) {
            print '<div id="iddivjstreecontrol"><a class="notasortlink" href="#">' . img_picto('', 'object_category') . ' ' . Globals::$langs->trans("UndoExpandAll") . '</a> | <a class="notasortlink" href="#">' . img_picto('', 'object_category-expanded') . ' ' . Globals::$langs->trans("ExpandAll") . '</a></div>';
        }
        print '</td></tr>';

        $nbofentries = (count($data) - 1);

        if ($nbofentries > 0) {
            print '<tr class="pair"><td colspan="3">';
            tree_recur($data, $data[0], 0);
            print '</td></tr>';
        } else {
            print '<tr class="pair">';
            print '<td colspan="3"><table class="nobordernopadding"><tr class="nobordernopadding"><td>' . img_picto_common('', 'treemenu/branchbottom.gif') . '</td>';
            print '<td valign="middle">';
            print Globals::$langs->trans("NoCategoryYet");
            print '</td>';
            print '<td>&nbsp;</td>';
            print '</table></td>';
            print '</tr>';
        }

        print "</table>";

        print '</div>';

// End of page
        llxFooter();
        $db->close();
    }
}
