<?php

DEFINE("CREATE", 1);
DEFINE("UPDATE", 2);
DEFINE("DELETE", 3);
DEFINE("RENAME", 4);
DEFINE("MOVE", 5);
DEFINE("DOWNLOAD", 6);
DEFINE("CHECKIN", 7);
DEFINE("CHECKOUT", 8);
DEFINE("COLLAB_ROLLBACK",9);
DEFINE("VIEW", 10);

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
	 * Returns the current version
	 */
	function getVersion() {
		return $this->sVersion;
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
	
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
        if ($this->iId >= 0) {
            $sql = $default->db;
            // TODO: insert into sys_deleted
            //$result = $sql->query("INSERT INTO  " . $default->owl_sys_deleted_table . " () VALUES ()");
            $result = $sql->query("DELETE FROM  " . $default->owl_document_transactions_table . "  WHERE id = " . $this->iId);
            if ($result) {
                $this->iId = -1;
                return true;
            }
            $_SESSION["errorMessage"] = $lang_err_database;
            return false;
        }
        $_SESSION["errorMessage"] = $lang_err_object_key;
        return false;
	}
	
    /**
     * Static function.  Given a document transaction primary key will create
     * a document transaction object and populate it with the corresponding
     * database values
     *
     * @return DocumentTransaction populated DocumentTransaction object on success, false otherwise and set $_SESSION["errorMessage"]
     */
    function & get($iDocumentTransactionID) {
        global $default, $lang_err_doc_not_exist;
        if (strlen($iDocumentTransactionID) > 0) {
	        $sql = $default->db;
	        $sql->query("SELECT * FROM $default->owl_document_transactions_table WHERE id = $iDocumentTransactionID");
	        if ($sql->next_record()) {			
	            $oDocumentTransaction = & new DocumentTransaction($sql->f("document_id"), stripslashes($sql->f("comment")), $sql->f("transaction_id"));
	            $oDocumentTransaction->iId = $sql->f("id");	
				$oDocumentTransaction->sVersion = $sql->f("version");
				$oDocumentTransaction->iUserID = $sql->f("user_id");
				$oDocumentTransaction->dDateTime = $sql->f("datetime");		
				$oDocumentTransaction->sIP = $sql->f("ip");
				$oDocumentTransaction->sFileName = $sql->f("filename");
	            return $oDocumentTransaction;
	        }
	        $_SESSION["errorMessage"] = $lang_err_object_not_exist . "id = " . $iDocumentTransactionID . " table = document_transactions";
	        return false;
        } else {
        	$_SESSION["errorMessage"] = "document transaction ID not set.  Cannot retrieve document with no id";
        	return false;
        }
    }

    /**
     * Static function
     * Get a list of DocumentTransactions
     *
     *  @param  String  Where clause (not required)
     *
     * @return Array array of DocumentTransaction objects, false otherwise and set $_SESSION["errorMessage"]
     */
    function getList($sWhereClause = null) {
        global $default, $lang_err_database;
        $aDocumentTransactionArray = array();
        $sql = $default->db;
        $sQuery = "SELECT * FROM $default->owl_document_transactions_table " . (isset($sWhereClause) ? " WHERE " . $sWhereClause : "");
        $result = $sql->query($sQuery);
        if ($result) {
            while ($sql->next_record()) {
            	$oDocumentTransaction = & DocumentTransaction::get($sql->f("id"));
                $aDocumentTransactionArray[] = $oDocumentTransaction;
            }
            return $aDocumentTransactionArray;
        }
        $_SESSION["errorMessage"] = $lang_err_database;
        return false;
    }
}
?>