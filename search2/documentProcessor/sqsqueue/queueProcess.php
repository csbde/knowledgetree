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

abstract class queueProcess {
	/**
	 * Name
	 *
	 * @var name
	 */
	public $name;
	/**
	 * Events
	 *
	 * @var events
	 */
	public $events;
	/**
	 * List of events
	 *
	 * @var queueProcess
	 */
	public $list_of_events;
	/**
	 * Document object
	 *
	 * @var document
	 */
	public $document;
		
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
		$this->list_of_events = array();
		$this->document = null;
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
	public function getName() {
		return $this->name;
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
	public function setListOfEvent($list_of_events) {
		$this->list_of_events = $list_of_events;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function getListOfEvent() {
		return $this->list_of_events;
	}
	
    /**
    * 
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function addEventsToProcess() {
		foreach ($this->list_of_events as $key => $message) 
		{
			$event_name = $key . "Event";
			$event_file = $event_name . ".inc.php";
			$event_file_path = realpath(dirname(__FILE__)) . "/events/" . $event_file;
			if (file_exists($event_file_path)) 
			{
				require_once($event_file_path);
				$event_class = new $event_name();
				$event_class->setDocument($this->document);
				$event_class->buildParameters();
				$this->addEvent($event_class);
			}
		}
	}
	
    public function setDocument($document) {
    	$this->document = $document;
    }
}
?>