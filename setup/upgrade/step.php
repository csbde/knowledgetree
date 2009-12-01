<?php
/**
* Step Controller.
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

require_once("../wizard/steps/configuration.php"); // configuration to read the ini path

class Step
{
	/**
	* List of variables to be loaded to template
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $temp_variables = array();

	/**
	* List of errors encountered by step
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $error = array();

	/**
	* List of warnings encountered by step
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array
	*/
    protected $warnings = array();

	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $storeInSession = false;

	/**
	* Flag if step needs to be upgraded
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runUpgrade = false;

	/**
	* Step order
	*
	* @author KnowledgeTree Team
	* @access public
	* @var string
	*/
    protected $order = false;

	/**
	* Flag if step needs to run silently
	*
	* @author KnowledgeTree Team
	* @access public
	* @var boolean
	*/
    protected $silent = false;

    public $displayFirst = false;

    private $salt = 'upgrade';

    public function __construct() {
    	$this->util = new UpgradeUtil();
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

    public function displayFirst() {
    	return $this->displayFirst;
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

	/**
	* Returns step errors
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getErrors() {
        return $this->error;
    }

	/**
	* Returns step errors
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getWarnings() {
        return $this->warnings;
    }

	/**
	* Load default step values
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function loadDefaults() {

    }

	/**
	* Return default step values
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getDefaults() {
        return array();
    }

	/**
	* Checks if edit button has been clicked
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function edit() {
        if(isset($_POST['Edit'])) {
            return true;
        }

        return false;
    }

	/**
	* Checks if next button has been clicked
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function next() {
        if(isset($_POST['Next'])) {
            return true;
        }

        return false;
    }

	/**
	* Checks if previous button has been clicked
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    public function previous() {
        if(isset($_POST['Previous'])) {
            return true;
        }

        return false;
    }

	/**
	* Checks if Confirm button has been clicked
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    function confirm() {
        if(isset($_POST['Confirm'])) {
            return true;
        }

        return false;
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
	* Checks if we are currently in this class step
	*
	* @author KnowledgeTree Team
	* @param string
	* @access public
	* @return boolean
	*/
    public function inStep($name) {
    	if(!isset($_GET['step_name'])) return false;
        if($_GET['step_name'] == $name)
            return true;
        return false;
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
	* Safer way to return post data
	*
	* @author KnowledgeTree Team
	* @params SimpleXmlObject $simplexml
	* @access public
	* @return void
	*/
    public function getPostSafe($key) {
    	return isset($_POST[$key]) ? $_POST[$key] : "";
    }

	/**
	* Safer way to return post data
	*
	* @author KnowledgeTree Team
	* @params SimpleXmlObject $simplexml
	* @access public
	* @return void
	*/
    public function getPostBoolean($key) {
    	return isset($_POST[$key]) ? $_POST[$key] : false;
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
     * Return whether or not to store a step information in session
     *
     * @author KnowledgeTree Team
     * @param none
     * @access public
     * @return boolean
     */
    public function storeInSession() {
    	return $this->storeInSession;
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

    public function setPostConfig() {
    	return '';
    }

    /**
     * Return whether or not to a step has to be in silent mode
     *
     * @author KnowledgeTree Team
     * @param none
     * @access public
     * @return boolean
     */
    public function silentMode() {
    	return $this->silent;
    }

	/**
	* Set step errors
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function setErrors($error) {
        $this->error = $error;
    }

	/**
	* Get session data from package
	*
	* @author KnowledgeTree Team
	* @params none
	* @access private
	* @return boolean
	*/
    public function getDataFromPackage($package, $class) {
    	if(empty($_SESSION[$package][$class])) {
    		return false;
    	}

    	return $_SESSION[$package][$class];
    }

    protected function readConfig() {
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

    protected function storeSilent() {

    }
    
	/**
	* Is the installation 
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function isCe() {
    	if($this->util->getVersionType() == "community")
    		return true;
    	return false;
    }
}

?>