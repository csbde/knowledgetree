<?php

// main library routines and defaults
require_once("../config/dmsDefaults.php");
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
    require_once("webpageTemplate.inc");

    
    $sHtml = startTable("0", "100%") .
                 // pending documents
                 startTableRowCell() .
                     startTable("0", "100%") .
                         tableRow("left", "#996600", tableHeading("sectionHeading", 3, "Pending Documents")) . 
                         tableRow("", "", pendingDocumentsHeaders());
                         // FIXME: replace with the real method when its implemented
                         // something like:
                         //    DocumentManager::getPendingDocuments();                         
                         $aPendingDocumentList = getPendingDocuments($_SESSION["userID"]);
                         for ($i = 0; $i < count($aPendingDocumentList); $i++) {
                             $row = tableData($aPendingDocumentList[$i]->getTitleLink()) .
                                    tableData($aPendingDocumentList[$i]->getStatus()) .
                                    tableData($aPendingDocumentList[$i]->getDays());
    $sHtml = $sHtml .    tableRow("", "", $row); 
                         }
    $sHtml = $sHtml . 
                     stopTable() . 
                 endTableRowCell() .
                 // checked out documents
                 startTableRowCell() .
                     startTable("0", "100%") .
                         tableRow("left", "#996600", tableHeading("sectionHeading", 2, "Checked Out Documents")) . 
                         tableRow("", "", checkedOutDocumentsHeaders());
                         // FIXME: replace with the real method when its implemented
                         // something like:
                         //    DocumentManager::getCheckoutDocuments();                         
                         $aCheckedOutDocumentList = getCheckedoutDocuments($_SESSION["userID"]);
                         for ($i = 0; $i < count($aCheckedOutDocumentList); $i++) {
                             $row = tableData($aCheckedOutDocumentList[$i]->getTitleLink()) .
                                    tableData($aCheckedOutDocumentList[$i]->getDays());
    $sHtml = $sHtml .    tableRow("", "", $row); 
                         }
    $sHtml = $sHtml . 
                     stopTable() . 
                 endTableRowCell() .
                 
                 // subscription alerts
                 startTableRowCell() .
                     startTable("0", "100%") .
                         tableRow("left", "#996600", tableHeading("sectionHeading", 3, "Subscriptions Alerts")) . 
                         tableRow("", "", subscriptionDocumentsHeaders());
                         // FIXME: replace with the real method when its implemented
                         // something like:
                         //    SubscriptionManager::getAlerts();
                         
                         $aSubscriptionList = getSubscriptionDocuments($_SESSION["userID"]);
                         for ($i = 0; $i < count($aSubscriptionList); $i++) {
                             $row = tableData($aSubscriptionList[$i]->getTitleLink()) .
                                    tableData($aSubscriptionList[$i]->getStatus()) .
                                    tableData($aSubscriptionList[$i]->getDays());
    $sHtml = $sHtml .    tableRow("", "", $row); 
                         }
    $sHtml = $sHtml . 
                     stopTable() . 
                 endTableRowCell() .
                 
             stopTable();
    
    $oContent = new PatternCustom();
    $oContent->setHtml($sHtml);
    //$oContent->setHtml(getPage());
    
    $main->setCentralPayload($oContent);
    $main->render();
        
} else {
    // redirect to no permission page
    redirect("$default->uiUrl/noAccess.php");
}
?>

