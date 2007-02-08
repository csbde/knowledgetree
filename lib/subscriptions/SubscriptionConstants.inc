<?php
/**
 * $Id$
 *
 * Holds all the subscription constants.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.subscriptions
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