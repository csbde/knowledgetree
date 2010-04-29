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

// TODO : Restructure ktqueue folder
/**
 * Load KTQueue Complex event
 */
require_once("sqsDispatcher.php");
require_once('ktqueue/common/ComplexEvent.class.php'); // sqs queue complex event


/**
 * Dispatchers complex events to the SQS control queue for processing.
 *
 * @author KnowledgeTree Team
 * @package
 * @version 1.0
 */
class queueDispatcher extends sqsDispatcher
{
	/**
	 * List of process names
	 *
	 * @var array string
	 */
	protected $processNames;
	/**
	 * List of process objects
	 *
	 * @var array queueProcess
	 */
	protected $processes;
	/**
	 * Complex event object
	 *
	 * @var complexEvent
	 */
	protected $complexEvent;
	/**
	 * Callback type
	 *
	 * @var array
	 */
	protected $callback_type = "POST|";
	/**
	 * Callback script
	 *
	 * @var array
	 */
	protected $callback_script = "search2/documentProcessor/sqsqueue/callbackDispatcher.php";
	/**
	 * Traceback script
	 *
	 * @var array
	 */
	protected $traceback_script = "search2/documentProcessor/sqsqueue/tracebackDispatcher.php";
	/**
	 * Callback and traceback options
	 *
	 * @var array
	 */
	protected $callback_options = "msg=[eventMessage]&eid=[eventId]&cid=[complexEventId]&status=[status]";
	
    /**
     * Constructor
     *
     * @author KnowledgeTree Team
     * @access public
     * @param none
     * @return none
     */
    public function __construct()
    {
    	$this->processNames = array();
    	$this->processes = array();
    }

    /**
     * Add a process, which maps to a complex event
     *
     * @author KnowledgeTree Team
     * @access public
     * @param none
     * @return none
     */
    public function addProcess($process, $document) 
    {
    	// Check if process has not been added before
    	if(!in_array($process, $this->processNames)) 
    	{
    		// Store process name
    		$this->processNames[] = $process;
    		// Load process
    		$process_class = $this->getProcess($process);
    		// Set the document in the process
    		$process_class->setDocument($document);
			// Add events to process
			$process_class->addEventsToProcess();
    		// Store process object
    		$this->processes[$process] = $process_class;
    	}
    }
    
    /**
     * Instantiate the process used to contain the complex object mapping
     *
     * @author KnowledgeTree Team
     * @access private
     * @param none
     * @return none
     */
    private function getProcess($process) 
    {
    	$process_class = null;
    	$process_name = $process . "Process";
		$process_file = $process_name . ".inc.php";
		$process_file_path = dirname(__FILE__) .  "/processes/" . $process_file;
		if (file_exists($process_file_path)) 
		{
			require_once($process_file_path);
			// Instantiate process
			$process_class = new $process_name();
		}
		
		return $process_class;
    }
    
    /**
    * Creates a complex event mapping the document processing process.
    *
    * @author KnowledgeTree Team
    * @access public
    * @return ComplexEvent $complexEvent
    */
    public function createComplexEvent() 
    {
    	// Create complex event object
		$this->complexEvent = new ComplexEvent();
		$dependencyList = array();
		// Iterate through processes
    	foreach ($this->processes as $process) 
    	{
    		// Retrieve process events
			$events = $process->getEvents();
			if($events) 
			{
				foreach ($events as $event) 
				{
		    		// Retrieve event name
		    		$name = $event->getName();
		    		// Retrieve event message
		    		$message = $event->getMessage();
		    		// Retrieve event parameters
		    		$params = $event->getParameters();
		    		// Add events to complex event
		    		$this->addEventToComplexEvent($params, $name, $message);
		    		// Retrieve event callbacks
		    		$callbacks = $event->getCallbacks();
					// Check for callbacks
		    		if(count($callbacks) > 0)
		    		{
		    			// Add simple event level callbacks
		    			$this->addCallbacksToEvent($name, $callbacks);
		    		}
		    		// Store event dependencies for later processing
		    		$dependencies = $event->getDependencies();
		    		// Check for dependencies
		    		if(count($dependencies) > 0)
		    		{
		    			// Store dependencies for later process
		    			$dependencyList[$event->getName()] = $dependencies;
		    		}
				}
			}
			// Process event dependencies
			$this->addDependencies($dependencyList);
			// TODO : Map complex event callbacks properly
			$this->addCallbacks($this->getCallbacks());
			//$this->addCallbacks($process);
    	}
    }
    
    /**
    * Add callbacks to the given simple event
    *
    * @author KnowledgeTree Team
    * @access private
    * @param array $params
    * @param string $name
    * @param array $message
    * @return none
    */
    function addCallbacksToEvent($name, $callbacks) {
    	// Check if simple event exists in complex event
    	if (isset($this->complexEvent->events[$name])) {
    		foreach ($callbacks as $eventCallback) {
    			$params = array('url' => $eventCallback->getUrl());
    			$event = new Event('HttpEventRequest.run', $params);
    			$this->complexEvent->addEvent($eventCallback->getName(), $event);
    			$this->complexEvent->setDependency($eventCallback->getName(), $eventCallback->getDependencies());
    		}
    	}
    }
    
    /**
    * Add an event to the given complex event
    *
    * @author KnowledgeTree Team
    * @access private
    * @param array $params
    * @param string $name
    * @param array $message
    * @return none
    */
    private function addEventToComplexEvent($params, $name, $message) 
    {
    	$event = new Event($message, $params);

    	return $this->complexEvent->addEvent($name, $event);
    }
    
    /**
    * Add a list of dependencies to complex event
    *
    * @author KnowledgeTree Team
    * @access private
    * @param $dependencyList
    * @return none
    */
	private function addDependencies($dependencyList) 
	{
    	foreach ($dependencyList as $event=>$dependencies) 
    	{
    		$this->addDependencyToComplexEvent($event, $dependencies);
    	}
	}

    /**
    * Add a dependency between events.
    *
    * @author KnowledgeTree Team
    * @access private
    * @param string $name
    * @param array $dependencies
    * @return 
    */
    private function addDependencyToComplexEvent($name, $dependencies)
    {
    	$this->complexEvent->setDependency($name, $dependencies);
    }
    
    /**
    * Load list of callbacks to complex event
    * These are for demo purposes only.
    * Are far as I know the complex event callbacks are used for testing only.
    *
    * @author KnowledgeTree Team
    * @access private
    * @param none
    * @return none
    */
	private function getCallbacks() {
		//return array();
		global $default;
		$server = 'http://' . $default->serverName . ':'  . $default->server_port . '' . $default->rootUrl . '/';
		$server = $this->callback_type . $server . $this->callback_script . '?' . $this->callback_options;
		$done = $server;
		$onQueueNextEvent = $server;
		$onReturnEvent = $server;
		$onReturnEventFailure = $server;
		$onReturnEventSuccess = $server;
		$callbacks = array(
								'done' => $done,
								'onQueueNextEvent' => $onQueueNextEvent,
								'onReturnEvent' => $onReturnEvent,
								'onReturnEventFailure' => $onReturnEventFailure,
								'onReturnEventSuccess' => $onReturnEventSuccess,
		);
		
		return $callbacks;
	}
	
    /**
    * Add process callbacks to complex event
    *
    * @author KnowledgeTree Team
    * @access private
    * @param $process queueProcess
    * @return none
    */
	private function addCallbacks($process) 
	{
		if ($process instanceof queueProcess ) {
			// Retrieve process callbacks
			$callbacks = $process->getCallbacks();
		} else {
			$callbacks = $process;
		}

		if($callbacks) 
		{
			foreach ($callbacks as $callback=>$url) 
			{
				// Add callback to Complex Event
				$this->complexEvent->callbacks[$callback] = $url;
			}
		}
	}
	
    /**
    * Instantiates the sqs queue manager and sends a complex event to the sqs queue
    *
    * @author KnowledgeTree Team
    * @access public
    * @param $send
    * @return none
    */
    public function sendToQueue($send = true)
    {
    	// Load sqs queue controller class
    	require_once('SqsQueueController.inc.php'); // sqs queue manager
    	// Create the complex event
    	$this->createComplexEvent();
		if($send)
		{
	    	// Instantiate SQS Queue Manager
			$queueManager = new SqsQueueController('controlQueue');
			// Send To SQS Queue Manager
			$complexEvent = $this->complexEvent;
			$sComplexEvent = serialize($complexEvent);
			$oComplexEvent = unserialize($sComplexEvent);
			if ($oComplexEvent instanceof ComplexEvent )
			{
	    		$response = $queueManager->sendToQueue($complexEvent);
	    		if($response === false) {
	    			// TODO : Not placed on queue. Resend event.
	    		}
			} else {
				// TODO : Malformed complex. Resend event.
			}
		}
    }

    /**
    * Used for testing purposes to create and does not send complex object to the queue.
    *
    * @author KnowledgeTree Team
    * @access public
    * @return none
    */
    public function testing($document_id)
    {
    	$document = Document::get($document_id);
    	// Create processes
    	$this->addProcess('processing', $document);
    	$this->addProcess('indexing', $document);
		$this->sendToQueue(false);
//		print_r($this);
		print_r($this->complexEvent);
    }
    

    
}


?>