<?php
/**
 * $Id$
 *
 * Describes a behaviour that values in a lookup field can have that
 * define how they affect dependent columns in terms of restricting
 * the available lookups.
 *
 * Copyright (c) 2005 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTFieldBehaviour extends KTEntity {
    var $iId = -1;
    var $sName;
    var $sHumanName;
    var $iFieldID;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sHumanName" => "human_name",
        "iFieldID" => "field_id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function getHumanName() { return $this->sHumanName; }
    function getFieldID() { return $this->iFieldID; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setHumanName($sHumanName) { $this->sHumanName = $sHumanName; }
    function setFieldID($iFieldID) { $this->iFieldID = $iFieldID; }

    function _table () {
        return KTUtil::getTableName('field_behaviours');
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTFieldBehaviour', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTFieldBehaviour', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permissions_table, 'KTFieldBehaviour', $sWhereClause);
    }

    // STATIC
    function &getByName($sName) {
        return KTEntityUtil::getBy('KTFieldBehaviour', 'name', $sName);
    }

    // STATIC
    function &getByField($oField) {
        $iFieldId = KTUtil::getId($oField);
        $aOptions = array('multi' => true);
        return KTEntityUtil::getBy('KTFieldBehaviour', 'field_id', $iFieldId, $aOptions);
    }
}

?>
