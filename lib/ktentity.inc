<?php

/**
 * $Id$
 *
 * Base class for database-backed objects
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @version $Revision$
 * @author Neil Blakey-Milner, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib
 */

class KTEntity {
    /** object primary key */
    var $iId;

    /**
     * Create the current object in the database
     *
     * @return boolean on successful store, false otherwise and set $_SESSION["errorMessage"]
     *
     */
    function create() {
        if ($this->iId < 0) {
            $id = DBUtil::autoInsert($this->_table(), $this->_fieldValues());
            if (PEAR::isError($id)) {
                $_SESSION["errorMessage"] = $id->toString();
                return false;
            }
            $this->iId = $id;
            return true;
        }
        $_SESSION["errorMessage"] = "Can't create an object that already exists id = " . $this->iId . ' table = ' . $this->_table();
        return false;
    }

    /**
     * Update the values in the database table with the object's current values
     *
     * @return boolean true on successful update, false otherwise and set $_SESSION["errorMessage"]
     *
     */
    function update() {
        if ($this->iId > 0) {
            $res = DBUtil::autoUpdate($this->_table(), $this->_fieldValues(), $this->iId);
            if (PEAR::isError($res)) {
                $_SESSION['errorMessage'] = $res->toString();
            }
            return true;
        }
        $_SESSION["errorMessage"] = "Can't update an object that isn't in the database";
        return false;
    }

    /**
    * Delete the current object from the database
    *
    * @return boolean true on successful deletion, false otherwise and set $_SESSION["errorMessage"]
    *
    */
    function delete() {
        if ($this->iId >= 0) {
            $res = DBUtil::autoDelete($this->_table(), $this->iId);
            if (PEAR::isError($res)) {
                $_SESSION['errorMessage'] = $res->toString();
                return false;
            }
            return true;
        }
        $_SESSION["errorMessage"] = "Can't delete an object that isn't in the database";;
        return false;
    }
}

class KTEntityUtil {
    function &getList($sTable, $sClassName, $sWhereClause = null) {
        $sQuery = "SELECT id FROM " . $sTable;/*ok*/
        $aParams = array();
        if (!is_null($sWhereClause)) {
            if (is_string($sWhereClause)) {
                $sQuery .= ' ' . $sWhereClause;
            } else if (is_array($sWhereClause)) {
                $sQuery .= ' ' . $sWhereClause[0];
                $aParams = $sWhereClause[1];
            } else {
                return new PEAR_Error('Weird WhereClause passed');
            }
        }
        $aIDs = DBUtil::getResultArrayKey(array($sQuery, $aParams), 'id');
        $aRet = array();
        foreach ($aIDs as $iID) {
            $aRet[] =& call_user_func(array($sClassName, 'get'), $iID);
        }
        return $aRet;
    }
}

?>
