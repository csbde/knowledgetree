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

require_once(realpath(dirname(__FILE__)) . '/ktqueue/config/config.inc.php');
require_once(realpath(dirname(__FILE__)) . '/ktqueue/common/ConfigManager.inc.php');
require_once (HOME . '/common/thirdparty/cloudfusion/cloudfusion.class.php');
require_once (HOME . '/common/thirdparty/cloudfusion/sqs.class.php');

class SqsQueueController
{
    private $queueConfig;

    /**
     * Queue Controller constructor
     *
     * @param string $output Output queue(s) for processing messages
     * @param string $config Path to override config file
     */
    public function __construct($output, $config = null)
    {
        $queues = $this->getQueues();
        // declare the queue configuration file which will be used
        $this->queueConfig = !empty($config) ? $config : HOME . '/config/queue_config.ini';
        // initialise the SQS Queue Listener
        $this->init($queues[$output]);
    }

    /**
     * Initialise the Queue Manager
     */
    protected function init($output)
    {
    	$this->output = $output;
        ConfigManager::load(HOME . '/config/aws_config.ini');
        if (ConfigManager::error()) {
        	// TODO : Return proper error
        }
        // load amazon authentication information
        $awsAuth = ConfigManager::getSection('AWS Authentication');
        // create the SQS Queue Manager
        try {
            $this->sqsQueue = new AmazonSQS($awsAuth['key'], $awsAuth['secret']);
            $this->createQueues();
        }
        catch (Exception $e) {
        	// TODO : Return proper error
        }
    }

    /**
     * Create all queues needed by the Queue Manager, if they do not already exist
     */
    // TODO shorter sleep time
    // TODO catch errors in queue creation and log appropriately?
    // (should only happen when queues have been deleted and request to recreate comes too soon
    // - rather implement a sleep then try again?)
    protected function createQueues()
    {
		try {
            $this->sqsQueue->create_queue($this->output);
            sleep(3);
        }
        catch (Exception $e) {
			// TODO : Return proper error
        }
        // sleep in order to give queues time to be created if they do not already exist
        // TODO should only do this if queues did not already exist
    }

    /**
     * Sets the output queue dynamically and sends to that queue
     * Sends to first available queue if none specified
     *
     * @param string $queue the name of the output queue to use
     * @param object $eventObject
     */
    public function sendToQueue($eventObject)
    {
        $response = $this->sendMessage($eventObject);
    }
    
    /**
     * Sends messages to the output queue
     * 
     * @param Event $eventObject
     * @return string $response->body->SendMessageResult->MessageId The id of the sent message
     */
    protected function sendMessage($eventObject)
    {
        try {
            $response = $this->sqsQueue->send_message($this->output, lib::sSerialize($eventObject));
            
            return $response->body->SendMessageResult->MessageId;
        }
        catch (Exception $e) {

            return null;
        }
    }

    /**
     * Fetches the full list of available queues which can be used by either a manager or listener
     *
     * @return array A list of queues
     */
    protected function getQueues()
    {
        ConfigManager::load(HOME . '/config/queue_config.ini');
        if (ConfigManager::error()) {
			// TODO : Return error
        }

        // load amazon authentication information
        $queues = ConfigManager::getSection('Queues');

        return $queues;
    }
        
} // end class
?>