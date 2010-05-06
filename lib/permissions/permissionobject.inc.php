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

require_once(KT_LIB_DIR . "/ktentity.inc");

class KTPermissionObject extends KTEntity {
    /** primary key */
    var $iId = -1;

    var $_aFieldToSelect = array(
        "iId" => "id",
    );

    var $_bUsePearError = true;

    function getID() { return $this->iId; }
    function setID($iId) { $this->iId = $iId; }

    function _table () {
        global $default;
        return $default->permission_objects_table;
    }

    // STATIC
    function &get($iId) {
        return KTEntityUtil::get('KTPermissionObject', $iId);
    }

    // STATIC
    function &createFromArray($aOptions) {
        return KTEntityUtil::createFromArray('KTPermissionObject', $aOptions);
    }

    // STATIC
    function &getList($sWhereClause = null) {
        global $default;
        return KTEntityUtil::getList($default->permission_objects_table, 'KTPermissionObject', $sWhereClause);
    }
    

    /**
     * Overriding the create to specify the id field value
     * Create the current object in the database.
     * 
     * @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
     *
     */
    function create() {
        if ($this->iId <= 0) {
            $fieldValues = $this->_fieldValues();
            
            if (empty($fieldValues)) {
                // NOTE was 'id' => 'id' which, while it should ALWAYS generate an error (field is integer, not string)
                //      somehow manages to only happen on some systems at some times...
                // FIX: modified to 'id' => null which results in the autoincrement kicking in
                $fieldValues = array('id' => null);
            }
            
            $id = DBUtil::autoInsert($this->_table(), $fieldValues);
            if (PEAR::isError($id)) {
                if ($this->_bUsePearError === false) {
                    $_SESSION["errorMessage"] = $id->toString();
                    return false;
                } else {
                    return $id;
                }
            }
            $this->clearCachedGroups();
            $this->iId = $id;
            return true;
        }
        $_SESSION["errorMessage"] = "Can't create an object that already exists id = " . $this->iId . ' table = ' . $this->_table();
        return false;
    }    
    
}

?>
