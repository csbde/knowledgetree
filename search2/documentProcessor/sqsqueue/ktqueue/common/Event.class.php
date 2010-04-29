<?php 

require_once(KT_DIR . '/search2/documentProcessor/sqsqueue/ktqueue/common/lib.static.php');

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
		'@TRACE'=>null
	);	
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
	 * Provide a processed Callback URL
	 * @param $callbackName		This refers to the named callbacks defined in this object (callbacks)
	 * @param $additional		Additional variables to parse into the callback string.
	 * @return string			URL
	 */
	public function processCallbackUrl($callbackName=NULL,$additional=NULL){
		$url=NULL;
		if(isset($this->callbacks[$callbackName]))if($this->callbacks[$callbackName]){
			$parseVars=array();
			$parseVars['eventId']=$this->id;
			$parseVars['eventMessage']=$this->message;
			$parseVars['eventObject']=lib::sSerialize($this);
			$parseVars['id']=$parseVars['complexEventId']=$this->containerId;
			if(is_array($additional))$parseVars=array_merge($parseVars,$additional);
			$parseVars=lib::aUrlEncode($parseVars);
			$url=lib::parseString($this->callbacks[$callbackName],$parseVars);
		}
		return $url;
	}
	
	
	/**
	 * Get the Trace Url if it is defined
	 * @param $vars	Additional variables to parse into the callback url string
	 * @return String url
	 */
	public function traceUrl($vars=NULL){
		return $this->processCallbackUrl('@TRACE',$vars);
	}
	
	/**
	 * Set a trace Url
	 * @param $url		The url template to use as trace callback - see the comment on callbacks above
	 * @return void
	 */
	public function setTraceUrl($url=NULL){
		if($url)$this->callbacks['@TRACE']=$url;
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