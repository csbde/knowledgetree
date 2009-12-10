<?php
/**
* Services Step Tests.
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
if(isset($_GET['action'])) {
	$func = $_GET['action'];
	if($func != '') {
		require_once(WIZARD_DIR. "step.php");
		require_once(WIZARD_DIR. "installUtil.php");
		require_once(WIZARD_DIR. "path.php");
		require_once(WIZARD_DIR. "dbUtilities.php");
	}
}

class servicesStep {
	/** External Access **/
	public function doDeleteAll() {
    	$serverDetails = $this->getServices();
		foreach ($serverDetails as $serviceName) {
			$className = OS.$serviceName;
			require_once("../lib/services/service.php");
			require_once("../lib/services/".OS."Service.php");
			require_once("../lib/services/$className.php");
			$service = new $className();
			$service->uninstall();
			echo "Delete Service {$service->getName()}<br/>";
			echo "Status of service ".$service->status()."<br/>";
		}
	}
	
	public function doInstallAll() {
    	$serverDetails = $this->getServices();
    	if(!empty($serverDetails)) {
			require_once("../lib/validation/serviceValidation.php");
			require_once("../lib/services/service.php");
    	}
		foreach ($serverDetails as $serviceName) {
			$className = OS.$serviceName;
			$serv = strtolower($serviceName); // Linux Systems.
			require_once("../lib/services/".OS."Service.php");
			require_once("../lib/validation/$serv"."Validation.php");
			require_once("../lib/services/$className.php");
			$service = new $className();
			$class = strtolower($serviceName)."Validation";
			$vClass = new $class();
			$passed = $vClass->binaryChecks(); // Run Binary Pre Checks
			$service->load(array('binary'=>$passed));
			$service->install();
			echo "Install Service {$service->getName()}<br/>";
			echo "Status of service ".$service->status()."<br/>";
		}
	}
	
	public function doStatusAll() {
    	$serverDetails = $this->getServices();
		foreach ($serverDetails as $serviceName) {
			$className = OS.$serviceName;
			require_once("../lib/services/service.php");
			require_once("../lib/services/".OS."Service.php");
			require_once("../lib/services/$className.php");
			$service = new $className();
			$service->load();
			echo "{$service->getName()} : Status of service = ".$service->status()."<br/>";
		}
	}
}

if(isset($_GET['action'])) {
	$func = $_GET['action'];
	if(isset($_GET['debug'])) {
		define('DEBUG', $_GET['debug']);
	} else {
		define('DEBUG', 0);
	}
	if($func != '') {
		$serv = new services();
		$func_call = strtoupper(substr($func,0,1)).substr($func,1);
		$method = "do$func_call";
		$serv->$method();
	}
}
?>