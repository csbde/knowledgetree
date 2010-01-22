<?php
/**
 * ClientTools System Logging Static Class
 * 
 * For more information about use: http://ktwiki.kt-cpt.internal/Logging
 * @author Mark Holtzhausen
 *
 */
class Clienttools_Syslog{
	/** The default folder in which to put the log files **/
	private static $logFolder='../../var/log/';
	private static $debugLogTemplate='[date] | [time] | INFO | [session] | [user] | [location] | [debug_message] | ([debug_data])';
	private static $traceLogTemplate='[date] | [time] | LOG | [session] | [user] | [location] | [trace_message]';
	private static $errorLogTemplate='[date] | [time] | ERROR | [session] | [user] | [location] | [error_detail] | ([error])';
	
	
	/**
	 * Parse an array into a string template
	 * @param $template		The template string - array keys in square brackets : [date] | [time] | ERROR | [session] | [user] | [location] | [error_detail] | ([error])
	 * @param $data			The associative array to parse into template. Keys will replace [keys] in template string.
	 * @return string		The parsed template string
	 */
	private static function parseTemplate($template=NULL,$data=NULL){
		$ret=null;
		if(is_array($data)){
			$txs=array_keys($data);
			foreach($txs as $idx=>$val){
				$txs[$idx]='['.$val.']';
			}
			$txd=array_values($data);
			$ret=str_replace($txs,$txd,$template);
		};
		return $ret;
	}
	
	
	/**
	 * Return the calculated log file name
	 * @return void
	 */
	private static function getLogFile(){
		$fileName=self::$logFolder.'kt_clienttools_'.date('Y-m-d').'.log.txt';
		return $fileName;
	}
	
	
	/**
	 * Write a line to the log file.
	 * @param $line
	 * @return void
	 */
	private static function writeLogLine($line=NULL){
		if($line){
			$fp=fopen(self::getLogFile(),'a');
			fwrite($fp,$line."\n");
			fclose($fp);
		}
	}
	
	/**
	 * Return a boolean indicating whether error logging should be done
	 * @return boolean
	 */
	private static function doErrorLogging(){
		return KTConfig::getSingleton()->get('explorerCPSettings/debugLevel')=='error' || self::doDebugLogging();
	}
	
	/**
	 * Return a boolean indicating whether debug logging should be done
	 * @return boolean
	 */
	private static function doDebugLogging(){
		return KTConfig::getSingleton()->get('explorerCPSettings/debugLevel')=='debug';
	}
	
	/**
	 * Store a line in the log file.. the message and a json string containing the data information will be stored
	 * @param $user			The logged in user
	 * @param $location		Information about the location from whence the function was called
	 * @param $message		The descriptive message explaining the debug data that follows
	 * @param $data			The debug data - this will be converted to a json string.
	 * @return void
	 */
	public static function logInfo($user,$location,$message,$data){
		if(self::doDebugLogging()){
			list($usec, $sec) = explode(" ", microtime());
			$usec=ceil($usec*1000);
			$entry=self::parseTemplate(self::$debugLogTemplate,array(
				'date'	=>date('Y-m-d'),
				'time'	=>date('h:i:s').':'.$usec,
				'user'	=>$user,
				'session'=>session_id(),
				'location'=>$location,
				'debug_message'=>$message,
				'debug_data'=>json_encode($data)
			));
			
			self::writeLogLine($entry);
		}
	}
	
	/**
	 * Store a line in the log file.. A simple string to indicate a point in the software
	 * @param $user			The logged in user
	 * @param $location		Information about the location from whence the function was called
	 * @param $message		A string indicating a point reached in the software
	 * @return void
	 */
	public static function logTrace($user,$location,$message){
		if(self::doDebugLogging()){
			list($usec, $sec) = explode(" ", microtime());
			$usec=ceil($usec*1000);
			$entry=self::parseTemplate(self::$traceLogTemplate,array(
				'date'	=>date('Y-m-d'),
				'time'	=>date('h:i:s').':'.$usec,
				'user'	=>$user,
				'session'=>session_id(),
				'location'=>$location,
				'trace_message'=>$message,
			));
			
			self::writeLogLine($entry);
		}
	}
	
	/**
	 * Store a line in the log file.. An Error log
	 * @param $user			The logged in user
	 * @param $location		Information about the location from whence the function was called
	 * @param $detail		A string providing information as to the context of the encountered error
	 * @param $err			The exception object - this will be serialized
	 * @return void
	 */
	public static function logError($user=NULL,$location=NULL,$detail=NULL,$err=NULL){
		if(self::doErrorLogging()){
			list($usec, $sec) = explode(" ", microtime());
			$usec=ceil($usec*1000);
			$entry=self::parseTemplate(self::$errorLogTemplate,array(
				'date'	=>date('Y-m-d'),
				'time'	=>date('h:i:s').':'.$usec,
				'user'	=>$user,
				'session'=>session_id(),
				'location'=>$location,
				'error_detail'=>json_encode($detail),
				'error'=>json_encode($err),
			));
			
			self::writeLogLine($entry);
		}
		
	}
}
?>