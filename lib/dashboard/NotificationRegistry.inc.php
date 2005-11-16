<?php

class KTNotificationRegistry {
    var $notification_types = array();
    var $notification_instances = array();
    // {{{ getSingleton
    function &getSingleton () {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTNotificationRegistry')) {
            $GLOBALS['oKTNotificationRegistry'] = new KTNotificationRegistry;
        }
        return $GLOBALS['oKTNotificationRegistry'];
        
    }
    // }}}

    // pass in:
    //   nsname (e.g. ktcore/subscription)
    //   classname (e.g. KTSubscriptionNotification)
    function registerNotificationHandler($nsname, $className) {
        $this->notification_types[$nsname] = $className;
    }

    // FIXME insert into notification instances {PERF}
    
    function getHandler($nsname) {
        if (!array_key_exists($nsname, $this->notification_types)) {
            return null;
        } else {
            return new $this->notification_types[$nsname];
        }
    }
}

?>