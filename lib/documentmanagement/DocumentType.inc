<?php
/**
 * $Id$
 *
 * Represents a document type as per the database document_types_lookup table.
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
 */

class DocumentType extends KTEntity {
	
	/** primary key value */
	var $iId;
	/** document type name */
	var $sName;
	/** disabled boolean */
	var $bDisabled;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
		'bDisabled' => 'disabled'
    );
	
	/**
	* Default constructor
	*
	* @param 	Name of document type
	* @param 	document type data type
	*
	*/
	function DocumentType($sNewName = null) {
		//object not created yet
		$this->iId = -1;
		$this->sName = $sNewName;
		$this->bDisabled = false;
	}
	
	function getID() { return $this->iId; }
	function getName() { return $this->sName; }
	function setName($sNewValue) { $this->sName = $sNewValue; }
	function getDisabled() { return $this->bDisabled; }
    function setDisabled($bNewValue) { $this->bDisabled = ($bNewValue) ? true : false; }

    function _fieldValues () {
        return array(
            'name' => $this->sName,
	        'disabled' => $this->bDisabled,
        );
    }

    function _table () { return KTUtil::getTableName('document_types'); }
	
	function delete() {
        if ($this->isUsed()) {
            return PEAR::raiseError(_kt('Document type still in use'));
        }
        return parent::delete();
	}

    function isUsed() {
        $sTable = KTUtil::getTableName('documents');
        $res = DBUtil::getOneResultKey(array(
            "SELECT COUNT(id) AS cnt FROM documents WHERE document_type_id = ?",
            array($this->getId()),
        ), 'cnt');
        if (PEAR::isError($res)) {
            return $res;
        }
        if ($res != 0) {
            return true;
        }
        return false;
    }

    /* 
    Get the fieldsets associated with this document type.
    Simplifies listing associated fieldsets in doctypes displays.
    */

    function &getFieldsets() {
	return KTFieldset::getForDocumentType($this);
    }
	
	/**
 	 * Static- Get a list document types;
	 *
	 * @param 	String		Where clause (not required)
	 *
	 * @return Array array of DocumentType objects, false otherwise
	*/
	function &getList($sWhereClause = null) {
	    // default ordering to name
	    $aOptions = array('orderby' => 'name');
	    return KTEntityUtil::getList2('DocumentType', $sWhereClause, $aOptions);
	}
	
	/* alternative for use in creation:  delegate for user and location */
	function &getListForUserAndFolder($oUser, $oFolder) {
	    $src =& KTDocumentTypeManager::getSingleton();
		return $src->getListForUserAndFolder($oUser, $oFolder);
	}

    function &createFromArray($aArray) {
        return KTEntityUtil::createFromArray('DocumentType', $aArray);
    }
    
    function &get($iId) { return KTEntityUtil::get('DocumentType', $iId); }
	
	

}


/**
* Static function
*
* Creates a document type object from an array
*
* @param 	Array		Array of parameters.  Must match order of parameters in constructor
*
* @return User user object
*/
function & documenttypeCreateFromArray($aParameters) {
	$oDocType = & new DocumentType($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
	return $oDocType;
}


class DemoDelegation {
    var $handler_ns = 'brad.oddhandler';
	var $handler_name = null;
	
	function DemoDelegation() {
	    $this->handler_name = _kt('Demo Delegator');
	}
	
    function &getListForUserAndFolder($oUser, $oFolder) {
	    $list =&  DocumentType::getList();
		$finallist = array();
		foreach ($list as $oType) {
		    if ($oType->getId() % 2 == 0) {
			    $finallist[] = $oType;
			}
		}
		return $finallist;
	}
}

/* simple singleton util class */
class KTDocumentTypeManager {
    var $_handlers = array();
	var $_active_handler = null;
	var $_checked = false;

    function &getSingleton() {
        if (!KTUtil::arrayGet($GLOBALS, 'oKTDocumentTypeManager')) {
            $GLOBALS['oKTDocumentTypeManager'] = new KTDocumentTypeManager;
        }
        return $GLOBALS['oKTDocumentTypeManager'];
    }
		
	function &getListForUserAndFolder($oUser, $oFolder) {
	    $this->checkActiveHandler();
	    if (is_null($this->_active_handler)) {
		    // as totally normal if nothing is registered.
		    $l = (array) DocumentType::getList();
		} else {
		    $l = (array) $this->_active_handler->getListForUserAndFolder($oUser, $oFolder);
		}
		
        $final = array();
        foreach ($l as $oType) {
            if (PEAR::isError($oType) || ($oType->getDisabled())) {
                continue;
            }
            $final[] = $oType;
        }
        return $final;
	}
	
	function checkActiveHandler() {
	    if ($this->_checked) { return ; }
	    // not perfect - see workflow-delegator for explanation.
		$res = KTUtil::getSystemSetting('documenttypehandler');
		
		if (empty($res) || PEAR::isError($res)) { // just fail silently - don't degrade the system
		    $this->_active_handler = null;
		} else {
		    $ns = $res;
			$this->_active_handler = KTUtil::arrayGet($this->_handlers, $ns);
		}
		$this->_checked = true;
		
		return ;
	}
	
	function registerHandler($oHandler) {
	    $this->_handlers[$oHandler->handler_ns] = $oHandler;
	}
}


?>
