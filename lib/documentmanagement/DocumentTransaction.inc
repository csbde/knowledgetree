<?php

DEFINE("CREATE", 1);
DEFINE("UPDATE", 2);
DEFINE("DELETE", 3);
DEFINE("RENAME", 4);
DEFINE("MOVE", 5);
DEFINE("DOWNLOAD", 5);

/**
*
* Class DocumentTransaction
*
* Represents a document transaction as per the database table document_transaction
*
* No delete or update functions are provided.  Once a transaction has been stored 
* in the database it may not be changed.
*
* @author Rob Cherry, Jam Warehouse(Pty) Ltd, South Africa
* @date 18 January 2003
* @package lib.documentmanagement
*/

class DocumentTransaction {
	
	//these values must match the values in database table document_transaction_type_lookup
	/*define(1, "CREATE");
	define(2, "UPDATE");
	define(3, "DELETE");
	define(4, "RENAME");
	define(5, "MOVE");*/
	
	/** primary key of document transaction */
	var $iId;
	/** primary key of document on which transaction occured */
	var $iDocumentID;
	/** version of document on which transaction occurs */
	var $sVersion;
	/** primary key of user who performed transaction */
	var $iUserID;
	/** time of transaction */
	var $dDateTime;
	/** IP address of computer that user was logged onto */
	var $sIP;
	/** path to documet on file system on which transaction was performed */
	var $sFilename;
	/** user comment associated with transaction */
	var $sComment;
	/** primary key of transaction type */
	var $iTransactionID;
	
	/**
	* Constructor
	*
	* @param 	Primary key of document on which transaction was performed
	* @param 	User comment associated with transaction
	* @param 	Primary key of transaction type
	*	
	*/
	function DocumentTransaction($iNewDocumentID, $sNewComment, $iNewTransactionID) {
		//object not stored yet, id = -1
		$this->iId = -1;
		$this->iDocumentID = $iNewDocumentID;
		$this->sComment = $sNewComment;
		$this->iTransactionID = $iNewTransactionID;
		$oDocument = & Document::get($iNewDocumentID);
		$this->sVersion = $oDocument->getMajorVersionNumber() . "." . $oDocument->getMinorVersionNumber();
		$this->iUserID = $_SESSION["userID"];
		$this->dDateTime = getCurrentDateTime();		
		$this->sIP = $_SERVER["REMOTE_ADDR"];
		$this->sFileName = Folder::getFolderPath($oDocument->getFolderID()) . "/" . $oDocument->getName();
	}
	
	/**
	* Create the document transaction in the database
	*
	* @return boolean true on successful creation, false otherwise and set $_SESSION["errorMessage"]
	*
	*/
	function create() {
		global $default, $lang_err_object_exists;
		//if the object hasn't been stored yet
		if ($this->iId < 0) {
			$sql = $default->db;
			$result = $sql->query("INSERT INTO " . $default->owl_document_transactions_table . " (document_id, version, user_id, datetime, ip, filename, comment, transaction_id) " .
						"VALUES ($this->iDocumentID, '" . addslashes($this->sVersion) . "', $this->iUserID, '" . addslashes($this->dDateTime) . "', '" . addslashes($this->sIP) . "', '" . addslashes($this->sFileName) . "', '" . addslashes($this->sComment) . "', $this->iTransactionID)");
			 if ($result) {
				 //object has been stored, set the primary key
				 $this->iId = $sql->insert_id();
				 return true;
			 } else {
				 $_SESSION["errorMessage"] = $lang_err_database;
				 return false;
			 }
		} else {
			$_SESSION["errorMessage"] = $lang_err_object_exists;
			return false;
		}
		
	}
	
	
}
?>
