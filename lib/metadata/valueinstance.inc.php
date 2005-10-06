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

    function getId() { return $this->iId; }
    function getFieldId() { return $this->iFieldId; }
    function getFieldValueId() { return $this->iFieldValueId; }
    function getBehaviourId() { return $this->iBehaviourId; }
    function setId($iId) { $this->iId = $iId; }
    function setFieldId($iFieldId) { $this->iFieldId = $iFieldId; }
    function setFieldValueId($iFieldValue) { $this->iFieldValueId = $iFieldValueId; }
    function setBehaviourId($iBehaviourId) { $this->iBehaviourId = $iBehaviourId; }

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
        $iFieldId = KTUtil::getId($oField);
        return KTEntityUtil::getBy('KTValueInstance', 'fieldid', $iFieldId);
    }

    function &getByLookupSingle($oLookup) {
        $aOptions = array('noneok' => true);
        $iLookupId = KTUtil::getId($oLookup);
        return KTEntityUtil::getBy('KTValueInstance', 'field_value_id', $iLookupId, $aOptions);
    }

    function &getByLookup($oLookup, $aOptions) {
        $aOptions = array('multi' => true);
        $iLookupId = KTUtil::getId($oLookup);
        return KTEntityUtil::getBy('KTValueInstance', 'field_value_id', $iLookupId, $aOptions);
    }
}

?>
