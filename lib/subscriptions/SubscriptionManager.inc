<?php
require_once("$default->fileSystemRoot/lib/subscriptions/Subscription.inc");
/**
 * $Id$
 *
 * Facilitates adding and removing file and folder subscriptions.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
class SubscriptionManager {

    /**
     * Creates a subscription.
     *
     * @param int the user to create the subscription for     
     * @param int the external id to create the subscription for
     * @param int the subscription type     
     * @return true if the subscription creation succeed, false and error message otherwise
     */
    function createSubscription($iUserID, $iExternalID, $iSubscriptionType) {
        global $lang_sub_exists;

        if (!Subscription::exists($iUserID, $iExternalID, $iSubscriptionType)) {
            $oSubscription = new Subscription($iUserID, $iExternalID, $iSubscriptionType);
            if ($oSubscription->create()) {
                return true;
            } else {
                // error message set in Subscription::create
                return false;
            }
        } else {
            // TODO: lookup username and folder name from ids for error message
            $_SESSION["errorMessage"] = $lang_sub_exists . " type=$iSubscriptionType, ID=$iExternalID, userID=$iUserID";
            return false;
        }
    }

    /**
     * Removes all subscriptions
     *
     * @param int the user to create the subscription for     
     * @param int the external id to create the subscription for
     * @param int the subscription type     
     * @return true if the subscription creation succeed, false and error message otherwise
     */
    function removeSubscriptions($iExternalID, $iSubscriptionType ) {
        global $lang_sub_not_exists;

        // first retrieve all the subscribers to this content
        $aSubscriptions = SubscriptionManager::retrieveSubscriptions($iExternalID, $iSubscriptionType);
        $bResult = true;
        for ($i = 0; $i < count($aSubscriptions); $i++) {
            // delete them
            if ($aSubscriptions[$i]->delete()) {
                $bResult = $bResult && true;
            } else {
                $bResult = $bResult && false;
            }
        }
        return $bResult;
    }
    
    /**
     * Retrieves all subscriptions for the specified content.
     *
     * @param int the external id to create the subscription for
     * @param int the subscription type
     */
    function retrieveSubscriptions($iExternalID, $iSubscriptionType) {
        global $default;

        $sql = $default->db;
        if ($sql->query(array("SELECT id FROM " . Subscription::getTableName($iSubscriptionType) .  " " ./*ok*/
                        "WHERE " . Subscription::getIdFieldName($iSubscriptionType) . " = ?", $iExternalID))) {
            $aSubscriptions = array();
            while ($sql->next_record()) {
                $aSubscriptions[] = & Subscription::get($sql->f("id"), $iSubscriptionType);
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aSubscriptions;
    }        

    /**
     * Clears the alert from the selected subscription and returns the
     * path to the document.
     *
     * @param int the subscription ID to view
     * @param int the subscription type
     * @return string link to the subscription content, false if the operation failed
     */
    function viewSubscription($iSubscriptionID, $iSubscriptionType) {
        // instantiate subscription from id
        $oSubscription = Subscription::get($iSubscriptionID, $iSubscriptionType);
        if ($oSubscription) {
            // update the alerted flag
            $oSubscription->setIsAlerted(false);
            // save the update
            if ($oSubscription->update()) {
                // return the url to the content
                return $oSubscription->getContentUrl();
            } else {
                // error message set by Subscription::updates
                return false;
            }
        } else {
            // error message set by Subscription::get
            return false;
        }
    }

    /**
    * Retrieves all subscriptions for this user.
    *
    * @param integer the ID of the user to retrieve subscriptions for
    * @return array of subscription objects
    */
    function listSubscriptions($iUserID) {
        return $aSubscriptions = array_merge(SubscriptionManager::retrieveUserSubscriptions($iUserID, SubscriptionConstants::subscriptionType("FolderSubscription")),
                                             SubscriptionManager::retrieveUserSubscriptions($iUserID, SubscriptionConstants::subscriptionType("DocumentSubscription")));
    }
    
    /**
    * Retrieves the users's subscriptions
    *
    * @param integer the ID of the user to retrieve subscriptions for
    * @return array of subscription objects, false if the database interaction fails
    */
    function retrieveUserSubscriptions($iUserID, $iSubscriptionType) {
        global $default;

        $sql = $default->db;
        if ($sql->query(array("SELECT id FROM " . Subscription::getTableName($iSubscriptionType) .  " " ./*ok*/
                        "WHERE user_id = ?", $iUserID))) {
            $aSubscriptions = array();
            while ($sql->next_record()) {
                $aSubscriptions[] = & Subscription::get($sql->f("id"), $iSubscriptionType);
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aSubscriptions;
    }
    
    /**
    * Retrieves all alerted subscriptions for this user.
    *
    * @param integer the ID of the user to retrieve alerted subscriptions for
    * @return array of subscription objects
    */    
    function listSubscriptionAlerts($iUserID) {
        return $aSubscriptions = array_merge(SubscriptionManager::retrieveSubscriptionAlerts($iUserID, SubscriptionConstants::subscriptionType("FolderSubscription")),
                                             SubscriptionManager::retrieveSubscriptionAlerts($iUserID, SubscriptionConstants::subscriptionType("DocumentSubscription")));        
    }

    /**
    * Retrieves the users's subscriptions
    *
    * @param integer the ID of the user to retrieve subscriptions for
    * @return array of subscription objects, false if the database interaction fails
    */
    function retrieveSubscriptionAlerts($iUserID, $iSubscriptionType) {
        global $default;

        $sql = $default->db;
        if ($sql->query(array("SELECT id FROM " . Subscription::getTableName($iSubscriptionType) .  " " ./*ok*/
                        "WHERE user_id = ? " .
                        "AND is_alerted = ?", array($iUserID, true)))) {
            $aSubscriptions = array();
            while ($sql->next_record()) {
                $aSubscriptions[] = & Subscription::get($sql->f("id"), $iSubscriptionType);
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aSubscriptions;
    }
}
?>
