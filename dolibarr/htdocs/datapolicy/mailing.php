<?php
/* Copyright (C) 2018      Nicolas ZABOURI      <info@inovea-conseil.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    datapolicy/mailing.php
 * \ingroup datapolicy
 * \brief   datapolicy mailing page.
 */


// Copyright (C) 2018 Alxarafe/Alixar  <info@alxarafe.com>
defined('BASE_PATH') or die('Single entry point through the index.php of the main folder');
require DOL_BASE_PATH . '/main.inc.php';
dol_include_once('/contact/class/contact.class.php');
dol_include_once('/datapolicy/class/datapolicy.class.php');

$idcontact = GETPOST('idc');

if(!empty($idcontact)){
    $contact = new Contact($db);
    $contact->fetch($idcontact);
    DataPolicy::sendMailDataPolicyContact($contact);
}else{

    $contacts = new DataPolicy($db);
    $contacts->getAllContactNotInformed();
    $contacts->getAllCompaniesNotInformed();
    $contacts->getAllAdherentsNotInformed();
    echo $langs->trans('AllAgreementSend');
}
