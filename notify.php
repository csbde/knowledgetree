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
        $clear_all = KTUtil::arrayGet($_REQUEST, 'clearAll');
        if ($clear_all) {
            return true;
        }
    
        $notification_id = KTUtil::arrayGet($_REQUEST, 'id', null);
        $oKTNotification =& KTNotification::get($notification_id);
        
        if (PEAR::isError($oKTNotification)) {
            $this->addErrorMessage(_('Invalid notification.'));
            exit(redirect(generateControllerLink('dashboard')));
        }
        
        $this->notification =& $oKTNotification;
        
        return true;
    }
    function do_main() {
        $clear_all = KTUtil::arrayGet($_REQUEST, 'clearAll');
        if ($clear_all) {
            return $this->clearAll();
        }
    
    
        // get the notification-handler, instantiate it, call resolveNotification.
        return $this->notification->resolve();
    }
    
    function clearAll() {
        $this->startTransaction();
        $aNotifications = KTNotification::getList('user_id = ' . $this->oUser->getId());
        
        foreach ($aNotifications as $oNotification) {
            $res = $oNotification->delete();
            if (PEAR::isError($res)) {
                $this->rollbackTransaction();
                $this->addErrorMessage(_('Failed to clear notifications.'));
                exit(redirect(generateControllerLink('dashboard')));                
            }
        }
        $this->commitTransaction();
        $this->addInfoMessage(_('Notifications cleared.'));
        exit(redirect(generateControllerLink('dashboard')));
    }
}

$dispatcher =& new KTNotificationDispatcher();
$dispatcher->dispatch();