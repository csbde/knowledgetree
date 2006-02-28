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
