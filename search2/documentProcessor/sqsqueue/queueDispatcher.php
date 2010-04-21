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
 * Load KTQueue Complex
 */
require_once('ktqueue/common/ComplexEvent.class.php'); // sqs queue complex event

/**
 * Dispatchers complex events to the SQS control queue for processing.
 *
 * @author KnowledgeTree Team
 * @package
 * @version 1.0
 */
class queueDispatcher
{
	/**
	 * Process name store
	 *
	 * @var array
	 */
	public $processNames;
	/**
	 * Process object store
	 *
	 * @var queueProcess
	 */
	public $processes;
	/**
	 * Complex event store
	 *
	 * @var queueProcess
	 */
	public $complexEvent;

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
    		// Load process events
    		$process_class->loadEvents();
    		// Load process callbacks
    		$process_class->loadCallbacks();
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
		    		// Store event dependencies for later processing
		    		$dependencies = $event->getDependencies();
		    		// Check for dependencies
		    		if(count($dependencies))
		    			$dependencyList[$event->getName()] = $dependencies;
				}
			}
			// Process event dependencies
			$this->addDependencies($dependencyList);
			$this->addCallbacks($process);
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
    * Add process callbacks to complex event
    *
    * @author KnowledgeTree Team
    * @access private
    * @param $process queueProcess
    * @return none
    */
	private function addCallbacks($process) 
	{
		// Retrieve process callbacks
		$callbacks = $process->getCallbacks();
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
    * Add process tracebacks to complex event
    *
    * @author KnowledgeTree Team
    * @access private
    * @param $event queueEvent
    * @return none
    */
	private function addTracebacks($event)
	{
		
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
    	// Instantiate SQS Queue Manager
		$queueManager = new SqsQueueController('controlQueue');
		if($send)
		{
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
    * Used for testing purposes to create and send complex object to the queue. (Testing)
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
    
    /**
    * Check if a user is logged in. (Testing)
    *
    * @author KnowledgeTree Team
    * @access public
    * @return boolean
    */
    private function isLoggedIn() {
    	$session = new Session();
    	$sessionStatus = $session->verify();
    	if ($sessionStatus !== true) {
    		return false;
    	}
    	return true;
    }
    
}

// (Testing)
if(isset($_GET['sqsmethod'])) {
	require_once(dirname(__FILE__) . '/../../../config/dmsDefaults.php');
	$oQueueDispatcher = new queueDispatcher();
	if (!$oQueueDispatcher->isLoggedIn()) {
    	echo _kt('Session has expired. Refresh page and login.');
    	exit();
	}
	if(!isset($_GET['sqsmethod'])) {
    	echo _kt('No sqsmethod specified.');
    	exit();
	}
	$method = $_GET['sqsmethod'];
	unset($_GET['sqsmethod']);
	call_user_func_array(array($oQueueDispatcher, $method), $_GET);
	exit();
}
?>