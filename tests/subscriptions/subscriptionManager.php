<?php
include("../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->owl_fs_root/lib/subscriptions/SubscriptionManager.inc");
    $oSubscriptionManager = new SubscriptionManager();
    echo "<b>Testing folder subscriptions</b><br>";
    $iFolderID = 1;
    $iUserID = 1;
    echo "<ul><li>Testing folder subscription creation with folderID=$iFolderID, userID=$iUserID :";
    if ($oSubscriptionManager->createFolderSubscription($iFolderID, $iUserID)) {
        echo "Passed creating folder subscription</li>";

        echo "<li>Testing folder subscription removal with folderID=$iFolderID, userID=$iUserID :";
        if ($oSubscriptionManager->removeFolderSubscription($iFolderID, $iUserID)) {
            echo "Passed removing folder subscription</li>";
        } else {
            echo "Failed removing folder subscription(" . $_SESSION["errorMessage"] . ")</li>";
        }
    } else {
        echo "Failed creating folder subscription(" . $_SESSION["errorMessage"] . ")</li><li>Skipped folder subscription removal</li>";
    }

    echo "</ul><b>Testing document subscriptions</b><br>";
    $iDocumentID = 2;
    $iUserID = 1;
    echo "<ul><li>Testing document subscription creation with documentID=$iDocumentID, userID=$iUserID :";
    if ($oSubscriptionManager->createDocumentSubscription($iDocumentID, $iUserID)) {
        echo "Passed creating document subscription</li>";

        echo "<li>Testing document subscription removal with documentID=$iDocumentID, userID=$iUserID :";
        if ($oSubscriptionManager->removeDocumentSubscription($iDocumentID, $iUserID)) {
            echo "Passed removing document subscription</li>";
        } else {
            echo "Failed removing document subscription(" . $_SESSION["errorMessage"] . ")</li>";
        }
    } else {
        echo "Failed creating document subscription(" . $_SESSION["errorMessage"] . ")</li><li>Skipped document subscription removal</li></ul>";
    }

    echo "</ul><b>Testing subscription retrieval</b><br>";
    // first create some subscriptions
    $iUserID = 10;
    echo "<ul><li>Creating folder subscriptions<ul>";
    for ($i = 1; $i<=5; $i++) {

        if ($oSubscriptionManager->createFolderSubscription($i, $iUserID)) {
            echo "<li>created folder subscription folderID=$i for userID=$iUserID</li>";
        } else {
            echo "<li>folder subscription creation failed(" . $_SESSION["errorMessage"] . "): folderID=$i for userID=$iUserID</li>";
        }
    }
    echo "</li></ul>";
    echo "<li>Creating document subscriptions<ul>";
    for ($i = 5; $i>0; $i--) {
        if ($oSubscriptionManager->createDocumentSubscription($i, $iUserID)) {
            echo "<li>created document subscription documentID=$i for userID=$iUserID</li>";
        } else {
            echo "<li>document subscription creation failed(" . $_SESSION["errorMessage"] . "): documentID=$i for userID=$iUserID</li>";
        }
    }
    echo "</li></ul>";

    // now try to retrieve them
    echo "<li>folder subscriptions for userID=$iUserID:";
    $aFolders = $oSubscriptionManager->retrieveFolderSubscriptions($iUserID);
    echo "<pre>" . arrayToString($aFolders) . "</pre></li>";

    echo "<li>document subscriptions for userID=$iUserID:";
    $aDocuments = $oSubscriptionManager->retrieveDocumentSubscriptions($iUserID);
    echo "<pre>" . arrayToString($aDocuments) . "</pre></li>";

    // now try retrieving both
    echo "<li>all subscriptions for userID=$iUserID:";
    $oResults = $oSubscriptionManager->retrieveSubscriptions($iUserID);
    echo "<pre>" . arrayToString($oResults) . "</pre></li>";
    echo "</ul>";

    // test subscribers retrieval methods
    echo "<b>Testing subscriber retrieval</b><br>";
    // add some subscriptions
    $iDocumentID = 4;
    $iFolderID = 20;
    for ($i = 5; $i>0; $i--) {
        if ($oSubscriptionManager->createFolderSubscription($iFolderID, $i)) {
            echo "<li>created folder subscription folderID=$iFolderID for userID=$i</li>";
        } else {
            echo "<li>folder creation failed(" . $_SESSION["errorMessage"] . "): folderID=$iFolderID for userID=$i</li>";
        }
        if ($oSubscriptionManager->createDocumentSubscription($iDocumentID, $i)) {
            echo "<li>created document subscription documentID=$iDocumentID for userID=$i</li>";
        } else {
            echo "<li>document creation failed(" . $_SESSION["errorMessage"] . "): documentID=$iDocumentID for userID=$i</li>";
        }
    }

    $aFolderSubscribers = $oSubscriptionManager->retrieveFolderSubscribers($iFolderID);
    $aDocumentSubscribers = $oSubscriptionManager->retrieveDocumentSubscribers($iDocumentID);
    echo "Subscribers for folderID=$iFolderID:";
    echo "<pre>" . arrayToString($aFolderSubscribers) . "</pre>";
    echo "Subscribers for documentID=$iDocumentID:";
    echo "<pre>" . arrayToString($aDocumentSubscribers) . "</pre>";

    echo "<pre>";

    echo "<b>Testing subscription firing</b>";
    // test subscription firing
    require_once("../../phpSniff/phpTimer.class.php");
    $timer = new phpTimer();
    $timer->start('subscriptionFiring');

    $timer->start('onAddFolder');
    $oSubscriptionManager->onAddFolder($iFolderID, "newFolder-onAddFolder");
    $timer->stop('onAddFolder');
    echo "<ul><li>onAddFolder: " . $timer->get_current('onAddFolder') . "</li>";

    $timer->start('onRemoveFolder');
    $oSubscriptionManager->onRemoveFolder($iFolderID, "removeFolder");
    $timer->stop('onRemoveFolder');
    echo "<li>onRemoveFolder: " . $timer->get_current('onRemoveFolder') . "</li>";

    $timer->start('onAddDocument');
    $oSubscriptionManager->onAddDocument($iFolderID, "addDocumentName");
    $timer->stop('onAddDocument');
    echo "<li>onAddDocument: " . $timer->get_current('onAddDocument') . "</li>";

    $timer->start('onRemoveDocument');
    $oSubscriptionManager->onRemoveDocument($iFolderID, "removeDocumentName");
    $timer->stop('onRemoveDocument');
    echo "<li>onRemoveDocument: " . $timer->get_current('onRemoveDocument') . "</li>";

    $timer->start('onModifyDocument');
    $oSubscriptionManager->onModifyDocument($iDocumentID);
    $timer->stop('onModifyDocument');
    echo "<li>onModifyDocument: " . $timer->get_current('onModifyDocument') . "</li>";

    $timer->stop('subscriptionFiring');

    echo "</ul>all subscription firing: " . $timer->get_current('subscriptionFiring');
    echo "</pre>";

    // cleanup silently
    for ($i = 1; $i<=5; $i++) {
        $oSubscriptionManager->removeFolderSubscription($i, $iUserID);
        $oSubscriptionManager->removeDocumentSubscription($i, $iUserID);

        $oSubscriptionManager->removeFolderSubscription($iFolderID, $i);
        $oSubscriptionManager->removeDocumentSubscription($iDocumentID, $i);
    }
}
?>
