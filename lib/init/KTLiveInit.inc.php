<?php

require_once(KT_LIB_DIR . '/init/KTInit.inc.php');

class KTLiveInit extends KTInit {

    private $logger;

    public function __construct()
    {
        // The line below will switch on tracing for debugging & dev purposes.
        define('KTLIVE_TRACE_ENABLE', false);
        define('KTLIVE_TRACE_LOG_FILE', $GLOBALS['default']->varDirectory . '/tmp/live_trace.log');
        define('KTLIVE_CALLBACK_LOG_FILE', $GLOBALS['default']->varDirectory . '/tmp/live_callback.log');
        
        $KTConfig = KTConfig::getSingleton();

        if (!isset($GLOBALS['default']->log)) {
            $logDir = $KTConfig->get('urls/logDirectory', KT_DIR.'/var/log');
            $userId = isset($_SESSION['userID']) ? $_SESSION['userID'] : 'n/a';
            $this->configureLog($logDir, 'ERROR', $userId, ACCOUNT_NAME);

            $this->logger = LoggerManager::getLogger('default');
            $GLOBALS['default']->log = $this->logger;
        }
        else {
            $this->logger = $GLOBALS['default']->log;
        }
    }

    protected function checkCacheSystem()
    {
        $KTConfig = KTConfig::getSingleton();
        return $KTConfig->setMemCache();
    }

    public function showDBError($dbError)
    {
        if (liveAccounts::accountExists()) {
            if (!liveAccounts::accountEnabled()) {
                $this->logger->info(ACCOUNT_NAME . " DB Setup. DB CONNECT FAILURE and ACCOUNT DISABLED(" . $dbError->getMessage() . ")");
                liveRenderError::errorDisabled($_SERVER, LIVE_ACCOUNT_DISABLED);
            }
            else {
                $this->logger->error(ACCOUNT_NAME . " DB Setup. DB CONNECT FAILURE and ACCOUNT ENABLED(" . $dbError->getMessage() . ")");
                liveRenderError::errorFail($_SERVER, LIVE_ACCOUNT_DISABLED);
            }
        }
        else {
            $account_name = ACCOUNT_NAME;
            if (!empty($account_name)) {
                $this->logger->error(ACCOUNT_NAME . " DB Setup. DB CONNECT FAILURE and NO ACCOUNT(" . $dbError->getMessage() . ")");
            }
            liveRenderError::errorNoAccount($dbError, LIVE_ACCOUNT_DISABLED);
        }
    }

    /**
     * This function is for any code that must be run only when all other initilization is finished.
     */
    public function finalize()
    {
        $this->accountRoutingLicenceCheck();
    }

    public function accountRoutingLicenceCheck()
    {
        $this->logger = $GLOBALS['default']->log;
        if (!$this->isActiveAccount()) {
            $this->renderLicenseError();
        }
    }

    public function isActiveAccount()
    {
        return $this->checkLicenseOverride() || liveAccounts::accountLicensed();
    }

    public function checkLicenseOverride()
    {
        return isset($_SESSION[LIVE_LICENSE_OVERRIDE]);
    }

    private function renderLicenseError()
    {
        if (liveAccounts::accountExists()) {
            if (liveAccounts::accountEnabled()) {
                $this->logger->error(ACCOUNT_NAME . " License Check. Account Not Licenced, Exists AND Enabled AND Not Expired in SimpleDB.");
                liveRenderError::errorFail(null, LIVE_ACCOUNT_LICENCE);
            }
            else if (liveAccounts::isTrialAccount()) {
                $this->logger->warn(ACCOUNT_NAME . " License Check. Trial Account License expired, Exists but Not Enabled. ");
                liveRenderError::errorTrialLicense($_SERVER, LIVE_ACCOUNT_DISABLED);
            }
            else {
                $this->logger->warn(ACCOUNT_NAME . " License Check. Account Not Licenced, Exists but Not Enabled. ");
                liveRenderError::errorDisabled($_SERVER, LIVE_ACCOUNT_DISABLED);
            }
        }
        else {
            $this->logger->warn(ACCOUNT_NAME . " License Check. Account Not Licenced, and does not exist. ");
            liveRenderError::errorNoAccount(null, LIVE_ACCOUNT_DISABLED);
        }
    }

}

?>
