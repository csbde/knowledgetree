<?php
require_once("$default->owl_fs_root/lib/subscriptions/Subscription.inc");

/**
 * 
 * $Id$
 * 
 * Facilitates adding and removing file and folder subscriptions.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
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
     * Removes a subscription.
     *
     * @param int the user to create the subscription for     
     * @param int the external id to create the subscription for
     * @param int the subscription type     
     * @return true if the subscription creation succeed, false and error message otherwise
     */
    function removeSubscription($iUserID, $iExternalID, $iSubscriptionType ) {
        global $lang_sub_not_exists;

        if (Subscription::exists($iUserID, $iExternalID, $iSubscriptionType)) {
            $oSubscription = Subscription::getByIDs($iUserID, $iExternalID, $iSubscriptionType);
            if ($oSubscription->delete()) {
                return true;
            } else {
                // error message set in Subscription::delete
                return false;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_sub_not_exists;
            return false;
        }
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
    }

    /**
    * Retrieves all subscriptions for this user.
    *
    * @param integer the ID of the user to retrieve subscriptions for
    * @return array of subscription objects
    */
    function listSubscriptions($iUserID) {
        return $aSubscriptions = array("folders" => SubscriptionManager::retrieveSubscriptions($iUserID, SubscriptionConstants::subscriptionType("FolderSubscription")),
                                       "documents" => SubscriptionManager::retrieveSubscriptions($iUserID, SubscriptionConstants::subscriptionType("DocumentSubscription")));
    }

    /**
    * Retrieves the users's subscriptions
    *
    * @param integer the ID of the user to retrieve subscriptions for
    * @return array of subscription objects, false if the database interaction fails
    */
    function retrieveSubscriptions($iUserID, $iSubscriptionType) {
        global $default;

        $sql = $default->db;
        if ($sql->query("SELECT id FROM " . Subscription::getTableName($iSubscriptionType) .  " " .
                        "WHERE user_id = $iUserID")) {
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
