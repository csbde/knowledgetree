<?php
/**
 * $Id$
 *
 * Contains the content for subscription alerts.  Also responsible for inserting dynamic
 * data into the notification text before the alert is sent
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
        $sSubject = _("DMS Subscription Alert") . " - ";
        $aAlertSubject = array(SubscriptionConstants::subscriptionAlertType("AddFolder") => $sSubject . _("New Folder"),
                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedFolder") => $sSubject . _("Removed Folder"),
                               SubscriptionConstants::subscriptionAlertType("RemoveChildFolder") => $sSubject . _("Removed Folder"),
                               SubscriptionConstants::subscriptionAlertType("AddDocument") => $sSubject . _("New Document"),
                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument") => $sSubject . _("Removed Document"),
                               SubscriptionConstants::subscriptionAlertType("RemoveChildDocument") => $sSubject . _("Removed Document"),
                               SubscriptionConstants::subscriptionAlertType("ModifyDocument") => $sSubject . _("Modified Document"),
                               SubscriptionConstants::subscriptionAlertType("CheckInDocument") => $sSubject . _("Checked In Document"),
                               SubscriptionConstants::subscriptionAlertType("CheckOutDocument") => $sSubject . _("Checked Out Document"),
                               SubscriptionConstants::subscriptionAlertType("MovedDocument") => $sSubject . _("Moved Document"),
                               SubscriptionConstants::subscriptionAlertType("ArchivedDocument") => $sSubject . _("Archived Document"),
                               SubscriptionConstants::subscriptionAlertType("RestoredArchivedDocument") => $sSubject . _("Restored Document"));

        $sViewAlertParams = "fSubscriptionID=" . $aValues["subscriptionID"] . "&fSubscriptionType=" . $aValues["subscriptionType"];
        $oUser = User::get($_SESSION["userID"]);
        $sAttribution = $oUser ? " by " . $oUser->getName() : "";
        $aAlertContent = array(SubscriptionConstants::subscriptionAlertType("AddFolder") =>
                               			sprintf(_("A new folder ' %s ' has been added to folder ' %s '%s.<br>Please clear this subscription alert by clicking on the following link: %s"), $aValues["newFolderName"], $aValues["parentFolderName"],$sAttribution,  
                               			generateControllerLink("viewAlert", $sViewAlertParams, $aValues["parentFolderName"])),

                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedFolder") =>
                                       sprintf(_("The folder '%s' has been removed from folder '%s'%s.<br>Your subscription to this folder has been removed also."),$aValues["removedFolderName"],$aValues["parentFolderName"], $sAttribution),
                                       
                               SubscriptionConstants::subscriptionAlertType("RemoveChildFolder") =>
                                       sprintf(_("The folder '%s' has been removed from folder '%s'%s.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["removedFolderName"], $aValues["parentFolderName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["parentFolderName"]),

                               SubscriptionConstants::subscriptionAlertType("AddDocument") =>
                                       sprintf(_("A new document '%s' has been added to folder '%s'%s.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["newDocumentName"], $aValues["folderName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["newDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("RemoveChildDocument") =>
                                       sprintf(_("The document '%s' has been removed from folder '%s'.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["removedDocumentName"], $aValues["folderName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["removedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("RemoveSubscribedDocument") =>
                                       sprintf(_("The document '%s' has been removed from folder '%s'%s.<br>Your subscription to this document has been removed also."), $aValues["removedDocumentName"], $aValues["folderName"], $sAttribution),

                               SubscriptionConstants::subscriptionAlertType("ModifyDocument") =>
                                       sprintf(_("The document '%s' has been modified%s.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["modifiedDocumentName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("CheckInDocument") =>
                                       sprintf(_("The document '%s' has been checked in%s.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["modifiedDocumentName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("CheckOutDocument") =>
                                       sprintf(_("The document '%s' has been checked out%s.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["modifiedDocumentName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                       
                               SubscriptionConstants::subscriptionAlertType("MovedDocument") =>
                                       sprintf(_("The document '%s' has been moved from folder '%s' to folder '%s'%s.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["modifiedDocumentName"], $aValues["oldFolderName"], $aValues["newFolderName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),

                               SubscriptionConstants::subscriptionAlertType("ArchivedDocument") =>
                                       sprintf(_("The document '%s' has been archived%s.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["modifiedDocumentName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]),
                                                                              
                               SubscriptionConstants::subscriptionAlertType("RestoredArchivedDocument") =>
                                       sprintf(_("The document '%s' has been restored from the archive%s.<br>Please clear this subscription alert by clicking on the following link: "), $aValues["modifiedDocumentName"], $sAttribution) .
                                       generateControllerLink("viewAlert", $sViewAlertParams, $aValues["modifiedDocumentName"]));

        return array ("subject" => $aAlertSubject[$iSubscriptionAlertType],
                      "text" => sprintf(_("Hello %s"), $aValues["subscriberName"]) . ",<br><br>" . $aAlertContent[$iSubscriptionAlertType]);
    }
}
?>
