<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
/**
 * $Id$
 *
 * Clears the subscription alert, and forwards to the content that
 * triggered the alert.
 *
 * Querystring variables
 * ---------------------
 * fSubscriptionID - the subscription to view
 * fSubscriptionType - the subscription type (folder,document) to view
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

    $default->log->debug("subID=$fSubscriptionID, type=$fSubscriptionType");
    // retrieve variables
    if ((!$fSubscriptionID) || (!$fSubscriptionType)) {
        require_once("../../../webpageTemplate.inc");
        $main->setErrorMessage("You have not selected a subscription alert");
        $oPatternCustom = & new PatternCustom();
        $main->setCentralPayload($oPatternCustom);
        $main->setFormAction($_SERVER["PHP_SELF"]);
        $main->render();
    } else {

        // instantiate the subscription manager
        $oSubscriptionManager = new SubscriptionManager();
        // clear the subscription alert and return the url to redirect to
        $sContentUrl = SubscriptionManager::viewSubscription($fSubscriptionID, $fSubscriptionType);
        if ($sContentUrl) {
            $default->log->debug("retrieved $sContentUrl from viewSubscription");
            // now redirect
            redirect($sContentUrl);
        } else {
            // viewSubscription called failed
            require_once("../../../webpageTemplate.inc");
            $main->setErrorMessage("This subscription alert does not exist.");
            $oPatternCustom = & new PatternCustom();
            $main->setCentralPayload($oPatternCustom);
            $main->setFormAction($_SERVER["PHP_SELF"]);
            $main->render();
        }
    }
}
?>
