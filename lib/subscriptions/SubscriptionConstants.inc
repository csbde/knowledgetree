<?php
/**
 * 
 * $Id$
 * 
 * Holds all the subscription constants.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
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
                             "ArchivedDocument" => 11);
        return $aChangeType[$sType];
    }
}
