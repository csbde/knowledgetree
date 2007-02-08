<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
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
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */
class Unit extends KTEntity {
    var $_bUsePearError = true;

    var $iId;
    var $sName;
	var $iFolderID;

    var $_aFieldToSelect = array(
        'iId' => 'id',
        'sName' => 'name',
        'iFolderId' => 'folder_id',
    );

    function _table () {
        return KTUtil::getTableName('units');
    }

    function getName() { return $this->sName; }
    function setName($sNewValue) { $this->sName = $sNewValue; }
    function getFolderId() { return $this->iFolderId; }
    function setFolderId($iNewValue) { $this->iFolderId = $iNewValue; }

    function &get($iId) {
        return KTEntityUtil::get('Unit', $iId);
    }

    function getList($sWhereClause = null) {
        return KTEntityUtil::getList2('Unit', $sWhereClause);
    }

    function &createFromArray($aArray) {
        return KTEntityUtil::createFromArray('Unit', $aArray);
    }

    function &getByFolder($oFolder) {
        $iFolderId = KTUtil::getId($oFolder);
        return KTEntityUtil::getByDict('Unit', array(
            'folder_id' => $iFolderId,
        ), array(
            'noneok' => true,
        ));
    }

    function getFolder() {
        require_once(KT_LIB_DIR . '/foldermanagement/Folder.inc');
        return Folder::get($this->iFolderId);
    }

    function getUnitsForFolder($oFolder) {
        $oFolder =& KTUtil::getObject('Folder', $oFolder);
        $sUnitTable = KTUtil::getTableName('units');
        if (PEAR::isError($oFolder)) {  // can't be admin for a non-existant folder.
            return false;
        }
        $sFolderIds = $oFolder->getParentFolderIds();
        $aFolderIds = split(",", $sFolderIds);
        $aFolderIds[] = $oFolder->getId();

        $sParams = DBUtil::paramArray($aFolderIds);
        $aParams = $aFolderIds;
        $sQuery = "SELECT id FROM $sUnitTable WHERE folder_id IN ($sParams)";
        $aUnitIds = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');

        $aRet = array();
        foreach ($aUnitIds as $iId) {
            $aRet[] = Unit::get($iId);
        }
        return $aRet;
    }
}

?>
