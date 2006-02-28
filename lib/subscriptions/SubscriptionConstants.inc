<?php
/**
 * $Id$
 *
 * Holds all the subscription constants.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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