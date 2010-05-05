<?php
/**
* Step Controller.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
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

require_once(realpath(dirname(__FILE__)) . "/../wizard/share/stepBase.php");

/**
*
* @copyright 2008-2010, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Upgrader
* @version Version 0.1
*/

class Step extends StepBase
{
	/**
	* Flag if step needs to be upgraded
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    private $runUpgrade = false;

    public function __construct() {
    	$this->util = new UpgradeUtil();
    	$this->salt = 'upgrade';
    }

	/**
	* Returns step state
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doStep()
    {
        // if not authenticated, return to step 1
        if (!isset($_SESSION['setup_user'])) {
            header('Location: index.php?step=welcome');
            exit;
        }

        return '';
    }


    /**
    * Checks if Upgrade button has been clicked
    *
    * @author KnowledgeTree Team
    * @param none
    * @access public
    * @return boolean
    */
    function upgrade() {
        return isset($_POST['Upgrade']);
    }

    /**
    * Checks if Upgrade button has been clicked
    *
    * @author KnowledgeTree Team
    * @param none
    * @access public
    * @return boolean
    */
    function restore() {
        return isset($_POST['Restore']);
    }

	/**
	* Load session data to post
	*
	* @author KnowledgeTree Team
	* @params none
	* @access private
	* @return boolean
	*/
    public function setDataFromSession($class) {
        if(empty($_SESSION[$this->salt][$class])) {
            return false;
        }
        $_POST = isset($_SESSION[$this->salt]['upgrade'][$class]) ? $_SESSION[$this->salt]['upgrade'][$class]: '';

        return true;
    }

	/**
	* Get session data from post
	*
	* @author KnowledgeTree Team
	* @params none
	* @access private
	* @return boolean
	*/
    public function getDataFromSession($class) {
    	if(empty($_SESSION[$this->salt][$class])) {
    		return false;
    	}

    	return $_SESSION[$this->salt][$class];
    }

	/**
	* Runs step upgrade if required
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function upgradeStep() {
		return '';
    }

    /**
     * Return whether or not to a step has to be upgraded
     *
     * @author KnowledgeTree Team
     * @param none
     * @access public
     * @return boolean
     */
    public function runUpgrade() {
    	return $this->runUpgrade;
    }

    protected function readConfig() {
    	require_once("../wizard/steps/configuration.php"); // TODO
    	$wizConfigHandler = new configuration();
    	$path = $wizConfigHandler->readConfigPathIni();
		$this->util->iniUtilities->load($path);
        $dbSettings = $this->util->iniUtilities->getSection('db');
        $this->dbSettings = array('dbHost'=> $dbSettings['dbHost'],
                                  'dbName'=> $dbSettings['dbName'],
                                  'dbUser'=> $dbSettings['dbUser'],
                                  'dbPass'=> $dbSettings['dbPass'],
                                  'dbPort'=> $dbSettings['dbPort'],
                                  'dbAdminUser'=> $dbSettings['dbAdminUser'],
                                  'dbAdminPass'=> $dbSettings['dbAdminPass'],
        );
        $this->paths = $this->util->iniUtilities->getSection('urls');
        $this->paths = array_merge($this->paths, $this->util->iniUtilities->getSection('cache'));
        $this->sysVersion = $this->readVersion();
        $this->cachePath = $wizConfigHandler->readCachePath();
        $this->proxyPath = $this->cachePath."/.."; // Total guess.
        $this->proxyPath = realpath($this->proxyPath."/proxies");
        $this->storeSilent();
    }

    protected function readVersion() {
    	$verFile = SYSTEM_DIR."docs".DS."VERSION.txt";
    	if(file_exists($verFile)) {
			$foundVersion = file_get_contents($verFile);
			return $foundVersion;
    	} else {
			$this->error[] = "KnowledgeTree installation version not found";
    	}

		return false;
    }   
}

?>