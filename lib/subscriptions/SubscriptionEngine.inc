<?php
require_once("$default->owl_fs_root/lib/users/User.inc");
require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
require_once("$default->owl_fs_root/lib/subscriptions/Subscription.inc");
require_once("$default->owl_fs_root/lib/subscriptions/AlertContent.inc");
require_once("$default->owl_fs_root/lib/subscriptions/alert/EmailAlert.inc");
require_once("$default->owl_fs_root/lib/subscriptions/alert/SMSAlert.inc");

/**
 * 
 * $Id$
 * 
 * Facilitates firing subscription alerts.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
 * @package lib.subscriptions
 */
class SubscriptionEngine {

    /**
     * Fires a subscription alert for this subscription content
     *
     * @param int the id of the subscription content
     * @param int the alert type (document change, new folder, etc.)
     * @param int the subscription content type (folder, document)
     * @param array any dynamic values that should be sent with the alert (eg. document name, path to modified document)
     */
    function fireSubscription($iExternalID, $iSubscriptionAlertType, $iSubscriptionType, $aValues) {
        global $default;

        // get the list of subscriber addresses that we need to alert
        $aSubscribers = SubscriptionEngine::retrieveSubscribers($iExternalID, $iSubscriptionType);

        // count the number of subscriptions we've sent
        $iSubscriptionsSent = 0;

        // if the subscription type is document, fire the folder subscriptions also
        if ($iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            $oDocument = & Document::get($iExternalID);
            $default->log->debug("document=$oDocument");
            $iSubscriptionsSent = SubscriptionEngine::fireSubscription($oDocument->getFolderID(),
                                  $iSubscriptionAlertType,
                                  SubscriptionConstants::subscriptionType("FolderSubscription"),
                                  $aValues);
            $default->log->info("fired folder subscribers, count=$iSubscriptionsSent");
        }
        $default->log->info("subscribers=" . arrayToString($aSubscribers));

        // for each subscriber, construct an address based on their notification preferences
        for ($i=0; $i<count($aSubscribers); $i++) {
            $default->log->info("looking up subscription subID=" . $aSubscribers[$i]->getID() . " ID=$iExternalID; type=$iSubscriptionType");
            // lookup the subscription
            $oSubscription = & Subscription::getByIDs($aSubscribers[$i]->getID(), $iExternalID, $iSubscriptionType);

            // update the alerted status
            $oSubscription->setIsAlerted(true);

            // write it back to the db
            if ($oSubscription->update()) {

                // get the subscription id
                $aValues["subscriptionID"] = $oSubscription->getID();
                // and type
                $aValues["subscriptionType"] = $iSubscriptionType;
                // and subscriber name
                $aValues["subscriberName"] = $aSubscribers[$i]->getName();

                // retrieve the appropriate content
                // using the values array to customise the notification message
                $sAlertContent = AlertContent::get($iSubscriptionAlertType, $aValues);

                // construct alerts
                if ($aSubscribers[$i]->getEmailNotification() && (strlen($aSubscribers[$i]->getEmail()) > 0)) {

                    $oEmail = new EmailAlert($aSubscribers[$i]->getEmail(), $sAlertContent["subject"], $sAlertContent["text"]);
                    if ($oEmail->send()) {
                        $iSubscriptionsSent++;
                        $default->log->debug("SubscriptionEngine::fireSubscription successfully sent email alert to " . $aSubscribers[$i]->getEmail() . " for subscriptionID=" . $aSubscribers[$i]->getID());
                    } else {
                        $default->log->error("SubscriptionEngine::fireSubscription failed sending email alert to " . $aSubscribers[$i]->getEmail() . " for subscriptionID=" . $aSubscribers[$i]->getID() . "; text=$sAlertContent");
                    }
                }

                // if sms notification is enabled, sms them
                if ($aSubscribers[$i]->getSmsNotification() && strlen($aSubscribers[$i]->getMobile()) > 0) {
                    $oSms = new SMSAlert($aSubscribers[$i]->getMobile(), $sAlertContent["text"]);
                    if ($oSms->send()) {
                        $iSubscriptionsSent++;
                        $default->log->debug("SubscriptionEngine::fireSubscription successfully sent sms for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                    } else {
                        $default->log->error("SubscriptionEngine::fireSubscription failed sending sms for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                    }
                }
            } else {
                $default->log->error("SubscriptionEngine::fireSubscription could not update subscription- db error?");
            }
        }
        // return the number of processed subscriptions
        return $iSubscriptionsSent;
    }

    /**
    * Retrieves the users that are subscribed to this subscription content
    *
    * @param integer the ID of the subscription content to retrieve subscribers for
    * @param int the subscription content type (folder, document)
    * @return array of users objects representing the subscribers, false on error
    */
    function retrieveSubscribers($iExternalID, $iSubscriptionType) {
        global $default;

        $sql = $default->db;
        $aUsers = array();
        if ($sql->query("SELECT user_id FROM " . Subscription::getTableName($iSubscriptionType) .  " " .
                        "WHERE " . Subscription::getIdFieldName($iSubscriptionType) .  " = $iExternalID")) {
            while ($sql->next_record()) {
                $aUsers[] = & User::get($sql->f("user_id"));
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aUsers;
    }
}
