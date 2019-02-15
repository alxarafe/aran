<?php
/* Copyright (C) 2019       Alxarafe            <info@alxarafe.com>
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
namespace Alixar\Helpers;

/**
 * Provides secure and flexible support to the GET and POST request methods.
 *
 * @package Alxarafe\Helpers
 */
class Request
{

    // Filters
    const NO_CHECK = 0;             // 'none'=no check (only for param that should have very rich content)
    const NUMERIC = 1;              // 'int'=check it's numeric (integer or float)
    const NUMBER_COMMA = 2;         // 'intcomma'=check it's integer+comma ('1,2,3,4...')
    const ALPHA = 3;                // 'alpha'=check it's text and sign
    const LETTERS_ONLY = 4;         // 'aZ'=check it's a-z only
    const LETTERS_AND_NUMBERS = 5;  // 'aZ09'=check it's simple alpha string (recommended for keys)
    const AN_ARRAY = 6;             // 'array'=check it's array
    const SANITIZE = 7;             // 'san_alpha' = Use filter_var with FILTER_SANITIZE_STRING (do not use this for free text string)
    const NO_HTML = 8;              // 'nohtml', 'alphanohtml' = check there is no html content
    const ALPHA_NO_HTML = 9;              // 'nohtml', 'alphanohtml' = check there is no html content
    const CUSTOM = 10;               // 'custom' = custom filter specify $filter and $options)

    public static function get(string $variable, array $methods = [INPUT_GET, INPUT_POST], int $filter = self::NO_CHECK): string
    {
        $result = null;
        foreach ($methods as $method) {
            $result = filter_input($method, $variable);
            if (isset($result)) {
                break;
            }
        }

        if (!isset($result)) {
            return '';
        }

        switch ($filter) {
            case self::NO_CHECK : // 'none'=no check (only for param that should have very rich content)
                break;
            case self::NUMERIC : // 'int'=check it's numeric (integer or float)
                // Check param is a numeric value (integer but also float or hexadecimal)
                if (!is_numeric($result)) {
                    $result = '';
                }
                break;
            case self::NUMBER_COMMA: // 'intcomma'=check it's integer+comma ('1,2,3,4...')
                break;
            case self::ALPHA :// 'alpha'=check it's text and sign
                if (!is_array($result)) {
                    $result = trim($result);
                    // '"' is dangerous because param in url can close the href= or src= and add javascript functions.
                    // '../' is dangerous because it allows dir transversals
                    if (preg_match('/"/', $result)) {
                        $result = '';
                    } else {
                        if (preg_match('/\.\.\//', $result)) {
                            $result = '';
                        }
                    }
                }
                break;
            case self::LETTERS_ONLY:// 'aZ'=check it's a-z only
                if (!is_array($result)) {
                    $out = trim($result);
                    if (preg_match('/[^a-z]+/i', $result))
                        $result = '';
                }
                break;
            case self::LETTERS_AND_NUMBERS:// 'aZ09'=check it's simple alpha string (recommended for keys)
                if (!is_array($result)) {
                    $result = trim($result);
                    if (preg_match('/[^a-z0-9_\-\.]+/i', $result))
                        $result = '';
                }
                break;
            case self::AN_ARRAY :// 'array'=check it's array
                break;
            case self::SANITIZE :// 'san_alpha' = Use filter_var with FILTER_SANITIZE_STRING (do not use this for free text string)
                break;
            case self::NO_HTML :// 'nohtml = check there is no html content
                $result = dol_string_nohtmltag($result, 0);
                break;
            case self::ALPHA_NO_HTML :// 'alphanohtml' = check there is no html content
                if (!is_array($result)) {
                    $result = trim($result);
                    // '"' is dangerous because param in url can close the href= or src= and add javascript functions.
                    // '../' is dangerous because it allows dir transversals
                    if (preg_match('/"/', $result)) {
                        $result = '';
                    } else {
                        if (preg_match('/\.\.\//', $result)) {
                            $result = '';
                        }
                    }
                    $result = dol_string_nohtmltag($result);
                }
                break;
            case self::CUSTOM :// 'custom' = custom filter specify $filter and $options)
                break;
        }

        return $result;
    }

    public static function getAlpha(string $variable, array $methods = [INPUT_GET, INPUT_POST]): string
    {
        return self::get($variable, $methods, self::ALPHA);
    }

    public static function getAlphaNoHtml(string $variable, array $methods = [INPUT_GET, INPUT_POST]): string
    {
        return self::get($variable, $methods, self::ALPHA_NO_HTML);
    }

    public static function getAz(string $variable, array $methods = [INPUT_GET, INPUT_POST]): string
    {
        return self::get($variable, $methods, self::LETTERS_ONLY);
    }

    public static function getAz09(string $variable, array $methods = [INPUT_GET, INPUT_POST]): string
    {
        return self::get($variable, $methods, self::LETTERS_AND_NUMBERS);
    }

    public static function getNumber(string $variable, array $methods = [INPUT_GET, INPUT_POST]): string
    {
        return self::get($variable, $methods, self::NUMERIC);
    }

    /**
     *  Return value of a param into GET or POST supervariable.
     *  Use the property $user->default_values[path]['creatform'] and/or $user->default_values[path]['filters'] and/or $user->default_values[path]['sortorder']
     *  Note: The property $user->default_values is loaded by main.php when loading the user.
     *
     *  @param  string  $paramname   Name of parameter to found
     *  @param  string  $check	     Type of check
     *                               ''=no check (deprecated)
     *                               'none'=no check (only for param that should have very rich content)
     *                               'int'=check it's numeric (integer or float)
     *                               'intcomma'=check it's integer+comma ('1,2,3,4...')
     *                               'alpha'=check it's text and sign
     *                               'aZ'=check it's a-z only
     *                               'aZ09'=check it's simple alpha string (recommended for keys)
     *                               'array'=check it's array
     *                               'san_alpha'=Use filter_var with FILTER_SANITIZE_STRING (do not use this for free text string)
     *                               'nohtml', 'alphanohtml'=check there is no html content
     *                               'custom'= custom filter specify $filter and $options)
     *  @param	int		$method	     Type of method (0 = get then post, 1 = only get, 2 = only post, 3 = post then get, 4 = post then get then cookie)
     *  @param  int     $filter      Filter to apply when $check is set to 'custom'. (See http://php.net/manual/en/filter.filters.php for détails)
     *  @param  mixed   $options     Options to pass to filter_var when $check is set to 'custom'
     *  @param	string	$noreplace	 Force disable of replacement of __xxx__ strings.
     *  @return string|string[]      Value found (string or array), or '' if check fails
     */
    function GETPOST($paramname, $check = 'none', $method = 0, $filter = null, $options = null, $noreplace = 0)
    {
        global $mysoc, $user, $conf;

        Debug::addMessage('Deprecated', 'Using GETPOST of functions.lib.php instead of Request library');

        if (empty($paramname))
            return 'BadFirstParameterForGETPOST';
        if (empty($check)) {
            dol_syslog("Deprecated use of GETPOST, called with 1st param = " . $paramname . " and 2nd param is '', when calling page " . $_SERVER["PHP_SELF"], LOG_WARNING);
// Enable this line to know who call the GETPOST with '' $check parameter.
//var_dump(debug_backtrace()[0]);
        }

        if (empty($method))
            $out = isset($_GET[$paramname]) ? $_GET[$paramname] : (isset($_POST[$paramname]) ? $_POST[$paramname] : '');
        elseif ($method == 1)
            $out = isset($_GET[$paramname]) ? $_GET[$paramname] : '';
        elseif ($method == 2)
            $out = isset($_POST[$paramname]) ? $_POST[$paramname] : '';
        elseif ($method == 3)
            $out = isset($_POST[$paramname]) ? $_POST[$paramname] : (isset($_GET[$paramname]) ? $_GET[$paramname] : '');
        elseif ($method == 4)
            $out = isset($_POST[$paramname]) ? $_POST[$paramname] : (isset($_GET[$paramname]) ? $_GET[$paramname] : (isset($_COOKIE[$paramname]) ? $_COOKIE[$paramname] : ''));
        else
            return 'BadThirdParameterForGETPOST';

        if (empty($method) || $method == 3 || $method == 4) {
            $relativepathstring = $_SERVER["PHP_SELF"];
// Clean $relativepathstring
            if (constant('DOL_URL_ROOT'))
                $relativepathstring = preg_replace('/^' . preg_quote(constant('DOL_URL_ROOT'), '/') . '/', '', $relativepathstring);
            $relativepathstring = preg_replace('/^\//', '', $relativepathstring);
            $relativepathstring = preg_replace('/^custom\//', '', $relativepathstring);
//var_dump($relativepathstring);
//var_dump($user->default_values);
// Code for search criteria persistence.
// Retrieve values if restore_lastsearch_values
            if (!empty($_GET['restore_lastsearch_values'])) {        // Use $_GET here and not GETPOST
                if (!empty($_SESSION['lastsearch_values_' . $relativepathstring])) { // If there is saved values
                    $tmp = json_decode($_SESSION['lastsearch_values_' . $relativepathstring], true);
                    if (is_array($tmp)) {
                        foreach ($tmp as $key => $val) {
                            if ($key == $paramname) { // We are on the requested parameter
                                $out = $val;
                                break;
                            }
                        }
                    }
                }
// If there is saved contextpage, page or limit
                if ($paramname == 'contextpage' && !empty($_SESSION['lastsearch_contextpage_' . $relativepathstring])) {
                    $out = $_SESSION['lastsearch_contextpage_' . $relativepathstring];
                } elseif ($paramname == 'page' && !empty($_SESSION['lastsearch_page_' . $relativepathstring])) {
                    $out = $_SESSION['lastsearch_page_' . $relativepathstring];
                } elseif ($paramname == 'limit' && !empty($_SESSION['lastsearch_limit_' . $relativepathstring])) {
                    $out = $_SESSION['lastsearch_limit_' . $relativepathstring];
                }
            }
// Else, retreive default values if we are not doing a sort
            elseif (!isset($_GET['sortfield'])) { // If we did a click on a field to sort, we do no apply default values. Same if option MAIN_ENABLE_DEFAULT_VALUES is not set
                if (!empty($_GET['action']) && $_GET['action'] == 'create' && !isset($_GET[$paramname]) && !isset($_POST[$paramname])) {
// Search default value from $object->field
                    global $object;
                    if (is_object($object) && isset($object->fields[$paramname]['default'])) {
                        $out = $object->fields[$paramname]['default'];
                    }
                }
                if (!empty($conf->global->MAIN_ENABLE_DEFAULT_VALUES)) {
                    if (!empty($_GET['action']) && $_GET['action'] == 'create' && !isset($_GET[$paramname]) && !isset($_POST[$paramname])) {
// Now search in setup to overwrite default values
                        if (!empty($user->default_values)) {  // $user->default_values defined from menu 'Setup - Default values'
                            if (isset($user->default_values[$relativepathstring]['createform'])) {
                                foreach ($user->default_values[$relativepathstring]['createform'] as $defkey => $defval) {
                                    $qualified = 0;
                                    if ($defkey != '_noquery_') {
                                        $tmpqueryarraytohave = explode('&', $defkey);
                                        $tmpqueryarraywehave = explode('&', dol_string_nohtmltag($_SERVER['QUERY_STRING']));
                                        $foundintru = 0;
                                        foreach ($tmpqueryarraytohave as $tmpquerytohave) {
                                            if (!in_array($tmpquerytohave, $tmpqueryarraywehave))
                                                $foundintru = 1;
                                        }
                                        if (!$foundintru)
                                            $qualified = 1;
//var_dump($defkey.'-'.$qualified);
                                    } else
                                        $qualified = 1;

                                    if ($qualified) {
//var_dump($user->default_values[$relativepathstring][$defkey]['createform']);
                                        if (isset($user->default_values[$relativepathstring]['createform'][$defkey][$paramname])) {
                                            $out = $user->default_values[$relativepathstring]['createform'][$defkey][$paramname];
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                    }
// Management of default search_filters and sort order
//elseif (preg_match('/list.php$/', $_SERVER["PHP_SELF"]) && ! empty($paramname) && ! isset($_GET[$paramname]) && ! isset($_POST[$paramname]))
                    elseif (!empty($paramname) && !isset($_GET[$paramname]) && !isset($_POST[$paramname])) {
                        if (!empty($user->default_values)) {  // $user->default_values defined from menu 'Setup - Default values'
//var_dump($user->default_values[$relativepathstring]);
                            if ($paramname == 'sortfield' || $paramname == 'sortorder') {   // Sorted on which fields ? ASC or DESC ?
                                if (isset($user->default_values[$relativepathstring]['sortorder'])) { // Even if paramname is sortfield, data are stored into ['sortorder...']
                                    foreach ($user->default_values[$relativepathstring]['sortorder'] as $defkey => $defval) {
                                        $qualified = 0;
                                        if ($defkey != '_noquery_') {
                                            $tmpqueryarraytohave = explode('&', $defkey);
                                            $tmpqueryarraywehave = explode('&', dol_string_nohtmltag($_SERVER['QUERY_STRING']));
                                            $foundintru = 0;
                                            foreach ($tmpqueryarraytohave as $tmpquerytohave) {
                                                if (!in_array($tmpquerytohave, $tmpqueryarraywehave))
                                                    $foundintru = 1;
                                            }
                                            if (!$foundintru)
                                                $qualified = 1;
//var_dump($defkey.'-'.$qualified);
                                        } else
                                            $qualified = 1;

                                        if ($qualified) {
                                            $forbidden_chars_to_replace = array(" ", "'", "/", "\\", ":", "*", "?", "\"", "<", ">", "|", "[", "]", ";", "=");  // we accept _, -, . and ,
                                            foreach ($user->default_values[$relativepathstring]['sortorder'][$defkey] as $key => $val) {
                                                if ($out)
                                                    $out .= ', ';
                                                if ($paramname == 'sortfield') {
                                                    $out .= dol_string_nospecial($key, '', $forbidden_chars_to_replace);
                                                }
                                                if ($paramname == 'sortorder') {
                                                    $out .= dol_string_nospecial($val, '', $forbidden_chars_to_replace);
                                                }
                                            }
//break;	// No break for sortfield and sortorder so we can cumulate fields (is it realy usefull ?)
                                        }
                                    }
                                }
                            } elseif (isset($user->default_values[$relativepathstring]['filters'])) {
                                foreach ($user->default_values[$relativepathstring]['filters'] as $defkey => $defval) { // $defkey is a querystring like 'a=b&c=d', $defval is key of user
                                    $qualified = 0;
                                    if ($defkey != '_noquery_') {
                                        $tmpqueryarraytohave = explode('&', $defkey);
                                        $tmpqueryarraywehave = explode('&', dol_string_nohtmltag($_SERVER['QUERY_STRING']));
                                        $foundintru = 0;
                                        foreach ($tmpqueryarraytohave as $tmpquerytohave) {
                                            if (!in_array($tmpquerytohave, $tmpqueryarraywehave))
                                                $foundintru = 1;
                                        }
                                        if (!$foundintru)
                                            $qualified = 1;
//var_dump($defkey.'-'.$qualified);
                                    } else
                                        $qualified = 1;

                                    if ($qualified) {
                                        if (isset($_POST['sall']) || isset($_POST['search_all']) || isset($_GET['sall']) || isset($_GET['search_all'])) {
// We made a search from quick search menu, do we still use default filter ?
                                            if (empty($conf->global->MAIN_DISABLE_DEFAULT_FILTER_FOR_QUICK_SEARCH)) {
                                                $forbidden_chars_to_replace = array(" ", "'", "/", "\\", ":", "*", "?", "\"", "<", ">", "|", "[", "]", ";", "=");  // we accept _, -, . and ,
                                                $out = dol_string_nospecial($user->default_values[$relativepathstring]['filters'][$defkey][$paramname], '', $forbidden_chars_to_replace);
                                            }
                                        } else {
                                            $forbidden_chars_to_replace = array(" ", "'", "/", "\\", ":", "*", "?", "\"", "<", ">", "|", "[", "]", ";", "=");  // we accept _, -, . and ,
                                            $out = dol_string_nospecial($user->default_values[$relativepathstring]['filters'][$defkey][$paramname], '', $forbidden_chars_to_replace);
                                        }
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

// Substitution variables for GETPOST (used to get final url with variable parameters or final default value with variable paramaters)
// Example of variables: __DAY__, __MONTH__, __YEAR__, __MYCOMPANY_COUNTRY_ID__, __USER_ID__, ...
// We do this only if var is a GET. If it is a POST, may be we want to post the text with vars as the setup text.
        if (!is_array($out) && empty($_POST[$paramname]) && empty($noreplace)) {
            $maxloop = 20;
            $loopnb = 0;    // Protection against infinite loop
            while (preg_match('/__([A-Z0-9]+_?[A-Z0-9]+)__/i', $out, $reg) && ($loopnb < $maxloop)) {    // Detect '__ABCDEF__' as key 'ABCDEF' and '__ABC_DEF__' as key 'ABC_DEF'. Detection is also correct when 2 vars are side by side.
                $loopnb++;
                $newout = '';

                if ($reg[1] == 'DAY') {
                    $tmp = dol_getdate(dol_now(), true);
                    $newout = $tmp['mday'];
                } elseif ($reg[1] == 'MONTH') {
                    $tmp = dol_getdate(dol_now(), true);
                    $newout = $tmp['mon'];
                } elseif ($reg[1] == 'YEAR') {
                    $tmp = dol_getdate(dol_now(), true);
                    $newout = $tmp['year'];
                } elseif ($reg[1] == 'PREVIOUS_DAY') {
                    $tmp = dol_getdate(dol_now(), true);
                    $tmp2 = dol_get_prev_day($tmp['mday'], $tmp['mon'], $tmp['year']);
                    $newout = $tmp2['day'];
                } elseif ($reg[1] == 'PREVIOUS_MONTH') {
                    $tmp = dol_getdate(dol_now(), true);
                    $tmp2 = dol_get_prev_month($tmp['mon'], $tmp['year']);
                    $newout = $tmp2['month'];
                } elseif ($reg[1] == 'PREVIOUS_YEAR') {
                    $tmp = dol_getdate(dol_now(), true);
                    $newout = ($tmp['year'] - 1);
                } elseif ($reg[1] == 'NEXT_DAY') {
                    $tmp = dol_getdate(dol_now(), true);
                    $tmp2 = dol_get_next_day($tmp['mday'], $tmp['mon'], $tmp['year']);
                    $newout = $tmp2['day'];
                } elseif ($reg[1] == 'NEXT_MONTH') {
                    $tmp = dol_getdate(dol_now(), true);
                    $tmp2 = dol_get_next_month($tmp['mon'], $tmp['year']);
                    $newout = $tmp2['month'];
                } elseif ($reg[1] == 'NEXT_YEAR') {
                    $tmp = dol_getdate(dol_now(), true);
                    $newout = ($tmp['year'] + 1);
                } elseif ($reg[1] == 'MYCOMPANY_COUNTRY_ID' || $reg[1] == 'MYCOUNTRY_ID' || $reg[1] == 'MYCOUNTRYID') {
                    $newout = $mysoc->country_id;
                } elseif ($reg[1] == 'USER_ID' || $reg[1] == 'USERID') {
                    $newout = $user->id;
                } elseif ($reg[1] == 'USER_SUPERVISOR_ID' || $reg[1] == 'SUPERVISOR_ID' || $reg[1] == 'SUPERVISORID') {
                    $newout = $user->fk_user;
                } elseif ($reg[1] == 'ENTITY_ID' || $reg[1] == 'ENTITYID') {
                    $newout = $conf->entity;
                } else
                    $newout = '';     // Key not found, we replace with empty string
//var_dump('__'.$reg[1].'__ -> '.$newout);
                $out = preg_replace('/__' . preg_quote($reg[1], '/') . '__/', $newout, $out);
            }
        }

// Check is done after replacement
        switch ($check) {
            case 'none':
                break;
            case 'int':
                // Check param is a numeric value (integer but also float or hexadecimal)
                if (!is_numeric($out)) {
                    $out = '';
                }
                break;
            case 'intcomma':
                if (preg_match('/[^0-9,-]+/i', $out))
                    $out = '';
                break;
            case 'alpha':
                if (!is_array($out)) {
                    $out = trim($out);
// '"' is dangerous because param in url can close the href= or src= and add javascript functions.
// '../' is dangerous because it allows dir transversals
                    if (preg_match('/"/', $out))
                        $out = '';
                    else if (preg_match('/\.\.\//', $out))
                        $out = '';
                }
                break;
            case 'san_alpha':
                $out = filter_var($out, FILTER_SANITIZE_STRING);
                break;
            case 'aZ':
                if (!is_array($out)) {
                    $out = trim($out);
                    if (preg_match('/[^a-z]+/i', $out))
                        $out = '';
                }
                break;
            case 'aZ09':
                if (!is_array($out)) {
                    $out = trim($out);
                    if (preg_match('/[^a-z0-9_\-\.]+/i', $out))
                        $out = '';
                }
                break;
            case 'aZ09comma':  // great to sanitize sortfield or sortorder params that can be t.abc,t.def_gh
                if (!is_array($out)) {
                    $out = trim($out);
                    if (preg_match('/[^a-z0-9_\-\.,]+/i', $out))
                        $out = '';
                }
                break;
            case 'array':
                if (!is_array($out) || empty($out))
                    $out = array();
                break;
            case 'nohtml':  // Recommended for most scalar parameters
                $out = dol_string_nohtmltag($out, 0);
                break;
            case 'alphanohtml': // Recommended for search parameters
                if (!is_array($out)) {
                    $out = trim($out);
// '"' is dangerous because param in url can close the href= or src= and add javascript functions.
// '../' is dangerous because it allows dir transversals
                    if (preg_match('/"/', $out))
                        $out = '';
                    else if (preg_match('/\.\.\//', $out))
                        $out = '';
                    $out = dol_string_nohtmltag($out);
                }
                break;
            case 'custom':
                if (empty($filter))
                    return 'BadFourthParameterForGETPOST';
                $out = filter_var($out, $filter, $options);
                break;
        }

// Code for search criteria persistence.
// Save data into session if key start with 'search_' or is 'smonth', 'syear', 'month', 'year'
        if (empty($method) || $method == 3 || $method == 4) {
            if (preg_match('/^search_/', $paramname) || in_array($paramname, array('sortorder', 'sortfield'))) {
//var_dump($paramname.' - '.$out.' '.$user->default_values[$relativepathstring]['filters'][$paramname]);
// We save search key only if $out not empty that means:
// - posted value not empty, or
// - if posted value is empty and a default value exists that is not empty (it means we did a filter to an empty value when default was not).

                if ($out != '') {  // $out = '0' or 'abc', it is a search criteria to keep
                    $user->lastsearch_values_tmp[$relativepathstring][$paramname] = $out;
                }
            }
        }

        return $out;
    }
}
