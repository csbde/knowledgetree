<?php

/**
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */
 
class KTNotificationOverflowPage extends KTStandardDispatcher {
    function predispatch() {
        $this->persistParams(array('page'));
        $this->aBreadcrumbs[] = array(
            'url' => $_SERVER['PHP_SELF'],
            'name' => _kt("Notifications"),
        );
    }
    
    function do_main() {
        
        $notifications = (array) KTNotification::getList(array("user_id = ?", $this->oUser->getId()));
        $num_notifications = count($notifications);
        
        $PAGE_SIZE = 5;
        
        $page = (int) KTUtil::arrayGet($_REQUEST, 'page', 0);
        $page_count = ceil($num_notifications / $PAGE_SIZE);
        
        if ($page >= $page_count) {
            $page = $page_count - 1;
        }
        
        if ($page < 0) { $page = 0; }
        
        // slice the notification array.
        $notifications = array_slice($notifications, $page * $PAGE_SIZE, $PAGE_SIZE);
        
        // prepare the batch html.  easier to do this here than in the template.
        $batch = array();
        for ($i = 0; $i < $page_count; $i++) {
            if ($i == $page) {
                $batch[] = sprintf("<strong>%d</strong>", $i+1);
            } else {
                $batch[] = sprintf('<a href="%s">%d</a>', 
                    KTUtil::addQueryStringSelf($this->meldPersistQuery(array("page" => $i), "main", true)),
                    $i+1);            
            }
        }
        $batch_html = implode(' &middot; ', $batch);
        
        $count_string = sprintf(_kt("Showing Notifications %d - %d of %d"), $page * $PAGE_SIZE + 1, min(($page+1)  * $PAGE_SIZE, $num_notifications), $num_notifications);
        
        $this->oPage->setTitle(_kt("Items that require your attention"));
        $oTemplate =& $this->oValidator->validateTemplate("ktcore/misc/notification_overflow");
        $oTemplate->setData(array(
            'count_string' => $count_string,
            'batch_html' => $batch_html,
            'notifications' => $notifications,
        ));
        return $oTemplate->render();
    }
} 
 
?>
