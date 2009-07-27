<?php
/**
* Services Step Controller.
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
* @package Installer
* @version Version 0.1
*/


//require_once('../../thirdparty/xmlrpc-2.2/xmlrpc.inc');

class services extends Step 
{
	/**
	* List of errors encountered
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    public $error = array();
    
	/**
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runInstall = true;
    
    
	/**
	* Constructs database object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
    public function __construct() {
    	
    }
    
	/**
	* Main control of services setup
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return string
	*/
    public function doStep()
    {
        // Check dependencies
        $passed = $this->doRun();
        if($this->next()) {
            if($passed)
                return 'next';
            else
                return 'error';
        } else if($this->previous()) {

            return 'previous';
        }

        return 'landing';
    }
    
    private function doRun() {
    	$util = new InstallUtil();
//    	$response = $util->pexec("java"); // Java Runtime Check
    	$response = $util->pexec("java -version"); // Java Runtime Check
    	if(empty($response['out'])) {
    		$this->error[] = "Java runtime environment required";
//    		return false;
    	}
		$this->installStep();
		return true;
    }
    
    
    /**
	* Runs step install if required
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function installStep() {
		$util = new InstallUtil();
		if(WINDOWS_OS) { // Add service to tasks list if needed
			$lucene = new windowsLucene();
		// Start service
		} else { // Unix based systems
			$lucene = new unixLucene();
			$lucene->load();
		}


		
		
    }

  
		
	/**
	* Returns database errors
	*
	* @author KnowledgeTree Team
	* @access public
	* @params none
	* @return array
	*/
    public function getErrors() {
        return $this->error;
    }
}
?>