<?php

/**
 * $Id$
 *
 * UI for subscription pages.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.subscriptions
 */
 
 /**
 * Confirms the users subscription deletion
 *
 * @param object the subscription
 */
function renderSubscriptionRemoveConfirmationPage($oSubscription) {
	global $default;
	
    $sContentType = $oSubscription->getSubscriptionTypeName();
    if ($sContentType == "folder") {
        $sContentName = Folder::getFolderName($oSubscription->getExternalID());
        $sContentUrl = "fFolderID=" . $oSubscription->getExternalID();
    } else {
        $sContentName = Document::getDocumentName($oSubscription->getExternalID());
        $sContentUrl = "fDocumentID=" . $oSubscription->getExternalID();        
    }
 
	$sToRender  = "<table>\n";
	$sToRender .= "<td>You have chosen to delete the your $sContentType subscription to '$sContentName'.</td>\n";
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
function renderSubscriptionAddConfirmationPage($oSubscription) {
	global $default;
	
    $sContentType = $oSubscription->getSubscriptionTypeName();
    if ($sContentType == "folder") {
        $sContentName = Folder::getFolderName($oSubscription->getExternalID());
        $sContentUrl = "fFolderID=" . $oSubscription->getExternalID();
    } else {
        $sContentName = Document::getDocumentName($oSubscription->getExternalID());
        $sContentUrl = "fDocumentID=" . $oSubscription->getExternalID();        
    }
 
	$sToRender  = "<table>\n";
	$sToRender .= "<td>You have chosen to subscribe to the $sContentType entitled '$sContentName'.</td>\n";
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
    return "<p class=\"errorText\">$sErrorMessage</p>\n" .
           "<a href=\"javascript:history.go(-1)\"><img src=\"$default->graphicsUrl/widgets/back.gif\" border=\"0\"/></a>\n";
}
?>
