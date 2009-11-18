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
require_once(KT_LIB_DIR . "/util/sanitize.inc");

class KTHelpReplacement extends KTEntity {
    /** primary key */
    var $iId = -1;
    /** help file name */
    var $sName;
    /** replacement string */
    var $sDescription;
    var $sTitle;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sName" => "name",
        "sDescription" => "description",
        "sTitle" => 'title',
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return sanitizeForSQLtoHTML($this->sName); }
    function getDescription() { return str_replace('#APP_NAME#', APP_NAME, $this->sDescription); }
    function getTitle() { return str_replace('#APP_NAME#', APP_NAME, $this->sTitle); }
    function setID($iId) { $this->iId = $iId; }
    function setName($sName) { $this->sName = $sName; }
    function setDescription($sDescription) { $this->sDescription = $sDescription; }
    function setTitle($sTitle) { $this->sTitle= $sTitle; }

    function _table () {
        global $default;
        return $default->help_replacement_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTHelpReplacement', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTHelpReplacement', $aOptions);
    }

    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->help_replacement_table, 'KTHelpReplacement', $sWhereClause);
    }

    function &getByName($sName) {
        return KTEntityUtil::getBy('KTHelpReplacement', 'name', $sName);
    }
}

?>
