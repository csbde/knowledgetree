<?php
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionConstants.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/documentmanagement/Document.inc");
/**
 * $Id$
 *
 * Represents a subscription.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.subscriptions
 */ 
class Subscription {

    /**
    * Primary key
    */
    var $iID;
    /**
    * The ID of the user subscribed to the document
    */
    var $iUserID;
    /**
     * The artefact type
     */
    var $iSubscriptionType;
    /**
    * The ID of the artefact subscribed to
    */
    var $iExternalID;
    /**
     * Whether this subscription is triggered
     */
    var $bIsAlerted;
    /**
     * The subscription database table to use
     */
    var $sTableName;
    /**
     * The subscription content id field name
     */
    var $sIdFieldName;

    /**
    * Creates a new subscription object
    *
    * @param integer the user ID    
    * @param integer the external ID
    * @param integer the subscription type
    * @param bool whether alerted or not
    */
    function Subscription($iUserID, $iExternalID, $iSubscriptionType, $bIsAlerted = false) {
        global $default;

        //id of -1 means that the object has not yet been stored in the database
        $this->iID = -1;
        $this->iUserID = $iUserID;
        $this->iExternalID = $iExternalID;
        $this->iSubscriptionType = $iSubscriptionType;
        $this->bIsAlerted = $bIsAlerted;
        $this->sTableName   =  Subscription::getTableName($iSubscriptionType);
        $this->sIdFieldName =  Subscription::getIdFieldName($iSubscriptionType);
    }

    /**
    * Get the primary key of the current subscription object
    *
    * @return integer primary key of subscription
    */
    function getID() {
        return $this->iID;
    }

    /**
    * Get the primary key of the subscription content.
    *
    * @return integer primary key of subscription content.
    */
    function getExternalID() {
        return $this->iExternalID;
    }

    /**
    * Set the subscription content id
    *
    * @param integer new subscription content primary key
    */
    function setExternalID($iNewValue) {
        $this->iExternalID = $iNewValue;
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
     * Returns the display path to the subscription content
     */
    function getContentDisplayPath() {
        if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return Document::getDocumentDisplayPath($this->iExternalID);
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return Folder::getFolderDisplayPath($this->iExternalID);
        }
    }
    
    /**
     * Returns the link to view the subscription content
     */
    function getContentLink() {
        // TODO: add subscription icon
        if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return generateControllerLink("viewDocument", "fDocumentID=$this->iExternalID", Document::getDocumentDisplayPath($this->iExternalID));
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return generateControllerLink("browse", "fBrowseType=folder&fFolderID=$this->iExternalID", Folder::getFolderDisplayPath($this->iExternalID));
        }
    }

    /**
     * Returns the url to the subscription content
     */
    function getContentUrl() {
        if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return generateControllerUrl("viewDocument", "fDocumentID=$this->iExternalID");
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return generateControllerUrl("browse", "fBrowseType=folder&fFolderID=$this->iExternalID");
        }
    }

    function getAlertLink() {
        global $default;
        // TODO: add alerted icon
        $sViewAlertUrl = "/control.presentation/lookAndFeel/knowledgeTree/subscriptions/viewAlertBL.php";
        $sViewAlertParams = "fSubscriptionID=" . $this->iID . "&fSubscriptionType=" . $this->iSubscriptionType;

        if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return generateControllerLink("viewAlert", $sViewAlertParams, "<img src=\"$default->graphicsUrl/widgets/subsc.gif\" border=\"0\"/>&nbsp;" . Document::getDocumentDisplayPath($this->iExternalID));
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return generateControllerLink("viewAlert", $sViewAlertParams, "<img src=\"$default->graphicsUrl/widgets/subsc.gif\" border=\"0\"/>&nbsp;" . Folder::getFolderDisplayPath($this->iExternalID));
        }
    }
    
    function getSubscriptionTypeName() {
        if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return "document";
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return "folder";
        }
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
            // TODO: set table name and id_field_name
            if ($sql->query("INSERT INTO " . $this->sTableName . " (user_id, $this->sIdFieldName, is_alerted) " .
                            "VALUES ($this->iUserID, $this->iExternalID, " . (int)$this->bIsAlerted . ")")) {
                $this->iID = $sql->insert_id();
                return true;
            } else {
                $_SESSION["errorMessage"] = $lang_err_database;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iID . " table = $this->sTableName";
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
            // TODO: set table name and id_field_name
            if ($sql->query("UPDATE " . $this->sTableName . " SET " .
                            "user_id = $this->iUserID, " .
                            "$this->sIdFieldName = $this->iExternalID, " .
                            "is_alerted = " . (int)$this->bIsAlerted . " " .
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
            // TODO: set table name and id_field_name
            if ($sql->query("DELETE FROM " . $this->sTableName . " WHERE id = " . $this->iID)) {
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
    * Given a subscription primary key will create a subscription object and populate it with the corresponding
    * database values
    *
    * @param integer primary key of subscription to get
    * @param integer the type of subscription
    * @return object subscription object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
    */
    function get($iSubscriptionID, $iSubscriptionType) {
        global $default, $lang_err_object_not_exist, $lang_err_database;
        $sql = $default->db;

        if ($sql->query("SELECT * FROM " . Subscription::getTableName($iSubscriptionType) . " WHERE id = " . $iSubscriptionID)) {
            if ($sql->next_record()) {
                $oSubscription = & new Subscription($sql->f("user_id"),
                                                    $sql->f(Subscription::getIdFieldName($iSubscriptionType)),
                                                    $iSubscriptionType,
                                                    $sql->f("is_alerted"));
                $oSubscription->iID = $iSubscriptionID;
                return $oSubscription;
            } else {
                $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iSubscriptionID . " table = " . Subscription::getTableName($iSubscriptionType);
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
        }
        return false;
    }

    /**
    * Static function.
    * Given a subscription's values will create a subscription object and populate it with the corresponding
    * primary key
    *
    * @param integer the user ID    
    * @param integer the external ID
    * @param integer the type of subscription    
    * @return object subscription object on successful retrieval, false otherwise and set $_SESSION["errorMessage"]
    */
    function getByIDs($iUserID, $iExternalID, $iSubscriptionType) {
        global $default, $lang_err_database, $lang_err_object_not_exist;
        $sql = $default->db;
        if ($sql->query("SELECT * FROM " . Subscription::getTableName($iSubscriptionType) . " " .
                        "WHERE " . Subscription::getIdFieldName($iSubscriptionType) . " = $iExternalID AND user_id = $iUserID")) {
            if ($sql->next_record()) {
                $oSubscription = & new Subscription($sql->f("user_id"),
                                                    $sql->f(Subscription::getIdFieldName($iSubscriptionType)),
                                                    $iSubscriptionType,
                                                    $sql->f("is_alerted"));
                $oSubscription->iID = $sql->f("id");
                return $oSubscription;
            } else {
                $_SESSION["errorMessage"] = $lang_err_object_not_exist . " extID=$iExternalID, uid=$iUserID; table = " . Subscription::getTableName($iSubscriptionType);
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
        }
        return false;
    }

    /**
    * Checks if a given subscription already exists using the external and user ids
    *
    * @param integer the user ID    
    * @param integer the external ID
    * @param integer the subscription type
    * @return true if the document subscription exists, false otherwise
    */
    function exists($iUserID, $iExternalID, $iSubscriptionType) {
        global $default, $lang_err_database;
        $sql = $default->db;
        if ($sql->query("SELECT id FROM " . Subscription::getTableName($iSubscriptionType) . " " .
                        "WHERE " . Subscription::getIdFieldName($iSubscriptionType) . " = $iExternalID AND user_id = $iUserID")) {
            if ($sql->next_record()) {
                return true;
            }
        } else {
            $_SESSION["errorMessage"] = $lang_err_database;
        }
        return false;
    }

    /**
     * Returns the correct table name for the subscription type
     *
     * @param integer the subscription type
     * @return string the subscription table name to use
     */
    function getTableName($iSubscriptionType) {
        global $default;

        if ($iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return $default->document_subscriptions_table;
        } else if($iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return $default->folder_subscriptions_table;
        }

    }
    /**
     * Returns the correct id field name for the subscription type
     *
     * @param integer the subscription type
     * @return string the subscription id field name to use
     */
    function getIdFieldName($iSubscriptionType) {
        if ($iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return "document_id";
        } else if($iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return "folder_id";
        }

    }
}
?>
