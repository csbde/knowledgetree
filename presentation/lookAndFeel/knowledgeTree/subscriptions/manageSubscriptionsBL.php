<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/subscriptions/Subscription.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("subscriptionUI.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
/**
 * $Id$
 *
 * Manages subscriptions- displays all current subscriptions and allows
 * multiple unsubscribes.
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

// only if we have a valid session
if (checkSession()) {

    $oPatternCustom = & new PatternCustom();
    
    if (isset($fFolderSubscriptionIDs) || isset($fDocumentSubscriptionIDs)) {
        // we've got subscriptions to remove,
        $aFolderSubscriptions = array();
        $aDocumentSubscriptions = array();
        
        for ($i = 0; $i < count($fFolderSubscriptionIDs); $i++) {
            $aFolderSubscriptions[] = & Subscription::get($fFolderSubscriptionIDs[$i], SubscriptionConstants::subscriptionType("FolderSubscription"));
        }
        for ($i = 0; $i < count($fDocumentSubscriptionIDs); $i++) {
            $aDocumentSubscriptions[] = & Subscription::get($fDocumentSubscriptionIDs[$i], SubscriptionConstants::subscriptionType("DocumentSubscription"));
        }        
        
        if (isset($fConfirmed)) {
            // remove subscriptions
            $oSubscriptions = array_merge($aFolderSubscriptions, $aDocumentSubscriptions);
            
            $sErrorMessage = "";
            for ($i = 0; $i < count($oSubscriptions); $i++) {
                if ($oSubscriptions[$i]->delete()) {
                    $default->log->info("manageSubscriptionBL.php removed subscription for userID=$iUserID, subType=$iSubscriptionType, id=$iExternalID");
                } else {
                    // error removing subscription                    
                    $default->log->error("manageSubscriptionBL.php error removing subscription=" . $oSubscriptions[$i]);
                    // add to error message
                    if (strlen($sErrorMessage) > 0) {
                        $sErrorMessage .= ", ";
                    }
                    $sErrorMessage .= $oSubscriptions[$i]->getContentDisplayPath();
                }
            }
            if (strlen($sErrorMessage) > 0) {
                $oPatternCustom->setHtml(renderErrorPage(_("There were errors removing the following subscriptions:") . $sErrorMessage));
            } else {
                // display the manage subscriptions page
                $oPatternCustom->setHtml(renderManagePage());
            }
        } else {
            // display confirmation page
            $oPatternCustom->setHtml(renderMultipleRemoveConfirmationPage($aFolderSubscriptions, $aDocumentSubscriptions));
        }
    } else {
        // display the manage subscriptions page
        $oPatternCustom->setHtml(renderManagePage());
    }

    require_once("../../../webpageTemplate.inc");
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->render();
}
?>
