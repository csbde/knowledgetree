<?php
/**
 * $Id$
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1.2 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and
 * limitations under the License.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *    (i) the "Powered by KnowledgeTree" logo and
 *    (ii) the KnowledgeTree copyright notice
 * in the same form as they appear in the distribution.  See the License for
 * requirements.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 * Contributor( s): ______________________________________
 *
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
