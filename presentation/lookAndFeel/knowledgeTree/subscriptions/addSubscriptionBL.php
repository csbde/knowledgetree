<?php

require_once("../../../../config/dmsDefaults.php");

KTUtil::extractGPC('fConfirmed', 'fDocumentID', 'fFolderID');

require_once("$default->fileSystemRoot/lib/subscriptions/Subscription.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
/**
 * $Id$
 *
 * Adds a document or folder subscription for a user.
 *
 * Querystring variables
 * ---------------------
 * fFolderID - the folder to subscribe the current user to (optional)
 * fDocumentID - the document to subscribe the current user to (optional)
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
 * @package subscriptions
 */

/**
 * Checks if the user has read permission on the subscription content
 *
 * @param integer the id of the subscription content
 * @param integer the subscription type
 */
function checkPermission($iExternalID, $iSubscriptionType) {
	if  ($iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
		$oFolder = Folder::get($iExternalID);
        return Permission::userHasFolderReadPermission($oFolder);
    } else {
    	$oDocument = Document::get($iExternalID);
        return Permission::userHasDocumentReadPermission($oDocument);
    }
}
// only if we have a valid session
if (checkSession()) {

    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("subscriptionUI.inc");
	require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
	require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
    
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
                        $oPatternCustom->setHtml(renderErrorPage(_("An error occurred while creating this subscription")));
                    }
                } else {
                    // ask for confirmation
                    $oPatternCustom->setHtml(renderAddConfirmationPage($oSubscription));
                }
            } else {
                // you're already subscribed
                $oPatternCustom->setHtml(renderErrorPage(_("You are already subscribed to this folder or document")));
            }
            
            require_once("../../../webpageTemplate.inc");
            $main->setCentralPayload($oPatternCustom);
            $main->setFormAction($_SERVER["PHP_SELF"]);
            $main->render();
            
        } else {
            // no permission
            $oPatternCustom->setHtml(renderErrorPage(_("You don't have permission to subscribe to this folder or document")));
            require_once("../../../webpageTemplate.inc");
            $main->setCentralPayload($oPatternCustom);
            $main->render();        
        }        
    } else {
        // neither document or folder chosen
        $oPatternCustom->setHtml(renderErrorPage(_("You haven't chosen a folder or a document to subscribe to")));
        require_once("../../../webpageTemplate.inc");
        $main->setCentralPayload($oPatternCustom);
        $main->render();
    }
}
?>
