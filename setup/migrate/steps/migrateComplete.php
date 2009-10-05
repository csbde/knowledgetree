<?php
/**
* Complete Step Controller. 
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
* @package Migrater
* @version Version 0.1
*/

class migrateComplete extends Step {

    /**
	* Reference to Database object
	*
	* @author KnowledgeTree Team
	* @access private
	* @var object
	*/	
    private $_dbhandler = null;

    /**
     * List of services to check
     * 
     * @access private
     * @var array
     */
    private $services_check = 'tick';
    private $paths_check = 'tick';
    private $privileges_check = 'tick';
    private $database_check = 'tick';
    protected $silent = true;
    
    protected $util = null;
    
    public function __construct() {
    	$this->temp_variables = array("step_name"=>"complete", "silent"=>$this->silent);
        $this->_dbhandler = new dbUtil();
    	$this->util = new MigrateUtil();
    }

    function doStep() {
        $this->doRun();
    	return 'landing';
    }
    
    function doRun() {
        $this->checkServices();
        $this->storeSilent();// Set silent mode variables
    }
    
    private function checkServices()
    {
        $services = new services();
        foreach ($services->getServices() as $serviceName) {
			$this->temp_variables[$serviceName."Status"] = 'tick';
        }     
		return true;
    }
    
    /**
     * Set all silent mode varibles
     *
     */
    private function storeSilent() {
    	$this->temp_variables['services_check'] = $this->services_check;
    }
}
?>