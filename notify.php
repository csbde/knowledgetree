<?php

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/dashboard/NotificationRegistry.inc.php');
require_once(KT_LIB_DIR . '/dashboard/Notification.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/session/control.inc');

/*
 * Using KTStandardDispatcher for errorPage, overriding handleOutput as
 * the document action dispatcher will handle that.
 */

/**
 * Dispatcher for action.php/actionname
 *
 * This dispatcher looks up the action from the Action Registry, and
 * then chains onto that action's dispatcher.
 */
class KTNotificationDispatcher extends KTStandardDispatcher {
    var $notification;

    function check() {
        $notification_id = KTUtil::arrayGet($_REQUEST, 'id', null);
        $oKTNotification =& KTNotification::get($notification_id);
        
        if (PEAR::isError($oKTNotification)) {
            $_SESSION['KTErrorMessage'][] = 'Invalid notification.';
            exit(redirect(generateControllerLink('dashboard')));
        }
        
        $this->notification =& $oKTNotification;
        
        return true;
    }
    function do_main() {
        // get the notification-handler, instantiate it, call resolveNotification.
        return $this->notification->resolve();
    }
}

$dispatcher =& new KTNotificationDispatcher();
$dispatcher->dispatch();