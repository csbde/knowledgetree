<?php
/**
 * $Id$
 *
 * Represents a document transaction as per the database table document_transaction.
  * No delete or update functions are provided.  Once a transaction has been stored
 * in the database it may not be changed.
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009 KnowledgeTree Inc.
 *
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
 * California 94120-7775, or email info@knowledgetree.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * KnowledgeTree" logo and retain the original copyright notice. If the display of the
 * logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
 * must display the words "Powered by KnowledgeTree" and retain the original
 * copyright notice.
 * Contributor( s): ______________________________________
 */

require_once(KT_LIB_DIR . "/documentmanagement/documentutil.inc.php");

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
	var $iSessionId;
	var $bAdminMode = 0;

	var $_aFieldToSelect = array(
        'iId' => 'id',
        'iDocumentID' => 'document_id',
        'sVersion' => 'version',
        'iUserID' => 'user_id',
        'dDateTime' => 'datetime',
        'sIP' => 'ip',
        'sFileName' => 'filename',
        'sComment' => 'comment',
        'sTransactionNS' => 'transaction_namespace',
        'iSessionId' => 'session_id',
        'bAdminMode' => 'admin_mode',
    );

	/**
	* Constructor
	*
	* @param 	Primary key of document on which transaction was performed
	* @param 	User comment associated with transaction
	* @param 	Primary key of transaction type
	*
	*/
	function DocumentTransaction($oDocument = null, $sNewComment = null, $sTransactionNS = null, $aOptions = null) {
        if (is_null($aOptions)) {
            $aOptions = array();
        }

        // class breaks convention and passes the document object into the constructor
        // ktentity creates the object to get the sql columns, adding a workaround to exit if the document object is empty
        if(empty($oDocument)){
            return ;
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
	            $oDocumentTransaction = new DocumentTransaction(Document::get($sql->f("document_id")), $sql->f("comment"), $sql->f("transaction_namespace"));
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
