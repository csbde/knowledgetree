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

class KTPermissionAssignment extends KTEntity {
    /** primary key */
    var $iId = -1;

    var $iPermissionID;
    var $iPermissionObjectID;
    var $iPermissionDescriptorID;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iPermissionID" => "permission_id",
        "iPermissionObjectID" => "permission_object_id",
        "iPermissionDescriptorID" => "permission_descriptor_id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }
    function getPermissionID() { return $this->iPermissionID; }
    function setPermissionID($iPermissionID) { $this->iPermissionID = $iPermissionID; }
    function getPermissionObjectID() { return $this->iPermissionObjectID; }
    function setPermissionObjectID($iPermissionObjectID) { $this->iPermissionObjectID = $iPermissionObjectID; }
    function getPermissionDescriptorID() { return $this->iPermissionDescriptorID; }
    function setPermissionDescriptorID($iPermissionDescriptorID) { $this->iPermissionDescriptorID = $iPermissionDescriptorID; }

    function _table () {
        global $default;
        return $default->permission_assignments_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermissionAssignment', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermissionAssignment', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permission_assignments_table, 'KTPermissionAssignment', $sWhereClause);
    }

    function &getByPermissionAndObject($oPermission, $oObject) {
        return KTEntityUtil::getByDict('KTPermissionAssignment', array(
            'permission_id' => $oPermission->getId(),
            'permission_object_id' => $oObject->getId(),
        ));
    }

    function &getByObjectMulti($oObject) {
        return KTEntityUtil::getByDict('KTPermissionAssignment', array(
            'permission_object_id' => $oObject->getId(),
        ), array('multi' => true));
    }
}

?>
