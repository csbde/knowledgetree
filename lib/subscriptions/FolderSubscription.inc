<?php
/**
 * 
 * $Id$
 * 
 * Represents a folder subscription.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$ 
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 *
 * @package lib.subscriptions
 */
class FolderSubscription {

    /**
     * Primary key
     */
    var $iID;
    /**
     * The ID of the folder subscribed to
     */
    var $iFolderID;
    /**
     * The ID of the user subscribed to the folder
     */
    var $iUserID;
    /**
     * The time this subscription was triggered
     */
    var $dTimeAlerted;
    /**
     * Whether this subscription is triggered
     */
    var $bIsAlerted;

    /**
     * Creates a new folder subscription object
     *
     * @param integer the folder ID
     * @param integer the user ID
     */
    function FolderSubscription($iFolderID, $iUserID) {
        //id of -1 means that the object has not yet been stored in the database
        $this->iID = -1;
        $this->iFolderID = $iFolderID;
        $this->iUserID = $iUserID;
        $this->dTimeAlerted = getCurrentDateTime();
        $this->bIsAlerted = false;
    }

    /**
    * Get the primary key of the current folder subscription object
    *
    * @return integer primary key of folder subscription
    */
    function getID() {
        return $this->iID;
    }

    /**
    * Get the primary key of the folder.
    *
    * @return integer primary key of folder
    */
    function getFolderID() {
        return $this->iFolderID;
    }

    /**
     * Set the folder id
     *
     * @param integer new folder primary key
     */
    function setFolderID($iNewValue) {
        $this->iFolderID = $iNewValue;
    }

    /**
    * Get the primary key of the user
    *
    * @return integer primary key of user
    */
    function getUserID() {
        return $this->iUserID;
    }

    /**
     * Set the user id
     *
     * @param integer new user primary key
     */
    function setUserID($iNewValue) {
        $this->iUserID = $iNewValue;
    }

    /**
    * Get the time this document subscription was alerted
    *
    * @return string the date time the subscription alert was triggered
    */
    function getTimeAlerted() {
        return $this->dTimeAlerted;
    }

    /**
    * Get the trigger status of this subscription
    *
    * @return boolean the trigger status of this subscription
    */
    function getIsAlerted() {
        return $this->bIsAlerted;
    }

    /**
    * Set the trigger status of the subscription
    *
    * @param boolean new trigger status 
    */
    function setIsAlerted($iNewValue) {
        $this->bIsAlerted = $iNewValue;
    }

    /**
     * Create the current folder subscription in the database
     *
     * @return boolean true and set $this->iID with new primary key, false otherwise and set $_SESSION["errorMessage"]
     */
    function create() {
        global $default, $lang_err_database;
        $lang_err_object_exists;
        //if the object has not already been stored
        if ($this->iID < 0) {
            $sql = $default->db;
            if ($sql->query("INSERT INTO " . $default->owl_folder_subscriptions_table . " (user_id, folder_id) " .
                            "VALUES ($this->iUserID, $this->iFolderID)")) {
                $this->iID = $sql->insert_id();
                // TODO: now create subscriptions for all the documents in this folder
                return true;
            } else {
                $_SESSION["errorMessage"] = $lang_err_database;
            }

        } else {
            $_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iID . " table = folder_subscriptions";
        }
        return false;
    }

    /**
     * Update the current folder subscription values in the database
     *
     * @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
     */
    function update() {
        global $default, $lang_err_database, $lang_err_object_key;
        //can only update the object if it has already been stored
        if ($this->iID >= 0) {
            $sql = $default->db;
            if ($sql->query("UPDATE " . $default->owl_folder_subscriptions_table . " SET " .
                            "user_id = $this->iUserID, " .
                            "folder_id = $this->iFolderID, " .
                            "datetime_alerted = '" . getCurrentDateTime() . "', " .
                            "is_alerted = $this->bIsAlerted " .
                            "WHERE id = " . $this->iID)) {
                return true;
            } else {
                $_SESSION["errorMessage"] = $lang_err_database;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_object_key;
        }
        return false;
    }


    /**
    * Delete the current object from the database
    *
    * @return boolean true and reset id to -1 on successful delete, false otherwise and set $_SESSION["errorMessage"]
    */
    function delete() {
        global $default, $lang_err_database, $lang_err_object_key;
        if ($this->iID >= 0) {
            $sql = $default->db;
            if ($sql->query("DELETE FROM " . $default->owl_folder_subscriptions_table . " WHERE id = " . $this->iID)) {
                $this->iID = -1;
                // TODO: remove all document subscriptions?
                return true;
            } else {
                $_SESSION["errorMessage"] = $lang_err_database;
            }

        } else {
            $_SESSION["errorMessage"] = $lang_err_object_key;
        }
        return false;

    }

    /**
     * Static function.  
     * Given a folder subscription primary key will create
     * a folder subscription object and populate it with the corresponding
     * database values
     *
     * @param integer primary key of folder subscription to get
     * @return object folder subscription object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
     */
    function get($iFolderSubscriptionID) {
        global $default,  $lang_err_database, $lang_err_object_not_exist;
        $sql = $default->db;
        if ($sql->query("SELECT * FROM " . $default->owl_folder_subscriptions_table . " WHERE id = " . $iFolderSubscriptionID)) {
            if ($sql->next_record()) {
                $oFolderSubscription = & new FolderSubscription($sql->f("folder_id"), $sql->f("user_id"));
                $oFolderSubscription->iID = $iFolderSubscriptionID;
                return $oFolderSubscription;
            } else {
                $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderSubscriptionID . " table = folder_subscriptions";
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
        }
        return false;
    }

    /**
     * Static function.
     * Given a folder subscription's values will create
     * a folder subscription object and populate it with the corresponding
     * primary key
     *
     * @param integer the folder ID
     * @param integer the user ID
     * @return object folder subscription object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
     */
    function getByIDs($iFolderID, $iUserID) {
        global $default,  $lang_err_object_not_exist;
        $sql = $default->db;
        $sql->query("SELECT * FROM " . $default->owl_folder_subscriptions_table . " " .
                    "WHERE folder_id = $iFolderID AND user_id = $iUserID");
        if ($sql->next_record()) {
            $oFolderSubscription = & new FolderSubscription($sql->f("folder_id"), $sql->f("user_id"));
            $oFolderSubscription->iID = $sql->f("id");
            return $oFolderSubscription;
        }
        $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iFolderSubscriptionID . " table = folder_subscriptions";
        return false;
    }

    /**
     * Checks if a given folder subscription already exists using the folder and user ids
     *
     * @param integer the folder ID
     * @param integer the user ID
     * @return true if the folder subscription exists, false otherwise
     */
    function exists($iFolderID, $iUserID) {
        global $default;
        $sql = $default->db;
        $sql->query("SELECT id FROM " . $default->owl_folder_subscriptions_table . " " .
                    "WHERE folder_id = $iFolderID AND user_id = $iUserID");
        if ($sql->next_record()) {
            return true;
        }
        return false;
    }
}
?>
