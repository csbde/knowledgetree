<?php

/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ktdms.com/KPL
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
 * (C) 2006 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
 */

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc"); 

class KTDocumentTransactionType extends KTEntity {
    /** primary key */
    var $iId = -1;
    var $sName;
    var $sNamespace;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sNamespace" => "namespace",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return $this->sName; }
    function getNamespace() { return $this->sNamespace; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setNamespace($sNamespace) { $this->sNamespace = $sNamespace; }

    function _table () {
        return KTUtil::getTableName('transaction_types');
    }

    function &get($iId) { return KTEntityUtil::get('KTDocumentTransactionType', $iId); }
    function &createFromArray($aOptions) { return KTEntityUtil::createFromArray('KTDocumentTransactionType', $aOptions); }
    function &getList($sWhereClause = null) { return KTEntityUtil::getList2('KTDocumentTransactionType', $sWhereClause); }
    function &getByNamespace($sNamespace) { return KTEntityUtil::getBy('KTDocumentTransactionType', 'namespace', $sNamespace); }
}

?>
