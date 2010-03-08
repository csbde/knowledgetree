<?php
/**
* Step .
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

/**
*
* @copyright 2008-2010, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Installer
* @version Version 0.1
*/
class Step extends StepBase
{
	/**
	* Flag if step needs to be installed
	*
	* @author KnowledgeTree Team
	* @access private
	* @var booelean
	*/
    public $runInstall = false;
    
    public function __construct() {
    	$this->util = new InstallUtil();
    	$this->salt = 'installers';
    }
    
    /**
     * Return whether or not to a step has to be installed
     * 
     * @author KnowledgeTree Team
     * @param none
     * @access public
     * @return boolean
     */
    public function runInstall() {
    	return $this->runInstall;
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
        $_POST = $_SESSION[$this->salt][$class];
		
        return true;
    }
    
	/**
	* Get session data from class
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
}

?>