<?php

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/subscriptions/Subscription.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");

/**
 * $Id$
 *  
 * Manages subscriptions- displays all current subscriptions and allows
 * multiple unsubscribes.
 *
 * Licensed under the GNU GPL. For full terms see the file DOCS/COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.subscriptions
 */

// -------------------------------
// page start
// -------------------------------

// only if we have a valid session
if (checkSession()) {

    require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
    require_once("subscriptionUI.inc");
    
    require_once("../../../webpageTemplate.inc");
    $oPatternCustom = & new PatternCustom();    
    $oPatternCustom->setHtml(renderManageSubscriptions());            
    $main->setCentralPayload($oPatternCustom);
    $main->setFormAction($_SERVER["PHP_SELF"]);
    $main->render();
}
?>
