<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");

/**
 * $Id$
 *  
 * Clears the subscription alert, and forwards to the content that
 * triggered the alert.
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
 * fSubscriptionID - the subscription to view
 * fSubscriptionType - the subscription type (folder,document) to view
 */

// -------------------------------
// page start
// -------------------------------

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
            $main->setErrorMessage("This subscription alert does not exist (" . $_SESSION["errorMessage"] . ")");
            $oPatternCustom = & new PatternCustom();
            $main->setCentralPayload($oPatternCustom);
            $main->setFormAction($_SERVER["PHP_SELF"]);
            $main->render();
        }
    }
} else {
    // redirect to no permission page
    redirect("$default->uiUrl/noAccess.php");
}
?>
