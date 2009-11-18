<?php
/**
 * $Id$
 *
 * Holds all the subscription constants.
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
 */ 

class SubscriptionConstants {
    /**
     * Statically returns subscription types
     *
     * @param string the subscription type to lookup
     * @return integer the constant for this type
     */
    function subscriptionType($sType) {
        $aSubscriptionType = array("DocumentSubscription" => 1,
                                   "FolderSubscription" => 2);
        return $aSubscriptionType[$sType];

    }
    /**
     * Statically returns subscription alert types
     *
     * @param string the subscription alert type to lookup
     * @return integer the constant for this type
     */
    function subscriptionAlertType($sType) {
        $aChangeType = array("AddFolder" => 1,
                             "RemoveSubscribedFolder" => 2,
                             "RemoveChildFolder" => 3,
                             "AddDocument" => 4,
                             "RemoveSubscribedDocument" => 5,
                             "RemoveChildDocument" => 6,
                             "ModifyDocument" => 7,
                             "CheckInDocument" => 8,
                             "CheckOutDocument" => 9,
                             "MovedDocument" => 10,
                             "ArchivedDocument" => 11,
                             "RestoredArchivedDocument" => 12);
        return $aChangeType[$sType];
    }
    
    function subscriptionAlertTypeString($iType) {
        $aChangeType = array(1 => "AddFolder",
                             2 => "RemoveSubscribedFolder",
                             3 => "RemoveChildFolder",
                             4 => "AddDocument",
                             5 => "RemoveSubscribedDocument",
                             6 => "RemoveChildDocument",
                             7 => "ModifyDocument",
                             8 => "CheckInDocument",
                             9 => "CheckOutDocument",
                             10 => "MovedDocument",
                             11 => "ArchivedDocument",
                             12 => "RestoredArchivedDocument",
                             );
        return $aChangeType[$iType];
    }
}
?>
