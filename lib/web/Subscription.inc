<?php
/**
*
* Class Subscription
* Represents a subscription as per the subscriptions database table
*
* @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
* @date 20 January 2003
* @package lib.web
*/

class Subscription {
	
	var $iId;
	var $iUserID;
	var $iDocumentID;
	
	function Subscription($iNewUserID, $iNewDocumentID) {
		$this->iId = -1;
		$this->iUserID = $iNewUserID;
		$this->iDocumentID = $iNewDocumentID;
	}
	
	function getID() {
		return $this->iID;
	}
	
	function getUserID() {
		return $this->iUserID;
	}
	
	function setUserID($iNewValue) {
		$this->iNewValue = $iNewValue;
	}
	
	function getDocumentID() {
		return $this->iDocumentID;
	}
	
	function setDocumentID($iNewValue) {
		$this->iDocumentID = $iNewValue;
	}
	
	/**
	* Create the current object in the database
	*
	* @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create() {
		global $default, $lang_err_database, $lang_err_object_exists;
		//if the object hasn't been created
		if ($this->iId < 0) {
			$sql = $default->db;
			$result = $sql->query("INSERT INTO " . $default->owl_subscriptions_table . " (user_id, document_id) VALUES ($this->iUserID, $this->iDocumentID)");
			if ($result) {
				$this->iId = $sql->insert_id();
				return  true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_exists . "id = " . $this->iId . " table = document_fields";
		return false;
	}
	
	/**
	* Update the values in the database table with the object's current values
	*
	* @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function update() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only update if the object has been stored
		if ($this->iId > 0) {
			$sql = $default->db;
			$result = $sql->query("UPDATE " . $default->owl_subscriptions_table . " SET user_id = $this->iUserID, document_id = $this->iDocumentID WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}
	
	/**
	* Delete the current object from the database
	*
	* @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
		//only delete the object if it exists in the database
		if ($this->iId >= 0) {
			$sql = $default->db;
			$result = $sql->query("DELETE FROM $default->owl_subscriptions_table WHERE id = $this->iId");
			if ($result) {
				return true;
			}
			$_SESSION["errorMessage"] = $lang_err_database;
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_object_key;
		return false;
	}
	
	/**
	* Static function.
	* Given a subscriptions primary key it will create a 
	* Subscription object and populate it with the 
	* corresponding database values
	*
	* @return Subscription populated Subscription object on successful query, false otherwise and set $_SESSION["errorMessage"]
	*/
	function & get($iSubscriptionID) {
		global $default;
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM $default->owl_subscriptions_table WHERE id = $iSubscriptionID");
		if ($result) {
			if ($sql->next_record()) {
				$oSubscription = & new Subscription($sql->f("user_id"), $sql->f("document_id"));
				$oSubscription->iId = $iSubscriptionID;
				return $oSubscription;
			}
			$_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iWebDocumentsID . " table = $default->owl_subscriptions_table";
			return false;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
	/**
	* Static function
	* Get a list of subscriptions
	*
	* @param 	String		Where clause (not required)
	*
	* @return Array array of Subscription objects, false otherwise and set $_SESSION["errorMessage"]
	*/
	function getList($sWhereClause = null) {
		global $default, $lang_err_database;
		$aSubscriptionArray;
		settype($aSubscriptionArray, "array");
		$sql = $default->db;
		$result = $sql->query("SELECT * FROM " . $default->owl_subscriptions_table  . (isset($sWhereClause) ? " " . $sWhereClause : ""));
		if ($result) {			
			$iCount = 0;
			while ($sql->next_record()) {
				$oSubscription = & Subscription::get($sql->f("id"));
				$aSubscriptionArray[$iCount] = $oSubscription;
				$iCount++;
			}
			return $aSubscriptionArray;
		}
		$_SESSION["errorMessage"] = $lang_err_database;
		return false;
	}
	
}
?>
