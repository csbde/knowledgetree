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
        echo "Failed creating document subscription(" . $_SESSION["errorMessage"] . "), skipped document subscription removal</li>";
    }    
}
?>
