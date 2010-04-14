<?php

// TODO we should build in checks for duplicated pickup (we can use persisted objects for this)
//      these checks could be set to run in debug mode only

// check for existence of curl, else exit - perhaps log something - this should probably be part of the class?
if (!function_exists('curl_init')) {
    die('error: curl not supported');
}

require_once(realpath(dirname(__FILE__)) . '/../config/config.inc.php');
require_once (HOME . '/common/PersistenceManager.inc.php');
require_once (HOME . '/common/ComplexEvent.class.php');
require_once (HOME . '/common/SqsQueueListener.inc.php');

/**
 * The queue manager extends the listener to allow listening to multiple input queues
 * and delegation to multiple output queues
 * 
 * It also adds the ability to fetch a message indepent of the listening operation
 * and deal with it as the calling application wishes
 * 
 * NOTE this latter ability is not in fact used by any of the current code
 */
class SqsQueueManager extends SqsQueueListener {

    static private $queueMap = array();

    /**
     * Queue Manager constructor
     *
     * @param string $inQueue Input queue for incoming messages
     * @param string $outQueue Output queue for responses
     * @param int $sleep Time to sleep in microseconds (1/1000th of a millisecond)
     * @param int $visibility Visibility Timeout - the time during which no other process may access this message
     */
    public function __construct($inQueue = array(), $outQueue = array(), $sleep = null, $visibility = null)
    {
        if (empty($sleep)) {
            $sleep = 50000;
        }
        $this->sleep = $sleep;
        if (empty($visibility)) {
            $visibility = 15;
        }

        parent::__construct($inQueue, $outQueue, $sleep, $visibility);
    }

    /**
     * Initialise the Queue Manager
     */
    protected function init()
    {
        parent::init();

        ConfigManager::load(HOME . '/config/queue_config.ini');
        if (ConfigManager::error()) {
            // log error and die...
            die (ConfigManager::getErrorMessage());
        }

        // load the queue map, used to map processes to queues
        self::$queueMap = ConfigManager::getSection('Queue Map');
        // if the queue map is empty, log an error
        if (empty(self::$queueMap)) {
            // TODO log an error
            die('Unable to get queue map for output queues');
        }

        // set up persistence manager
        PersistenceManager::connect();
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
        // controller will listen to many queues, so inQueue will be an array of queue names
        try {
            foreach ($this->inQueue as $queue) {
                $this->sqsQueue->create_queue($queue);
            }
            // controller will send to many queues, so outQueue will be an array of queue names
            foreach ($this->outQueue as $queue) {
                $this->sqsQueue->create_queue($queue);
            }
        }
        catch (Exception $e) {
            // TODO log an error
            die($e->getMessage());
        }

        // sleep in order to give queues time to be created if they do not already exist
        sleep(5);
    }

    /**
     * Sets the output queue dynamically and sends to that queue
     *
     * @param string $queue the name of the output queue to use
     * @param object $eventObject
     */
    public function sendToQueue($queue, $eventObject)
    {
        $this->outQueue = $queue;
        $response = $this->sendMessage($eventObject);
    }

    /**
     * Checks the input queue for messages
     *
     * @param array $opt The options to use when fetching messages from a queue
     */
    protected function checkMessages($opt = array())
    {
        foreach ($this->inQueue as $queue) {
            try {
                fwrite(STDOUT, "There are " . $this->sqsQueue->get_queue_size($queue) . " messages on {$queue}\n");
            }
            catch (Exception $e) {
                fwrite(STDOUT, "There was an error getting the queue size for {$queue}\n");
            }

            try {
                $response = $this->sqsQueue->receive_message($queue, $opt);
                if ($response->isOK()) {
                    $body = $response->body;
                    if ($body->ReceiveMessageResult->Message->MessageId) {
                        $this->processMessage($body, $queue);
                    }
                }
            }
            catch (Exception $e) {
                // TODO log an error
            }
        }
    }

    /**
     * Delegates events to queues
     * Receives and deals with responses
     *
     * @param simplexml object $body The body of the SQS response
     * @param string $queue The queue from which the message was picked up
     */
    protected function processMessage($body, $queue)
    {
        // picked up a message
        fwrite(STDOUT, "Picked up a message on {$queue} with id " . $body->ReceiveMessageResult->Message->MessageId."\n");

        $eventObject = lib::sUnserialize($body->ReceiveMessageResult->Message->Body);
        // what error handling do we want here?
        
    	if ($eventObject instanceof Event) {
            $this->_processSimpleEventIfPartOfAComplexEventButNameMeBetter($body->ReceiveMessageResult->Message->ReceiptHandle, $queue, $eventObject);
        } else
        
        if ($eventObject instanceof ComplexEvent) {
            $this->_processComplexEvent($body->ReceiveMessageResult->Message->ReceiptHandle, $queue, $eventObject);
        }
        
        // remove the original message from the queue
        $response = $this->sqsQueue->delete_message($queue, trim($receiptHandle));
        

        fwrite(STDOUT,"------------------------------\n\n\n");
    }

    private function getEventBatch( &$complexEvent )
    {
        $events = $complexEvent->getNextBatch();
        foreach ($events as $key => $event) {
            // mark as started
            $events[$key]->start();
        }
        // send batch to complex event to update
        $complexEvent->updateEvent($events);
    	return $events; 
    }
    
    /**
     * Processes a complex event object, directing simple events to the appropriate queues
     *
     * @param string $receiptHandle The SQS identifier for the message
     * @param string $queue The queue from which the message was received
     * @param ComplexEvent $complexEvent The complex event object
     */
    private function _processComplexEvent($receiptHandle, $queue, $complexEvent)
    {
        // we will need a lock on the database during the entire process of this operation
        PersistenceManager::lockPersistenceDatabase();

		$events = $this->getEventBatch( $complexEvent );
        
        // before persistence, we need to set the first batch as started
        // persist
        PersistenceManager::persistComplexEvent($complexEvent);

            foreach ($events as $event) {
            	$error = true;
            	$eventProcess = $this->_getProcess($event->message);
                if (!empty($eventProcess)) {
                    // NOTE if there is a problem adding to the queue then re-persist with the event marked as unstarted
                    try {
                        $error = !($this->sqsQueue->send_message(self::$queueMap[$eventProcess], lib::sSerialize($event))->isOK());
                    }
                    catch (Exception $e) {
                    	// TODO Log
                    }
                    
                    if ($error) {
                    	$this->_unStartEvent( $complexEvent, $event );
                    } else {
                    	fwrite(STDOUT, "Added message to " . self::$queueMap[$eventProcess]
                        . ":[".$response->body->SendMessageResult->MessageId."]\n");
                    }
                }
                else {
					// TODO log
                }
            }
        

        // unlock persistence database
        PersistenceManager::unlockPersistenceDatabase();
    }

    /**
     * Processes a simple event object which has returned from a listener
     *
     * @param string $receiptHandle The SQS identifier for the message
     * @param string $queue The queue from which the message was received
     * @param Event $eventObject The simple event object
     */
    private function _processSimpleEventIfPartOfAComplexEventButNameMeBetter($receiptHandle, $queue, $eventObject)
    {
        // check if event object is in a DONE state, if so update (will also persist or remove persisted object)
        if ($eventObject->completed) {
            // get complex event via containerId
            $complexEventId = $eventObject->containerId;
            if (!empty($complexEventId)) {
	            $persistedEvent = PersistenceManager::getPersistedEvent($complexEventId);
	            $complexEvent = lib::sUnserialize($persistedEvent);
                // update complex event - this is the responsibility of the event object
                $complexEvent->updateEvent($eventObject);
                // is the complex event now done?
                if (!$complexEvent->getBatchCount()) {
                    PersistenceManager::deletePersistedEvent($complexEvent->id);
                }
                else {
                    // complex event not yet done, continue to next batch of events
                    $this->_processComplexEvent($receiptHandle, $queue, $complexEvent);
                }
            }
        }

    }

    /**
     * Determine the process for which an event was intended
     *
     * @param string $eventMethod (format is class.method)
     * @return string The process/class for the object
     */
    private function _getProcess($eventMethod)
    {
        $processArray = explode('.', $eventMethod);
        return $processArray[0];
    }

    /**
     * Resets an event's started status to false
     *
     * @param ComplexEvent $complexEvent
     * @param Event $event
     * @param boolean $persist Whether to persist the complex event after update
     */
    private function _unStartEvent($complexEvent, $event)
    {
        $event->started = null;
        $complexEvent->updateEvent($event);
        PersistenceManager::persistComplexEvent($complexEvent);
    }

} // end class

?>