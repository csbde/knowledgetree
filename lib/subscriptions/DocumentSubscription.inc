<?php
/**
* 
* $Id$
* 
* Represents a document subscription.
*
* Licensed under the GNU GPL. For full terms see the file COPYING.
*
* @version $Revision$ 
* @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
*
* @package lib.subscriptions
*/
class DocumentSubscription {

    /**
    * Primary key
    */
    var $iID;
    /**
    * The ID of the document subscribed to
    */
    var $iDocumentID;
    /**
    * The ID of the user subscribed to the document
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
    * Creates a new document subscription object
    *
    * @param integer the document ID
    * @param integer the user ID
    */
    function DocumentSubscription($iDocumentID, $iUserID) {
        //id of -1 means that the object has not yet been stored in the database
        $this->iID = -1;
        $this->iDocumentID = $iDocumentID;
        $this->iUserID = $iUserID;
        $this->dTimeAlerted = getCurrentDateTime();
        $this->bIsAlerted = false;
    }

    /**
    * Get the primary key of the current document subscription object
    *
    * @return integer primary key of document subscription
    */
    function getID() {
        return $this->iID;
    }

    /**
    * Get the primary key of the document.
    *
    * @return integer primary key of document
    */
    function getDocumentID() {
        return $this->iDocumentID;
    }

    /**
    * Set the document id
    *
    * @param integer new document primary key
    */
    function setDocumentID($iNewValue) {
        $this->iDocumentID = $iNewValue;
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
    * Create the current document subscription in the database
    *
    * @return boolean true and set $this->iID with new primary key, false otherwise and set $_SESSION["errorMessage"]
    */
    function create() {
        global $default, $lang_err_database;
        $lang_err_object_exists;
        //if the object has not already been stored
        if ($this->iID < 0) {
            $sql = $default->db;
            if ($sql->query("INSERT INTO " . $default->owl_document_subscriptions_table . " (user_id, document_id) " .
                            "VALUES ($this->iUserID, $this->iDocumentID)")) {
                $this->iID = $sql->insert_id();
                return true;
            } else {
                $_SESSION["errorMessage"] = $lang_err_database;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iID . " table = document_subscriptions";
        }
        return false;
    }

    /**
    * Update the current document subscription values in the database
    *
    * @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
    */
    function update() {
        global $default, $lang_err_database, $lang_err_object_key;
        //can only update the object if it has already been stored
        if ($this->iID >= 0) {
            $sql = $default->db;
            if ($sql->query("UPDATE " . $default->owl_document_subscriptions_table . " SET " .
                            "user_id = $this->iUserID, " .
                            "document_id = $this->iDocumentID " .
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
            if ($sql->query("DELETE FROM " . $default->owl_document_subscriptions_table . " WHERE id = " . $this->iID)) {
                $this->iID = -1;
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
    * Given a document subscription primary key will create
    * a document subscription object and populate it with the corresponding
    * database values
    *
    * @param integer primary key of document subscription to get
    *
    * @return object document subscription object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
    */
    function get($iDocumentSubscriptionID) {
        global $default, $lang_err_object_not_exist, $lang_err_database;
        $sql = $default->db;
        if ($sql->query("SELECT * FROM " . $default->owl_document_subscriptions_table . " WHERE id = " . $iDocumentSubscriptionID)) {
            if ($sql->next_record()) {
                $oDocumentSubscription = & new DocumentSubscription($sql->f("document_id"), $sql->f("user_id"));
                $oDocumentSubscription->iID = $iDocumentSubscriptionID;
                return $oDocumentSubscription;
            } else {
                $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentSubscriptionID . " table = document_subscriptions";
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
        }
        return false;
    }

    /**
    * Static function.
    * Given a document subscription's values will create
    * a document subscription object and populate it with the corresponding
    * primary key
    *
    * @param integer the document ID
    * @param integer the user ID
    * @return object document subscription object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
    */
    function getByIDs($iDocumentID, $iUserID) {
        global $default, $lang_err_database, $lang_err_object_not_exist;
        $sql = $default->db;
        if ($sql->query("SELECT * FROM " . $default->owl_document_subscriptions_table . " " .
                        "WHERE document_id = $iDocumentID AND user_id = $iUserID")) {
            if ($sql->next_record()) {
                $oDocumentSubscription = & new DocumentSubscription($sql->f("document_id"), $sql->f("user_id"));
                $oDocumentSubscription->iID = $sql->f("id");
                return $oDocumentSubscription;
            } else {
                $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentSubscriptionID . " table = folder_subscriptions";
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
        }
        return false;
    }

    /**
    * Checks if a given document subscription already exists using the document and user ids
    *
    * @param integer the document ID
    * @param integer the user ID
    * @return true if the document subscription exists, false otherwise
    */
    function exists($iDocumentID, $iUserID) {
        global $default, $lang_err_database;
        $sql = $default->db;
        if ($sql->query("SELECT id FROM " . $default->owl_document_subscriptions_table . " " .
                        "WHERE document_id = $iDocumentID AND user_id = $iUserID")) {
            if ($sql->next_record()) {
                return true;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
        }
        return false;
    }
}
?>
