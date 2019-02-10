<?php
/* Copyright (C) 2002-2007	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2003		Xavier Dutoit			<doli@sydesy.com>
 * Copyright (C) 2004-2012	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2004		Sebastien Di Cintio		<sdicintio@ressource-toi.org>
 * Copyright (C) 2004		Benoit Mortier			<benoit.mortier@opensides.be>
 * Copyright (C) 2005-2017	Regis Houssin			<regis.houssin@inodbox.com>
 * Copyright (C) 2005		Simon Tosser			<simon@kornog-computing.com>
 * Copyright (C) 2006		Andre Cianfarani		<andre.cianfarani@acdeveloppement.net>
 * Copyright (C) 2010		Juanjo Menent			<jmenent@2byte.es>
 * Copyright (C) 2011		Philippe Grand			<philippe.grand@atoo-net.com>
 * Copyright (C) 2014		Teddy Andreotti			<125155@supinfo.com>
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
 *	\file       htdocs/master.inc.php
 * 	\ingroup	core
 *  \brief      File that defines environment for all Dolibarr process (pages or scripts)
 * 				This script reads the conf file, init $lang, $db and and empty $user
 */

require_once 'filefunc.inc.php';	// May have been already require by main.inc.php. But may not by scripts.



/*
 * Create $conf object
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/conf.class.php';


// Chargement des includes principaux de librairies communes
if (! defined('NOREQUIREUSER')) require_once DOL_DOCUMENT_ROOT .'/user/class/user.class.php';		// Need 500ko memory
if (! defined('NOREQUIRETRAN')) require_once DOL_DOCUMENT_ROOT .'/core/class/translate.class.php';
if (! defined('NOREQUIRESOC'))  require_once DOL_DOCUMENT_ROOT .'/societe/class/societe.class.php';



/*
 * Object $db
 */
if (! defined('NOREQUIREDB'))
{
    $db = getDoliDBInstance(Globals::$conf->db->type, Globals::$conf->db->host, Globals::$conf->db->user, Globals::$conf->db->pass, Globals::$conf->db->name, Globals::$conf->db->port);

    if ($db->error)
	{
		dol_print_error($db, "host=" . Globals::$conf->db->host . ", port=" . Globals::$conf->db->port . ", user=" . Globals::$conf->db->user . ", databasename=" . Globals::$conf->db->name . ", " . $db->error);
        exit;
	}
}

// Now database connexion is known, so we can forget password
//unset($dolibarr_main_db_pass); 	// We comment this because this constant is used in a lot of pages
unset(Globals::$conf->db->pass);    // This is to avoid password to be shown in memory/swap dump

/*
 * Object $user
 */

/*
 * Load object $conf
 * After this, all parameters conf->global->CONSTANTS are loaded
 */

// By default conf->entity is 1, but we change this if we ask another value.
if (session_id() && ! empty($_SESSION["dol_entity"]))			// Entity inside an opened session
{
	Globals::$conf->entity = $_SESSION["dol_entity"];
}
else if (! empty($_ENV["dol_entity"]))							// Entity inside a CLI script
{
	Globals::$conf->entity = $_ENV["dol_entity"];
}
else if (isset($_POST["loginfunction"]) && GETPOST("entity",'int'))	// Just after a login page
{
	Globals::$conf->entity = GETPOST("entity", 'int');
}
else if (defined('DOLENTITY') && is_numeric(DOLENTITY))			// For public page with MultiCompany module
{
	Globals::$conf->entity = DOLENTITY;
}

// Sanitize entity
if (!is_numeric(Globals::$conf->entity))
    Globals::$conf->entity = 1;

if (! defined('NOREQUIREDB'))
{
	//print "Will work with data into entity instance number '".Globals::$conf->entity."'";
    // Here we read database (llx_const table) and define Globals::$conf->global->XXX var.
    Globals::$conf->setValues($db);
}

// Overwrite database value
if (!empty(Globals::$conf->file->mailing_limit_sendbyweb)) {
	Globals::$conf->global->MAILING_LIMIT_SENDBYWEB = Globals::$conf->file->mailing_limit_sendbyweb;
}
if (empty(Globals::$conf->global->MAILING_LIMIT_SENDBYWEB)) {
    Globals::$conf->global->MAILING_LIMIT_SENDBYWEB = 25;
}
if (!empty(Globals::$conf->file->mailing_limit_sendbycli)) {
    Globals::$conf->global->MAILING_LIMIT_SENDBYCLI = Globals::$conf->file->mailing_limit_sendbycli;
}
if (empty(Globals::$conf->global->MAILING_LIMIT_SENDBYCLI)) {
    Globals::$conf->global->MAILING_LIMIT_SENDBYCLI = 0;
}

// If software has been locked. Only login Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED is allowed.
if (!empty(Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED)) {
	$ok=0;
	if ((! session_id() || ! isset($_SESSION["dol_login"])) && ! isset($_POST["username"]) && ! empty($_SERVER["GATEWAY_INTERFACE"])) $ok=1;	// We let working pages if not logged and inside a web browser (login form, to allow login by admin)
	elseif (isset($_POST["username"]) && $_POST["username"] == Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED)
        $ok = 1;    // We let working pages that is a login submission (login submit, to allow login by admin)
    elseif (defined('NOREQUIREDB'))   $ok=1;				// We let working pages that don't need database access (xxx.css.php)
	elseif (defined('EVEN_IF_ONLY_LOGIN_ALLOWED')) $ok=1;	// We let working pages that ask to work even if only login enabled (logout.php)
	elseif (session_id() && isset($_SESSION["dol_login"]) && $_SESSION["dol_login"] == Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED)
        $ok = 1; // We let working if user is allowed admin
    if (! $ok)
	{
		if (session_id() && isset($_SESSION["dol_login"]) && $_SESSION["dol_login"] != Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED) {
			print 'Sorry, your application is offline.'."\n";
			print 'You are logged with user "' . $_SESSION["dol_login"] . '" and only administrator user "' . Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED . '" is allowed to connect for the moment.' . "\n";
            $nexturl=DOL_URL_ROOT.'/user/logout.php';
			print 'Please try later or <a href="'.$nexturl.'">click here to disconnect and change login user</a>...'."\n";
		}
		else
		{
			print 'Sorry, your application is offline. Only administrator user "' . Globals::$conf->global->MAIN_ONLY_LOGIN_ALLOWED . '" is allowed to connect for the moment.' . "\n";
            $nexturl=DOL_URL_ROOT.'/';
			print 'Please try later or <a href="'.$nexturl.'">click here to change login user</a>...'."\n";
		}
		exit;
	}
}

// Create object $mysoc (A thirdparty object that contains properties of companies managed by Dolibarr.
if (! defined('NOREQUIREDB') && ! defined('NOREQUIRESOC'))
{
	require_once DOL_DOCUMENT_ROOT .'/societe/class/societe.class.php';

	$mysoc=new Societe($db);
	$mysoc->setMysoc($conf);

	// For some countries, we need to invert our address with customer address
	if ($mysoc->country_code == 'DE' && !isset(Globals::$conf->global->MAIN_INVERT_SENDER_RECIPIENT))
        Globals::$conf->global->MAIN_INVERT_SENDER_RECIPIENT = 1;
}


// Set default language (must be after the setValues setting global Globals::$conf->global->MAIN_LANG_DEFAULT. Page main.inc.php will overwrite langs->defaultlang with user value later)
if (! defined('NOREQUIRETRAN'))
{
    $langcode = (GETPOST('lang', 'aZ09') ? GETPOST('lang', 'aZ09', 1) : (empty(Globals::$conf->global->MAIN_LANG_DEFAULT) ? 'auto' : Globals::$conf->global->MAIN_LANG_DEFAULT));
    if (defined('MAIN_LANG_DEFAULT')) $langcode=constant('MAIN_LANG_DEFAULT');
    $langs->setDefaultLang($langcode);
}




if (! defined('MAIN_LABEL_MENTION_NPR') ) define('MAIN_LABEL_MENTION_NPR','NPR');

