<?php
/**
 * Exception class which can be thrown by
 * the WSHelper class.
 *@author KnowledgeTree Team
 *@package Webservice
 *@version Version 0.9
 */
class WSException extends Exception {
 	/**
 	 * @param string The error message
 	 * @return void
 	 */
	public function __construct($msg) {
		$this->msg = $msg;
	}
 	/**
 	 * @return void
 	 */
 	public function Display() {
		echo $this->msg;
	}
}
?>