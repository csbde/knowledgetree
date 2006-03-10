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

class KTPermissionLookupAssignment extends KTEntity {
    /** primary key */
    var $iId = -1;

    var $iPermissionID;
    var $iPermissionLookupID;
    var $iPermissionDescriptorID;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iPermissionID" => "permission_id",
        "iPermissionLookupID" => "permission_lookup_id",
        "iPermissionDescriptorID" => "permission_descriptor_id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }
    function getPermissionID() { return $this->iPermissionID; }
    function setPermissionID($iPermissionID) { $this->iPermissionID = $iPermissionID; }
    function getPermissionLookupID() { return $this->iPermissionLookupID; }
    function setPermissionLookupID($iPermissionLookupID) { $this->iPermissionLookupID = $iPermissionLookupID; }
    function getPermissionDescriptorID() { return $this->iPermissionDescriptorID; }
    function setPermissionDescriptorID($iPermissionDescriptorID) { $this->iPermissionDescriptorID = $iPermissionDescriptorID; }

    function _table () {
        global $default;
        return $default->permission_lookup_assignments_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermissionLookupAssignment', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermissionLookupAssignment', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList2('KTPermissionLookupAssignment', $sWhereClause);
    }

    function &getByPermissionAndDescriptor($oPermission, $oDescriptor) {
        return KTEntityUtil::getByDict('KTPermissionLookupAssignment', array(
            'permission_id' => $oPermission->getId(),
            'permission_descriptor_id' => $oDescriptor->getId(),
        ), array('multi' => true));
    }

    function _cachedGroups() {
        return array('getList', 'getByPermissionAndLookup');
    }

    function &getByPermissionAndLookup($oPermission, $oLookup) {
        $aOptions = array('cache' => 'getByPermissionAndLookup');
        return KTEntityUtil::getByDict('KTPermissionLookupAssignment', array(
            'permission_id' => $oPermission->getId(),
            'permission_lookup_id' => $oLookup->getId(),
        ), $aOptions);
    }

    function &_getLookupIDsByPermissionIDAndDescriptorID($iPermissionID, $iDescriptorID) {
        return KTEntityUtil::getByDict('KTPermissionLookupAssignment', array(
            'permission_id' => $iPermissionID,
            'permission_descriptor_id' => $iDescriptorID,
        ), array('multi' => true, 'ids' => true, 'idfield' => 'permission_lookup_id'));
    }

    function &findOrCreateLookupByPermissionDescriptorMap($aMapPermDesc) {
        $aOptions = array();
        foreach ($aMapPermDesc as $iPermissionID => $iDescriptorID) {
            $aThisOptions = array();
            foreach (KTPermissionLookupAssignment::_getLookupIDsByPermissionIDAndDescriptorID($iPermissionID, $iDescriptorID) as $iPLID) {
                $aThisOptions[] = $iPLID;
            }
            $aOptions[] = $aThisOptions;
        }
        if (count($aOptions) > 1) {
            $aPLIDs = call_user_func_array('array_intersect', $aOptions);
        } elseif (count($aOptions) == 1) {
            $aPLIDs = $aOptions[0];
        } else {
            $aPLIDs = array();
        }
        if (empty($aPLIDs)) {
            $oPL = KTPermissionLookup::createFromArray(array());
            $iPLID = $oPL->getID();
            foreach ($aMapPermDesc as $iPermissionID => $iDescriptorID) {
                $res = KTPermissionLookupAssignment::createFromArray(array(
                    'permissionlookupid' => $iPLID,
                    'permissionid' => $iPermissionID,
                    'permissiondescriptorid' => $iDescriptorID,
                ));
            }
            return $oPL;
        }
        sort($aPLIDs);
        $res = KTPermissionLookup::get($aPLIDs[0]);
        return $res;
    }
}

?>
