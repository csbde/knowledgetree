<?php
require_once("$default->owl_fs_root/lib/users/User.inc");
require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
require_once("$default->owl_fs_root/lib/subscriptions/DocumentSubscription.inc");
require_once("$default->owl_fs_root/lib/subscriptions/FolderSubscription.inc");
require_once("$default->owl_fs_root/lib/email/Email.inc");

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


    /*
    fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("AddFolder"), 
                 SubscriptionConstants::subscriptionType("FolderSubscription"),
                 array( "newFolderName" =>, 
                        "parentFolderName" =>));
                
    fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("RemoveFolder"),
                 SubscriptionConstants::subscriptionType("FolderSubscription"),
                 array( "removedFolderName" =>, 
                        "parentFolderName" =>));
    fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("AddDocument"),
                 SubscriptionConstants::subscriptionType("FolderSubscription"),
                 array( "newDocumentName" =>, 
                        "folderName" =>));

    fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("RemoveDocument"),
                 SubscriptionConstants::subscriptionType("FolderSubscription"),
                 array( "removedDocumentName" =>, 
                        "folderName" =>));                 

    fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("ModifyDocument"),
                 SubscriptionConstants::subscriptionType("DocumentSubscription"),
                 array( "modifiedDocumentName" =>, ));
                 */
    /**
     * Fires a subscription alert for this subscription content
     *
     * @param int the id of the subscription content
     * @param int the alert type (document change, new folder, etc.)
     * @param int the subscription content type (folder, document)
     * @param array any dynamic values that should be sent with the alert (eg. document name, path to modified document)
     */
    function fireSubscription($iExternalID, $iSubscriptionAlertType, $iSubscriptionType, $aValues) {
        // get the list of subscriber addresses that we need to alert
        $aSubscribers = retrieveSubscribers($iExternalID, $iSubscriptionType);

        // if the subscription type is document, add the folder subscriber also
        if ($iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            $aSubscribers = array_merge($aSubscribers, retrieveSubscribers($iExternalID, SubscriptionConstants::subscriptionType("FolderSubscription")));
        }

        // for each subscriber, construct an address based on their notification preferences
        for ($i=0; $i<count($aSubscribers); $i++) {
            // lookup the subscription (just for the ID)
            $oSubscription = & Subscription::getByIDs($aSubscribers[$i]->getID(), $iExternalID, $iSubscriptionType);

            // update the alerted status
            $oSubscription->setIsAlerted(true);

            if ($oSubscription->update()) {

                // create the link to this subscription
                $aValues["viewAlertLink"] = generateControllerUrl("viewAlert", "fSubscriptionID=" . $oSubscription->getID());
                // supply the subscriber name
                $aValues["subscriberName"] = $aSubscribers[$i]->getName();

                // retrieve the appropriate content
                $sAlertContent = AlertContent::get($iSubscriptionAlertType, $aValues);

                // construct alerts
                if ($aSubscribers[$i]->getEmailNotification() && (strlen($aSubscribers[$i]->getEmail()) > 0)) {

                    $oEmail = new EmailAlert($aSubscribers[$i]->getEmail(), $sAlertContent);
                    if ($oEmail->send()) {
                        $default->log->debug("SubscriptionEngine::fireSubscription successfully sent email for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                    } else {
                        $default->log->error("SubscriptionEngine::fireSubscription failed sending email for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                    }
                }

                // if sms notification is enabled, sms them
                if ($aSubscribers[$i]->getSmsNotification() && strlen($aSubscribers[$i]->getMobile()) > 0) {
                    $oSms = new SMSAlert($aSubscribers[$i]->getMobile(), $sAlertContent);
                    if ($oSms->send()) {
                        $default->log->debug("SubscriptionEngine::fireSubscription successfully sent sms for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                    } else {
                        $default->log->error("SubscriptionEngine::fireSubscription failed sending sms for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                    }
                }
            }
        }
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
        if ($sql->query("SELECT user_id from $default->owl_folder_subscriptions_table WHERE folder_id=$iFolderID")) {
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
