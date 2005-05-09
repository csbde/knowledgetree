<?php

// main library routines and defaults
require_once("../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/unitmanagement/Unit.inc");
require_once("$default->fileSystemRoot/lib/dashboard/Dashboard.inc");
require_once("$default->fileSystemRoot/lib/dashboard/DashboardNews.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->uiDirectory/dashboardUI.inc");
/**
 * $Id$
 *
 * Main dashboard page -- This page is presented to the user after login.
 * It contains a high level overview of the users subscriptions, checked out 
 * document, pending approval routing documents, etc. 
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

if (checkSession()) {
    // include the page template (with navbar)
    require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
    
    // instantiate my content pattern    
    $oContent = new PatternCustom();
    
    // construct the dashboard object
    $oDashboard = new Dashboard($_SESSION["userID"]);
    
    // retrieve collaboration pending documents for this user
    $aPendingDocumentList = $oDashboard->getPendingCollaborationDocuments();
    
    // retrieve checked out documents for this user                         
    $aCheckedOutDocumentList = $oDashboard->getCheckedOutDocuments();

    // retrieve subscription alerts for this user
    $aSubscriptionAlertList = $oDashboard->getSubscriptionAlerts();
    
    // retrieve quicklinks
    $aQuickLinks = $oDashboard->getQuickLinks();
    
    // retrieve pending web documents
    $aPendingWebDocuments = $oDashboard->getPendingWebDocuments();
    
    // retrieve dependant documents
    $aDependantDocuments = $oDashboard->getDependantDocuments();

    // retrieve archive restoration requests
    $aRestorationRequests = $oDashboard->getArchiveRestorationRequestDocuments();
    
	// retrieve public folders
	$aPublicFolders = $oDashboard->getPublicFolders();

	// retrieve browseable folders
	$aBrowseableFolders = $oDashboard->getBrowseableFolders();		

	// generate the html
    $oContent->setHtml(renderPage($aPendingDocumentList, $aCheckedOutDocumentList, $aSubscriptionAlertList, $aQuickLinks, $aPendingWebDocuments, $aDependantDocuments, $aRestorationRequests, $aBrowseableFolders, $aPublicFolders));
    
    // display
    $main->setCentralPayload($oContent);
    $main->render();
}
?>

