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
realpath(dirname(__FILE__) . '/queueEvent.php');

abstract class queueProcess {
	/**
	 * Name
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Events
	 *
	 * @var array
	 */
	public $events;
	/**
	 * List of events
	 *
	 * @var array 
	 */
	public $list_of_events;
	/**
	 * Document object
	 *
	 * @var Document object
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
    * Set process name
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
    * Set the list of event names
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function setListOfEvents($list_of_events) {
		$this->list_of_events = $list_of_events;
	}

    /**
    * Set the document to be processed
    *
    * @author KnowledgeTree Team
    * @access public
    * @param object Document 
    * @return none
    */
	public function setDocument($document) {
    	$this->document = $document;
    }
    
    /**
    * Add an event to list of events
    *
    * @author KnowledgeTree Team
    * @access public
    * @param processEvent $event
    * @return none
    */
	public function addEvent($event) {
		$this->events[] = $event;
	}

    /**
    * Load the event class and store in a list of events
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return
    */
	public function addEventsToProcess() {
		foreach ($this->list_of_events as $key => $message) 
		{
			$process_name = $this->getName();
			$event_name = $key . "Event";
			$event_file = $event_name . ".inc.php";
			$event_file_path = realpath(dirname(__FILE__)) ."/events/" . $process_name . "/" . $event_file;
			if (file_exists($event_file_path)) 
			{
				require_once($event_file_path);
				$event_class = new $event_name();
				$event_class->setDocument($this->document);
				$event_class->buildParameters();
				$event_class->loadCallbacks();
				$this->addEvent($event_class);
				$previous_event = $key;
			} else {
				// TODO : Die Gracefully
				
			}
		}
	}
	
    /**
    * Get the process name
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return string
    */
	public function getName() {
		return $this->name;
	}
	
    /**
    * Get process list of events
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return array
    */
	public function getEvents() {
		return $this->events;
	}
	
}
?>