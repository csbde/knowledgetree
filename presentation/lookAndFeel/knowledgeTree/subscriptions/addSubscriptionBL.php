<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/subscriptions/Subscription.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");

/**
 * $Id$
 *  
 * Adds a document or folder subscription for a user.
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

/**
 * Checks if the user has read permission on the subscription content
 *
 * @param integer the id of the subscription content
 * @param integer the subscription type
 */
function checkPermission($iExternalID, $iSubscriptionType) {
    if  ($iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
        return Permission::userHasFolderReadPermission($iExternalID);
    } else {
        return Permission::userHasDocumentReadPermission($iExternalID);
    }
}
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
        
        if (checkPermission($iExternalID, $iSubscriptionType)) {
        
            if (!Subscription::exists($iUserID, $iExternalID, $iSubscriptionType)) {
                $oSubscription = new Subscription($iUserID, $iExternalID, $iSubscriptionType);            
                // if we've confirmed the subscription
                if ($fConfirmed) {
                    // add it
                    if ($oSubscription->create()) {
                        $default->log->info("addSubscriptionBL.php added subscription for userID=$iUserID, subType=$iSubscriptionType, id=$iExternalID");
                        // redirect to viewFolder or viewDocument
                        $default->log->info("redirecting to " . $oSubscription->getContentUrl());
    
                        redirect($oSubscription->getContentUrl());
                    } else {
                        // error creating subscription
                        $default->log->error("addSubscriptionBL.php error creating subscription for userID=$iUserID, subType=$iSubscriptionType, id=$iExternalID");                
                        $oPatternCustom->setHtml(renderErrorPage("An error occurred while creating this subscription"));
                    }
                } else {
                    // ask for confirmation
                    $oPatternCustom->setHtml(renderAddConfirmationPage($oSubscription));
                }
            } else {
                // you're already subscribed
                $oPatternCustom->setHtml(renderErrorPage("You are already subscribed to the " . ($fFolderID ? "folder '" . Folder::getFolderName($fFolderID) . "'" : "document '" . Document::getDocumentName($fDocumentID) . "'")));
            }
            
            require_once("../../../webpageTemplate.inc");
            $main->setCentralPayload($oPatternCustom);
            $main->setFormAction($_SERVER["PHP_SELF"]);
            $main->render();
            
        } else {
            // no permission
            $oPatternCustom->setHtml(renderErrorPage("You don't have permission to subscribe to this folder or document"));
            require_once("../../../webpageTemplate.inc");
            $main->setCentralPayload($oPatternCustom);
            $main->render();        
        }        
    } else {
        // neither document or folder chosen
        $oPatternCustom->setHtml(renderErrorPage("You haven't chosen a folder or a document to subscribe to"));
        require_once("../../../webpageTemplate.inc");
        $main->setCentralPayload($oPatternCustom);
        $main->render();
    }
}
?>
