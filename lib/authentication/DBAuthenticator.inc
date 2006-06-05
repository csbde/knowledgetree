<?php
require_once(KT_LIB_DIR . "/authentication/Authenticator.inc");
/**
 * $Id$
 *
 * Perform authentication tasks against the database.
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
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
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package lib.authentication 
 */
class DBAuthenticator extends Authenticator {

    /**
     * Checks the user's password against the database
     *
     * @param string the name of the user to check
     * @param string the password to check
     * @return boolean true if the password is correct, else false
     */
    function checkPassword($userName, $password) {
        global $default;

        $sql = $default->db;
        $sTable = KTUtil::getTableName('users');
        $sQuery = "SELECT count(*) AS match_count FROM $sTable WHERE username = ? AND password = ?";
        $aParams = array($userName, md5($password));
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'match_count');
        if (PEAR::isError($res)) { return false; }
        else {
            return ($res == 1);
        }
    }

    /**
     * Searches the directory for a specific user
     *
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */ 
    function getUser($sUserName, $aAttributes) {
        global $default;

        $sql = $default->db;
        $sTable = KTUtil::getTableName('users'); 
        $sQuery = "SELECT ";/*ok*/
        // build select
        for ($i=0; $i<count($aAttributes); $i++) {
            $sQuery .= $aAttributes[$i] . (( ($i+1) == count($aAttributes) ) ? "" : ", ");
        }
        $sQuery .= " FROM $sTable WHERE username = ?";
        $aParams = array($sUserName);
        $res = DBUtil::getResultArray(array($sQuery, $aParams));
        if (PEAR::isError($res)) { 
            return false; 
        }
        
        $aUserResults = array();        
        foreach ($res as $aRow) {
            foreach ($aAttributes as $sAttrName) {
                $aUserResults[$sUserName][$sAttrName] = $aRow[$sAttrName];
            }
        } 
        return $aUserResults;
    }
    
    /**
     * Searches the user store for users matching the supplied search string.
     * 
     * @param string the username to search for
     * @param array the attributes to return from the search
     * @return array containing the users found
     */
    function searchUsers($sUserNameSearch, $aAttributes) {
        global $default;

        $sTable = KTUtil::getTableName('users')
        $sQuery = "SELECT ";/*ok*/
        $sQuery .= implode(', ', $aAttributes); 
        $sQuery .= " FROM $sTable where username like '%" . DBUtil::escapeSimple($sUserNameSearch) . "%'";

        $res = DBUtil::getResultArray(array($sQuery, array()));
        if (PEAR::isError($res)) {
            return false; // return $res;
        }
        
        $aUserResults = array();
        foreach ($res as $aRow) {    
            $sUserName = $aRow['username'];
            foreach ($aAttributes as $sAttrName) {
                $aUserResults[$sUserName][$sAttrName] = $aRow[$sAttrName];
            }
        }
        return $aUserResults;
        
    }
}
?>
