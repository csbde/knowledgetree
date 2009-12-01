<?php
/**
* Install Step Controller.
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
* @package Installer
* @version Version 0.1
*/

class install extends step
{

	/**
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $storeInSession = true;

	/**
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runInstall = true;
	private $ce_check = false;

    public function doStep() {
    	$this->temp_variables = array("step_name"=>"install");
    	$this->checkInstallType(); // Set silent mode variables
    	if(!$this->inStep("install")) {
    		return 'landing';
    	}
        if($this->install()) {
            $this->doRun();
            return 'install';
        } else if($this->previous()) {
            return 'previous';
        }

        $this->doRun();
        return 'landing';
    }

    public function getStepVars()
    {
        return $this->temp_variables;
    }

    public function getErrors() {
        return $this->error;
    }

    public function doRun()
    {
    	$value = 'disable';
        if(isset($_POST['Install'])) {
            if(isset($_POST['call_home'])){
                $value = $_POST['call_home'];
            }
            $this->temp_variables['call_home'] = $value;
            return true;
        }
        $this->temp_variables['call_home'] = $value;
    }

    public function installStep()
    {
		$this->callHome();
		if ($this->util->isMigration()) { // copy indexing directory if this is a migration
			$migrateSessionData = $this->getDataFromPackage('migrate', 'installation');
			$configSessionData = $this->getDataFromSession('configuration');
			$src = $migrateSessionData['location'] . DS . 'var' . DS .  'indexes';

			if(WINDOWS_OS){
                $dst = $configSessionData['paths']['varDirectory']['path'] . DS . 'indexes';
			}else{
			    $dst = $configSessionData['paths']['varDirectory']['path'];
			}
			$this->util->copyDirectory($src, $dst);
		}
    }

    /**
     * Check the install type and store
     *
     */
    function checkInstallType() {
    	if ($this->util->isCommunity()) {
    		$this->ce_check = true;
    		$this->registerPlugins(); // Set silent mode variables
    	} else {
    		$this->ce_check = false;
    	}
    	$this->temp_variables['ce_check'] = $this->ce_check;
    }

    function registerPlugins() {

    }

    public function callHome() {
        $conf = $this->getDataFromSession("install"); // retrieve database information from session
        $dbconf = $this->getDataFromSession("database");
        $this->util->dbUtilities->load($dbconf['dhost'], '', $dbconf['duname'], $dbconf['dpassword'], $dbconf['dname']); // initialise the db connection
        $complete = 1;
        if($conf['call_home'] == 'enable'){
            $complete = 0;
        }
        $query = "UPDATE scheduler_tasks SET is_complete = {$complete} WHERE task = 'Call Home'";
        $this->util->dbUtilities->query($query);
        $this->util->dbUtilities->close(); // close the database connection
    }


}
?>