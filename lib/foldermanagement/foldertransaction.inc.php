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
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTFolderTransaction extends KTEntity {
    var $_aFieldToSelect = array(
        'iId' => 'id',
        'iFolderId' => 'folder_id',
        'iUserId' => 'user_id',
        'dDateTime' => 'datetime',
        'sIp' => 'ip',
        'sComment' => 'comment',
        'sTransactionNS' => 'transaction_namespace',
        'iSessionId' => 'session_id',
        'bAdminMode' => 'admin_mode',
    );

    var $_bUsePearError = true;

    function _table () {
        return KTUtil::getTableName('folder_transactions');
    }

    function _fieldValues() {
        if (empty($this->dDateTime)) {
            $this->dDateTime = getCurrentDateTime();
        }
        if (empty($this->iSessionId)) {
            $this->iSessionId = $_SESSION['sessionID'];
        }
        $oFolder = Folder::get($this->iFolderId);
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
        return parent::_fieldValues();
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTFolderTransaction', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTFolderTransaction', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        return KTEntityUtil::getList2('KTFolderTransaction', $sWhereClause);
    }

    function &getByFolder($oFolder, $aOptions = null) {
        $aOptions = KTUtil::meldOptions(array(
            'multi' => true,
        ), $aOptions);
        $iFolderId = KTUtil::getId($oFolder);
        return KTEntityUtil::getByDict('KTFolderTransaction', array(
            'folder_id' => $iFolderId,
        ), $aOptions);
    }

    function &getByUser($oFolder, $aOptions = null) {
        $aOptions = KTUtil::meldOptions(array(
            'multi' => true,
        ), $aOptions);
        $iFolderId = KTUtil::getId($oFolder);
        return KTEntityUtil::getByDict('KTFolderTransaction', array(
            'folder_id' => $iFolderId,
        ), $aOptions);
    }
}

?>
