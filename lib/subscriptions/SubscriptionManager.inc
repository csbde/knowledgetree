<?php
require_once("$default->owl_fs_root/lib/documentmanagement/Document.inc");
require_once("$default->owl_fs_root/lib/foldermanagement/Folder.inc");
require_once("$default->owl_fs_root/lib/subscriptions/DocumentSubscription.inc");
require_once("$default->owl_fs_root/lib/subscriptions/FolderSubscription.inc");

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
     * Sends an alert to each of the users subscribed to this folder
     * that the folder has changed.
     *
     * @param int the folder to fire alerts for
     */
    function fireFolderSubscriptionAlert($iFolderID) {
        // retrieve all users subscribed to this folder and their notification settings
        
        // insert into notification table (dashboard)
        // if email notification is enabled, email them
        // if sms notification is enabled, sms them
    }
    

    /**
     * Sends an alert to each of the users subscribed to this document
     * that the document has changed.
     *
     * @param int the document to fire alerts for
     */
    function fireDocumentSubscriptionAlert($iDocumentID) {
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
                $aDocuments[] = & Document::get($sql->f("document_id"));
            }
		} else {
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        return $aDocuments;        
    }
}
?>
