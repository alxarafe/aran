<?php

use Alxarafe\Helpers\Config;
use Alixar\Helpers\AlDolUtils;
use Alixar\Helpers\AlSecurity;
use Alixar\Helpers\Globals;

/**
 * Check validity of user/password/entity
 * If test is ko, reason must be filled into $_SESSION["dol_loginmesg"]
 *
 * @param	string	$usertotest		Login
 * @param	string	$passwordtotest	Password
 * @param   int		$entitytotest   Number of instance (always 1 if module multicompany not enabled)
 * @return	string					Login if OK, '' if KO
 */
function check_user_password_dolibarr($usertotest, $passwordtotest, $entitytotest = 1)
{
    // Force master entity in transversal mode
    $entity = $entitytotest;
    if (!empty($conf->multicompany->enabled) && !empty($conf->global->MULTICOMPANY_TRANSVERSE_MODE)) {
        $entity = 1;
    }

    $login = '';

    if (!empty($usertotest)) {
        AlDolUtils::dol_syslog("functions_dolibarr::check_user_password_dolibarr usertotest=" . $usertotest . " passwordtotest=" . preg_replace('/./', '*', $passwordtotest) . " entitytotest=" . $entitytotest);

        // If test username/password asked, we define $test=false if ko and $login var to login if ok, set also $_SESSION["dol_loginmesg"] if ko
        $table = MAIN_DB_PREFIX . "user";
        $usernamecol1 = 'login';
        $usernamecol2 = 'email';
        $entitycol = 'entity';

        $sql = 'SELECT rowid, login, entity, pass, pass_crypted';
        $sql .= ' FROM ' . $table;
        //$sql .= ' WHERE (' . $usernamecol1 . " = '" . $db->escape($usertotest) . "'";
        $sql .= ' WHERE (' . $usernamecol1 . " = '" . $usertotest . "'";
        if (preg_match('/@/', $usertotest)) {
            // $sql .= ' OR ' . $usernamecol2 . " = '" . $db->escape($usertotest) . "'";
            $sql .= ' OR ' . $usernamecol2 . " = '" . $usertotest . "'";
        }
        $sql .= ') AND ' . $entitycol . " IN (0," . ($entity ? $entity : 1) . ")";
        $sql .= ' AND statut = 1';
        // Required to first found the user into entity, then the superadmin.
        // For the case (TODO and that we must avoid) a user has renamed its login with same value than a user in entity 0.
        $sql .= ' ORDER BY entity DESC';

        $users = Config::$dbEngine->select($sql);
        if (count($users) > 0) {
            $user = $users[0];

            $passclear = $user['pass'];
            $passcrypted = $user['pass_crypted'];
            $passtyped = $passwordtotest;

            $passok = false;
            // Check crypted password
            $cryptType = '';
            if (!empty($conf->global->DATABASE_PWD_ENCRYPTED)) {
                $cryptType = $conf->global->DATABASE_PWD_ENCRYPTED;
            }

            // By default, we used MD5
            if (!in_array($cryptType, array('md5'))) {
                $cryptType = 'md5';
            }

            // Check crypted password according to crypt algorithm
            if ($cryptType == 'md5') {
                if (AlSecurity::dol_verifyHash($passtyped, $passcrypted)) {
                    $passok = true;
                    AlDolUtils::dol_syslog("functions_dolibarr::check_user_password_dolibarr Authentification ok - " . $cryptType . " of pass is ok");
                }
            }

            // For compatibility with old versions
            if (!$passok) {
                if ((!$passcrypted || $passtyped) && ($passclear && ($passtyped == $passclear))) {
                    $passok = true;
                    AlDolUtils::dol_syslog("functions_dolibarr::check_user_password_dolibarr Authentification ok - found pass in database");
                }
            }

            // Password ok ?
            if ($passok) {
                $login = $user['login'];
            } else {
                DolUtils::dol_syslog("functions_dolibarr::check_user_password_dolibarr Authentification ko bad password for '" . $usertotest . "'");
                sleep(2);      // Anti brut force protection
                // Load translation files required by the page
                Globals::$langs->loadLangs(array('main', 'errors'));

                $_SESSION["dol_loginmesg"] = Globals::$langs->trans("ErrorBadLoginPassword");
            }
        } else {
            AlDolUtils::dol_syslog("functions_dolibarr::check_user_password_dolibarr Authentification ko user not found for '" . $usertotest . "'");
            sleep(1);

            // Load translation files required by the page
            Globals::$langs->loadLangs(array('main', 'errors'));

            $_SESSION["dol_loginmesg"] = Globals::$langs->trans("ErrorBadLoginPassword");
        }
    }

    return $login;
}
