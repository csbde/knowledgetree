<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/subscriptions/Subscription.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");

/**
 * $Id$
 *  
 * Removes a document or folder subscription for a user.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.subscriptions
 */

/*
 * Querystring variables
 * ---------------------
 * fFolderID - the folder to subscribe the current user to (optional)
 * fDocumentID - the document to subscribe the current user to (optional)
 */

// -------------------------------
// page start
// -------------------------------

// only if we have a valid session
if (checkSession()) {

    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("subscriptionUI.inc");
    
    $oPatternCustom = & new PatternCustom();
    
    // retrieve variables
    if ($fFolderID || $fDocumentID) {
        $iUserID = $_SESSION["userID"];
        // if fFolderID was passed in then its a folder subscription
        if ($fFolderID) {
            $iExternalID = $fFolderID;
            $iSubscriptionType = SubscriptionConstants::subscriptionType("FolderSubscription");
        // or a document subscription
        } else if ($fDocumentID) {
            $iExternalID = $fDocumentID;
            $iSubscriptionType = SubscriptionConstants::subscriptionType("DocumentSubscription");
        }
        if (Subscription::exists($iUserID, $iExternalID, $iSubscriptionType)) {
            $oSubscription = & Subscription::getByIDs($iUserID, $iExternalID, $iSubscriptionType);
            // if we've confirmed the deletion
            if ($fConfirmed) {
                // remove it
                if ($oSubscription->delete()) {
                    $default->log->info("removeSubscriptionBL.php removed subscription for userID=$iUserID, subType=$iSubscriptionType, id=$iExternalID");
                    // redirect to viewFolder or viewDocument
                    redirect($oSubscription->getContentUrl());
                } else {
                    // error removing subscription
                    $default->log->error("removeSubscriptionBL.php error removing subscription for userID=$iUserID, subType=$iSubscriptionType, id=$iExternalID");                
                    $oPatternCustom->setHtml(renderErrorPage("An error occurred while removing this subscription (" . $_SESSION["errorMessage"] . ")" ));
                }
            } else {
                // ask for confirmation
                $default->log->info("sub=" . arrayToString($oSubscription));
                $oPatternCustom->setHtml(renderSubscriptionRemoveConfirmationPage($oSubscription));
            }
        } else {
            // you're not subscribed
            $default->log->error("removeSubscriptionBL.php not subscribed ($iUserID, $iExternalID, $iSubscriptionType)");
            $oPatternCustom->setHtml(renderErrorPage("You aren't subscribed to the " . ($fFolderID ? "folder '" . Folder::getFolderName($fFolderID) . "'" : "document '" . Document::getDocumentName($fDocumentID) . "'")));
        }
        
        require_once("../../../webpageTemplate.inc");
        $main->setCentralPayload($oPatternCustom);
        $main->setFormAction($_SERVER["PHP_SELF"]);
        $main->render();
        
    } else {
        // neither document or folder chosen
        $oPatternCustom->setHtml(renderErrorPage("You haven't chosen a folder or a document to unsubscribe from"));
        require_once("../../../webpageTemplate.inc");
        $main->setCentralPayload($oPatternCustom);
        $main->render();
    }
}
?>
