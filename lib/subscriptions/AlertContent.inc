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
                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedFolder") => $sSubject . "Removed Folder",
                               SubscriptionConstants::subscriptionAlertType("RemoveChildFolder") => $sSubject . "Removed Folder",
                               SubscriptionConstants::subscriptionAlertType("AddDocument") => $sSubject . "New Document",
                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument") => $sSubject . "Removed Document",
                               SubscriptionConstants::subscriptionAlertType("RemoveChildDocument") => $sSubject . "Removed Document",
                               SubscriptionConstants::subscriptionAlertType("ModifyDocument") => $sSubject . "Modified Document",
                               SubscriptionConstants::subscriptionAlertType("CheckInDocument") => $sSubject . "Checked In Document",
                               SubscriptionConstants::subscriptionAlertType("CheckOutDocument") => $sSubject . "Checked Out Document",
                               SubscriptionConstants::subscriptionAlertType("MovedDocument") => $sSubject . "Moved Document",
                               SubscriptionConstants::subscriptionAlertType("ArchivedDocument") => $sSubject . "Archived Document",
                               SubscriptionConstants::subscriptionAlertType("RestoredArchivedDocument") => $sSubject . "Restored Document");

        $sViewAlertParams = "fSubscriptionID=" . $aValues["subscriptionID"] . "&fSubscriptionType=" . $aValues["subscriptionType"];
        $aAlertContent = array(SubscriptionConstants::subscriptionAlertType("AddFolder") =>
                               "A new folder '" . $aValues["newFolderName"] . "' has been added to folder '" . $aValues["parentFolderName"] . "'.<br>" .
                               "Please clear this subscription alert by clicking on the following link: " .
                               generateControllerLink("viewAlert", $sViewAlertParams, $aValues["parentFolderName"]),

                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedFolder") =>
                                       "The folder '" . $aValues["removedFolderName"] . "' has been removed from folder '" . $aValues["parentFolderName"] . "'.<br>" .
                                       "Your subscription to this folder has been removed also.",
                                       
                               SubscriptionConstants::subscriptionAlertType("RemoveChildFolder") =>
                                       "The folder '" . $aValues["removedFolderName"] . "' has been removed from folder '" . $aValues["parentFolderName"] . "'.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["parentFolderName"]),

                               SubscriptionConstants::subscriptionAlertType("AddDocument") =>
                                       "A new document '" . $aValues["newDocumentName"] . "' has been added to folder '" . $aValues["folderName"] . "'.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["newDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("RemoveChildDocument") =>
                                       "The document '" . $aValues["removedDocumentName"] . "' has been removed from folder '" . $aValues["folderName"] . "'.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["removedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument") =>
                                       "The document '" . $aValues["removedDocumentName"] . "' has been removed from folder '" . $aValues["folderName"] . "'.<br>" .
                                       "Your subscription to this document has been removed also.",

                               SubscriptionConstants::subscriptionAlertType("ModifyDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been modified.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("CheckInDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been checked in.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("CheckOutDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been checked out.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("MovedDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been moved from folder '" . $aValues["oldFolderName"] . "' to folder '" . $aValues["newFolderName"] . "'.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("ArchivedDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been archived.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                                                              
                               SubscriptionConstants::subscriptionAlertType("RestoredArchivedDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been restored from the archive.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]));

        return array ("subject" => $aAlertSubject[$iSubscriptionAlertType],
                      "text" => "Hello " . $aValues["subscriberName"] . ",<br><br>" . $aAlertContent[$iSubscriptionAlertType]);
    }
}
