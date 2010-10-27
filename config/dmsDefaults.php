<?php
/**
 * $Id$
 *
 * Defines KnowledgeTree application defaults.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright(C) 2008, 2009, 2010 KnowledgeTree Inc.
 *
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
 * Contributor(s): Guenter Roeck______________________________________
 *
 */
// The line below will switch on tracing for debugging & dev purposes
define('KTLIVE_TRACE_ENABLE', false);

// stuff in the new installer section database upgrade fails without this
global $default;
// ensure $default is a proper class
if (!($default instanceof stdClass)) {
    $default = new stdClass();
}

if (defined('DMS_DEFAULTS_INCLUDED')) {
	return;
}

define('DMS_DEFAULTS_INCLUDED', 1);
define('LATEST_WEBSERVICE_VERSION', 2);

if (!session_id())
	session_start();

if (function_exists('apd_set_pprof_trace')) {
	apd_set_pprof_trace();
}

// Default settings differ, we need some of these, so force the matter.
// Can be overridden here if actually necessary.
error_reporting(E_ALL & ~ E_NOTICE);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('magic_quotes_runtime', '0');
ini_set('arg_separator.output', '&');

$microtime_simple = explode(' ', microtime());

$_KT_starttime = (float)$microtime_simple[1] + (float)$microtime_simple[0];
unset($microtime_simple);

// If not defined, set KT_DIR based on my usual location in the tree
if (!defined('KT_DIR')) {
	$rootLoc = realpath(dirname(__FILE__) . '/..');
	if (substr(PHP_OS, 0, 3) == 'WIN') {
		$rootLoc = str_replace('\\', '/', $rootLoc);
	}
	define('KT_DIR', $rootLoc);
}

if (!defined('KT_PLUGIN_DIR'))
	define('KT_PLUGIN_DIR', KT_DIR . '/plugins');

if (!defined('KT_LIB_DIR')) {
	define('KT_LIB_DIR', KT_DIR . '/lib');
}

// If not defined, set KT_STACK_DIR based on my usual location in the tree
// TODO: This needs to use a config.ini entry if available
if (!defined('KT_STACK_DIR')) {
	$stackLoc = realpath(dirname(__FILE__) . '/../..');
	if (substr(PHP_OS, 0, 3) == 'WIN') {
		$stackLoc = str_replace('\\', '/', $stackLoc);
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

require_once(KT_LIB_DIR . '/validation/customerror.php');

// {{{ prependPath()
function prependPath($path) {

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

require_once(KT_DIR . '/thirdparty/pear/PEAR.php');
// Give everyone access to legacy PHP functions
require_once(KT_LIB_DIR . '/util/legacy.inc');
// Give everyone access to KTUtil utility functions
require_once(KT_LIB_DIR . '/util/ktutil.inc');
require_once(KT_LIB_DIR . '/ktentity.inc');
require_once(KT_LIB_DIR . '/config/config.inc.php');

// {{{ KTInit
class KTInit {

    function configureLog($logDir, $logLevel, $userId, $dbName){
		define('KT_LOG4PHP_DIR', KT_DIR . '/thirdparty/apache-log4php/src/main/php' . DIRECTORY_SEPARATOR);
		define('LOG4PHP_CONFIGURATION', KT_DIR . '/config/ktlog.ini');
		define('LOG4PHP_DEFAULT_INIT_OVERRIDE', true);

		require_once(KT_LOG4PHP_DIR . 'LoggerManager.php');
		require_once(KT_LOG4PHP_DIR . 'LoggerPropertyConfigurator.php');

		$configurator = new LoggerPropertyConfigurator();
		$repository = LoggerManager::getLoggerRepository();
		$properties = @parse_ini_file(LOG4PHP_CONFIGURATION);
		$properties['log4php.appender.default'] = 'LoggerAppenderDailyFile';
		$properties['log4php.appender.default.layout'] = 'LoggerPatternLayout';
		$properties['log4php.appender.default.layout.conversionPattern'] = '%d{Y-m-d | H:i:s} | %p | %t | %r | %X{userid} | %X{db} | %c | %M | %m%n';
		$properties['log4php.appender.default.datePattern'] = 'Y-m-d';
		$properties['log4php.appender.default.file'] = $logDir . '/kt%s.' . KTUtil::running_user() . '.log.txt';

		// get the log level set in the configuration settings to override the level set in ktlog.ini
		// for the default / main logging. Additional logging can be configured through the ini file
		$properties['log4php.rootLogger'] = $logLevel . ', default';

		$configurator->doConfigureProperties($properties, $repository);

		LoggerMDC::put('userid', $userId);
		LoggerMDC::put('db', $dbName);
    }

	// {{{ setupLogging()
	function setupLogging() {
		global $default;
		$oKTConfig = & KTConfig::getSingleton();

		if (!defined('APP_NAME')) {
			define('APP_NAME', $oKTConfig->get('ui/appName', 'KnowledgeTree'));
		}

		$logDir = $oKTConfig->get('urls/logDirectory');
		$logLevel = $oKTConfig->get('KnowledgeTree/logLevel');
		$userId = isset($_SESSION['userID']) ? $_SESSION['userID'] : 'n/a';
		$dbName = $oKTConfig->get('db/dbName');

		$this->configureLog($logDir, $logLevel, $userId, $dbName);

		$default->log = LoggerManager::getLogger('default');
		$default->queryLog = LoggerManager::getLogger('sql');
		$default->timerLog = LoggerManager::getLogger('timer');
		$default->phpErrorLog = LoggerManager::getLogger('php');
	}
	// }}}


	/**
	 * Account Routing
	 *
	 * This method will switch between accounts based on the subdomain of the site if ktlive plugin is installed
	 * Tech Used:
	 * 		-ktlive/liveEnable.php
	 * 		-ktlive/helpers/liveAccountRouting.helper.php
	 * 		-ktlive/helpers/liveAccounts.helper.php
	 *
	 * @return void
	 */
	public function accountRouting() {
		if (file_exists(KT_PLUGIN_DIR . '/ktlive/liveEnable.php'))
		 {
		    define('ACCOUNT_ROUTING_ENABLED',true);
			require_once(KT_PLUGIN_DIR . '/ktlive/liveEnable.php');
			/**
			 * The code below demonstrates how to use accountOverride functionality.
			 * It allows you to simulate a different account by providing 'accountOverride' as a
			 * parameter in the $_GET request variable set
			 * To clear this override, this example makes use of clearAccountOverride as a parameter
			 * in the url.
			 */
			define('ACCOUNT_NAME', liveAccountRouting::getAccountName());
			define('KTLIVE_CALLBACK_PATH', '/plugins/ktlive/webservice/callback.php');
			define('KTLIVE_TRACE_PATH', KTLIVE_CALLBACK_PATH . '?action=trace');

		/**
		 * Uncomment below for development overrides to work.
		 *
		 */
		//liveAccountRouting::setOverrides();
		} else {
			define('ACCOUNT_ROUTING_ENABLED', false);
			define('ACCOUNT_NAME', '');
		}

	}

	public function accountRoutingLicenceCheck() {
		/* Check if account is licensed */
		if (ACCOUNT_ROUTING_ENABLED) {

//		    $oKTConfig = KTConfig::getSingleton();
//
//		    // Set up logging so that we can log the error.
//		    $logDir = $oKTConfig->get('urls/logDirectory', KT_DIR.'/var/log');
//		    $userId = isset($_SESSION['userID']) ? $_SESSION['userID'] : 'n/a';
//		    $this->configureLog($logDir, 'ERROR', $userId, ACCOUNT_NAME);
//
//		    $logger = LoggerManager::getLogger('default');
		    $logger = $GLOBALS['default']->log;


			if (!isset($_SESSION[LIVE_LICENSE_OVERRIDE])) {
				if (!liveAccounts::accountLicenced()) {
					// Check if account exists
					if (liveAccounts::accountExists()) {
						// Check if account is enabled
						if (!liveAccounts::accountEnabled()) {

						    if (liveAccounts::isTrialAccount()){

    						    $logger->error(ACCOUNT_NAME." License Check. Trial Account License expired, Exists but Not Enabled. ");
    							liveRenderError::errorTrialLicense($_SERVER, LIVE_ACCOUNT_DISABLED);

						    }else {

						        $logger->error(ACCOUNT_NAME." License Check. Account Not Licenced, Exists but Not Enabled. ");
    							liveRenderError::errorDisabled($_SERVER, LIVE_ACCOUNT_DISABLED);

						    }

						}else{

						    $logger->error(ACCOUNT_NAME." License Check. Account Not Licenced, Exists AND Enabled AND Not Expired in SimpleDB. ");
							liveRenderError::errorFail(NULL, LIVE_ACCOUNT_LICENCE);

						}
					} else {

					    $logger->error(ACCOUNT_NAME." License Check. Account Not Licenced, and does not exist. ");
						liveRenderError::errorNoAccount(NULL, LIVE_ACCOUNT_DISABLED);

					}
				}
			}
		}
	}

	/**
	 * setupI18n
	 *
	 */
	// {{{ setupI18n()
	function setupI18n() {
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
	function cleanGlobals() {
		/*
         * Borrowed from TikiWiki
         *
         * Copyright(c) 2002-2004, Luis Argerich, Garland Foster,
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
	function cleanMagicQuotesItem(&$var) {
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
	function cleanMagicQuotes() {
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
		$oKTConfig = & KTConfig::getSingleton();
		$bPathInfoSupport = $oKTConfig->get('KnowledgeTree/pathInfoSupport');
		if ($bPathInfoSupport) {
			// KTS-21: Some environments(FastCGI only?) don't set PATH_INFO
			// correctly, but do set ORIG_PATH_INFO.
			$path_info = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
			$orig_path_info = KTUtil::arrayGet($_SERVER, 'ORIG_PATH_INFO');
			if (empty($path_info) && !empty($orig_path_info)) {
				$_SERVER['PATH_INFO'] = strip_tags($_SERVER['ORIG_PATH_INFO']);
				$_SERVER['PHP_SELF'] .= $_SERVER['PATH_INFO'];
			}
			$env_path_info = KTUtil::arrayGet($_SERVER, 'REDIRECT_kt_path_info');
			if (empty($path_info) && !empty($env_path_info)) {
				$_SERVER['PATH_INFO'] = strip_tags($env_path_info);
				$_SERVER['PHP_SELF'] .= $_SERVER['PATH_INFO'];
			}

			// KTS-50: IIS(and probably most non-Apache web servers) don't
			// set REQUEST_URI.  Fake it.
			$request_uri = KTUtil::arrayGet($_SERVER, 'REQUEST_URI');
			if (empty($request_uri)) {
				$_SERVER['REQUEST_URI'] = strip_tags(KTUtil::addQueryString($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']));
			}
		} else {
			unset($_SERVER['PATH_INFO']);
		}

		$script_name = strip_tags(KTUtil::arrayGet($_SERVER, 'SCRIPT_NAME'));
		$php_self = strip_tags(KTUtil::arrayGet($_SERVER, 'PHP_SELF'));

		$_SERVER['SCRIPT_NAME'] = $script_name;
		$_SERVER['PHP_SELF'] = $php_self;

		$kt_path_info = strip_tags(KTUtil::arrayGet($_REQUEST, 'kt_path_info'));
		if (!empty($kt_path_info)) {
			$_SERVER['PHP_SELF'] .= '?kt_path_info=' . $kt_path_info;
			$_SERVER['PATH_INFO'] = $kt_path_info;
		}

		$sServerName = $oKTConfig->get('KnowledgeTree/serverName');
		$_SERVER['HTTP_HOST'] = $sServerName;
	}
	// }}}

	// {{{ setupRandomSeed()
	function setupRandomSeed() {
		mt_srand(hexdec(substr(md5(microtime()), - 8)) & 0x7fffffff);
	}
	// }}}

	// {{{ handleInitError()
	function handleInitError($oError) {
		global $checkup;
		$msg = $oError->toString();

		if ($checkup === true) {
			echo $msg;
			exit(0);
			//return;
		}

		if (KTUtil::arrayGet($_SERVER, 'REQUEST_METHOD')) {
			//            session_start();
			$_SESSION['sErrorMessage'] = $msg;

			$url = KTUtil::kt_url() . '/customerrorpage.php';
			// Redirect to custom error page
			header('Location: ' . $url . $qs);

		/* A lot of steps to display the error page ... are they really needed?
            require_once(KT_LIB_DIR . '/dispatcher.inc.php');
            $oDispatcher =new KTErrorDispatcher($oError);
            $oDispatcher->dispatch();
            */
		} else {
			print $msg . "\n";
		}
		exit(0);
	}
	// }}}

	static function detectMagicFile() {
		$knownPaths = array('/usr/share/file/magic', // the old default
'/etc/httpd/conf/magic', // fedora's location
'/etc/magic'); // worst case scenario. Noticed this is sometimes empty and containing a reference to somewher else


		foreach ($knownPaths as $path) {
			if (file_exists($path)) {
				return $path;
			}
		}
		return KT_DIR . '/config/magic';
	}

	protected static $handlerMapping = array(E_WARNING => 'warn', E_USER_WARNING => 'warn', E_NOTICE => 'info', E_USER_NOTICE => 'info', E_ERROR => 'error', E_USER_ERROR => 'error');

	// {{{ handlePHPError()
	static function handlePHPError($code, $message, $file, $line) {
		global $default;

		$priority = 'info';
		if (array_key_exists($code, KTInit::$handlerMapping)) {
			$priority = KTInit::$handlerMapping[$code];
		}

		if (empty($priority)) {
			$priority = 'info';
		}

		$msg = $message . ' in ' . $file . ' at line ' . $line;

		if (isset($default->phpErrorLog)) {
			$default->phpErrorLog->$priority($msg);
		}
	}

	// }}}

	function catchFatalErrors() {
		ini_set('display_errors', 'On');
		$phperror = '><div id="phperror" style="display:none">';
		ini_set('error_prepend_string', $phperror);

		$CustomErrorPage = KTUtil::kt_url() . '/customerrorpage.php';

		$phperror = '</div>><form name="catcher" action="' . $CustomErrorPage . '" method="post" ><input type="hidden" name="fatal" value=""></form>
		<script> document.catcher.fatal.value = document.getElementById("phperror").innerHTML; document.catcher.submit();</script>';
		ini_set('error_append_string', $phperror);
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
			if ($rootUrl) {
				$rootUrl .= '/';
			}
			$rootUrl .= substr($urlpath, 0, $i);
			$urlpath = substr($urlpath, $i + 1);
		}
		if ($bFound) {
			if ($rootUrl) {
				$rootUrl = '/' . $rootUrl;
			}

			// If the rootUrl contains KT_DIR then it is the full path and not relative to the apache document root
			// We return an empty string which will work for all stack installs but might break source installs.
			// However this situation should only crop up when running background scripts and can be avoided by setting
			// the rootUrl in the config settings.
			if (strpos($rootUrl, KT_DIR) !== false) {
				return '';
			}
			return $rootUrl;
		}
		return '';
	}
	// }}}

	// {{{ getDynamicConfigSettings
	//This function gets the intial config settings which can only be resolved by using php
	function getDynamicConfigSettings() {
		$oKTConfig = & KTConfig::getSingleton();

		// Override the config setting - KT_DIR is resolved on page load
		$oKTConfig->setdefaultns('KnowledgeTree', 'fileSystemRoot', KT_DIR);

		// Set ssl to enabled if using https - if the server variable is not set, allow the config setting to take precedence
		if (array_key_exists('HTTPS', $_SERVER)) {
			if (strtolower($_SERVER['HTTPS']) === 'on') {
				$oKTConfig->setdefaultns('KnowledgeTree', 'sslEnabled', 'true');
			}
		}

		$oKTConfig->setdefaultns('KnowledgeTree', 'serverName', $_SERVER['HTTP_HOST']);

		// Check for the config setting before overriding with the resolved setting
		$serverName = $oKTConfig->get('KnowledgeTree/serverName');
		$rootUrl = $oKTConfig->get('KnowledgeTree/rootUrl');
		$execSearchPath = $oKTConfig->get('KnowledgeTree/execSearchPath');
		$magicDatabase = $oKTConfig->get('KnowledgeTree/magicDatabase');

		// base server name
		if (empty($serverName) || $serverName == 'default') {
			$oKTConfig->setdefaultns('KnowledgeTree', 'serverName', KTUtil::getServerName());
		}

		// the sub directory or root url
		if (empty($rootUrl) || $rootUrl == 'default') {
			$oKTConfig->setdefaultns('KnowledgeTree', 'rootUrl', $this->guessRootUrl());
		}

		// path to find the executable binaries
		if (empty($execSearchPath) || $execSearchPath == 'default') {
			$oKTConfig->setdefaultns('KnowledgeTree', 'execSearchPath', $_SERVER['PATH']);
		}

		// path to magic database
		if (empty($magicDatabase) || $magicDatabase == 'default') {
			$oKTConfig->setdefaultns('KnowledgeTree', 'magicDatabase', KTInit::detectMagicFile());
		}
	}
	// }}}

	// {{{ initConfig
	function initConfig() {
		global $default;
		$oKTConfig = KTConfig::getSingleton();

		// Override the config setting - KT_DIR is resolved on page load
		$oKTConfig->setdefaultns('KnowledgeTree', 'fileSystemRoot', KT_DIR);

		$use_cache = false;
		$store_cache = true;

		if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) {
			// If the http_host server variable is not set then the serverName gets set to localhost
			// We don't want to store this setting so we set store_cache to false
			$store_cache = false;
		}

		if (ACCOUNT_ROUTING_ENABLED) {
			$use_cache = $oKTConfig->setMemCache();
		}

		// If the cache needs to be cleared for debugging purposes uncomment the following lines..
		/*$oKTConfig->clearCache();
		$use_cache = false;*/

		if ($use_cache) {
			$use_cache = $oKTConfig->loadCache();
		}

		if ($use_cache === false) {
			//Read in DB settings and config settings
			$oKTConfig->readDBConfig();
		}

		$dbSetup = $oKTConfig->setupDB();

		if (PEAR::isError($dbSetup)) {
			/* We need to setup the language handler to display this error correctly */
			$this->setupI18n();
            $this->showDBError($dbSetup);
		}

		// Read in the config settings from the database
		// Create the global $default array(NOTE this was actually created at the top of dmsDefaults, perhaps needs to move here?)
		if ($use_cache === false){
			$res = $oKTConfig->readConfig();
			// If the config can't be read then it is most likely caused by a DB connection error
			if(PEAR::isError($res)){
				$this->showDBError($res);
			}
		}

		// Get default server url settings
		$this->getDynamicConfigSettings();

		if ($use_cache === false && $store_cache){
			$oKTConfig->createCache();
		}
	}
	// }}}

	function showDBError($dbError)
	{
        if (ACCOUNT_ROUTING_ENABLED) {
            $oKTConfig = KTConfig::getSingleton();

            if(!isset($GLOBALS['default']->log)){
                // Set up the logging so that we can log the error.
                $logDir = $oKTConfig->get('urls/logDirectory', KT_DIR.'/var/log');
                $userId = isset($_SESSION['userID']) ? $_SESSION['userID'] : 'n/a';
                $this->configureLog($logDir, 'ERROR', $userId, ACCOUNT_NAME);

                $logger = LoggerManager::getLogger('default');
                $GLOBALS['default']->log = $logger;
            }else {
                $logger = $GLOBALS['default']->log;
            }

                // Check if account exists
            if (liveAccounts::accountExists()) {
                // Check if account is enabled
                if (!liveAccounts::accountEnabled()) {
                    $logger->error(ACCOUNT_NAME." DB Setup. DB CONNECT FAILURE and ACCOUNT DISABLED(".$dbError->getMessage().")");
                    liveRenderError::errorDisabled($_SERVER, LIVE_ACCOUNT_DISABLED);
                }else{
                    $logger->error(ACCOUNT_NAME." DB Setup. DB CONNECT FAILURE and ACCOUNT ENABLED(".$dbError->getMessage().")");
                    liveRenderError::errorFail($_SERVER, LIVE_ACCOUNT_DISABLED);
                }
            } else {
                $logger->error(ACCOUNT_NAME." DB Setup. DB CONNECT FAILURE and NO ACCOUNT(".$dbError->getMessage().")");
                liveRenderError::errorNoAccount($dbError, LIVE_ACCOUNT_DISABLED);
            }
        } else {
            $this->handleInitError($dbError);
        }
	}

	// {{{ initTesting
	function initTesting() {
		$oKTConfig = & KTConfig::getSingleton();
		$sConfigFile = file_exists(KT_DIR . '/config/test-config-path') ? trim(file_get_contents(KT_DIR . '/config/test-config-path')) : '';
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

$KTInit = new KTInit();
$KTInit->accountRouting();
$KTInit->initConfig();
$KTInit->setupI18n();

//====================================

define('KTLOG_CACHE', false);

if (isset($GLOBALS['kt_test'])) {
	$KTInit->initTesting();
}

$oKTConfig = KTConfig::getSingleton();

if ($oKTConfig->get('CustomErrorMessages/customerrormessages') == 'on') {
	$KTInit->catchFatalErrors();
}

$KTInit->setupServerVariables();

// instantiate log
$loggingSupport = $KTInit->setupLogging();
$oKTConfig->logErrors();

// Send all PHP errors to a file(and maybe a window)
set_error_handler(array('KTInit', 'handlePHPError'));

$KTInit->setupRandomSeed();

$GLOBALS['KTRootUrl'] = $oKTConfig->get('KnowledgeTree/rootUrl');

require_once(KT_LIB_DIR . '/database/lookup.inc');
// table mapping entries
include('tableMappings.inc');

$default->systemVersion = trim(file_get_contents(KT_DIR . '/docs/VERSION.txt'));
$default->versionName = trim(file_get_contents(KT_DIR . '/docs/VERSION-NAME.txt'));
$default->versionTier = 'community';

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

	$res = KTPluginUtil::loadPlugins($sType);

	if(PEAR::isError($res)){
	    // If the plugins aren't loaded, there was a DB error, possibly a DB connection error
	    $KTInit->showDBError($res);
	}
}

if ($checkup !== true) {
	if (KTPluginUtil::pluginIsActive('ktdms.wintools')) {
		$path = KTPluginUtil::getPluginPath('ktdms.wintools');
		require_once($path . 'baobabkeyutil.inc.php');
		$name = BaobabKeyUtil::getName();
		if ($name) {
			$default->versionName = sprintf('%s %s', $default->versionName, $name);
		}
	} else {
		$default->versionName = $default->versionName . ' ' . _kt('(Community Edition)');
	}
}
if (!extension_loaded('mbstring')) {
	require_once(KT_LIB_DIR . '/mbstring.inc.php');
}

require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
$GLOBALS['main'] = new KTPage();

/** KTLIVE Account Routing **/
define('KTLIVE_TRACE_LOG_FILE', $GLOBALS['default']->varDirectory . '/tmp/live_trace.log');
define('KTLIVE_CALLBACK_LOG_FILE', $GLOBALS['default']->varDirectory . '/tmp/live_callback.log');
$KTInit->accountRoutingLicenceCheck();
?>