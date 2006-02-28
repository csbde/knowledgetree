<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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