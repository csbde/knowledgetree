<?php
/**
 * $Id$
 *
 * Perform authentication tasks against the database.
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
 */

require_once(KT_LIB_DIR . "/authentication/Authenticator.inc");

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
        $sQuery = 'SELECT ';/*ok*/
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
        $sQuery = 'SELECT '; /*ok*/
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
