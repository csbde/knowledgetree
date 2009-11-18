<?php
/**
 * $Id$
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
 *
 */

require_once(KT_LIB_DIR . "/ktentity.inc");
require_once(KT_LIB_DIR . "/util/ktutil.inc"); 
require_once(KT_LIB_DIR . "/util/sanitize.inc");

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
    function getName() { return sanitizeForSQLtoHTML($this->sName); }
    function getNamespace() { return $this->sNamespace; }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = sanitizeForSQL($sName); }
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
