<?php
/**
 * $Id: $
 *
 * The contents of this file are subject to the KnowledgeTree
 * Commercial Editions On-Premise License ("License");
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.knowledgetree.com/about/legal/
 * The terms of this license may change from time to time and the latest
 * license will be published from time to time at the above Internet address.
 *
 * This edition of the KnowledgeTree software
 * is NOT licensed to you under Open Source terms.
 * You may not redistribute this source code.
 * For more information please see the License above.
 *
 * (c) 2008, 2009, 2010 KnowledgeTree Inc.
 * All Rights Reserved.
 *
 */

require_once(realpath(dirname(__FILE__) . '/dependencyList.php'));

class queueEvent {
	// Event name
	public $name;
	// Event message
	public $message;
	// List of event dependencies
	public $list_of_dependencies;
	// Parameters to be passed with event
	public $parameters;
	
    /**
    * Constructor
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $name
    * @param string $message
    * @return
    */
	public function __construct($name = '', $message = '') {
		$this->name = $name;
		$this->message = $message;
		$this->list_of_dependencies = array();
		$this->parameters = array();
	}
	
    /**
    * Set event name
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $name
    * @return
    */
	public function setName($name) {
		$this->name = $name;
	}
	
    /**
    * Set event message
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $message
    * @return
    */
	public function setMessage($message) {
		$this->message = $message;
	}
	
    /**
    * Add event dependency
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $message
    * @return
    */
	public function setDependency($list_of_dependencies) {
		$this->list_of_dependencies = $list_of_dependencies;
	}
	
    /**
    * Get event name
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getName() {
		return $this->name;
	}
	
    /**
    * Get event message 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getMessage() {
		return $this->message;
	}
	
    /**
    * Get list of event dependencies
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getDependencies() {
		return $this->list_of_dependencies;
	}
	
}

?>