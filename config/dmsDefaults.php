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

// Default settings differ, we need some of these, so force the matter.
// Can be overridden here if actually necessary.
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('magic_quotes_runtime', '0');

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
        $default->log = new Log($default->fileSystemRoot . "/log", $default->logLevel);
        $default->timerLog = new Log($default->fileSystemRoot . "/log", $default->logLevel, "timer");
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

    // {{{ setupDb()
    function setupDb () {
        global $default;

        require_once("DB.php");

        $dsn = array(
            'phptype'  => $default->dbType,
            'username' => $default->dbUser,
            'password' => $default->dbPass,
            'hostspec' => $default->dbHost,
            'database' => $default->dbName,
        );

        $options = array(
            'debug'       => 2,
            'portability' => DB_PORTABILITY_ERRORS,
            'seqname_format' => 'zseq_%s',
        );

        $default->_db = &DB::connect($dsn, $options);
        $default->_db->setFetchMode(DB_FETCHMODE_ASSOC);

        // DBCompat allows phplib API compatibility
        require_once(KT_LIB_DIR . '/database/dbcompat.inc');
        $default->db = new DBCompat;

        // DBUtil is the preferred database abstraction
        require_once(KT_LIB_DIR . '/database/dbutil.inc');
    }
    /// }}}

    // {{{ cleanGlobals()
    function cleanGlobals () {
        /* 
         * Borrowed from TikiWiki
         * 
         * Copyright (c) 2002-2004, Luis Argerich, Garland Foster,
         * Eduardo Polidor, et. al.
         */
        if (ini_get('register_globals')) {
            foreach (array($_ENV, $_GET, $_POST, $_COOKIE, $_SERVER) as $superglob) {
                foreach ($superglob as $key => $val) {
                    if (isset($GLOBALS[$key]) && $GLOBALS[$key] == $val) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }
    // }}}

    // {{{ cleanMagicQuotesItem()
    function cleanMagicQuotesItem (&$var) {
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                KTInit::cleanMagicQuotesItem($var[$key]);
            }
        } else {
            $var = stripslashes($var);
        }
    }
    // }}}

    // {{{ cleanMagicQuotes()
    function cleanMagicQuotes () {
        if (get_magic_quotes_gpc()) {
            KTInit::cleanMagicQuotesItem($_GET);
            KTInit::cleanMagicQuotesItem($_POST);
            KTInit::cleanMagicQuotesItem($_REQUEST);
            KTInit::cleanMagicQuotesItem($_COOKIE);
        }
    }
    // }}}
}
// }}}

// Application defaults
//
// Overriden in environment.php

$default->fileSystemRoot = KT_DIR;
$default->serverName = $_SERVER['HTTP_HOST'];

$default->execSearchPath = $_SERVER['PATH'];
$default->unzipCommand = "unzip";
$default->logLevel = INFO;

$default->useDatabaseConfiguration = false;

// include the environment settings
require_once("environment.php");

KTInit::prependPath(KT_DIR . '/pear');
KTInit::setupDB();

require_once("$default->fileSystemRoot/lib/authentication/$default->authenticationClass.inc");

// instantiate system settings class
require_once("$default->fileSystemRoot/lib/database/lookup.inc");
require_once("$default->fileSystemRoot/lib/System.inc");
$default->system = new System();

if ($default->useDatabaseConfiguration && $default->system->initialised()) {
    $aSettings = $default->system->aSettings;

    for ($i=0; $i<count($aSettings); $i++) {
        $default->$aSettings[$i] = $default->system->get($aSettings[$i]);
    }
}

// table mapping entries
include("tableMappings.inc");

// instantiate log
KTInit::setupLogging();

KTInit::setupI18n();

KTInit::cleanGlobals();
KTInit::cleanMagicQuotes();

// site map definition
include("siteMap.inc");

require_once(KT_DIR . '/phpmailer/class.phpmailer.php');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_DIR . '/presentation/Html.inc');
// browser settings
require_once(KT_DIR . '/phpSniff/phpSniff.class.php');
require_once('browsers.inc');

// Give everyone access to KTUtil utility functions
require_once(KT_LIB_DIR . '/util/ktutil.inc');

?>
