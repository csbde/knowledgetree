<?php
/**
 * $Id$
 *
 * Defines KnowledgeTree application defaults.
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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

    // {{{ setupDB()
    function setupDB () {
        global $default;

        require_once('DB.php');

        // DBCompat allows phplib API compatibility
        require_once(KT_LIB_DIR . '/database/dbcompat.inc');
        $default->db = new DBCompat;

        // DBUtil is the preferred database abstraction
        require_once(KT_LIB_DIR . '/database/dbutil.inc');

        // KTEntity is the database-backed base class
        require_once(KT_LIB_DIR . '/ktentity.inc');

        $oKTConfig =& KTConfig::getSingleton();

        $prefix = defined('USE_DB_ADMIN_USER')?'Admin':'';

        $dsn = array(
            'phptype'  => $oKTConfig->get('db/dbType'),
            'username' => $oKTConfig->get("db/db{$prefix}User"),
            'password' => $oKTConfig->get("db/db{$prefix}Pass"),
            'hostspec' => $oKTConfig->get('db/dbHost'),
            'database' => $oKTConfig->get('db/dbName'),
            'port' => $oKTConfig->get('db/dbPort'),
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

function catchFatalErrors($p_OnOff='On'){
	ini_set('display_errors','On');
    $phperror='><div id="phperror" style="display:none">';
	ini_set('error_prepend_string',$phperror);

	$sUrl = KTInit::guessRootUrl();
	global $default;
	$sRootUrl = ($default->sslEnabled ? 'https' : 'http') .'://'.$_SERVER['HTTP_HOST'].$sUrl;

	$phperror='</div>><form name="catcher" action="'.$sRootUrl.'/customerrorpage.php" method="post" ><input type="hidden" name="fatal" value=""></form>
	<script> document.catcher.fatal.value = document.getElementById("phperror").innerHTML; document.catcher.submit();</script>';
	ini_set('error_append_string',$phperror);
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

        if ($use_cache) {
            $oKTConfig =& KTConfig::getSingleton();
            $oKTConfig->loadCache($cache_file);

            foreach ($oKTConfig->flat as $k => $v) {
                $default->$k = $oKTConfig->get($k);
            }
        } else {
            $oKTConfig =& KTConfig::getSingleton();

			$oKTConfig->setdefaultns('ui', 'appName', 'KnowledgeTree');
            $oKTConfig->setdefaultns('KnowledgeTree', 'fileSystemRoot', KT_DIR);
            $oKTConfig->setdefaultns('KnowledgeTree', 'serverName', KTUtil::arrayGet($_SERVER, 'HTTP_HOST', 'localhost'));
            $oKTConfig->setdefaultns('KnowledgeTree', 'sslEnabled', false);
            if (array_key_exists('HTTPS', $_SERVER)) {
                if (strtolower($_SERVER['HTTPS']) === 'on') {
                    $oKTConfig->setdefaultns('KnowledgeTree', 'sslEnabled', true);
                }
            }
            $oKTConfig->setdefaultns('KnowledgeTree', 'useNewDashboard', true);
            $oKTConfig->setdefaultns('KnowledgeTree', 'rootUrl', $this->guessRootUrl());
            $oKTConfig->setdefaultns('KnowledgeTree', 'execSearchPath', $_SERVER['PATH']);
            $oKTConfig->setdefaultns('KnowledgeTree', 'pathInfoSupport', false);
            $oKTConfig->setdefaultns('KnowledgeTree', 'magicDatabase', KTInit::detectMagicFile());
			$oKTConfig->setdefaultns('KnowledgeTree', 'schedulerInterval', 30);			
			
            $oKTConfig->setdefaultns('dashboard', 'alwaysShowYCOD', true);

            $oKTConfig->setdefaultns('storage', 'manager', 'KTOnDiskHashedStorageManager');
            $oKTConfig->setdefaultns('config', 'useDatabaseConfiguration', false);

            $oKTConfig->setdefaultns('urls', 'varDirectory', '${fileSystemRoot}/var');
            $oKTConfig->setdefaultns('urls', 'logDirectory', '${varDirectory}/log');
            $oKTConfig->setdefaultns('urls', 'documentRoot', '${varDirectory}/Documents');
            $oKTConfig->setdefaultns('urls', 'uiDirectory', '${fileSystemRoot}/presentation/lookAndFeel/knowledgeTree');
            $oKTConfig->setdefaultns('urls', 'tmpDirectory', '${varDirectory}/tmp');
            $oKTConfig->setdefaultns('urls', 'graphicsUrl', '${rootUrl}/graphics');
            $oKTConfig->setdefaultns('urls', 'uiUrl', '${rootUrl}/presentation/lookAndFeel/knowledgeTree');
            $oKTConfig->setdefaultns('urls', 'stopwordsFile', '${fileSystemRoot}/config/stopwords.txt');

            $oKTConfig->setdefaultns('tweaks', 'browseToUnitFolder', false);
            $oKTConfig->setdefaultns('tweaks', 'genericMetaDataRequired', true);
            $oKTConfig->setdefaultns('tweaks', 'phpErrorLogFile', false);
            $oKTConfig->setdefaultns('tweaks', 'developmentWindowLog', false);
            $oKTConfig->setdefaultns('tweaks', 'noisyBulkOperations', false);
            
            $oKTConfig->setdefaultns('email', 'emailServer', 'none');
            $oKTConfig->setdefaultns('email', 'emailPort', '');
            $oKTConfig->setdefaultns('email', 'emailAuthentication', false);
            $oKTConfig->setdefaultns('email', 'emailUsername', 'username');
            $oKTConfig->setdefaultns('email', 'emailPassword', 'password');
            $oKTConfig->setdefaultns('email', 'emailFrom', 'kt@example.org');
            $oKTConfig->setdefaultns('email', 'emailFromName', 'KnowledgeTree Document Management System');
            $oKTConfig->setdefaultns('email', 'allowAttachment', false);
            $oKTConfig->setdefaultns('email', 'allowEmailAddresses', false);
            $oKTConfig->setdefaultns('email', 'sendAsSystem', false);
            $oKTConfig->setdefaultns('email', 'onlyOwnGroups', false);

            $oKTConfig->setdefaultns('user_prefs', 'passwordLength', 6);
            $oKTConfig->setdefaultns('user_prefs', 'restrictAdminPasswords', false);
            $oKTConfig->setdefaultns('user_prefs', 'restrictPreferences', false);

            $oKTConfig->setdefaultns('session', 'sessionTimeout', 1200);
            $oKTConfig->setdefaultns('session', 'allowAnonymousLogin', false);

			$oKTConfig->setdefaultns('ui', 'companyLogo', '${rootUrl}/resources/companylogo.png');
			$oKTConfig->setdefaultns('ui', 'companyLogoWidth', '313px');
			$oKTConfig->setdefaultns('ui', 'companyLogoTitle', 'ACME Corporation');
            $oKTConfig->setdefaultns('ui', 'ieGIF', true);
            $oKTConfig->setdefaultns('ui', 'alwaysShowAll', false);
            $oKTConfig->setdefaultns('ui', 'automaticRefresh', false);
            $oKTConfig->setdefaultns('ui', 'condensedAdminUI', false);
            $oKTConfig->setdefaultns('ui', 'fakeMimetype', false);
			$oKTConfig->setdefaultns('ui', 'dot', 'dot');
			$oKTConfig->setdefaultns('ui', 'metadata_sort', true);
			
			$oKTConfig->setdefaultns('i18n', 'useLike', false);

            $oKTConfig->setdefaultns(null, 'logLevel', 'INFO');
            $oKTConfig->setdefaultns('import', 'unzip', 'unzip');
            $oKTConfig->setdefaultns('export', 'zip', 'zip');
            $oKTConfig->setdefaultns('export', 'encoding', 'UTF-8');
            
            $oKTConfig->setdefaultns('externalBinary', 'xls2csv', 'xls2csv');
            $oKTConfig->setdefaultns('externalBinary', 'pdftotext', 'pdftotext');
            $oKTConfig->setdefaultns('externalBinary', 'catppt', 'catppt');
            $oKTConfig->setdefaultns('externalBinary', 'pstotext', 'pstotext');
            $oKTConfig->setdefaultns('externalBinary', 'catdoc', 'catdoc');
            $oKTConfig->setdefaultns('externalBinary', 'antiword', 'antiword');
            $oKTConfig->setdefaultns('externalBinary', 'python', 'python');
            $oKTConfig->setdefaultns('externalBinary', 'java', 'java');
            $oKTConfig->setdefaultns('externalBinary', 'php', 'php');
            $oKTConfig->setdefaultns('externalBinary', 'df', 'df');
            
            $oKTConfig->setdefaultns('cache', 'cacheDirectory', '${varDirectory}/cache');
            $oKTConfig->setdefaultns('cache', 'cacheEnabled', 'false');
            $oKTConfig->setdefaultns('cache', 'proxyCacheDirectory', '${varDirectory}/proxies');
            $oKTConfig->setdefaultns('cache', 'proxyCacheEnabled', 'true');
            $oKTConfig->setdefaultns('cache', 'cachePlugins', 'true');
            
            $oKTConfig->setdefaultns('KTWebDAVSettings', 'debug', 'off');
            $oKTConfig->setdefaultns('KTWebDAVSettings', 'safemode', 'on');
            
            $oKTConfig->setdefaultns('BaobabSettings', 'debug', 'off');
            $oKTConfig->setdefaultns('BaobabSettings', 'safemode', 'on');

            $oKTConfig->setdefaultns('search', 'searchBasePath', KT_DIR . '/search2');
            $oKTConfig->setdefaultns('search', 'fieldsPath', '${searchBasePath}/search/fields');
            $oKTConfig->setdefaultns('search', 'resultsDisplayFormat', 'searchengine');
            $oKTConfig->setdefaultns('search', 'resultsPerPage', 25);
            $oKTConfig->setdefaultns('search', 'dateFormat', 'Y-m-d');
            
            $oKTConfig->setdefaultns('browse', 'previewActivation', 'mouse-over');

            $oKTConfig->setdefaultns('indexer', 'coreClass', 'JavaXMLRPCLuceneIndexer');
            $oKTConfig->setdefaultns('indexer', 'batchDocuments', 20);
            $oKTConfig->setdefaultns('indexer', 'batchMigrateDocuments', 500);
            $oKTConfig->setdefaultns('indexer', 'indexingBasePath', '${searchBasePath}/indexing');
            $oKTConfig->setdefaultns('indexer', 'luceneDirectory', '${varDirectory}/indexes');
            $oKTConfig->setdefaultns('indexer', 'extractorPath', '${indexingBasePath}/extractors');
            $oKTConfig->setdefaultns('indexer', 'extractorHookPath', '${indexingBasePath}/extractorHooks');
			$oKTConfig->setdefaultns('indexer', 'javaLuceneURL', 'http://127.0.0.1:8875');

            $oKTConfig->setdefaultns('openoffice', 'host', '127.0.0.1');
            $oKTConfig->setdefaultns('openoffice', 'port', 8100);

            $oKTConfig->setdefaultns('webservice', 'uploadDirectory', '${varDirectory}/uploads');
            $oKTConfig->setdefaultns('webservice', 'downloadUrl', '${rootUrl}/ktwebservice/download.php');
            $oKTConfig->setdefaultns('webservice', 'uploadExpiry', '30');
            $oKTConfig->setdefaultns('webservice', 'downloadExpiry', '30');
            $oKTConfig->setdefaultns('webservice', 'randomKeyText', 'bkdfjhg23yskjdhf2iu');
            $oKTConfig->setdefaultns('webservice', 'validateSessionCount', false);
            $oKTConfig->setdefaultns('webservice', 'useDefaultDocumentTypeIfInvalid', true);
            $oKTConfig->setdefaultns('webservice', 'debug', false);

            $oKTConfig->setdefaultns('clientToolPolicies', 'explorerMetadataCapture', true);
            $oKTConfig->setdefaultns('clientToolPolicies', 'officeMetadataCapture', true);
            $oKTConfig->setdefaultns('clientToolPolicies', 'captureReasonsDelete', true);
            $oKTConfig->setdefaultns('clientToolPolicies', 'captureReasonsCheckin', true);
            $oKTConfig->setdefaultns('clientToolPolicies', 'captureReasonsCheckout', true);
            $oKTConfig->setdefaultns('clientToolPolicies', 'captureReasonsCancelCheckout', true);
            $oKTConfig->setdefaultns('clientToolPolicies', 'captureReasonsCopyInKT', true);
            $oKTConfig->setdefaultns('clientToolPolicies', 'captureReasonsMoveInKT', true);
            $oKTConfig->setdefaultns('clientToolPolicies', 'allowRememberPassword', true);

			$oKTConfig->setdefaultns('DiskUsage', 'warningThreshold', 10);
			$oKTConfig->setdefaultns('DiskUsage', 'urgentThreshold', 5);	            

            $res = $this->readConfig();
            if (PEAR::isError($res)) { return $res; }

            $oKTConfig =& KTConfig::getSingleton();
            @touch($cache_file);
            if ($store_cache && is_writable($cache_file)) {
                $oKTConfig->createCache($cache_file);
            }


        }
    }
    // }}}

    // {{{ readConfig
    function readConfig () {
        global $default;
        $oKTConfig =& KTConfig::getSingleton();
        $sConfigFile = trim(file_get_contents(KT_DIR .  '/config/config-path'));
        if (KTUtil::isAbsolutePath($sConfigFile)) {
            $res = $oKTConfig->loadFile($sConfigFile);
        } else {
            $res = $oKTConfig->loadFile(sprintf('%s/%s', KT_DIR, $sConfigFile));
        }

        if (PEAR::isError($res)) {
            $this->handleInitError($res);
            // returns only in checkup
            return $res;
        }

        foreach (array_keys($oKTConfig->flat) as $k) {
            $v = $oKTConfig->get($k);
            if ($v === 'default') {
                continue;
            }
            if ($v === 'false') {
                $v = false;

            }
            if ($v === 'true') {
                $v = true;
            }
            $default->$k = $v;
        }
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

$KTInit = new KTInit();
$KTInit->prependPath(KT_DIR . '/thirdparty/ZendFramework/library');
$KTInit->prependPath(KT_DIR . '/thirdparty/pear');
$KTInit->prependPath(KT_DIR . '/thirdparty/Smarty');
$KTInit->prependPath(KT_DIR . '/thirdparty/simpletest');
$KTInit->prependPath(KT_DIR . '/thirdparty/xmlrpc-2.2/lib');
$KTInit->prependPath(KT_DIR . '/ktapi');
$KTInit->prependPath(KT_DIR . '/search2');
require_once('PEAR.php');

// Give everyone access to legacy PHP functions
require_once(KT_LIB_DIR . '/util/legacy.inc');

// Give everyone access to KTUtil utility functions
require_once(KT_LIB_DIR . '/util/ktutil.inc');

require_once(KT_LIB_DIR . '/ktentity.inc');

//$KTInit->catchFatalErrors();

if (phpversion()<5){

	$sErrorPage = 'http://'.$_SERVER['HTTP_HOST'].'/'.'customerrorpage.php';

	session_start();

	$_SESSION['sErrorMessage'] = 'KnowledgeTree now requires that PHP version 5 is installed. PHP version 4 is no longer supported.';


	header('location:'. $sErrorPage ) ;

}

require_once(KT_LIB_DIR . '/config/config.inc.php');
require_once(KT_DIR . '/search2/indexing/indexerCore.inc.php');

$KTInit->initConfig();
$KTInit->setupI18n();

define('KTLOG_CACHE',false);

if (isset($GLOBALS['kt_test'])) {
    $KTInit->initTesting();
}

$oKTConfig =& KTConfig::getSingleton();
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
