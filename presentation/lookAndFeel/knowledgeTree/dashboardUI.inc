<?php

require_once("$default->fileSystemRoot/lib/users/User.inc");
/**
 * $Id$
 *
 * Dashboard page html UI building functions.
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
        $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
            
        $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">Pending Web Documents</font></th>\n";
        $sToRender .= "\t\t\t</tr>\n";
        for ($i = 0; $i < count($aPendingDocumentList); $i++) {
            $oWebDocument = $aPendingDocumentList[$i];
            $sToRender .= "\t\t\t<tr>\n";
            $sToRender .= "\t\t\t\t<td colspan=\"2\">" . generateControllerLink("webDocument", "fWebDocumentID=" . $oWebDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/dstatus.gif\" border=\"0\"/>&nbsp;" . $oWebDocument->getDisplayPath()) . "</td>\n";        
            $sToRender .= "\t\t\t</tr>\n";
        }
        return $sToRender;
    } else {
        return "";
    }
}

function renderRestorationRequests($aRestorationRequests) {
    global $default, $dashboard_archived_document_restoration_heading;

    if (count($aRestorationRequests) > 0) {
        $sBgColor = "#9D9D7F"; 
    } else {
        $sBgColor = "#CECEBF";
    }            
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
        
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . $$dashboard_archived_document_restoration_heading . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aRestorationRequests); $i++) {
        $oDocument = Document::get($aRestorationRequests[$i]->getDocumentID());
        if ($oDocument) {
          $sToRender .= "\t\t\t<tr>\n";
          $sToRender .= "\t\t\t\t<td colspan=\"2\">" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/dstatus.gif\" border=\"0\"/>&nbsp;" . $oDocument->getDisplayPath()) . "</td>\n";        
          $sToRender .= "\t\t\t</tr>\n";
        }
    }
    return $sToRender;
}

/**
 * Displays the pending collaboration documents
 *
 * @param array of pending collaboration documents
 */
function renderPendingCollaborationDocuments($aPendingDocumentList) {
    global $default, $dashboard_pending_documents_heading;

    if (count($aPendingDocumentList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
        
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . $dashboard_pending_documents_heading . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aPendingDocumentList); $i++) {
        $oDocument = $aPendingDocumentList[$i];
        if ($oDocument) {
	        $sToRender .= "\t\t\t<tr>\n";
	        $sToRender .= "\t\t\t\t<td colspan=\"2\">" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID() . "&fShowSection=documentRouting", "<img src=\"$default->graphicsUrl/widgets/dstatus.gif\" border=\"0\"/>&nbsp;" . $oDocument->getDisplayPath()) . "</td>\n";        
	        $sToRender .= "\t\t\t</tr>\n";
        }
    }    
    return $sToRender;
}

/**
 * Displays the checked out documents
 *
 * @param array of checked out documents
 */
function renderCheckedOutDocuments($aCheckedOutDocumentList) {
    global $default, $dashboard_checked_out_documents_heading;
    if (count($aCheckedOutDocumentList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";   
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . $dashboard_checked_out_documents_heading . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    
    if (count($aCheckedOutDocumentList) > 0) {
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<th align=\"left\" width=\"66%\" class=\"sectionColumns\">";
        $sToRender .= "Title";
        $sToRender .= "</th>\n";
        $sToRender .= "\t\t\t\t<th align=\"right\" width=\"33%\" class=\"sectionColumns\">";
        $sToRender .= "Days";
        $sToRender .= "\t\t\t\t</th>\n";
        $sToRender .= "\t\t\t</tr>\n";
    }
    
    for ($i = 0; $i < count($aCheckedOutDocumentList); $i++) {
        $oDocument = $aCheckedOutDocumentList[$i];
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<td>" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/dstatus.gif\" border=\"0\"/>&nbsp;" . $oDocument->getDisplayPath()) . "</td>\n";
        $sToRender .= "\t\t\t\t<td align=right>" . $oDocument->getDaysSinceLastModified() . "</td>\n";
        $sToRender .= "\t\t\t</tr>\n";
    }
    return $sToRender;
}

/**
 * Displays the subscription alerts
 *
 * @param array of subscription alerts
 */
function renderSubscriptionAlerts($aSubscriptionAlertList) {
    global $default, $dashboard_subscription_alerts_heading;
    if (count($aSubscriptionAlertList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . $dashboard_subscription_alerts_heading . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aSubscriptionAlertList); $i++) {
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<td colspan=\"2\">" . $aSubscriptionAlertList[$i]->getAlertLink() . "</td>\n";
        $sToRender .= "\t\t\t</tr>\n";
    }    
    return $sToRender;
}

function renderDependantDocuments($aDependantDocumentList) {
	global $default, $dashboard_dependant_documents_heading;
    if (count($aDependantDocumentList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";   
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . $dashboard_dependant_documents_heading . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    
    if (count($aDependantDocumentList) > 0) {
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<th align=\"left\" width=\"33%\" class=\"sectionColumns\">";
        $sToRender .= "Title";
        $sToRender .= "</th>\n";
        $sToRender .= "\t\t\t\t<th align=\"left\" width=\"66%\" class=\"sectionColumns\">";
        $sToRender .= "Template document";
        $sToRender .= "\t\t\t\t</th>\n";
        $sToRender .= "\t\t\t</tr>\n";
    }
    
    for ($i = 0; $i < count($aDependantDocumentList); $i++) {
        $oDependantDocument = $aDependantDocumentList[$i];
        $oTemplateDocument = Document::get($oDependantDocument->getTemplateDocumentID());                
        
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<td>" . generateControllerLink("addDocument", "fFolderID=" . User::getUserRootFolderID() . "&fDependantDocumentID=" . $oDependantDocument->getID(), $oDependantDocument->getDocumentTitle()) . "</td>";
        if (!($oTemplateDocument === false)) {        
	        $sToRender .= "<td>" . generateControllerLink("viewDocument", "fDocumentID=" . $oTemplateDocument->getID(), "<img src=\"$default->graphicsUrl/widgets/dstatus.gif\" border=\"0\"/>&nbsp;" . $oTemplateDocument->getName()) . "</td>\n";	     
        } else {
        	$sToRender .= "<td>No template specified</td>\n";
        }
        $sToRender .= "\t\t\t</tr>\n";        
    }
    return $sToRender;	 
	
}

/**
 * Displays the quicklinks
 */
function renderQuickLinks($aQuickLinks) {
    global $default, $dashboard_quick_links_heading;
    $sToRender .= "\t\t\t<tr align=\"left\" bgcolor=\"#9D9D7F\">\n";
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\"><font color=\"ffffff\">" . $dashboard_quick_links_heading . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aQuickLinks); $i++) {
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<td colspan=\"2\"><a href=\"" . $aQuickLinks[$i]->getUrl() . "\" target=\"_new\"><img src=\"$default->graphicsUrl/widgets/qlink.gif\" border=\"0\"/>" . $aQuickLinks[$i]->getName() . "</a></td>\n";
        $sToRender .= "\t\t\t</tr>\n";
    }
    return $sToRender;
}

/**
 * Displays the dashboard news items
 *
 */
function renderDashboardNews() {
	global $default;
	
	// retrieve all news items
	$aDashboardNews = DashboardNews::getList("active=1");
	
	if ($default->ScrollingNews) {
	  
	  $sToRender .= "<MARQUEE onmouseover=this.stop() onmouseout=this.start() scrollAmount=1 direction=up width='100%' height='100'>";
	$sToRender .= "\t\t\t<table border=\"0\" width=\"100%\">\n";

	  // For each news ...
	  foreach($aDashboardNews as $key => $oMainDashboardNews) {
	    
	    $sToRender .= "\t\t\t\t<tr><td>". $oMainDashboardNews->getImageLink() . "\t<strong><a href=\"#\" onClick=\"javascript:window.open('" . generateControllerUrl("viewNewsItem", "fNewsID=" . $oMainDashboardNews->getID()) . "', 'newwindow', config='height=200,width=300,left=0,top=0, toolbar=no, menubar=no, scrollbars=yes, resizable=no,location=no, directories=no, status=no');return false;\">".$oMainDashboardNews->getSynopsis() . "...</strong></a></td></tr>\n";
	    
	  }

	  $sToRender .= "\t\t\t</table>\n";
	  $sToRender .= "</MARQUEE>";
	
	} else {
	  
	  $sToRender .= "\t\t\t<table border=\"0\" width=\"100%\">\n";
	  
	  // the main news item
	  $oMainDashboardNews = $aDashboardNews[0];
	  if ($oMainDashboardNews) {
	    $sToRender .= "\t\t\t\t<tr><td><strong>" . $oMainDashboardNews->getSynopsis() . "</strong></td></tr>\n";	
	    $sToRender .= "\t\t\t\t<tr><td>" . $oMainDashboardNews->getImageLink() . "</td></tr>\n";
	    $sToRender .= "\t\t\t\t<tr><td>" . $oMainDashboardNews->getBodyFragment() . "..... <a href=\"#\" onClick=\"javascript:window.open('" . generateControllerUrl("viewNewsItem", "fNewsID=" . $oMainDashboardNews->getID()) . "', 'newwindow', config='height=200,width=300,left=0,top=0, toolbar=no, menubar=no, scrollbars=yes, resizable=no,location=no, directories=no, status=no');return false;\">more</a></td></tr>\n";
	  }
	  $sToRender .= "\t\t\t</table>\n";
	  
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
function renderPage($aPendingDocumentList, $aCheckedOutDocumentList, $aSubscriptionAlertList, $aQuickLinks, $aWebDocuments, $aDependantDocuments, $aRestorationRequests) {
    global $default, $dashboard_welcome;
    
    $sToRender  = "<table border=\"0\" width=\"600\" >\n";
	$sToRender .= "\t<tr valign=\"top\">\n";
    $sToRender .= "\t\t<td><img src=\"$default->graphicsUrl/welcome.gif\" border=\"0\"/></td>\n";
    
    // some arb welcoming text goes here
    $oUser = & User::get($_SESSION["userID"]);
    $sUserName = $oUser->getName();    
    $sToRender .= "\t\t<td>" . getTranslatedString($dashboard_welcome, array(0 => (strlen($sUserName) > 0 ? " " . $sUserName : ""), 1 => lookupField($default->organisations_table, "name", "id", $default->organisationID))) . "</td>\n";    
        
    // dashboard news
    $sToRender .= "\t\t<td>\n";
    $sToRender .= renderDashboardNews();
    $sToRender .= "\t\t</td>\n";    
    $sToRender .= "\t</tr>\n";    
    
    // dashboard alerts
    $sToRender .= "\t<tr>\n";
    $sToRender .= "\t\t<td width=\"50%\" valign=\"top\" colspan=\"2\">\n";
    $sToRender .= "\t\t\t<table border=\"0\" width=\"100%\">\n";
    $sToRender .= renderPendingWebDocuments($aWebDocuments) . "\n";
    $sToRender .= renderRestorationRequests($aRestorationRequests) . "\n";
    $sToRender .= renderPendingCollaborationDocuments($aPendingDocumentList);
    $sToRender .= renderCheckedOutDocuments($aCheckedOutDocumentList) . "\n";
    $sToRender .= renderSubscriptionAlerts($aSubscriptionAlertList) . "\n";
    $sToRender .= renderDependantDocuments($aDependantDocuments) . "\n";
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

/**
 * Displays a news item
 */
function renderNewsItemPage($oDashboardNews) {
	global $default;
	     	
	$sToRender .= "<html>\n";
	$sToRender .= "<head>\n";
	$sToRender .= "<meta http-equiv=\"refresh\" content=\"" . ($default->sessionTimeout+3) . "\">\n";
	$sToRender .= "<link rel=\"SHORTCUT ICON\" href=\"$default->graphicsUrl/tree.ico\">\n";
	$sToRender .= "<link rel=\"stylesheet\" href=\"$default->uiUrl/stylesheet.php\">\n";
	$sToRender .= "</head>\n";
	$sToRender .= "<body marginleft=\"0\" marginheight=\"0\">\n";
	$sToRender .= "\t\t\t<table border=\"0\" width=\"100%\">\n";
	$sToRender .= "\t\t\t\t<tr><td><strong>" . $oDashboardNews->getSynopsis() . "</strong></td></tr>\n";	
	$sToRender .= "\t\t\t\t<tr><td>" . $oDashboardNews->getImageLink() . "</td></tr>\n";
	$sToRender .= "\t\t\t\t<tr><td>" . $oDashboardNews->getBody() . "</td></tr>\n";
	$sToRender .= "<tr><td><a href=\"javascript:window.close()\">close</a></td></tr>\n";
	$sToRender .= "</table>";
	$sToRender .= "</body>";
	$sToRender .= "</html>";
	
    return $sToRender;
}
?>
