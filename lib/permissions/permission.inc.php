<?php

/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

require_once(KT_LIB_DIR . '/ktentity.inc');
require_once(KT_LIB_DIR . '/util/sanitize.inc');

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
        'iId' => 'id',
        'sName' => 'name',
        'sHumanName' => 'human_name',
        'bBuiltIn' => 'built_in',
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function getName() { return sanitizeForSQLtoHTML($this->sName); }
    function getHumanName() { return sanitizeForSQLtoHTML($this->sHumanName); }
    function getBuiltIn() { return $this->bBuiltIn; }
    function setID($id) { $this->iId = $id; }
    function setName($name) { $this->sName = sanitizeForSQL($name); }
    function setHumanName($humanName) { $this->sHumanName = sanitizeForSQL($humanName); }
    function setBuiltIn($builtIn) { $this->sBuiltIn = $builtIn; }

    function _table () {
        global $default;
        return $default->permissions_table;
    }

    // STATIC
    function &get($id) { return KTEntityUtil::get('KTPermission', $id); }
    function &createFromArray($options) { return KTEntityUtil::createFromArray('KTPermission', $options); }
    function &getList($whereClause = null) { return KTEntityUtil::getList2('KTPermission', $whereClause); }

    // STATIC
    function &getDocumentRelevantList($whereClause = null) {
        $list =& KTEntityUtil::getList2('KTPermission', $whereClause);
        if (PEAR::isError($list)) { return $list; }

        $nonRelevant = array(
            'ktcore.permissions.addFolder' => true,
            'ktcore.permissions.folder_details' => true,
        );

        $secondaryList = array();
        foreach ($list as $permission) {
            if ($nonRelevant[$permission->getName()]) {
                continue;
            }
            $secondaryList[] = $permission;
        }

        return $secondaryList;
    }

    // STATIC
    function &getByName($name) {
        $options = array('cache' => 'getByName');
        return KTEntityUtil::getBy('KTPermission', 'name', $name, $options);
    }

}

?>
