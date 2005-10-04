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

class KTValueInstance extends KTEntity {
    var $iId = -1;
    var $iFieldId;
    var $iFieldValueId;
    var $iBehaviourId;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iFieldId" => "field_id",
        "iFieldValueId" => "field_value_id",
        "iBehaviourId" => "behaviour_id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getFieldID() { return $this->iFieldID; }
    function getFieldValue() { return $this->iFieldValue; }
    function getBehaviourID() { return $this->iBehaviourID; }
    function setID($iId) { $this->iId = $iId; }
    function setFieldID($iFieldID) { $this->iFieldID = $iFieldID; }
    function setFieldValue($iFieldValue) { $this->iFieldValue = $iFieldValue; }
    function setBehaviourID($iBehaviourID) { $this->iBehaviourID = $iBehaviourID; }

    function _table () {
        return KTUtil::getTableName('field_value_instances');
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTValueInstance', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTValueInstance', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permissions_table, 'KTValueInstance', $sWhereClause);
    }

    // STATIC
    function &getByField($oField) {
        if (is_object($oField)) {
            $iFieldID = $oField->getID();
        } else {
            $iFieldID = $oField;
        }
        return KTEntityUtil::getBy('KTValueInstance', 'fieldid', $iFieldID);
    }
}

?>
