<?php
/**
 * $Id$
 *
 * UI for subscription pages.
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
 * @package subscriptions
 */
    
/**
 * Displays all current subscriptions with remove checkbox
 */
function renderManagePage() {
    global $default;
    
    // retrieve subscriptions for this user
    $aFolderSubscriptions = SubscriptionManager::retrieveUserSubscriptions($_SESSION["userID"], SubscriptionConstants::subscriptionType("FolderSubscription"));
    $aDocumentSubscriptions = SubscriptionManager::retrieveUserSubscriptions($_SESSION["userID"], SubscriptionConstants::subscriptionType("DocumentSubscription"));    
    $bNoSubscriptions  = ( (count($aFolderSubscriptions) == 0) && (count($aDocumentSubscriptions) == 0) ) ? true : false;

    $sToRender  = renderHeading("Subscriptions", "Subscriptions");
    $sToRender .= "<table width=\"600\">\n";
    if ($bNoSubscriptions) {
        $sToRender .= "<tr><td>You are not subscribed to any documents or folders.</td></tr>\n";       
    } else {
        $sToRender .= "<tr><td>The documents and folders you're subscribed to are listed below.</td></tr>\n";
        $sToRender .= "<tr><td>Select the subscriptions you'd like to unsubscribe from, and click 'Unsubscribe'</td></tr>\n";
    }
	$sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
	$sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";    
    
    // loop through them    
    for ($i = 0; $i < count($aFolderSubscriptions); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
        $sToRender .= "<input type=\"checkbox\" name=\"fFolderSubscriptionIDs[]\" value=\"" . $aFolderSubscriptions[$i]->getID() . "\"/>\n";        
        if ($aFolderSubscriptions[$i]->getIsAlerted()) {
            $sToRender .= $aFolderSubscriptions[$i]->getAlertLink() . "\n";
        } else {
            $sToRender .= $aFolderSubscriptions[$i]->getContentLink() . "\n";
        }
        $sToRender .= "\t\t</td>\n";
        $sToRender .= "\t</tr>\n";
    }
    for ($i = 0; $i < count($aDocumentSubscriptions); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i+count($aFolderSubscriptions)) . "\">\n";
        $sToRender .= "<input type=\"checkbox\" name=\"fDocumentSubscriptionIDs[]\" value=\"" . $aDocumentSubscriptions[$i]->getID() . "\"/>\n";        
        if ($aDocumentSubscriptions[$i]->getIsAlerted()) {
            $sToRender .= $aDocumentSubscriptions[$i]->getAlertLink() . "\n";
        } else {
            $sToRender .= $aDocumentSubscriptions[$i]->getContentLink() . "\n";
        }
        $sToRender .= "\t\t</td>\n";
        $sToRender .= "\t</tr>\n";
    }    

	$sToRender .= "<tr/>\n";
	$sToRender .= "<tr/>\n";
    $sToRender .= "<tr><td>";
    if (!$bNoSubscriptions) {
        $sToRender .= "<input type=\"image\" src=\"$default->graphicsUrl/widgets/unsubscribe.gif\" border=\"0\"/></a>\n";
    }
	$sToRender .= "</td></tr>\n";
    $sToRender .= "</table>\n";
    
    return $sToRender;
}

/**
 * Asks for confirmation for removing multiple subscriptions
 * 
 * @param array subscriptions to remove
 */
function renderMultipleRemoveConfirmationPage($aFolderSubscriptions, $aDocumentSubscriptions) {
    global $default;
    
	$sToRender  = renderHeading("Subscriptions", "Subscriptions");    
	$sToRender .= "<table width=\"600\">\n";   
    $sToRender .= "<tr><td>You have chosen to delete the following subscription(s):</td></tr>\n";
	$sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    
    // loop through them
    for ($i = 0; $i < count($aFolderSubscriptions); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
        $sToRender .= $aFolderSubscriptions[$i]->getContentDisplayPath() . "\n";
        $sToRender .= "\t\t</td>\n";
        $sToRender .= "\t</tr>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fFolderSubscriptionIDs[]\" value=\"" . $aFolderSubscriptions[$i]->getID() . "\"/>";
    }
    
    for ($i = 0; $i < count($aDocumentSubscriptions); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "\t\t<td bgcolor=\"" . getColour($i) . "\">\n";
        $sToRender .= $aDocumentSubscriptions[$i]->getContentDisplayPath() . "\n";
        $sToRender .= "\t\t</td>\n";
        $sToRender .= "\t</tr>\n";
        $sToRender .= "<input type=\"hidden\" name=\"fDocumentSubscriptionIDs[]\" value=\"" . $aDocumentSubscriptions[$i]->getID() . "\"/>";
    }
    
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
	$sToRender .= "<td>Select 'Unsubscribe' to confirm the deletion(s), or 'Cancel' to cancel them</td>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<input type=\"hidden\" name=\"fConfirmed\" value=\"1\"/>";
    $sToRender .= "<tr><td><input type=\"image\" src=\"$default->graphicsUrl/widgets/unsubscribe.gif\" border=\"0\"/></a>\n";    
	$sToRender .= "<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td>\n";
	$sToRender .= "</tr>\n";
    $sToRender .= "</table>\n";
    
    return $sToRender;
}

 /**
 * Confirms the users subscription deletion
 *
 * @param object the subscription
 */
function renderRemoveConfirmationPage($oSubscription) {
	global $default;
	
    $sContentType = $oSubscription->getSubscriptionTypeName();
    if ($sContentType == "folder") {
        $sContentName = Folder::getFolderName($oSubscription->getExternalID());
        $sContentUrl = "fFolderID=" . $oSubscription->getExternalID();
    } else {
        $sContentName = Document::getDocumentName($oSubscription->getExternalID());
        $sContentUrl = "fDocumentID=" . $oSubscription->getExternalID();        
    }
 
 	$sToRender  = renderHeading("Subscriptions", "Subscriptions");
	$sToRender .= "<table width=\"600\">\n";
	$sToRender .= "<tr><td>You have chosen to delete your $sContentType subscription to '$sContentName'.</td></tr>\n";
	$sToRender .= "<tr/>\n";
	$sToRender .= "<tr/>\n";
	$sToRender .= "<td>Select 'Unsubscribe' to confirm the deletion, or 'Cancel' to cancel it</td>\n";
	$sToRender .= "</tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=removeSubscription&$sContentUrl&fConfirmed=1\"><img src =\"$default->graphicsUrl/widgets/unsubscribe.gif\" border=\"0\"/></a>\n";
	$sToRender .= "<a href=\"" . $oSubscription->getContentUrl() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;
}

/**
 * Confirms the users subscription addition
 *
 * @param object the subscription 
 */
function renderAddConfirmationPage($oSubscription) {
	global $default;
	
    $sContentType = $oSubscription->getSubscriptionTypeName();
    if ($sContentType == "folder") {
        $sContentName = Folder::getFolderName($oSubscription->getExternalID());
        $sContentUrl = "fFolderID=" . $oSubscription->getExternalID();
    } else {
        $sContentName = Document::getDocumentName($oSubscription->getExternalID());
        $sContentUrl = "fDocumentID=" . $oSubscription->getExternalID();        
    }
 
	$sToRender  = renderHeading("Subscriptions", "Subscriptions");
	$sToRender .= "<table width=\"600\">\n";
	$sToRender .= "<tr><td>You have chosen to subscribe to the $sContentType entitled '$sContentName'.</td></tr>\n";
	$sToRender .= "<tr/>\n";
	$sToRender .= "<tr>\n";
	$sToRender .= "<td>Select 'Subscribe' to confirm the subscription, or 'Cancel' to cancel it</td>\n";
	$sToRender .= "</tr>\n";
    $sToRender .= "<tr/>\n";
    $sToRender .= "<tr><td><a href=\"$default->rootUrl/control.php?action=addSubscription&$sContentUrl&fConfirmed=1\"><img src =\"$default->graphicsUrl/widgets/subscribe.gif\" border=\"0\"/></a>\n";
	$sToRender .= "<a href=\"" . $oSubscription->getContentUrl() . "\"><img src=\"$default->graphicsUrl/widgets/cancel.gif\" border=\"0\"></a></td>\n";
	$sToRender .= "</tr>\n";
	$sToRender .= "</table>\n";
	
	return $sToRender;    
}

function renderErrorPage($sErrorMessage) {
    global $default;
    $sToRender  = renderHeading("Subscriptions", "Subscriptions");
    $sToRender .= "<table>\n";
    $sToRender .= "<tr><td><p class=\"errorText\">$sErrorMessage</p>\n" .
                  "<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/></a>\n" .
                  "</p></td></tr></table>\n";
   return $sToRender;
}
?>
