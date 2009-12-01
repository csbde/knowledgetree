<?php
/**
* Welcome Step Controller.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
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

class upgradeWelcome extends step {

    protected $silent = true;
    protected $temp_variables = array();
	protected $error = array() ;
	protected $storeInSession = true;

    public function doStep() {
    	$upgradeOnly = false;
    	if(isset($_GET['action'])) {
    		if($_GET['action'] == 'installer') {
    			$upgradeOnly = true;
    		}
    	}
    	$this->temp_variables = array("step_name"=>"welcome", "upgradeOnly"=>$upgradeOnly);
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
    	$upgradeOnly = false;

    	if(isset($_POST['upgradeOnly'])) $upgradeOnly = $_POST['upgradeOnly'];
    	$dconf = $this->getDataFromPackage('installers', 'database'); // Use info from install
    	if($dconf) { // From Install
	    	$this->util->dbUtilities->load($dconf['dhost'], $dconf['dport'], $dconf['duname'], $dconf['dpassword'], $dconf['dname']);
			$sQuery = "SELECT count(*) AS match_count FROM users WHERE username = '$username' AND password = '".md5($password)."'";
			$res = $this->util->dbUtilities->query($sQuery);
			$ass = $this->util->dbUtilities->fetchAssoc($res);
			if($ass[0]['match_count'] == 1)
				return true;
    	} elseif($upgradeOnly) {
    		require_once("../wizard/steps/configuration.php"); // configuration to read the ini path
    		$wizConfigHandler = new configuration();
    		$configPath = $wizConfigHandler->readConfigPathIni();
			$this->util->iniUtilities->load($configPath);
			$dconf = $this->util->iniUtilities->getSection('db');
    		$this->util->dbUtilities->load($dconf['dbHost'],$dconf['dbPort'], $dconf['dbUser'], $dconf['dbPass'], $dconf['dbName']);
			$sQuery = "SELECT count(*) AS match_count FROM users WHERE username = '$username' AND password = '".md5($password)."'";
			$res = $this->util->dbUtilities->query($sQuery);
			$ass = $this->util->dbUtilities->fetchAssoc($res);
			if($ass[0]['match_count'] == 1)
				return true;
    	} else { // Upgrade
    		require_once("../wizard/steps/configuration.php"); // configuration to read the ini path
    		$wizConfigHandler = new configuration();
    		$configPath = $wizConfigHandler->readConfigPathIni();
    		if($configPath) {
				$this->util->iniUtilities->load($configPath);
				$dconf = $this->util->iniUtilities->getSection('db');
	    		$this->util->dbUtilities->load($dconf['dbHost'],$dconf['dbPort'], $dconf['dbUser'], $dconf['dbPass'], $dconf['dbName']);
				$sQuery = "SELECT count(*) AS match_count FROM users WHERE username = '$username' AND password = '".md5($password)."'";
				$res = $this->util->dbUtilities->query($sQuery);
				$ass = $this->util->dbUtilities->fetchAssoc($res);
				if($ass[0]['match_count'] == 1)
					return true;
    		}
    	}
        $this->error[] = 'Could Not Authenticate User';
        return false;

    }

    public function getErrors() {
    	return $this->error;
    }

    /**
	* Returns step variables
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getStepVars()
    {
        return $this->temp_variables;
    }

    public function storeSilent() {

    }
}

?>