<?php
/**
 * $Id$
 *
 * Contains the content for subscription alerts.  Also responsible for inserting dynamic
 * data into the notification text before the alert is sent
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
class AlertContent {
	
	/**
	 * Returns the appropriate alert content for document collaboration emails
	 * after appropriately substituting the passed values into the text.
	 *
	 * @param int the type of document collaboration alert, see CollaborationConstants.inc
	 * @param array contains the dynamic values that need to be substituted into the alert notification text
	 * @return string the alert content
	 */
	function getCollaborationAlert($iCollaborationAlertType, $aValues) {
	}
	

    /**
     * Returns the appropriate alert content for the subscriptionAlertType
     * after appropriately substituting the values in the array into the text.
     *
     * @param int the type of subscription alert, see SubscriptionConstants.inc
     * @param array contains the dynamic values that need to be substituted into the 
     *              alert notification text
     * @return string the alert content
     */
    function getSubscriptionAlert($iSubscriptionAlertType, $aValues) {
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
        $oUser = User::get($_SESSION["userID"]);
        $sAttribution = $oUser ? " by " . $oUser->getName() : "";
        $aAlertContent = array(SubscriptionConstants::subscriptionAlertType("AddFolder") =>
                               "A new folder '" . $aValues["newFolderName"] . "' has been added to folder '" . $aValues["parentFolderName"] . "'$sAttribution.<br>" .
                               "Please clear this subscription alert by clicking on the following link: " .
                               generateControllerLink("viewAlert", $sViewAlertParams, $aValues["parentFolderName"]),

                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedFolder") =>
                                       "The folder '" . $aValues["removedFolderName"] . "' has been removed from folder '" . $aValues["parentFolderName"] . "'$sAttribution.<br>" .
                                       "Your subscription to this folder has been removed also.",
                                       
                               SubscriptionConstants::subscriptionAlertType("RemoveChildFolder") =>
                                       "The folder '" . $aValues["removedFolderName"] . "' has been removed from folder '" . $aValues["parentFolderName"] . "'$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["parentFolderName"]),

                               SubscriptionConstants::subscriptionAlertType("AddDocument") =>
                                       "A new document '" . $aValues["newDocumentName"] . "' has been added to folder '" . $aValues["folderName"] . "'$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["newDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("RemoveChildDocument") =>
                                       "The document '" . $aValues["removedDocumentName"] . "' has been removed from folder '" . $aValues["folderName"] . "'$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["removedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument") =>
                                       "The document '" . $aValues["removedDocumentName"] . "' has been removed from folder '" . $aValues["folderName"] . "'$sAttribution.<br>" .
                                       "Your subscription to this document has been removed also.",

                               SubscriptionConstants::subscriptionAlertType("ModifyDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been modified$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("CheckInDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been checked in$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("CheckOutDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been checked out$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("MovedDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been moved from folder '" . $aValues["oldFolderName"] . "' to folder '" . $aValues["newFolderName"] . "'$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("ArchivedDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been archived$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                                                              
                               SubscriptionConstants::subscriptionAlertType("RestoredArchivedDocument") =>
                                       "The document '" . $aValues["modifiedDocumentName"] . "' has been restored from the archive$sAttribution.<br>" .
                                       "Please clear this subscription alert by clicking on the following link: " .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]));

        return array ("subject" => $aAlertSubject[$iSubscriptionAlertType],
                      "text" => "Hello " . $aValues["subscriberName"] . ",<br><br>" . $aAlertContent[$iSubscriptionAlertType]);
    }
}
?>