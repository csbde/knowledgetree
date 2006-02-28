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
require_once(KT_LIB_DIR . "/help/helpreplacement.inc.php");

class KTHelpEntity extends KTEntity {
    /** primary key */
    var $iId = -1;
    /** help file name */
    var $sSection;
    /** replacement string */
    var $sFilename;

    var $_aFieldToSelect = array(
        "iId" => "id",
        "sSection" => "fSection",
        "sFilename" => "help_info",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getSection() { return $this->sSection; }
    function getFilename() { return $this->sFilename; }
    function setID($iId) { $this->iId = $iId; }
    function setSection($sSection) { $this->sSection = $sSection; }
    function setFilename($sFilename) { $this->sFilename = $sFilename; }

    function _table () {
        global $default;
        return $default->help_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTHelpEntity', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTHelpEntity', $aOptions);
    }

    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->help_table, 'KTHelpEntity', $sWhereClause);
    }

    function checkReplacement() {
        $oHelpReplacement = KTHelpReplacement::getByName($this->sFilename);
        if (PEAR::isError($oHelpReplacement)) {
            return false;
        }
        return $oHelpReplacement;
    }

    function &getByFilename($sFilename) {
        return KTEntityUtil::getBy('KTHelpEntity', 'help_info', $sFilename);
    }
}

?>
