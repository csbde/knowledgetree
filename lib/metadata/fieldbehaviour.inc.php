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
require_once(KT_LIB_DIR . "/util/sanitize.inc");

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
    function getName() { return sanitizeForSQLtoHTML($this->sName); }
    function getHumanName() { return sanitizeForSQLtoHTML($this->sHumanName); }
    function getFieldID() { return $this->iFieldID; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = sanitizeForSQL($sName); }
    function setHumanName($sHumanName) { $this->sHumanName = sanitizeForSQL($sHumanName); }
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
