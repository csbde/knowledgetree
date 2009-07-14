<?php
/**
* Step .
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
class Step
{
	/**
	* List of variables needed by step
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
	* Flag to store class information in session
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $storeInSession = false;
    
	/**
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access public
	* @var array
	*/
    protected $runInstall = false;
    
	/**
	* Step order
	*
	* @author KnowledgeTree Team
	* @access public
	* @var string
	*/
    protected $order = false;
    
	/**
	* Returns step state
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function doStep()
    {
        return '';
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
	* Load default step values
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function loadDefaults() {
        return '';
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
	* Checks if Confirm button has been clicked
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return boolean
	*/
    function install() {
        if(isset($_POST['Install'])) {
            return true;
        }

        return false;
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
        if(empty($_SESSION[$class])) {
            return false;
        }
        $_POST = $_SESSION[$class];
		
        return true;
    }
    
    public function getDataFromSession($class) {
    	if(empty($_SESSION[$class])) {
    		return false;
    	}
    	
    	return $_SESSION[$class];
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
	* Runs step install if required
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function installStep() {
		return '';
    }
    
    public function storeInSession() {
    	return $this->storeInSession;
    }
    
    public function runInstall() {
    	return $this->runInstall;
    }
    
    public function setDBConfig() {
    	return '';
    }
}

?>