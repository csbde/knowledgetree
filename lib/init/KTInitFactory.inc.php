<?php

require_once(KT_LIB_DIR . '/init/KTInit.inc.php');
require_once(KT_LIB_DIR . '/init/KTLiveInit.inc.php');

class KTInitFactory {

    public static function getSystemInitializer()
    {
        self::initSystem();
        return ACCOUNT_ROUTING_ENABLED ? new KTLiveInit() : new KTInit();
    }

    private static function initSystem()
    {
        if (file_exists(KT_PLUGIN_DIR . '/ktlive/liveEnable.php')) {
            define('ACCOUNT_ROUTING_ENABLED', true);
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
        }
        else {
            define('ACCOUNT_ROUTING_ENABLED', false);
            define('ACCOUNT_NAME', '');
        }
    }

}

?>
