<?php
require_once("$default->owl_fs_root/lib/users/User.inc");
require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
require_once("$default->owl_fs_root/lib/subscriptions/DocumentSubscription.inc");
require_once("$default->owl_fs_root/lib/subscriptions/FolderSubscription.inc");
require_once("$default->owl_fs_root/lib/email/Email.inc");

/**
 * 
 * $Id$
 * 
 * Facilitates adding and removing file and folder subscriptions.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
 * @package lib.subscriptions
 */
class SubscriptionManager {
    /**
     * The default text for the folder subscription alert email
     */
    var $sFolderSubscriptionEmail;

    /**
     * The default text for the document subscription alert email
     */
    var $sDocumentSubscriptionEmail;

    // ----------------- manage subscriptions

    /**
     * Creates a folder subscription.
     *
     * @param int the folder to create the subscription for
     * @param int the user to create the subscription for
     * @return true if the subscription creation succeed, false and error message otherwise
     */
    function createFolderSubscription($iFolderID, $iUserID) {
        global $lang_folder_subs_exists;

        if (!FolderSubscription::exists($iFolderID, $iUserID)) {
            $oFolderSubscription = new FolderSubscription($iFolderID, $iUserID);
            if ($oFolderSubscription->create()) {
                return true;
            } else {
                // error message set in FolderSubscription::create
                return false;
            }
        } else {
            // TODO: lookup username and folder name from ids for error message
            $_SESSION["errorMessage"] = $lang_folder_sub_exists . " folderID=$iFolderID, userID=$iUserID";
            return false;
        }
    }

    /**
     * Removes a folder subscription.
     *
     * @param int the folder to remove the subscription for
     * @param int the user to remove the subscription for
     */
    function removeFolderSubscription($iFolderID, $iUserID) {
        global $lang_folder_sub_not_exists;

        if (FolderSubscription::exists($iFolderID, $iUserID)) {
            $oFolderSubscription = FolderSubscription::getByIDs($iFolderID, $iUserID);
            if ($oFolderSubscription->delete()) {
                return true;
            } else {
                // error message set in FolderSubscription::delete
                return false;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_folder_sub_not_exists;
            return false;
        }
    }

    /**
    * Creates a document subscription.
    *
    * @param int the document to create the subscription for
    * @param int the user to create the subscription for
    */
    function createDocumentSubscription($iDocumentID, $iUserID) {
        global $lang_document_subs_exists;

        if (!DocumentSubscription::exists($iDocumentID, $iUserID)) {
            $oDocumentSubscription = new DocumentSubscription($iDocumentID, $iUserID);
            if ($oDocumentSubscription->create()) {
                return true;
            } else {
                // error message set in DocumentSubscription::create
                return false;
            }
        } else {
            // TODO: lookup username and folder name from ids for error message
            $_SESSION["errorMessage"] = $lang_document_subs_exists . " documentID=$iDocumentID, userID=$iUserID";
            return false;
        }
    }

    /**
    * Removes a document subscription.
    *
    * @param int the document to remove the subscription for
    * @param int the user to remove the subscription for
    */
    function removeDocumentSubscription($iDocumentID, $iUserID) {
        global $lang_document_sub_not_exists;

        if (DocumentSubscription::exists($iDocumentID, $iUserID)) {
            $oDocumentSubscription = DocumentSubscription::getByIDs($iDocumentID, $iUserID);
            if ($oDocumentSubscription->delete()) {
                return true;
            } else {
                // error message set in DocumentSubscription::delete
                return false;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_document_sub_not_exists;
            return false;
        }
    }

    // ----------------- retrieve subscriptions

    /**
    * Retrieves all document and folders that the user is subscribed to
    *
    * @param integer the ID of the user to retrieve subscriptions for
    * @return array of folder objects
    */
    function retrieveSubscriptions($iUserID) {
        return $aSubscriptions = array("folders" => SubscriptionManager::retrieveFolderSubscriptions($iUserID),
                                       "documents" => SubscriptionManager::retrieveDocumentSubscriptions($iUserID));
    }

    /**
    * Retrieves the folders that the passed user is subscribed to
    *
    * @param integer the ID of the user to retrieve folder subscriptions for
    * @return array of folder objects, false if the database interaction fails
    */
    function retrieveFolderSubscriptions($iUserID) {
        global $default;

        $sql = $default->db;
        if ($sql->query("SELECT folder_id FROM " . $default->owl_folder_subscriptions_table . " WHERE user_id = $iUserID")) {
            $aFolders = array();
            while ($sql->next_record()) {
                $aFolders[] = & Folder::get($sql->f("folder_id"));
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aFolders;
    }

    /**
    * Retrieves the documents that the passed user is subscribed to
    *
    * @param integer the ID of the user to retrieve document subscriptions for
    * @return array of document objects
    */
    function retrieveDocumentSubscriptions($iUserID) {
        global $default;

        $sql = $default->db;
        if ($sql->query("SELECT document_id FROM " . $default->owl_document_subscriptions_table . " WHERE user_id = $iUserID")) {
            $aDocuments = array();
            while ($sql->next_record()) {
                // don't need the whole document object- just a link to it
                // TODO: add path to doc and folder sub objects and write methods in there
                //       to instantiate sub objects and return
                $aDocuments[] = & Document::get($sql->f("document_id"));
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aDocuments;
    }

    // ----------------- retrieve subscribers

    /**
    * Retrieves the users that are subscribed to this folder.
    *
    * @param integer the ID of the folder to retrieve subscribers for
    * @return array of users objects representing the subscribers, false on error
    */
    function retrieveFolderSubscribers($iFolderID) {
        global $default;

        $sql = $default->db;
        $aUsers = array();
        if ($sql->query("SELECT user_id from $default->owl_folder_subscriptions_table WHERE folder_id=$iFolderID")) {
            while ($sql->next_record()) {
                $aUsers[] = & User::get($sql->f("user_id"));
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aUsers;
    }

    /**
    * Retrieves the users that are subscribed to this document.
    *
    * @param integer the ID of the document to retrieve subscribers for
    * @return array of users objects representing the subscribers
    */
    function retrieveDocumentSubscribers($iDocumentID) {
        global $default;

        $sql = $default->db;
        $aUsers = array();
        if ($sql->query("SELECT user_id from $default->owl_document_subscriptions_table WHERE document_id=$iDocumentID")) {
            while ($sql->next_record()) {
                $aUsers[] = & User::get($sql->f("user_id"));
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aUsers;
    }

    // ----------------- alert on changes

    /**
     * Fires a subscription alert (if any exists) for adding a folder
     *
     * @param integer the parent folder id of the new folder
     * @param string the name of the new folder
     * @param string the notification text to send (optional)
     */
    function onAddFolder($iParentFolderID, $sNewFolderName, $sNotificationText = "") {
        // set optional parameter if necessary
        if ($sNotificationText == "") {
            $sNotificationText = "A new folder '$sNewFolderName' has been added to folder '" . Folder::getFolderName($iParentFolderID) . "'.<br>" .
                                 "Please clear this subscription alert by clicking on the following link: " .
                                 generateControllerLink("viewAlert", "fFolderID=$iParentFolderID") . Folder::getFolderDisplayPath($iParentFolderID) . "</a>";
        }
        SubscriptionManager::fireFolderSubscriptionAlert($iParentFolderID, $sNotificationText);
    }

    /**
     * Fires a subscription alert (if any exists) for removing a folder
     *
     * @param integer the parent folder id of the removed folder
     * @param string the name of the removed folder
     * @param string the notification text to send (optional)
     */
    function onRemoveFolder($iParentFolderID, $sRemovedFolderName, $sNotificationText = "") {
        // set optional parameter if necessary
        if ($sNotificationText == "") {
            $sNotificationText = "The folder '$sRemovedFolderName' has been removed from folder '" . Folder::getFolderName($iParentFolderID) . "'.<br>" .
                                 "Please clear this subscription alert by clicking on the following link: " .
                                 generateControllerLink("viewAlert", "fFolderID=$iParentFolderID") . Folder::getFolderDisplayPath($iParentFolderID) . "</a>";
        }
        SubscriptionManager::fireFolderSubscriptionAlert($iParentFolderID, $sNotificationText);
    }

    /**
     * Fires a subscription alert (if any exists) when adding a document to a folder
     *
     * @param integer the id of the folder the new document is in
     * @param string the name of the new document
     * @param string the notification text to send (optional)
     */
    function onAddDocument($iFolderID, $sNewDocumentName, $sNotificationText = "") {
        // set optional parameter if necessary
        if ($sNotificationText == "") {
            $sNotificationText = "A new document '$sNewDocumentName' has been added to folder '" . Folder::getFolderName($iFolderID) . "'.<br>" .
                                 "Please clear this subscription alert by clicking on the following link: " .
                                 generateControllerLink("viewAlert", "fFolderID=$iFolderID") . Folder::getFolderDisplayPath($iFolderID) . "</a>";
        }
        SubscriptionManager::fireFolderSubscriptionAlert($iFolderID, $sNotificationText);
    }


    /**
     * Fires a subscription alert (if any exists) when adding a document to a folder
     *
     * @param integer the id of the folder the new document is in
     * @param string the name of the new document
     * @param string the notification text to send (optional)
     */
    function onRemoveDocument($iFolderID, $sRemovedDocumentName, $sNotificationText = "") {
        // set optional parameter if necessary
        if ($sNotificationText == "") {
            $sNotificationText = "The document '$sRemovedDocumentName' has been removed from folder '" . Folder::getFolderName($iFolderID) . "'.<br>" .
                                 "Please clear this subscription alert by clicking on the following link: " .
                                 generateControllerLink("viewAlert", "fFolderID=$iFolderID") . Folder::getFolderDisplayPath($iFolderID) . "</a>";
        }
        SubscriptionManager::fireFolderSubscriptionAlert($iFolderID, $sNotificationText);
    }

    /**
     * Fires a subscription alert (if any) when a document is modified.
     *
     * @param integer the modified document id
     * @param string the notification text to send (optional)
     */
    function onModifyDocument($iDocumentID, $sNotificationText = "") {
        // set optional parameter if necessary
        if ($sNotificationText == "") {
            $sNotificationText = "The document '" . Document::getDocumentName($iDocumentID) . "' has been modified.<br>" .
                                 "Please clear this subscription alert by clicking on the following link: " .
                                 generateControllerLink("viewAlert", "fDocumentID=$iDocumentID") . Document::getDocumentDisplayPath($iDocumentID) . "</a>";
        }
        SubscriptionManager::fireDocumentSubscriptionAlert($iDocumentID, $sNotificationText);
    }

    /**
     * Sends an alert to each of the users subscribed to this folder
     * that the folder has changed.
     *
     * @param int the folder to fire alerts for
     * @param string the notification text to send
     */
    function fireFolderSubscriptionAlert($iFolderID, $sNotificationText) {
        global $default;
        $default->log->debug("fireFolderSubscriptionAlert folderID=$iFolderID; text=$sNotificationText");
        // retrieve all users subscribed to this folder and their notification settings
        $aSubscribers = SubscriptionManager::retrieveFolderSubscribers($iFolderID);
        if (count($aSubscribers) > 0) {
            // we've got some live ones!
            for ($i = 0; $i < count($aSubscribers); $i++) {
                $default->log->info("SubscriptionManager::fireFolderSubscriptionAlert folderID=$iFolderID; folder subscriber=" . $aSubscribers[$i]->getID());
                // set notification flag (dashboard)
                if ($default->db->query("UPDATE $default->owl_folder_subscriptions_table SET datetime_alerted='" . getCurrentDateTime() . "', is_alerted = 1 " .
                                        "WHERE folder_id = $iFolderID AND user_id = " . $aSubscribers[$i]->getID())) {
                    $default->log->debug("SubscriptionManager::fireFolderSubscriptionAlert updated folder subscription");

                    // if email notification is enabled, email them
                    if ($aSubscribers[$i]->getEmailNotification() && (strlen($aSubscribers[$i]->getEmail()) > 0)) {
                        $oEmail= new Email("", "MRC DMS Subscriptions");
                        // personalise
                        $sText = "Hello " . $aSubscribers[$i]->getName() . "<br><br>" . $sNotificationText;
                        if ($oEmail->send($aSubscribers[$i]->getEmail(), "DMS Folder Subscription Alert", $sText)) {
                            $default->log->debug("SubscriptionManager::fireFolderSubscriptionAlert successfully sent email for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                        } else {
                            $default->log->error("SubscriptionManager::fireFolderSubscriptionAlert failed sending email for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                        }
                    }

                    // if sms notification is enabled, sms them
                    if ($aSubscribers[$i]->getSmsNotification()) {
                        // TODO
                    }

                }
                else {
                    $default->log->error("SubscriptionManager::fireFolderSubscriptionAlert could not update subscription for folderID=$iFolderID; subscriberID=" . $aSubscribers[$i]->ID());
                }
            }
        }
    }

    /**
    * Sends an alert to each of the users subscribed to this document
    * that the document has changed.
    *
    * @param int the document to fire alerts for
    */
    function fireDocumentSubscriptionAlert($iDocumentID, $sNotificationText) {
        global $default;

        // retrieve all subscribers subscribed to the parent folder of this document
        $iParentFolderID = Document::getFolderID($iDocumentID);

        // fire the alert for folder subscribers
        // FIXME: if you're subscribed to a document in a folder
        //        and then subscribe to the folder you'll get two alerts
        // TODO:  when adding a folder subscription check if any of the documents in the folder
        $default->log->debug("SubscriptionManager::fireDocumentSubscriptionAlert about to fire folder subscriptions documentID=$iDocumentID, parent folder id=$iParentFolderID");
        SubscriptionManager::fireFolderSubscriptionAlert($iParentFolderID, $sNotificationText);

        // retrieve all users subscribed to this document
        $aSubscribers = SubscriptionManager::retrieveDocumentSubscribers($iDocumentID);
        // update db notifications
        if (count($aSubscribers) > 0) {
            for ($i = 0; $i < count($aSubscribers); $i++) {
                $default->log->info("SubscriptionManager::fireDocumentSubscriptionAlert documentID=$iDocumentID; subscriber=" . $aSubscribers[$i]->getID());
                // set notification flag (dashboard)
                if ($default->db->query("UPDATE $default->owl_document_subscriptions_table SET datetime_alerted='" . getCurrentDateTime() . "', is_alerted = 1 " .
                                        "WHERE document_id = $iDocumentID AND user_id = " . $aSubscribers[$i]->getID())) {
                    // if email notification is enabled, email them
                    if ($aSubscribers[$i]->getEmailNotification() && (strlen($aSubscribers[$i]->getEmail()) > 0)) {
                        $oEmail= new Email("", "MRC DMS Subscriptions");
                        // personalise
                        $sText = "Hello " . $aSubscribers[$i]->getName() . "<br><br>" . $sNotificationText;
                        if ($oEmail->send($aSubscribers[$i]->getEmail(), "DMS Document Subscription Alert", $sText)) {
                            $default->log->debug("SubscriptionManager::fireDocumentSubscriptionAlert successfully sent email for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                        } else {
                            $default->log->error("SubscriptionManager::fireDocumentSubscriptionAlert failed sending email for folderID=$iFolderID, subscriber=" . $aSubscribers[$i]->getID() . "; text=$sNotificationText");
                        }
                    }
                    // if sms notification is enabled, sms them
                    if ($aSubscribers[$i]->getSmsNotification()) {
                        // TODO
                    }
                    $default->log->debug("SubscriptionManager::fireDocumentSubscriptionAlert updated folder subscription");
                } else {
                    $default->log->error("SubscriptionManager::fireDocumentSubscriptionAlert could not update subscription for folderID=$iParentFolderID; subscriberID=" . $aFolderSubscribers[$i]->ID());
                }
            }
        }
    }
}
?>
