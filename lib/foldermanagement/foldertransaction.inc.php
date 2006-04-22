<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
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
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
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
