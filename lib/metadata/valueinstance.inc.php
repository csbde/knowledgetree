<?php
/**
 * $Id$
 *
 * Describes a behaviour that values in a lookup field can have that
 * define how they affect dependent columns in terms of restricting
 * the available lookups.
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

    function &getByLookup($oLookup) {
        $aOptions = array('multi' => true);
        $iLookupId = KTUtil::getId($oLookup);
        return KTEntityUtil::getBy('KTValueInstance', 'field_value_id', $iLookupId, $aOptions);
    }

    function &getByLookupAndParentBehaviour($oLookup, $oBehaviour, $aOptions = null) {
        $iLookupId = KTUtil::getId($oLookup);
        $iBehaviourId = KTUtil::getId($oBehaviour);
        $GLOBALS['default']->log->debug('KTValueInstance::getByLookupAndParentBehaviour: lookup id is ' . print_r($iLookupId, true));
        $GLOBALS['default']->log->debug('KTValueInstance::getByLookupAndParentBehaviour: behaviour id is ' . $iBehaviourId);
        $sInstanceTable = KTUtil::getTableName('field_value_instances');
        $sBehaviourOptionsTable = KTUtil::getTableName('field_behaviour_options');
        $aQuery = array(
            "SELECT instance_id FROM $sBehaviourOptionsTable AS BO INNER JOIN
            $sInstanceTable AS I ON BO.instance_id = I.id WHERE
            BO.behaviour_id = ? AND I.field_value_id = ?",
            array($iBehaviourId, $iLookupId),
        );
        $iId = DBUtil::getOneResultKey($aQuery, 'instance_id');
        if (PEAR::isError($iId)) {
            $GLOBALS['default']->log->error('KTValueInstance::getByLookupAndParentBehaviour: error from db is: ' . print_r($iId, true));
            return $iId;
        }
        if (is_null($iId)) {
            return null;
        }    
        $GLOBALS['default']->log->debug('KTValueInstance::getByLookupAndParentBehaviour: id of instance is ' . $iId);
        if (KTUtil::arrayGet($aOptions, 'ids')) {
            return $iId;
        }
        return KTValueInstance::get($iId);
    }
}

?>
