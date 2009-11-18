<?php
/**
 * $Id$
 *
 * Describes a behaviour that values in a lookup field can have that
 * define how they affect dependent columns in terms of restricting
 * the available lookups.
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
