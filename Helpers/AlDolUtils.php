<?php
/* Copyright (C) 2000-2007	Rodolphe Quiedeville			<rodolphe@quiedeville.org>
 * Copyright (C) 2003		Jean-Louis Bergamo			<jlb@j1b.org>
 * Copyright (C) 2004-2018	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2004		Sebastien Di Cintio			<sdicintio@ressource-toi.org>
 * Copyright (C) 2004		Benoit Mortier				<benoit.mortier@opensides.be>
 * Copyright (C) 2004		Christophe Combelles			<ccomb@free.fr>
 * Copyright (C) 2005-2017	Regis Houssin				<regis.houssin@inodbox.com>
 * Copyright (C) 2008		Raphael Bertrand (Resultic)	<raphael.bertrand@resultic.fr>
 * Copyright (C) 2010-2018	Juanjo Menent				<jmenent@2byte.es>
 * Copyright (C) 2013		Cédric Salvador				<csalvador@gpcsolutions.fr>
 * Copyright (C) 2013-2017	Alexandre Spangaro			<aspangaro@zendsi.com>
 * Copyright (C) 2014		Cédric GROSS					<c.gross@kreiz-it.fr>
 * Copyright (C) 2014-2015	Marcos García				<marcosgdf@gmail.com>
 * Copyright (C) 2015		Jean-François Ferry			<jfefe@aternatik.fr>
 * Copyright (C) 2018       Frédéric France             <frederic.france@netlogic.fr>
 * Copyright (C) 2018-2019  Alxarafe                    <info@alxarafe.com>
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
 * or see http://www.gnu.org/
 */
namespace Alixar\Helpers;

class AlDolUtils
{

    /**
     * 	Write log message into outputs. Possible outputs can be:
     * 	SYSLOG_HANDLERS = ["mod_syslog_file"]  		file name is then defined by SYSLOG_FILE
     * 	SYSLOG_HANDLERS = ["mod_syslog_syslog"]  	facility is then defined by SYSLOG_FACILITY
     *  Warning, syslog functions are bugged on Windows, generating memory protection faults. To solve
     *  this, use logging to files instead of syslog (see setup of module).
     *  Note: If constant 'SYSLOG_FILE_NO_ERROR' defined, we never output any error message when writing to log fails.
     *  Note: You can get log message into html sources by adding parameter &logtohtml=1 (constant MAIN_LOGTOHTML must be set)
     *  This static function works only if syslog module is enabled.
     * 	This must not use any call to other static function calling AlDolUtils::dol_syslog (avoid infinite loop).
     *
     * 	@param  string		$message				Line to log. ''=Show nothing
     *  @param  int			$level					Log level
     * 												On Windows LOG_ERR=4, LOG_WARNING=5, LOG_NOTICE=LOG_INFO=6, LOG_DEBUG=6 si define_syslog_variables ou PHP 5.3+, 7 si dolibarr
     * 												On Linux   LOG_ERR=3, LOG_WARNING=4, LOG_INFO=6, LOG_DEBUG=7
     *  @param	int			$ident					1=Increase ident of 1, -1=Decrease ident of 1
     *  @param	string		$suffixinfilename		When output is a file, append this suffix into default log filename.
     *  @param	string		$restricttologhandler	Output log only for this log handler
     *  @return	void
     */
    static function dol_syslog($message, $level = LOG_INFO, $ident = 0, $suffixinfilename = '', $restricttologhandler = '')
    {
        // global Globals::$conf, $user;
// If syslog module enabled
        if (empty(Globals::$conf->syslog->enabled))
            return;

        if ($ident < 0) {
            foreach (Globals::$conf->loghandlers as $loghandlerinstance) {
                $loghandlerinstance->setIdent($ident);
            }
        }

        if (!empty($message)) {
// Test log level
            $logLevels = array(LOG_EMERG, LOG_ALERT, LOG_CRIT, LOG_ERR, LOG_WARNING, LOG_NOTICE, LOG_INFO, LOG_DEBUG);
            if (!in_array($level, $logLevels, true)) {
                throw new Exception('Incorrect log level');
            }
            if ($level > Globals::$conf->global->SYSLOG_LEVEL)
                return;

            $message = preg_replace('/password=\'[^\']*\'/', 'password=\'hidden\'', $message); // protection to avoid to have value of password in log
// If adding log inside HTML page is required
            if (!empty($_REQUEST['logtohtml']) && (!empty(Globals::$conf->global->MAIN_ENABLE_LOG_TO_HTML) || !empty(Globals::$conf->global->MAIN_LOGTOHTML))) {   // MAIN_LOGTOHTML kept for backward compatibility
                Globals::$conf->logbuffer[] = AlDolUtils::dol_print_date(time(), "%Y-%m-%d %H:%M:%S") . " " . $message;
            }

//TODO: Remove this. MAIN_ENABLE_LOG_INLINE_HTML should be deprecated and use a log handler dedicated to HTML output
// If html log tag enabled and url parameter log defined, we show output log on HTML comments
            if (!empty(Globals::$conf->global->MAIN_ENABLE_LOG_INLINE_HTML) && !empty($_GET["log"])) {
                print "\n\n<!-- Log start\n";
                print $message . "\n";
                print "Log end -->\n";
            }

            $data = array(
                'message' => $message,
                'script' => (isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF'], '.php') : false),
                'level' => $level,
                'user' => ((is_object($user) && $user->id) ? $user->login : false),
                'ip' => false
            );

// This is when server run behind a reverse proxy
            if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
                $data['ip'] = $_SERVER['HTTP_X_FORWARDED_FOR'] . (empty($_SERVER["REMOTE_ADDR"]) ? '' : '->' . $_SERVER['REMOTE_ADDR']);
// This is when server run normally on a server
            else if (!empty($_SERVER["REMOTE_ADDR"]))
                $data['ip'] = $_SERVER['REMOTE_ADDR'];
// This is when PHP session is ran inside a web server but not inside a client request (example: init code of apache)
            else if (!empty($_SERVER['SERVER_ADDR']))
                $data['ip'] = $_SERVER['SERVER_ADDR'];
// This is when PHP session is ran outside a web server, like from Windows command line (Not always defined, but useful if OS defined it).
            else if (!empty($_SERVER['COMPUTERNAME']))
                $data['ip'] = $_SERVER['COMPUTERNAME'] . (empty($_SERVER['USERNAME']) ? '' : '@' . $_SERVER['USERNAME']);
// This is when PHP session is ran outside a web server, like from Linux command line (Not always defined, but usefull if OS defined it).
            else if (!empty($_SERVER['LOGNAME']))
                $data['ip'] = '???@' . $_SERVER['LOGNAME'];
// Loop on each log handler and send output
            foreach (Globals::$conf->loghandlers as $loghandlerinstance) {
                if ($restricttologhandler && $loghandlerinstance->code != $restricttologhandler)
                    continue;
                $loghandlerinstance->export($data, $suffixinfilename);
            }
            unset($data);
        }

        if ($ident > 0) {
            foreach (Globals::$conf->loghandlers as $loghandlerinstance) {
                $loghandlerinstance->setIdent($ident);
            }
        }
    }

    /**
     *      Return a string encoded into OS filesystem encoding. This static function is used to define
     * 	    value to pass to filesystem PHP functions.
     *
     *      @param	string	$str        String to encode (UTF-8)
     * 		@return	string				Encoded string (UTF-8, ISO-8859-1)
     */
    function dol_osencode($str)
    {
        // global Globals::$conf;

        $tmp = ini_get("unicode.filesystem_encoding");      // Disponible avec PHP 6.0
        if (empty($tmp) && !empty($_SERVER["WINDIR"]))
            $tmp = 'iso-8859-1'; // By default for windows
        if (empty($tmp))
            $tmp = 'utf-8';          // By default for other
        if (!empty(Globals::$conf->global->MAIN_FILESYSTEM_ENCODING))
            $tmp = Globals::$conf->global->MAIN_FILESYSTEM_ENCODING;

        if ($tmp == 'iso-8859-1')
            return utf8_decode($str);
        return $str;
    }
}
