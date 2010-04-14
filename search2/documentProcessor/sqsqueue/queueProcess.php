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
require_once('queueEvent.php');
require_once('dependencyList.php');

abstract class queueProcess {
	public $name;
	public $events;
	public $dependencies;
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function __construct() {
		$this->name = '';
		$this->events = array();
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function setName($name) {
		$this->name = $name;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function addEvent($event) {
		$this->events[] = $event;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function addDependencyList($dependency) {
		$this->dependencies[] = $dependency;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getEvents() {
		return $this->events;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getDependencies() {
		return $this->dependencies;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	abstract function addEvents();
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	abstract function addDependencies();
}
?>