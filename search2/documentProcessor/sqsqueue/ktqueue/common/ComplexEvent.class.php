<?php 

require_once('lib.static.php');
require_once('Event.class.php');
require_once('Benchmark.inc.php');
/**
 * ComplexEventException as simple extention of Exception
 * @author Mark Holtzhausen
 *
 */
class ComplexEventException extends Exception{}

class ComplexEvent{
	/**
	 * The container for benchmarking this event 
	 * @var array
	 */
	public $benchmark=NULL;
	/**
	 * The unique identifier for this container. Created on object construction
	 * @var string
	 */
	public $id=null;
	/**
	 * An array of callback urls for different reporting methods.
	 * A callback url can contain the following variable declarations:
	 * 		[eventId]			Denoting the id of the event in question.
	 * 		[complexEventId]	Denoting the id of the complex event.
	 * 		[eventMessage]		Denoting the event Message.
	 * 		[eventObject]		Denoting the serialized event object.
	 * 
	 * If a callback url is preceded with POST|, the callback will be accessed via a curl post 
	 * while all the variables mentioned above will be sent as post variables
	 * 
	 * Example URL: POST|http://example.com/callback.php?eid=[eventId]&ceid=[complexEventId]&action=[eventMessage]
	 * 
	 * @var array
	 */
	public $callbacks=array(
		'done'					=>null,
		'onQueueNextEvent'		=>null,
		'onReturnEvent'			=>null,
		'onReturnEventFailure'	=>null,
		'onReturnEventSuccess'	=>null
	);
	/**
	 * Flag to determine whether an error was encountered
	 * @var boolean
	 */
	public $hadError=false;
	/**
	 * The exception object if an error occurred
	 * @var Exception
	 */
	public $exception=null;
	/**
	 * An array containing the event list comprising the complex event.
	 * @var Array
	 */
	public $events=array();
	/**
	 * A nested array containing dependency information.
	 * @var Array
	 */
	public $dependencies=array();
		
	
	
	/**
	 * ComplexEvent Class Constructor
	 * @return void
	 */
	public function __construct(){
		$this->id=lib::uuid();
	}
	
	/**
	 * Wrapper function for starting a named benchmark
	 * @param String $benchmarkName		The name of the benchmark to start
	 * @return void
	 */
	public function benchmark_start($benchmarkName=true){
		if($benchmarkName)Benchmark::start($this->benchmark,$benchmarkName);
	}
	
	/**
	 * Wrapper function for stopping a named benchmark
	 * @param String $benchmarkName		The name of the benchmark to stop
	 * @return void
	 */
	public function benchmark_stop($benchmarkName=NULL){
		if($benchmarkName)Benchmark::stop($this->benchmark,$benchmarkName);
	}
	
	/**
	 * Wrapper function for getting the named benchmark. 
	 * @param String $benchmarkName[TRUE]		The name of the benchmark to fetch. If called without a benchmarkName, it will return a summary containing all benchmarks in the container.
	 * @return void
	 */
	public function benchmark_get($benchmarkName=true){
		return Benchmark::get($this->benchmark,$benchmarkName);
	}
	
	/**
	 * Create a new exception with the message and code provided and set this event exception and error flag
	 * @param $message			The error Message
	 * @param $code				The error Code
	 * @return void
	 */
	public function error($message=NULL,$code=0){
		$this->hadError=true;
		$this->exception=new ComplexEventException($message,$code); 
	}
	
	/**
	 * Provide a processed Callback URL
	 * @param $callbackName		This refers to the named callbacks defined in this object (callbacks)
	 * @param $eventObject		The relevant event object to investigate for the url parsing. An event name can also be passed.
	 * @return string			URL
	 */
	public function processCallbackUrl($callbackName=NULL,$eventObject=NULL){
		$url=NULL;
		if(isset($this->callbacks[$callbackName]))if($this->callbacks[$callbackName]){
			$parseVars=array();
			if(!($eventObject instanceof Event)){
				$eventObject=isset($this->events[$eventObject])?$this->events[$eventObject]:NULL;
			}
			if($eventObject instanceof Event){
				$parseVars['eventId']=$eventObject->id;
				$parseVars['eventMessage']=$eventObject->message;
				$parseVars['eventObject']=lib::sSerialize($eventObject);
			}
			$parseVars['id']=$this->id;
			$url=lib::parseString($this->callbacks[$callbackName],$parseVars);
		}
		return $url;
	}
	
	/**
	 * Get's the batch of events next in line. These events include those that have already been started.
	 * @return Array
	 */
	public function getNextUnfilteredBatch(){
		$batch=array();
		foreach($this->events as $name=>$event){
			if(!$event->completed){
				if(!key_exists($name,$this->dependencies))$this->dependencies[$name]=array();
				$dependencies=$this->dependencies[$name];
				$flag=true;
				foreach($dependencies as $dependency=>$done){
					$flag=$flag && $this->events[$dependency]->completed;
				}
				if($flag)$batch[$name]=$event;
			}
		}
		return $batch;
	}
	
	/**
	 * Return the next batch of events that need to be queued
	 * @return unknown_type
	 */
	public function getNextBatch(){
		$batch=$this->getNextUnfilteredBatch();
		$nextBatch=array();
		foreach($batch as $name=>$event){
			if(!$event->started)$nextBatch[$name]=$event;
		}
		return $nextBatch;
	}
	
	/**
	 * Get the count of the remaining items to determine whether the complex event is completed.
	 * @return integer
	 */
	public function getBatchCount(){
		return count($this->getNextUnfilteredBatch());
	}
	
	/**
	 * Updates an event or an array of events into the internal structure.
	 * @param $event
	 * @return void
	 */
	public function updateEvent($newEvent=null){
		if(!is_array($newEvent)){
			$eventName=$this->findEventNameById($newEvent->id);
			if($eventName!=false){
				$this->events[$eventName]=$newEvent;
			}
		}else{
			foreach($newEvent as $item){
				$this->updateEvent($item);
			}
		}
	}
	

	/**
	 * BY REF: Find an event by it's id and return a pointer
	 * @param $id
	 * @return Event
	 */
	public function &findEventById($id=NULL){
		$eventName=$this->findEventNameById($id);
		return in_array($eventName,$this->events)?$this->events[$eventName]:NULL;
	}
	
	/**
	 * Find the name of an event by it's id
	 * @param $id
	 * @return String
	 */	
	public function findEventNameById($id=NULL){
		foreach($this->events as $eventName => $event){
			if($event->id==$id)return $eventName;
		}
		return false;
	}
	
	
	/**
	 * Add a new event to the complex event list.
	 * @param String $name		An identifier for this event later to be used for setting up dependencies
	 * @param Event $event
	 * @return void
	 */
	public function addEvent($name=NULL,Event $event=NULL){
		if($event instanceof Event){
		    $event->containerId = $this->id;
			$this->events[$name]=$event;
		}else{
			throw new EventException('Dependency you tried to add is not an Event object');
		}
	}
	
	/**
	 * Sets a dependency. 
	 * @param $name				The name used to refer to an event in the list.
	 * @param $dependency		An array containing the names of other events that must complete before the named one can be executed.
	 * @return void
	 */
	public function setDependency($name=NULL,$dependency=NULL){
		if(!is_array($dependency)){
			$dependency=(string)$dependency;
			if(!key_exists($name,$this->dependencies))$this->dependencies[$name]=array();
			$this->dependencies[$name][$dependency]=true;
		}else{
			foreach($dependency as $item){
				$this->setDependency($name,$item);
			}
		}
	}
	
}

?>