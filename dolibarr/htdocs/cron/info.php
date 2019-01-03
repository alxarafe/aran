<?php
/* Copyright (C) 2013	Florian Henry	<florian.henry@open-concept.pro>
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

/**
 *  \file       	htdocs/cron/info.php
 *  \brief      	Page of info of a cron job
 */


// Copyright (C) 2018 Alxarafe/Alixar  <info@alxarafe.com>
defined('BASE_PATH') or die('Single entry point through the index.php of the main folder');
require DOL_BASE_PATH . '/main.inc.php';


require_once DOL_DOCUMENT_ROOT."/cron/class/cronjob.class.php";
require_once DOL_DOCUMENT_ROOT.'/core/lib/cron.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

// Load translation files required by the page
$langs->loadLangs(array('admin', 'cron'));

// Security check
if (!$user->rights->cron->read) accessforbidden();

$id=GETPOST('id','int');

$mesg = '';

/*
 * View
*/

llxHeader('',$langs->trans("CronInfo"));

$object = new Cronjob($db);
$object->fetch($id);
$object->info($id);

$head = cron_prepare_head($object);

dol_fiche_head($head, 'info', $langs->trans("CronTask"), -1, 'cron');

$linkback = '<a href="' . DOL_URL_ROOT . '/cron/list.php?status=-2&restore_lastsearch_values=1">' . $langs->trans("BackToList") . '</a>';

$morehtmlref='<div class="refidno">';
$morehtmlref.='</div>';

dol_banner_tab($object, 'id', $linkback, 1, 'rowid', 'ref', $morehtmlref);

print '<div class="underbanner clearboth"></div>';

print '<br>';

print '<table width="100%"><tr><td>';
dol_print_object_info($object);
print '</td></tr></table>';
print '</div>';

llxFooter();
$db->close();
