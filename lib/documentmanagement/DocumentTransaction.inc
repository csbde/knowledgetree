<?php

require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");

/**
 * $Id$
 *
 * Represents a document transaction as per the database table document_transaction.
  * No delete or update functions are provided.  Once a transaction has been stored 
 * in the database it may not be changed.
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
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.documentmanagement 
 */
class DocumentTransaction {
	
	/** primary key of document transaction */
	var $iId = -1;
	var $iDocumentID;
	var $sVersion;
	var $iUserID;
	var $dDateTime;
	var $sIP;
	var $sFilename;
	var $sComment;
	var $sTransactionNS;
	
	/**
	* Constructor
	*
	* @param 	Primary key of document on which transaction was performed
	* @param 	User comment associated with transaction
	* @param 	Primary key of transaction type
	*	
	*/
	function DocumentTransaction($oDocument, $sNewComment, $sTransactionNS, $aOptions = null) {
        if (is_null($aOptions)) {
            $aOptions = array();
        }
		//object not stored yet, id = -1
		$this->iId = -1;
		
		$this->iDocumentID = $oDocument->getID();
		$this->sComment = $sNewComment;
		$this->sTransactionNS = $sTransactionNS;
		$this->sVersion = $oDocument->getMajorVersionNumber() . "." . $oDocument->getMinorVersionNumber();
		$oFolder = Folder::get($oDocument->iFolderID);
		$this->sFileName = addslashes($oFolder->sFullPath . "/" . $oFolder->getName() . "/" . $oDocument->getName());			
		
		$this->iUserID = $_SESSION["userID"];
        $oUser = KTUtil::arrayGet($aOptions, 'user');
		
        if (!(PEAR::isError($oUser) || ($oUser == false))) {
            $this->iUserID = $oUser->getID();
        }
		$this->dDateTime = getCurrentDateTime();		
		$this->sIP = KTUtil::arrayGet($_SERVER, "REMOTE_ADDR", '0.0.0.0');
	}
	
	function getVersion() {	return $this->sVersion;	}
	function getComment() {	return $this->sComment;	}
    function _table() { return KTUtil::getTableName('document_transactions'); }
	
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
            $aFieldValues = array(
                'document_id' => $this->iDocumentID,
                'version' => $this->sVersion,
                'user_id' => $this->iUserID,
                'datetime' => $this->dDateTime,
                'ip' => $this->sIP,
                'filename' => $this->sFileName,
                'comment' => $this->sComment,
                'transaction_namespace' => $this->sTransactionNS,
            );
            $id =& DBUtil::autoInsert($this->_table(), $aFieldValues);

            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            KTDocumentUtil::updateTransactionText($this->iDocumentID);
            KTDocumentUtil::updateSearchableText($this->iDocumentID);
            return true;
		} else {
			$_SESSION["errorMessage"] = $lang_err_object_exists;
			return false;
		}
		
	}
	
	function delete() {
		global $default, $lang_err_database, $lang_err_object_key;
        if ($this->iId >= 0) {
            $sql = $default->db;
            $result = $sql->query("DELETE FROM  " . $default->document_transactions_table . "  WHERE id = " . $this->iId);
            if ($result) {
                $this->iId = -1;
                return true;
            }
            $_SESSION["errorMessage"] = $lang_err_database;
            KTDocumentUtil::updateTransactionText($this->iDocumentID);
            KTDocumentUtil::updateSearchableText($this->iDocumentID);
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
	        $sql->query(array("SELECT * FROM $default->document_transactions_table WHERE id = ?", $iDocumentTransactionID));/*ok*/
	        if ($sql->next_record()) {			
	            $oDocumentTransaction = & new DocumentTransaction(Document::get($sql->f("document_id")), $sql->f("comment"), $sql->f("transaction_namespace"));
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
        return KTEntityUtil::getList2('DocumentTransaction', $sWhereClause);
    }

    function getByDocument($oDocument) {
        $iDocumentId = KTUtil::getId($oDocument);
        return DocumentTransaction::getList(array('document_id = ?', array($iDocumentId)));
    }
}
?>
