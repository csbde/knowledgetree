<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 foldmethod=marker: */

/**
 * $Id$
 *
 * Defines KnowledgeTree application defaults.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
ini_set('arg_separator.output', '&');

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

// {{{ KTInit
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
        require_once(KT_LIB_DIR . '/Log.inc');
        $oKTConfig =& KTConfig::getSingleton();
        $logLevel = $default->logLevel;
        if (!is_numeric($logLevel)) {
            $logLevel = @constant($logLevel);
            if (is_null($logLevel)) {
                $logLevel = @constant("ERROR");
            }
        }
        $default->log = new KTLegacyLog($oKTConfig->get('urls/logDirectory'), $logLevel);
        $res = $default->log->initialiseLogFile();
        if (PEAR::isError($res)) {
            $this->handleInitError($res);
            // returns only in checkup
            return $res;
        }
        $default->queryLog = new KTLegacyLog($oKTConfig->get('urls/logDirectory'), $logLevel, "query");
        $res = $default->queryLog->initialiseLogFile();
        if (PEAR::isError($res)) {
            $this->handleInitError($res);
            // returns only in checkup
            return $res;
        }
        $default->timerLog = new KTLegacyLog($oKTConfig->get('urls/logDirectory'), $logLevel, "timer");
        $res = $default->timerLog->initialiseLogFile();
        if (PEAR::isError($res)) {
            $this->handleInitError($res);
            // returns only in checkup
            return $res;
        }

        require_once("Log.php");
        $default->phpErrorLog =& Log::factory('composite');

        if ($default->phpErrorLogFile) {
            $fileLog =& Log::factory('file', $oKTConfig->get('urls/logDirectory') . '/php_error_log', 'KT');
            $default->phpErrorLog->addChild($fileLog);
        }

        if ($default->developmentWindowLog) {
            $windowLog =& Log::factory('win', 'LogWindow', 'BLAH');
            $default->phpErrorLog->addChild($windowLog);
        }
    }
    // }}}

    // {{{ setupI18n()
    /**
     * setupI
     *
     */
    function setupI18n () {
        require_once(KT_LIB_DIR . '/i18n/i18nutil.inc.php');
        require_once("HTTP.php");
        global $default;
        if (in_array("gettext", get_loaded_extensions()) && function_exists('gettext') && function_exists('_')) {
            if ($default->useAcceptLanguageHeader) {
                $aInstalledLocales = KTi18nUtil::getInstalledLocales();
                $sLocale = $aInstalledLocales[HTTP::negotiateLanguage($aInstalledLocales)];
                $default->defaultLanguage = $sLocale;
            }
            putenv('LANG=' . $default->defaultLanguage);
            putenv('LANGUAGE=' . $default->defaultLanguage);
            setlocale(LC_ALL, $default->defaultLanguage);
            // Set the text domain
            $sDomain = 'knowledgeTree';
            bindtextdomain($sDomain, $default->fileSystemRoot . "/i18n");
            textdomain($sDomain);
            return true;
        } else {
            return false;
            $default->log->info("Gettext not installed, i18n disabled.");
        }
    }
    // }}}

    // {{{ setupDB()
    function setupDB () {
        global $default;

        require_once("DB.php");

        // DBCompat allows phplib API compatibility
        require_once(KT_LIB_DIR . '/database/dbcompat.inc');
        $default->db = new DBCompat;

        // DBUtil is the preferred database abstraction
        require_once(KT_LIB_DIR . '/database/dbutil.inc');

        // KTEntity is the database-backed base class
        require_once(KT_LIB_DIR . '/ktentity.inc');

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
        if (PEAR::isError($default->_db)) {
            $this->handleInitError($default->_db);
            // returns only in checkup
            return $default->_db;
        }
        $default->_db->setFetchMode(DB_FETCHMODE_ASSOC);

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
                $this->cleanMagicQuotesItem($var[$key]);
            }
        } else {
            // XXX: Make it look pretty
            $var = stripslashes($var);
        }
    }
    // }}}

    // {{{ cleanMagicQuotes()
    function cleanMagicQuotes () {
        if (get_magic_quotes_gpc()) {
            $this->cleanMagicQuotesItem($_GET);
            $this->cleanMagicQuotesItem($_POST);
            $this->cleanMagicQuotesItem($_REQUEST);
            $this->cleanMagicQuotesItem($_COOKIE);
        }
    }
    // }}}

    // {{{ setupServerVariables
    function setupServerVariables() {
        $oKTConfig =& KTConfig::getSingleton();
        $bPathInfoSupport = $oKTConfig->get("KnowledgeTree/pathInfoSupport");
        if ($bPathInfoSupport) {
            // KTS-21: Some environments (FastCGI only?) don't set PATH_INFO
            // correctly, but do set ORIG_PATH_INFO.
            $path_info = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
            $orig_path_info = KTUtil::arrayGet($_SERVER, 'ORIG_PATH_INFO');
            if (empty($path_info) && !empty($orig_path_info)) {
                $_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
                $_SERVER["PHP_SELF"] .= $_SERVER['PATH_INFO'];
            }
            $env_path_info = KTUtil::arrayGet($_SERVER, 'REDIRECT_kt_path_info');
            if (empty($path_info) && !empty($env_path_info)) {
                $_SERVER['PATH_INFO'] = $env_path_info;
                $_SERVER["PHP_SELF"] .= $_SERVER['PATH_INFO'];
            }

            // KTS-50: IIS (and probably most non-Apache web servers) don't
            // set REQUEST_URI.  Fake it.
            $request_uri = KTUtil::arrayGet($_SERVER, 'REQUEST_URI');
            if (empty($request_uri)) {
                $_SERVER['REQUEST_URI'] = KTUtil::addQueryString($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']);
            }
        } else {
            unset($_SERVER['PATH_INFO']);
        }

        $script_name = KTUtil::arrayGet($_SERVER, 'SCRIPT_NAME');
        $php_self = KTUtil::arrayGet($_SERVER, 'PHP_SELF');

        $kt_path_info = KTUtil::arrayGet($_REQUEST, 'kt_path_info');
        if (!empty($kt_path_info)) {
            $_SERVER["PHP_SELF"] .= "?kt_path_info=" . $kt_path_info;
            $_SERVER["PATH_INFO"] = $kt_path_info;
        }

        $oConfig =& KTConfig::getSingleton();
        $sServerName = $oConfig->get('KnowledgeTree/serverName');
        $_SERVER['HTTP_HOST'] = $sServerName;
    }
    // }}}

    // {{{ setupRandomSeed()
    function setupRandomSeed () {
        mt_srand(hexdec(substr(md5(microtime()), -8)) & 0x7fffffff);
    }
    // }}}

    // {{{ handleInitError()
    function handleInitError($oError) {
        global $checkup;
        if ($checkup === true) {
            return;
        }
        if (KTUtil::arrayGet($_SERVER, 'REQUEST_METHOD')) {
            require_once(KT_LIB_DIR . '/dispatcher.inc.php');
            $oDispatcher =& new KTErrorDispatcher($oError);
            $oDispatcher->dispatch();
        } else {
            print $oError->toString() . "\n";
        }
        exit(0);
    }
    // }}}

    // {{{ handlePHPError()
    function handlePHPError($code, $message, $file, $line) {
        global $default;

        /* Map the PHP error to a Log priority. */
        switch ($code) {
        case E_WARNING:
        case E_USER_WARNING:
            $priority = PEAR_LOG_WARNING;
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $priority = PEAR_LOG_NOTICE;
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $priority = PEAR_LOG_ERR;
            break;
        default:
            $priotity = PEAR_LOG_INFO;
        }

        if (!empty($default->phpErrorLog)) {
            $default->phpErrorLog->log($message . ' in ' . $file . ' at line ' . $line, $priority);
        }
        return false;
    }
    // }}}


    // {{{ guessRootUrl()
    function guessRootUrl() {
        $urlpath = $_SERVER['SCRIPT_NAME'];
        $bFound = false;
        $rootUrl = "";
        while ($urlpath) {
            if (file_exists(KT_DIR . '/' . $urlpath)) {
                $bFound = true;
                break;
            }
            $i = strpos($urlpath, '/');
            if ($i === false) {
                break;
            }
            $rootUrl .= substr($urlpath, 0, $i);
            $urlpath = substr($urlpath, $i + 1);
        }
        if ($bFound) {
            if ($rootUrl) {
                $rootUrl = '/' . $rootUrl;
            }
            return $rootUrl;
        }
        return "";
    }
    // }}}
    // {{{ readConfig
    function readConfig () {
        global $default;
        $oKTConfig =& KTConfig::getSingleton();
        $sConfigFile = trim(file_get_contents(KT_DIR .  "/config/config-path"));
        if (KTUtil::isAbsolutePath($sConfigFile)) {
            $oKTConfig->loadFile($sConfigFile);
        } else {
            $oKTConfig->loadFile(sprintf("%s/%s", KT_DIR, $sConfigFile));
        }
        foreach (array_keys($oKTConfig->flat) as $k) {
            $v = $oKTConfig->get($k);
            if ($v === "default") {
                continue;
            }
            if ($v === "false") {
                $v = false;
            }
            if ($v === "true") {
                $v = true;
            }
            $default->$k = $v;
        }
    }
    // }}}
}
// }}}

$KTInit = new KTInit();

$KTInit->prependPath(KT_DIR . '/thirdparty/pear');
$KTInit->prependPath(KT_DIR . '/thirdparty/Smarty');
require_once('PEAR.php');

// Give everyone access to legacy PHP functions
require_once(KT_LIB_DIR . '/util/legacy.inc');

// Give everyone access to KTUtil utility functions
require_once(KT_LIB_DIR . '/util/ktutil.inc');

require_once(KT_LIB_DIR . "/config/config.inc.php");

$oKTConfig =& KTConfig::getSingleton();

$oKTConfig->setdefaultns("KnowledgeTree", "fileSystemRoot", KT_DIR);
$oKTConfig->setdefaultns("KnowledgeTree", "serverName", KTUtil::arrayGet($_SERVER, 'HTTP_HOST', 'localhost'));
$oKTConfig->setdefaultns("KnowledgeTree", "sslEnabled", false);
if (array_key_exists('HTTPS', $_SERVER)) {
    if (strtolower($_SERVER['HTTPS']) === 'on') {
        $oKTConfig->setdefaultns("KnowledgeTree", "sslEnabled", true);
    }
}
$oKTConfig->setdefaultns("KnowledgeTree", "rootUrl", $KTInit->guessRootUrl());
$oKTConfig->setdefaultns("KnowledgeTree", "execSearchPath", $_SERVER['PATH']);
$oKTConfig->setdefaultns("KnowledgeTree", "pathInfoSupport", false);
$oKTConfig->setdefaultns("storage", "manager", 'KTOnDiskPathStorageManager');
$oKTConfig->setdefaultns("config", "useDatabaseConfiguration", false);
$oKTConfig->setdefaultns("tweaks", "browseToRoot", false);
$oKTConfig->setdefaultns("tweaks", "genericMetaDataRequired", true);
$oKTConfig->setdefaultns("tweaks", "phpErrorLogFile", false);
$oKTConfig->setdefaultns("tweaks", "developmentWindowLog", false);

$oKTConfig->setdefaultns("user_prefs", "passwordLength", 6);
$oKTConfig->setdefaultns("user_prefs", "restrictAdminPasswords", false);

$oKTConfig->setdefaultns("ui", "ieGIF", true);
$oKTConfig->setdefaultns("ui", "alwaysShowAll", false);
$oKTConfig->setdefaultns("ui", "condensedAdminUI", false);

$oKTConfig->setdefaultns(null, "logLevel", 'INFO');
$oKTConfig->setdefaultns("import", "unzip", 'unzip');

$KTInit->readConfig();

$KTInit->setupServerVariables();

// instantiate log
$loggingSupport = $KTInit->setupLogging();

// Send all PHP errors to a file (and maybe a window)
set_error_handler(array('KTInit', 'handlePHPError'));

$dbSupport = $KTInit->setupDB();
$KTInit->setupRandomSeed();

$GLOBALS['KTRootUrl'] = $oKTConfig->get('KnowledgeTree/rootUrl');

require_once(KT_LIB_DIR . '/database/lookup.inc');

// table mapping entries
include("tableMappings.inc");

$i18nLoaded = $KTInit->setupI18n();
if ($i18nLoaded === false) {
    // define a dummy _ function so gettext is not -required-
    function _($sString) {
        return $sString;
    }
}

$default->systemVersion = trim(file_get_contents(KT_DIR . '/docs/VERSION.txt'));
$default->lastDatebaseVersion = '2.0.2';

$KTInit->cleanGlobals();
$KTInit->cleanMagicQuotes();

// site map definition
require_once(KT_DIR . "/config/siteMap.inc");

require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/session/control.inc');

require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

if ($checkup !== true) {
    KTPluginUtil::loadPlugins();
}

?>
