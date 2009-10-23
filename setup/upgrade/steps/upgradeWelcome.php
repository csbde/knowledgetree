<?php
/**
* Welcome Step Controller. 
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software(Pty) Limited
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Upgrader
* @version Version 0.1
*/

require_once('../../config/dmsDefaults.php');
require_once KT_LIB_DIR . '/authentication/authenticationutil.inc.php';

class upgradeWelcome extends step {

    protected $silent = false;
    protected $temp_variables = array();

    public function doStep() {
    	$this->temp_variables = array("step_name"=>"welcome");
        if($this->next()) {
            if ($this->doRun()) {
                return 'next';
            }
            else {
                return 'error';
            }
        }

        return 'landing';
    }
    
    private function doRun() {
        // attempt login
        $username = $_REQUEST['username'];
        $password = $_REQUEST['password'];

        $authenticated = $this->checkPassword($username, $password);
    
        if (!$authenticated)
        {
            session_unset();
            return false;
        }

        $_SESSION['setup_user'] = $username;
        
        return true;
    }
    
    private function checkPassword($username, $password) {
//        global $default;
    
        $sTable = KTUtil::getTableName('users');
        $sQuery = "SELECT count(*) AS match_count FROM $sTable WHERE username = ? AND password = ?";
        $aParams = array($username, md5($password));
        $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'match_count');
        if (PEAR::isError($res)) { return false; }
        else {
            $sTable = KTUtil::getTableName('users_groups_link');
            $sQuery = "SELECT count(*) AS match_count FROM $sTable WHERE user_id = ? AND group_id = 1";
            $aParams = array($res);
            $res = DBUtil::getOneResultKey(array($sQuery, $aParams), 'match_count');
            if (PEAR::isError($res)) { return false; }
            else {
                return ($res == 1);
            }
        }
    }

}

?>