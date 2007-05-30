<?php

require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");

/**
 * $Id$
 *
 * Represents a document transaction as per the database table document_transaction.
  * No delete or update functions are provided.  Once a transaction has been stored 
 * in the database it may not be changed.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
 *
 * @version $Revision$
 * @author Rob Cherry, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.documentmanagement 
 */
 
require_once(KT_LIB_DIR . '/users/User.inc'); 
require_once(KT_LIB_DIR . '/documentmanagement/DocumentTransactionType.inc.php'); 
 
require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc'); 
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php'); 
 
class DocumentTransaction extends KTEntity {
	
	/** primary key of document transaction */
	var $iId = -1;
	var $iDocumentID;
	var $sVersion;
	var $iUserID;
	var $dDateTime;
	var $sIP;
	var $sFileName;
	var $sComment;
	var $sTransactionNS;
	var $bAdminMode = 0;
	
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
		$this->sVersion = KTUtil::arrayGet($aOptions, 'version', $oDocument->getMajorVersionNumber() . "." . $oDocument->getMinorVersionNumber());
		$oFolder = Folder::get($oDocument->getFolderId());
		// handle the case where oFolder is broken.
		if (PEAR::isError($oFolder) || ($oFolder == false)) {
		    $this->sFileName = '.../' . $oDocument->getName();
		} else {
		    $this->sFileName = addslashes($oFolder->sFullPath . "/" . $oFolder->getName() . "/" . $oDocument->getName());			
		}
		
		$this->iUserID = $_SESSION["userID"];
		$this->iSessionId = $_SESSION["sessionID"];
        $oUser = KTUtil::arrayGet($aOptions, 'user');
		
        if (!(PEAR::isError($oUser) || ($oUser == false))) {
            $this->iUserID = $oUser->getID();
        }
		$this->dDateTime = getCurrentDateTime();		
		$this->sIP = KTUtil::arrayGet($_SERVER, "REMOTE_ADDR", '0.0.0.0');
		
		$oFolder = Folder::get($oDocument->getFolderID());
		// head off the certain breakage down the line.
		if (PEAR::isError($oFolder) || ($oFolder === false)) {
			$this->bAdminMode = 0;
		} else {
		    if (KTBrowseUtil::inAdminMode($oUser, $oFolder)) {
				$this->bAdminMode = 1;
			} else {
			    $this->bAdminMode = 0;
			}		
		}
	}
	
	function getVersion() {	return $this->sVersion;	}
	function getComment() {	return $this->sComment;	}
    function _table() { return KTUtil::getTableName('document_transactions'); }
	
	// some of these can throw a PEAR::error
	function & getUser() { return User::get($this->iUserID); }
	function getFilename() { return $this->sFileName; }
	function getDate() { return $this->dDateTime; }
	function & getTransactionType() { return KTDocumentTransactionType::getByNamespace($this->sTransactionNS); }
	function getAction() {
		$oType = $this->getTransactionType();
		$action = _kt('Unknown Action');
		if (!PEAR::isError($oType)) { $action = $oType->getName(); }
		
		return $action;
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
            $aFieldValues = array(
                'document_id' => $this->iDocumentID,
                'version' => $this->sVersion,
                'user_id' => $this->iUserID,
                'datetime' => $this->dDateTime,
                'ip' => $this->sIP,
                'filename' => $this->sFileName,
                'comment' => $this->sComment,
                'transaction_namespace' => $this->sTransactionNS,
                'session_id' => $this->iSessionId,
				'admin_mode' => $this->bAdminMode,
            );
            $id =& DBUtil::autoInsert($this->_table(), $aFieldValues);

            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            $this->clearCachedGroups();
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
                $this->clearCachedGroups();
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
	        $sql->query(array("SELECT * FROM $default->document_transactions_table WHERE id = ?", $iDocumentTransactionID));/*ok*/
	        if ($sql->next_record()) {			
	            $oDocumentTransaction = & new DocumentTransaction(Document::get($sql->f("document_id")), $sql->f("comment"), $sql->f("transaction_namespace"));
	            $oDocumentTransaction->iId = $sql->f("id");	
				$oDocumentTransaction->sVersion = $sql->f("version");
				$oDocumentTransaction->iUserID = $sql->f("user_id");
				$oDocumentTransaction->dDateTime = $sql->f("datetime");		
				$oDocumentTransaction->sIP = $sql->f("ip");
				$oDocumentTransaction->sFileName = $sql->f("filename");
				$oDocumentTransaction->bAdminMode = $sql->f("admin_mode");
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
