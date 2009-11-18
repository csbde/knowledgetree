<?php
/**
 * $Id$
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
