<?php
require_once(KT_LIB_DIR . "/subscriptions/SubscriptionConstants.inc");
require_once(KT_LIB_DIR . "/foldermanagement/Folder.inc");
require_once(KT_LIB_DIR . "/documentmanagement/Document.inc");
require_once(KT_LIB_DIR . "/subscriptions/subscriptions.inc.php");
/**
 * $Id$
 *
 * Represents a subscription.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.subscriptions
 */ 
class Subscription extends KTEntity {

    /**
    * Primary key
    */
    var $iId;
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
        $this->iId = -1;
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
        return $this->iId;
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
            return generateControllerLink("viewDocument", "fDocumentId=$this->iExternalID", Document::getDocumentDisplayPath($this->iExternalID));
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return generateControllerLink("browse", "fBrowseType=folder&fFolderId=$this->iExternalID", Folder::getFolderDisplayPath($this->iExternalID));
        }
    }

    /**
     * Returns the url to the subscription content
     */
    function getContentUrl() {
        if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return generateControllerUrl("viewDocument", "fDocumentId=$this->iExternalID");
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return generateControllerUrl("browse", "fBrowseType=folder&fFolderId=$this->iExternalID");
        }
    }

    function getAlertLink() {
        global $default;
        // TODO: add alerted icon
        $sViewAlertParams = "fSubscriptionID=" . $this->iId . "&fSubscriptionType=" . $this->iSubscriptionType;

        if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return generateControllerLink("viewAlert", $sViewAlertParams, "<img src=\"" . imgSrc("widgets/subsc.gif") . "\" border=\"0\"/>&nbsp;" . Document::getDocumentDisplayPath($this->iExternalID));
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return generateControllerLink("viewAlert", $sViewAlertParams, "<img src=\"" . imgSrc("widgets/subsc.gif") . "\" border=\"0\"/>&nbsp;" . Folder::getFolderDisplayPath($this->iExternalID));
        }
    }
    
    function getSubscriptionTypeName() {
        if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("DocumentSubscription")) {
            return "document";
        } else if ($this->iSubscriptionType == SubscriptionConstants::subscriptionType("FolderSubscription")) {
            return "folder";
        }
    }

    function _fieldValues () {
        return array(
            'user_id' => $this->iUserID,
            $this->sIdFieldName => $this->iExternalID,
            'is_alerted' => KTUtil::anyToBool($this->bIsAlerted),
        );
    }

    function _table () {
        return $this->sTableName;
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
    function & get($iSubscriptionID, $iSubscriptionType) {
        // FIXME this is crack - need to start looking at multiple entities here.
        $sQuery = "SELECT * FROM " . Subscription::getTableName($iSubscriptionType) . " WHERE id = ?";/*ok*/
        $aParams = array($iSubscriptionID);
        $res = DBUtil::getOneResult(array($sQuery, $aParams));
        
        if (PEAR::isError($res)) {
            return false; // return $res;
        }
        
        if (empty($res)) {
            return false; // return PEAR::raiseError(_kt('No such Subscription ID'))
        }

        $oSubscription = & new Subscription($res["user_id"],
                                            $res[Subscription::getIdFieldName($iSubscriptionType)],
                                            $iSubscriptionType,
                                            $res["is_alerted"]);
        $oSubscription->iId = $iSubscriptionID;
        return $oSubscription;        
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
        $sQuery = "SELECT * FROM " . Subscription::getTableName($iSubscriptionType) . " WHERE " . Subscription::getIdFieldName($iSubscriptionType) . " = ? AND user_id = ?";
        $aParams = array($iExternalID, $iUserID);
        $res = DBUtil::getOneResult(array($sQuery, $aParams));

        if (PEAR::isError($res)) {
            return false; // return $res;
        }
        
        if (empty($res)) {
            return false; // return PEAR::raiseError(_kt('No such Subscription ID'))
        }

        $oSubscription = & new Subscription($res["user_id"],
                                            $res[Subscription::getIdFieldName($iSubscriptionType)],
                                            $iSubscriptionType,
                                            $res["is_alerted"]);
        $oSubscription->iId = $iSubscriptionID;
        return $oSubscription;
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
        $sQuery = "SELECT count(*) AS `subcount` FROM " . Subscription::getTableName($iSubscriptionType) . " WHERE " . Subscription::getIdFieldName($iSubscriptionType) . " = ? AND user_id = ?";
        $aParams = array($iExternalID, $iUserID);
        
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'subcount');
        
        if (PEAR::isError($res)) {
            return false;
        } else {
            return ($res == 1);
        }
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
        if ($iSubscriptionType == SubscriptionEvent::subTypes("Document")) {
            return "document_id";
        } else if($iSubscriptionType == SubscriptionEvent::subTypes("Folder")) {
            return "folder_id";
        }

    }
}
?>
