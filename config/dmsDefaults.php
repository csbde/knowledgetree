<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

/**
 * $Id$
 *
 * Defines KnowledgeTree application defaults.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */ 

error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// If not defined, set KT_DIR based on my usual location in the tree
if (!defined('KT_DIR')) {
    define('KT_DIR', realpath(dirname(__FILE__) . '/..'));
}

if (!defined('KT_LIB_DIR')) {
    define('KT_LIB_DIR', KT_DIR . '/lib');
}

// PATH_SEPARATOR added in PHP 4.3.0
if (!defined('PATH_SEPARATOR')) {
    if (substr(PHP_OS, 0, 3) == 'WIN') {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}

/// {{{ KTInit
class KTInit {
    // {{{ prependPath()
    function prependPath ($path) {
        $include_path = ini_get('include_path');
        ini_set('include_path', $path . PATH_SEPARATOR . $include_path);
    }
    // }}}

    // {{{ setupLogging()
    function setupLogging () {
        global $default;
        require_once("$default->fileSystemRoot/lib/Log.inc");
        $default->log = new Log($default->fileSystemRoot . "/log", INFO);
        $default->timerLog = new Log($default->fileSystemRoot . "/log", INFO, "timer");
    }
    // }}}

    // {{{ setupI18n()
    /**
     * setupI
     *
     */
    function setupI18n () {
        global $default;
        if (in_array("gettext", get_loaded_extensions()) && function_exists('gettext') && function_exists('_')) {
            require_once("$default->fileSystemRoot/lib/i18n/languageFunctions.inc");	
            require_once("$default->fileSystemRoot/lib/i18n/accept-to-gettext.inc");	
            if ($default->useAcceptLanguageHeader) {
                $aInstalledLocales = getInstalledLocales();
                $sLocale=al2gt($aInstalledLocales, 'text/html');
                $default->defaultLanguage = $sLocale;
            }		
            putenv('LANG=' . $default->defaultLanguage); 
            setlocale(LC_ALL, $default->defaultLanguage);
            // Set the text domain
            $sDomain = 'knowledgeTree';
            bindtextdomain($sDomain, $default->fileSystemRoot . "/i18n"); 
            textdomain($sDomain);
        } else {
            $default->log->info("Gettext not installed, i18n disabled.");
            // define a dummy _ function so gettext is not -required-
            function _($sString) {
                return $sString;
            }
        }
    }
    // }}}
}
// }}}

$default->fileSystemRoot = KT_DIR;
$default->serverName = $_SERVER['HTTP_HOST'];

// include the environment settings
require_once("environment.php");


// table mapping entries
include("tableMappings.inc");

KTInit::prependPath($default->pear_path);

// instantiate log
KTInit::setupLogging();

KTInit::setupI18n();

// site map definition
include("siteMap.inc");

require_once(KT_DIR . '/phpmailer/class.phpmailer.php');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_DIR . '/presentation/Html.inc');
// browser settings
require_once(KT_DIR . '/phpSniff/phpSniff.class.php');
require_once('browsers.inc');
// import request variables and setup language
require_once(KT_LIB_DIR . '/dms.inc');
?>
