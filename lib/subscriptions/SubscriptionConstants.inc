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
        $aSubscriptionType = array("DocumentSubscription" => 0,
                                   "FolderSubscription" => 1);
        return $aSubscriptionType[$sType];

    }
    /**
     * Statically returns subscription alert types
     *
     * @param string the subscription alert type to lookup
     * @return integer the constant for this type
     */
    function subscriptionAlertType($sType) {
        $aChangeType = array("AddFolder" => 0,
                             "RemoveFolder" => 1,
                             "AddDocument" => 2,
                             "RemoveDocument" => 3,
                             "ModifyDocument" => 4);
        return $aChangeType[$sType];
    }
}
