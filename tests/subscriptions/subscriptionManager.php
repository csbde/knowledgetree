<?php
include("../../config/dmsDefaults.php");

if (checkSession()) {
    require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
    $oSubscriptionManager = new SubscriptionManager();
    echo "<b>Testing folder subscriptions</b><br>";
    $iFolderID = 1;
    $iUserID = 1;
    echo "<ul><li>Testing folder subscription creation with folderID=$iFolderID, userID=$iUserID :";
    if ($oSubscriptionManager->createSubscription($iUserID, $iFolderID, SubscriptionConstants::subscriptionType("FolderSubscription"))) {
        echo "Passed creating folder subscription</li>";

        echo "<li>Testing folder subscription removal with folderID=$iFolderID, userID=$iUserID :";
        if ($oSubscriptionManager->removeSubscription($iUserID, $iFolderID, SubscriptionConstants::subscriptionType("FolderSubscription"))) {
            echo "Passed removing folder subscription</li>";
        } else {
            echo "Failed removing folder subscription(" . $_SESSION["errorMessage"] . ")</li>";
        }
    } else {
        echo "Failed creating folder subscription(" . $_SESSION["errorMessage"] . ")</li><li>Skipped folder subscription removal</li>";
    }

    echo "</ul><b>Testing document subscriptions</b><br>";
    $iDocumentID = 6;
    $iUserID = 1;
    echo "<ul><li>Testing document subscription creation with documentID=$iDocumentID, userID=$iUserID :";
    if ($oSubscriptionManager->createSubscription($iUserID, $iDocumentID, SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
        echo "Passed creating document subscription</li>";

        echo "<li>Testing document subscription removal with documentID=$iDocumentID, userID=$iUserID :";
        if ($oSubscriptionManager->removeSubscription($iUserID, $iDocumentID, SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
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
    for ($tmpFolderID = 1; $tmpFolderID<=5; $tmpFolderID++) {

        if ($oSubscriptionManager->createSubscription($iUserID, $tmpFolderID, SubscriptionConstants::subscriptionType("FolderSubscription"))) {
            echo "<li>created folder subscription folderID=$tmpFolderID for userID=$iUserID</li>";
        } else {
            echo "<li>folder subscription creation failed(" . $_SESSION["errorMessage"] . "): folderID=$tmpFolderID for userID=$iUserID</li>";
        }
    }
    echo "</li></ul>";
    echo "<li>Creating document subscriptions<ul>";
    for ($tmpDocumentID = 5; $tmpDocumentID>0; $tmpDocumentID--) {
        if ($oSubscriptionManager->createSubscription($iUserID, $tmpDocumentID, SubscriptionConstants::subscriptionType("DocumentSubscription"))) {
            echo "<li>created document subscription documentID=$tmpDocumentID for userID=$iUserID</li>";
        } else {
            echo "<li>document subscription creation failed(" . $_SESSION["errorMessage"] . "): documentID=$tmpDocumentID for userID=$iUserID</li>";
        }
    }
    echo "</li></ul>";

    // now try to retrieve them
    echo "<li>folder subscriptions for userID=$iUserID:";
    $aFolders = $oSubscriptionManager->retrieveSubscriptions($iUserID, SubscriptionConstants::subscriptionType("FolderSubscription"));
    echo "<pre>" . arrayToString($aFolders) . "</pre></li>";

    echo "<li>document subscriptions for userID=$iUserID:";
    $aDocuments = $oSubscriptionManager->retrieveSubscriptions($iUserID, SubscriptionConstants::subscriptionType("DocumentSubscription"));
    echo "<pre>" . arrayToString($aDocuments) . "</pre></li>";

    // now try retrieving both
    echo "<li>all subscriptions for userID=$iUserID:";
    $oResults = $oSubscriptionManager->listSubscriptions($iUserID);
    echo "<pre>" . arrayToString($oResults) . "</pre></li>";
    echo "</ul>";

    // cleanup silently
    for ($i = 1; $i<=5; $i++) {
        $oSubscriptionManager->removeSubscription($iUserID, $i, SubscriptionConstants::subscriptionType("FolderSubscription"));
        $oSubscriptionManager->removeSubscription($iUserID, $i, SubscriptionConstants::subscriptionType("DocumentSubscription"));

        $oSubscriptionManager->removeSubscription($i, $iFolderID,SubscriptionConstants::subscriptionType("FolderSubscription") );
        $oSubscriptionManager->removeSubscription($i, $iDocumentID, SubscriptionConstants::subscriptionType("DocumentSubscription"));
    }
}
?>
