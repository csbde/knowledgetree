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
                             "RemoveFolder" => 2,
                             "AddDocument" => 3,
                             "RemoveDocument" => 4,
                             "ModifyDocument" => 5);
        return $aChangeType[$sType];
    }
}
