<?php

require_once(KT_LIB_DIR . "/ktentity.inc");

require_once(KT_LIB_DIR . "/documentmanagement/DocumentField.inc");
require_once(KT_LIB_DIR . "/documentmanagement/MetaData.inc");

class MDConditionNode extends KTEntity {
    /** boilerplate DB code. */
    /** primary key */
    var $iId = -1; 
    var $iFieldId;
    var $sName;
    var $iParentNode;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iFieldId" => "document_field_id",
        "iLookupId" => "metadata_lookup_id",
        "sName" => "name", // this can be null - if it isn't we are looking at a rooted subtree
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }
    function getFieldId() { return $this->iFieldId; }
    function setFieldId($iFieldId) {  $this->iFieldId = $iFieldId; }
    function getLookupId() { return $this->iLookupId; }
    function setLookupId($iLookupId) { $this->$iLookupId = $iLookupId; }

    function _table () {
        global $default;
        return $default->metadata_condition_table;
    }

    // Static Functions (dull)
    function &get($iId) { return KTEntityUtil::get("MDConditionNode", $iId); }
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray("MDConditionNode", $aOptions); }
    function &getList($sWhereClause = null) { global $default; return KTEntityUtil::getList2("MDConditionNode", $sWhereClause); }

    /** end boilerplate.  anything interesting goes below here. */

}

class MDConditionChain extends KTEntity {
    /** boilerplate DB code. */
    /** primary key */
    var $iId = -1; 
    var $iParentCondition;
    var $iChildCondition;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "iChildCondition" => "child_condition",
        "iParentCondition" => "parent_condition",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }
    function getParentConditionId() { return $this->iParentCondition; }
    function setParentConditionId($iParentCondition) { $this->$iParentCondition = $iParentCondition; }
    function getChildConditionId() { return $this->iChildCondition; }
    function setChildConditionId($iChildCondition) { $this->$iChildCondition = $iParentCondition; }

    function _table () {
        global $default;
        return $default->md_condition_chain_table;
    }

    // Static Functions (dull)
    function &get($iId) { return KTEntityUtil::get("MDConditionChain", $iId); }
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray("MDConditionChain", $aOptions); }
    function &getList($sWhereClause = null) { global $default; return KTEntityUtil::getList2("MDConditionChain", $sWhereClause); }

    /** end boilerplate.  anything interesting goes below here. */

}
