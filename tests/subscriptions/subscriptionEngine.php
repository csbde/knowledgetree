<?php
include("../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->owl_fs_root/lib/subscriptions/SubscriptionManager.inc");
    require_once("$default->owl_fs_root/lib/subscriptions/SubscriptionEngine.inc");
    $oSubscriptionManager = new SubscriptionManager();

    echo "<ul><li><b>create some test subscriptions for subscription firing testing</b></li>";

    // add some subscriptions
    $iFolderID = 1;
    echo "<li>Creating folder subscriptions</li><ul>";
    for ($tmpUserID = 1; $tmpUserID<=5; $tmpUserID++) {

        if ($oSubscriptionManager->createSubscription($tmpUserID, $iFolderID, SubscriptionConstants::subscriptionType("FolderSubscription"))) {
            echo "<li>created folder subscription folderID=$iFolderID for userID=$tmpUserID</li>";
        } else {
            echo "<li>folder subscription creation failed(" . $_SESSION["errorMessage"] . "): folderID=$iFolderID for userID=$tmpUserID</li>";
        }
    }
    echo "</ul>";
    $iDocumentID = 5;
    echo "<li>Creating document subscriptions</li><ul>";
    for ($tmpUserID = 6; $tmpUserID<=10; $tmpUserID++) {
        if ($oSubscriptionManager->createSubscription($tmpUserID, $iDocumentID, SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
            echo "<li>created document subscription documentID=$iDocumentID for userID=$tmpUserID</li>";
        } else {
            echo "<li>document subscription creation failed(" . $_SESSION["errorMessage"] . "): documentID=$iDocumentID for userID=$tmpUserID</li>";
        }
    }
    echo "</ul>";

    // test subscription firing
    echo "<li><b>Testing subscription firing</b></li><ul>";
    require_once("../../phpSniff/phpTimer.class.php");
    $timer = new phpTimer();

    $iTotalCount = 0;

    $timer->start('subscriptionFiring');
    $timer->start('AddFolder');
    $count = SubscriptionEngine::fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("AddFolder"),
             SubscriptionConstants::subscriptionType("FolderSubscription"),
             array( "newFolderName" => "Fictitious Folder",
                    "parentFolderName" => "Medical Research Council"));
    $iTotalCount += $count;
    $timer->stop('AddFolder');
    $time = $timer->get_current('AddFolder');
    echo "<li>AddFolder: processed alerts=$count; time=$time; subs/s=" . ($time/$count)  . "</li>";

    $timer->start('RemoveFolder');
    $count = SubscriptionEngine::fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("RemoveFolder"),
             SubscriptionConstants::subscriptionType("FolderSubscription"),
             array( "removedFolderName" => "Fictitious Folder",
                    "parentFolderName" => "Medical Research Council"));
    $iTotalCount += $count;
    $timer->stop('RemoveFolder');
    $time = $timer->get_current('RemoveFolder');
    echo "<li>RemoveFolder: processed alerts=$count; time=$time; subs/s=" . ($time/$count)  . "</li>";

    $timer->start('AddDocument');
    $count = SubscriptionEngine::fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("AddDocument"),
             SubscriptionConstants::subscriptionType("FolderSubscription"),
             array( "newDocumentName" => "Fictitious Document",
                    "folderName" => "Medical Research Council"));
    $timer->stop('AddDocument');
    $iTotalCount += $count;
    $time = $timer->get_current('AddDocument');
    echo "<li>AddDocument: processed alerts=$count; time=$time; subs/s=" . ($time/$count)  . "</li>";

    $timer->start('RemoveDocument');
    $count = SubscriptionEngine::fireSubscription($iFolderID, SubscriptionConstants::subscriptionAlertType("RemoveDocument"),
             SubscriptionConstants::subscriptionType("FolderSubscription"),
             array( "removedDocumentName" => "Fictitious Document",
                    "folderName" => "Medical Research Council"));
    $iTotalCount += $count;
    $timer->stop('RemoveDocument');
    $time = $timer->get_current('RemoveDocument');
    echo "<li>RemoveDocument: processed alerts=$count; time=$time; subs/s=" . ($time/$count)  . "</li>";

    $timer->start('ModifyDocument');
    $count = SubscriptionEngine::fireSubscription($iDocumentID, SubscriptionConstants::subscriptionAlertType("ModifyDocument"),
             SubscriptionConstants::subscriptionType("DocumentSubscription"),
             array( "modifiedDocumentName" => "dashboardBL.html"));
    $iTotalCount += $count;
    $timer->stop('ModifyDocument');
    $time = $timer->get_current('ModifyDocument');
    echo "<li>ModifyDocument: processed alerts=$count; time=$time; subs/s=" . ($time/$count)  . "</li>";
    $timer->stop('subscriptionFiring');

    $time = $timer->get_current('subscriptionFiring');
    echo "</ul><li><b>All Subscriptions: processed alerts=$iTotalCount; time=$time; subs/s=" . ($time/$iTotalCount) . "</b></li></ul>";

    /*
    // cleanup silently
    for ($i = 1; $i<=10; $i++) {
        if ($i<=5) {
            $oSubscriptionManager->removeSubscription($i, $iFolderID, SubscriptionConstants::subscriptionType("FolderSubscription"));
        } else {
            $oSubscriptionManager->removeSubscription($i, $iDocumentID, SubscriptionConstants::subscriptionType("DocumentSubscription"));
        }
}
    */
}
?>
