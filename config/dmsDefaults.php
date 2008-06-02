<?php
/**
 * $Id$
 *
 * Defines KnowledgeTree application defaults.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008 KnowledgeTree Inc.
 * Portions copyright The Jam Warehouse Software (Pty) Limited
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): Guenter Roeck______________________________________
 *
 */

if (defined('DMS_DEFAULTS_INCLUDED'))
{
	return;
}

define('DMS_DEFAULTS_INCLUDED',1);
define('LATEST_WEBSERVICE_VERSION',2);


if (function_exists('apd_set_pprof_trace')) {
    apd_set_pprof_trace();
}

// Default settings differ, we need some of these, so force the matter.
// Can be overridden here if actually necessary.
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('magic_quotes_runtime', '0');
ini_set('arg_separator.output', '&');

$microtime_simple = explode(' ', microtime());

$_KT_starttime = (float) $microtime_simple[1] + (float) $microtime_simple[0];
unset($microtime_simple);

// If not defined, set KT_DIR based on my usual location in the tree
if (!defined('KT_DIR')) {
    $rootLoc = realpath(dirname(__FILE__) . '/..');
    if (substr(PHP_OS, 0, 3) == 'WIN') {
            $rootLoc = str_replace('\\','/',$rootLoc);
    }
    define('KT_DIR', $rootLoc);
}

if (!defined('KT_LIB_DIR')) {
    define('KT_LIB_DIR', KT_DIR . '/lib');
}

// If not defined, set KT_STACK_DIR based on my usual location in the tree
// TODO: This needs to use a config.ini entry if available
if (!defined('KT_STACK_DIR')) {
    $stackLoc = realpath(dirname(__FILE__) . '/../..');
    if (substr(PHP_OS, 0, 3) == 'WIN') {
            $stackLoc = str_replace('\\','/',$stackLoc);
    }
    define('KT_STACK_DIR', $stackLoc);
}


// PATH_SEPARATOR added in PHP 4.3.0
if (!defined('PATH_SEPARATOR')) {
    if (substr(PHP_OS, 0, 3) == 'WIN') {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}

require_once(KT_LIB_DIR . '/Log.inc');
require_once(KT_LIB_DIR . '/validation/customerror.php');



// {{{ prependPath()
function prependPath ($path) {
    
    $include_path = ini_get('include_path');
    ini_set('include_path', $path . PATH_SEPARATOR . $include_path);
}
// }}}
    
prependPath(KT_DIR . '/thirdparty/ZendFramework/library');
prependPath(KT_DIR . '/thirdparty/pear');
prependPath(KT_DIR . '/thirdparty/Smarty');
prependPath(KT_DIR . '/thirdparty/simpletest');
prependPath(KT_DIR . '/thirdparty/xmlrpc-2.2/lib');
prependPath(KT_DIR . '/ktapi');
prependPath(KT_DIR . '/search2');
require_once('PEAR.php');

// Give everyone access to legacy PHP functions
require_once(KT_LIB_DIR . '/util/legacy.inc');

// Give everyone access to KTUtil utility functions
require_once(KT_LIB_DIR . '/util/ktutil.inc');

require_once(KT_LIB_DIR . '/ktentity.inc');

require_once(KT_LIB_DIR . '/config/config.inc.php');
require_once(KT_DIR . '/search2/indexing/indexerCore.inc.php');   

// {{{ KTInit
class KTInit {
    
    // {{{ setupLogging()
    function setupLogging () {
        global $default;
        require_once(KT_LIB_DIR . '/Log.inc');
        $oKTConfig =& KTConfig::getSingleton();

        if(!defined('APP_NAME')) {
		    define('APP_NAME', $oKTConfig->get('ui/appName', 'KnowledgeTree'));
		}
        $logLevel = $default->logLevel;
        if (!is_numeric($logLevel)) {
            $logLevel = @constant($logLevel);
            if (is_null($logLevel)) {
                $logLevel = @constant('ERROR');
            }
        }
        $default->log = new KTLegacyLog($oKTConfig->get('urls/logDirectory'), $logLevel);
        $res = $default->log->initialiseLogFile();
        if (PEAR::isError($res)) {
            $this->handleInitError($res);
            // returns only in checkup
            return $res;
        }
        $default->queryLog = new KTLegacyLog($oKTConfig->get('urls/logDirectory'), $logLevel, 'query');
        $res = $default->queryLog->initialiseLogFile();
        if (PEAR::isError($res)) {
            $this->handleInitError($res);
            // returns only in checkup
            return $res;
        }
        $default->timerLog = new KTLegacyLog($oKTConfig->get('urls/logDirectory'), $logLevel, 'timer');
        $res = $default->timerLog->initialiseLogFile();
        if (PEAR::isError($res)) {
            $this->handleInitError($res);
            // returns only in checkup
            return $res;
        }

        require_once('Log.php');
        $default->phpErrorLog =& Log::factory('composite');

        if ($default->phpErrorLogFile) {
            $fileLog =& Log::factory('file', $oKTConfig->get('urls/logDirectory') . '/php_error_log', 'KT', array(), $logLevel);
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
     * setupI18n
     *
     */
    function setupI18n () {
        require_once(KT_LIB_DIR . '/i18n/i18nutil.inc.php');
        require_once('HTTP.php');
        global $default;
        $language = KTUtil::arrayGet($_COOKIE, 'kt_language');
        if ($language) {
            $default->defaultLanguage = $language;
        }
    }
    // }}}

    

    // {{{ cleanGlobals()
    function cleanGlobals () {
        /*
         * Borrowed from TikiWiki
         *
         * Copyright (c) 2002-2004, Luis Argerich, Garland Foster,
         * Eduardo Polidor, et. al.
         */
        if (ini_get('register_globals')) {
            $aGlobals = array($_ENV, $_GET, $_POST, $_COOKIE, $_SERVER);
            foreach ($aGlobals as $superglob) {
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
        $bPathInfoSupport = $oKTConfig->get('KnowledgeTree/pathInfoSupport');
        if ($bPathInfoSupport) {
            // KTS-21: Some environments (FastCGI only?) don't set PATH_INFO
            // correctly, but do set ORIG_PATH_INFO.
            $path_info = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
            $orig_path_info = KTUtil::arrayGet($_SERVER, 'ORIG_PATH_INFO');
            if (empty($path_info) && !empty($orig_path_info)) {
                $_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
                $_SERVER['PHP_SELF'] .= $_SERVER['PATH_INFO'];
            }
            $env_path_info = KTUtil::arrayGet($_SERVER, 'REDIRECT_kt_path_info');
            if (empty($path_info) && !empty($env_path_info)) {
                $_SERVER['PATH_INFO'] = $env_path_info;
                $_SERVER['PHP_SELF'] .= $_SERVER['PATH_INFO'];
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
            $_SERVER['PHP_SELF'] .= '?kt_path_info=' . $kt_path_info;
            $_SERVER['PATH_INFO'] = $kt_path_info;
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
            $oDispatcher =new KTErrorDispatcher($oError);
            $oDispatcher->dispatch();
        } else {
            print $oError->toString() . "\n";
        }
        exit(0);
    }
    // }}}

    static function detectMagicFile()
    {
    	$knownPaths = array(
    			'/usr/share/file/magic', // the old default
    			'/etc/httpd/conf/magic', // fedora's location
    			'/etc/magic' // worst case scenario. Noticed this is sometimes empty and containing a reference to somewher else
    		);

		foreach($knownPaths as $path)
		{
			if (file_exists($path))
			{
				return $path;
			}
		}
		return KT_DIR . '/config/magic';
    }


    static protected $handlerMapping = array(
    		E_WARNING=>PEAR_LOG_WARNING,
    		E_USER_WARNING=>PEAR_LOG_WARNING,
        	E_NOTICE=>PEAR_LOG_NOTICE,
        	E_USER_NOTICE=>PEAR_LOG_NOTICE,
			E_ERROR=>PEAR_LOG_ERR,
			E_USER_ERROR=>PEAR_LOG_ERR,
    );

    // {{{ handlePHPError()
    static function handlePHPError($code, $message, $file, $line) {
        global $default;

        if (array_key_exists($code, KTInit::$handlerMapping))
        {
			$priority = KTInit::$handlerMapping[$code];
        }
        else
        {
        	$priority = PEAR_LOG_INFO;
        }

        $msg = $message . ' in ' . $file . ' at line ' . $line;
        if ($priority == PEAR_LOG_ERR)
        {
        	$default->log->error($msg);
        }

        if (!empty($default->phpErrorLog)) {
            $default->phpErrorLog->log($msg, $priority);
        }
        return false;
    }

    // }}}

	function catchFatalErrors()
	{
		
		$CustomErrorPage = KTCustomErrorViewer::getCustomErrorRedirectPage();
		if($CustomErrorPage != '0')
		{
			ini_set('display_errors','On');
		    $phperror='><div id="phperror" style="display:none">';
			ini_set('error_prepend_string',$phperror);
		
			$sUrl = KTInit::guessRootUrl();
			global $default;
			$sRootUrl = ($default->sslEnabled ? 'https' : 'http') .'://'.$_SERVER['HTTP_HOST'].$sUrl;
			
			$CustomErrorPage = basename($CustomErrorPage);
			
			$phperror='</div>><form name="catcher" action="'.$sRootUrl.'/'.$CustomErrorPage.'" method="post" ><input type="hidden" name="fatal" value=""></form>
			<script> document.catcher.fatal.value = document.getElementById("phperror").innerHTML; document.catcher.submit();</script>';
			ini_set('error_append_string',$phperror);
		}
		
	}



    // {{{ guessRootUrl()
    function guessRootUrl() {
        $urlpath = $_SERVER['SCRIPT_NAME'];
        $bFound = false;
        $rootUrl = '';
        while ($urlpath) {
            if (file_exists(KT_DIR . '/' . $urlpath)) {
                $bFound = true;
                break;
            }
            $i = strpos($urlpath, '/');
            if ($i === false) {
                break;
            }
            if ($rootUrl)
            {
            	$rootUrl .= '/';
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
        return '';
    }
    // }}}

	// {{{ getDynamicConfigSettings
	//This function gets the intial config settings which can only be resolved by using php
	function getDynamicConfigSettings()
	{
		$oKTConfig =& KTConfig::getSingleton();

        $oKTConfig->setdefaultns('KnowledgeTree', 'fileSystemRoot', KT_DIR);
        $oKTConfig->setdefaultns('KnowledgeTree', 'serverName', KTUtil::arrayGet($_SERVER, 'HTTP_HOST', 'localhost'));
        $oKTConfig->setdefaultns('KnowledgeTree', 'sslEnabled', 0);
        if (array_key_exists('HTTPS', $_SERVER)) {
            if (strtolower($_SERVER['HTTPS']) === 'on') {
                $oKTConfig->setdefaultns('KnowledgeTree', 'sslEnabled', 1);
            }
        }
        $oKTConfig->setdefaultns('KnowledgeTree', 'rootUrl', $this->guessRootUrl());
        $oKTConfig->setdefaultns('KnowledgeTree', 'execSearchPath', $_SERVER['PATH']);
        $oKTConfig->setdefaultns('KnowledgeTree', 'magicDatabase', KTInit::detectMagicFile());
	}
	// }}}

    // {{{ initConfig
    function initConfig() {
        global $default;
        $use_cache = false;
        $store_cache = false;
        if (file_exists(KT_DIR .  '/config/cache-path')) {
            $store_cache = true;
            $user = KTLegacyLog::running_user();
            // handle vhosts.
            $truehost = KTUtil::arrayGet($_SERVER, 'HTTP_HOST', 'default');
            $trueport = KTUtil::arrayGet($_SERVER, 'SERVER_PORT', '80');
            $cache_file = trim(file_get_contents(KT_DIR .  '/config/cache-path')) . '/configcache' . $user . $truehost . $trueport;
            if (!KTUtil::isAbsolutePath($cache_file)) { $cache_file = sprintf('%s/%s', KT_DIR, $cache_file); }
            $config_file = trim(file_get_contents(KT_DIR .  '/config/config-path'));
            // Remove any double slashes
            $config_file = str_replace('//', '/', $config_file);
            $config_file = str_replace('\\\\', '\\', $config_file);
            if (!KTUtil::isAbsolutePath($config_file)) { $config_file = sprintf('%s/%s', KT_DIR, $config_file); }

            $exists = file_exists($cache_file);
            if ($exists) {
                $cachestat = stat($cache_file);
                $configstat = stat($config_file);
                $tval = 9;
                // print sprintf("is %d > %d\n", $cachestat[$tval], $configstat[$tval]);
                if ($cachestat[$tval] > $configstat[$tval]) {
                    $use_cache = true;
                }
            }
        }

/*
        if ($use_cache) {
            $oKTConfig =& KTConfig::getSingleton();
            $oKTConfig->loadCache($cache_file);

            foreach ($oKTConfig->flat as $k => $v) {
                $default->$k = $oKTConfig->get($k);
            }
        } else {
        	//fail safe will be put here
        	  
        	  
            if (PEAR::isError($res)) { return $res; }

            //$oKTConfig =& KTConfig::getSingleton();
            @touch($cache_file);
            if ($store_cache && is_writable($cache_file)) {
                $oKTConfig->createCache($cache_file);
            }


        }
*/
    }
    // }}}

    

    // {{{ initTesting
    function initTesting() {
        $oKTConfig =& KTConfig::getSingleton();
        $sConfigFile = trim(@file_get_contents(KT_DIR .  '/config/test-config-path'));
        if (empty($sConfigFile)) {
            $sConfigFile = 'config/test.ini';
        }
        if (!KTUtil::isAbsolutePath($sConfigFile)) {
            $sConfigFile = sprintf('%s/%s', KT_DIR, $sConfigFile);
        }
        if (!file_exists($sConfigFile)) {
            $this->handleInitError(PEAR::raiseError('Test infrastructure not configured'));
            exit(0);
        }
        $res = $oKTConfig->loadFile($sConfigFile);
        if (PEAR::isError($res)) {
            return $res;
        }
        $_SESSION['userID'] = 1;
    }
    // }}}
}
// }}}

//Creating all the config settings 
//====================================
$oKTConfig = KTConfig::getSingleton();

//Read in DB specific config settings
$res = $oKTConfig->readDBConfig();
//Set up DB connection
$dbSetup = $oKTConfig->setupDB();

$KTInit = new KTInit();

if(PEAR::isError($dbSetup))
{
	$KTInit->handleInitError($dbSetup);
}



$KTInit->getDynamicConfigSettings();
$KTInit->initConfig();
$KTInit->setupI18n();

//Create final flatns and $default arrays to finish config setup
$res = $oKTConfig->readConfig();

//====================================

define('KTLOG_CACHE',false);

if (isset($GLOBALS['kt_test'])) {
    $KTInit->initTesting();
}




if($oKTConfig->get('CustomErrorMessages/customerrormessages') == 'on')
{
	$KTInit->catchFatalErrors();
}

if (phpversion()<5){

	$sErrorPage = 'http://'.$_SERVER['HTTP_HOST'].'/'.'customerrorpage.php';

	session_start();

	$_SESSION['sErrorMessage'] = 'KnowledgeTree now requires that PHP version 5 is installed. PHP version 4 is no longer supported.';


	header('location:'. $sErrorPage ) ;

}
$KTInit->setupServerVariables();

// instantiate log
$loggingSupport = $KTInit->setupLogging();

// Send all PHP errors to a file (and maybe a window)
set_error_handler(array('KTInit', 'handlePHPError'));


$KTInit->setupRandomSeed();

$GLOBALS['KTRootUrl'] = $oKTConfig->get('KnowledgeTree/rootUrl');

require_once(KT_LIB_DIR . '/database/lookup.inc');

// table mapping entries
include('tableMappings.inc');

$default->systemVersion = trim(file_get_contents(KT_DIR . '/docs/VERSION.txt'));
$default->versionName = trim(file_get_contents(KT_DIR . '/docs/VERSION-NAME.txt'));

$KTInit->cleanGlobals();
$KTInit->cleanMagicQuotes();

// site map definition
require_once(KT_DIR . '/config/siteMap.inc');

require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/session/control.inc');

require_once(KT_LIB_DIR . '/plugins/pluginutil.inc.php');

if ($checkup !== true) {
    // Replace function later
    /* ** Get the page being loaded and load the plugins specific to the page ** */
    $sScriptName = $GLOBALS['_SERVER']['SCRIPT_NAME'];
    $sScript = basename($sScriptName);
    $pos = strpos($sScript, '.');
    $sType = substr($sScript, 0, $pos);

    KTPluginUtil::loadPlugins($sType);
}

if ($checkup !== true) {
    if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
        require_once(KT_DIR .  '/plugins/wintools/baobabkeyutil.inc.php');
        $name = BaobabKeyUtil::getName();
        if ($name) {
            $default->versionName = sprintf('%s %s', $default->versionName, $name);
        }
    }else{
        $default->versionName = $default->versionName.' '._kt('(Community Edition)');
    }
}
if (!extension_loaded('mbstring'))
{
	require_once(KT_LIB_DIR . '/lib/mbstring.inc.php');
}


require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
$GLOBALS['main'] =new KTPage();

?>
