<?php

// main library routines and defaults
require_once("../../../config/dmsDefaults.php");
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
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation
 */

// -------------------------------
// page start
// -------------------------------

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
    
    //retrive dependant documents
    $aDependantDocuments = $oDashboard->getDependantDocuments();
    
    // generate the html
    $oContent->setHtml(renderPage($aPendingDocumentList, $aCheckedOutDocumentList, $aSubscriptionAlertList, $aQuickLinks, $aPendingWebDocuments, $aDependantDocuments));
    
    // display
    $main->setCentralPayload($oContent);
    $main->render();
}
?>

