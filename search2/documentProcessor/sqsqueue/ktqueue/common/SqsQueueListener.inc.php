<?php

// TODO we should build in checks for duplicated pickup (we can use persisted objects for this)
//      these checks could be set to run in debug mode only

// check for existence of curl, else exit - perhaps log something - this should probably be part of the class?
if (!function_exists('curl_init')) {
    die('error: curl not supported');
}

require_once(realpath(dirname(__FILE__)) . '/../config/config.inc.php');
require_once (HOME . '/common/lib.static.php');
require_once (HOME . '/common/Event.class.php');
require_once (HOME . '/common/EventHandler.inc.php');
require_once (HOME . '/common/thirdparty/cloudfusion/cloudfusion.class.php');
require_once (HOME . '/common/thirdparty/cloudfusion/sqs.class.php');
require_once (HOME . '/common/ConfigManager.inc.php');

/**
 * The standard queue listener listens to a single queue and responds on a single queue
 */
class SqsQueueListener {

    protected $inQueue = null;
    protected $outQueue = null;
    protected $sqsQueue;
    protected $sleep;
    protected $visibilityTimeout;
    
    /**
     * Queue Listener constructor
     *
     * @param string $inQueue Input queue for incoming messages
     * @param string $outQueue Output queue for responses
     * @param int $sleep Time to sleep in microseconds (1/1000th of a millisecond)
     * @param int $visibility Visibility Timeout - the time during which no other process may access this message
     */
    public function __construct($inQueue, $outQueue = null, $sleep = null, $visibility = null)
    {
        $this->inQueue = $inQueue;
        $this->outQueue = $outQueue;
        if (empty($sleep)) {
            $sleep = 50000;
        }
        $this->sleep = $sleep;
        if (empty($visibility)) {
            $visibility = 60;
        }
        $this->visibilityTimeout = $visibility;

        // initialise the SQS Queue Manager
        $this->init();
    }

    /**
     * Initialise the Queue Listener
     */
    protected function init()
    {
        ConfigManager::load(HOME . '/config/aws_config.ini');
        if (ConfigManager::error()) {
            // log error and die...
            die (ConfigManager::getErrorMessage());
        }

        // load amazon authentication information
        $awsAuth = ConfigManager::getSection('AWS Authentication');
        // create the SQS Queue Manager
        try {
            $this->sqsQueue = new AmazonSQS($awsAuth['key'], $awsAuth['secret']);
            $this->createQueues();
        }
        catch (Exception $e) {
            // TODO log an error
            die($e->getMessage());
        }
    }

    /**
     * Create all queues needed by the Queue Listener, if they do not already exist
     */
    // TODO shorter sleep time
    // TODO catch errors in queue creation and log appropriately?
    // (should only happen when queues have been deleted and request to recreate comes too soon
    // - rather implement a sleep then try again?)
    protected function createQueues()
    {
        // ensure the required input and output queues exist
        try {
            $this->sqsQueue->create_queue($this->inQueue);
            $this->sqsQueue->create_queue($this->outQueue);
        }
        catch (Exception $e) {
            // TODO log an error
            die($e->getMessage());
        }

        // sleep in order to give queues time to be created if they do not already exist
        // TODO should only do this if queues did not already exist
        sleep(5);
    }

    /**
     * Listens to the input queue(s), running in an endless loop with sleep periods
     * 
     * @param $visibilityTimeout The time during which the message is not visible to other listeners
     */
    public function listen($visibilityTimeout = null)
    {
        if (!empty($visibilityTimeout)) {
            $this->visibilityTimeout = $visibilityTimeout;
        }

        $opt = array();
        $opt['VisibilityTimeout'] = $this->visibilityTimeout;

        while (true) {
            // look for messages on the input queue(s)
            $this->checkMessages($opt);
            usleep($this->sleep);
        }
    }

    /**
     * Checks the input queue for messages
     *
     * @param array $opt The options to use when fetching messages from a queue
     */
    protected function checkMessages($opt = array())
    {
        try {
            fwrite(STDOUT, "There are " . $this->sqsQueue->get_queue_size($this->inQueue) . " messages on {$this->inQueue}\n");
        }
        catch (Exception $e) {
            // TODO log an error
            fwrite(STDOUT, "There was an error getting the queue size for {$this->inQueue}\n");
        }

        try {
            $response = $this->sqsQueue->receive_message($this->inQueue, $opt);
            if ($response->isOK()) {
                $body = $response->body;
                if ($body->ReceiveMessageResult->Message->MessageId) {
                    $this->processMessage($body);
                }
            }
        }
        catch (Exception $e) {
            // TODO log an error
        }
    }

    /**
     * Processes a message picked up from the input queue and sends a response message to the output queue
     *
     * @param simplexml object $body The body of the SQS message
     */
    protected function processMessage($body)
    {
        // picked up a message
        fwrite(STDOUT, "Picked up a message on {$this->inQueue} with id " . $body->ReceiveMessageResult->Message->MessageId."\n");

        $eventObject = lib::sUnserialize($body->ReceiveMessageResult->Message->Body);
        // if anything went wrong and we cannot unserialize
        if (!($eventObject instanceof Event)) {
            // logging will be done by calling function
            throw new RuntimeException('The event could not be unserialized');
        }
        
        // pass the event to the event handler, receiving the updated event in return
        EventHandler::run_event($eventObject->message, $eventObject->params, $eventObject);
        // whether there was failure or not, mark the event as complete - this does not depend on successful execution 
        // of the method
        $eventObject->complete();

        // delete original message
        try {
            $response = $this->sqsQueue->delete_message($this->inQueue, trim($body->ReceiveMessageResult->Message->ReceiptHandle));
        }
        catch (Exception $e) {
            // TODO log an error
            // do we need to do anything else here?
        }

        // send a response to a message picked up
        return $this->sendMessage($eventObject, $body->ReceiveMessageResult->Message->MessageId);

        fwrite(STDOUT,"------------------------------\n\n\n");
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
            $response = $this->sqsQueue->send_message($this->outQueue, lib::sSerialize($eventObject));
            fwrite(STDOUT, "Added message to {$this->outQueue}: [".$response->body->SendMessageResult->MessageId."]\n");
            return $response->body->SendMessageResult->MessageId;
        }
        catch (Exception $e) {
            // TODO log an error
            return null;
        }
    }

    /**
     * Fetches the full list of available queues which can be used by either a manager or listener
     *
     * @return array A list of queues
     */
    static public function getQueues()
    {
        ConfigManager::load(HOME . '/config/queue_config.ini');
        if (ConfigManager::error()) {
            // log error and die...
            die (ConfigManager::getErrorMessage());
        }

        // load amazon authentication information
        $queues = ConfigManager::getSection('Queues');

        return $queues;
    }

    /**
     * Static function to get command line arguments for starting up the listener or manager object
     *
     * @param array $argc The count of command line arguments
     * @param array $argv The list of command line arguments
     * @return array $args The parsed collection of command line arguments
     */
    static public function getCmdLnArguments($argc, $argv)
    {
        $argMap = array('i' => 'input', 'o' => 'output', 's' => 'sleep', 'v' => 'visibility');
        $args = array('input' => null, 'output' => null, 'sleep' => null, 'visibility' => null);

        for ($i = 1; $i < $argc; ++$i) {
            $argument = $argv[$i];
            if (preg_match('/^-{1,2}(\w*)/', $argument, $matches)) {
                // check for value
                ++$i;
                if (isset($argv[$i])) {
                    // multiples allowed
                    if (($argMap[$matches[1]] == 'input') || ($argMap[$matches[1]] == 'output')) {
                        $args[$argMap[$matches[1]]][] = $argv[$i];
                    }
                    // single
                    else {
                        $args[$argMap[$matches[1]]] = $argv[$i];
                    }
                }
                // else it will be a switch type argument and not a value set argument;
                // no need to deal with these at the moment
            }
        }

        return $args;
    }

} // end class

?>