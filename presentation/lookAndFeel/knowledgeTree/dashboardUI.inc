<?php

require_once("$default->fileSystemRoot/lib/users/User.inc");

/**
 * $Id$
 *
 * Dashboard page html UI building functions.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation
 */

 /**
 * Displays the pending web documents
 *
 * @param array of pending web documents
 */
function renderPendingWebDocuments($aPendingDocumentList) {
    global $default;

    if (count($aPendingDocumentList) > 0) {
        $sBgColor = "#9D9D7F"; 
        $sToRender  = "\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
            
        $sToRender .= "\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">Pending Web Documents</font></th>\n";
        $sToRender .= "\t</tr>\n";
        for ($i = 0; $i < count($aPendingDocumentList); $i++) {
            $oWebDocument = $aPendingDocumentList[$i];
            $sToRender .= "\t<tr>\n";
            $sToRender .= "<td colspan=\"2\">" . generateControllerLink("webDocument", "fWebDocumentID=" . $oWebDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/dstatus.gif\" border=\"0\"/>&nbsp;" . $oWebDocument->getDisplayPath()) . "</td>\n";        
            $sToRender .= "\t</tr>\n";
        }
        return $sToRender;
    } else {
        return "";
    }
}

/**
 * Displays the pending collaboration documents
 *
 * @param array of pending collaboration documents
 */
function renderPendingCollaborationDocuments($aPendingDocumentList) {
    global $default;

    if (count($aPendingDocumentList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
        
    $sToRender .= "\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">Pending Documents</font></th>\n";
    $sToRender .= "\t</tr>\n";
    for ($i = 0; $i < count($aPendingDocumentList); $i++) {
        $oDocument = $aPendingDocumentList[$i];
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<td colspan=\"2\">" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/dstatus.gif\" border=\"0\"/>&nbsp;" . $oDocument->getDisplayPath()) . "</td>\n";        
        $sToRender .= "\t</tr>\n";
    }    
    return $sToRender;
}

/**
 * Displays the checked out documents
 *
 * @param array of checked out documents
 */
function renderCheckedOutDocuments($aCheckedOutDocumentList) {
    global $default;
    if (count($aCheckedOutDocumentList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";   
    $sToRender .= "\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">Checked Out Documents</font></th>\n";
    $sToRender .= "\t</tr>\n";
    
    if (count($aCheckedOutDocumentList) > 0) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<th align=\"left\" width=\"66%\" class=\"sectionColumns\">\n";
        $sToRender .= "Title\n";
        $sToRender .= "</th>\n";
        $sToRender .= "<th align=\"right\" width=\"33%\" class=\"sectionColumns\">\n";
        $sToRender .= "Days\n";
        $sToRender .= "</th>";
        $sToRender .= "\t\t</tr>\n";
    }
    
    for ($i = 0; $i < count($aCheckedOutDocumentList); $i++) {
        $oDocument = $aCheckedOutDocumentList[$i];
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<td>" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/dstatus.gif\" border=\"0\"/>&nbsp;" . $oDocument->getDisplayPath()) . "</td>\n";
        $sToRender .= "<td align=right>" . $oDocument->getDaysSinceLastModified() . "</td>\n";
        $sToRender .= "\t</tr>\n";
    }
    return $sToRender;
}

/**
 * Displays the subscription alerts
 *
 * @param array of subscription alerts
 */
function renderSubscriptionAlerts($aSubscriptionAlertList) {
    global $default;
    if (count($aSubscriptionAlertList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
    $sToRender .= "\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">Subscription Alerts</font></th>\n";
    $sToRender .= "\t</tr>\n";
    for ($i = 0; $i < count($aSubscriptionAlertList); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<td colspan=\"2\">" . $aSubscriptionAlertList[$i]->getAlertLink() . "</td>\n";
        $sToRender .= "\t</tr>\n";
    }    
    return $sToRender;
}

/**
 * Displays the quicklinks
 */
function renderQuickLinks($aQuickLinks) {
    global $default;
    $sToRender .= "\t<tr align=\"left\" bgcolor=\"#9D9D7F\">\n";
    $sToRender .= "\t\t<th class=\"sectionHeading\">Quick Links</th>\n";
    $sToRender .= "\t</tr>\n";
    for ($i = 0; $i < count($aQuickLinks); $i++) {
        $sToRender .= "\t<tr>\n";
        $sToRender .= "<td colspan=\"2\"><a href=\"" . $aQuickLinks[$i]->getUrl() . "\" target=\"_new\"><img src=\"$default->graphicsUrl/widgets/qlink.gif\" border=\"0\"/>" . $aQuickLinks[$i]->getName() . "</a></td>\n";
        $sToRender .= "\t</tr>\n";
    }
    return $sToRender;
}

/**
 * Renders the dashboard
 *
 * @param array pending collaboration documents for this user
 * @param array checked out documents for this user
 * @param array subscription alerts for this user
 */
function renderPage($aPendingDocumentList, $aCheckedOutDocumentList, $aSubscriptionAlertList, $aQuickLinks, $aWebDocuments) {
    global $default;
    
    $sToRender  = "<table border=\"0\" width=\"600\" >\n";
	$sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td><img src=\"$default->graphicsUrl/welcome.gif\"/></td>\n";
    
    // some arb welcoming text goes here
    $oUser = & User::get($_SESSION["userID"]);
    $sUserName = $oUser->getName();
    $sToRender .= "\t\t<td>Hi" . (strlen($sUserName) > 0 ? " " . $sUserName : "") . ", welcome back to the MRC DMS, part of the Knowledge Tree.</td>\n";
    $sToRender .= "\t</tr>\n";
    
    // dashboard alerts
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td width=\"50%\" valign=\"top\" colspan=2>\n";
    $sToRender .= "\t\t\t<table border=\"0\" width=\"100%\">\n";
    $sToRender .= renderPendingWebDocuments($aWebDocuments) . "\n";    
    $sToRender .= renderPendingCollaborationDocuments($aPendingDocumentList);
    $sToRender .= renderCheckedOutDocuments($aCheckedOutDocumentList) . "\n";
    $sToRender .= renderSubscriptionAlerts($aSubscriptionAlertList) . "\n";
    $sToRender .= "\t\t\t</table>\n";
    $sToRender .= "\t\t</td>\n";
    
    // quick links
    $sToRender .= "\t\t<td width=\"50%\" valign=top>\n";
    $sToRender .= "\t\t\t<table width=\"100%\">\n";
    $sToRender .= renderQuickLinks($aQuickLinks);
    $sToRender .= "\t\t\t</table>\n";
    
    $sToRender .= "\t\t</td>\n";
    $sToRender .= "\t</tr>\n";
    $sToRender .= "</table>\n";  
    
    return $sToRender;
}
?>
