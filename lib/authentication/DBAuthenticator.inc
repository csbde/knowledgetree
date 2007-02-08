<?php
require_once(KT_LIB_DIR . "/authentication/Authenticator.inc");
/**
 * $Id$
 *
 * Perform authentication tasks against the database.
 *
 * The contents of this file are subject to the KnowledgeTree Public
 * License Version 1.1 ("License"); You may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.knowledgetree.com/KPL
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
 * for the specific language governing rights and limitations under the
 * License.
 * 
 * The Original Code is: KnowledgeTree Open Source
 * 
 * The Initial Developer of the Original Code is The Jam Warehouse Software
 * (Pty) Ltd, trading as KnowledgeTree.
 * Portions created by The Jam Warehouse Software (Pty) Ltd are Copyright
 * (C) 2007 The Jam Warehouse Software (Pty) Ltd;
 * All Rights Reserved.
 *
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

        $sTable = KTUtil::getTableName('users'); 
        $sQuery = "SELECT ";/*ok*/
        $sQuery .= implode(', ', $aAttributes);
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
        $sTable = KTUtil::getTableName('users');
        $sQuery = "SELECT "; /*ok*/
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
