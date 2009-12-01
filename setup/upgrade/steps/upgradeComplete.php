<?php
/**
* Complete Step Controller. 
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

class upgradeComplete extends Step {

    protected $silent = true;
    protected $temp_variables = array();
    private $migrateCheck = false;
	private $servicesCheck = 'tick';
	
    public function doStep() {
    	$this->temp_variables = array("step_name"=>"complete", "silent"=>$this->silent);
    	$this->temp_variables['isCE'] = false;
		$type = $this->util->getVersionType();
		if($type == "community")
		 	$this->temp_variables['isCE'] = true;
        $this->doRun();
        $this->storeSilent();
        $this->util->deleteMigrateFile();
    	return 'landing';
    }
    
    private function doRun() {
		if($this->util->isMigration()) {
	        $this->storeSilent();// Set silent mode variables
			require_once("../wizard/steps/services.php"); // configuration to read the ini path
			$wizServices = new services();
			foreach ($wizServices->getServices() as $serviceName) {
				$className = OS.$serviceName;
				require_once("../wizard/lib/services/service.php");
				require_once("../wizard/lib/services/".OS."Service.php");
				require_once("../wizard/lib/services/$className.php");
				$aService = new $className();
				$aService->load(); // Load Defaults
				$aService->start(); // Start Service
				$status = $aService->status(); // Get service status
				if($status) {
					$this->temp_variables[$serviceName."Status"] = 'tick';
				} else {
					$this->temp_variables[$serviceName."Status"] = 'cross_orange';
					$this->servicesCheck = 'cross_orange';
				}
			}
			$this->migrateCheck = true;
		}
		return true;
    }
    
    /**
     * Set all silent mode varibles
     *
     */
    protected function storeSilent() {
    	$v = $this->getDataFromSession('upgradeProperties');
    	$this->temp_variables['sysVersion'] = $this->util->readVersion();
    	$this->temp_variables['migrateCheck'] = $this->migrateCheck;
    	$this->temp_variables['servicesCheck'] = $this->servicesCheck;
    }

}
?>