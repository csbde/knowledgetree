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
    function getName() { return $this->sName; }
    function getDescription() { return $this->sDescription; }
    function getTitle() { return $this->sTitle; }
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
