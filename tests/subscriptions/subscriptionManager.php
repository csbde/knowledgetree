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
    // now try to retrieve them
    echo "<li>folder subscriptions for userID=$iUserID:";
    $aFolders = $oSubscriptionManager->retrieveFolderSubscriptions($iUserID);
    echo "<pre>" . arrayToString($aFolders) . "</pre></li>";
    
    echo "<li>Creating document subscriptions<ul>";
    for ($i = 6; $i>0; $i--) {
        if ($oSubscriptionManager->createDocumentSubscription($i, $iUserID)) {
            echo "<li>created document subscription documentID=$i for userID=$iUserID</li>";
        } else {
            echo "<li>folder document creation failed(" . $_SESSION["errorMessage"] . "): documentID=$i for userID=$iUserID</li>";
        }
    }
    echo "</li></ul>";    
    // now try to retrieve them
    echo "<li>folder subscriptions for userID=$iUserID:";    
    $aDocuments = $oSubscriptionManager->retrieveDocumentSubscriptions($iUserID);
    echo "<pre>" . arrayToString($aDocuments) . "</pre></li>";

    // now try retrieving both
    echo "<li>all subscriptions for userID=$iUserID:";
    $oResults = $oSubscriptionManager->retrieveSubscriptions($iUserID);
    echo "<pre>" . arrayToString($oResults) . "</pre></li>";
    echo "</ul>";

    // cleanup silently
    for ($i = 1; $i<=5; $i++) {       
        $oSubscriptionManager->removeFolderSubscription($i, $iUserID);
        $oSubscriptionManager->removeDocumentSubscription($i, $iUserID);
    } 
}
?>
