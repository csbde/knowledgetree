<?php

class KTInit {

    protected static $handlerMapping = array(
                                            E_WARNING => 'warn',
                                            E_USER_WARNING => 'warn',
                                            E_NOTICE => 'info',
                                            E_USER_NOTICE => 'info',
                                            E_ERROR => 'error',
                                            E_USER_ERROR => 'error'
    );

    public function setupLogging()
    {
        global $default;
        $KTConfig = KTConfig::getSingleton();

        if (!defined('APP_NAME')) {
            define('APP_NAME', $KTConfig->get('ui/appName', 'KnowledgeTree'));
        }

        $logDir = $KTConfig->get('urls/logDirectory');
        $logLevel = $KTConfig->get('KnowledgeTree/logLevel');
        $userId = isset($_SESSION['userID']) ? $_SESSION['userID'] : 'n/a';
        $dbName = $KTConfig->get('db/dbName');

        $this->configureLog($logDir, $logLevel, $userId, $dbName);

        $default->log = LoggerManager::getLogger('default');
        $default->queryLog = LoggerManager::getLogger('sql');
        $default->timerLog = LoggerManager::getLogger('timer');
        $default->phpErrorLog = LoggerManager::getLogger('php');
    }

    public function configureLog($logDir, $logLevel, $userId, $dbName)
    {
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

    public function setupI18n()
    {
        require_once(KT_LIB_DIR . '/i18n/i18nutil.inc.php');
        require_once('HTTP.php');
        global $default;
        $language = KTUtil::arrayGet($_COOKIE, 'KTLanguage');
        if ($language) {
            $default->defaultLanguage = $language;
        }
    }

    public function cleanGlobals()
    {
        /*
         * Borrowed from TikiWiki
         *
         * Copyright(c) 2002-2004, Luis Argerich, Garland Foster,
         * Eduardo Polidor, et. al.
         */
        if (ini_get('register_globals')) {
            $globals = array($_ENV, $_GET, $_POST, $_COOKIE, $_SERVER);
            foreach ($globals as $superglob) {
                foreach ($superglob as $key => $val) {
                    if (isset($GLOBALS[$key]) && $GLOBALS[$key] == $val) {
                        unset($GLOBALS[$key]);
                    }
                }
            }
        }
    }

    public function cleanMagicQuotes()
    {
        if (get_magic_quotes_gpc()) {
            $this->cleanMagicQuotesItem($_GET);
            $this->cleanMagicQuotesItem($_POST);
            $this->cleanMagicQuotesItem($_REQUEST);
            $this->cleanMagicQuotesItem($_COOKIE);
        }
    }

    public function cleanMagicQuotesItem(&$var)
    {
        if (is_array($var)) {
            foreach ($var as $key => $val) {
                $this->cleanMagicQuotesItem($var[$key]);
            }
        }
        else {
            // XXX: Make it look pretty
            $var = stripslashes($var);
        }
    }

    public function setupServerVariables()
    {
        $KTConfig = & KTConfig::getSingleton();
        $pathInfoSupport = $KTConfig->get('KnowledgeTree/pathInfoSupport');
        if ($pathInfoSupport) {
            // KTS-21: Some environments(FastCGI only?) don't set PATH_INFO
            // correctly, but do set ORIG_PATH_INFO.
            $pathInfo = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
            $origPathInfo = KTUtil::arrayGet($_SERVER, 'ORIG_PATH_INFO');
            if (empty($pathInfo) && !empty($origPathInfo)) {
                $_SERVER['PATH_INFO'] = strip_tags($_SERVER['ORIG_PATH_INFO']);
                $_SERVER['PHP_SELF'] .= $_SERVER['PATH_INFO'];
            }

            $envPathInfo = KTUtil::arrayGet($_SERVER, 'REDIRECT_kt_path_info');
            if (empty($pathInfo) && !empty($envPathInfo)) {
                $_SERVER['PATH_INFO'] = strip_tags($envPathInfo);
                $_SERVER['PHP_SELF'] .= $_SERVER['PATH_INFO'];
            }

            // KTS-50: IIS(and probably most non-Apache web servers) don't
            // set REQUEST_URI.  Fake it.
            $requestUri = KTUtil::arrayGet($_SERVER, 'REQUEST_URI');
            if (empty($requestUri)) {
                $_SERVER['REQUEST_URI'] = strip_tags(KTUtil::addQueryString($_SERVER['PHP_SELF'], $_SERVER['QUERY_STRING']));
            }
        }
        else {
            unset($_SERVER['PATH_INFO']);
        }

        $_SERVER['SCRIPT_NAME'] = strip_tags(KTUtil::arrayGet($_SERVER, 'SCRIPT_NAME'));
        $_SERVER['PHP_SELF'] = strip_tags(KTUtil::arrayGet($_SERVER, 'PHP_SELF'));

        $ktPathInfo = strip_tags(KTUtil::arrayGet($_REQUEST, 'kt_path_info'));
        if (!empty($ktPathInfo)) {
            $_SERVER['PHP_SELF'] .= '?kt_path_info=' . $ktPathInfo;
            $_SERVER['PATH_INFO'] = $ktPathInfo;
        }

        $_SERVER['HTTP_HOST'] = $KTConfig->get('KnowledgeTree/serverName');
    }

    public function setupRandomSeed()
    {
        mt_srand(hexdec(substr(md5(microtime()), - 8)) & 0x7fffffff);
    }

    public static function detectMagicFile()
    {
        $knownPaths = array(
                            '/usr/share/file/magic', // the old default
                            '/etc/httpd/conf/magic', // fedora's location
                            '/etc/magic' // worst case scenario. Noticed this is sometimes empty and containing a reference to somewher else
        );

        foreach ($knownPaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return KT_DIR . '/config/magic';
    }

    public static function handlePHPError($code, $message, $file, $line)
    {
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

    public function catchFatalErrors()
    {
        ini_set('display_errors', 'On');
        $phpError = '><div id="phperror" style="display:none">';
        ini_set('error_prepend_string', $phpError);

        $customErrorPage = KTUtil::kt_url() . '/customerrorpage.php';
        $phpError = '</div>><form name="catcher" action="' . $customErrorPage . '" method="post" ><input type="hidden" name="fatal" value=""></form>
        <script> document.catcher.fatal.value = document.getElementById("phperror").innerHTML; document.catcher.submit();</script>';
        ini_set('error_append_string', $phpError);
    }

    public function guessRootUrl()
    {
        $urlPath = $_SERVER['SCRIPT_NAME'];
        $found = false;
        $rootUrl = '';
        while ($urlPath) {
            if (file_exists(KT_DIR . '/' . $urlPath)) {
                $found = true;
                break;
            }

            $i = strpos($urlPath, '/');
            if ($i === false) {
                break;
            }

            if ($rootUrl) {
                $rootUrl .= '/';
            }

            $rootUrl .= substr($urlPath, 0, $i);
            $urlPath = substr($urlPath, $i + 1);
        }

        if ($found) {
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

    public function initConfig()
    {
        $KTConfig = KTConfig::getSingleton();

        // Override the config setting - KT_DIR is resolved on page load
        $KTConfig->setdefaultns('KnowledgeTree', 'fileSystemRoot', KT_DIR);

        $useCache = false;
        $storeCache = true;

        // If the cache needs to be cleared for debugging purposes uncomment the following lines..
        /*$KTConfig->clearCache();
        $useCache = false;*/

        if (!isset($_SERVER['HTTP_HOST']) || empty($_SERVER['HTTP_HOST'])) {
            // If the http_host server variable is not set then the serverName gets set to localhost
            // We don't want to store this setting so we set store_cache to false
            $storeCache = false;
        }

        $useCache = $KTConfig->setMemCache();

        if ($useCache) {
            $useCache = $KTConfig->loadCache();
        }

        if ($useCache === false) {
            //Read in DB settings and config settings
            $KTConfig->readDBConfig();
        }

        $dbSetup = $KTConfig->setupDB();

        if (PEAR::isError($dbSetup)) {
            /* We need to setup the language handler to display this error correctly */
            $this->setupI18n();
            $this->showDBError($dbSetup);
        }

        // Read in the config settings from the database
        // Create the global $default array(NOTE this was actually created at the top of dmsDefaults, perhaps needs to move here?)
        if ($useCache === false) {
            $res = $KTConfig->readConfig();
            // If the config can't be read then it is most likely caused by a DB connection error
            if (PEAR::isError($res)) {
                $this->showDBError($res);
            }
        }

        // Get default server url settings
        $this->getDynamicConfigSettings();

        if ($useCache === false && $storeCache) {
            $KTConfig->createCache();
        }
    }

    /**
     * This function gets the intial config settings which can only be resolved by using php
     */
    protected function getDynamicConfigSettings()
    {
        $KTConfig = & KTConfig::getSingleton();

        // Override the config setting - KT_DIR is resolved on page load
        $KTConfig->setdefaultns('KnowledgeTree', 'fileSystemRoot', KT_DIR);

        // Set ssl to enabled if using https - if the server variable is not set, allow the config setting to take precedence
        if (array_key_exists('HTTPS', $_SERVER)) {
            if (strtolower($_SERVER['HTTPS']) === 'on') {
                $KTConfig->setdefaultns('KnowledgeTree', 'sslEnabled', 'true');
            }
        }

        $KTConfig->setdefaultns('KnowledgeTree', 'serverName', $_SERVER['HTTP_HOST']);

        // Check for the config setting before overriding with the resolved setting
        $serverName = $KTConfig->get('KnowledgeTree/serverName');
        $rootUrl = $KTConfig->get('KnowledgeTree/rootUrl');
        $execSearchPath = $KTConfig->get('KnowledgeTree/execSearchPath');
        $magicDatabase = $KTConfig->get('KnowledgeTree/magicDatabase');

        // base server name
        if (empty($serverName) || $serverName == 'default') {
            $KTConfig->setdefaultns('KnowledgeTree', 'serverName', KTUtil::getServerName());
        }

        // the sub directory or root url
        if (empty($rootUrl) || $rootUrl == 'default') {
            $KTConfig->setdefaultns('KnowledgeTree', 'rootUrl', $this->guessRootUrl());
        }

        // path to find the executable binaries
        if (empty($execSearchPath) || $execSearchPath == 'default') {
            $KTConfig->setdefaultns('KnowledgeTree', 'execSearchPath', $_SERVER['PATH']);
        }

        // path to magic database
        if (empty($magicDatabase) || $magicDatabase == 'default') {
            $KTConfig->setdefaultns('KnowledgeTree', 'magicDatabase', KTInit::detectMagicFile());
        }
    }

    public function showDBError($dbError)
    {
        $this->handleInitError($dbError);
    }

    private function handleInitError($error)
    {
        global $checkup;
        $msg = $error->toString();

        if ($checkup === true) {
            echo $msg;
            exit(0);
        }

        if (KTUtil::arrayGet($_SERVER, 'REQUEST_METHOD')) {
            // session_start();
            $_SESSION['sErrorMessage'] = $msg;
            $url = KTUtil::kt_url() . '/customerrorpage.php';
            header('Location: ' . $url . $qs);
        }
        else {
            print "$msg\n";
        }

        exit(0);
    }

    public function initTesting()
    {
        $KTConfig = & KTConfig::getSingleton();
        $configFile = file_exists(KT_DIR . '/config/test-config-path') ? trim(file_get_contents(KT_DIR . '/config/test-config-path')) : '';
        if (empty($configFile)) {
            $configFile = 'config/test.ini';
        }

        if (!KTUtil::isAbsolutePath($configFile)) {
            $configFile = sprintf('%s/%s', KT_DIR, $configFile);
        }

        if (!file_exists($configFile)) {
            $this->handleInitError(PEAR::raiseError('Test infrastructure not configured'));
            exit(0);
        }

        $res = $KTConfig->loadFile($configFile);
        if (PEAR::isError($res)) {
            return $res;
        }

        $_SESSION['userID'] = 1;
    }

    /**
     * This function is for any code that must be run only when all other initilization is finished.
     */
    public function finalize()
    {
        // Nothing to do here, though other initializers may have something to do.
    }

}

?>
