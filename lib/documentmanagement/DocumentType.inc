<?php
/**
 * $Id$
 *
 * Represents a document type as per the database document_types_lookup table.
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

require_once(KT_LIB_DIR . "/util/sanitize.inc");

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
	function getName() { return ($this->sName); }
	function setName($sNewValue) { $this->sName = ($sNewValue); }
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
        $sTable = KTUtil::getTableName('document_metadata_version');
        $res = DBUtil::getOneResultKey(array(
            "SELECT COUNT(id) AS cnt FROM {$sTable} WHERE document_type_id = ?",
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
	* Get the document types associated with a document
	*/
	function &getAssociatedTypes() {
        $sTable = KTUtil::getTableName('document_metadata_version');
        $sQuery = "SELECT DISTINCT(document_type_id) FROM {$sTable}";
        $aDocTypes = DBUtil::getResultArray($sQuery);

        $aTypes = array();
        if(!empty($aDocTypes)){
            foreach($aDocTypes as $type){
                $aTypes[] = $type['document_type_id'];
            }
        }

        return $aTypes;
	}

	/**
	* Check if a type is associated with any documents
	*/
	function checkDelete($aAssocDocs, $sId) {
        if(in_array($sId, $aAssocDocs)){
            return FALSE;
        }else{
            return TRUE;
        }
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
	$oDocType = new DocumentType($aParameters[0], $aParameters[1], $aParameters[2], $aParameters[3], $aParameters[4], $aParameters[5], $aParameters[6], $aParameters[7], $aParameters[8], $aParameters[9], $aParameters[10]);
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
