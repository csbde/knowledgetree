<?php

require_once("../../config/dmsDefaults.php");

/**
 * Contains unit test code for class Subscription in /lib/subscription/Subscription.inc
 *
 * Tests are:
 * - creation of subscription object
 * - setting/getting of values
 * - storing of object
 * - updating of object
 * - deletion of object
 *
 * @package tests.subscriptions
 */
if (checkSession()) {
    require_once("$default->owl_fs_root/lib/subscriptions/Subscription.inc");

    echo "<b>Testing creation of new folder subscription object</b><br>";
    $oFolderSubscription = & new Subscription(1, 1, SubscriptionConstants::subscriptionType("FolderSubscription"));
    if (isset($oFolderSubscription)) {
        echo "Passed folder subscription creation test<br><br>";

        echo "<b>Testing getting and setting of values</b><br><br>";

        echo "Current value of primary key: " . $oFolderSubscription->getID() . "<br>";
        echo "This value CANNOT be altered manually<br><br>";

        echo "Current value of folder subscription user id: " . $oFolderSubscription->getUserID() . "<br>";
        echo "Setting folder subscription user id to: 23<br>";
        $oFolderSubscription->setUserID(23);
        echo "New value of folder subscription user id: " . $oFolderSubscription->getUserID() . "<br><br>";

        echo "Current value of folder subscription folder id: " . $oFolderSubscription->getExternalID() . "<br>";
        echo "Setting folder subscription folder id to 56<br>";
        $oFolderSubscription->getExternalID(56);
        echo "New folder subscription folder id: " . $oFolderSubscription->getExternalID() . "<br><br>";

        echo "<b>Testing storing of object in database</b><br>";
        if ($oFolderSubscription->create()) {
            echo "Passed storing of object in database test<br><br>";

            echo "<b>Testing object updating</b><br>";
            if ($oFolderSubscription->update()) {
                echo "Passed object updating test<br><br>";

                echo "<b>Testing getting of object from database using primary key</b><br>";
                $oNewFolderSubscription = & Subscription::get($oFolderSubscription->getID(), SubscriptionConstants::subscriptionType("FolderSubscription"));
                if (isset($oNewFolderSubscription)) {
                    echo "<pre> " . arrayToString($oNewFolderSubscription) . "</pre><br>";
                    echo "Passed getting of object from db using primary key<br><br>";

                    echo "<b>Testing deletion of object from database</b><br>";
                    if ($oFolderSubscription->delete()) {
                        echo "Passed deletion of object from database test.<br><br>END OF UNIT TEST";
                    } else {
                        echo "Failed deletion of object from database test(" . $_SESSION["errorMessage"] . ")";
                    }
                } else {
                    echo "Failed getting of object test(" . $_SESSION["errorMessage"] . ").<br> Tests not run (a)deletion of object<br>";
                }
            } else {
                echo "Failed object updating test(" . $_SESSION["errorMessage"] . ").<br> Tests not run (a)deletion of object (b)getting of object using id<br>";
            }
        } else {
            echo "Failed storing of object in database test (" . $_SESSION["errorMessage"] . ").<br> Tests not run (a)updating of object (b)deletion of object (c)getting of object using id<br>";
        }
    } else {
        echo "Failed folder subscription creation tests(" . $_SESSION["errorMessage"] . ").<br>Tests not run: (a)setting/getting of values (b)storing of object (c)updating of object (d)deletion of object (e)getting of object using id<br>";
    }

    echo "<br><br>";

    echo "<b>Testing creation of new document subscription object</b><br>";
    $oDocumentSubscription = & new Subscription(1, 1, SubscriptionConstants::subscriptionType("DocumentSubscription"));

    if (isset($oDocumentSubscription)) {
        echo "Passed document subscription creation test<br><br>";

        echo "<b>Testing getting and setting of values</b><br><br>";

        echo "Current value of primary key: " . $oDocumentSubscription->getID() . "<br>";
        echo "This value CANNOT be altered manually<br><br>";

        echo "Current value of document subscription user id: " . $oDocumentSubscription->getUserID() . "<br>";
        echo "Setting document subscription user id to: 12<br>";
        $oDocumentSubscription->setUserID(12);
        echo "New value of document subscription user id: " . $oDocumentSubscription->getUserID() . "<br><br>";

        echo "Current value of document subscription document id: " . $oDocumentSubscription->getExternalID() . "<br>";
        echo "Setting document subscription document id to 34<br>";
        $oDocumentSubscription->getExternalID(34);
        echo "New document subscription document id: " . $oDocumentSubscription->getExternalID() . "<br><br>";

        echo "<b>Testing storing of object in database</b><br>";
        if ($oDocumentSubscription->create()) {
            echo "Passed storing of object in database test<br><br>";

            echo "<b>Testing object updating</b><br>";
            if ($oDocumentSubscription->update()) {
                echo "Passed object updating test<br><br>";

                echo "<b>Testing getting of object from database using primary key</b><br>";
                $oNewDocumentSubscription = & Subscription::get($oDocumentSubscription->getID(), SubscriptionConstants::subscriptionType("DocumentSubscription"));
                if (isset($oNewDocumentSubscription)) {
                    echo "<pre> " . arrayToString($oNewDocumentSubscription) . "</pre><br>";
                    echo "Passed getting of object from db using primary key<br><br>";

                    echo "<b>Testing deletion of object from database</b><br>";
                    if ($oDocumentSubscription->delete()) {
                        echo "Passed deletion of object from database test.<br><br>END OF UNIT TEST";
                    } else {
                        echo "Failed deletion of object from database test";
                    }
                } else {
                    echo "Failed getting of object test.<br> Tests not run (a)deletion of object<br>";
                }
            } else {
                echo "Failed object updating test.<br> Tests not run (a)deletion of object (b)getting of object using id<br>";
            }
        } else {
            echo "Failed storing of object in database test.<br> Tests not run (a)updating of object (b)deletion of object (c)getting of object using id<br>";
        }
    } else {
        echo "Failed document subscription creation tests.<br>Tests not run: (a)setting/getting of values (b)storing of object (c)updating of object (d)deletion of object (e)getting of object using id<br>";
    }
}

?>
