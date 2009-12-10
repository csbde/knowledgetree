<?php
/**
* Session Controller.
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
class session
{
	private $salt = 'upgrade';
	/**
	* Constructs session object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param none
 	*/
	public function __construct() {
		$this->startSession();
	}
	
	/**
	* Starts a session if one does not exist
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
	public function startSession() {
		if(!isset($_SESSION[$this->salt]['ready'])) {
			@session_start();
			$_SESSION[$this->salt] ['ready'] = TRUE;
		}
	}

	/**
	* Sets a value key pair in session
	*
	* @author KnowledgeTree Team
	* @param string $fld
	* @param string $val
	* @access public
	* @return void
	*/
	public function set($fld, $val) {
		$this->startSession();
		$_SESSION[$this->salt] [$fld] = $val;
	}
	
	/**
	* Sets a value key pair in a class in session
	*
	* @author KnowledgeTree Team
	* @param string $class
	* @param string $fld
	* @param string $val
	* @access public
	* @return void
	*/
	public function setClass($class , $k, $v) {
		$this->startSession();
		$classArray = $this->get($class);
		if(isset($classArray[$k])) {
			$classArray[$k] = $v;
		} else {
			$classArray[$k] = $v;
		}
		$_SESSION[$this->salt] [ $class] = $classArray;
	}
	
	/**
	* Sets a error value key pair in a class in session
	*
	* @author KnowledgeTree Team
	* @param string $class
	* @param string $fld
	* @param string $val
	* @access public
	* @return void
	*/
	public function setClassError($class, $k, $v) {
		$this->startSession();
		$classArray = $this->get($class);
		if(isset($classArray[$k])) {
			$classArray[$k] = $v;
		} else {
			$classArray[$k] = $v;
		}
		$_SESSION[$this->salt] [ $class] = $classArray;
	}
	
	/**
	* Clear error values in a class session
	*
	* @author KnowledgeTree Team
	* @param string $class
	* @param string $fld
	* @param string $val
	* @access public
	* @return void
	*/
	public function clearErrors($class) {
		$classArray = $this->get($class);
		unset($classArray['errors']);
		$_SESSION[$this->salt] [ $class] = $classArray;
	}
	
	/**
	* Unset a value in session
	*
	* @author KnowledgeTree Team
	* @param string $fld
	* @access public
	* @return void
	*/
	public function un_set($fld) {
		$this->startSession();
		unset($_SESSION[$this->salt] [$fld]);
	}
	
	/**
	* Unset a class value in session
	*
	* @author KnowledgeTree Team
	* @param string $class
	* @access public
	* @return void
	*/
	public function un_setClass($class) {
		$this->startSession();
		if(isset($_SESSION[$this->salt] [$class]))
			unset($_SESSION[$this->salt] [$class]);
	}
	
	/**
	* Destroy the session
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
	public function destroy() {
		$this->startSession();
		unset($_SESSION[$this->salt]);
		session_destroy();
	}
	
	/**
	* Get a session value
	*
	* @author KnowledgeTree Team
	* @param string $fld
	* @access public
	* @return string
	*/
	public function get($fld) {
		$this->startSession();
		if(isset($_SESSION[$this->salt] [$fld]))
			return $_SESSION[$this->salt] [$fld];
		return false;
	}
	
	/**
	* Check if a field exists in session
	*
	* @author KnowledgeTree Team
	* @param string $fld
	* @access public
	* @return string
	*/
	public function is_set($fld) {
		$this->startSession();
		return isset($_SESSION[$this->salt] [$fld]);
	}
	
	/**
	* Return a class from session
	*
	* @author KnowledgeTree Team
	* @param string $fld
	* @access public
	* @return string
	*/
	public function getClass($class) {
		return $_SESSION[$this->salt][$class];
	}
}
?>