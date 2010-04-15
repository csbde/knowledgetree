<?php 

require_once('lib.static.php');

/**
 * EventException as simple extention of Exception
 * @author Mark Holtzhausen
 *
 */
class EventException extends Exception{}

/**
 * Event object - container for event encapsulation and transport
 * @author Mark
 *
 */
class Event{
	/**
	 * The id of the complex event container
	 * @var string
	 */
	public $containerId=null;
	
	/**
	 * The unique id of this Event Object
	 * @var unknown_type
	 */
	public $id=null;
	/**
	 * The event message. In the current model contains *classname.method* for remote execution.
	 * @var string
	 */
	public $message=null;
	/**
	 * Execution Parameters. Contains parameters to be used in remote handling.
	 * @var array
	 */
	public $params=array();
	/**
	 * Event Process start timestamp.
	 * @var timestamp
	 */
	public $started=null;
	/**
	 * The container for benchmarking this event 
	 * @var array
	 */
	public $benchmark=NULL;
	/**
	 * Flag to determine whether the event is in the inbox or the outbox.
	 * @var boolean
	 */
	public $completed=false;
	/**
	 * Flag to determine whether the event encountered an execution exception while being handled.
	 * @var boolean
	 */
	public $hadError=false;
	/**
	 * Return Data. This might contain data returned from the remote handler.
	 * @var array
	 */
	public $returnData=array();
	/**
	 * Return Exeption. If an exception occurred while handling this event, this variable will contain that exception object.
	 * @var Exception or extension thereof
	 */
	public $exception=null;
	
	/**
	 * Event Class Constructor
	 * @return void
	 */
	public function __construct($message=NULL,$parameters=NULL){
		$parameters=is_array($parameters)?$parameters:array();
		$this->id=lib::uuid();
		$this->message=(string)$message;
		$this->params=$parameters;
	}

	/**
	 * Create a new exception with the message and code provided and set this event exception and error flag
	 * @param $message			The error Message
	 * @param $code				The error Code
	 * @return void
	 */
	public function error($message=NULL,$code=0){
		if($message instanceof Exception){
			$this->exception=$message; 
		}else{
			$this->exception=new EventException($message,$code); 
		}
		$this->hadError=true;
	}
	/**
	 * Set the event start timestamp to current time.
	 * @return unknown_type
	 */
	public function start(){
		$this->started=time();
	}
	
	/*
	 * Set the event completion flag
	 */
	public function complete(){
		$this->completed=true;
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
}

?>