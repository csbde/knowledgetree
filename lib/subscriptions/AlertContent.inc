<?php
/**
 * 
 * $Id$
 * 
 * Contains the content for subscription alerts.  Also responsible for inserting dynamic
 * data into the notification text before the alert is sent
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
 * @package lib.subscriptions
 */
class AlertContent {

    /**
     * Returns the appropriate alert content for the subscriptionAlertType
     * after appropriately substituting the values in the array into the text.
     *
     * @param int the type of subscription alert, see SubscriptionConstants.inc
     * @param array contains the dynamic values that need to be substituted into the 
     *              alert notification text
     * @return string the alert content
     */
    function get($iSubscriptionAlertType, $aValues) {
        $sSubject = "MRC DMS Subscription Alert- ";
        $aAlertSubject = array(SubscriptionConstants::subscriptionAlertType("AddFolder") => $sSubject . "New Folder",
                               SubscriptionConstants::subscriptionAlertType("RemoveFolder") => $sSubject . "Removed Folder",
                               SubscriptionConstants::subscriptionAlertType("AddDocument") => $sSubject . "New Document",
                               SubscriptionConstants::subscriptionAlertType("RemoveDocument") => $sSubject . "Removed Document",
                               SubscriptionConstants::subscriptionAlertType("ModifyDocument") => $sSubject . "Modified Document");

        $sViewAlertUrl = "/presentation/lookAndFeel/knowledgeTree/subscriptions/viewAlertBL.php";
        $sViewAlertParams = "fSubscriptionID=" . $aValues["subscriptionID"] . "&fSubscriptionType=" . $aValues["subscriptionType"];
        $aAlertContent = array(SubscriptionConstants::subscriptionAlertType("AddFolder") =>
                               "A new folder '" . $aValues["newFolderName"] . "' has been added to folder '" . $aValues["parentFolderName"] . "'.<br>" .
                               "Please clear this subscription alert by clicking on the following link: " .
                               generateLink($sViewAlertUrl, $sViewAlertParams, $aValues["parentFolderName"]),

                               SubscriptionConstants::subscriptionAlertType("RemoveFolder") =>
                                       "The folder '" . $aValues["removedFolderName"] . "' has been removed from folder '" . $aValues["parentFolderName"] . "'.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateLink($sViewAlertUrl, $sViewAlertParams, $aValues["parentFolderName"]),

                               SubscriptionConstants::subscriptionAlertType("AddDocument") =>
                                       "A new document '" . $aValues["newDocumentName"] . "' has been added to folder '" . $aValues["folderName"] . "'.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateLink($sViewAlertUrl, $sViewAlertParams, $aValues["newDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("RemoveDocument") =>
                                       "The document '" . $aValues["removedDocumentName"] . "' has been removed from folder '" . $aValues["folderName"] . "'.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateLink($sViewAlertUrl, $sViewAlertParams, $aValues["folderName"]),

                               SubscriptionConstants::subscriptionAlertType("ModifyDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been modified.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateLink($sViewAlertUrl, $sViewAlertParams, $aValues["modifiedDocumentName"]));

        return array ("subject" => $aAlertSubject[$iSubscriptionAlertType],
                      "text" => "Hello " . $aValues["subscriberName"] . "<br><br>" . $aAlertContent[$iSubscriptionAlertType]);
    }
}
