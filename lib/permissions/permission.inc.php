<?php

/**
 * $Id$
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
 */

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTPermission extends KTEntity {
    /** primary key */
    var $iId = -1;
    /** help file name */
    var $sName;
    /** help file name */
    var $sHumanName;
    /** whether it's built into KT */
    var $bBuiltIn = false;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sHumanName" => "human_name",
        "bBuiltIn" => "built_in",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function getHumanName() { return _kt($this->sHumanName); }
    function getBuiltIn() { return $this->bBuiltIn; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setHumanName($sHumanName) { $this->sHumanName = $sHumanName; }
    function setBuiltIn($sBuiltIn) { $this->sBuiltIn = $sBuiltIn; }

    function _table () {
        global $default;
        return $default->permissions_table;
    }

    // STATIC
    function &get($iId) { return KTEntityUtil::get('KTPermission', $iId); }
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('KTPermission', $aOptions); }
    function &getList($sWhereClause = null) { return KTEntityUtil::getList2('KTPermission', $sWhereClause); }
    
    // STATIC
    function &getDocumentRelevantList($sWhereClause = null) {
        $aList =& KTEntityUtil::getList2('KTPermission', $sWhereClause);
        if (PEAR::isError($aList)) { return $aList; }
        
        $nonrelevant = array(
            'ktcore.permissions.addFolder' => true,
            'ktcore.permissions.folder_details' => true,            
        );
        
        $aSecondaryList = array();
        foreach ($aList as $oPerm) {
            if ($nonrelevant[$oPerm->getName()]) {
                continue;
            }
            $aSecondaryList[] = $oPerm;
        }
        return $aSecondaryList;
    }    

    // STATIC
    function &getByName($sName) {
        $aOptions = array("cache" => "getByName");
        return KTEntityUtil::getBy('KTPermission', 'name', $sName, $aOptions);
    }
}

?>
