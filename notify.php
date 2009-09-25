<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
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
