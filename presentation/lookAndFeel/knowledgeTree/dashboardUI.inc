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
            
    	$sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . _("Pending Web Documents") . "</font></th>\n";			        
        $sToRender .= "\t\t\t</tr>\n";
        for ($i = 0; $i < count($aPendingDocumentList); $i++) {
            $oWebDocument = $aPendingDocumentList[$i];
            $sToRender .= "\t\t\t<tr>\n";
            $sToRender .= "\t\t\t\t<td colspan=\"2\">" . generateControllerLink("webDocument", "fWebDocumentID=" . $oWebDocument->getID(), "<img src=\"" . imgSrc("widgets/dstatus.gif") . "\" border=\"0\"/>&nbsp;" . $oWebDocument->getDisplayPath()) . "</td>\n";        
            $sToRender .= "\t\t\t</tr>\n";
        }
        return $sToRender;
    } else {
        return "";
    }
}

function renderRestorationRequests($aRestorationRequests) {
    global $default;

    if (count($aRestorationRequests) > 0) {
        $sBgColor = "#9D9D7F"; 
    } else {
        $sBgColor = "#CECEBF";
    }            
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
        
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . _("Archived Document Restoration Requests") . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aRestorationRequests); $i++) {
        $oDocument = Document::get($aRestorationRequests[$i]->getDocumentID());
        if ($oDocument) {
          $sToRender .= "\t\t\t<tr>\n";
          $sToRender .= "\t\t\t\t<td colspan=\"2\">" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), "<img src=\"" . imgSrc("widgets/dstatus.gif") . "\" border=\"0\"/>&nbsp;" . $oDocument->getDisplayPath()) . "</td>\n";        
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
        
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . _("Pending Documents") . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aPendingDocumentList); $i++) {
        $oDocument = $aPendingDocumentList[$i];
        if ($oDocument) {
	        $sToRender .= "\t\t\t<tr>\n";
	        $sToRender .= "\t\t\t\t<td colspan=\"2\">" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID() . "&fShowSection=documentRouting", "<img src=\"" . imgSrc("widgets/dstatus.gif") . "\" border=\"0\"/>&nbsp;" . $oDocument->getDisplayPath()) . "</td>\n";        
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
    global $default;
    if (count($aCheckedOutDocumentList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";   
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . _("Checked Out Documents") . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    
    if (count($aCheckedOutDocumentList) > 0) {
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<th align=\"left\" width=\"66%\" class=\"sectionColumns\">";
        $sToRender .= _("Title");
        $sToRender .= "</th>\n";
        $sToRender .= "\t\t\t\t<th align=\"right\" width=\"33%\" class=\"sectionColumns\">";
        $sToRender .= _("Days");
        $sToRender .= "\t\t\t\t</th>\n";
        $sToRender .= "\t\t\t</tr>\n";
    }
    
    for ($i = 0; $i < count($aCheckedOutDocumentList); $i++) {
        $oDocument = $aCheckedOutDocumentList[$i];
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<td>" . generateControllerLink("viewDocument", "fDocumentID=" . $oDocument->getID(), "<img src=\"" . imgSrc("widgets/dstatus.gif") . "\" border=\"0\"/>&nbsp;" . $oDocument->getDisplayPath()) . "</td>\n";
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
    global $default;
    if (count($aSubscriptionAlertList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . _("Subscription Alerts") . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aSubscriptionAlertList); $i++) {
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<td colspan=\"2\">" . $aSubscriptionAlertList[$i]->getAlertLink() . "</td>\n";
        $sToRender .= "\t\t\t</tr>\n";
    }    
    return $sToRender;
}

function renderDependantDocuments($aDependantDocumentList) {
	global $default;
    if (count($aDependantDocumentList) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";   
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"><font color=\"ffffff\">" . _("Dependant Documents") . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    
    if (count($aDependantDocumentList) > 0) {
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<th align=\"left\" width=\"33%\" class=\"sectionColumns\">";
        $sToRender .= _("Title");
        $sToRender .= "</th>\n";
        $sToRender .= "\t\t\t\t<th align=\"left\" width=\"66%\" class=\"sectionColumns\">";
        $sToRender .= _("Template document");
        $sToRender .= "\t\t\t\t</th>\n";
        $sToRender .= "\t\t\t</tr>\n";
    }
    
    for ($i = 0; $i < count($aDependantDocumentList); $i++) {
        $oDependantDocument = $aDependantDocumentList[$i];
        $oTemplateDocument = Document::get($oDependantDocument->getTemplateDocumentID());                
        
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<td>" . generateControllerLink("addDocument", "fFolderID=" . User::getUserRootFolderID() . "&fDependantDocumentID=" . $oDependantDocument->getID(), $oDependantDocument->getDocumentTitle()) . "</td>";
        if (!($oTemplateDocument === false)) {        
	        $sToRender .= "<td>" . generateControllerLink("viewDocument", "fDocumentID=" . $oTemplateDocument->getID(), "<img src=\"" . imgSrc("widgets/dstatus.gif") . "\" border=\"0\"/>&nbsp;" . $oTemplateDocument->getName()) . "</td>\n";	     
        } else {
        	$sToRender .= "<td>" . _("No template specified") . "</td>\n";
        }
        $sToRender .= "\t\t\t</tr>\n";        
    }
    return $sToRender;	 
	
}

/**
 * Displays the quicklinks
 */
function renderQuickLinks($aQuickLinks) {
    global $default;
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"#9D9D7F\">\n";
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\"><font color=\"ffffff\">" . _("Quick Links") . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aQuickLinks); $i++) {
        $sToRender .= "\t\t\t<tr>\n";
        $sToRender .= "\t\t\t\t<td colspan=\"2\"><a href=\"" . $aQuickLinks[$i]->getUrl() . "\" target=\"_new\"><img src=\"" . imgSrc("widgets/qlink.gif") . "\" border=\"0\"/>" . $aQuickLinks[$i]->getName() . "</a></td>\n";
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
	$aDashboardNews = DashboardNews::getList("active=1");/*ok*/

    $sToRender = "";
	
	if ($default->scrollingNews) {
	  
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


function renderPublicFolders($aPublicFolders) {
	global $default;
    if (count($aPublicFolders) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"> <img src=$default->graphicsUrl/widgets/dfolder.gif> <font color=\"ffffff\">" . _("Public Folders") . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aPublicFolders); $i++) {
        $sToRender .= "\t\t\t<tr>\n";
        
        $sToRender .= "\t\t\t\t<td colspan=\"2\">";
        $sToRender .= "<a href='$default->rootUrl/control.php?action=browse&fFolderID=". $aPublicFolders[$i]->getID()."'>";
        
        $oUnit = Unit::get($aPublicFolders[$i]->getUnitID());        
        if ($oUnit) {
        	$sToRender .= $aPublicFolders[$i]->getName() . " (<i>Unit: ";
	        $sToRender .= $oUnit->getName() . "</i>)</a></td>\n";
        } else {
        	$sToRender .= $aPublicFolders[$i]->getName() . " (<i>No Unit</i>)</a></td>\n";        	
        }
        
        $sToRender .= "\t\t\t</tr>\n";
    }    
    return $sToRender;
}

function renderBrowseableFolders($aBrowseableFolders) {
	global $default;
    if (count($aBrowseableFolders) > 0) {
        $sBgColor = "#9D9D7F";
    } else {
        $sBgColor = "#CECEBF";
    }    
    $sToRender  = "\t\t\t<tr align=\"left\" bgcolor=\"$sBgColor\">\n";
    $sToRender .= "\t\t\t\t<th class=\"sectionHeading\" colspan=\"2\"> <img src=$default->graphicsUrl/widgets/dfolder.gif> <font color=\"ffffff\">" . _("Browseable Folders") . "</font></th>\n";
    $sToRender .= "\t\t\t</tr>\n";
    for ($i = 0; $i < count($aBrowseableFolders); $i++) {
        $sToRender .= "\t\t\t<tr>\n";
        
        $sToRender .= "\t\t\t\t<td colspan=\"2\">";
        $sToRender .= "<a href='$default->rootUrl/control.php?action=browse&fFolderID=". $aBrowseableFolders[$i]->getID()."'>";

        $oUnit = Unit::get($aBrowseableFolders[$i]->getUnitID());        
        if ($oUnit) {
        	$sToRender .= $aBrowseableFolders[$i]->getName() . " (<i>Unit: ";
	        $sToRender .= $oUnit->getName() . "</i>)</a></td>\n";
        } else {
        	$sToRender .= $aBrowseableFolders[$i]->getName() . " (<i>No Unit</i>)</a></td>\n";        	
        }

        $sToRender .= "\t\t\t</tr>\n";
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
function renderPage($aPendingDocumentList, $aCheckedOutDocumentList, $aSubscriptionAlertList, $aQuickLinks, $aWebDocuments, $aDependantDocuments, $aRestorationRequests, $aBrowseableFolders, $aPublicFolders) {
    global $default;
    
    $sToRender  = "<table border=\"0\" width=\"600\" >\n";
	$sToRender .= "\t<tr valign=\"top\">\n";
    $sToRender .= "\t\t<td><img src=\"" . imgSrc("welcome.gif") . "\" border=\"0\"/></td>\n";
    
    // some arb welcoming text goes here
    $oUser = & User::get($_SESSION["userID"]);
    $sUserName = $oUser->getName();
    $sToRender .= "\t\t<td>" . sprintf(_('Hi %1$s, welcome back to the %2$s DMS, part of the Knowledge Tree'), 
    									(strlen($sUserName) > 0 ? " " . $sUserName : ""), 
    									lookupField($default->organisations_table, "name", "id", $default->organisationID)) . "</td>\n";
        
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
    $sToRender .= "<td><br></td>\n";
    $sToRender .= renderBrowseableFolders($aBrowseableFolders) . "\n";
    $sToRender .= renderPublicFolders($aPublicFolders) . "\n";
    $sToRender .= "\t\t\t</table>\n";
    $sToRender .= "\t\t</td>\n";
    
    // quick links
    $sToRender .= "\t\t<td width=\"50%\" valign=top>\n";
    $sToRender .= "\t\t\t<table width=\"100%\">\n";
    $sToRender .= renderQuickLinks($aQuickLinks);
    $sToRender .= "</td>\n";
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
	     	
	$sToRender  = "<html>\n";
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
