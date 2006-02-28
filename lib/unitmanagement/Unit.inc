<?php
/**
 * $Id$
 *
 * Copyright (c) 2006, 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 */
class Unit extends KTEntity {
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
}

?>
