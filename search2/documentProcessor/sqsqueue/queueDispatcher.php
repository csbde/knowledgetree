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

require_once('ktqueue/config/config.inc.php'); // sqs queue configuration
require_once('ktqueue/common/SqsQueueManager.inc.php'); // sqs queue manager

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
     * Constructor
     *
     * @author KnowledgeTree Team
     * @access public
     * @param none
     * @return none
     */
    public function __construct()
    {
    }

    /**
    * Execute all document processes
    *
    * @author KnowledgeTree Team
    * @access public
    * @param array $params
    * @return none
    */
    public function runProcesses($params) {
    	$processes = self::getListOfProcesses();
    	foreach ($processes as $process) {
			self::run($process, $params);
    	}
    }

    /**
    * Get the list if enabled processors
    *
    * @author KnowledgeTree Team
    * @access public
    * @param none
    * @return array
    */
    private function getListOfProcesses() {
    	return array('indexing', 'processing'); // documentProcessing documentIndexing
    }
    
    /**
    * Executes a specified document process
    *
    * @author KnowledgeTree Team
    * @access public
    * @param string $process_name
    * @param array $params
    * @return none
    */
    public function run($process, $params) {
    	$process_name = $process . "Process";
		$process_file = $process_name . ".inc.php";
		$process_file_path = dirname(__FILE__) .  "/processes/" . $process_file;
		if (file_exists($process_file_path)) {
			require_once($process_file_path);
			$process_class = new $process_name();
			$process_class->init();
			$process_class->addEvents();
			$process_class->addDependencies();
			$complexEvent = self::buildComplexEvent($process_class, $params);
			self::sendToQueue($complexEvent);
		}
    }
    
    /**
    * Creates a complex event mapping the document processing process.
    *
    * @author KnowledgeTree Team
    * @access public
    * @param queueProcess $process
    * @param array $params
    * @return ComplexEvent $complexEvent
    */
    public function buildComplexEvent($process, $params) {
    	// Create complex event object
		$complexEvent = new ComplexEvent();
		self::addEvents($complexEvent, $params, $process);
		self::addDependencies($complexEvent, $process->getDependencies());
		return $complexEvent;
    }
    
    /**
    * Add events to a given complex event object
    *
    * @author KnowledgeTree Team
    * @access private
    * @param ComplexEvent $complexEvent
    * @param array $params
	* @param queueProcess $process
    */
    private function addEvents($complexEvent, $params, $process) 
    {
    	foreach ($process->getEvents() as $event) 
    	{
    		// Retrieve event name
    		$name = $event->getName();
    		// Retrieve event message
    		$message = $event->getMessage();
    		// Add events to complex event
    		self::addEvent($complexEvent, $params, $name, $message); 
    	}
    }

    /**
    * Add an event to the given complex event
    *
    * @author KnowledgeTree Team
    * @access public
    * @param ComplexEvent $complexEvent
    * @param array $params
    * @param string $name
    * @param array $message
    * @return none
    */
    private function addEvent($complexEvent, $params, $name, $message) {
    	return $complexEvent->addEvent($name, new Event($message, $params));
    }
    
    /**
    * Set dependencies between events within a complex event.
    *
    * @author KnowledgeTree Team
    * @access public
    * @param ComplexEvent $complexEvent
    * @param array $dependencies
    * @return none
    */
    private function addDependencies($complexEvent, $dependencies) {
    	foreach ($dependencies as $dependency) {
    		self::addDependency($complexEvent, $dependency->getName(), $dependency->getDependencList());
    	}
    }
    
    /**
    * Set a dependency between events.
    *
    * @author KnowledgeTree Team
    * @access public
    * @param ComplexEvent $complexEvent
    * @param string $name
    * @param array $dependencies
    * @return 
    */
    private function addDependency($complexEvent, $name, $dependencies) {
    	$complexEvent->setDependency($name, $dependencies); // Add simple event dependencies
    }

    /**
    * Instantiates the sqs queue manager and sends a complex event to the sqs queue
    *
    * @author KnowledgeTree Team
    * @access private
    * @param ComplexEvent $complexEvent
    * @return none
    */
    private function sendToQueue($complexEvent) {
		$queues = SqsQueueManager::getQueues();
		$queueManager = new SqsQueueManager(array(), array($queues['controlQueue']));
    	$queueManager->sendToQueue($queues['controlQueue'], $complexEvent);
    }
    
}

if(isset($_GET['method'])) {
	$oKTSQSQueues = new queueDispatcher();
	$method = $_GET['method'];
	unset($_GET['method']);
	call_user_func_array(array($oKTSQSQueues, $method), $_GET);
}
?>