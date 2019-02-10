<?php
use Alixar\Helpers\AlDolUtils;

/**
 * Check validity of user/password/entity
 * If test is ko, reason must be filled into $_SESSION["dol_loginmesg"]
 *
 * @param	string	$usertotest		Login
 * @param	string	$passwordtotest	Password
 * @param   int		$entitytotest   Number of instance (always 1 if module multicompany not enabled)
 * @return	string					Login if OK, '' if KO
*/
function check_user_password_http($usertotest,$passwordtotest,$entitytotest)
{
	AlDolUtils::dol_syslog("functions_http::check_user_password_http _SERVER[REMOTE_USER]=" . (empty($_SERVER["REMOTE_USER"]) ? '' : $_SERVER["REMOTE_USER"]));

    $login='';
	if (! empty($_SERVER["REMOTE_USER"]))
	{
		$login=$_SERVER["REMOTE_USER"];
	}

	return $login;
}
