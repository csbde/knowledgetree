<?php

// main library routines and defaults
require_once("../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
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
    
    // retrieve collaboration pending documents for this user
    $aPendingDocumentList = getPendingDocuments($_SESSION["userID"]);
    // retrieve checked out documents for this user                         
    $aCheckedOutDocumentList = getCheckedoutDocuments($_SESSION["userID"]);
    // retrieve subscription alerts for this user
    $aSubscriptionAlertList = SubscriptionManager::listSubscriptionAlerts($_SESSION["userID"]);
    
    // generate the html
    $oContent->setHtml(renderPage($aPendingDocumentList, $aCheckedOutDocumentList, $aSubscriptionAlertList));
    
    // display
    $main->setCentralPayload($oContent);
    $main->render();
}
?>

