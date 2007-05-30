<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 */

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
            $this->addInfoMessage(_kt('Cannot find the notification you requested.  Notification may already have been cleared.'));
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
        $oHandler =& $this->notification->getHandler();
        $oHandler->notification =& $this->notification;
        $oHandler->subDispatch($this);
        exit(0);
    }
    
    function clearAll() {
        $this->startTransaction();
        $aNotifications = KTNotification::getList('user_id = ' . $this->oUser->getId());
        
        foreach ($aNotifications as $oNotification) {
            $res = $oNotification->delete();
            if (PEAR::isError($res)) {
                $this->rollbackTransaction();
                $this->addErrorMessage(_kt('Failed to clear notifications.'));
                exit(redirect(generateControllerLink('dashboard')));                
            }
        }
        $this->commitTransaction();
        $this->addInfoMessage(_kt('Notifications cleared.'));
        exit(redirect(generateControllerLink('dashboard')));
    }
}

$dispatcher =& new KTNotificationDispatcher();
$dispatcher->dispatch();
