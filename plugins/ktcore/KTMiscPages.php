<?php

/**
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
